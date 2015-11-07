<?php
/**
 * E-Mails
 *
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Add license tag to email templates
 * Source: http://docs.easydigitaldownloads.com/article/497-edd-add-email-tag
 *
 * @since 1.0.0
 * @return void
 */

function edd_slm_email_add_license_tag( $payment_id ) {
    edd_add_email_tag( 'slm_license_keys', 'Displays the generated license keys.', 'edd_slm_email_replace_license_tag' );
}
add_action( 'edd_add_email_tags', 'edd_slm_email_add_license_tag' );

/**
 * Replace license tag
 * Source: http://docs.easydigitaldownloads.com/article/497-edd-add-email-tag
 *
 * @since 1.0.0
 * @return mixed
 */
function edd_slm_email_replace_license_tag( $payment_id ) {

    $output = '';

    // Check if licenses were generated
    $licenses = get_post_meta( $payment_id, '_edd_slm_payment_licenses', true );

    if ( $licenses && count($licenses) != 0 ) {
        foreach ( $licenses as $license) {

            if ( isset( $license['item'] ) && isset( $license['key'] ) ) {

                if ( $output != '' ) {
                    $output .= '<br />';
                }

                $output .= $license['item'] . ': ' . $license['key'];
            }
        }
    } else {
        $output = __('Please get in contact to receive the license keys.', 'edd-slm');
    }

    return $output;
}