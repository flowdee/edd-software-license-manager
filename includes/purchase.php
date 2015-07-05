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

    if ( EDD_SLM_API_URL != '' && EDD_SLM_API_SECRET != '' ) {
        edd_slm_create_license_keys( $payment_id );
    }
}
add_action( 'edd_complete_purchase', 'edd_slm_on_complete_purchase' );

/**
 * Create license key
 *
 * @since 1.0.0
 * @return void
 */
function edd_slm_create_license_keys( $payment_id ) {

    // Collect license keys
    $licenses = array();

    // Payment meta
    $payment_meta = edd_get_payment_meta( $payment_id );

    //edd_slm_print_pretty($payment_meta);

    foreach ($payment_meta['cart_details'] as $item) {

        $download_id = $item['id'];

        if ( edd_slm_is_licensing_enabled( $download_id ) ) {

            // Download data
            $download_data = edd_get_download( $download_id );;

            if ( $download_data ) {

                $download_quantity = absint($item['quantity']);

                for ($i = 1; $i <= $download_quantity; $i++) {

                    // Get price id
                    $price_id = edd_get_cart_item_price_id( $item );
                    $price_name = edd_get_cart_item_price_name( $item );

                    // Sites allowed
                    $sites_allowed = edd_slm_get_sites_allowed( $price_id, $payment_id, $download_id );

                    if ( !$sites_allowed ) {
                        $sites_allowed_error = __('License could not be created: Invalid sites allowed number.', 'edd-slm');

                        $int = edd_insert_payment_note( $payment_id, $sites_allowed_error );
                        break;
                    }

                    // Transaction id
                    $transaction_id = edd_get_payment_transaction_id( $payment_id );

                    // Build item name
                    $item_name = ( !empty( $price_name ) ) ? $item['name'] . ' - ' . $price_name : $item['name'];

                    // Build parameters
                    $api_params = array();
                    $api_params['slm_action'] = 'slm_create_new';
                    $api_params['secret_key'] = EDD_SLM_API_SECRET;
                    $api_params['first_name'] = (isset($payment_meta['user_info']['first_name'])) ? $payment_meta['user_info']['first_name'] : '';
                    $api_params['last_name'] = (isset($payment_meta['user_info']['last_name'])) ? $payment_meta['user_info']['last_name'] : '';
                    $api_params['email'] = (isset($payment_meta['user_info']['email'])) ? $payment_meta['user_info']['email'] : '';
                    $api_params['company_name'] = 'Company GmbH';
                    $api_params['txn_id'] = $transaction_id . ' - ' . $item_name;
                    $api_params['max_allowed_domains'] = $sites_allowed;
                    $api_params['date_created'] = date('Y-m-d');
                    $api_params['date_expiry'] = '0000-00-00';

                    // Send query to the license manager server
                    $url = EDD_SLM_API_URL . '?' . http_build_query($api_params);

                    $response = wp_remote_get($url, array('timeout' => 20, 'sslverify' => false));

                    // Get license key
                    $license_key = edd_slm_get_license_key( $response );

                    // Collect license keys
                    if ( $license_key ) {
                        $licenses[] = array(
                            'item' => $item_name,
                            'key' => $license_key
                        );
                    }
                }
            }

        }
    }

    // Payment note
    edd_slm_payment_note( $payment_id, $licenses );

    // Assign licenses
    edd_slm_assign_licenses( $payment_id, $licenses );
}

/**
 * Get generated license key
 *
 * @since 1.0.0
 * @return mixed
 */
function edd_slm_get_license_key( $response ) {

    // Check for error in the response
    if (is_wp_error($response)){
        return false;
    }

    // Get License data
    $license_data = json_decode(wp_remote_retrieve_body($response));

    if ( !isset($license_data->key) ) {
        return false;
    }

    // Prepare note text
    return $license_data->key;
}

/**
 * Leave payment not for license creation
 *
 * @since 1.0.0
 * @return void
 */
function edd_slm_payment_note( $payment_id, $licenses ) {

    if ( $licenses && count($licenses) != 0 ) {
        $message = __('License Key(s) generated', 'edd-slm');

        foreach ( $licenses as $license ) {

            $message .= '<br />' . $license['item'] . ': ' . $license['key'];
        }
    } else {
        $message = __('License Key(s) could not be created.', 'edd-slm');
    }

    // Save note
    $int = edd_insert_payment_note( $payment_id, $message );
}

/**
 * Assign generated license keys to payments
 *
 * @since 1.0.0
 * @return void
 */
function edd_slm_assign_licenses( $payment_id , $licenses ) {

    if ( count($licenses) != 0 ) {
        update_post_meta( $payment_id, '_edd_slm_payment_licenses', $licenses );
    }
}

/**
 * Get sites allowed from download.
 *
 * @since  1.0.0
 * @return mixed
 */
function edd_slm_get_sites_allowed( $price_id = 0, $payment_id = 0, $download_id = 0 ) {

    if ( edd_has_variable_prices( $download_id ) ) {
        $edd_slm_sites_allowed = edd_slm_get_variable_price_sites_allowed( $download_id, $price_id );
    } else {
        $edd_slm_sites_allowed = absint( get_post_meta( $download_id, '_edd_slm_sites_allowed', true ) );
    }

    if( empty( $edd_slm_sites_allowed ) ) {
        return false;
    }

    return $edd_slm_sites_allowed;
}

/**
 * Get licensing data for variable prices.
 *
 * @since  1.0.0
 * @return string
 */
function edd_slm_get_variable_price_sites_allowed( $download_id = 0, $price_id = null ) {

    $prices = edd_get_variable_prices( $download_id );

    //edd_slm_print_pretty($prices);

    if ( isset( $prices[ $price_id ][ 'edd_slm_sites_allowed' ] ) ) {
        return absint( $prices[ $price_id ][ 'edd_slm_sites_allowed' ] );
    }

    return false;
}

/**
 * Check if licensing for a certain download is enabled
 *
 * @since  1.0.0
 * @return bool
 */
function edd_slm_is_licensing_enabled( $download_id ) {

    $licensing_enabled = absint( get_post_meta( $download_id, '_edd_slm_licensing_enabled', true ) );

    // Set defaults
    if( $licensing_enabled ) {
        return true;
    } else {
        return false;
    }
}