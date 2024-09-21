<?php

/* The namespace to avoid class name collisions. */
namespace Simple_Jwt_Auth\Notice;

/* Includes required classes and libraries. */
use stdClass;

/**
 * This class is responsible for managing all plugin-related notices,
 * such as error and success notifications. It provides a method to 
 * retrieve all notices or a specific notice based on a given key.
 * configuration data.
 * 
 * @link       https://github.com/sayandey18
 * @since      1.0.0
 * 
 * @package    Simple_Jwt_Auth
 * @subpackage Simple_Jwt_Auth\Notice
 * @author     Sayan Dey <mr.sayandey18@outlook.com>
 */
class JWTNotice {
    /**
     * Retrieves a list of plugin notices.
     * 
     * This method returns an object containing various plugin notices
     * such as error, success, and other notices related to the plugin's
     * admin settings and API responses.
     * 
     * @since   1.0.0
     * @return  stdClass Returns an object containing plugin notices.
     */
    public static function get_notices() {
        $notices = new stdClass();

        // Notices for the plugin admin panel.
		$notices->error               = __( 'Settings save failed!', 'simple-jwt-auth' );
		$notices->success             = __( 'Settings saved successfully', 'simple-jwt-auth' );
		$notices->unknown_error       = __( 'Something went wrong, try again', 'simple-jwt-auth' );
		$notices->unsupported_algo    = __( 'Unsupported algorithm', 'simple-jwt-auth' );
		$notices->empty_secret_key	  = __( 'Secret key is missing', 'simple-jwt-auth' );
		$notices->empty_public_key	  = __( 'Public key is missing', 'simple-jwt-auth' );
		$notices->empty_private_key   = __( 'Private key is missing', 'simple-jwt-auth' );
		$notices->invalid_private_key = __( 'Invalid private key format', 'simple-jwt-auth' );
		$notices->invalid_public_key  = __( 'Invalid public key format', 'simple-jwt-auth' );

        // Notices for the API responses.
        $notices->auth_credential       = __( 'Token created successfully', 'simple-jwt-auth' );
        $notices->valid_token           = __( 'Token is valid', 'simple-jwt-auth' );
        $notices->bad_request           = __( 'User ID not found in the token', 'simple-jwt-auth' );
        $notices->bad_issuer            = __( 'The issuer does not match with this server', 'simple-jwt-auth' );
        $notices->bad_config            = __( 'JWT is not configured properly, please contact the admin', 'simple-jwt-auth' );
        $notices->bad_auth_header       = __( 'Authorization header malformed', 'simple-jwt-auth' );
        $notices->no_auth_header        = __( 'Authorization header not found', 'simple-jwt-auth' );
        $notices->bad_secret_key        = __( 'JWT secret key not configured, please contact the admin', 'simple-jwt-auth' );
        $notices->bad_private_key       = __( 'JWT private key not configured, please contact the admin', 'simple-jwt-auth' );
        $notices->bad_signing_key       = __( 'JWT signing key not configured, please contact the admin', 'simple-jwt-auth' );
        $notices->unsupported_algorithm = __( 'Algorithm not supported, see https://tools.ietf.org/html/draft-ietf-jose-json-web-algorithms-40', 'simple-jwt-auth' );

		return $notices;
    }

    /**
     * Retrieves a specific plugin notice by key.
     * 
     * This method looks up a notice by its key and returns it. If the key
     * does not exist, it returns a default notice as string.
     * 
     * @since   1.0.0
     * @param   string $key The key of the message to retrieve.
     * @return  string Returns the message associated with the key.
     */
    public static function get_notice( string $key ) {
        // Get the all notices from above function.
        $notices = self::get_notices();

        // Returns the message associated with the key.
        return isset($notices->$key) ? $notices->$key : __( 'Something magical is happening', 'simple-jwt-auth' );
    }
}
