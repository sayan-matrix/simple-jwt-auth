<?php

/* The namespace to avoid class name collisions. */
namespace Simple_Jwt_Auth\OpenSSL;

/**
 * Handles encryption and decryption of data using AES-256-GCM.
 * 
 * This class depends on the availability of an encryption key, which should be defined
 * in the system as the `SIMPLE_JWT_AUTH_ENCRYPT_KEY` constant. If the key is not defined,
 * the class will return an error when attempting to encrypt or decrypt data.
 *
 * @link       https://github.com/sayandey18
 * @since      1.0.0
 * 
 * @package    Simple_Jwt_Auth
 * @subpackage Simple_Jwt_Auth\OpenSSL
 * @author     Sayan Dey <mr.sayandey18@outlook.com>
 */
class Crypto {
    public function __construct() {
        // Constructor code if needed
    }

    /**
	 * Encrypts the provided data using the AES-256-GCM algorithm.
	 * 
	 * @since	1.0.0
	 * 
	 * @param	string $decrypted 
	 * @return	string|WP_Error
	 */
	public static function encrypt( string $decrypted ) {
		$secret = defined( 'SIMPLE_JWT_AUTH_ENCRYPT_KEY' ) ? SIMPLE_JWT_AUTH_ENCRYPT_KEY : false;

		// Check the encryption key, if not exists return an error.
		if ( !$secret ) {
			return new WP_Error(
				'simplejwt_auth_bad_config',
				__( 'Encryption key is not configured properly.', 'simple-jwt-auth' ),
				['status' => 403]
			);
		}

		$cipher = 'aes-256-gcm';
		$iv_length = openssl_cipher_iv_length( $cipher );
		$iv_key = openssl_random_pseudo_bytes( $iv_length ); // Generate a secure IV.
		$tag = ''; // Will be filled after encryption.
		$option = 0;

		// Encrypt the data.
		$encrypted = openssl_encrypt( $decrypted, $cipher, $secret, $option, $iv_key, $tag );

		if ( $encrypted === false ) {
			return new WP_Error(
				'simplejwt_auth_encryption_failed',
				__( 'Encryption process failed, contact admin.', 'simple-jwt-auth' ),
				['status' => 500]
			);
		}

		// Return the encrypted data along with the IV and tag.
		return base64_encode( $iv_key . $tag . $encrypted );
	}

	/**
	 * Decrypts the provided encrypted data using the AES-256-GCM algorithm.
	 *
	 * @since	1.0.0
	 *
	 * @param	string $encrypted 
	 * @return	string|WP_Error
	 */
	public static function decrypt( string $encrypted ) {
		$secret = defined( 'SIMPLE_JWT_AUTH_ENCRYPT_KEY' ) ? SIMPLE_JWT_AUTH_ENCRYPT_KEY : false;

		// Check the encryption key, if not exists return an error.
		if ( !$secret ) {
			return new WP_Error(
				'simplejwt_auth_bad_config',
				__( 'Encryption key is not configured properly.', 'simple-jwt-auth' ),
				['status' => 403]
			);
		}

		$cipher = 'aes-256-gcm';
		$iv_length = openssl_cipher_iv_length( $cipher );
		$encrypted = base64_decode( $encrypted );
		$option = 0;

		// Extract the IV, tag, and ciphertext from the encrypted data.
		$iv_key = substr( $encrypted, 0, $iv_length );
		$tag = substr( $encrypted, $iv_length, 16 ); // GCM tag is always 16 bytes.
		$ciphertext = substr( $encrypted, $iv_length + 16 );

		// Decrypt the data.
		$decrypted = openssl_decrypt( $ciphertext, $cipher, $secret, $option, $iv_key, $tag );

		if ( $decrypted === false ) {
			return new WP_Error(
				'simplejwt_auth_decryption_failed',
				__( 'Decryption process failed, contact admin.', 'simple-jwt-auth' ),
				['status' => 500]
			);
		}
	
		// Return the decrypted data.
		return $decrypted;
	}
}
