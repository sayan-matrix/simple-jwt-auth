<?php

/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://github.com/sayandey18
 * @since      1.0.0
 *
 * @package    Simple_Jwt_Auth
 * @subpackage Simple_Jwt_Auth/admin/partials
 */
?>

<!-- This file should primarily consist of HTML with a little bit of PHP. -->

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
        // Generate and display the admin notice.
        $notice = $this->simplejwt_admin_notices();
        if ( !empty( $notice ) ) {
            // Escape output to prevent XSS
            echo wp_kses_post( $notice );
        } ?>

        <div class="simplejwt-container-items">
            <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post" id="simplejwt_options">
                <div class="simplejwt-site-control">
                    <?php $simplejwt_nonce = wp_create_nonce( 'simplejwt_nonce' ); ?>
                    <input type="hidden" name="action" value="simplejwt_options_action">
                    <input type="hidden" name="simplejwt_nonce" value="<?php echo esc_attr( $simplejwt_nonce ); ?>" />
                    <div class="simplejwt-item-card">
                        <h2><?php esc_html_e( 'Deleting Data', 'simple-jwt-auth' ); ?></h2>
                        <div class="simplejwt-card-header">
                            <div class="simplejwt-stack-heading">
                                <h3><?php esc_html_e( 'Remove configs data', 'simple-jwt-auth' ); ?></h3>
                                <p class="simplejwt-body-desc simplejwt-mt-10">
                                    <?php esc_html_e( 'Checking this box will permanently delete all JWT authentication 
                                    tables and data while uninstalling, which is irreversible 
                                    and cannot be recovered.', 'simple-jwt-auth' ); ?>
                                </p>
                            </div>
                            <div class="simplejwt-action-area">
                                <div class="simplejwt-checkbox-wrapper">
                                    <input type="checkbox" class="simplejwt-checkbox-btn" id="simplejwt_enable_cors"
                                        name="simplejwt_enable_cors" <?php checked( isset( $config[ 'enable_cors' ] ) && $config[ 'enable_cors' ] == '1', true ); ?> />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="simplejwt-site-update simplejwt-mt-15">
                    <button id="simplejwt_submit_btn" class="simplejwt-submit-btn" type="submit">
                        <?php esc_html_e( 'Save Changes', 'simple-jwt-auth' ); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>