<?php
/*
 * This file belongs to the SMM Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! class_exists( 'SMM_Upgrade' ) ) {
	/**
	 * SMM Upgrade
	 *
	 * Notify and Update plugin
	 *
	 * @class       SMM_Upgrade
	 * @package     SMMS
	 * @since       1.0
	 * @author      Softnwords Themes
	 * @see         WP_Updater Class
	 */
	class SMM_Upgrade {
		/**
		 * @var SMM_Upgrade The main instance
		 */
		protected static $_instance;

		/**
		 * Construct
		 *
		 * @author sam softnwords
		 * @since  1.0
		 */
		public function __construct() {
			//Silence is golden...
		}

		/**
		 * Main plugin Instance
		 *
		 * @param $plugin_slug | string The plugin slug
		 * @param $plugin_init | string The plugin init file
		 *
		 * @return void
		 *
		 * @since  1.0
		 * @author sam softnwords
		 */
		public function register( $plugin_slug, $plugin_init ) {
			if( ! function_exists( 'SMMS_Plugin_Upgrade' ) ){
				//Try to load SMMS_Plugin_Upgrade class
				smms_plugin_fw_load_update_and_licence_files();
			}

            try {
                SMMS_Plugin_Upgrade()->register( $plugin_slug, $plugin_init );
            } catch( Error $e ){
            }
		}

		/**
		 * Main plugin Instance
		 *
		 * @static
		 * @return object Main instance
		 *
		 * @since  1.0
		 * @author sam softnwords
		 */
		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}
	}
}

if ( ! function_exists( 'SMM_Upgrade' ) ) {
	/**
	 * Main instance of plugin
	 *
	 * @return SMM_Upgrade
	 * @since  1.0
	 * @author sam softnwords
	 */
	function SMM_Upgrade() {
		return SMM_Upgrade::instance();
	}
}

/**
 * Instance a SMM_Upgrade object
 */
SMM_Upgrade();
