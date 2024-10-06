<?php

/* Require the Firebase JWT and Crypto library. */
use Simple_Jwt_Auth\Firebase\JWT\JWT;
use Simple_Jwt_Auth\Firebase\JWT\Key;
use Simple_Jwt_Auth\Database\DBManager;
use Simple_Jwt_Auth\OpenSSL\Crypto;
use Simple_Jwt_Auth\Notice\JWTNotice;

/**
 * The public-facing functionality of the plugin.
 * This class will extend the Simple_Jwt_Auth_Public class for JWT auth.
 *
 * @link       https://github.com/sayandey18/simple-jwt-auth
 * @since      1.0.0
 *
 * @package    Simple_Jwt_Auth
 * @subpackage Simple_Jwt_Auth/public/endpoints
 * @author     Sayan Dey <mr.sayandey18@outlook.com>
 */

class Simple_Jwt_Auth_Api extends Simple_Jwt_Auth_Public {
    /**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private string $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private string $version;

	/**
	 * The endpoint of this plugin API.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $endpoint The JWT endpoint of this plugin.
	 */
	private string $endpoint;

    /**
	 * Store errors to display if the JWT is wrong.
	 * @since   1.0.0
	 * @var     WP_Error|null
	 */
    private ?WP_Error $jwt_error = null;

    /**
     * Supported algorithms to sign the token.
     * 
     * @since   1.0.0
	 * @see     https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40
     */
    private array $supported_algos = [
		'HS256', 'HS384', 'HS512', 
		'RS256', 'RS384', 'RS512', 
		'ES256', 'ES384', 'ES512', 
		'PS256', 'PS384', 'PS512'
	];

    /**
	 * Initialize the class and set its properties.
	 *
	 * @since   1.0.0
	 * @param   string $plugin_name
	 * @param   string $version
     * @param   string $endpoint
	 */
	public function __construct( string $plugin_name, string $version, string $endpoint ) {
		parent::__construct( $plugin_name, $version );
        
        $this->endpoint = $endpoint . '/v' . intval( $version );
	}

