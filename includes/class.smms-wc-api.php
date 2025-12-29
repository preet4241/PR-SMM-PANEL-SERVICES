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
if ( !class_exists( 'SMAPI_Api' ) ) {

class SMAPI_Api{
    
    
      public $api_link 					= ''; // API URL
      public $api_quantity 				= ''; // Your API key
      public $smm_service_id 			= ''; // check for API for multiple servers
      public $api_server 				= ''; // multiple servers post ID is taken

      public $api_server_url 			= ''; // server parameters for individual order processing for each server
      public $api_server_parameter 		= array();
      public $smm_api_server_dbrequest 	= array();// post_meta data for advanced users
      


	public function __construct($arg){  //initializing array data

			$this->api_server_parameter 		= $this->get_all_api_parameter($arg); 
			// get str from postmeta from id of api_server advanced users
			$smm_api_server_request_dbstr 		= get_post_meta( $this->api_server, '_api_server_request', true );
			$this->smm_api_server_dbrequest 	= json_decode($smm_api_server_request_dbstr,true);// STR  TO ARR
				
		}
		// Get Post deatils for API servers		
	Public function get_all_api_parameter($server_name){
	    //global $post;
		$this->api_server = $server_name;
	    $Api_server_handle = array();
	    $post_data = get_post($server_name);
	    $Api_server_handle['server_api_add_url'] = 
	           isset($post_data->post_title) ? $post_data->post_title:'';
	    $Api_server_handle['server_api_key'] = 
	           isset($post_data->post_content) ? $post_data->post_content:'';
        $Api_server_handle['server_api_enable'] = 
               isset($post_data->post_status) ? $post_data->post_status : '';
        $meta_value =   get_post_meta( $this->api_server, '_parameter_handle', true );
        $meta_value_array = json_decode($meta_value, true);
        $Api_server_handle['server_link'] = $meta_value_array['api_link_handle'];
        $Api_server_handle['server_key'] = $meta_value_array['api_key_handle'];
        $Api_server_handle['server_quantity'] = $meta_value_array['api_quantity_handle'];
        $Api_server_handle['server_service'] = $meta_value_array['api_service_handle'];
        $Api_server_handle['server_response'] = $meta_value_array['api_order_response_handle'];
        $Api_server_handle['server_error'] = $meta_value_array['api_error_response_handle'];
        $Api_server_handle['server_retrieve_status_query'] = $meta_value_array['api_retrieve_status_query'];
        $Api_server_handle['server_status_order_handle'] = $meta_value_array['api_status_order_handle'];
        $Api_server_handle['server_enable_api'] = $meta_value_array['enable_api'];
        $Api_server_handle['server_retrieve_status_check'] = 'https://seoclerks.in/api/check/';
        
                        
	    
	    return $Api_server_handle;
	    
	}
    public function order($link='', $service_id='', $quantity='',$product_attributes=array()) {                  
          
          // Add order
		  if(empty($this->smm_api_server_dbrequest )){
          $this->api_server_url = 
          $this->api_server_parameter['server_api_add_url'];
            return json_decode($this->connect(array(
            $this->api_server_parameter['server_key'] 
            => $this->api_server_parameter['server_api_key'],
            
            'action' => 'add',
            $this->api_server_parameter['server_link'] => $link,
            $this->api_server_parameter['server_service'] => $service_id,
	        $this->api_server_parameter['server_quantity'] => $quantity
            )));
			}
			else{ //using url meta
			$request_url_db				= $this->smm_api_server_dbrequest['add_query_url']; 	
			$parts 						= explode('?', $request_url_db);
			$api_baseurl				= $parts[0];
			$url_params					= $parts[1];
			// Parse into an array
			$params						= array();
			parse_str($url_params, $params);
			// Get all keys
			$keys 						= array_keys($params);
			// Get the 2nd key (remember — arrays are 0-based)
			$secondKey 					= $keys[1]; 
			$params[$secondKey] 		= $link;
			$thirdKey 					= $keys[2];
			$params[$thirdKey] 			= $service_id;
			$forthKey 					= $keys[3];
			$params[$forthKey] 			= $quantity;
			$this->api_server_url 		= $api_baseurl; 
			// Get matching keys array_intersect_key($array2, $array1)
			if (!empty($product_attributes))
			$matching_keys = array_intersect_key($product_attributes, $params);
			// Set values from array2 into array1 where keys match
			if (!empty($matching_keys))
			foreach ($matching_keys as $key => $value)
				$params[$key] = $value;
			// for extracting data from link url for keywords
			
			$parts_of_link 						= explode('?', $link) ?? null;
			if($parts_of_link != null)
			{
			$params[$secondKey]					= $parts_of_link[0];
			$url_link_params					= $parts_of_link[1];
			// Parse into an array
			$link_params						= array();
			if (!empty($url_link_params))
			parse_str($url_link_params, $link_params);// converting to arrays
			// Get matching keys array_intersect_key($array2, $array1
			if (!empty($link_params))
			$matching_keys = array_intersect_key($link_params, $params);
			// Set values from array2 into array1 where keys match
			if (!empty($matching_keys))
			foreach ($matching_keys as $key => $value)
				$params[$key] = $value;
			
			}
			//file_put_contents(plugin_dir_path( __FILE__ )."check.php", serialize($params) );
			return json_decode($this->connect($params));
			}	
    }
    public function status($order_id) { // Get status, remains
            if(empty($this->smm_api_server_dbrequest )){
			$this->api_server_url = 
            $this->api_server_parameter['server_retrieve_status_query'];
		
            return json_decode($this->connect(array(
            $this->api_server_parameter['server_key'] => 
            $this->api_server_parameter['server_api_key'],
            'action' => 'status',
          $this->api_server_parameter['server_status_order_handle'] => $order_id
          )));
          }
		  else{ //using url meta
			$request_url_db				= $this->smm_api_server_dbrequest['status_query_url']; 	
			$parts 						= explode('?', $request_url_db);
			$api_baseurl				= $parts[0];
			$url_params					= $parts[1];
			// Parse into an array
			$params						= array();
			parse_str($url_params, $params);
			// Get all keys
			$keys 						= array_keys($params);
			// Get the 2nd key (remember — arrays are 0-based)
			$secondKey 					= $keys[1]; 
			$params[$secondKey] 		= $$order_id;
			
			$this->api_server_url 		= $api_baseurl; 
			
			return json_decode($this->connect($params));
			}	
	}
    public function smm_url_check_status($order_id) { // Get status
            
			$this->api_server_url = 
            $this->api_server_parameter['server_retrieve_status_check'];
		
            return json_decode($this->connect(array(
            'action' => 'status_check',
            'check' => $order_id
          )));
          }
	public function multi_status($order_ids) { // get order status
		if(empty($this->smm_api_server_dbrequest )){
          $this->api_server_url = 
          $this->api_server_parameter['server_retrieve_status_query'];
			return json_decode($this->connect(array(
            $this->api_server_parameter['server_key'] 
            => $this->api_server_parameter['server_api_key'],
            'action' => 'status',
            $this->api_server_parameter['server_status_order_handle'] => implode(",", (array)$order_ids)
        )));
		}
		else{ //using url meta
			$request_url_db				= $this->smm_api_server_dbrequest['status_query_url']; 	
			$parts 						= explode('?', $request_url_db);
			$api_baseurl				= $parts[0];
			$url_params					= $parts[1];
			// Parse into an array
			$params						= array();
			parse_str($url_params, $params);
			// Get all keys
			$keys 						= array_keys($params);
			// Get the 2nd key (remember — arrays are 0-based)
			$secondKey 					= $keys[1]; 
			$params[$secondKey] 		= implode(",", (array)$order_ids);
			
			$this->api_server_url 		= $api_baseurl; 
			
			return json_decode($this->connect($params));
			}	
	}
	public function services() { // get services
			if(empty($this->smm_api_server_dbrequest )){
			$this->api_server_url = 
			$this->api_server_parameter['server_api_add_url'];
				
        return json_decode($this->connect(array(
            $this->api_server_parameter['server_key'] 
            => $this->api_server_parameter['server_api_key'],
            'action' => 'services'
        )));
		}
		else{ //using url meta
			$request_url_db				= $this->smm_api_server_dbrequest['service_query_url']; 	
			$parts 						= explode('?', $request_url_db);
			$api_baseurl				= $parts[0];
			$url_params					= $parts[1];
			// Parse into an array
			$params						= array();
			parse_str($url_params, $params);
						
			$this->api_server_url 		= $api_baseurl; 
			
			return json_decode($this->connect($params));
			}	
			
    }
	public function api_url_request($api_request_url = '') { // get result based api builder form
			// Split at the ?
			
			//$request_url_db				= $smm_api_server_request['sendaddrequest']; 	
			//$parts = explode('?', $request_url_db);
			$parts = explode('?', $api_request_url);
			
			$this->api_server_url		= $parts[0];
			$url_params					= $parts[1];
			// Parse into an array
			$params						= array();
			parse_str($url_params, $params);
			
			
			//$this->api_server_parameter['server_api_add_url'];
		//return 	$api_baseurl.serialize($params);
        return json_decode($this->connect($params));
    }
      
      private function connect($post) {
        
          $httpcode = wp_remote_post( $this->api_server_url, array(
                        'method'      => 'POST',
                        'timeout'     => 45,
                        'redirection' => 5,
                        'httpversion' => '1.0',
                        'blocking'    => true,
                        'headers'     => array(),
                        'body'        => $post,
                        'cookies'     => array()
                                        )
            );
             
          $get_details_of_request = "server arg = ".esc_url($this->api_server_url)."?"." return data = ".esc_html(serialize($httpcode))."body data = ";
         
                if ( is_wp_error( $httpcode ) ) {
                $error_message =  wp_kses_post($httpcode->get_error_message());
                $myarr = array('Error from API HTTP CALL' => $error_message, 'result' => $get_details_of_request );

                $result = wp_json_encode($myarr, JSON_FORCE_OBJECT);
                }
          $result = wp_remote_retrieve_body( $httpcode );
          //file_put_contents(plugin_dir_path( __FILE__ )."check.php", $result );
          return $result;
          }
 }
}//End of class