<?php
if ( !defined( 'ABSPATH' ) || !defined( 'SMMS_SMAPI_VERSION' ) ) {
    exit; // Exit if accessed directly
}
/**
 * Implements Ajax form Field features of API TEMS AND API SERVERS
 *
 * @class   SMMS_Ajax_Call_Admin
 * @package SMMS WooCommerce Subscription
 * @since   1.0.0
 * @author  SMMS
 */
if ( !class_exists( 'SMMS_Ajax_Call_Admin' ) ) {

class SMMS_Ajax_Call_Admin {
		
		
		private $post_type;
		
		
		/**
         * Single instance of the class
         *
         * @var \SMMS_Ajax_Call_Admin
         */
        protected static $instance;
		/**
         * Returns single instance of the class
         *
         * @return \SMMS_Ajax_Call_Admin
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
			 
		 $this->post_type = 'smapi_server';
		 //Adding Ajax for editing smm tables
            add_action('wp_ajax_server_display',array($this, 'smm_ajax_action_function_server_display'));
            add_action('wp_ajax_server_save', array($this,'smm_ajax_action_function_server_save'));
			add_action('wp_ajax_server_demo', array($this,'smm_ajax_action_function_server_demo'));
            add_action('wp_ajax_server_edit', array($this,'smm_ajax_action_function_server_edit'));
			add_action('wp_ajax_api_build', array($this,'smm_ajax_action_function_api_build'));
			add_action('wp_ajax_api_server_request', array($this,'smm_ajax_action_function_api_server_request'));
			add_action('wp_ajax_server_delete',array($this, 'smm_ajax_action_function_server_delete'));
            add_action('wp_ajax_f_item_save', array($this,'smm_ajax_action_function_item_save'));
            add_action('wp_ajax_f_item_edit', array($this, 'smm_ajax_action_function_item_edit'));
            add_action('wp_ajax_f_item_delete',array($this, 'smm_ajax_action_function_item_delete'));
			add_action('wp_ajax_f_item_import',array($this, 'smm_ajax_action_function_item_import'));
            add_action('wp_ajax_f_item_display',array($this, 'smm_ajax_action_function_item_display'));
            add_action('wp_ajax_server_list',array($this, 'smm_ajax_action_function_item_select_list'));
            add_action('wp_ajax_server_product_list',array($this, 'smm_ajax_action_function_product_select_list'));
            add_action('wp_ajax_var_server_product_list',array($this, 'smm_ajax_action_function_var_product_select_list'));
            add_action('wp_ajax_var_service_span_data',array($this, 'smm_ajax_action_function_var_var_service_span_data'));
            add_action('wp_ajax_n_item_product',array($this, 'smm_ajax_action_function_item_product'));
            add_action('wp_ajax_f_item_product',array($this, 'smm_ajax_action_function_item_product'));
			
		 }
        /* create notice div  notice-info notice-success notice-error notice-warning*/
		public function smm_ajax_action_function_server_display(){ 

			global $wpdb;
			$post_type ='smapi_server';
			//$link = " AND ( smapi_pm.meta_key = '_parameter_handle' ) ";
			$order_string ='ORDER BY ID DESC';
			$post_id = sanitize_text_field($_POST['item_id']);

			$server = get_post_meta( $post_id, '_parameter_handle', true );
			$server_item = json_decode($server, true);
			$args  = array(
            'post_type' => $post_type
							);
			//$query = new WP_Query( $args );
			
			//$items = $wpdb->query( $query );
			$items = $wpdb->get_results( $wpdb->prepare( "SELECT smapi_p.* FROM $wpdb->posts as smapi_p INNER JOIN " . $wpdb->prefix . "postmeta as smapi_pm ON ( smapi_p.ID = smapi_pm.post_id )
			WHERE ID=%s %1s 
			AND smapi_p.post_type = %s
			GROUP BY smapi_p.ID %1s", $post_id, $link, $post_type, $order_string
			) );
			//$totalitems =  $items->num_rows;
        //foreach($items as $item=>$value);
		// response output
			$response['success']= 1;
			$response['action']= 'server_display';

		// $response['id']= $post_id;
			$response['display_api_url']= $items[0]->post_title;
			$response['display_api_key_handle']= $server_item['api_key_handle'];
			$response['display_api_key']= $items[0]->post_content;
			$response['display_api_link_handle']=$server_item['api_link_handle'];
			$response['display_api_service_handle']= $server_item['api_service_handle'];
			$response['display_api_quantity_handle']= $server_item['api_quantity_handle'];
			$response['display_api_order_response_handle']= $server_item['api_order_response_handle'];
			$response['display_api_error_response_handle']= $server_item['api_error_response_handle'];
			$response['display_api_retrieve_status_query']= $server_item['api_retrieve_status_query'];
			$response['display_api_status_order_handle']= $server_item['api_status_order_handle'];
	 
			$response['display_api_server_status']= $items[0]->post_status;
			echo wp_json_encode($response);

			exit();
		} 
		public function smm_ajax_action_function_server_save(){
                          $reponse = array();
            	if(isset($_POST))
                          {
                          if( isset( $_POST['action'] ) )
                          $action = sanitize_text_field( $_POST['action'] );
                          if( isset( $_POST['fapi_url'] ) )
                          $fapi_url   = sanitize_text_field($_POST['fapi_url']);
                          if( isset($_POST['fapi_key_handle']))
                          $fapi_key_handle  = sanitize_text_field($_POST['fapi_key_handle']);
                          if( isset($_POST['fapi_key']))
                          $fapi_key   = sanitize_text_field($_POST['fapi_key']);
                          if( isset($_POST['fapi_link_handle']))
                          $fapi_link_handle   = sanitize_text_field($_POST['fapi_link_handle']);
                          if( isset($_POST['fapi_service_handle']))
                          $fapi_service_handle = sanitize_text_field($_POST['fapi_service_handle']);// service handle
                          if( isset($_POST['fapi_quantity_handle']))
                          $fapi_quantity_handle   = sanitize_text_field($_POST['fapi_quantity_handle']);
                           if( isset($_POST['fapi_order_response_handle']))
                          $fapi_order_response_handle   = sanitize_text_field($_POST['fapi_order_response_handle']);
						  if( isset($_POST['fapi_error_response_handle']))
                          $fapi_error_response_handle   = sanitize_text_field($_POST['fapi_error_response_handle']);
                          if( isset($_POST['fapi_retrieve_status_query']))
                          $fapi_retrieve_status_query   = sanitize_text_field($_POST['fapi_retrieve_status_query']);
                          if( isset($_POST['fapi_status_order_handle']))
                          $fapi_status_order_handle   = sanitize_text_field($_POST['fapi_status_order_handle']);
						  if( isset($_POST['fapi_price_update']))
                          $fapi_price_update = sanitize_text_field($_POST['fapi_price_update']);
                          if( isset($_POST['fapi_server_status']))
                          $fapi_server_status = sanitize_text_field($_POST['fapi_server_status']);
						  $fsmm_price_update = isset($_POST['fsmm_price_update']) ? '1':'0';						  
                          $_POST['fsmm_price_update'] = sanitize_text_field($fsmm_price_update);			
			              if( isset($_POST['fsid']))
                          $fsid = ( $_POST['fsid'] == '') ? NULL : sanitize_text_field($_POST['fsid']);
         			$missing = array();
         			foreach ($_POST as $key => $value) { if ($key != "fsid" && $value == "") { array_push($missing, sanitize_text_field($key));}}//phpcs:ignore
                 	if (count($missing) > 0) {
                 	$response['success'] = 0;
                 	foreach ($missing as $k => $v) { $response[$v]="is empty";}
                 	echo wp_json_encode($response);
                 	exit;
                 	} else {
                        unset($missing);
         // do your stuff here with the $_POST
                       
	       if($action == "server_save")
				{
	        $response['success'] = 1;
            $response['notice'] = 'Data has been saved!';
            $response['color'] = 'notice-sucess';
   			//Don't forget to always exit in the ajax function.
         // Action ADD section
            $args = array(
            'api_url'                          => $fapi_url,
            'api_key'                          => $fapi_key,
            'api_key_handle'                   => $fapi_key_handle,
            'api_link_handle'                  => $fapi_link_handle,
            'api_quantity_handle'              => $fapi_quantity_handle,
            'api_service_handle'               => $fapi_service_handle,
            'api_order_response_handle'        => $fapi_order_response_handle,
            'api_error_response_handle'        => $fapi_error_response_handle,
            // Action status section
            'api_retrieve_status_query_handle' =>'retrieve_status_query_handle',
            'api_retrieve_status_query'        => $fapi_retrieve_status_query,
            'api_status_order_handle'          => $fapi_status_order_handle,
            'enable_api'                       => $fapi_server_status,
			'api_price_update'                 => $fapi_price_update,//auto price update shedule
			'smm_price_update'                 => $fsmm_price_update,//checkbox enable
            // Action parameter
            'api_add_action'            	   => 'add',
            'api_status_action'                => 'status',
            'smapi_free_version'               => SMMS_SMAPI_VERSION
        );
            $server = new SMAPI_Server( sanitize_text_field($_POST['fsid']), $args );
            if($server->duplicate == true){
            $response['notice'] = 'Duplicated Entery Restricted!';
            $message = __( 'Server has been in the list. You cannot add same server again and again', 'smm-api' );
			    wc_add_notice( $message, 'notice' );}
            $response['dbdata'] = serialize($server).sanitize_text_field($_POST['fsid']);
            echo wp_json_encode($response);
            exit();   
                        	

	        }
            }
            }
         
        }
		public function smm_ajax_action_function_server_demo(){
					      $reponse = array();
					if(isset($_POST))
                          {
                          if( isset( $_POST['action'] ) )
                          $action = sanitize_text_field( $_POST['action'] );
                          
                          $fapi_url   = "https://seoclerks.in/api/";
                          
                          $fapi_key_handle  = "key";
                          
                          $fapi_key   = "abcdefg12345";
                          
                          $fapi_link_handle   = "link";
                          
                          $fapi_service_handle = "service";// service handle
                          
                          $fapi_quantity_handle   = "quantity";
                          
                          $fapi_order_response_handle   = "order";
						  
                          $fapi_error_response_handle   = "error";
                          
                          $fapi_retrieve_status_query   = "https://seoclerks.in/api/status/";
                          
                          $fapi_status_order_handle   = "orders";
			 
                          
                          $fapi_server_status = "enabled";
			              
                          $fsid =  NULL;
         			
         			
                 	
         // do your stuff here with the $_POST
                       
	       if($action == "server_demo")
				{
	        $response['success'] = 1;
            $response['notice'] = 'Demo Server has been added!';
            $response['color'] = 'notice-warning';
   		    //Don't forget to always exit in the ajax function.
            // Action demo section
            $args = array(
            'api_url'                          => $fapi_url,
            'api_key'                          => $fapi_key,
            'api_key_handle'                   => $fapi_key_handle,
            'api_link_handle'                  => $fapi_link_handle,
            'api_quantity_handle'              => $fapi_quantity_handle,
            'api_service_handle'               => $fapi_service_handle,
            'api_order_response_handle'        => $fapi_order_response_handle,
            'api_error_response_handle'        => $fapi_error_response_handle,
            // Action status section
            'api_retrieve_status_query_handle' =>'retrieve_status_query_handle',
            'api_retrieve_status_query'        => $fapi_retrieve_status_query,
            'api_status_order_handle'          => $fapi_status_order_handle,
            'enable_api'                       => $fapi_server_status,
            // Action parameter
            'api_add_action'            => 'add',
            'api_status_action'         => 'status',
            'smapi_free_version'     => SMMS_SMAPI_VERSION
						);
            $server = new SMAPI_Server( sanitize_text_field($_POST['fsid']), $args );
            
            if($server->duplicate == true){
            $response['notice'] = 'Duplicated Entery Restricted!';
              $message = __( 'A subscription has been removed from your cart. You cannot purchases different subscriptions at the same time.', 'smm-api' );
			    wc_add_notice( $message, 'notice' );}
            //file_put_contents(plugin_dir_path( __FILE__ )."check.txt", serialize($server));
            echo wp_json_encode($response);
            exit();   
                        	

	        }
            
            }
         
        }
        
		public function smm_ajax_action_function_server_edit(){

			global $wpdb;
			$post_type ='smapi_server';
			//$link = " AND ( smapi_pm.meta_key = '_parameter_handle' ) ";
			$order_string ='ORDER BY ID DESC';
			$post_id = sanitize_text_field($_POST['item_id']);
			$server = get_post_meta( $post_id, '_parameter_handle', true );
			$server_item = json_decode($server, true);
			$args  = array(
            'post_type' => $post_type
							);
			//$query = new WP_Query( $args );
			

			
			$items = $wpdb->get_results( $wpdb->prepare( "SELECT smapi_p.* FROM $wpdb->posts as smapi_p INNER JOIN " . $wpdb->prefix . "postmeta as smapi_pm ON ( smapi_p.ID = smapi_pm.post_id )
			WHERE ID=%s %1s
			AND smapi_p.post_type = %s
			GROUP BY smapi_p.ID %1s",$post_id, $link, $post_type, $order_string
			) );
			//$totalitems = $items->num_rows;
        //foreach($items as $item=>$value);
		// response output
			$response['success']					= 1;
			$response['action']						= 'server_edit';

			$response['id']							= $post_id;
			$response['api_url']					= $items[0]->post_title;
			$response['api_key_handle']				= $server_item['api_key_handle'];
			$response['api_key']					= $items[0]->post_content;
			$response['api_link_handle']			= $server_item['api_link_handle'];
			$response['api_service_handle']			= $server_item['api_service_handle'];
			$response['api_quantity_handle']		= $server_item['api_quantity_handle'];
			$response['api_order_response_handle']	= $server_item['api_order_response_handle'];
			$response['api_error_response_handle']	= $server_item['api_error_response_handle'];
			$response['api_retrieve_status_query']	= $server_item['api_retrieve_status_query'];
			$response['api_status_order_handle']	= $server_item['api_status_order_handle'];
	 
			$response['api_server_status'] 			= $items[0]->post_status;
			$response['api_price_update'] 			= $server_item['api_price_update'];
			$response['smm_price_update'] 			= $server_item['smm_price_update'];
			echo wp_json_encode($response);

			exit();
		}
		public function smm_ajax_action_function_api_server_request(){
			$reponse = array();

                        if( isset( $_POST['action'] ) )
                        $action 	= sanitize_text_field( $_POST['action'] );
                        if( isset( $_POST['sendapirequest'] ) )
                        $sendapirequest  = esc_url_raw(urldecode($_POST['sendapirequest']));
						if( isset( $_POST['server_id'] ) )
                        $server_id 	= sanitize_text_field( $_POST['server_id'] );

						if($action == "api_server_request"){
						
						//API call based on server name as post ID
//file_put_contents(plugin_dir_path( __FILE__ )."checkk.txt", $sendaddrequest);
						if(strlen($sendapirequest ?? '') >6){
						$smm_api_request  = new SMAPI_Api($server_id);
						$smm_api_response = $smm_api_request->api_url_request($sendapirequest);
						// response output
						$response['success']		= 1;
						$response['action']			= 'api_server_request';
						$response['server_id']		= $server_id;
						$response['result']			= $smm_api_response;
						echo wp_json_encode($response);
						}
						if(strlen($sendapirequest ?? '') < 1 ){
						if( isset( $_POST['add_query_url'] ) )
                        $add_query_url  	= esc_url_raw(urldecode($_POST['add_query_url']));
						if( isset( $_POST['result_order'] ) )
                        $result_order  	= esc_url_raw(urldecode($_POST['result_order']));		
						if( isset( $_POST['status_query_url'] ) )
                        $status_query_url  	= esc_url_raw(urldecode($_POST['status_query_url']));
						if( isset( $_POST['result_status'] ) )
                        $result_status  	= esc_url_raw(urldecode($_POST['result_status']));					
						if( isset( $_POST['service_query_url'] ) )
                        $service_query_url  = esc_url_raw(urldecode($_POST['service_query_url']));
						if( isset( $_POST['result_services'] ) )
                        $result_services  	= esc_url_raw(urldecode($_POST['result_services']));						
						$_smm_url 			= array(
													"add_query_url" 	=> $add_query_url,
													"result_order" 		=> $result_order,
													"status_query_url" 	=> $status_query_url,
													"result_status" 	=> $result_status,
													"service_query_url" => $service_query_url,
													"result_services" 	=> $result_services,
													);
						if(strlen($service_query_url ?? '') > 1 ){							
						update_post_meta( $server_id,'_api_server_request',wp_json_encode($_smm_url,JSON_FORCE_OBJECT | JSON_NUMERIC_CHECK));								
						
						// response output
						$response['success']		= 1;
						$response['action']			= 'api_server_request';
						$response['server_id']		= $server_id;
						$response['result']			= 'Api Requests Have been Saved!';
						}
						if(strlen($service_query_url ?? '') < 1 ){							
						update_post_meta( $server_id,'_api_server_request','');								
						
						// response output
						$response['success']		= 1;
						$response['action']			= 'api_server_request';
						$response['server_id']		= $server_id;
						$response['result']			= 'Api Requests Have been Deleted!';
						}
						echo wp_json_encode($response);
						}
						}
			exit();
		}
		public function smm_ajax_action_function_api_build(){
			
			// get str from postmeta from id of api_server advanced users
			if( isset( $_POST['item_id'] ) )
			{	
			$post_id 							= sanitize_text_field($_POST['item_id']);
            $server_id 							= filter_var($post_id, FILTER_SANITIZE_NUMBER_INT);
			$smm_api_server_request_dbstr 		= get_post_meta( $server_id, '_api_server_request', true );
			$smm_api_server_dbrequest 			= json_decode($smm_api_server_request_dbstr,true);// STR  TO ARR
			}
		    if(empty($smm_api_server_dbrequest )){
			global $wpdb;
			$post_type ='smapi_server';
			$order_string ='ORDER BY ID DESC';
			
			$server = get_post_meta( $post_id, '_parameter_handle', true );
			$server_item = json_decode($server, true);
			$args  = array(
            'post_type' => $post_type
							);
			$items = $wpdb->get_results( $wpdb->prepare( "SELECT smapi_p.* FROM $wpdb->posts as smapi_p INNER JOIN " . $wpdb->prefix . "postmeta as smapi_pm ON ( smapi_p.ID = smapi_pm.post_id )
			WHERE ID=%s %1s
			AND smapi_p.post_type = %s
			GROUP BY smapi_p.ID %1s",$post_id, $link, $post_type, $order_string
			) );
			
			// response output
			$response['success']					= 1;
			$response['action']						= 'api_build';

			$response['id']							= $post_id;
			$splitUrl_arr 							= smms_splitUrl($items[0]->post_title);
			$response['api_baseurl']				= $splitUrl_arr['base_url'];
			$response['url_end_points']				= $splitUrl_arr['remaining'];
			$response['api_key_handle']				= $server_item['api_key_handle'];
			$response['api_key']					= $items[0]->post_content;
			$response['api_link_handle']			= $server_item['api_link_handle'];
			$response['api_service_handle']			= $server_item['api_service_handle'];
			$response['api_quantity_handle']		= $server_item['api_quantity_handle'];
			$response['api_order_response_handle']	= $server_item['api_order_response_handle'];
			$response['api_error_response_handle']	= $server_item['api_error_response_handle'];
			
			$response['api_retrieve_status_query']	= $server_item['api_retrieve_status_query'];
			$response['api_status_order_handle']	= $server_item['api_status_order_handle'];
			$response['api_action_handle'] 			= "action";
			$response['api_action_add_order'] 		= "add";
			$response['api_action_order_status'] 	= "status";
			$response['api_action_get_services'] 	= "services";
						
			echo wp_json_encode($response);
			}
			else
			{ 							//using url meta
			$request_url_db				= $smm_api_server_dbrequest['add_query_url'];
//file_put_contents(plugin_dir_path( __FILE__ )."checkk.txt", $request_url_db);
						
			$parts 						= explode('?', $request_url_db);
			$api_baseurl				= $parts[0];
			$url_params					= $parts[1];
			// Parse into an array
			$params						= array();
			parse_str($url_params, $params);
			// Get all keys
			$keys 						= array_keys($params);
			// Get the 2nd key (remember — arrays are 0-based)
			$firstKey 					= $keys[0];
			$secondKey 					= $keys[1]; 
			$thirdKey 					= $keys[2];
			$forthKey 					= $keys[3];
			$fifthKey 					= $keys[4];
			
			// response output
			$response['success']					= 1;
			$response['action']						= 'api_build';

			$response['id']							= $post_id;
			$splitUrl_arr 							= smms_splitUrl($api_baseurl);
			$response['api_baseurl']				= $splitUrl_arr['base_url'];
			$response['url_end_points']				= $splitUrl_arr['remaining'];
			$response['api_key_handle']				= $firstKey;
			$response['api_key']					= $params[$firstKey];
			$response['api_link_handle']			= $secondKey;
			$response['api_link']					= $params[$secondKey];
			$response['api_service_handle']			= $thirdKey;
			$response['api_service']				= $params[$thirdKey];
			$response['api_quantity_handle']		= $forthKey;
			$response['api_quantity']				= $params[$forthKey];
			$response['api_action_handle']			= $fifthKey;
			$response['api_action_add_order'] 		= $params[$fifthKey];
			$response['api_addparam']				= Array();
			for($i=5; $i < count($keys);$i++)
			$response['api_addparam'][$keys[$i]] 	= $params[$keys[$i]];
			//Order Result Params
			$request_url_db							= $smm_api_server_dbrequest['result_order']; 
			$parts 									= explode('?', $request_url_db);
			$params									= array();
			$url_params								= $parts[1];
			parse_str($url_params, $params);
			// Get all keys
			$keys 									= array_keys($params);
			
			$response['api_resultorder']			= Array();
			for($i=0; $i < count($keys);$i++)
			$response['api_resultorder'][$keys[$i]] = $params[$keys[$i]];
			//Status Query
			$request_url_db							= $smm_api_server_dbrequest['status_query_url']; 
			$parts 									= explode('?', $request_url_db);
			$params									= array();
			$url_params								= $parts[1];
			parse_str($url_params, $params);
			// Get all keys
			$keys 									= array_keys($params);
			$response['api_retrieve_status_query']	= $parts[0];
			$response['api_statusparam']			= Array();
			for($i=0; $i < count($keys);$i++)
			$response['api_statusparam'][$keys[$i]] = $params[$keys[$i]];
			//Status Result Params
			$request_url_db							= $smm_api_server_dbrequest['result_status']; 
			$parts 									= explode('?', $request_url_db);
			$params									= array();
			$url_params								= $parts[1];
			parse_str($url_params, $params);
			// Get all keys
			$keys 									= array_keys($params);
			
			$response['api_resultstatus']			= Array();
			for($i=0; $i < count($keys);$i++)
			$response['api_resultstatus'][$keys[$i]] = $params[$keys[$i]];
			// services query
			$request_url_db							= $smm_api_server_dbrequest['service_query_url']; 
			$parts 									= explode('?', $request_url_db);
			$params									= array();
			$url_params								= $parts[1];
			parse_str($url_params, $params);
			// Get all keys
			$keys 									= array_keys($params);
			for($i=0; $i < count($keys);$i++)
			$response['api_servicesparam'][$keys[$i]] = $params[$keys[$i]];
			//Service Result Params
			$request_url_db							= $smm_api_server_dbrequest['result_services']; 
			$parts 									= explode('?', $request_url_db);
			$params									= array();
			$url_params								= $parts[1];
			parse_str($url_params, $params);
			// Get all keys
			$keys 									= array_keys($params);
			
			$response['api_resultservices']			= Array();
			for($i=0; $i < count($keys);$i++)
			$response['api_resultservices'][$keys[$i]] = $params[$keys[$i]];
			
			echo wp_json_encode($response);
			}	
			exit();
		}
		public function smm_ajax_action_function_server_delete(){

			$reponse = array();

                        if( isset( $_POST['action'] ) )
                        $action = sanitize_text_field( $_POST['action'] );
                        if( isset( $_POST['item_id'] ) )
                        $smm_item_delete   = sanitize_text_field($_POST['item_id']);

			$response_delete = wp_delete_post($smm_item_delete, true );
		
			// response output
			$response['success']= 1;
			$response['notice'] = 'Server has been deleted!';
            $response['color'] = 'notice-error';
			//$response['dbdata'] = $response_delete.$post_id. $smm_item_delete;
			echo wp_json_encode($response);
			exit();

		}
		public function smm_ajax_action_function_item_save(){
                          $reponse = array();
            	if(isset($_POST) )
                          {
                          if( isset( $_POST['action'] ) )
                          $action = sanitize_text_field( $_POST['action'] );
                          if( isset( $_POST['f_service_id'] ) )
                          $f_service_id   = sanitize_text_field($_POST['f_service_id']);
                          if( isset($_POST['f_api_description']))
                          $f_api_description  = sanitize_text_field($_POST['f_api_description']);
                          if( isset($_POST['f_min_order']))
                          $f_min_order   = sanitize_text_field($_POST['f_min_order']);
                          if( isset($_POST['f_max_order']))
                          $f_max_order   = sanitize_text_field($_POST['f_max_order']);
                          if( isset($_POST['f_item_price']))
                          $f_item_price = sanitize_text_field($_POST['f_item_price']);// service handle
                          if( isset($_POST['f_item_status']))
                          $f_item_status   = sanitize_text_field($_POST['f_item_status']);
                           if( isset($_POST['f_item_post_count']))
                          $f_item_post_count   = sanitize_text_field($_POST['f_item_post_count']);
			              if( isset($_POST['f_item_post_delay']))
                          $f_item_post_delay   = sanitize_text_field($_POST['f_item_post_delay']);
                          //if( isset($_POST['f_item_post_ex_date']))
                          //$f_item_post_ex_date   = sanitize_text_field($_POST['f_item_post_ex_date']);
                          if( isset($_POST['f_item_subscribe_check']))
                          $f_item_subscribe_check = sanitize_text_field($_POST['f_item_subscribe_check']);
			 
	                if( isset($_POST['f_meta_key']))
                          $f_meta_key = ( $_POST['f_meta_id'] == '') ? NULL : sanitize_text_field($_POST['f_meta_key']);
         			$missing = array();
         			foreach (wc_clean($_POST) as $key => $value) { if ($value == "") { array_push($missing, sanitize_text_field($key));}}//phpcs:ignore
                 	    if (count($missing) > 1 ) {
                 	    $response['success'] = 0;
                 	        foreach ($missing as $k => $v) { $response[$v]="is empty";}
                 	       echo wp_json_encode($response);
                 	        exit;
                 	} else {
                            unset($missing);
         // do your stuff here with the $_POST
                       
	                        if($action == "f_item_save")
				                {
	                                $response['success'] = 1;
                                    $response['notice'] = 'Item has been saved!';
                                    $response['color'] = 'notice-sucess';
   			                        //Don't forget to always exit in the ajax function.
                                    // Action ADD section
                                    $args = array(
			                        'f_service_id'            => $f_service_id,
			                        'f_api_description'       => $f_api_description,
			                        'f_min_order'             => $f_min_order,
			                        'f_max_order'             => $f_max_order,
			                        'f_item_price'            => $f_item_price,
			                        'f_item_status'           => $f_item_status,
			                        'f_item_post_count'       => $f_item_post_count,
			                        'f_item_post_delay'       => $f_item_post_delay,
			                        //'f_item_post_ex_date'   => $f_item_post_ex_date,
			                        'f_item_subscribe_check'  => $f_item_subscribe_check
						            );
                                    $smm_api_items = new SMAPI_API_ITEM( $f_meta_id, $args );
                                    $response['dbdata'] = serialize($smm_api_items).$f_meta_id;
                                    echo wp_json_encode($response);
                                     exit();   
                        	

	                               }
                        }//end of else
            }
         
        }
		public function smm_ajax_action_function_item_edit(){

			global $wpdb;
			$reponse = array();


			$post_id  = get_option('smm_api_server_item');
			$meta_key = sanitize_text_field($_POST['f_meta_key']);
			$smm_items = get_post_meta( $post_id, $meta_key, true );
			$smm_item = json_decode($smm_items, true);

        
			$response['success']= 1;
			$response['action']= 'f_item_edit';

			$response['f_post_id']             = $post_id;
			$response['f_meta_key']            = $meta_key;
			$response['f_service_id']          = $smm_item['f_service_id'];
			$response['f_api_description']     = $smm_item['f_api_description'];
			$response['f_min_order']           = $smm_item['f_min_order'];
			$response['f_max_order']           = $smm_item['f_max_order'];
			$response['f_item_price']          = $smm_item['f_item_price'];
			$response['f_item_status']         = $smm_item['f_item_status'];
			$response['f_item_post_count']     = $smm_item['f_item_post_count'];
			$response['f_item_post_delay']     = $smm_item['f_item_post_delay'];
			//$response['f_item_post_ex_date']   = $server_item['f_item_post_ex_date'];
			$response['f_item_subscribe_check']= $smm_item['f_item_subscribe_check'];
			echo wp_json_encode($response);

			exit();
		}
		public function smm_ajax_action_function_item_display(){

			$reponse = array();


			$post_id  = get_option('smm_api_server_item');
			$meta_key = sanitize_text_field($_POST['f_meta_key']);
			$smm_items = get_post_meta( $post_id, $meta_key, true );
			$smm_item = json_decode($smm_items, true);
			// response output
			$response['success']= 1;
			$response['action']= 'f_item_display';

			// $response['id']= $post_id;
			// $response['id']= $post_id;
			$response['display_service_id']= $smm_item['f_service_id'];
			$response['display_api_description']= $smm_item['f_api_description'];
			$response['display_min_order']= $smm_item['f_min_order'];
			$response['display_max_order']=$smm_item['f_max_order'];
			$response['display_item_price']= $smm_item['f_item_price'];
			$response['display_item_status']= $smm_item['f_item_status'];
			$response['display_item_post_count']= $smm_item['f_item_post_count'];
			$response['display_item_post_delay']= $smm_item['f_item_post_delay'];
			//$response['display_api_item_post_ex_date']=$server_item['f_item_post_ex_date'];
			$response['display_item_subscribe_check']= $smm_item['f_item_subscribe_check'];
				echo wp_json_encode($response);

			exit();
		}

		public function smm_ajax_action_function_item_delete(){

			$reponse = array();
			$post_id  = get_option('smm_api_server_item');
                        if( isset( $_POST['action'] ) )
                        $action = sanitize_text_field( $_POST['action'] );
                        if( isset( $_POST['f_meta_key'] ) )
                        $smm_item_delete   = sanitize_text_field($_POST['f_meta_key']);
                        
                         global $wpdb;
                $PostIDs = $wpdb->get_results($wpdb->prepare( "SELECT DISTINCT (post_id) 
                        FROM {$wpdb->prefix}postmeta
                        WHERE post_id IN (SELECT post_id 
                        FROM {$wpdb->prefix}postmeta 
                        WHERE meta_value = %s) 
                        And post_id IN (SELECT post_id 
                        FROM {$wpdb->prefix}postmeta 
                        WHERE meta_value = %s)", $post_id, $smm_item_delete), ARRAY_A );
            foreach ($PostIDs as $PostID)
            wp_delete_post( $PostID['post_id'], true );
			$response_delete = delete_post_meta( $post_id, $smm_item_delete );
			// response output
			$response['success']= 1;
			$response['notice'] = 'Item has been deleted!';
            $response['color'] = 'notice-error';
			//$response['dbdata'] = $response_delete.$post_id. $smm_item_delete;
			echo wp_json_encode($response);
			exit();

			}
			public function	smm_ajax_action_function_item_product(){
			
			$reponse = array();
           
			if( isset( $_POST['action'] ) ){
		    do_action('smm_ajax_action_function_add_product');
            // response output
			$response['success']= 1;
			$response['notice'] = "THIS FUNCTION IS AVAILABLE WITH PREMIUM VERSION";
            $response['color'] = 'notice-warning';
			echo wp_json_encode($response);
			exit();			
			}   
			}
			public function smm_ajax_action_function_item_import(){
			
			$reponse = array();
			$args1 = array();
			if( isset( $_POST['service'] ) ){
			do_action('smm_ajax_action_function_Api_Item_Add');
		    // response output
			$response['success']= 1;
			$response['notice'] = "THIS FUNCTION IS AVAILABLE WITH PREMIUM VERSION!";
            $response['color'] = 'notice-warning';
			echo wp_json_encode($response);
			exit();			
			}
			
            if( isset( $_POST['action'] ) )
            $action = sanitize_text_field( $_POST['action'] );
					
			$active_tab = home_url();
			$response['active_tab']= $active_tab;
            if($action == 'f_item_import'){
			// response output for item table listing
			$response['success']= 1;
			$server_id = get_option('smm_api_server_item');
			$new_api_item = new SMAPI_Api($server_id);
		    
			$api_item_object = $new_api_item->services();
			$smm_api_server_request_dbstr 		= get_post_meta( $server_id, '_api_server_request', true );
			$smm_api_server_dbrequest 			= json_decode($smm_api_server_request_dbstr,true);// STR  TO ARR
			if(empty($smm_api_server_dbrequest )){
			$table_api_service = (array)$api_item_object;
			foreach($table_api_service as $key=>$table_rows){
			$table_api_service[$key]->cb= "";
			$smmtgle_id = $table_api_service[$key]->service;
			$table_api_service[$key]->service = $smmtgle_id.
			"<div class=\"tgle-flip\"><input class=\"tgl tgl-flip\" id=\"$smmtgle_id\" type=\"checkbox\"/>
    <label class=\"tgl-btn\" data-tg-off=\"Add Item\" data-tg-on=\"Add Product\" for=\"$smmtgle_id\"></label></div>";
			$table_api_service[$key]->status= "active";
			$table_api_service[$key]->sub= "disabled";
			$table_api_service[$key]->view= 'As Item or Product';
			$table_api_service[$key]->product= 'Click Red Box & checkbox';
			}
			}
			else{
			// from service result param from db
			//Service Result Params
			$request_url_db							= $smm_api_server_dbrequest['result_services']; 
			$parts 									= explode('?', $request_url_db);
			$params									= array();
			$url_params								= $parts[1];
			parse_str($url_params, $params);
			// Get all keys
			$keys 									= array_keys($params);
			
			$response_api_resultservices			= Array();
			for($i=0; $i < count($keys);$i++)
			$response_api_resultservices[$keys[$i]] = $params[$keys[$i]];
			$table_api_service = (array)$api_item_object;
			foreach($table_api_service as $key=>$table_rows){
			$table_api_service[$key]->cb= "";
			$smmtgle_id = $table_rows->{$params[$keys[0]]};
			//Keys[0] = first inputbox from form data & service id
			$table_api_service[$key]->service = $smmtgle_id.
			"<div class=\"tgle-flip\"><input class=\"tgl tgl-flip\" id=\"$smmtgle_id\" type=\"checkbox\"/>
    <label class=\"tgl-btn\" data-tg-off=\"Add Item\" data-tg-on=\"Add Product\" for=\"$smmtgle_id\"></label></div>";
			$table_api_service[$key]->name 	= $table_rows->{$params[$keys[1]]} ?? "NA";
			$table_api_service[$key]->rate 	= $table_rows->{$params[$keys[2]]};
			$table_api_service[$key]->min 	= $table_rows->{$params[$keys[3]]};
			$table_api_service[$key]->max 	= $table_rows->{$params[$keys[4]]} ?? "100000";
			$table_api_service[$key]->status= "active";
			$table_api_service[$key]->sub= "disabled";
			$table_api_service[$key]->view= 'As Item or Product';
			$table_api_service[$key]->product= 'Click Red Box & checkbox';
			}	
			}
			$response['table_api_service'] = $table_api_service;
			//printf( '%s', json_encode($response));
			echo wp_json_encode($response);
			exit();

			}
			    
			}//end of function
		public function smm_ajax_action_function_item_select_list(){
			$reponse = array();
			if(isset($_POST))
                          {
                          if( isset( $_POST['action'] ) )
                          $action = sanitize_text_field( $_POST['action'] );
                          if( isset( $_POST['f_api_server_list'] ) )
                          $f_api_server_list   = sanitize_text_field($_POST['f_api_server_list']);
                          }
                if($action == "server_list")
				{
	               update_option( 'smm_api_server_item', $f_api_server_list );
	               $response['f_api_server_list'] = $f_api_server_list;
	                    $response['success'] = 1;
				        wp_send_json_success($response);
				}
				exit();   
		}
			// To get server selction on each product admin page
		public function smm_ajax_action_function_product_select_list(){
		            global $wpdb;
					//global $thepostid;
  					if( isset( $_POST['smmid'] ) )
                          $smmid = sanitize_text_field( $_POST['smmid'] );
                    
                    // get_option used
                    $reponse = array();
                    $product = wc_get_product( $smmid );
					//$id = $product->name; 
					
	                $args = array();
                    if(isset($_POST))
                          {
                          if( isset( $_POST['action'] ) )
                          $action = sanitize_text_field( $_POST['action'] );
                          
                          if ( isset( $_POST['f_smapi_server_name_option'] ) ) 
                          {
			                    $args['_smapi_server_name_option'] = 
			                    sanitize_text_field($_POST['f_smapi_server_name_option']);
			                
		                  }
						  
                    if($action == "server_product_list")
				            {  // saving the api server for each product meta
				                smm_save_prop( $product, $args );
	            
	                        $response['f_smapi_server_name_option'] = 
	                        $args['_smapi_server_name_option'];
	                        // API ITEM LISTING
	                        $api_server_list_options_saved = 
	                        $response['f_smapi_server_name_option'];                    
	                        
	                        $smm_api_items_listing = $wpdb->get_results( $wpdb->prepare( "SELECT smapi_pm.* FROM " .$wpdb->prefix ."postmeta as smapi_pm INNER JOIN $wpdb->posts as smapi_p ON ( smapi_pm.post_id = %s )
                            WHERE 1=1 
                            AND smapi_p.post_type = %s
                            AND smapi_pm.meta_key LIKE %s
                            GROUP BY smapi_pm.meta_id
                            ", $api_server_list_options_saved, 'smapi_server', '_item_%'
                            ), ARRAY_A);
						$smm_total_items = count($smm_api_items_listing);
	                        $descrption_opted = '';
	                        $api_item_list_options_output='';
	                            foreach($smm_api_items_listing as $sub_item_result){
	            
			                            $descrption_opted_data =
			                            " Min Order: " .smms_decode_string($sub_item_result['meta_value'],
			                            'f_min_order').
			                            " Max Order: " .smms_decode_string(
			                                $sub_item_result['meta_value'],'f_max_order')
			                                .' Service ID - '.filter_var(
			                                    $sub_item_result['meta_key'], FILTER_SANITIZE_NUMBER_INT); 
			
			                    $api_item_list_options_output .= '<option ';
			                    $api_item_list_options_output .=
			                    ($sub_item_result['meta_key'] == 
			                    $api_item_list_options_saved ) ?
			                    'value="'.$sub_item_result['meta_key'].'"'
			                    .' data-desc="'.$descrption_opted_data
			                    .'" selected'
			                    : 
	                            'value="'.$sub_item_result['meta_key'].'"'
	                             .' data-desc="'.$descrption_opted_data.'"'
	                            ;
	                            ($sub_item_result['meta_key'] ==
	                            $api_item_list_options_saved ) ?
	                            $descrption_opted = $descrption_opted_data
	                            :NULL
	                            ;
	                            $api_item_list_options_output .= '>'
	                            .smms_decode_string($sub_item_result['meta_value'],'f_api_description')
	                            . '</option>';
			                    }
	                            $response['option_data'] =  
	                            $api_item_list_options_output ;
	                        
	                            $response['success'] = 1;
				                echo wp_json_encode($response);
				            }
                          }
						exit();   
		}
		public function smm_ajax_action_function_var_var_service_span_data(){
		                $reponse    = array();
		                $args       = array();
                    if(isset($_POST))
                          {
                          if( isset( $_POST['action'] ) )
                          $action = sanitize_text_field( $_POST['action'] );
                          
                          if ( isset( $_POST['var_smapi_service_id_option'] ) ) 
                          {
			                    $args['var_smapi_service_id_option'] =
			                    sanitize_text_field($_POST['var_smapi_service_id_option']);
			                
		                  }
                          if($action == "var_service_span_data") {
                                $response['span_data']  = 
                                $args['var_smapi_service_id_option'];
                                $response['success'] = 1;
				                echo wp_json_encode($response);
				                exit();
                          }  
                    }//end of first if
		 
		}
		// To get server selction on each var product at admin page
		public function smm_ajax_action_function_var_product_select_list(){
		            global $wpdb;
                    //$thepostid = $this->product_select_list; 
                    // get_option used
                    $reponse = array();
                    $product = wc_get_product( $thepostid );
	                $args = array();
                    if(isset($_POST))
                          {
                          if( isset( $_POST['action'] ) )
                          $action = sanitize_text_field( $_POST['action'] );
                          
                          if ( isset( $_POST['var_smapi_server_name_option'] ) ) 
                          {
			                    $args['var_smapi_server_name_option'] = 
			                    sanitize_text_field($_POST['var_smapi_server_name_option']);
			                
		                  }
		                 // file_put_contents(plugin_dir_path( __FILE__ )."checkk.txt", serialize($_POST));
                    if($action == "var_server_product_list")
				            {  // saving the api server for each product meta
				                //smm_save_prop( $product, $args );
	            
	                        $response['var_smapi_server_name_option'] = 
	                        $args['var_smapi_server_name_option'];
	                        // API ITEM LISTING
	                        $api_server_list_options_saved = 
	                        $response['var_smapi_server_name_option'];
                            
	                        
	                        $smm_api_items_listing = $wpdb->get_results( $wpdb->prepare( "SELECT smapi_pm.* FROM " .$wpdb->prefix ."postmeta as smapi_pm INNER JOIN $wpdb->posts as smapi_p ON ( smapi_pm.post_id = %s )
                            WHERE 1=1 
                            AND smapi_p.post_type = %s
                            AND smapi_pm.meta_key LIKE %s
                            GROUP BY smapi_pm.meta_id
                            ", $api_server_list_options_saved, 'smapi_server', '_item_%'
                            ), ARRAY_A);
							$smm_total_items = count($smm_api_items_listing);
	                        $descrption_opted = '';
	                        $api_item_list_options_output='';
	                            foreach($smm_api_items_listing as $sub_item_result){
	                                    $iteration += 1;
			                            $descrption_opted_data = " Min Order: " .smms_decode_string(
			                                $sub_item_result['meta_value'],'f_min_order').
			                            " Max Order: " .smms_decode_string(
			                                $sub_item_result['meta_value'],'f_max_order')
			                                .' Service ID - '.filter_var(
			                                    $sub_item_result['meta_key'], FILTER_SANITIZE_NUMBER_INT); 
			
			                            $api_item_list_options_output .= '<option ';
			                            $api_item_list_options_output .=
			                            ($sub_item_result['meta_key'] == 
			                            $api_item_list_options_saved ) ?
			                            'value="'.$sub_item_result['meta_key'].'"'
			                            .' data-desc="'.$descrption_opted_data
			                            .'" selected'
			                            : 
	                                    'value="'.$sub_item_result['meta_key'].'"'
	                                     .' data-desc="'.$descrption_opted_data.'"'
	                                    ;
	                                    ($sub_item_result['meta_key'] == 
	                                    $api_item_list_options_saved ) ?
	                                    $descrption_opted = $descrption_opted_data
	                                    :NULL
	                                    ;
	                                    $api_item_list_options_output .= '>'
	                                    .smms_decode_string($sub_item_result['meta_value'],'f_api_description')
	                                    . '</option>';
	                                    if($iteration == 1 )
	                                        $response['span_data'] =  $descrption_opted_data;
			                        }
	                                    $response['option_data'] =  $api_item_list_options_output ;
	                        
	                                    $response['success'] = 1;
				                        echo wp_json_encode($response);
				            }
                          }
						exit();   
		}
	}
	
 }
 

/**
 * Unique access to instance of SMMS_Ajax_Call_Admin class
 *
 * @return \SMMS_Ajax_Call_Admin
 */
function SMMS_Ajax_Call_Admin() {
    return SMMS_Ajax_Call_Admin::get_instance();
}