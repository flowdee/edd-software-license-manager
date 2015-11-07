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

/*
 * Debug logging
 */
function edd_slm_add_log( $log_string, $date = false ) {

    $debug = edd_get_option( 'edd_slm_debug' );

    if ( $debug == 1 ) {

        $folder = EDD_SLM_DIR . 'log';
        $file = EDD_SLM_DIR . 'log/edd_slm_debug.log';

        if ($date) {
            $log_string = "Log Date: " . date("r") . "\n" . $log_string . "\n";
        } else {
            $log_string .= "\n";
        }

        if (file_exists($file)) {
            if ($log = fopen($file, "a")) {
                fwrite($log, $log_string, strlen($log_string));
                fclose($log);
            }
        } else {

            if (!file_exists($folder)) {
                mkdir($folder, 0755, true);
            }

            if ($log = fopen($file, "c")) {
                fwrite($log, $log_string, strlen($log_string));
                fclose($log);
            }
        }
    }
}