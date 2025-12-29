<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly 
class SMM_WC_AJAX extends WC_AJAX {
    
        /**
         * Single instance of the class
         * License: GPLv3
   		 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
         * @var \SMMS_WC_Subscription_Admin
         */

        protected static $instance;

    public static function get_instance() {
      
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }
     /**
	 * Load variations via AJAX.
	 */
	public static function smm_load_variations() {
	    
		ob_start();

		check_ajax_referer( 'load-variations', 'security' );
		if ( ! current_user_can( 'edit_products' ) || empty( $_POST['product_id'] ) ) {
		    wp_die( -1 );
		}

		// Set $post global so its available, like within the admin screens.
		global $post;

		$loop           = 0;
		$product_id     = absint( $_POST['product_id'] );
		$post           = get_post( $product_id ); // phpcs:ignore
		
		$product_object = wc_get_product( $product_id );
		$per_page       = ! empty( $_POST['per_page'] ) ? absint( $_POST['per_page'] ) : 10;
		$page           = ! empty( $_POST['page'] ) ? absint( $_POST['page'] ) : 1;
		$variations     = wc_get_products(
			array(
				'status'  => array( 'private', 'publish' ),
				'type'    => 'variation',
				'parent'  => $product_id,
				'limit'   => $per_page,
				'page'    => $page,
				'orderby' => array(
					'menu_order' => 'ASC',
					'ID'         => 'DESC',
				),
				'return'  => 'objects',
			)
		);
		if ( $variations ) {
			wc_render_invalid_variation_notice( $product_object );
        
			foreach ( $variations as $variation_object ) {
			    
				$variation_id   = $variation_object->get_id();
				$variation      = get_post( $variation_id );
				$variation_data = array_merge( get_post_custom( $variation_id ), wc_get_product_variation_attributes( $variation_id ) ); 
				// kept for BW compatibility.
				
				include 'html-variation-admin.php';
				$loop++;
			}
		}
		wp_die();
	}
	/**
	 * Add variation via ajax function.
	 */
	public static function smm_add_variation() {
	    
		check_ajax_referer( 'add-variation', 'security' );

		if ( ! current_user_can( 'edit_products' ) || ! isset( $_POST['post_id'], $_POST['loop'] ) ) {
		   wp_die( -1 );
		}

		global $post; 
		// Set $post global so its available, like within the admin screens.

		$product_id       = intval( $_POST['post_id'] );
		$post             = get_post( $product_id ); // phpcs:ignore
		$loop             = intval( $_POST['loop'] );
		$product_object   = wc_get_product_object( 'variable', $product_id ); 
		// Forces type to variable in case product is unsaved.
		$variation_object = wc_get_product_object( 'variation' );
		$variation_object->set_parent_id( $product_id );
		$variation_object->set_attributes( array_fill_keys( array_map( 'sanitize_title', array_keys( $product_object->get_variation_attributes() ) ), '' ) );
		
		$variation_id   = $variation_object->save();
		$variation      = get_post( $variation_id );
		$variation_data = array_merge( get_post_custom( $variation_id ), wc_get_product_variation_attributes( $variation_id ) ); 
		// kept for BW compatibility.
		include 'html-variation-admin.php';
		wp_die();
	}
	    /**
	    * Save variations via AJAX.
	    */
	public static function smm_save_variations() {
		ob_start();

		check_ajax_referer( 'save-variations', 'security' );

		// Check permissions again and make sure we have what we need.
		if ( ! current_user_can( 'edit_products' ) || empty( $_POST ) || empty( $_POST['product_id'] ) ) {
			wp_die( -1 );
		}

		$product_id                           = absint( $_POST['product_id'] );
		WC_Admin_Meta_Boxes::$meta_box_errors = array();
		
		WC_Meta_Box_Product_Data::save_variations( $product_id, get_post( $product_id ) );
        //file_put_contents(plugin_dir_path( __FILE__ )."check23.txt",serialize(get_post( $product_id )));
		do_action( 'woocommerce_ajax_save_product_variations', $product_id );

		$errors = WC_Admin_Meta_Boxes::$meta_box_errors;

		if ( $errors ) {
			echo '<div class="error notice is-dismissible">';

			foreach ( $errors as $error ) {
				echo '<p>' . wp_kses_post( $error ) . '</p>';
			}

			echo '<button type="button" class="notice-dismiss"><span class="screen-reader-text">' . esc_html__( 'Dismiss this notice.', 'smm-api' ) . '</span></button>';
			echo '</div>';

			delete_option( 'woocommerce_meta_box_errors' );
		}

		wp_die();
	 }
}
/**
 * Unique access to instance of SMMS_WC_AJAX class
 *
 * @return \SMMS_WC_AJAX
 */
function SMMS_WC_AJAX() {
    return SMM_WC_AJAX::get_instance();
    
}