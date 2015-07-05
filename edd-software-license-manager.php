<?php
/**
 * Plugin Name:     EDD Software License Manager
 * Plugin URI:      http://flowdee.de
 * Description:     EDD Software License Manager
 * Version:         1.0.0
 * Author:          flowdee
 * Author URI:      http://flowdee.de
 * Text Domain:     edd-slm
 * Domain Path:     /languages
 *
 * This program is free software; you can redistribute it and/or modify it under the terms of the GNU
 * General Public License version 3, as published by the Free Software Foundation. You may NOT assume
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without
 * even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @author          flowdee <coder@flowdee.de>
 * @copyright       Copyright (c) flowdee
 * @license         http://www.gnu.org/licenses/gpl-3.0.html
 */

// Exit if accessed directly
if( !defined( 'ABSPATH' ) ) {
    exit;
}

if( !class_exists( 'EDD_SLM' ) ) {

    /**
     * Main EDD_SLM class
     *
     * @since       1.0.0
     */
    class EDD_SLM {

        /**
         * @var         EDD_SLM $instance The one true EDD_SLM
         * @since       1.0.0
         */
        private static $instance;


        /**
         * Get active instance
         *
         * @access      public
         * @since       1.0.0
         * @return      object self::$instance The one true EDD_SLM
         */
        public static function instance() {
            if( !self::$instance ) {
                self::$instance = new EDD_SLM();
                self::$instance->setup_constants();
                self::$instance->includes();
                self::$instance->load_textdomain();
            }

            return self::$instance;
        }


        /**
         * Setup plugin constants
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function setup_constants() {

            // Plugin version
            define( 'EDD_SLM_VER', '1.0.0' );

            // Plugin path
            define( 'EDD_SLM_DIR', plugin_dir_path( __FILE__ ) );

            // Plugin URL
            define( 'EDD_SLM_URL', plugin_dir_url( __FILE__ ) );

            // SLM Credentials
            $api_url = rtrim(edd_get_option( 'edd_slm_api_url' ), '/');

            define( 'EDD_SLM_API_URL', $api_url );
            define( 'EDD_SLM_API_SECRET', edd_get_option( 'edd_slm_api_secret' ) );
        }


        /**
         * Include necessary files
         *
         * @access      private
         * @since       1.0.0
         * @return      void
         */
        private function includes() {

            // Get out if EDD is not active
            if( ! function_exists( 'EDD' ) ) {
                return;
            }

            // Include files and scripts
            require_once EDD_SLM_DIR . 'includes/helper.php';

            if ( is_admin() ) {
                require_once EDD_SLM_DIR . 'includes/meta-boxes.php';
                require_once EDD_SLM_DIR . 'includes/settings.php';
            }

            require_once EDD_SLM_DIR . 'includes/emails.php';
            require_once EDD_SLM_DIR . 'includes/purchase.php';
        }

        /**
         * Internationalization
         *
         * @access      public
         * @since       1.0.0
         * @return      void
         */
        public function load_textdomain() {

            // Load the default language files
            load_plugin_textdomain( 'edd-slm', false, 'edd-software-license-manager/languages' );
        }
        
        /*
         * Activation function fires when the plugin is activated.
         *
         * @since  1.0.0
         * @access public
         * @return void
         */
        public static function activation() {
            // nothing
        }

        /*
         * Uninstall function fires when the plugin is being uninstalled.
         *
         * @since  1.0.0
         * @access public
         * @return void
         */
        public static function uninstall() {
            // nothing
        }
    }

    /**
     * The main function responsible for returning the one true EDD_SLM
     * instance to functions everywhere
     *
     * @since       1.0.0
     * @return      \EDD_SLM The one true EDD_SLM
     */
    function EDD_SLM_load() {

        return EDD_SLM::instance();
    }

    /**
     * The activation & uninstall hooks are called outside of the singleton because WordPress doesn't
     * register the call from within the class hence, needs to be called outside and the
     * function also needs to be static.
     */
    register_activation_hook( __FILE__, array( 'EDD_SLM', 'activation' ) );
    register_uninstall_hook( __FILE__, array( 'EDD_SLM', 'uninstall') );

    add_action( 'plugins_loaded', 'EDD_SLM_load' );

} // End if class_exists check