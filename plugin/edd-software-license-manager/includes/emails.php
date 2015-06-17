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
    edd_add_email_tag( 'slm_license', 'Displays the generated license keys.', 'edd_slm_email_replace_license_tag' );
}
add_action( 'edd_add_email_tags', 'edd_slm_email_add_license_tag' );

/**
 * Replace license tag
 * Source: http://docs.easydigitaldownloads.com/article/497-edd-add-email-tag
 *
 * @since 1.0.0
 * @return void
 */
function edd_slm_email_replace_license_tag() {
    return 'DEV TEST';
}