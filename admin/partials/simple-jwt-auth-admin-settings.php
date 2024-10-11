<?php

/* Prevent direct access to this file. */
if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/* Require Crypto and DBManager class. */
use Simple_Jwt_Auth\OpenSSL\Crypto;
use Simple_Jwt_Auth\Database\DBManager;

/**
 * Provide a admin area view for the plugin.
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://github.com/sayandey18
 * @since      1.0.0
 *
 * @package    Simple_Jwt_Auth
 * @subpackage Simple_Jwt_Auth/admin/partials
 */

/**
 * Decrypt the value and handle the error result.
 * 
 * @link    https://github.com/sayandey18
 * @since   1.0.0
 */

$keys = ['secret_key', 'private_key', 'public_key'];

// Decrypted key values will be stored in this object.
$decrypted = new stdClass();

// Loop through $config key and decrypt only the specified keys.
foreach ( $keys as $key ) {
    if ( !empty( $config[$key] ) ) {
        // Decrypt the $config key.
        $decrypted_value = Crypto::decrypt( $config[$key] );

        // If decryption fails, set the error message and flag, else assign the decrypted value.
        $status = is_wp_error( $decrypted_value ) ? true : false;
        $message = is_wp_error( $decrypted_value ) ? $decrypted_value->get_error_message() : '';
        $decrypted->$key = is_wp_error( $decrypted_value ) ? null : $decrypted_value;
    }
}
?>

<div class="simplejwt-navbar">
    <div class="simplejwt-navbar-wrapper">
        <div class="simplejwt-menu-area">
            <ul class="simplejwt-menu-wrapper">
                <li class="simplejwt-menu-items">
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=simple-jwt-auth' ) ); ?>">
                        <?php esc_html_e( 'JWT Settings', 'simple-jwt-auth' ); ?>
                    </a>
                </li>
                <li class="simplejwt-menu-items">
                    <a href="<?php echo esc_url( admin_url( 'admin.php?page=simple-jwt-auth-options' ) ); ?>">
                        <?php esc_html_e( 'Options', 'simple-jwt-auth' ); ?>
                    </a>
                </li>
            </ul>
        </div>
        <div class="simplejwt-logo-area">
            <img width="144px" height="36px" src="<?php echo esc_attr( plugin_dir_url( __FILE__ ) . '../img/jwt-auth.svg' ); ?>" alt="jwt" />
        </div>
    </div>
</div>

