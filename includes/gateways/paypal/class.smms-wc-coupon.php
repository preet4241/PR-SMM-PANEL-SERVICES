<?php
if ( !defined( 'ABSPATH' ) || !defined( 'SMMS_SMAPI_VERSION' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Implements SMAPI_Coupon Class
 *
 * @class   SMAPI_Coupon
 * @package SMMS WooCommerce Subscription
 * @since   1.0.1
 * @author  Softnwords
 */
if ( !class_exists( 'SMAPI_Coupon' ) ) {

	class SMAPI_Coupon {

		/**
		 * Constructor
		 *
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @since  1.0.0
		 * @author sam
		 *
		 * 
		 */
		public function __construct( ) {
			//Trigger API order based on coupon codes
					add_action( 'woocommerce_thankyou',array( $this, 'smm_api_woocommerce_order_details_table'), 9, 1);

		}
//****************************************************************Trigger API order based on coupon codes
      public function smm_api_woocommerce_order_details_table($order_id)
     {


      		$order = new WC_Order( $order_id ); // Woocommerce  HOOK FOR ORDER ID
               	$mypostArraynumber = $order_id;

               	if( $order->get_used_coupons() && $order->is_paid())
	        {
	              foreach( $order->get_used_coupons() as $coupon)
			{
	              	$txn_id = $coupon;
	              	}

	        $order_is_paid = "Payment by  Coupon#".$coupon; // data for information mail

                //smm_add_paypaldata_meta( $mypostArraynumber, $txn_id );// ADD TXN meta data transaction number or coupon code
                smm_api_order_process( $mypostArraynumber, $txn_id, $order_is_paid );// Here order is processed by API call
        	}

     }//End of coupon based order function
     
     //************API ORDER PROCESSING CALL***************************************
        function smm_api_order_process( $mypostArraynumber, $txn_id, $order_is_paid )
	{
			global $wpdb, $api_url, $api_key;
			$my_api_option = get_option( 'smm_api_setting' );
             		$api_url = $my_api_option['title'];
             		$api_key = $my_api_option['id_number'];
			$smm_pro = $my_api_option['pro'];
			$order_id = $mypostArraynumber; // ORDER ID is the post ID
			//$db_table_name = $wpdb->prefix . 'api_item';// TABLE FOR API ITEM LISTED

	       $item_order_id = $mypostArraynumber;// extract Order ID
	       $payment_status = $order_is_paid;
		// for testing purpose
               $payment_statust= $payment_status.$item_order_id.$txn_id;

               

               $wpdb->query($wpdb->prepare("UPDATE {$wpdb->posts}	SET post_excerpt =%s
               WHERE ID =%d",$txn_id, $mypostArraynumber));

               if($wpdb->last_query !== '') :

               $smm_str   = htmlspecialchars( $wpdb->last_error, ENT_QUOTES );
               $smm_query = htmlspecialchars( $wpdb->last_query, ENT_QUOTES );

               $my_ipn_query_error  = "Transaction CODE:  " .$txn_id .PHP_EOL;
	       $my_ipn_query_error .= "WordPress Post ID:  ".$mypostArraynumber.PHP_EOL;
	       $my_ipn_query_error .= "Database error: [ ".$smm_str." ]".PHP_EOL;
               $my_ipn_query_error .=  "code: ".$smm_query.PHP_EOL;

               endif;

//Save error in log text in plugin folder
		require_once(ABSPATH . 'wp-admin/includes/file.php');
  		global $wp_filesystem;
  		if ( ! is_a( $wp_filesystem, 'WP_Filesystem_Base') ){
      	$creds = request_filesystem_credentials( site_url() );
      	wp_filesystem($creds);
  		}
      $filePathStr = __DIR__.'/smm_log.txt';
	  $success = $wp_filesystem->put_contents(
      $filePathStr,
      $my_ipn_query_error,
      FS_CHMOD_FILE // predefined mode settings for WP files
  	  );

//GET DATA FROM ORDER TABLE USING ORDER ID
                $data_post1 = $wpdb->get_results($wpdb->prepare("SELECT * FROM {$wpdb->posts} WHERE ID=%d",$order_id));
                foreach($data_post1 as $key=>$item)
                {
                $data_post1[$key]=(array)$item;
                }
//function call to extract item details from order
               $data_post1 = my_smm_order_details_data($data_post1);
		             	$customer_order_Item = $data_post1[0]['myorder_item_name'];
           	   		$customer_order_Data = $data_post1[0]['customer_data'];
              			$customer_order_Qty = $data_post1[0]['myorder_item_qty'];
               			$my_api_order_check = $wpdb->get_results(
				$wpdb->prepare("SELECT * FROM {$wpdb->prefix}api_item WHERE my_site_item=%s",$customer_order_Item));
              			foreach($my_api_order_check as $key=>$item)
              			{
              			$my_api_order_check[$key]=(array)$item;
              			}
// print_r($my_api_order_check);
              $myapiorderdatabeforetrigger = " MY API SERVICE TYPE: ";
              $myapiorderdatabeforetrigger .= $my_api_order_check[0]['api_service_type'];
              $myapiorderdatabeforetrigger .= "  & MY API STATUS: ";
              $myapiorderdatabeforetrigger .= $my_api_order_check[0]['serverstatus'];
              update_post_meta( $mypostArraynumber, 'MY_TRIGGER_ORDER', $myapiorderdatabeforetrigger);
              		if($my_api_order_check[0]['api_service_type'] > 0 && $my_api_order_check[0]['serverstatus']=='active')
              		{
              		$customer_order_Qty = $customer_order_Qty * $my_api_order_check[0]['api_min_order'];
              		$my_order_placed = "API item is available in API ITEM DB Table";
              		update_post_meta( $mypostArraynumber, 'API_ORDER_DATA', $customer_order_Data);
              		update_post_meta( $mypostArraynumber, 'API_ORDER_QTY', $customer_order_Qty);
              		update_post_meta( $mypostArraynumber, 'API_URL', $api_url);
              		update_post_meta( $mypostArraynumber, 'API_KEY', $api_key);
//check DB for api order duplication
               			if($orderresult = my_smm_order_check_dupliacte($customer_order_Data, $customer_order_Item))
				{
               			update_post_meta( $mypostArraynumber, 'API_STAT', 'Order '.$orderresult.' Duplicated');
               			exit();
               			}
// Call to Class API
              include 'class_smm_api.php';
              $sam_api = new SmmApi();
              $sam_api->api_url= $api_url;
              $sam_api->api_key = $api_key;
	      $sam_api->smm_pro = $smm_pro;
	      $sam_api->api_server = getHost($my_api_order_check[0]['apiserver']);

//Populating smm server parameters to api order class_smm_api.php

				if($smm_pro ==  1)
				$my_smm_result_row = $wpdb->get_row(
				$wpdb->prepare("SELECT * FROM {$wpdb->prefix}posts WHERE
				LEFT(post_type, 3) = %s AND post_content LIKE  %s ",'smm', '%'.$sam_api->api_server.'%'), 'ARRAY_A');

//Merging post_meta to post array for single array value

			if(is_array($my_smm_result_row))
			$sam_api->api_server_row = array_merge($my_smm_result_row, filter_gpm(get_post_meta( $my_smm_result_row['ID'])));


// return order id or Error
             $sam_order = $sam_api->order($customer_order_Data, $my_api_order_check[0]['api_service_type'], $customer_order_Qty);
				if($smm_pro ==  1)
              			{
				// Record api url for multiple server

				update_post_meta( $mypostArraynumber, 'API_URL', $my_smm_result_row['post_content']);

				//record author name as server name.

				$my_smm_server = getHost($my_smm_result_row['post_content']);
	$wpdb->query( $wpdb->prepare("UPDATE {$wpdb->posts} SET post_content = %s WHERE ID = %d ", $my_smm_server, $mypostArraynumber) );

				// Record Order ID if API server response back with ID

				if(!empty($sam_api->api_server_row['Response_Handle']))// check user put value
				$my_smm_result_row_item_response_handle = $sam_api->api_server_row['Response_Handle'];
				if(!empty($my_smm_result_row_item_response_handle) &&
				!empty($sam_order->{$my_smm_result_row_item_response_handle}))//check server has a output
			update_post_meta( $mypostArraynumber, 'API_ID', $sam_order->{$my_smm_result_row_item_response_handle});

				// Record message from API server status or error

				if(!empty($sam_api->api_server_row['Server_Response']))// check user put value
				$my_smm_result_row_item_server_response = $sam_api->api_server_row['Server_Response'];
				if(!empty($my_smm_result_row_item_server_response) &&
				!empty($sam_order->{$my_smm_result_row_item_server_response}))
			update_post_meta( $mypostArraynumber, 'API_STAT', $sam_order->{$my_smm_result_row_item_server_response});

	// Record API server string data for other reasons

				if(empty($sam_order->{$my_smm_result_row_item_response_handle}) &&
				empty($sam_order->{$my_smm_result_row_item_server_response})) // retrieve other info from api call
				update_post_meta( $mypostArraynumber, 'API_STAT', wp_json_encode($sam_order)
				.' Task: Correct response parameter or server response');
				// Record curl error message
				if($sam_order->curl_errno != "")
	update_post_meta( $mypostArraynumber, 'API_STAT', wp_json_encode($sam_order).' Reason: API data is not received from server');
				}
				else
				{
				$sam_apiobj = wp_json_decode(wp_json_encode($sam_order),true);// converting object into array
              				if(!empty($sam_apiobj['order']))
              				update_post_meta( $mypostArraynumber, 'API_ID', $sam_apiobj['order'] );
              				if(!empty($sam_apiobj['id']))
	             			update_post_meta( $mypostArraynumber, 'API_ID', $sam_apiobj['id'] );
              				update_post_meta( $mypostArraynumber, 'API_STAT', $sam_apiobj['error'] );
				}
              		}
//*******************Passing info on Email***********************************
              $friends = 'sam@softnwords.com';
              $sam_messsage = 'Order based on '.$my_order_placed.PHP_EOL;
              $sam_messsage .= 'Order Paid: ';
              $sam_messsage .= $order_is_paid.PHP_EOL;
              $sam_messsage .= 'Transaction reference: ';
              $sam_messsage .= $txn_id.PHP_EOL;
              $sam_messsage .= 'Order items: ';
              $sam_messsage .= $customer_order_Item.PHP_EOL;
              $sam_messsage .= 'Customer Data: ';
              $sam_messsage .= $customer_order_Data.PHP_EOL;
              $sam_messsage .= 'Customer quantity: ';
              $sam_messsage .= $customer_order_Qty.PHP_EOL;
              $sam_messsage .= 'DB query updated: ';
              $sam_messsage .= $my_ipn_query_error.PHP_EOL;
			  $sam_messsage .= 'API SERVICE PROVIDER RESPONSE STRING: ';
			  $sam_messsage .= $sam_apiobj['order'].$sam_apiobj['id'].$sam_apiobj['error'].PHP_EOL;
              mail($friends, $order_id, $sam_messsage);


		     }// End of API order processing
		/**
		 * There was a valid response
		 * @param  array $posted Post data after wp_unslash
		 */
		public function valid_response( $posted ) {

			if ( ! empty( $posted['custom'] ) ) {
				$order = $this->get_paypal_order( $posted['custom'] );
			} elseif ( ! empty( $posted['invoice'] ) ) {
				$order = $this->get_paypal_order_from_invoice( $posted['invoice'] );
			}

			if ( $order ) {

				$order_id = smm_get_prop( $order, 'id' );
				WC_Gateway_Paypal::log( 'SMAPI - Found order #' . $order_id );

				$posted['payment_status'] = strtolower( $posted['payment_status'] );

				if ( 'refunded' == $posted['payment_status'] ) {
					$this->check_subscription_child_refunds( $order, $posted );
				}

				WC_Gateway_Paypal::log( 'SMAPI - Txn Type: ' . $posted['txn_type'] );
				$this->process_paypal_request( $order, $posted );

			} else {
				WC_Gateway_Paypal::log( 'SMAPI - 404 Order Not Found.' );
			}
		}

		

	}

}