    /**
	 * Add the endpoints to the API
	 * 
	 * @since	1.0.0
	 */
	public function simplejwt_add_api_routes() {
		register_rest_route( $this->endpoint, 'token', 
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'simplejwt_generate_token' ),
				'permission_callback' => '__return_true',
			) 
		);

		register_rest_route( $this->endpoint, 'token/validate', 
			array(
				'methods'             => 'POST',
				'callback'            => array( $this, 'simplejwt_validate_token' ),
				'permission_callback' => '__return_true',
			) 
		);
	}

    /**
	 * Add CORS support to the request.
	 * 
	 * @since	1.0.0
	 */
	public function simplejwt_add_cors_support() {
		// Check the CORS status from database.
		$enable_cors = filter_var( 
			DBManager::get_config( 'enable_cors' ),
			FILTER_VALIDATE_BOOLEAN 
		);

		if ( $enable_cors ) {
			$headers = apply_filters( 'simplejwt_cors_allow_headers',
				'Access-Control-Allow-Headers, Content-Type, Authorization' );
			header( sprintf( 'Access-Control-Allow-Headers: %s', $headers ) );
		}
	}

    /** 
     * Get the user and password in the request body and generate a JWT token 
     * for further authentication.
	 * 
     * @param	WP_REST_Request $request
	 * @return	mixed|WP_Error|null
     * 
    */
    public function simplejwt_generate_token( WP_REST_Request $request ) {
		// Get the username and password from REST request.
        $username = $request->get_param( 'username' );
		$password = $request->get_param( 'password' );

		// Check if username or password is missing and return an error.
		if ( empty( $username ) || empty( $password ) ) {
			return new WP_Error(
				'simplejwt_missing_credentials',
				JWTNotice::get_notice( 'missing_credential' ),
				['status' => 400]
			);
		}

		// Get defined algorithm from the database.
        $algorithm = $this->simplejwt_get_algorithm();
        
        // Check algorithm if not exist return an error.
        if ( !$algorithm ) {
			return new WP_Error(
				'simplejwt_unsupported_algorithm',
				JWTNotice::get_notice( 'unsupported_algo' ),
				['status' => 403]
			);
		}

		// Determine if we're using symmetric (HS*) or asymmetric algorithms.
		$key_type = in_array( $algorithm, ['HS256', 'HS384', 'HS512'], true ) ? 'secret_key' : 'private_key';

		// Get the `signing_key` from database based on key type.
		$signing_key = DBManager::get_config( $key_type );

		// Check the signing key if not exist return an error.
		if ( $signing_key === false ) {
			return new WP_Error(
				'simplejwt_bad_' . $key_type,
				JWTNotice::get_notice( 'bad_' . $key_type ),
				['status' => 403]
			);
		}

		// Decrypt the `secret_key` key using `AES-256-GCM` algo.
		$encode_key = Crypto::decrypt(
			sanitize_textarea_field( $signing_key )
		);

		// If there is WP_Error, return the error.
		if ( is_wp_error( $encode_key ) ) {
			return $encode_key;
		}

		// Authenticate the user with the password cred.
        $user = wp_authenticate( $username, $password );

        //  If the authentication fails return an error.
		if ( is_wp_error( $user ) ) {
			$error_code = $user->get_error_code();
            $error_message = $user->get_error_message();

			return new WP_Error(
				'simplejwt_' . $error_code, 
				wp_strip_all_tags( $error_message ), 
				['status' => 403]
			);
		}

        // If the user validated create according JWT Token.
		$issued_at  = time();
		$not_before = apply_filters( 'simplejwt_not_before', $issued_at, $issued_at );
		$expire     = apply_filters( 'simplejwt_auth_expire', $issued_at + ( DAY_IN_SECONDS * 7 ), $issued_at );

		$payload = [
			'iss'  => $this->simplejwt_get_iss(),
			'iat'  => $issued_at,
			'nbf'  => $not_before,
			'exp'  => $expire,
			'data' => [
				'user' => [
					'id' => $user->data->ID,
				],
			],
		];

        // Let the user modify the token data before the sign.
        $token = JWT::encode(
			apply_filters( 'simplejwt_payload_before_sign', $payload, $user ),
			$encode_key,
			$algorithm
		);

		// Return error, there is any problem in creating token.
		if ( is_string( $token ) === false ) {
			return new WP_Error(
				'simplejwt_token_creation_error', 
				JWTNotice::get_notice( 'unknown_error' ),
				['status' => 500]
			);
		}

		// Prepare the token response.
		$data = new WP_REST_Response( array(
			'code'    => 'simplejwt_auth_credential',
			'message' => JWTNotice::get_notice( 'auth_credential' ),
			'data'    => [
				'status'       => 200,
				'id'           => $user->data->ID,
				'email'        => $user->data->user_email,
				'nicename'     => $user->data->user_nicename,
				'display_name' => $user->data->display_name,
				'token'        => $token
			]
		), 200 );

        // Let the user modify the data before send it back using `add_filter`.
        return apply_filters( 'simplejwt_token_before_dispatch', $data, $user );
    }

    /**
	 * This function is used by the /token/validate endpoint and by our middleware.
	 *
	 * The function take the token and try to decode it and validated it.
	 * @since   1.0.0
	 * 
	 * @param   WP_REST_Request $request
	 * @param	bool|string $custom_token
	 * 
	 * @return  object|WP_Error|array
	 * 
	 * The get_header( 'Authorization' ) checks for the header in the following order:
	 * 1. HTTP_AUTHORIZATION
	 * 2. REDIRECT_HTTP_AUTHORIZATION
	 */
    public function simplejwt_validate_token( WP_REST_Request $request, $custom_token = false ) {
		$auth_header = $custom_token ? $custom_token : $request->get_header( 'Authorization' );

		// If Authorization header not exist return an error.
		if ( !$auth_header ) {
			return new WP_Error(
				'simplejwt_no_auth_header',
				JWTNotice::get_notice( 'no_auth_header' ),
				['status' => 403]
			);
		}

		// Extract the authorization header.
		[$token] = sscanf( $auth_header, 'Bearer %s' );

		// If the format is not valid return an error.
		if ( !$token ) {
			return new WP_Error(
				'simplejwt_bad_auth_header',
				JWTNotice::get_notice( 'bad_auth_header' ),
				['status' => 400]
			);
		}

		$algorithm = $this->simplejwt_get_algorithm();
        
        // Check algorithm if not exist return an error.
        if ( !$algorithm ) {
			return new WP_Error(
				'simplejwt_unsupported_algorithm',
				JWTNotice::get_notice( 'unsupported_algo' ),
				['status' => 403]
			);
		}

		// Determine if we're using symmetric (HS*) or asymmetric algorithms.
		$key_type = in_array( $algorithm, ['HS256', 'HS384', 'HS512'], true ) ? 'secret_key' : 'public_key';

		// Get the `signing_key` from database based on key type.
		$signing_key = DBManager::get_config( $key_type );

		// Check the signing key if not exist return an error.
		if ( $signing_key === false ) {
			return new WP_Error(
				'simplejwt_bad_' . $key_type,
				JWTNotice::get_notice( 'bad_' . $key_type ),
				['status' => 403]
			);
		}

		// Decrypt secret key using `AES-256-GCM` algo.
		$decode_key = Crypto::decrypt( 
			sanitize_textarea_field( $signing_key )
		);

		// If there is WP_Error, return the error.
		if ( is_wp_error( $decode_key ) ) {
			return $decode_key;
		}

		// Decode the JWT token using try catch block
		try {
			$decoded_token = JWT::decode( $token, new Key( $decode_key, $algorithm ) );

			// Validate the issuer from decoded token.
			if ( $decoded_token->iss !== $this->simplejwt_get_iss() ) {
				return new WP_Error(
					'simplejwt_bad_issuer',
					JWTNotice::get_notice( 'bad_issuer' ),
					['status' => 403]
				);
			}

			// No user id in the token, return error.
			if ( !isset( $decoded_token->data->user->id ) ) {
				return new WP_Error(
					'simplejwt_bad_request',
					JWTNotice::get_notice( 'bad_request' ),
					['status' => 403]
				);
			}

			// Everything looks good, return the decoded token.
			if ( $custom_token ) {
				return $decoded_token;
			}

			// Return successful response to `token/validate` endpoint.
			return new WP_REST_Response( array(
				'code'    => 'simplejwt_valid_token',
				'message' => JWTNotice::get_notice( 'valid_token' ),
				'data'    => ['status' => 200]
			), 200 );
		} catch ( Exception $e ) {
			// Send error if Something were wrong trying to decode the token.
			return new WP_Error(
				'simplejwt_invalid_token',
				wp_strip_all_tags( $e->getMessage() ),
				['status' => 403]
			);
		}
    }

    /**
	 * This Middleware to try to authenticate the user according to token send.
	 * 
	 * This hook only should run on the REST API requests to authenticate
	 * if the user Token is valid, for any other normal call ex. wp-admin/.* 
	 * return the user.
	 *
	 * @since   1.0.0
	 * @param	int|bool $current_user
	 * @return  int|bool
	 */
    public function simplejwt_determine_current_user( $current_user ) {
		$rest_api_slug = rest_get_url_prefix();
		$requested_uri = !empty( $_SERVER['REQUEST_URI'] ) ? sanitize_url( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

		// If already valid user, or have an invalid url, don't attempt to validate token.
		$is_rest_defined = defined( 'REST_REQUEST' ) && REST_REQUEST;
		$is_rest_request = $is_rest_defined || strpos( $requested_uri, $rest_api_slug );

		if ( $is_rest_request && $current_user ) {
			return $current_user ;
		}

		// If the request URI is for validate the token don't do anything.
		$validate_uri = strpos( $requested_uri, 'token/validate' );
		if ( $validate_uri > 0 ) {
			$current_user;
		}

		// Get the Authorization header and check for the token.
		$auth_header = !empty( $_SERVER['HTTP_AUTHORIZATION'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_AUTHORIZATION'] ) ) : false;

		if ( !$auth_header ) {
			$auth_header = !empty( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ) ) : false;
		}

		if ( !$auth_header ) {
			return $current_user;
		}

		// Check if the auth header is not bearer.
		if ( strpos( $auth_header, 'Bearer' ) !== 0 ) {
			return $current_user;
		}

		// Check the token from the headers.
		$token = $this->simplejwt_validate_token( new WP_REST_Request(), $auth_header );

		if ( is_wp_error( $token ) ) {
			if ( $token->get_error_code() != 'simplejwt_no_auth_header' ) {
				$this->jwt_error = $token;
			}

			return $current_user;
		}

		// Everything is ok, return the user ID from token.
		return $token->data->user->id;
    }

	/**
	 * Filter to hook the rest_pre_dispatch, if the is an error in the request
	 * send it, if there is no error just continue with the current request.
	 * 
	 * @param	$request
	 * @return	mixed|WP_Error|null
	 */
	public function simplejwt_rest_pre_dispatch( $request ) {
		if ( is_wp_error( $this->jwt_error ) ) {
			return $this->jwt_error;
		}
		
		return $request;
	}

	/**
	 * Defined the token issuer (iss) filter hook.
	 * 
	 * @since	1.0.0
	 * @return	string
	 */
	private function simplejwt_get_iss() {
		return apply_filters( 'simplejwt_auth_iss', get_bloginfo( 'url' ) );
	}

    /**
	 * Get the algorithm used to sign the token from database and validate
	 * that the algorithm is in the supported list.
     * 
     * @since   1.0.0
	 * @return	bool|string
	 */
    private function simplejwt_get_algorithm() {
		$algorithm = DBManager::get_config( 'algorithm' );

        if ( !empty( $algorithm ) ) {
            if ( !in_array( $algorithm, $this->supported_algos ) ) {
                return false;
            }
        }
		
		return $algorithm;
	}
}
