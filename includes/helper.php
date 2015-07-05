<?php
/**
 * Helper
 *
 * @since       1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

function edd_slm_print_pretty( $args ) {
    echo '<pre>';
    print_r($args);
    echo '</pre>';
}