<?php

if ( ! defined( 'ABSPATH' ) || ! defined( 'SMMS_SMAPI_VERSION' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Implements SMAPI_Subscription_Helper Class
 *
 * @class   SMAPI_Subscription_Helper
 * @package SMMS WooCommerce Subscription
 * @since   1.0.0
 * @author  SMMS
 */
if ( ! class_exists( 'SMAPI_Subscription_Helper' ) ) {

	/**
	 * Class SMAPI_Subscription_Helper
	 */
	class SMAPI_Subscription_Helper {

		/**
		 * Single instance of the class
		 *
		 * @var \SMAPI_Subscription_Helper
		 */

		protected static $instance;


		/**
		 * Returns single instance of the class
		 *
		 * @access public
		 *
		 * @return \SMAPI_Subscription_Helper
		 * @since  1.0.0
		 */

		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}


		/**
		 * Constructor
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @since  1.0.0
		 * @author sam
		 */

		public function __construct() {

			add_action( 'init', array( $this, 'register_subscription_post_type' ) );


		}


		/**
		 * Register smapi_subscription post type
		 *
		 *
		 * @since  1.0.0
		 * @author sam
		 */

		public function register_subscription_post_type() {

			$supports = false;

			if ( apply_filters( 'smapi_test_on', SMMS_SMAPI_TEST_ON ) ){
				$supports = array( 'custom-fields' );
			}

			$labels = array(
				'name'               => _x( 'Subscriptions', 'Post Type General Name', 'smm-api' ),
				'singular_name'      => _x( 'Subscription', 'Post Type Singular Name', 'smm-api' ),
				'menu_name'          => __( 'Subscription', 'smm-api' ),
				'parent_item_colon'  => __( 'Parent Item:', 'smm-api' ),
				'all_items'          => __( 'All Subscriptions', 'smm-api' ),
				'view_item'          => __( 'View Subscriptions', 'smm-api' ),
				'add_new_item'       => __( 'Add New Subscription', 'smm-api' ),
				'add_new'            => __( 'Add New Subscription', 'smm-api' ),
				'edit_item'          => __( 'Subscription', 'smm-api' ),
				'update_item'        => __( 'Update Subscription', 'smm-api' ),
				'search_items'       => __( 'Search Subscription', 'smm-api' ),
				'not_found'          => __( 'Not found', 'smm-api' ),
				'not_found_in_trash' => __( 'Not found in Trash', 'smm-api' ),
			);

			$args = array(
				'label'               => __( 'smapi_subscription', 'smm-api' ),
				'labels'              => $labels,
				'supports'            => $supports,
				'hierarchical'        => false,
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => false,
				'exclude_from_search' => true,
				'capability_type'     => 'post',
				'capabilities'        => array(
					'create_posts' => false, // Removes support for the "Add New" function ( use 'do_not_allow' instead of false for multisite set ups )
					'edit_post'    => 'edit_subscription',
					'delete_post'  => 'delete_subscription',

				),
				'map_meta_cap'        => false
			);


			register_post_type( 'smapi_subscription', $args );
			flush_rewrite_rules();
		}




		/**
		 * Get all subscriptions of a user
		 *
		 * @access public
		 *
		 * @param int $user_id
		 *
		 * @return array
		 * @since  1.0.0
		 */

		public function get_subscriptions_by_user( $user_id ) {
			$subscriptions = get_posts(
				array(
					'post_type'      => SMMS_WC_Subscription()->post_name,
					'posts_per_page' => - 1,
					'meta_key'       => 'user_id',
					'meta_value'     => $user_id,
				)
			);

			return $subscriptions;
		}



	}

}


/**
 * Unique access to instance of SMAPI_Subscription class
 *
 * @return \SMAPI_Subscription_Helper
 */
function SMAPI_Subscription_Helper() {
	return SMAPI_Subscription_Helper::get_instance();
}
