<?php


if ( !defined( 'ABSPATH' ) || !defined( 'SMMS_SMAPI_VERSION' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Implements SMAPI_Subscription_Paypal Class
 *
 * @class   SMAPI_Subscription_Paypal
 * @package SMMS WooCommerce Subscription
 * @since   1.0.0
 * @author  SMMS
 */
if ( !class_exists( 'SMAPI_Subscription_Paypal' ) ) {

	class SMAPI_Subscription_Paypal {

		/**
		 * Single instance of the class
		 *
		 * @var \SMAPI_Subscription_Cron
		 */
		protected static $instance;

		protected $wclog = '';

		protected $debug;
		protected $testmode;
		protected $email;
		protected $receiver_email;

		protected $api_username;
		protected $api_password;
		protected $api_signature;
		protected $api_endpoint;

		protected $setting_options;


		/**
		 * Returns single instance of the class
		 *
		 * @return \SMAPI_Subscription_Paypal
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

			$settings = get_option( 'woocommerce_paypal_settings' );

			if( $settings['enabled'] != 'yes'){
				return;
			}

			$this->setting_options = $settings;
			$this->debug           = ( isset( $settings['debug'] ) && $settings['debug'] == 'yes' ) ? true : false;
			$this->testmode        = ( isset( $settings['testmode'] ) && $settings['testmode'] == 'yes' ) ? true : false;
			$this->email           = ( isset( $settings['email'] ) ) ? $settings['email'] : '';
			$this->receiver_email  = ( isset( $settings['receiver_email'] ) ) ? $settings['receiver_email'] : $this->email;

			if ( $this->debug ) {
				$this->wclog = new WC_Logger();
			}

			// When necessary, set the PayPal args to be for a subscription instead of shopping cart
			add_filter( 'woocommerce_paypal_args', array( $this, 'subscription_args') );

			// Check if there's a subscription in a valid PayPal IPN request
			include_once( WC()->plugin_path() . '/includes/gateways/paypal/includes/class-wc-gateway-paypal-ipn-handler.php' );
			include_once( 'includes/class.smapi-paypal-ipn-handler.php' );

			new SMAPI_PayPal_IPN_Handler( $this->testmode, $this->receiver_email );

			//Set API credentials
			if ( ! empty( $settings['api_username'] ) && ! empty( $settings['api_password'] ) && ! empty( $settings['api_signature'] ) ) {

				$this->api_username  = $settings['api_username'];
				$this->api_password  = $settings['api_password'];
				$this->api_signature = $settings['api_signature'];
				$this->api_endpoint  = ( $settings['testmode'] == 'yes' ) ? 'https://api-3t.sandbox.paypal.com/nvp' : 'https://api-3t.paypal.com/nvp';

				add_filter( 'smapi_cancel_recurring_payment', array( $this, 'cancel_recurring_payment' ), 10, 2 );
			}


		}


		/**
		 * @param $args
		 *
		 * @return mixed
		 */
		public function subscription_args( $args ) {

			$order_info = $this->get_order_info( $args );

			if ( empty( $order_info ) || ! isset( $order_info['order_id'] ) ) {
				return $args;
			}

			$order = wc_get_order( $order_info['order_id'] );

			$is_a_renew = smm_get_prop( $order, 'is_a_renew' );

			if ( $is_a_renew == 'yes' ) {
				return $args;
			}

			//check if order has subscriptions
			$order_items = $order->get_items();

			if ( empty( $order_items ) ) {
				return $args;
			}

			$item_names       = array();
			$has_subscription = false;

			foreach ( $order_items as $key => $order_item  ) {

				$product_id = ( $order_item['variation_id'] ) ? $order_item['variation_id'] : $order_item['product_id'];
				$product    = wc_get_product( $product_id );

				if( SMMS_WC_Subscription()->is_subscription( $product_id ) ) {
					// It's a subscription
					$has_subscription = true;
					$args['cmd'] = '_xclick-subscriptions';

					//1 for reattempt failed recurring payments before canceling, use 0 for not
					$args['sra'] = apply_filters('smapi_reattempt_failed_recurring_payments', 1 );

					$subscription_info = wc_get_order_item_meta($key,'_subscription_info', true);

					$price_is_per      = smm_get_prop( $product, '_smapi_price_is_per' );
					$price_time_option = smm_get_prop( $product, '_smapi_price_time_option' );
					$price_time_option = smapi_get_price_time_option_paypal( $price_time_option );
					$max_length        = smm_get_prop( $product, '_smapi_max_length' );

					//order total

					if ( $subscription_info['order_total'] != $order->get_total() ) {
						$args['a1'] = wc_format_decimal( $order->get_total(), 2 );
						$args['p1'] = $price_is_per;
						$args['t1'] = $price_time_option;
					}

					$subscription_num = ( $max_length ) ? $max_length / $price_is_per : '';

					$args['a3'] = wc_format_decimal( $subscription_info['order_total'], 2 );
					$args['p3'] = $price_is_per;
					$args['t3'] = $price_time_option;

					if ( $subscription_num == '' || $subscription_num > 1 ) {
						$args['src'] = 1;
						if ( $subscription_num != '' ) {
							$args['srt'] = $subscription_num;
						}
					} else {
						$args['src'] = 0;
					}

				}

				if ( $order_item['qty'] > 1 ) {
					$item_names[] = $order_item['qty'] . ' x ' . $this->format_item_name( $order_item['name'] );
				}else{
					$item_names[] = $this->format_item_name( $order_item['name'] );
				}

			}


			if( ! $has_subscription ){
				return $args;
			}

			if ( count( $item_names ) > 1 ) {
				$args['item_name'] = $this->format_item_name(/* translators: %s: search term */ sprintf( __( 'Order %s', 'smm-api' ), $order->get_order_number() . " - " . implode( ', ', $item_names ) ) );
			} else {
				$args['item_name'] = implode( ', ', $item_names );
			}

			$args['rm'] = 2;
			if ( $this->debug ) {
				$this->wclog->add( 'paypal', 'SMAP - Subscription Request: ' . print_r( $args, true ) );
			}

			return $args;
		}

		/**
		 * @param $args
		 *
		 * @return array|mixed|object
		 */
		protected function get_order_info( $args ) {
			return isset( $args['custom'] ) ? json_decode( $args['custom'], true ) : false;
		}


		/**
		 * @param $item_name
		 *
		 * @return string
		 */
		protected static function format_item_name( $item_name ) {
			if ( strlen( $item_name ) > 127 ) {
				$item_name = substr( $item_name, 0, 124 ) . '...';
			}
			return html_entity_decode( $item_name, ENT_NOQUOTES, 'UTF-8' );
		}

		/**
		 * Cancel recurring payment if the subscription has a paypal subscription
		 *
		 * @param bool               $result
		 * @param SMAPI_Subscription $subscription
		 *
		 * @return bool
		 */
		public function cancel_recurring_payment( $result, $subscription ) {

			if ( ! isset( $subscription->paypal_subscriber_id ) || $subscription->paypal_subscriber_id == '' ) {
				return true;
			}

			$response = $this->change_paypal_subscription_status( $subscription->paypal_subscriber_id, 'Cancel' );

			if ( $this->debug ) {
				$this->wclog->add( 'paypal', 'SMAP - Subscription Cancel Request: #' . $subscription->id . '. Details of response: ' . print_r( $response, true ) );
			}

			return ( $response ) ? $result : false;
		}



		/**
		 * @param $subscriber_id
		 * @param $status
		 *
		 * @return bool
		 */
		public function change_paypal_subscription_status( $subscriber_id, $status ) {

			$response = $this->change_subscription_status( $subscriber_id, $status ) ;

			if ( ! empty( $response  )) {

				if ( $response['ACK'] == 'Failure' ) {
					if ( $this->debug ) {
						$this->wclog->add( 'paypal', "SMAP - Paypal was called to change status for '. $subscriber_id.' has Failed: " . $response['L_LONGMESSAGE0'] );
					}

					return $response['L_LONGMESSAGE0'];
				} else {
					return true;
				}
			}


		}

		function change_subscription_status( $subscriber_id , $status  ) {

			$api_request = array('USER' => urlencode( $this->api_username ),
			                'PWD' => urlencode( $this->api_password ),
			                'SIGNATURE' => urlencode( $this->api_signature ),
			                'VERSION' => '76.0',
			                'METHOD' => 'ManageRecurringPaymentsProfileStatus',
			                'PROFILEID' => urlencode( $subscriber_id ),
			                'ACTION' =>  urlencode( $status ),
			                'NOTE' =>  urlencode( /* translators: %s: search term */ sprintf( __( 'Subscription %1$s at %2$s', 'smm-api' ), strtolower( $status ), get_bloginfo( 'name' ) ) ));

			

			
			// Request response from PayPal
			$response = wp_remote_post( $this->api_endpoint, array(
                        'method'      => 'POST',
                        'timeout'     => 45,
                        'redirection' => 5,
                        'httpversion' => '1.0',
                        'blocking'    => true,
                        'headers'     => array(),
                        'body'        => $api_request,
                        'cookies'     => array()
                                        )
            );

			// If no response was received from PayPal there is no point parsing the response
			if(  is_wp_error( $response ) ){
				if ( $this->debug ) {
					$this->wclog->add( 'paypal', 'SMAP - Paypal was called to change status for  ' . $subscriber_id . 'has failed: ' . $response->get_error_message()  );
				}
			}
			if ( $this->debug ) {
				$this->wclog->add( 'paypal', 'SMAP - Paypal was called to change status for  ' . print_r($response, true));
			}

			$response = wp_remote_retrieve_body( $response );
			// An associative array is more usable than a parameter string
			parse_str( $response, $parsed_response );

			return $parsed_response;
		}

		/**
		 * @param $subscriber_id
		 *
		 * @return bool
		 */
		public function get_recurring_payments_profile( $subscriber_id ) {

			$request_data = array(
				'VERSION'   => '115.0',
				'USER'      => $this->api_username.'DFG',
				'PWD'       => $this->api_password,
				'SIGNATURE' => $this->api_signature,
				'METHOD'    => 'UpdateRecurringPaymentsProfile',
				'MAXFAILEDPAYMENTS' => 10,
				'PROFILEID' => $subscriber_id,
			);

			$response = wp_remote_post( $this->api_endpoint, array(
					'method'    => 'POST',
					'body'      => $request_data,
					'timeout'   => 100,
					'sslverify' => false
				)
			);



			if ( empty( $response['body'] ) && $this->debug ) {
				$this->wclog->add( 'paypal', 'SMAP -Paypal was called has failed: Empty Paypal Response.'. print_r($request_data, true) );
			}

			if ( isset( $response['response']['message'] ) && $response['response']['message'] == 'OK' ) {
				$response_args = wp_parse_args( $response['body'] );
				$this->wclog->add( 'paypal', print_r($response_args, true ) );
				if ( $response_args['ACK'] == 'Failure' ) {
					if ( $this->debug ) {
						$this->wclog->add( 'paypal', "SMAP - Paypal was called to change status for '. $subscriber_id.' has Failed: " . $response_args['L_LONGMESSAGE0'] );
					}

					return $response_args['L_LONGMESSAGE0'];
				}else{
					return true;
				}
			}
		}



	}

}

/**
 * Unique access to instance of SMAPI_Subscription_Paypal class
 *
 * @return \SMAPI_Subscription_Paypal
 */
function SMAPI_Subscription_Paypal() {
	return SMAPI_Subscription_Paypal::get_instance();
}