<div class="simplejwt-section">
    <div class="simplejwt-container">
        <?php 
        /**
         * Trigger the display of custom admin notices.
         * 
         * @since   1.0.0
         */
        do_action( 'simplejwt_admin_alert', $status ?? false, $message ?? null ); ?>

        <div class="simplejwt-container-items">
            <div class="simplejwt-site-info">
                <div class="simplejwt-item-card">
                    <div class="simplejwt-card-header simplejwt-mb-15">
                        <div class="simplejwt-stack-heading simplejwt-flex-center">
                            <img width="30px" height="30px" src="<?php echo esc_attr( plugin_dir_url( __FILE__ ) . '../img/wordpress.svg' ); ?>" alt="wordpress" />
                            <h3><?php esc_html_e( 'WordPress Version', 'simple-jwt-auth' ); ?></h3>
                        </div>
                        <div class="simplejwt-stack-version">
                            <?php echo esc_html( $versions_info['wp_version'] ); ?>
                        </div>
                    </div>
                    <div class="simplejwt-card-body">
                        <p class="simplejwt-body-desc"><?php echo esc_html( $versions_info['wp_body_message'] ); ?></p>

                        <?php if ( !empty( $versions_info['wp_update_message'] ) ) : ?>
                            <p class="simplejwt-update-notice"><?php echo esc_html( $versions_info['wp_update_message'] ); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="simplejwt-item-card">
                    <div class="simplejwt-card-header simplejwt-mb-15">
                        <div class="simplejwt-stack-heading simplejwt-flex-center">
                            <img width="30px" height="30px" src="<?php echo esc_attr( plugin_dir_url( __FILE__ ) . '../img/php.svg' ); ?>" alt="php" />
                            <h3><?php esc_html_e( 'PHP Version', 'simple-jwt-auth' ) ?></h3>
                        </div>
                        <div class="simplejwt-stack-version">
                            <?php echo esc_html( $versions_info['php_version'] ); ?>
                        </div>
                    </div>
                    <div class="simplejwt-card-body">
                        <p class="simplejwt-body-desc"><?php echo esc_html( $versions_info['php_body_message'] ); ?></p>
                        
                        <?php if ( !empty( $versions_info['php_update_message'] ) ) : ?>
                            <p class="simplejwt-update-notice"><?php echo esc_html( $versions_info['php_update_message'] ); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" id="simplejwt_settings">
                <div class="simplejwt-site-control">
                    <?php $simplejwt_nonce = wp_create_nonce( 'simplejwt_nonce' ); ?>
                    <input type="hidden" name="action" value="simplejwt_settings_action">
                    <input type="hidden" name="simplejwt_nonce" value="<?php echo esc_attr( $simplejwt_nonce ); ?>" />
                    <div class="simplejwt-item-card">
                        <h2><?php esc_html_e( 'Authencation', 'simple-jwt-auth' ); ?></h2>
                        <div class="simplejwt-card-header simplejwt-mb-15">
                            <div class="simplejwt-stack-heading">
                                <h3><?php esc_html_e( 'Enable JWT', 'simple-jwt-auth' ); ?></h3>
                                <p class="simplejwt-body-desc simplejwt-mt-10"><?php esc_html_e( 'Secure and reliable way to verify user identity and access control.', 'simple-jwt-auth' ); ?></p>
                            </div>
                            <div class="simplejwt-action-area">
                                <div class="simplejwt-checkbox-wrapper">
                                    <input type="checkbox" class="simplejwt-checkbox-btn" id="simplejwt_enable_auth" name="simplejwt_enable_auth" <?php checked( isset( $config[ 'enable_auth' ] ) && $config[ 'enable_auth' ] == '1', true ); ?> />
                                </div>
                            </div>
                        </div>
                        <div class="simplejwt-card-body">
                            <div class="simplejwt-key-area">
                                <div class="simplejwt-key-wrapper" 
                                    style="<?php echo esc_attr( isset( $config[ 'enable_auth' ] ) && $config[ 'enable_auth' ] == '0' ? 'display: none;' : '' ); ?>">
                                    <hr />
                                    <h3 class="simplejwt-mt-15"><?php esc_html_e( 'Choose Algorithm', 'simple-jwt-auth' ); ?></h3>

                                    <?php
                                    $default_algo = isset( $config[ 'algorithm' ] ) ? $config[ 'algorithm' ] : 'HS256';
                                    $algorithms = isset( $config[ 'supported_algo' ] ) ? maybe_unserialize( $config[ 'supported_algo' ] ) : []; ?>
                                    
                                    <select class="simplejwt-select-field" name="simplejwt_algorithm" id="simplejwt_algorithm">
                                        <?php
                                        foreach ( $algorithms as $algorithm ) : ?>
                                            <option <?php echo esc_attr( $default_algo === $algorithm ? 'selected' : '' ); ?> value="<?php echo esc_attr( $algorithm ); ?>"><?php echo esc_html( $algorithm ); ?></option>
                                        <?php endforeach; ?>
                                    </select>

                                    <?php $symmetric_algo = isset( $config['algorithm'] ) && in_array( $config['algorithm'], ['HS256', 'HS384', 'HS512'], true ) ? true : false; ?>
                                    <div class="simplejwt-signature-area HS256" style="<?php echo esc_attr( $symmetric_algo ? '' : 'display: none;' ); ?>">
                                        <label class="simplejwt-input-label" for="simplejwt_secret_key"><?php esc_html_e( 'Enter your secret key', 'simple-jwt-auth' ); ?></label>
                                        <input type="text" class="simplejwt-secretkey-input" name="simplejwt_secret_key" id="simplejwt_secret_key" placeholder="xxxxxxxxxxxxxx-xxxxxxxxxxxxxx-xxxxxxxxxxxxxx" <?php echo esc_attr( $symmetric_algo ? 'required' : '' ); ?> value="<?php echo esc_attr( $decrypted->secret_key ?? '' ); ?>" />
                                    </div>

                                    <div class="simplejwt-signature-area RS256" style="<?php echo esc_attr( $symmetric_algo ? 'display: none;' : '' ); ?>">
                                        <textarea class="simplejwt-keyfile-input" name="simplejwt_private_key" id="simplejwt_private_key" rows="8" placeholder="-----BEGIN PRIVATE KEY-----" <?php echo esc_attr( $symmetric_algo ? '' : 'required' ); ?>><?php echo esc_attr( $decrypted->private_key ?? '' ); ?></textarea>
                                        <textarea class="simplejwt-keyfile-input" name="simplejwt_public_key" id="simplejwt_public_key" rows="6" placeholder="-----BEGIN PUBLIC KEY-----" <?php echo esc_attr( $symmetric_algo ? '' : 'required' ); ?>><?php echo esc_attr( $decrypted->public_key ?? '' ); ?></textarea>
                                    </div>

                                    <span class="simplejwt-notes">
                                        <?php esc_attr_e( '*Generate a secure JSON Web Token signing keys using OpenSSL from here', 'simple-jwt-auth' ); ?>
                                        <a href="https://jwt-keys.21no.de" target="_blank" rel="nofollow noopener noreferrer">https://jwt-keys.21no.de</a>
                                    </span>
                                </div>
                            </div>

                            <div class="simplejwt-endpoint-area">
                                <hr class="simplejwt-mt-20" />
                                <h3 class="simplejwt-mt-15"><?php esc_html_e('Endpoints', 'simple-jwt-auth'); ?></h3>
                                <p class="simplejwt-body-desc simplejwt-mt-10"><?php esc_html_e( 'List of endpoint URLs that can be used to interact with the API, allowing you to perform various actions and retrieve data, enabling you to access and utilize the API\'s functionalities.', 'simple-jwt-auth' ); ?></p>
                                <div class="simplejwt-relative simplejwt-mt-20">
                                    <label class="simplejwt-input-label" for="simplejwt_generate_token"><?php esc_html_e( 'Generate JWT token', 'simple-jwt-auth' ); ?></label>
                                    <input type="text" class="simplejwt-endpoint-data" id="simplejwt_generate_token" value="<?php echo esc_url( $this->simplejwt_public_endpoints( 'token' ) ); ?>" readonly disabled />
                                    <span class="simplejwt-copy-btn" data-tooltip="Copied"></span>
                                </div>
                                <div class="simplejwt-relative simplejwt-mt-20">
                                    <label class="simplejwt-input-label" for="simplejwt_generate_token"><?php esc_attr_e( 'Validate JWT token', 'simple-jwt-auth' ); ?></label>
                                    <input type="text" class="simplejwt-endpoint-data" id="simplejwt_validate_token" value="<?php echo esc_url( $this->simplejwt_public_endpoints( 'token/validate' ) ); ?>" readonly disabled />
                                    <span class="simplejwt-copy-btn" data-tooltip="Copied"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="simplejwt-item-card">
                        <h2><?php esc_html_e( 'CORS Support', 'simple-jwt-auth' ); ?></h2>
                        <div class="simplejwt-card-header">
                            <div class="simplejwt-stack-heading">
                                <h3><?php esc_html_e( 'Enable CORS', 'simple-jwt-auth' ); ?></h3>
                                <p class="simplejwt-body-desc simplejwt-mt-10"><?php esc_html_e( 'CORS is an HTTP-header based mechanism that allows a web page to make requests to a server on a different domain than the one that served the web page.', 'simple-jwt-auth' ); ?></p>
                            </div>
                            <div class="simplejwt-action-area">
                                <div class="simplejwt-checkbox-wrapper">
                                    <input type="checkbox" class="simplejwt-checkbox-btn" id="simplejwt_enable_cors" name="simplejwt_enable_cors" <?php checked( isset( $config[ 'enable_cors' ] ) && $config[ 'enable_cors' ] == '1', true ); ?> />
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="simplejwt-item-card">
                        <h2><?php esc_html_e( 'Security', 'simple-jwt-auth' ); ?></h2>
                        <div class="simplejwt-card-header">
                            <div class="simplejwt-stack-heading">
                                <h3><?php esc_html_e( 'Disable XML-RPC', 'simple-jwt-auth' ); ?></h3>
                                <p class="simplejwt-body-desc simplejwt-mt-10"><?php esc_html_e( 'XML-RPC allows apps to connect to your WordPress site, but might expose your site\'s security. Disable this feature if you don\'t need it.', 'simple-jwt-auth' ); ?></p>
                            </div>
                            <div class="simplejwt-action-area">
                                <div class="simplejwt-checkbox-wrapper">
                                    <input type="checkbox" class="simplejwt-checkbox-btn" id="simplejwt_disable_xmlrpc" name="simplejwt_disable_xmlrpc" <?php checked( isset( $config[ 'disable_xmlrpc' ] ) && $config[ 'disable_xmlrpc' ] == '1', true ); ?> />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="simplejwt-site-update simplejwt-mt-15">
                    <button id="simplejwt_submit_btn" class="simplejwt-submit-btn" type="submit"><?php esc_html_e( 'Save Changes', 'simple-jwt-auth' ); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
