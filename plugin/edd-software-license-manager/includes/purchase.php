<?php
/**
 * Purchase
 *
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * On purchase complete
 *
 * @since 1.0.0
 * @return void
 */
function edd_slm_on_complete_purchase( $payment_id ) {

    edd_slm_create_license_keys( $payment_id );

    //echo '<pre>';
    //print_r($payment_meta);
    //echo '</pre>';


}
add_action( 'edd_complete_purchase', 'edd_slm_on_complete_purchase' );

/**
 * Create license key
 *
 * @since 1.0.0
 * @return void
 */
function edd_slm_create_license_keys( $payment_id ) {

    // Payment meta
    $payment_meta = edd_get_payment_meta( $payment_id );

    // Transaction id
    $transaction_id = edd_get_payment_transaction_id( $payment_id );

    foreach ($payment_meta['downloads'] as $download) {

        // Download data
        $download_data = edd_get_download($download['id']);

        if ( $download_data ) {

            // Build parameters
            $api_params = array();
            $api_params['slm_action'] = 'slm_create_new';
            $api_params['secret_key'] = EDD_SLM_API_SECRET;
            $api_params['first_name'] = (isset($payment_meta['user_info']['first_name'])) ? $payment_meta['user_info']['first_name'] : '';
            $api_params['last_name'] = (isset($payment_meta['user_info']['last_name'])) ? $payment_meta['user_info']['last_name'] : '';
            $api_params['email'] = (isset($payment_meta['user_info']['email'])) ? $payment_meta['user_info']['email'] : '';
            $api_params['company_name'] = 'Company GmbH';
            $api_params['txn_id'] = $transaction_id . ' - ' . $download_data->post_title;
            $api_params['max_allowed_domains'] = intval('5');
            $api_params['date_created'] = date('Y-m-d');
            $api_params['date_expiry'] = '0000-00-00';

            // Send query to the license manager server
            $url = EDD_SLM_API_URL . '?' . http_build_query($api_params);

            $response = wp_remote_get($url, array('timeout' => 20, 'sslverify' => false));

            // Payment note
            edd_slm_payment_note($response, $payment_id, $download_data->post_title);
        }
    }
}

/**
 * Leave payment not for license creation
 *
 * @since 1.0.0
 * @return void
 */
function edd_slm_payment_note( $response, $payment_id , $title) {

    // Check for error in the response
    if (is_wp_error($response)){
        $error = __('License Key could not be created.', 'edd-slm');
    }

    // Get License data
    $license_data = json_decode(wp_remote_retrieve_body($response));

    // Prepare note text
    $note = (isset($error)) ? $error : __('License Key generated:', 'edd-slm') . ' ' . $title . ' - ' . $license_data->key;

    // Save note
    $int = edd_insert_payment_note( $payment_id, $note );
}