<?php
/**
 * Settings
 *
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add settings
 *
 * @access      public
 * @since       1.0.0
 * @param       array $settings The existing EDD settings array
 * @return      array The modified EDD settings array
 */
function edd_slm_settings( $settings ) {
    $new_settings = array(
        array(
            'id'    => 'edd_slm_settings',
            'name'  => '<strong>' . __( 'EDD Software License Manager Settings', 'edd-slm' ) . '</strong>',
            'desc'  => __( 'Configure EDD Software License Manager Settings', 'edd-slm' ),
            'type'  => 'header',
        ),
        array(
            'id' => 'edd_slm_api_url',
            'name' => __( 'API URL', 'edd-slm' ),
            'desc' => __( 'Enter the url to the WordPress installation which hosts the Software License Manager plugin.', 'edd-slm' ),
            'type' => 'text',
            'std'  => get_bloginfo( 'url' )
        ),
        array(
            'id' => 'edd_slm_api_secret',
            'name' => __( 'API Secret', 'edd-slm' ),
            'desc' => __( 'Secret Key for License Creation', 'edd-slm' ),
            'type' => 'text',
            'std' => ''
        ),
        array(
            'id' => 'edd_slm_api_license_term',
            'name' => __( 'License Term', 'edd-slm' ),
            'desc' => __( 'How long the license is valid', 'edd-slm' ),
            'type' => 'select',
            'options' => array ( '0000-00-00' => __('Forever', 'edd-slm'),
                                  730         => __('Two Years', 'edd-slm'),
                                  365         => __('One Year', 'edd-slm'),
                                  180         => __('Six Months', 'edd-slm'),
                                   90         => __('90 Days', 'edd-slm'),
                                   60         => __('60 Days', 'edd-slm'),
                                   30         => __('30 Days', 'edd-slm'),
                           )
            									
        ),
        array(
            'id'   => 'edd_slm_debug',
            'name' => __( 'Enable Debug', 'edd-slm' ),
            'desc' => __( 'Check the box to create a log file for debug purposes.', 'edd-slm' ),
            'type' => 'checkbox',
        )
    );

    return array_merge( $settings, $new_settings );
}

add_filter( 'edd_settings_extensions', 'edd_slm_settings', 1 );

/*
 * API validation notice
 */
function edd_slm_api_validation_notice() {

    global $pagenow, $typenow;

    $api_validation = edd_slm_api_validation();

    if( 'download' == $typenow && !$api_validation ) {
        echo '
            <div class="error">
                <p>' . __('Software License Manager API credentials missing or invalid.', 'edd-slm') . '</p>
            </div>';
    }
}
add_action('admin_notices', 'edd_slm_api_validation_notice');

/*
 * API validation
 */
function edd_slm_api_validation() {

    $api_url = edd_get_option( 'edd_slm_api_url' );
    $api_secret = edd_get_option( 'edd_slm_api_secret' );

    if ( empty($api_url) || empty($api_secret) ) {

        return false;
    }

    return true;
}
