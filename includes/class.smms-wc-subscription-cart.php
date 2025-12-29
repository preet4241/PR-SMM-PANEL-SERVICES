<?php
if ( !defined( 'ABSPATH' ) || !defined( 'SMMS_SMAPI_VERSION' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Implements SMAPI_Subscription Cart Class
 *
 * @class   SMAPI_Subscription_Cart
 * @package SMMS WooCommerce Subscription
 * @since   1.0.0
 * @author  SMMS
 */
if ( !class_exists( 'SMAPI_Subscription_Cart' ) ) {

	/**
	 * Class SMAPI_Subscription_Cart
	 */
	class SMAPI_Subscription_Cart {

        /**
         * Single instance of the class
         *
         * @var \SMAPI_Subscription_Cart
         */
        protected static $instance;

		/**
		 * @var string
		 */
		public $post_type_name = 'smapi_subscription';
        public $title_label = 'Customer input';
        
        /**
         * Returns single instance of the class
         *
         * @return \SMAPI_Subscription_Cart
         * @since 1.0.0
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
			
			
            
            add_filter( 'woocommerce_cart_item_price', array($this, 'change_price_in_cart_html'), 10, 3);
            add_filter( 'woocommerce_quantity_input_args', array($this, 'smm_woocommerce_quantity_changes'), 10, 2 );
            add_filter( 'woocommerce_cart_item_subtotal', array($this, 'change_price_in_cart_html'), 10, 3);
            add_action( 'woocommerce_after_shop_loop_item',array($this, 'subscribe_arrow') );
            add_action( 'woocommerce_before_add_to_cart_button',array($this, 'smm_cfwc_display_custom_field' ));
            add_filter( 'woocommerce_add_to_cart_validation',array($this, 'smm_cfwc_validate_custom_field'), 10, 3 );
            add_filter( 'woocommerce_add_cart_item_data',array($this, 'smm_cfwc_add_custom_field_item_data'), 10, 4 );
            add_filter( 'woocommerce_cart_item_name',array($this, 'smm_cfwc_cart_item_name'), 10, 3 );
            add_action( 'wp_enqueue_scripts',array($this, 'prefix_enqueue_scripts' ), 10, 3 );
            
            add_action( 'woocommerce_after_add_to_cart_button', array($this,'smm_wc_text_after_quantity'),10,1); 
            add_filter( 'formatted_woocommerce_price', array($this, 'smm_ts_woo_decimal_price'), 10, 5 );
            add_action( 'wp_ajax_prefix_selected_variation_id',array($this, 'prefix_selected_variation_id' ), 10, 4 );
            add_action( 'wp_ajax_custom_input_data_id',array($this, 'get_custom_input_data' ), 10, 4 );
            add_action( 'wp_ajax_nopriv_custom_input_data_id',array($this, 'get_custom_input_data' ), 10, 4 );
            add_action( 'wp_ajax_subscription_select_data',array($this, 'set_subscription_select_data' ), 10, 4 );
            add_action( 'wp_ajax_nopriv_subscription_select_data',array($this, 'set_subscription_select_data' ), 10, 4 );
            
            add_filter( 'woocommerce_calculated_total',array($this, 'smm_calculated_total'), 10, 2 );
			// Load variation attributes in cart page
			add_action( 'wp_ajax_load_variation', [ $this, 'ajax_load_smm_variation' ] );
			add_action( 'wp_ajax_nopriv_load_variation', [ $this, 'ajax_load_smm_variation' ] );

			// Update variation attributes in cart page
			add_action( 'wp_ajax_update_variation', [ $this, 'ajax_update_smm_variation' ] );
			add_action( 'wp_ajax_nopriv_update_variation', [ $this, 'ajax_update_smm_variation' ] );

		}
        public function smm_wc_text_after_quantity() {
            global $product;
            $api_check_box_enabled = 
                smm_get_prop( $product, '_smapi_api' ) == "yes" ? 1 : null ;
                
                
            if ( is_product() && $api_check_box_enabled == 1 ) {
                $Min = 100;
                $Max = 1000;
                //SERVER ID AND ITEM ID TAKEN FROM PRODUCT OBJECT
	            $api_server_list_options_saved = 
	            smm_get_prop( $product, '_smapi_server_name_option' );
	            $api_item_list_options_saved   = 
	            smm_get_prop( $product, '_smapi_service_id_option' );
	            $order_item_meta =   
                get_post_meta( $api_server_list_options_saved, $api_item_list_options_saved, true );
	        
	            $order_item_meta_obj = json_decode($order_item_meta);
  
                $Min = $order_item_meta_obj->f_min_order; 
                $Max = $order_item_meta_obj->f_max_order; 
                if(get_option('smmapi_minmax')  !=  'no' )
                printf('<div class="min-max-qty">Min %1$d  Max %2$d </div>',
					   esc_html($Min),
					   esc_html($Max));
                }
        }
        public function smm_calculated_total( $total, $cart){
           // Iterate through each cart item
                    $subcribed=0;
                    foreach( $cart->get_cart() as $value ) {
                     if( isset( $value['subscribe_price'] ) ) {
                     $price = ($value['subscribe_price']-1)*$value['line_total'];
                     $subcribed +=$price;
                   
                     }
                    } 
          return round( $total + $subcribed, $cart->dp );  
        }
        
        public function set_subscription_select_data(){
            $smm_session_data = 
            isset($_POST['sub_smm_data']) ? 
            sanitize_text_field($_POST['sub_smm_data']) : null;
            $smm_session_product_id = 
            isset($_POST['smm_session_product']) ? 
            sanitize_text_field($_POST['smm_session_product']) : 'NA';
            $sub_smm_text = isset($_POST['sub_smm_text']) ? 
            sanitize_text_field($_POST['sub_smm_text']) : 'NA';
            if (strpos((string)$sub_smm_text, 'day'))
            $price_time_option_string = 'day';
            if (strpos((string)$sub_smm_text, 'week'))
            $price_time_option_string = 'week';
            if (strpos((string)$sub_smm_text, 'month'))
            $price_time_option_string = 'month';
            
            $product            = wc_get_product( $smm_session_product_id );
            $price_is_per       = smm_get_prop( $product, '_smapi_price_is_per' );
	        $price_time_option  = smm_get_prop( $product, '_smapi_price_time_option' );
	        if(empty($price_time_option_string))
	        $price_time_option_string = smapi_get_price_per_string( $price_is_per,$price_time_option);
	        
	        $price_time_option_string = $smm_session_data <2 ? 
	        " ".$price_time_option_string:
	        " ".$price_time_option_string.'s';
	        
            if( ( $data = WC()->session->get('subscribe_smm_data') ) )
            {
                
                $data[$smm_session_product_id]    = $smm_session_data ?? '';
                //data changes if the post carries a value$smm_session_data
                $data['price_time_option_string'] = ($price_time_option_string == "  ") ?
                $data['price_time_option_string'] : $price_time_option_string;
                $data['smm_session_data']         = $price_time_option_string;
                WC()->session->set( 'subscribe_smm_data', $data );
            }
			$response =   array( 'success'      => 1 ,
                                 'sub_smm_cycle'=> $price_time_option_string,
                                 'sub_smm_data' => $smm_session_data);
            echo wp_json_encode( $response );
            exit;
        }
        public function subscribe_arrow(){
            global $product;
            if ( SMMS_WC_Subscription()->is_subscription( $product->get_id() ) )
            {
            if ( isset(WC()->session) && ! WC()->session->has_session() ) {
                    WC()->session->set_customer_session_cookie( true );
                }
            // Set the session data
            WC()->session->set( 'subscribe_smm_data', array( $product->get_id() => 1,'price_time_option_string'=>'day' ) );    
            
            printf(
                '<div class="smm-counter"></div>
                    <div class="paginate left" smm_session_product= "%d"><i></i><i></i></div>
                    <div class="paginate right"><i></i><i></i></div>',
					esc_attr($product->get_id())
                    );
            }
            
        }
        
        public function smm_ts_woo_decimal_price( $formatted_price, $price, $decimal_places, $decimal_separator, $thousand_separator ) {
	        if(get_option('smmapi_sudolor') == 'yes'){
			$unit = number_format( intval( $price ), 0, $decimal_separator, $thousand_separator );
	        $decimal = substr(($price  - intval($unit)),2,$decimal_places);
	        
	        $decimalnew = explode(".",rtrim(sprintf('%f',floatval($price)),'0'));
			if ($decimalnew[1] == 0)
				$decimalnew[1] = "";
	        
	        return $unit . '<sup>'.esc_html($decimalnew[1]).'</sup>';
			}
			else return $price;
	        
        }
        /**
        * Enqueue our JS file
        */
        public function prefix_enqueue_scripts() {
         wp_register_script( 'prefix-script', trailingslashit( plugin_dir_url( __DIR__ ) ) . 'assets/js/smm-update-cart-item-ajax.js', array( 'jquery-blockui' ), time(), true );
         wp_register_script( 'var-attr-edit-script', trailingslashit( plugin_dir_url( __DIR__ ) ) . 'assets/js/smm-edit-var-attr-cart.js', array( 'jquery' ), time(), true );
         wp_register_style('smm_frontend',trailingslashit( plugin_dir_url( __DIR__ ) ).'assets/css/frontend.css',array(), '1', 'all' );

        wp_enqueue_script( 'prefix-script');
		wp_enqueue_script( 'var-attr-edit-script' );
		wp_enqueue_script( 'wc-add-to-cart-variation' );
        wp_enqueue_style( 'smm_frontend' );
		wp_localize_script(
         'prefix-script',
		 'prefix_vars',
         array(
         'ajaxurl' => admin_url( 'admin-ajax.php' )
         )
        );
		wp_localize_script( 
		 'var-attr-edit-script', 'attredit_vars', [
		 'ajax_url'          => admin_url( 'admin-ajax.php' ),
		 'nonce'             => wp_create_nonce( 'smmapi-security' ),
		 'update_text'       => esc_html__( 'Update', 'smm-api' ),
		 'cart_updated_text' => esc_html__( 'Cart updated.', 'smm-api' ),
							]
						);
        }
         // set the step for specific qty for smm items)

        public function smm_woocommerce_quantity_changes( $args, $product ) {
            // FIND FOR Variation qty attribute different from english
            $SMMS_QTYLANG = (get_option('smmqty_attribute') != 'Quantity')?
            get_option('smmqty_attribute'):'Quantity';
            if($SMMS_QTYLANG =='')$SMMS_QTYLANG = 'quantity';
            $product_name = $product->get_name();
                // get quantity from product name title
	            $smm_title_pack  = mb_substr($product_name, 0, 5);
	            $smm_result_num = filter_var($smm_title_pack, FILTER_SANITIZE_NUMBER_INT);
	            //quantity overrides the order qunatity
	            if (is_numeric($smm_result_num))
	            $quantity_from_title = $smm_result_num; 
	            
                if($product->is_type( 'simple' ) && ! is_cart() && empty($quantity_from_title)){
                $api_check_box_enabled = 
                smm_get_prop( $product, '_smapi_api' ) == "yes" ? 1 : null ;
                if ( $api_check_box_enabled == 1) {
                //SERVER ID AND ITEM ID TAKEN FROM PRODUCT OBJECT
	            $api_server_list_options_saved = 
	            smm_get_prop( $product, '_smapi_server_name_option' );
	            $api_item_list_options_saved   = 
	            smm_get_prop( $product, '_smapi_service_id_option' );
	            $order_item_meta =   
                get_post_meta( $api_server_list_options_saved, $api_item_list_options_saved, true );
	        
	            $order_item_meta_obj = json_decode($order_item_meta);
  
                $args['input_value'] = $order_item_meta_obj->f_min_order; // Start from this value (default = 1) 
                $args['max_value']   = $order_item_meta_obj->f_max_order; // Max quantity (default = -1)
                $args['min_value']   = $order_item_meta_obj->f_min_order; // Min quantity (default = 0)
                $args['step']        = $order_item_meta_obj->f_min_order; // Increment/decrement by this value (default = 1)
                }
                return $args;   
                }// end of simple product
                if($product->is_type( 'variable' ) && ! is_cart() && empty($quantity_from_title)){
                    foreach( $product->get_children() as $key => $variation_id ) {
                    // Get an instance of the WC_Product_Variation Object
                    $variation = wc_get_product( $variation_id );
                    $api_check_box_enabled = 
                    get_post_meta( $variation_id, 'variable_smm_api', true ) =='on' ? 1 : null;
                    // Get the variation attributes
                    $variation_attributes = $product->get_variation_attributes();
                        // Loop through each selected attributes
                        foreach($variation_attributes as $attribute_taxonomy => $term_slug ){
                            // Get product attribute name or taxonomy
                            $taxonomy = str_replace('attribute_', '', $attribute_taxonomy );
                            // The label name from the product attribute
                            $attribute_name = wc_attribute_label( $taxonomy, $product );
                            // The term name (or value) from this attribute
                            if( taxonomy_exists($taxonomy) ) {
                            $attribute_value = get_term_by( 'slug', $term_slug, $taxonomy )->name;
                            } else {
                                $attribute_value = 
                                $term_slug; // For custom product attributes
                                }
                            if(preg_match('/'.$SMMS_QTYLANG.'/i', $attribute_name))
                            $quantity_attribute = $attribute_value;
                        
                            }// end of Loop through each selected attributes
                    if ( $api_check_box_enabled == 1 && empty($quantity_attribute)) {
                    //SERVER ID AND ITEM ID TAKEN FROM VARIATIOON OBJECT 
                    $api_item_list_options_saved = 
                    get_post_meta( $variation_id, 'var_smapi_service_id_option', true );
                
			        $api_server_list_options_saved = 
			        get_post_meta( $variation_id, 'var_smapi_server_name_option', true );
			        $order_item_meta =   
                    get_post_meta( $api_server_list_options_saved, $api_item_list_options_saved, true );
	        
	                $order_item_meta_obj = json_decode($order_item_meta);
  
                    $args['input_value'] = 
                    $order_item_meta_obj->f_min_order ??''; // Start from this value (default = 1) 
                    $args['max_value'] = 
                    $order_item_meta_obj->f_max_order ?? ''; // Max quantity (default = -1)
                    $args['min_value'] = 
                    $order_item_meta_obj->f_min_order ?? ''; // Min quantity (default = 0)
                    $args['step'] = 
                    $order_item_meta_obj->f_min_order ?? ''; // Increment/decrement by this value (default = 1)
                    }
                    }
                        return $args;
                    }// end of product variations
                return $args;
        }
         
         
                /**
                 * Update selected variation id through ajax
                */
        public function prefix_selected_variation_id() {
        global $post;
        /*/ Do a nonce check
        if( ! isset( $_POST['security'] ) || ! wp_verify_nonce( $_POST['security'], 'woocommerce-cart' ) ) {
        wp_send_json( array( 'nonce_fail' => 1 ) );
        exit;
        }*/
        // Save the notes to the cart meta
        //$cart = WC()->cart->cart_contents;
        //update_post_meta( $variation_id, 'var_id_sel', "0" );
        $cart_id = isset($_POST['cart_id']) ? 
            sanitize_text_field($_POST['cart_id']) : 'NA';
        
        update_post_meta( $cart_id, 'var_id_sel', $cart_id );
	    //$notes = $_POST['cart_id'];
	    //$var_selected['var_id_sel'] == $cart_id;
	    
	    //$cart_item = $cart[$cart_id];
	    //$cart_item['notes'] = $notes;
	    //WC()->cart->cart_contents['var_id_sel'] = $cart_item;
         wp_send_json( array( 'success' => 1 , 'text' => $cart_id) );
         exit;
        }
        // geting customer input from cart page
        public function get_custom_input_data(){
         $cart_id = isset($_POST['cartkey']) ? 
            sanitize_text_field($_POST['cartkey']) : 'NA';
          
         $customer_data = isset($_POST['customer_data']) ? 
            sanitize_text_field($_POST['customer_data']) : 'NA';
         
         $cart_updated_item = WC()->cart->get_cart_item( $cart_id );
         $cart_item_meta = WC()->cart->cart_contents;
         $product_id = $cart_updated_item['product_id'];
         foreach ($cart_item_meta as $key => $item) {
            if($key == $cart_id){
             //file_put_contents(plugin_dir_path( __FILE__ )."check.txt", $key.PHP_EOL,FILE_APPEND);
            $cart_qty = $item['quantity'];
            $variation_id = $item['variation_id'];
            $variation = $item['variation'];
            WC()->cart->remove_cart_item($key);
            }
         }
		 
		 $custom_input_data = array('smm-cfwc-title-field' => $customer_data);
         WC()->cart->add_to_cart( $cart_updated_item['product_id'],$cart_qty, $variation_id, $variation, 
         $custom_input_data );
         wp_send_json( array( 'success' => 1 , 'text' => "Cart Item updated") );
         exit;  
        }
        

        /** 
        * Display custom field on the front end
        * @since 1.0.0
        */
        public function smm_cfwc_display_custom_field() {
                global $post;
                // Check for the custom field value
                $product = wc_get_product( $post->ID );
                if($product->is_type( 'simple' )){
                $title = $product->get_meta( 'smm_custom_text_field_title' ) ? $product->get_meta( 'smm_custom_text_field_title' ):'title is empty';
                $input_text_box_radio_saved   = smm_get_prop( $product, 'locate_input_box' ); 
                // FIND FOR SMM API CHECK BOX
                $api_check_box_enabled = 
                smm_get_prop( $product, '_smapi_api' ) == "yes" ? 1 : null ;
                if( $title && $input_text_box_radio_saved == 'product' && $api_check_box_enabled == 1) {
                // Only display our field if we've got a value for the field title
				//<div class="smm_form__group field">class="smm-cfwc-custom-field-wrapper"                
                //<label for="smm-form" class="smm_form__label" data-id=%s >%s</label>
                printf(
                '<div class="smm_form__group field">
				<input id="smm-form" class="smm_form__field" placeholder="%s" name="smm-cfwc-title-field" value="">
				<label for="smm-form" class="smm_form__label">%s</label>				
				</div>',
                    esc_html($title),esc_html($title)
                    );
                    }
                }
                if($product->is_type( 'variable' )){
                // Loop through the variation IDs

                    foreach( $product->get_children() as $key => $variation_id ) {
                    // Get an instance of the WC_Product_Variation Object
                    $variation = wc_get_product( $variation_id );
            
                    // Get meta of variation ID
                    $var_smm_customer_input_field_label = $variation->get_meta( 'var_smm_customer_input_field_label' );
                    $api_check_box_enabled = get_post_meta( $variation_id, 'variable_smm_api', true ) =='on' ? 1 : null;
                    $locate_input_box = get_post_meta( $variation_id, 'locate_input_box', true );
                    
                    if($api_check_box_enabled == 1 && $locate_input_box == "product" && $var_smm_customer_input_field_label != ""){
                        // Output
                        
                    printf(
                    '<div class="var_smm_customer_input_field_label var_smm_customer_input_field_label-%1$d" style="margin-bottom:10px;">
					<label for="var_smm_customer_input_field_text_%3$d" style="display:inline-block;">%2$s</label>
					<input type="text" id="var_smm_customer_input_field_text_%3$d" name="var_smm_customer_input_field_text[]" value=""></div>',
					esc_attr($variation_id),						
                    esc_html( $var_smm_customer_input_field_label .' * : ') ,
					esc_attr($variation_id)
                    );
                    }
                    }//end of foreach
                    
                    //script is on smm-update-cart-item-ajax.js
                    
                    }
            }
            /**
            * Validate the text field
            * @since 1.0.0
            * @param Array 		$passed					Validation status.
            * @param Integer   $product_id     Product ID.
             * @param Boolean  	$quantity   		Quantity
              */
            public function smm_cfwc_validate_custom_field( $passed, $product_id, $quantity ) {
    
                global $wpdb;
 	            $product = wc_get_product( $product_id );
                if($product->is_type( 'simple' )){
 	            $smm_api_checked   = smm_get_prop( $product, '_smapi_api' );   
                smm_get_prop( $product, '_smapi_price_is_per' );
                $input_text_box_radio_saved   = smm_get_prop( $product, 'locate_input_box' ); 
                if( empty( $_POST['smm-cfwc-title-field'] ) && $smm_api_checked =='yes' && $input_text_box_radio_saved == 'product') {
                // Fails validation
                $passed = false;
                wc_add_notice( __( 'Please enter a value into the text field marked! *', 'smm-api' ), 'error' );
                    }
                
                }
                if($product->is_type( 'variable' )){
                    
                    
                    
                    foreach( $product->get_children() as $key => $variation_id ) {
                        $selected = get_post_meta( $variation_id, 'var_id_sel', true );
                        update_post_meta( $variation_id, 'var_id_sel', "" );
                      
                        if($selected == $variation_id){
                        
                        // Get an instance of the WC_Product_Variation Object
                        $variation = wc_get_product( $variation_id );
            
                        // Get meta of variation ID
                    
                        $api_check_box_enabled = get_post_meta( $variation_id, 'variable_smm_api', true ) =='on' ? 1 : null;
                        $locate_input_box = get_post_meta( $variation_id, 'locate_input_box', true );
                    //file_put_contents(plugin_dir_path( __FILE__ )."check1.txt", serialize($_POST['var_smm_customer_input_field_text'])." check box = ".$api_check_box_enabled." varidclicked = ".$selected." foreach varid = ".$variation_id." locate = ".$locate_input_box." arryfilt = ".array_filter($_POST['var_smm_customer_input_field_text'] ),FILE_APPEND);
                 
                        // check if text field is empty for array
                    
                            if( empty( array_filter(wc_clean($_POST['var_smm_customer_input_field_text']) )) && $api_check_box_enabled == 1 && 
                            $locate_input_box == 'product'){
                    
                            // Fails validation
                            $passed = false;
                             wc_add_notice( __( 'Please enter a value into the text field marked for variation! *', 'smm-api' ), 'error' );    
                    
                            }
                        }
                    }//end of foreach
                }//end of product variable
                return $passed;
                }
            /**
             * Add the text field as item data to the cart object
             * @since 1.0.0
             * @param Array 	$cart_item_data Cart item meta data.
             * @param Integer   $product_id     Product ID.
             * @param Integer   $variation_id   Variation ID.
             * @param Boolean  	$quantity   		Quantity
             */
            public function smm_cfwc_add_custom_field_item_data( $cart_item_data, $product_id, $variation_id, $quantity ) {
            if( ! empty( $_POST['smm-cfwc-title-field'] ) ) {
            // Add the item data to cart
             $cart_item_data['smm-cfwc-title-field'] = isset($_POST['smm-cfwc-title-field']) ? 
            sanitize_text_field($_POST['smm-cfwc-title-field']) : 'NA';
             $product = wc_get_product($product_id); // The WC_Product Object
                    $price = (float) $product->get_price();
                    if( ( $data = WC()->session->get('subscribe_smm_data') ) )
            {
                $smm_session_data = $data[$product_id] ;
                
                
                $cart_item_data['subscribe_price'] = $smm_session_data;
            //file_put_contents(plugin_dir_path( __FILE__ )."check.txt", $price." ".$quantity.' '.$smm_session_data.' '.$cart_item_data['subscribe_price'].PHP_EOL,FILE_APPEND);
             
                
            }
                    
             }
			 
             //record only if value is a string for variations $result = array_filter( $array, 'strlen' );
             if( @is_array($_POST['var_smm_customer_input_field_text']) && ! empty( 
                 array_filter($_POST['var_smm_customer_input_field_text'] )) ) {
                 foreach ($_POST['var_smm_customer_input_field_text'] as $item)
                 if($item != '')
                 $cart_item_data['smm-cfwc-title-field'] =  sanitize_text_field($item);// phpcs:ignore
                 
             }
             
             return $cart_item_data;
            }
            /**
            * Display the custom field value in the cart
             * @since 1.0.0
             */
            public function smm_cfwc_cart_item_name( $name, $cart_item, $cart_item_key ) {
            // file_put_contents(plugin_dir_path( __FILE__ )."check.txt", serialize($cart_item));
            $product = $cart_item['data'];
			$sub_text_display = "Single Order";
            if( ( $data = WC()->session->get('subscribe_smm_data') ) ){
            $smm_session_data           = isset($data[$product->get_id()])?$data[$product->get_id()]:'';//frequency
            $price_time_option_string   = $data['price_time_option_string'];
            $sub_text_display           = $smm_session_data != 1 ?
            "Subsciption added for ".$smm_session_data." ".$price_time_option_string :
                "Single Order";
                    }
			if($product->is_type( 'simple' )){
            $title = $product->get_meta( 'smm_custom_text_field_title' ) ? $product->get_meta( 'smm_custom_text_field_title' ):'title is empty';
            $api_check_box_enabled = 
                smm_get_prop( $product, '_smapi_api' ) == "yes" ? 1 : null ;
             $input_text_box_radio_saved   = smm_get_prop( $product, 'locate_input_box' );
           
                if( isset( $cart_item['smm-cfwc-title-field'] ) && $input_text_box_radio_saved == "product" ) {
                    $name .= sprintf(
                    '<p class="smm-custom-input">Customer Input Item : %s</p>
                    <p class="smm-custom-product">%s</p>',
                    esc_html( $cart_item['smm-cfwc-title-field'] ),
                    esc_html($sub_text_display)
                    );
                    }
                elseif(isset( $cart_item['smm-cfwc-title-field'] ) && $input_text_box_radio_saved =='checkout'){
                $name .= sprintf('<p class="smm-custom-input">%s</p>
                <div class="smm_form__group field">                
                <label for="smm-form" class="smm_form__label" data-desc=%s data-id=%s >%s</label>
                </div>', esc_html($sub_text_display),
                                @esc_html( $cart_item['smm-cfwc-title-field'] ),esc_attr($cart_item_key),esc_html($title)
                                ); 
                return $name;
                    }
                elseif($api_check_box_enabled ==1 && $input_text_box_radio_saved =='checkout'){
                 
                $name .= sprintf('<p class="smm-custom-input">%s</p>
                <div class="smm_form__group field">                
                <label for="smm-form" class="smm_form__label" data-id=%s >%s</label>
                </div>', esc_html( $sub_text_display),esc_attr($cart_item_key),esc_html($title)
                                ); 
                    }
                
                 
                return $name;
			}
			// Change item name only if is a product variation
			if( $cart_item['data']->is_type('variation') ){
				//$product = $cart_item['data'];
				//foreach( $cart_item['data']->get_children() as $key => $variation_id ) {
                    // Get an instance of the WC_Product_Variation Object
                    //$variations = $cart_item['variation'];//attributes only
					//$product_id  = $cart_item['product_id'];
					//$current_product = new  WC_Product_Variable($product_id);
					//$variations = $current_product->get_available_variations();
					$variation_id = $cart_item['variation_id'];
					$variation = wc_get_product( $variation_id );
            
                    // Get meta of variation ID
                    $var_smm_customer_input_field_label = $variation->get_meta( 'var_smm_customer_input_field_label' );
                    $api_check_box_enabled = get_post_meta( $variation_id, 'variable_smm_api', true ) =='on' ? 1 : null;
                    $locate_input_box = get_post_meta( $variation_id, 'locate_input_box', true );
                    
                    
				
                   if(isset( $cart_item['smm-cfwc-title-field'] ) &&
					$locate_input_box == "cart" &&
					$api_check_box_enabled == 1){
                        // Output
                    
                    // HERE customize item name
					$name .= sprintf('<p class="smm-custom-input">%s</p>
					<div class="smm_form__group field">                
					<label for="smm-form" class="smm_form__label" data-desc=%s data-id=%s >%s</label>
					</div><span class="smmapi-edit" id="%s">%s</span>', esc_html($sub_text_display),
                                @esc_attr( $cart_item['smm-cfwc-title-field'] ),
								esc_attr($cart_item_key),esc_html($var_smm_customer_input_field_label),
								esc_attr($cart_item_key),esc_html__( 'Edit', 'smm-api' )
                                ); 
					}
					elseif($api_check_box_enabled == 1 &&
					$locate_input_box == "cart" &&
					$var_smm_customer_input_field_label != ""){
					
					$name .= sprintf('<p class="smm-custom-input">%s</p>
					<div class="smm_form__group field">                
					<label for="smm-form" class="smm_form__label" data-id=%s >%s</label>
					</div><span class="smmapi-edit" id="%s">%s</span>', 
					esc_html($sub_text_display),esc_attr($cart_item_key),esc_html($var_smm_customer_input_field_label),
					esc_attr($cart_item_key),esc_html__( 'Edit', 'smm-api' )
                                );
					}	
				return $name;
			}			
		}
		public function ajax_load_smm_variation() {
					check_ajax_referer( 'smmapi-security', 'nonce' );

					$current_key          = sanitize_key( $_POST['current_key'] );
					$cart_item            = WC()->cart->get_cart_item( $current_key );
					$variable_product     = wc_get_product( $cart_item['product_id'] );
					$selected_variation   = $cart_item['variation'];
					$selected_qty         = (float) $cart_item['quantity'];
					$available_variations = $variable_product->get_available_variations();
					$attributes           = $variable_product->get_variation_attributes();
					$attribute_keys       = array_keys( $attributes );
					// Get meta of variation ID
					$sub_text_display = "Single Order";
					if( ( $data = WC()->session->get('subscribe_smm_data') ) ){
					$smm_session_data           = isset($data[$product->get_id()])?$data[$product->get_id()]:'';//frequency
					$price_time_option_string   = $data['price_time_option_string'];
					$sub_text_display           = $smm_session_data != 1 ?
					"Subsciption added for ".esc_html($smm_session_data)." ".esc_html($price_time_option_string) :
					"Single Order";
                    }
					$variation_id = $cart_item['variation_id'];
					$variation = wc_get_product( $variation_id );
                    $var_smm_customer_input_field_label = $variation->get_meta( 'var_smm_customer_input_field_label' );
                    $api_check_box_enabled = get_post_meta( $variation_id, 'variable_smm_api', true ) =='on' ? 1 : null;
                    $locate_input_box = get_post_meta( $variation_id, 'locate_input_box', true );
					$update_button_text   = esc_html__( 'Update', 'smm-api' );
					$cancel_button_text   = esc_html__( 'Cancel', 'smm-api' );
					?>
                    <tr class="smmapi-new-item smmapi-new-item-<?php echo esc_attr( $current_key ); ?>">
                        <td colspan="100%">
                            <table class="smmapi-editor">
                                <tr>
                                    <td class="smmapi-thumbnail">
                                        <div class="smmapi-thumbnail-ori"><?php echo esc_html($variable_product->get_image( 'full' )); ?></div>
                                        <div class="smmapi-thumbnail-new"></div>
                                    </td>
                                    <td class="smmapi-info">
                                        <h4 class="smmapi-title">
											<?php echo esc_html($variable_product->get_name()); ?>
                                        </h4>
                                        <form class="variations_form cart" method="post" enctype='multipart/form-data' data-product_id="<?php echo esc_attr(absint( $variable_product->get_id() )); ?>" data-product_variations="<?php echo esc_attr(htmlspecialchars( wp_json_encode( $available_variations )) ); ?>">
                                            <table class="variations">
                                                <tbody>
												<?php foreach ( $attributes as $attribute_name => $options ) { ?>
                                                    <tr>
                                                        <td class="label">
                     <label for="<?php echo esc_attr( sanitize_title( $attribute_name ) ); ?>">
					<?php echo esc_html(wc_attribute_label( $attribute_name )); ?>
                                                            </label>
                                                        </td>
                                                        <td class="value">
					<?php
					$selected = $selected_variation[ 'attribute_' . 
					esc_attr(sanitize_title( $attribute_name )) ];															wc_dropdown_variation_attribute_options( [
																'options'   => $options,
																'attribute' => $attribute_name,
																'product'   => $variable_product,
																'selected'  => $selected,
															] );

				echo end( $attribute_keys ) === $attribute_name ? esc_attr(apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . esc_html__( 'Clear', 'smm-api' ) . '</a>' )) : '';
															?>
                                                        </td>
                                                    </tr>
												<?php } ?>
                                                </tbody>
                                            </table>
                                            <div class="single_variation_wrap">
                                                <div class="woocommerce-variation single_variation">
                                                    <div class="woocommerce-variation-description"></div>
                                                    <div class="woocommerce-variation-price">
                                                        <span class="price"></span>
                                                    </div>
                                                    <div class="woocommerce-variation-availability"></div>
                                                </div>
                                                <div class="woocommerce-variation-add-to-cart variations_button woocommerce-variation-add-to-cart-enabled">
                                                    <div class="smmapi-actions">
														<?php
														woocommerce_quantity_input(
															[
																'min_value'   => apply_filters( 'woocommerce_quantity_input_min', $variable_product->get_min_purchase_quantity(), $variable_product ),
																'max_value'   => apply_filters( 'woocommerce_quantity_input_max', $variable_product->get_max_purchase_quantity(), $variable_product ),
																'input_value' => $selected_qty ? wc_stock_amount( wp_unslash( $selected_qty ) ) : $variable_product->get_min_purchase_quantity(),
															], $variable_product
														);
					/*if(isset( $cart_item['smm-cfwc-title-field'] ) &&
					$locate_input_box == "cart" &&
					$api_check_box_enabled == 1){
                        // Output
                    
                    // HERE customize item name
					printf('<p class="smm-custom-input">%s</p>
					<div class="smm_form__group field">                
					<label for="smm-form" class="smm_form__label" data-desc=%s data-id=%s >%s</label>
					</div>', esc_html($sub_text_display),
                                @esc_html( $cart_item['smm-cfwc-title-field'] ),
								esc_attr($cart_item_key),esc_html($var_smm_customer_input_field_label)
	
                                ); 
					}
					elseif($api_check_box_enabled == 1 &&
					$locate_input_box == "cart" &&
					$var_smm_customer_input_field_label != ""){
					
					printf('<p class="smm-custom-input">%s</p>
					<div class="smm_form__group field">                
					<label for="smm-form" class="smm_form__label" data-id=%s >%s</label>
					</div>', 
					esc_html($sub_text_display),esc_attr($cart_item_key),esc_html($var_smm_customer_input_field_label)
					
                                );
					}*/
														?>
														<input type="text" class="customer_input" name="var_smm_customer_input_field_text[]" value="<?php echo esc_attr(htmlentities( $cart_item['smm-cfwc-title-field']) ); ?>"/>
                                                        <button type="submit" class="single_add_to_cart_button smmapi-update button">
															<?php echo esc_html( $update_button_text ); ?>
                                                        </button>
                                                        <span class="button button-primary smmapi-cancel" data-key="<?php echo esc_attr( $current_key ); ?>"><?php echo esc_html( $cancel_button_text ); ?></span>
                                                    </div>
                                                    <input type="hidden" class="product_thumbnail" value="<?php echo esc_attr(htmlentities( $variable_product->get_image()) ); ?>"/>
                                                    <input type="hidden" name="add-to-cart" value="<?php echo absint( $variable_product->get_id() ); ?>"/>
                                                    <input type="hidden" name="product_id" value="<?php echo absint( $variable_product->get_id() ); ?>"/>
                                                    <input type="hidden" name="variation_id" class="variation_id" value="0"/>
                                                    <input name="old_key" class="old_key" type="hidden" value="<?php echo esc_attr( $current_key ); ?>"/>
					
                                                </div>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
					<?php
					wp_die();
				}

				public function ajax_update_smm_variation() {
					check_ajax_referer( 'smmapi-security', 'nonce' );

					$form_data = sanitize_text_field( $_POST['form_data'] );
					$old_key   = sanitize_key( $_POST['old_key'] );
					$customer_input = sanitize_text_field( $_POST['customer_input'] );
					parse_str( $form_data, $data );

					if ( ! empty( $data['variation_id'] ) && ( absint( $data['variation_id'] ) > 0 ) ) {
						$variable_product = wc_get_product( $data['variation_id'] );

						if ( $variable_product ) {
							$max_quantity = $variable_product->get_max_purchase_quantity();

							if ( isset( $data['quantity'] ) && ( $max_quantity < 0 || (float) $data['quantity'] <= $max_quantity ) ) {
								// remove old variation when ready to add new variation
								WC()->cart->remove_cart_item( $old_key );
							}
						}
					}
					//file_put_contents(plugin_dir_path( __FILE__ )."check.txt", serialize($form_data).$customer_input);
					
					$custom_input_data = array('smm-cfwc-title-field' => $customer_input);
					WC()->cart->add_to_cart( $data['product_id'],$data['quantity'], $data['variation_id'], $variation, 
					$custom_input_data );
					//wp_redirect( sprintf( '%s', wc_get_cart_url() ) );
					wp_die();
				}
		/**
		 * @param $price
		 * @param $cart_item
		 * @param $cart_item_key
		 *
		 * @return string
		 */
		public function change_price_in_cart_html(  $price, $cart_item, $cart_item_key ) {

			$product_id = ! empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : $cart_item['product_id'];

            if ( !SMMS_WC_Subscription()->is_subscription( $product_id ) ) {
                
                return $price;
            }

            $product = $cart_item['data'];

            $price_is_per = smm_get_prop( $product, '_smapi_price_is_per' );
            $price_time_option = smm_get_prop( $product, '_smapi_price_time_option' );
			$price_is_per = smapi_get_price_per_string( $price_is_per,$price_time_option);
            if( ( $data = WC()->session->get('subscribe_smm_data') ) ){
            $smm_session_data           = $data[$product->get_id()];//frequency
            $price_time_option_string   = $data['price_time_option_string'];
            }
            //remove s for making singular
            if($smm_session_data != 1)
            $price .=  ' / '.esc_html(substr($price_time_option_string, 0, -1)); 
            

            return $price;
            
        }
    }
}

/**
 * Unique access to instance of SMAPI_Subscription_Cart class
 *
 * @return \SMAPI_Subscription_Cart
 */
function SMAPI_Subscription_Cart() {
    return SMAPI_Subscription_Cart::get_instance();
}