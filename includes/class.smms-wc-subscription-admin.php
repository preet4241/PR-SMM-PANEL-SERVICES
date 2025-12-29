<?php
if ( !defined( 'ABSPATH' ) || !defined( 'SMMS_SMAPI_VERSION' ) ) {
    exit; // Exit if accessed directly
}
/**
 * Implements admin features of SMMS WooCommerce Subscription
 *
 * @class   SMMS_WC_Subscription_Admin
 * @package SMMS WooCommerce Subscription
 * @since   1.0.0
 * @author  SMMS
   License: GPLv3
   License URI: https://www.gnu.org/licenses/gpl-3.0.html
 */
if ( !class_exists( 'SMMS_WC_Subscription_Admin' ) ) {

    class SMMS_WC_Subscription_Admin {

        /**
         * Single instance of the class
         *
         * @var \SMMS_WC_Subscription_Admin
         */

        protected static $instance;

        /**
         * @var $_panel Panel Object
         */
        protected $_panel;

        /**
         * @var $_premium string Premium tab template file name
         */
        protected $_premium = 'premium.php';

        /**
         * @var string Premium version landing link
         */
        protected $_premium_landing = 'http://softnwords.com';

        /**
         * @var string Panel page
         */
        protected $_panel_page = 'smms_woocommerce_subscription';

        /**
         * @var string Doc Url
         */
        public $doc_url = SMMS_SMAPI_PREMIUM_SUPPORT;
        public $cpt_obj_subscriptions;
        public $cpt_obj_servers;
        public $cpt_obj_items;
		public $cpt_obj_orders;
        /**
         * Returns single instance of the class
         * @return \SMMS_WC_Subscription_Admin
         * @since 1.0.0
         */
        public static function get_instance() {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }
            return self::$instance;
        }
        private $post_type;
        private $parameter_key;
        private $meta_key;
        private $product_select_list= '';
        /**  Constructor
         * Initialize plugin and registers actions and filters to be used
         * @since  1.0.0
         * @author sam
         */
        public function __construct() {
            $this->parameter_key= '_parameter_%';$this->meta_key = '_item_%';
            $this->post_type = 'smapi_server';
            $this->product_select_list = get_option('smapi_server_name_option');
            $this->create_menu_items();
            //Add action links
	        add_filter( 'plugin_action_links_' . plugin_basename( SMMS_SMAPI_DIR . '/' . basename( SMMS_SMAPI_FILE ) ), array( $this, 'action_links' ) );
	        add_filter( 'smms_show_plugin_row_meta', array( $this, 'plugin_row_meta' ), 10, 5 );
            //custom styles and javascripts
            add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_styles_scripts' ), 11);
            //product editor
            add_filter( 'product_type_options', array( $this, 'add_type_options' ) );
            //custom fields for single product 
            add_action( 'woocommerce_product_options_general_product_data', array( $this, 'add_custom_fields_for_api_products' ) );
            add_action( 'woocommerce_product_options_general_product_data',array($this, 'smm_cfwc_create_custom_field' ));
            add_action( 'woocommerce_product_options_general_product_data', array( $this, 'add_custom_fields_for_single_products' ) );
            add_action( 'woocommerce_process_product_meta', array( $this, 'save_custom_fields_for_single_products' ), 10, 2 );
            
	        //Sanitize the options before that are saved
	        add_filter( 'woocommerce_admin_settings_sanitize_option', array( $this, 'sanitize_value_option' ), 20, 3);
	        add_filter('smm_pull_order_status', array( $this, 'smm_pull_order_status_ar' ), 10, 3);
	        add_filter('smm_url_check_status', array( $this, 'smm_url_check_status_ar' ), 10, 1);
			add_action( 'woocommerce_variation_options',  array( $this,'smm_add_variation_option'), 10, 3 );
			add_action( 'woocommerce_product_after_variable_attributes', array( $this,'smm_variation_settings_fields'), 10, 3 );
            add_action( 'woocommerce_save_product_variation', array( $this, 'smm_save_custom_field_variations'), 10, 2 );
			add_filter( 'wc_order_statuses', array( $this, 'add_manual_to_order_statuses') );
			add_action( 'woocommerce_saved_order_items', array( $this, 'smm_status_manual'), 10, 2 );
			add_filter( 'product_type_options', array($this, 'smm_auto_virtual'),1);
			
			// Call to Ajax Fuctions to fill Form Fields for API ITEM and API SERVER
			SMMS_Ajax_Call_Admin();           
        }
		
        public function smm_status_manual($order_id, $items)
				{				    
				    $order = wc_get_order( $order_id );
				    
                if( 'wc-manual'== $_POST['order_status']) {                 
                  SMAPI_Subscription_Order()->smm_api_order_trigger($order_id);
                }		
		}        
         // Add custom status to order status list
        public function add_manual_to_order_statuses( $order_statuses ) {
            $new_order_statuses = array();
            
                foreach ( $order_statuses as $key => $status ) {
                    $new_order_statuses[ $key ] = $status;
                        if ( 'wc-processing' === $key ) {
                            if( get_option('smmapi_manual')  !=  'no' )
                        $new_order_statuses['wc-manual'] = 'Manual';
                        //$new_order_statuses['wc-renew'] = 'Renew';
                        }
                }
            return $new_order_statuses;
            }
            //       CUSTOM VARIATION FIELDS

                    /**
                     * Add custom variation product type option
                     * @version 1.7.14
                     * @param int $loop
                     * @param array $variation_data
                     * @param WP_Post $variation
                     */
        public function smm_add_variation_option( $loop, $variation_data, $variation ) { 
		    if ( ! is_object( $variation ) || ! isset( $variation->ID ) ) return;
                        $_smapi_api_check =
                                    get_post_meta( $variation->ID, 'variable_smm_api', true );
                        if($_smapi_api_check == "")
                        $_smapi_api_check = get_post_meta( ($variation->ID- 1), 'variable_smm_api', true );
                        if($_smapi_api_check == "")
                        {
                        $product_id = wp_get_post_parent_id($variation->ID);
                        $product = wc_get_product( $product_id );
                        $_smapi_api_check = 
                                    smm_get_prop( $product, '_smapi_api' );
                        }
                        if($_smapi_api_check == "yes"){
                                        $_smapi_api_check = 'on';
                        
                        }
						$checked = $_smapi_api_check;
						printf('
								<label class="tips" data-tip="%1$s">
								<input type="checkbox" class="checkbox"
								name="variable_smm_api[%2$s]"
								id="smm_variable_smm_api_%3$s"
								%4$s /> SMM API
								</label>',
						esc_attr__( 'Enable this option if the product uses the SMM API.', 'smm-api' ),
						esc_attr( $loop ),
						esc_attr( $loop ),
						checked( $checked, 'on', false ) // false so it returns instead of echoing
						);      
                    }
        // Make product virtual if selected smmapi check box
        public function smm_auto_virtual($arr)
        {
            global $thepostid;
             //file_put_contents(plugin_dir_path( __FILE__ )."check.txt", $thepostid);
	        $product = wc_get_product( $thepostid );
	        $_smapi_api_check = 
                            smm_get_prop( $product, '_smapi_api' );
            if($_smapi_api_check  == "yes"  )            
            $arr['virtual']['default'] = "yes"; 
            //$arr['downloadable']['default'] = "yes"; 
            return $arr;
        }/*** Create new fields for variations  **/
        public function smm_variation_settings_fields( $loop, $variation_data, $variation ) {

                global $wpdb;
                $api_item_list_options_saved_simple;
                $input_text_box_radio_saved_simple;
                $smm_custom_text_field_title_simple;
			    $post_type      = 'smapi_server';
			    $parameter_key  = '_parameter_%';
			    $meta_key       = '_item_%';
			    $api_item_list_options_saved    = get_post_meta( $variation->ID, 'var_smapi_service_id_option', true ) != "" ? 
			    get_post_meta( $variation->ID, 'var_smapi_service_id_option', true ) :
			    get_post_meta( ($variation->ID - 1), 'var_smapi_service_id_option', true );
			    //file_put_contents(plugin_dir_path( __FILE__ )."check.txt",$api_item_list_options_saved);
			    $api_server_list_options_saved  = get_post_meta( $variation->ID, 'var_smapi_server_name_option', true ) != "" ?
			    get_post_meta( $variation->ID, 'var_smapi_server_name_option', true ) : 
			    get_post_meta( ($variation->ID - 1), 'var_smapi_server_name_option', true );			    
			    //Geting Server details for SMM API servers			    
                $my_smm_result  = $wpdb->get_results( $wpdb->prepare( "SELECT smapi_p.post_title, smapi_p.ID FROM $wpdb->posts as smapi_p INNER JOIN " . $wpdb->prefix . "postmeta as smapi_pm ON ( smapi_p.ID = smapi_pm.post_id )
                WHERE 1=1
                AND smapi_p.post_type = %s
                AND smapi_pm.meta_key LIKE %s", $post_type, $parameter_key
                ), ARRAY_A );
				$total_servers = count($my_smm_result);
                //Geting Item details for API ITEM LISTING
                //getting from simple product data
			    if($api_server_list_options_saved == "")
			    {
			    $product_id = wp_get_post_parent_id($variation->ID);
                $product = wc_get_product( $product_id );   
			    $_smapi_server_name_option_simple 		=
			    smm_get_prop( $product, '_smapi_server_name_option' ); 
			    $api_item_list_options_saved_simple 	= 
			     smm_get_prop( $product, '_smapi_service_id_option' );
			    $input_text_box_radio_saved_simple   	= smm_get_prop( $product, 'locate_input_box' );
			    $smm_custom_text_field_title_simple 	= smm_get_prop( $product, 'smm_custom_text_field_title' );
			    $api_server_list_options_saved 			= $_smapi_server_name_option_simple;			    
			    }
			    // for new product only
			    if($api_server_list_options_saved == "" )
			    //For setting first item in list from forloop
			    foreach($my_smm_result as $sub_result)
			    $api_server_list_options_saved = $sub_result['ID'];			    
			    //file_put_contents(plugin_dir_path( __FILE__ )."check.txt",$api_server_list_options_saved);                
	            $smm_api_items_listing = $wpdb->get_results( $wpdb->prepare( "SELECT smapi_pm.* FROM " .$wpdb->prefix ."postmeta as smapi_pm INNER JOIN $wpdb->posts as smapi_p ON ( smapi_pm.post_id = %s )
                WHERE 1=1 
                AND smapi_p.post_type = %s
                AND smapi_pm.meta_key LIKE %s
                GROUP BY smapi_pm.meta_id
                ", $api_server_list_options_saved, $post_type, $meta_key
                ), ARRAY_A);
	            $smm_total_items = count($smm_api_items_listing);
	            //SERVER DETAILS
                foreach($my_smm_result as $sub_result)
                        {
                    
			            if($sub_result['ID'] == $api_server_list_options_saved )
			            $input_checkbox_default_server = $sub_result['ID'];
			            
			            $api_server_list_options_output[$sub_result['ID']] = 
			            smms_getHost($sub_result['post_title']);
			        
			            }
			     //SERVICE ITEM DETAILS
			     
			     //choice from simple product
			    if($api_item_list_options_saved == "")
			    $api_item_list_options_saved =
			    $api_item_list_options_saved_simple;
			    
			    // span data of item details
			    foreach($smm_api_items_listing as $sub_item_result){
			            
			            $descrption_opted_data_raw = " Min Order: " .smms_decode_string(
			                $sub_item_result['meta_value'],'f_min_order').
			                " Max Order: " .smms_decode_string(
			                    $sub_item_result['meta_value'],'f_max_order')
			                    .' Service ID - '.filter_var(
			                        $sub_item_result['meta_key'], FILTER_SANITIZE_NUMBER_INT);
			            
			            if($sub_item_result['meta_key'].' data-desc'.$descrption_opted_data_raw == $api_item_list_options_saved )
			            {
			            $input_checkbox_default_item    = $sub_item_result['meta_key'].' data-desc'.$descrption_opted_data_raw;
			            $descrption_opted_data = $descrption_opted_data_raw;
			            }
			            if($descrption_opted_data == "")
			            $descrption_opted_data = $descrption_opted_data_raw;
			            
			     $api_service_list_options_output[$sub_item_result['meta_key']
	                    .' data-desc'.$descrption_opted_data_raw] =
			            smms_decode_string($sub_item_result['meta_value'],'f_api_description');
			            }
                        echo '<div class="options_group">';

    woocommerce_wp_select( array( // Text Field type
        'id'            => 'var_smapi_service_id_option_'.esc_attr( $loop ),
        'name'          => 'var_smapi_service_id_option['.esc_attr( $loop ).']',
        'label'         => 'SMM API SERVICE ITEM',
        'wrapper_class' => 'form-row form-row-first',
        'class'         => 'smm_select_'.esc_attr( $loop ),
        'description'   => __( 'SMM SERVICE Item Loaded from SMM API ITEM LIST', 'smm-api' ),
        'desc_tip'      => true,
        'cbvalue'       => 'yes', // selected if same than 'value' (default value)
        'options'       => $api_service_list_options_output,
        'value'         => $input_checkbox_default_item,
        
        
    ) );
    woocommerce_wp_select( array( // Text Field type
        'id'            => 'var_smapi_server_name_option_'.esc_attr( $loop ),
        'name'          => 'var_smapi_server_name_option['.esc_attr( $loop ).']',
        'label'         => __( 'SMM SERVER PROVIDER', 'smm-api' ),
        'wrapper_class' => 'form-row form-row-last',
        'description'   => __( 'SERVER Site Loaded from SMM API SERVER LIST', 'smm-api' ),
        'desc_tip'      => true,
        'cbvalue'       => 'yes', // selected if same than 'value' (default value)
        'options'       => $api_server_list_options_output,
        'value'         => $input_checkbox_default_server,
    ) );    
    echo '<div><span id="var_smapi_service_span_option_'. esc_attr( $loop ).'" style="float:left;display:block;">
                    '.esc_html($descrption_opted_data).'
                        </div><div>.    ).Quantity depends on MIN MAX </div></span></div> ';    
    // Text Field
    $var_customer_input_field_label = get_post_meta( $variation->ID, 'var_smm_customer_input_field_label', true ) != "" ?
    get_post_meta( $variation->ID, 'var_smm_customer_input_field_label', true ):
    get_post_meta( ($variation->ID - 1), 'var_smm_customer_input_field_label', true );
    if($var_customer_input_field_label == "")
    $var_customer_input_field_label =
                            $smm_custom_text_field_title_simple;
    woocommerce_wp_text_input( array(
        'id'            => 'var_smm_customer_input_field_label',        
        'class'         => 'short',
        'style'         => 'font-size: large;',
        'label'         => __( 'Customer Input Text Label', 'smm-api' ),
        'description'   => __( 'Getting Customer Inputs', 'smm-api' ),
        'desc_tip'      => true,
        'value'         => $var_customer_input_field_label
                   ) );
    //Radio button
   $input_text_box_radio_saved     = get_post_meta( $variation->ID, 'locate_input_box', true ) != "" ?
			    get_post_meta( $variation->ID, 'locate_input_box', true ) :
			    get_post_meta( ($variation->ID - 1), 'locate_input_box', true );
			    
			    if($input_text_box_radio_saved == "")
			    $input_text_box_radio_saved = 
			                    $input_text_box_radio_saved_simple;
			    $input_text_box_radio_product   = ($input_text_box_radio_saved == 'product') ? 'checked' :'';
	            $input_text_box_radio_cart     	= ($input_text_box_radio_saved == 'cart') ? 'checked' :'';
				$input_text_box_radio_other     = ($input_text_box_radio_saved == 'other') ? 'checked' :'';
    
	woocommerce_wp_radio( array(
        
        'id'            => 'locate_input_box_['.esc_attr( $loop ).']',
        'name'          => 'locate_input_box['.esc_attr( $loop ).']',
        'wrapper_class' => 'show_if_variable',
        'label'         => __('Locate Text Box for Customer Input', 'smm-api'),
        'style'         => 'font-size: large;',
        'description'   => __( 'Selection of Input on page', 'smm-api' ),
        'desc_tip'      => true,
        'value'        => $input_text_box_radio_saved,
        'options'       => array(
        'product'       => __('Product Page' , 'smm-api'),
		'cart'       	=> __('Cart Page' , 'smm-api'),
        'other'         => __('Other Plugin field', 'smm-api'),
            
        ),
        
    ) );

}

        // the smm variation field added in admin page and calling html
        //-variation-admin.php over ride by plugin specific
        public function smm_woocommerce_variation_option_name_load(){
            //load variation replaced here
            remove_action('wp_ajax_woocommerce_load_variations', 'WC_AJAX::load_variations', 10);
            SMM_WC_AJAX::smm_load_variations();
        	
	    }
	    public function smm_woocommerce_variation_option_name_add(){
            //Add variation replaced here
        	remove_action('wp_ajax_woocommerce_add_variation', 'WC_AJAX::add_variation', 10);
            SMM_WC_AJAX::smm_add_variation();
	    }
	    public function smm_woocommerce_variation_option_name_save(){
            //Save variation replaced here
        	remove_action('wp_ajax_woocommerce_save_variations', 'WC_AJAX::save_variations', 10);
            SMM_WC_AJAX::smm_save_variations();
	    }
        // -----------------------------------------
        //  Save everything of product variation save
        public function smm_save_custom_field_variations( $variation_id, $i ) {
            
            // Customer input field label for each variations
            $var_smm_customer_input_field_label = sanitize_text_field($_POST['var_smm_customer_input_field_label']);
            if ( isset( $var_smm_customer_input_field_label ) ) update_post_meta( $variation_id, 'var_smm_customer_input_field_label', sanitize_text_field( $var_smm_customer_input_field_label ) );
            
            //Adding SMM API check box for variable product
            
            $variable_smm_api = isset($_POST['variable_smm_api'][$i]) ? sanitize_key($_POST['variable_smm_api'][$i]) : 'off';
            if( $variable_smm_api )
            update_post_meta( $variation_id, 'variable_smm_api', $variable_smm_api  );
            
            //Adding SMM API service ID  for variable product
            
            $var_smapi_service_id_option = isset($_POST['var_smapi_service_id_option'][$i]) ? sanitize_text_field($_POST['var_smapi_service_id_option'][$i]) : 'NA';
            if( $var_smapi_service_id_option )
            update_post_meta( $variation_id, 'var_smapi_service_id_option', $var_smapi_service_id_option  );
            $var_smapi_server_name_option = isset($_POST['var_smapi_server_name_option'][$i]) ? sanitize_text_field($_POST['var_smapi_server_name_option'][$i]) : 'NA';
            if( $var_smapi_server_name_option )
            update_post_meta( $variation_id, 'var_smapi_server_name_option', $var_smapi_server_name_option  );
            $locate_input_box = isset($_POST['locate_input_box'][$i]) ? sanitize_text_field($_POST['locate_input_box'][$i]) : 'NA';
            if( $locate_input_box )
            update_post_meta( $variation_id, 'locate_input_box', $locate_input_box  );
            
            }
 
        // -----------------------------------------
        //  Store custom field value into variation data
 
        public function smm_add_custom_field_variation_data( $variations ) {
            $variations['var_smm_customer_input_field_label'] = '<div class="woocommerce_custom_field">Custom Field: <span>' .esc_html( get_post_meta( $variations[ 'variation_id' ], 'var_smm_customer_input_field_label', true ) ). '</span></div>';
            return $variations;
        }
		/**
		* Display the custom text field on admin product page backend
		* @since 1.0.0 
		*/
        
		public function smm_cfwc_create_custom_field() {
			
		}
        
	    /**
	     * Add a product type option in single product editor
	     *
	     * @access public
	     *
	     * @param $types
	     *
	     * @return array
	     * @since 1.0.0
	     */
        public function add_type_options( $types ) {
            if( get_option('smapi_enabled') == 'yes' )
            $types['smapi_subscription'] = array(
                'id'            => '_smapi_subscription',
                'wrapper_class' => 'show_if_simple show_if_variable',
                'label'         => __( 'SMM SUB', 'smm-api' ),
                'description'   => __( 'Create SMM subscription for this product', 'smm-api' ),
                'default'       => 'no'
            );
            if( get_option('smmapi_enabled')  !=  'no' )
            $types['smapi_api'] = array(
                'id'            => '_smapi_api',
                'wrapper_class' => 'show_if_simple',
                'label'         => __( 'SMM API', 'smm-api' ),
                'description'   => __( 'Create SMM API Order Trigger for this product', 'smm-api' ),
                'default'       => 'no'
            );
            return $types;
        }

        /**
         * Add custom fields for single product
         *
         * @since   1.0.0
         * @author  sam
         * @return  void
         */
         
         // for api service ID integration
         public function add_custom_fields_for_api_products() {
            global $thepostid, $wpdb;
            update_option( 'smapi_server_name_option',  $thepostid ); 
	        $product = wc_get_product( $thepostid );
	        
	        $api_server_list_options_saved = 
	        smm_get_prop( $product, '_smapi_server_name_option' );
	        $api_item_list_options_saved   = smm_get_prop( $product, '_smapi_service_id_option' );
	        $input_text_box_radio_saved   = smm_get_prop( $product, 'locate_input_box' );
	        
	        $input_text_box_radio_product = ($input_text_box_radio_saved == 'product')  || ($input_text_box_radio_saved != 'checkout')? 'checked' :'';
	        $input_text_box_radio_cart = ($input_text_box_radio_saved == 'checkout') ? 'checked' :'';
	        
        
        $my_smm_result  = $wpdb->get_results( $wpdb->prepare( "SELECT smapi_p.post_title, smapi_p.ID FROM $wpdb->posts as smapi_p INNER JOIN " . $wpdb->prefix . "postmeta as smapi_pm ON ( smapi_p.ID = smapi_pm.post_id )
        WHERE 1=1
        AND smapi_p.post_type = %s
        AND smapi_pm.meta_key LIKE %s", $this->post_type, $this->parameter_key
        ), ARRAY_A );
        
        
			 
			$total_servers = count($my_smm_result);
			$current_selection = get_option('smm_api_server_item');
			$api_server_list_options_output='';
			foreach($my_smm_result as $sub_result){
			if($api_server_list_options_saved == "" )
			$api_server_list_options_saved = esc_attr($sub_result['ID']);
			$api_server_list_options_output .= '<option ';
			$api_server_list_options_output .=
			($sub_result['ID'] == $api_server_list_options_saved ) ?
			'value="'.esc_attr($sub_result['ID']).'" selected': 
	        'value="'.esc_attr($sub_result['ID']).'"' ;
	        $api_server_list_options_output .= '>'
	        .esc_html(smms_getHost($sub_result['post_title']))
			. '</option>';
			}
			// API ITEM LISTING
        

        
	    $smm_api_items_listing = $wpdb->get_results( $wpdb->prepare( "SELECT smapi_pm.* FROM " .$wpdb->prefix ."postmeta as smapi_pm INNER JOIN $wpdb->posts as smapi_p ON ( smapi_pm.post_id = %s )
        WHERE 1=1 
        AND smapi_p.post_type = %s
        AND smapi_pm.meta_key LIKE %s
        GROUP BY smapi_pm.meta_id
        ", $api_server_list_options_saved, $this->post_type, $this->meta_key
        ), ARRAY_A); 
	    $smm_total_items = count($smm_api_items_listing);
	    $descrption_opted = '';
	    $api_item_list_options_output='';
	    foreach($smm_api_items_listing as $sub_item_result){
			$smm_service_id = filter_var($sub_item_result['meta_key'], FILTER_SANITIZE_NUMBER_INT);
			$descrption_opted_data = " Min Order: " .smms_decode_string($sub_item_result['meta_value'],'f_min_order').
			" Max Order: " .smms_decode_string($sub_item_result['meta_value'],'f_max_order')
			.' Service ID - '.$smm_service_id; 
			
			$api_item_list_options_output .= '<option ';
			$api_item_list_options_output .=
			($sub_item_result['meta_key'] == $api_item_list_options_saved ) ?
			'value="'.esc_attr($sub_item_result['meta_key']).'"'
			.' data-desc="'.esc_attr($descrption_opted_data)
			.'" selected'
			: 
	        'value="'.esc_attr($sub_item_result['meta_key']).'"'
	        .' data-desc="'.esc_attr($descrption_opted_data).'"'
	        ;
	        ($sub_item_result['meta_key'] == $api_item_list_options_saved ) ?
	        $descrption_opted = esc_attr($descrption_opted_data)
	        :NULL
	       ;
	        $api_item_list_options_output .= '>'
	        .esc_html(' ID: '. $smm_service_id .' ' . smms_decode_string($sub_item_result['meta_value'],'f_api_description'))
	        //service id = filter_var($sub_item_result['meta_key'], FILTER_SANITIZE_NUMBER_INT)
			. '</option>';
			}	    
            echo '<div class="options_group show_if_simple">';            
            ?>
            <h3 class="smapi_server_name" id="<?php echo esc_attr($thepostid)?>"><?php echo esc_html('SMM API Settings'); ?></h3>
            <div class="options_group show_if_simple show_if_api">                
                <p class="form-field smapi_server_name">
                    <label for="_smapi_server_name"><?php  echo esc_html( 'SERVER NAME' ); ?></label>                    
                    <select id="_smapi_server_name_option" name="_smapi_server_name_option" class="select" style="margin-left: 3px;">
                        <?php 
			 				$allowed_html = array(
												'option' => array(
        										'value' => array(),
												'selected'	=> array(),
										        'data-desc'	=> array()
     												 ),
								);			
                           echo wp_kses($api_server_list_options_output, $allowed_html);                        
                        ?>
                    </select>
                </p>
                <p class="form-field smapi_service_id">
                    <label for="_smapi_service_id"><?php  esc_html_e( 'SMM API SERVICE', 'smm-api' ); ?></label>                    
                    <select id="_smapi_service_id_option" name="_smapi_service_id_option" class="smm_select" style="margin-left: 3px;">
                        <?php 
			 					$allowed_html = array(
    								'option' => array(
        										'value' => array(),
												'selected'	=> array(),
										        'data-desc'	=> array()
     												 ),
								);
                             echo wp_kses($api_item_list_options_output, $allowed_html);                        
                        ?>
                    </select>   
                    <span class="description" style="float:left;display=block;"><span><?php 
                    echo esc_html( $descrption_opted ) ?></span> 
                    </p>                
				<p class="_input_box">
				<input  type="radio" id="product_input_box" name = "locate_input_box" value="product" <?php echo esc_attr($input_text_box_radio_product);?> >Customer Input Box at Product Page
                <input  type="radio" id="checkout_input_box" name = "locate_input_box" value="checkout" <?php echo esc_attr($input_text_box_radio_cart);?> > Customer Input Box at Check Out Page
       			</p>
            </div>
            </div><?php
            echo '<div class="options_group show_if_simple">';
            $args = array(
			'id'            => 'smm_custom_text_field_title',
			'wrapper_class' => 'show_if_simple',
			'label'         => __( 'Label For Customer Input TextBox', 'smm-api' ),
			'placeholder'   => __( 'Enter URL OR Username', 'smm-api'),
		
			'class'         => 'smm-cfwc-custom-field',
			'desc_tip'      => true,
			//'custom_attributes' => array( 'required' => '' ),
			'description'   => __( 'Request Customer for required input while placing Order.', 'smm-api' ),
			);
		woocommerce_wp_text_input( $args );
            
		echo '</div>';
        }
		public function debug_smm_data() {
			$order         = wc_get_order( 2524 );
			$subscriptions = smm_get_prop( $order, 'subscriptions', true );
			// This is how to grab line items from the order 
	        $line_items = $order->get_items();
	        foreach ( $line_items as $item ){
	        $product = $order->get_product_from_item( $item );
	        $product_name = $product->get_name();
	        // get order item meta data (in an unprotected array)
            $product_id = $item->get_product_id();            
	        // This is the qty purchased
		    $quantity = $item->get_quantity(); //Get the product QTY	        
            //$Customer Data_raw
            $item_meta_data = $item->get_meta('Entered', true );
	        $api_server_list_options_saved = get_post_meta( $product_id, 'smapi_server_name_option', true );			
	        $api_item_list_options_saved   = get_post_meta( $product_id, 'smapi_service_id_option', true );
			//smm_get_prop( $product, '_smapi_service_id_option' );
	        $api_item_max_qty   = get_post_meta( $product_id, 'smapi_service_qty_max', true );
			$api_item_min_qty   = get_post_meta( $product_id, 'smapi_service_qty_min', true );
			}
	        $int_api_item = (int) filter_var($api_item_list_options_saved, FILTER_SANITIZE_NUMBER_INT);
			// Only for product variation			
			if($product->is_type('variation')){
								// Get the variation attributes
								$variation_attributes = $product->get_variation_attributes();
								// Loop through each selected attributes
								foreach($variation_attributes as $attribute_taxonomy => $term_slug){
								$taxonomy = str_replace('attribute_', '', $attribute_taxonomy );
								// The name of the attribute
								$attribute_name = get_taxonomy( $taxonomy )->labels->singular_name;
								// The term name (or value) for this attribute
								$attribute_value = get_term_by( 'slug', $term_slug, $taxonomy )->name;												}
								if (preg_match("/quantity/", $taxonomy)){
								$order_quantity = (int) filter_var($attribute_value, FILTER_SANITIZE_NUMBER_INT) * $quantity;
								if ($order_quantity > $api_item_max_qty)
								$order_quantity = $api_item_max_qty;
								if ($order_quantity < $api_item_min_qty)
								$order_quantity = $api_item_min_qty;
									}
									}			
			return $api_server_list_options_saved.$taxonomy.$attribute_name.$attribute_value.$order_quantity;
		}
         // end of testing code
        public function add_custom_fields_for_single_products() {
            global $thepostid;
	        $product = wc_get_product( $thepostid );
            echo '<div class="options_group">';
            $_smapi_price_is_per           = smm_get_prop( $product, '_smapi_price_is_per' );
            $_smapi_price_time_option      = smm_get_prop( $product, '_smapi_price_time_option' );
            $_smapi_max_length             = smm_get_prop( $product, '_smapi_max_length' );
            $max_lengths = smapi_get_max_length_period();
            ?>
            <h3 class="smapi_price_is_per"><?php esc_html_e('Subscription Settings','smm-api') ?></h3>
            <div class="options_group show_if_simple show_if_variable">
                <p class="form-field smapi_price_is_per">
                    <label for="_smapi_price_is_per"><?php esc_html_e( 'Price is per', 'smm-api' ); ?></label>
                    <input type="text" class="short" name="_smapi_price_is_per" id="_smapi_price_is_per" value="<?php echo esc_attr( $_smapi_price_is_per ); ?>" style="float: left; width:15%;" /> 
                    <select id="_smapi_price_time_option" name="_smapi_price_time_option" class="select" style="margin-left: 3px;">
                        <?php foreach ( smapi_get_time_options() as $key => $value ):
                            $select = selected( $_smapi_price_time_option, $key, false );
                            echo '<option value="' . esc_attr($key) . '" ' . esc_attr($select). ' data-max="'.esc_attr($max_lengths[$key]).'">' . esc_html($value) . '</option>';
                        endforeach;
                        ?>
                    </select>
                </p>

                <p class="form-field smapi_max_length">
                    <label for="_smapi_max_length"><?php esc_html_e( 'Max length:', 'smm-api' ); ?>
                        <a href="#" class="tips" data-tip="<?php esc_attr_e( 'Leave it empty for unlimited subscription', 'smm-api' ) ?>"> [?]</a></label>
                    <input type="text" class="short" name="_smapi_max_length" id="_smapi_max_length" value="<?php echo esc_attr( $_smapi_max_length ); ?>" style="float: left; width:15%; " />
                    <span class="description">
						<span>
						<?php echo  $time_opt = ( $_smapi_price_time_option ) ?
							esc_html($_smapi_price_time_option) : 'days' ?>
						</span> 
						<?php printf(/* translators: search here */ 
						esc_html__('(Max: ', 'smm-api') ) ?>
						<span class="max-l">
							<?php printf('%d',esc_html($max_lengths[$time_opt])) ?>
							</span>)
							</span>
                </p>

            </div>
            </div>
            
		<?php

        }

	    /**
	     * Save custom fields for single product
	     *
	     * @since   1.0.0
	     * @author  sam
	     *
	     * @param $post_id
	     * @param $post
	     *
	     * @return void
	     */
	    public function save_custom_fields_for_single_products( $post_id, $post ) {
			global $wpdb;
		    $product = wc_get_product( $post_id );
		    $args    = array();

		    $args['_smapi_subscription'] = isset( $_POST['_smapi_subscription'] ) ? 'yes' : 'no';

		    if ( isset( $_POST['_smapi_price_is_per'] ) ) {
			    $args['_smapi_price_is_per'] = sanitize_text_field($_POST['_smapi_price_is_per']);
		    }


		    if ( isset( $_POST['_smapi_price_time_option'] ) ) {
			    $args['_smapi_price_time_option'] = sanitize_text_field($_POST['_smapi_price_time_option']);
		    }

		    if ( isset( $_POST['_smapi_price_time_option'] ) && isset( $_POST['_smapi_max_length'] ) ) {
			    $max_length                = smapi_validate_max_length( sanitize_text_field($_POST['_smapi_max_length']), sanitize_text_field($_POST['_smapi_price_time_option'] ));
			    $args['_smapi_max_length'] = $max_length;
		    }
		    

		    if ( isset( $_POST['_smapi_server_name_option'] ) ) {
			    $args['_smapi_server_name_option'] = sanitize_key($_POST['_smapi_server_name_option']);
			update_post_meta( $post_id, 'smapi_server_name_option', sanitize_key($_POST['_smapi_server_name_option']) );
			$api_server_list_options_saved = sanitize_key($_POST['_smapi_server_name_option']);
		    }
		    if ( isset( $_POST['_smapi_service_id_option'] ) ) {
			    $args['_smapi_service_id_option'] = sanitize_text_field($_POST['_smapi_service_id_option']);
				$api_item_list_options_saved = sanitize_text_field($_POST['_smapi_service_id_option']);
			update_post_meta( $post_id, 'smapi_service_id_option', sanitize_text_field($_POST['_smapi_service_id_option']) );
			// API ITEM LISTING
       

        
	    $smm_api_items_listing = $wpdb->get_results( $wpdb->prepare( "SELECT smapi_pm.* FROM " .$wpdb->prefix ."postmeta as smapi_pm INNER JOIN $wpdb->posts as smapi_p ON ( smapi_pm.post_id = %s )
        WHERE 1=1 
        AND smapi_p.post_type = %s
        AND smapi_pm.meta_key = %s
        GROUP BY smapi_pm.meta_id
        ", $api_server_list_options_saved, $this->post_type, $api_item_list_options_saved
        ), ARRAY_A); 
				$smm_total_items = $smm_api_items_listing->num_rows;
		foreach($smm_api_items_listing as $sub_item_result){
			
			
			$smapi_service_qty_max = smms_decode_string($sub_item_result['meta_value'],'f_max_order');
			$smapi_service_qty_min = smms_decode_string($sub_item_result['meta_value'],'f_min_order');
			}
			update_post_meta( $post_id, 'smapi_service_qty_max', $smapi_service_qty_max);
			update_post_meta( $post_id, 'smapi_service_qty_min', $smapi_service_qty_min);
		    }
		    //radio button selct value for input text box
		   
		   if(isset( $_POST['locate_input_box'] ) ){
		   $args['locate_input_box'] =   sanitize_text_field($_POST['locate_input_box']);
		   }
		    // saving custome input text data
		    if(isset( $_POST['smm_custom_text_field_title'] ) ){
		   $args['smm_custom_text_field_title'] =   sanitize_text_field($_POST['smm_custom_text_field_title']);
		   
		   
            // EDIT FOR SMM API CHECK BOX
		    $args['_smapi_api'] = isset( $_POST['_smapi_api'] ) && !empty($args['smm_custom_text_field_title']) ? 'yes' : 'no';//phpcs:ignore
            }
            
		    if ( $args ) {
			    smm_save_prop( $product, $args );
		    }

	    }


        /**
         * Enqueue styles and scripts
         *
         * @access public
         * @return void
         * @since 1.0.0
         */
        public function enqueue_styles_scripts() {
            wp_enqueue_style( 'smms_smapi_backend', SMMS_SMAPI_ASSETS_URL . '/css/backend.css', SMMS_SMAPI_VERSION );
            wp_enqueue_style( 'smms_smapi_datatables', SMMS_SMAPI_ASSETS_URL . '/css/datatables.css', SMMS_SMAPI_VERSION );
			wp_enqueue_style( 'smms_smapi_select_dataTables', SMMS_SMAPI_ASSETS_URL . '/css/select.dataTables.css', null, SMMS_SMAPI_VERSION );
			wp_enqueue_style( 'smms_select2css', '//cdnjs.cloudflare.com/ajax/libs/select2/3.4.8/select2.css', false, '1.0', 'all' );
			wp_enqueue_script( 'smms_select2', '//cdnjs.cloudflare.com/ajax/libs/select2/3.4.8/select2.js', array( 'jquery' ), '1.0', true );
			wp_enqueue_script( 'smms_smapi_admin', SMMS_SMAPI_ASSETS_URL . '/js/smapi-admin' . SMMS_SMAPI_SUFFIX . '.js', array( 'jquery' ), SMMS_SMAPI_VERSION, true );
            wp_enqueue_script( 'jquery-blockui', SMMS_SMAPI_ASSETS_URL . '/js/jquery.blockUI.min.js', array( 'jquery' ), '1.0', true );
            
            wp_localize_script( 'smms_smapi_admin', 'smms_smapi_admin', array(
                'ajaxurl'                 => admin_url( 'admin-ajax.php' ),
                'block_loader'            => apply_filters( 'smms_smapi_block_loader_admin', SMMS_SMAPI_ASSETS_URL . '/images/block-loader.gif' ),
            )
            );//added for addserver button
            wp_enqueue_script( 'jquery-smm-script', SMMS_SMAPI_ASSETS_URL . '/js/smm-script.js', array( 'jquery' ), SMMS_SMAPI_VERSION, true );
			wp_enqueue_script( 'jquery-datatables', SMMS_SMAPI_ASSETS_URL . '/js/datatables.js', array( 'jquery' ), SMMS_SMAPI_VERSION, true );
			wp_enqueue_script( 'jquery-dataTables-select', SMMS_SMAPI_ASSETS_URL . '/js/dataTables.select.js', array( 'jquery' ), SMMS_SMAPI_VERSION, true );
			if ( defined( 'SMM_API_PREMIUM' ) )
			wp_enqueue_script( 'jquery-smm-premium',  '/wp-content/plugins/smm-api-premium/smm-premium.js', array( 'jquery' ), SMMS_SMAPI_VERSION, true );
        }

        /**
         * Create Menu Items
         *
         * Print admin menu items
         *
         * @since  1.0
         * @author sam
         */

        private function create_menu_items() {
            // Add a panel under SMMS Plugins tab
            add_action( 'admin_menu', array( $this, 'register_panel' ), 5 );
            add_action( 'smms_smapi_subscriptions_tab', array( $this, 'subscriptions_tab' ) );
            add_action( 'smms_smapi_servers_tab', array( $this, 'servers_tab' ) );
            add_action( 'smms_smapi_items_tab', array( $this, 'items_tab' ) );
            add_action( 'smms_smapi_orders_tab', array( $this, 'orders_tab' ) );
            add_action( 'smms_smapi_premium_tab', array( $this, 'premium_tab' ) );
            
        }

        /**
         * Add a panel under SMMS Plugins tab
         *
         * @return   void
         * @since    1.0
         * @author   sam softnwords
         * @use      /SMM_Plugin_Panel_WooCommerce class
         * @see      plugin-fw/lib/smm-plugin-panel.php
         */

        public function register_panel() {

            if ( !empty( $this->_panel ) ) {
                return;
            }
            $admin_tabs = array(
                'items'     		=> __( 'API ITEMS', 'smm-api' ),
                'servers'     		=> __( 'API SERVERS', 'smm-api' ),
                'orders'     		=> __( 'API ORDERS', 'smm-api' ),
				'subscriptions'     => __( 'Subscriptions', 'smm-api' ),
                'general'   		=> __( 'Settings', 'smm-api' ),
                );
	        if( smms_check_privacy_enabled() ){
		        $admin_tabs['privacy'] = esc_html__( 'Privacy', 'smm-api' );
	        }
            $admin_tabs['premium'] =  apply_filters('smm_premium_tab_label', 'Free Version');

            $args = array(
                'create_menu_page' => true,
                'parent_slug'      => '',
                'page_title'       => 'SMM API & WooCommerce Subscriptions',
                'menu_title'       => 'SMM Panel',
                'capability'       => 'manage_options',
                'parent'           => '',
                'parent_page'      => 'smms_plugin_panel',
                'links'            => $this->get_panel_sidebar_link(),
                'page'             => $this->_panel_page,
                'admin-tabs'       => $admin_tabs,
                'class'            => smms_set_wrapper_class(),
                'options-path'     => SMMS_SMAPI_DIR . '/plugin-options'
            );

            /* === Fixed: not updated theme  === */
            if ( !class_exists( 'SMM_Plugin_Panel' ) ) {
                require_once( SMMS_SMAPI_DIR.'/plugin-fw/lib/smm-plugin-panel.php' );
            }
            if ( !class_exists( 'SMM_Plugin_Panel_WooCommerce' ) ) {
                require_once( SMMS_SMAPI_DIR.'/plugin-fw/lib/smm-plugin-panel-wc.php' );
            }


            $this->_panel = new SMM_Plugin_Panel_WooCommerce( $args );


        }/**
         * Premium Tab Template
         *
         * Load the premium tab template on admin page
         *
         * @return   void
         * @since    1.0
         * @author   sam softnwords
         */
        public function premium_tab() {
            $premium_tab_template = SMMS_SMAPI_TEMPLATE_PATH . '/admin/' . $this->_premium;

            if ( file_exists( $premium_tab_template ) ) {
               include_once( $premium_tab_template );
			    }		
		
        }
        /**
         * Action Links
         *
         * add the action links to plugin admin page
         *
         * @param $links | links plugin array
         *
         * @return   mixed Array
         * @since    1.0
         * @author   sam softnwords
         * @return mixed
         * @use      plugin_action_links_{$plugin_file_name}
         */
	    public function action_links( $links ) {
            if( function_exists('smms_add_action_links') ){
	            $links = smms_add_action_links( $links, $this->_panel_page, false );
            }
		    return $links;
	    }
	    /**
         * Plugin rows
         *
	     * @param $new_row_meta_args
	     * @param $plugin_meta
	     * @param $plugin_file
	     * @param $plugin_data
	     * @param $status
	     * @param string $init_file
	     *
	     * @return mixed
	     * @author sam softnwords
	     */
	    public function plugin_row_meta( $new_row_meta_args, $plugin_meta, $plugin_file, $plugin_data, $status, $init_file = 'SMMS_SMAPI_FREE_INIT' ) {
		    if ( defined( $init_file ) && constant( $init_file ) == $plugin_file ) {
			    $new_row_meta_args['slug'] = SMMS_SMAPI_SLUG;
		    }
		    return $new_row_meta_args;
	    }
        /**
         * Get the premium landing uri
         *
         * @since   1.0.0
         * @author  sam softnwords
         * @return  string The premium landing link
         */
        public function get_premium_landing_uri(){
            return $this->_premium_landing;
        }
        /**
         * Subscriptions List Table
         *
         * Load the subscriptions on admin page
         *
         * @return   void
         * @since    1.0
         * @author   sam
         */
        public function subscriptions_tab() {
            $this->cpt_obj_subscriptions = new SMMS_SMAPI_Subscriptions_List_Table();

           $subscriptions_tab = SMMS_SMAPI_TEMPLATE_PATH . '/admin/subscriptions-tab.php';

            if ( file_exists( $subscriptions_tab ) ) {
                include_once( $subscriptions_tab );
            }
        }
        /**
         * Add the widget of "Important Links" inside the admin sidebar
         * @return array
         */
        public function get_panel_sidebar_link() {
            return array(
                array(
                    'url'   => esc_url($this->doc_url),
                    'title' => esc_html__( 'Plugin Documentation', 'smm-api' )
                ),
                
                array(
                    'url'   => esc_url('https://wordpress.org/support/plugin/smm-api'),
                    'title' => esc_html__( 'WordPress support forum', 'smm-api' )
                ),
                array(
                    'url'   => esc_url($this->doc_url . '/changelog-free'),
                    'title' => sprintf(/* translators: search here */ esc_html( 'Changelog (current version %s)', 'smm-api' ), esc_html(SMMS_SMAPI_VERSION ))
                ),
            );
        }
	    /**
	     * Sanitize the option of type 'relative_date_selector' before that are saved.
	     *
	     * @param $value
	     * @param $option
	     * @param $raw_value
	     *
	     * @return array
	     * @since 1.4
	     * @author sam softnwords
	     */
	    public function sanitize_value_option(  $value, $option, $raw_value  ) {

		    if ( isset( $option['id'] ) && in_array( $option['id'], array( 'smapi_trash_pending_subscriptions', 'smapi_trash_cancelled_subscriptions' ) ) ) {
			    $raw_value = maybe_unserialize( $raw_value );
			    $value = wc_parse_relative_date_option( $raw_value );
		    }
		    return $value;
	    }
	    /**
         * Servers List Table
         *
         * Load the subscriptions on admin page
         *
         * @return   void
         * @since    1.0
         * @author   sam
         */
        public function servers_tab() {
            $this->cpt_obj_servers = new SMMS_SMAPI_Servers_List_Table();

            $servers_tab = SMMS_SMAPI_TEMPLATE_PATH . '/admin/servers-tab.php';

            if ( file_exists( $servers_tab ) ) {
                include_once( $servers_tab );
            }            
        }
        /**
         * SMM API ITEM List Table
         *
         * Load the smm api item  on admin page
         *
         * @return   void
         * @since    1.0
         * @author   sam
         */
        public function items_tab() {
             $this->cpt_obj_items = new SMMS_SMAPI_SMM_API_ITEMS_List_Table();

            $items_tab = SMMS_SMAPI_TEMPLATE_PATH . '/admin/items-tab.php';

            if ( file_exists( $items_tab ) ) {
                include_once( $items_tab );
            }
        }
		/**
         * SMM API ORDER List Table
         *
         * Load the smm api order  on admin page
         *
         * @return   void
         * @since    1.0
         * @author   sam
         */
		public function orders_tab() {
             $this->cpt_obj_orders = new SMMS_SMAPI_Orders_List_Table();

            $orders_tab = SMMS_SMAPI_TEMPLATE_PATH . '/admin/orders-tab.php';

            if ( file_exists( $orders_tab ) ) {
                include_once( $orders_tab );
            }
        }
        public function	smm_pull_order_status_ar($new_api_order_status,$by_product_server,$smm_api_orders){
	    
	        $new_api_order_status = new SMAPI_Api($by_product_server);
	        $new_api_order_status_obj = $new_api_order_status->multi_status($smm_api_orders);
	        //file_put_contents(plugin_dir_path( __FILE__ )."check.txt","this frin admin.php line 828 "."server ID = ".$by_product_server."order ids = ".serialize($smm_api_orders).PHP_EOL, FILE_APPEND );
	        return $new_api_order_status_obj;
	    }//call from premium
        public function	smm_url_check_status_ar($new_api_status){
	    
	        $new_api_status_init = new SMAPI_Api($by_product_server);
	        $new_api_status_init_obj = $new_api_status_init->smm_url_check_status($new_api_status);
	       // file_put_contents(plugin_dir_path( __FILE__ )."check.txt",$new_api_status);
	        return $new_api_status_init_obj;
		}
        
    }
}
/**
 * Unique access to instance of SMMS_WC_Subscription_Admin class
 *
 * @return \SMMS_WC_Subscription_Admin
 */
function SMMS_WC_Subscription_Admin() {
    return SMMS_WC_Subscription_Admin::get_instance();
}