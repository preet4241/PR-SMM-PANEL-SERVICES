<?php

if ( !defined( 'ABSPATH' ) || !defined( 'SMMS_SMAPI_VERSION' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Implements SMAPI_Subscription_Cron Class
 *
 * @class   SMAPI_Subscription_Cron
 * @package SMMS WooCommerce Subscription
 * @since   1.0.0
 * @author  SMMS
 */
if ( !class_exists( 'SMAPI_Subscription_Cron' ) ) {

    class SMAPI_Subscription_Cron {

	    /**
	     * Single instance of the class
	     *
	     * @var \SMAPI_Subscription_Cron
	     */
	    protected static $instance;
		// For cron shedule custom
		public  $minute 		 = 	60;
		public  $hourly 		 = 	3600;
		Public  $daily  		 =	86400;
		public  $weekly 		 =	604800;
		public  $monthly		 =	2628000;
		
		public  $smm_cron		 = 	array();
		public $smm_api_pro		 =  array();
		public $smm_api_ids		 =  array();
	    /**
	     * Returns single instance of the class
	     *
	     * @return \SMAPI_Subscription_Cron
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
			
				$smm_cron_set = json_decode(get_option('smm_cron'),true);
				if(is_array($smm_cron_set))//Check any cron is set or not
				$this->smm_cron = $smm_cron_set;//time str to arr
				add_action( 'smapi_renew_cron', array( $this, 'set_cron' ), 30 );				
	    		add_action( 'smapi_renew_orders', array( $this, 'renew_orders' ), 30 );
				add_action( 'smapi_price_update', array( $this, 'set_cron_price_update' ), 30 );
				add_action( 'smm_auto_price', array( $this, 'smm_auto_price_cron' ), 30 );
				//add_action( 'woocommerce_loaded', array( $this, 'smm_product_price_update' ), 30 );
			if (get_option( 'smapi_cron_job' ) == 'yes'){
				
				do_action('smapi_renew_cron');
				
				}
			else{
				smm_cron_deactivate();
			}
					
			// check cron schedule enabled in privacy page
			if (get_option( 'smapi_price_update' ) == 'yes'){
				
				if ( isset($this->smm_cron) &&  strtotime($this->smm_cron['smm_auto_price']) < time()){
				//update_option( 'active_cron_servers', gmdate( 'Y-m-d H:i:s',current_time('timestamp')));
				$ve = get_option( 'gmt_offset' ) > 0 ? '+' : '-';
				//$time_start = strtotime( '00:00 ' . $ve . get_option( 'gmt_offset' ) . ' HOURS' );
				if(strtotime($this->smm_cron['smm_auto_price']) == 0)
				{	
					$this->smm_cron['smm_auto_price'] = gmdate( 'Y-m-d H:i:s', time() + $this->minute );
					update_option( 'smm_cron', json_encode($this->smm_cron));
				}
				else{
					do_action('smm_auto_price');
					// Next Scchedule in sting format and update db
					
				}
				}					
				}
				else{
				$this->smm_cron['smm_auto_price'] = 0 ;
				update_option( 'smm_cron', json_encode($this->smm_cron));
				update_option( 'active_cron_servers', "N/A" );
			}
	    	
            
	    	if ( smms_check_privacy_enabled( true ) ) {
				
			    add_action( 'smapi_trash_pending_subscriptions', array( $this, 'smapi_trash_pending_subscriptions' ) );
			    add_action( 'smapi_trash_cancelled_subscriptions', array( $this, 'smapi_trash_cancelled_subscriptions' ) );
		    }
			
	    }

		public function set_cron_price_update() {

		    $ve = get_option( 'gmt_offset' ) > 0 ? '+' : '-';
		    $time_start = strtotime( '00:00 ' . $ve . get_option( 'gmt_offset' ) . ' HOURS' );
            //do_action('smm_auto_price', $this);
		    if ( ! wp_next_scheduled( 'smm_auto_price' ) ) {
			   wp_schedule_event( time(), 'hourly', 'smm_auto_price' );
			   //do_action('smm_auto_price', $this);
			    //smapi_renew_orders calls renew order
                }
		}

	    public function set_cron() {

		    $ve = get_option( 'gmt_offset' ) > 0 ? '+' : '-';
		    $time_start = strtotime( '00:00 ' . $ve . get_option( 'gmt_offset' ) . ' HOURS' );
            
		    if ( ! wp_next_scheduled( 'smapi_renew_orders' ) ) {
			   wp_schedule_event( $time_start, 'hourly', 'smapi_renew_orders' );
			   do_action('smapi_renew_orders', $this);
			    //smapi_renew_orders calls renew order
                }

		    if ( smms_check_privacy_enabled(true) ) {
			    $trash_pending = get_option( 'smapi_trash_pending_subscriptions' );
			    if ( isset( $trash_pending['number'] ) && ! empty( $trash_pending['number'] ) && !wp_next_scheduled( 'smapi_trash_cancelled_subscriptions' ) ) {
				    wp_schedule_event( $time_start, 'daily', 'smapi_trash_pending_subscriptions' );
			    }

			    $trash_cancelled = get_option( 'smapi_trash_cancelled_subscriptions' );
			    if ( isset( $trash_cancelled['number'] ) && ! empty( $trash_cancelled['number'] ) && !wp_next_scheduled( 'smapi_trash_cancelled_subscriptions' ) ) {
				    wp_schedule_event( $time_start, 'daily', 'smapi_trash_cancelled_subscriptions' );
			    }
		    }
	    }


	    /**
	     * Renew Order
	     *
	     * Create new order for active or in trial period subscription
	     *
	     * @author sam softnwords
	     */
	    public function renew_orders() {

		    global $wpdb;

		    $to         = current_time('timestamp') + 86400;
            $to_time    = current_time('timestamp');
		    

		    $subscriptions = $wpdb->get_row( $wpdb->prepare( "SELECT ID,post_content,post_title,post_excerpt FROM {$wpdb->prefix}posts 
		         WHERE post_type  = %s 
                 AND post_status  = 'publish'
                 AND post_excerpt > 0
                 AND post_content < %d
                 GROUP BY ID ORDER BY ID DESC
                ", 'smapi_subscription', $to_time ));
		  //  file_put_contents(plugin_dir_path( __FILE__ )."check.txt",serialize($subscriptions).PHP_EOL);
	
		   
		    if ( ! empty( $subscriptions ) ) {
			    foreach ( $subscriptions as $subscription ) {
			        
			    	$sbs = smapi_get_subscription( $subscription->ID );
				    $renew_order = $sbs->subscription_meta_data['renew_order'];
				    $can_be_renewed = 0 ;
				    $sub_status = 0;
				    if($subscription->post_title == 'active' ||
				    $subscription->post_title == 'renew' ||
				    $subscription->post_title == 'renewed')
				    $sub_status = 1;
				    if ($subscription->post_content < current_time('timestamp'))
				    $can_be_renewed = 1;
					 
				    if ( $renew_order == 1 && $can_be_renewed == 1 && $sub_status == 1) {
				        
				    // subscription order renews if it has created
                    $renew_subscription = $sbs->renew_subscription($subscription->ID);
                     
                    // New Order is creted in woocomerce order list   
					$order_id = SMAPI_Subscription_Order()->renew_order( $subscription->ID );
					
					// New Api trigger based on order data if the subscription status is active.renew/renewed
					// subsciption update is called if new api order is created at server   
				
				    SMAPI_Subscription_Order()->smm_api_order_trigger($order_id);
					    
				    }
			    }
		    }
	    }

	    /**
	     * Trash pending subscriptions after a specific time.
	     *
	     * @since 1.4.0
	     * @author sam softnwords
	     */
	    public function smapi_trash_pending_subscriptions() {
		    global $wpdb;
		    $trash_pending = get_option( 'smapi_trash_pending_subscriptions' );
		    if ( ! isset( $trash_pending['number'] ) || empty( $trash_pending['number'] ) ) {
			    return;
		    }

		    $time = strtotime( '-' . $trash_pending['number'] . ' ' . $trash_pending['unit'] );

		    $subscriptions = $wpdb->get_results( $wpdb->prepare( "SELECT smapi_p.ID FROM {$wpdb->prefix}posts as smapi_p
                 INNER JOIN  {$wpdb->prefix}postmeta as smapi_pm ON ( smapi_p.ID = smapi_pm.post_id )
                 WHERE ( smapi_pm.meta_key='status' AND  smapi_pm.meta_value = 'pending' )
                 AND smapi_p.post_type = %s
                 AND smapi_p.post_status = 'publish'
                 AND smapi_p.post_date < %s 
                 GROUP BY smapi_p.ID ORDER BY smapi_p.ID DESC
                ", 'smapi_subscription', gmdate( 'Y-m-d H:i:s', $time ) ) );


		    if ( ! empty( $subscriptions ) ) {
			    foreach ( $subscriptions as $subscription ) {
				    $subscription_id = $subscription->ID;
				    wp_trash_post( $subscription_id );
				    do_action( 'smapi_subscription_trashed', $subscription_id );
			    }
		    }

	    }

	    /**
	     * Trash cancelled subscriptions after a specific time.
	     *
	     * @since 1.4.0
	     * @author sam softnwords
	     */
	    public function smapi_trash_cancelled_subscriptions() {
		    global $wpdb;
		    $trash_cancelled = get_option( 'smapi_trash_cancelled_subscriptions' );
		    if ( ! isset( $trash_cancelled['number'] ) || empty( $trash_cancelled['number'] ) ) {
			    return;
		    }

		    $time = strtotime( '-' . $trash_cancelled['number'] . ' ' . $trash_cancelled['unit'] );

		    $subscriptions = $wpdb->get_results( $wpdb->prepare( "SELECT smapi_p.ID FROM {$wpdb->prefix}posts as smapi_p
                 INNER JOIN  {$wpdb->prefix}postmeta as smapi_pm ON ( smapi_p.ID = smapi_pm.post_id )
                 INNER JOIN  {$wpdb->prefix}postmeta as smapi_pm2 ON ( smapi_p.ID = smapi_pm2.post_id )
                 WHERE ( smapi_pm.meta_key='status' AND  smapi_pm.meta_value = 'cancelled' )
                 AND smapi_p.post_type = %s
                 AND smapi_p.post_status = 'publish'
                 AND ( smapi_pm2.meta_key='cancelled_date' AND  smapi_pm2.meta_value  < %d )
                 GROUP BY smapi_p.ID ORDER BY smapi_p.ID DESC
                ", 'smapi_subscription', $time ) );

		    if ( ! empty( $subscriptions ) ) {
			    foreach ( $subscriptions as $subscription ) {
				    $subscription_id = $subscription->ID;
				    wp_trash_post( $subscription_id );
				    do_action( 'smapi_subscription_trashed', $subscription_id );
				    SMMS_WC_Activity()->add_activity( $subscription_id, 'trashed', 'success', 0, __( 'The subscription was been trashed after the specific duration because was in cancelled status.', 'smm-api' ) );
			    }
		    }

	    }
		
		// Auto price Update for SMM products if cron enabled
		public function smm_auto_price_cron($price_update){
			global $wpdb;
			$smm_server_post_id 	= array();
			$smm_api_items			= array();
			$table_api_ser			= array();
			$smm_api_items_str 		='';
			$smm_api_item_meta_raw 	= array();
			
			$_smm_item 			= array();
			$args = array(							
							'post_type' 	 => 'smapi_server',
							'post_status' 	 => 'any',
							'posts_per_page' => -1,							
							);							
							 $posts = get_posts( $args );//get all posts type smapi_server
			if ( $posts != NULL ){
								
				foreach($posts as $post) {
				// get post ID is server Ids
				$smm_api_items_str = get_post_meta( $post->ID, '_parameter_handle', true );
				$smm_api_items = json_decode($smm_api_items_str,true);// STR  TO ARR
				if($smm_api_items['smm_price_update'] === "1"){ //check price update is enabled
				// load products that uses api items
				$update_cycle = $smm_api_items['api_price_update'];
				switch ($update_cycle) {
								case "daily":
								$cron_cycle = 86400;
								break;
								case "weekly":
								$cron_cycle = 604800;								
								break;
								case "monthly":								
								$cron_cycle = 2628000;
								break;
								default:
								$cron_cycle = 60;
								}
				// update cron cyclone based on smm api server parameter handle settings
				$this->smm_cron['smm_auto_price'] = gmdate( 'Y-m-d H:i:s', time() + $cron_cycle );
				update_option( 'smm_cron', json_encode($this->smm_cron));
				
					$new_api_item = new SMAPI_Api($post->ID);   // get api server data
					$api_item_object = $new_api_item->services();
					if($api_item_object == null) continue; //if no object then goto next iteration
					
					$table_api_service = (array)$api_item_object ?? null; //null use prevent php warning
					
					
					foreach($table_api_service as $key=>$table_rows){ // getting only price from api server
					$table_api_serv[$table_api_service[$key]->service] = $table_api_service[$key]->rate;
					}
					//file_put_contents(plugin_dir_path( __FILE__ )."check.txt",json_encode($table_api_serv));
					
					//$table_api_ser = json_decode(file_get_contents(plugin_dir_path( __FILE__ )."check.txt"),true);
					$this->smm_api_ids = $table_api_ser;
					
					$smm_api_item_meta_raw = get_post_meta( $post->ID);
					$_product_use_api = array();				
					foreach ( $smm_api_item_meta_raw as $key => $value ) {
            	
              			 
        				if(substr($key, 0, 6) == '_item_'){  								
														
						$find_service = substr($key, 6); //get value after_item_ 
														 
						$_smm_item = json_decode(reset($value),true);//reset to get first element
						
						// check local site price and server price from api query
						$_smm_item_price = $table_api_ser[$find_service] ?? $_smm_item["f_item_price"];
							if($_smm_item["f_item_price"] != $_smm_item_price )
							{ //changed price goto DB for smm api items
								$_smm_item["f_item_price"] = $_smm_item_price ;
								update_post_meta( $post->ID,$key,wp_json_encode($_smm_item,JSON_FORCE_OBJECT | JSON_NUMERIC_CHECK));								
							$this->smm_api_pro = $this->smm_api_products($post->ID, $key);
							}
							
						
						}
           			}
					$_item_price_updated = array();//stops undefined warning in foreach loop
					foreach($this->smm_api_pro as $smm_api_prod){
						
						//updates the price of products
						$_updated_product = $this->smm_set_product_price($smm_api_prod) ?? null;
						if(!empty($_updated_product))
						array_push($_item_price_updated, $_updated_product);
						
					}
					$_server_ids = array();
					if (!array_key_exists($post->ID, $smm_server_post_id))// ✅ Prevents "Undefined array key" error
					array_push($smm_server_post_id,[$smm_server_post_id[$post->ID] => $smm_api_items['smm_price_update']]) ?? null;
					array_push($_server_ids, $post->ID);
					}
				} 
			}
			$_pro_updated 	= empty($_item_price_updated) ? "No price change" : implode(",",$_item_price_updated);
			$result 		= implode(",",$_server_ids)." scheduled ".$update_cycle." and  Last Executed: ".'-'.$_pro_updated;
			
			update_option( 'active_cron_servers', $result );
			}// END FUNC
	/**
	 * Sets product ids of api item .
	 * @param N/A
	 *  @return array of product Ids
	*/
	// Get all products that use the api items
	public function smm_api_products($_server_id = '',$_item_val =''){
			
		//===========================product ids that has smm_api===========
		
					$args = array(
					'post_type'      => 'product',
					'posts_per_page' => -1, // Get all matching products 16978
					'meta_key'       => '_smapi_api',
					'meta_value'   	 => 'yes',					
					'fields'       	 => 'ids', // Only retrieve product IDs
					'meta_query'     =>	array(
													//'key'     => '_smapi_service_id_option',
													//'value'   => '_item_',
													//'compare' => 'LIKE'
													'key'     => '_smapi_server_name_option',//one server
													'value'   => $_server_id,
													'compare' => '='
													
											)
								);
					$argv = array(
					'post_type'      => 'product_variation',
					'posts_per_page' => -1, // Get all matching products 
					'meta_key'       => 'variable_smm_api',
					'meta_value'   	 => 'on',
					'fields'       	 => 'ids', // Only retrieve product IDs var_smapi_service_id_option
					'meta_query'     =>	array(
													'key'     => 'var_smapi_server_name_option',//one server
													'value'   => $_server_id,
													'compare' => '='
													
											)
								);
					
					$product_api 	= array();//avoids undefined warning in foreach					
					$simple_products_ids 	= get_posts( $args );//get all simple prod
					foreach($simple_products_ids as $simple_products_id){
						if($_item_val == get_post_meta( $simple_products_id, '_smapi_service_id_option', true ))
						array_push($product_api, "S".$simple_products_id);
					}
					$variable_products_ids 	= get_posts( $argv );//get all var prod
					foreach($variable_products_ids as $variable_products_id){
						if($_item_val == strtok( get_post_meta( $variable_products_id, 'var_smapi_service_id_option', true ), " "))
						{
						$parent_id = wp_get_post_parent_id($variable_products_id);
						array_push($product_api, "V".$parent_id);
						}
					}
					
					// Output product IDs				
					return array_unique($product_api);//unique ids 
				//=====================get id ends=============
				 
					
			
	}
	
	/**
	 * Sets product price based on api item price.
	 * @param int|WC_Product $product Product ID or WC_Product object.
	 * @param string $price Price.
	 * @param string $regular_or_sale Price type. Possible values: regular, sale. Default: regular.
	 * @param bool $apply_to_all_variations Whether to set the price for all product variations as well.
		Default: false.
	 *
	 * @return bool
	*/
	function smm_set_product_price($products_id ='') {
		
		 $_regular_price_percentage = (get_option('smmreg_price') != '')?get_option('smmreg_price')/100:0.3;
         $_sale_price_percentage 	= (get_option('smmsale_price') != '')?get_option('smmsale_price')/100:NULL;
		 $product_id = filter_var($products_id, FILTER_SANITIZE_NUMBER_INT);
		 // Get an instance of the product object
		 
		 $_service_id = get_post_meta( $product_id, '_smapi_service_id_option', true );
		 	
		if ( substr($products_id, 0, 1) == 'S' &&  $_service_id != "") 
		{
			$_product 			= new WC_Product($product_id);

			$_service_id_num 	= substr($_service_id, 6); //get value after _item_
			$_service_price 	= array_key_exists($_service_id_num, $this->smm_api_ids) ? $this->smm_api_ids[$_service_id_num]:'';
			//updates products that has new price from server
			if(!empty($_service_price)){
			$_regular_price 			= $_service_price/1000 * $_regular_price_percentage;
			$_sale_price				= $_service_price/1000 * $_sale_price_percentage;
		
			
			if ( $_product->is_on_sale() ) {
				$_product->set_sale_price( $_sale_price );
				$_product->set_regular_price( $_regular_price );
			} else {
				$_product->set_regular_price( $_regular_price );
			}

			$_product->save();
			return "s#".$product_id;
			}
			
		} 
		elseif ( substr($products_id, 0, 1) == 'V' ) {
				
				$smm_service_ids = array();
				$smm_service_id = '';
				$_smapi_api_check = "on";
				// Get the parent product object (variable product)
				$product = new WC_Product_Variable($product_id);
				foreach ( $product->get_children() as $variation_id ) {
					//checking smm api checkbox ON
					if(get_post_meta( $variation_id, 'variable_smm_api', true ) != "on")
					$_smapi_api_check = "off" ;
					//Getting last word as service id 
					
					$_item_number = strtok( get_post_meta( $variation_id, 'var_smapi_service_id_option', true ), " ");
					
					array_push($smm_service_ids,filter_var($_item_number, FILTER_SANITIZE_NUMBER_INT));
					} 
					// check sale product and unsimilar service ids change price only if smm check enabled
				if( !(count(array_unique($smm_service_ids, SORT_REGULAR)) === 1) && $_smapi_api_check == "on" ) {
					foreach ( $product->get_children() as $variation_id ) {
					
					$_item_number 		= strtok( get_post_meta( $variation_id, 'var_smapi_service_id_option', true ), " ");
					$_service_id_num 	= filter_var($_item_number, FILTER_SANITIZE_NUMBER_INT);
					$_service_price 	= array_key_exists($_service_id_num, $this->smm_api_ids) ? $this->smm_api_ids[$_service_id_num]:'';

					if(!empty($_service_price)){
					$_regular_price_var = $_service_price / 1000 * $_regular_price_percentage;
					$_sale_price_var	= $_service_price / 1000 * $_sale_price_percentage;
					
						$variation = wc_get_product_object( 'variation', $variation_id );
						if($variation->is_on_sale() && $_sale_price_percentage != NULL){
								$variation->set_props(
												array(
														'regular_price' => $_regular_price_var,
														'sale_price' 	=> $_sale_price_var,
													 )
											);
											$variation->save();
											}
						if(!$variation->is_on_sale()){
								$variation->set_props(
												array(
														'regular_price' => $_regular_price_var,
														
													 )
											);
											$variation->save();
											}					
					}
					}
				} return "v#".$product_id.PHP_EOL;
		}//end elseif
	}
    }
}

/**
 * Unique access to instance of SMAPI_Subscription_Cron class
 *
 * @return \SMAPI_Subscription_Cron
 */
function SMAPI_Subscription_Cron() {
    return SMAPI_Subscription_Cron::get_instance();
}