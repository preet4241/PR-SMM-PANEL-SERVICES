<?php

if ( ! defined( 'ABSPATH' ) || ! defined( 'SMMS_SMAPI_VERSION' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Implements SMAPI_Subscription_Order Class
 *
 * @class   SMAPI_Subscription_Order
 * @package SMMS WooCommerce Subscription
 * @since   1.0.0
 * @author  SMMS
 */
if ( ! class_exists( 'SMAPI_Subscription_Order' ) ) {

	/**
	 * Class SMAPI_Subscription_Order
	 */
	class SMAPI_Subscription_Order {

		/**
		 * Single instance of the class
		 *
		 * @var \SMAPI_Subscription_Order
		 */
		protected static $instance;

		/**
		 * @var string
		 */
		public $post_type_name = 'smapi_subscription';

		/**
		 * @var array
		 */
		public $subscription_meta = array();
		public $cart_item_order_item; // 👈 Add this to avoid depreciated warning
		/**
		 * Returns single instance of the class
		 *
		 * @return \SMAPI_Subscription_Order
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

			
				add_action( 'woocommerce_new_order_item', array( 
				$this, 'add_subscription_order_item_meta' ), 20, 3 );
				add_action( 'woocommerce_checkout_order_processed', array( 
				$this, 'get_extra_subscription_meta' ), 10, 2 );
			


			// Add subscriptions from orders
				add_action( 'woocommerce_checkout_order_processed', array(
				$this,'check_order_for_subscription'), 100, 2 );

			// Start subscription after payment received
				//add_action( 'woocommerce_payment_complete', array( $this, 'payment_complete' ) );
				//add_action( 'woocommerce_order_status_completed', array( $this, 'payment_complete' ) );
				//add_action( 'woocommerce_order_status_processing', array( $this, 'payment_complete' ) );
			// For api trigger
				add_action( 'woocommerce_payment_complete', array( 
				    $this, 'smm_api_order_trigger' ) );
				add_action( 'woocommerce_checkout_create_order_line_item',array( 
				    $this, 'smm_cfwc_add_custom_data_to_order'), 10, 4 );
			 


			   

		}


		/**
		 * Save the options of subscription in an array with order item id
		 *
		 * @access   public
		 *
		 * @param  $item_id
		 * @param  $item WC_Order_Item_Product
		 * @param  $order_id
		 *
		 * @return string
		 * @internal param int $cart_item_key
		 *
		 * @internal param int $item_id
		 * @internal param array $values
		 */
		public function add_subscription_order_item_meta( $item_id, $item, $order_id) {
			if( isset( $item->legacy_cart_item_key) ){
				$this->cart_item_order_item[ $item->legacy_cart_item_key ] = $item_id;
			}
		}
/**
 * Add custom field to order object
 */
		public function smm_cfwc_add_custom_data_to_order( $item, $cart_item_key, $values, $order ) {
			
			foreach( $item as $cart_item_key=>$values ) {
			    
			// Taken from cart page $cart_item_data['smm-cfwc-title-field'] 
			if( isset( $values['smm-cfwc-title-field'] ) )
			$item->add_meta_data( esc_html__( 'Entered', 'smm-api' ), $values['smm-cfwc-title-field'], true );
			
			}
			
			}
			
		
		/**
		 * Save the options of subscription in an array with order item id
		 *
		 * @access public
		 *
		 * @param  $item_id int
		 * @param  $values array
		 * @param  $cart_item_key int
		 *
		 * @return string
		 */
		public function add_subscription_order_item_meta_before_wc3( $item_id, $values, $cart_item_key ) {
			$this->cart_item_order_item[ $cart_item_key ] = $item_id;
		}


		/**
		 * Save some info if a subscription is in the cart
		 *
		 * @access public
		 *
		 * @param  $order_id int
		 * @param  $posted array
		 *
		 */
		public function get_extra_subscription_meta( $order_id, $posted ) {

			if ( ! SMMS_WC_Subscription()->cart_has_subscriptions() ) {
				return;
			}



			$this->actual_cart = WC()->session->get( 'cart' );

			add_filter('smapi_price_check', '__return_false' );

			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

				$product = $cart_item['data'];
				$id      = $product->get_id();

				if ( SMMS_WC_Subscription()->is_subscription( $product ) ) {


					$new_cart = new WC_Cart();

					$subscription_info = array(
						'shipping' => array(),
						'taxes' => array(),
					);

					if ( isset( $cart_item['variation'] ) ) {
						$subscription_info['variation'] = $cart_item['variation'];
					}
					

					$new_cart_item_key = $new_cart->add_to_cart(
						$cart_item['product_id'],
						$cart_item['quantity'],
						( isset( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : '' ),
						( isset( $cart_item['variation'] ) ? $cart_item['variation'] : '' ),
						$cart_item
					);

					$new_cart = apply_filters( 'smapi_add_cart_item_data', $new_cart, $new_cart_item_key, $cart_item );

					$new_cart_item_keys = array_keys( $new_cart->cart_contents );

					$applied_coupons = WC()->cart->get_applied_coupons();

					foreach ( $new_cart_item_keys as $new_cart_item_key ) {
						//shipping
						if ( $new_cart->needs_shipping() && $product->needs_shipping() ) {
							if ( method_exists( WC()->shipping, 'get_packages' ) ) {
								$packages = WC()->shipping->get_packages();

								foreach ( $packages as $key => $package ) {
									if ( isset( $package['rates'][ $posted['shipping_method'][ $key ] ] ) ) {
										if ( isset( $package['contents'][ $cart_item_key ] ) || isset( $package['contents'][ $new_cart_item_key ] ) ) {
											// This shipping method has the current subscription
											$shipping['method']      = $posted['shipping_method'][ $key ];
											$shipping['destination'] = $package['destination'];

											break;
										}
									}
								}


								if (  isset( $shipping ) ) {
									// Get packages based on renewal order details
									$new_packages = apply_filters( 'woocommerce_cart_shipping_packages', array(
										0 => array(
											'contents'        => $new_cart->get_cart(),
											'contents_cost'   => isset( $new_cart->cart_contents[ $new_cart_item_key ]['line_total'] ) ? $new_cart->cart_contents[ $new_cart_item_key ]['line_total'] : 0,
											'applied_coupons' => $new_cart->applied_coupons,
											'destination'     => $shipping['destination'],
										),
									) );

									//subscription_shipping_method_temp
									$save_temp_session_values = array(
										'shipping_method_counts'  => WC()->session->get( 'shipping_method_counts' ),
										'chosen_shipping_methods' => WC()->session->get( 'chosen_shipping_methods' ),
									);

									WC()->session->set( 'shipping_method_counts', array( 1 ) );
									WC()->session->set( 'chosen_shipping_methods', array( $shipping['method'] ) );

									add_filter( 'woocommerce_shipping_chosen_method', array( $this, 'change_shipping_chosen_method_temp' ) );
									$this->subscription_shipping_method_temp = $shipping['method'];

									WC()->shipping->calculate_shipping( $new_packages );

									remove_filter( 'woocommerce_shipping_chosen_method', array( $this, 'change_shipping_chosen_method_temp' ) );

									unset( $this->subscription_shipping_method_temp );
								}
							}

						}

						foreach ( $applied_coupons as $coupon_code ) {
							$coupon        = new WC_Coupon( $coupon_code );
							$coupon_type   = $coupon->get_discount_type();
							$coupon_amount = $coupon->get_amount();
							$valid   = smapi_coupon_is_valid( $coupon, WC()->cart );
							if ( $valid && in_array( $coupon_type, array( 'recurring_percent', 'recurring_fixed' ) ) ) {

								$price     = $new_cart->cart_contents[ $new_cart_item_key ]['line_subtotal'];
								$price_tax = $new_cart->cart_contents[ $new_cart_item_key ]['line_subtotal_tax'];

								switch ( $coupon_type ) {
									case 'recurring_percent':
										$discount_amount     = round( ( $price / 100 ) * $coupon_amount, WC()->cart->dp );
										$discount_amount_tax = round( ( $price_tax / 100 ) * $coupon_amount, WC()->cart->dp );
										break;
									case 'recurring_fixed':
										$discount_amount     = ( $price < $coupon_amount ) ? $price : $coupon_type;
										$discount_amount_tax = 0;
										break;
								}

								$subscription_info['coupons'][] = array(
									'coupon_code'         => $coupon_code,
									'discount_amount'     => $discount_amount * $cart_item['quantity'],
									'discount_amount_tax' => $discount_amount_tax * $cart_item['quantity']
								);

								$new_cart->applied_coupons[]    = $coupon_code;
								$new_cart->coupon_subscription  = true;

							}
						}

						if ( ! empty( $new_cart->applied_coupons ) ) {
							WC()->cart->discount_cart       = 0;
							WC()->cart->discount_cart_tax   = 0;
							WC()->cart->subscription_coupon = 1;
						}

						$new_cart->calculate_totals();

						// Recalculate totals
						//save some order settings
						$subscription_info['order_shipping']     = wc_format_decimal( $new_cart->shipping_total );
						$subscription_info['order_shipping_tax'] = wc_format_decimal( $new_cart->shipping_tax_total );
						$subscription_info['cart_discount']      = wc_format_decimal( $new_cart->get_cart_discount_total() );
						$subscription_info['cart_discount_tax']  = wc_format_decimal( $new_cart->get_cart_discount_tax_total() );
						$subscription_info['order_discount']     = $new_cart->get_total_discount();
						$subscription_info['order_tax']          = wc_format_decimal( $new_cart->tax_total );
						$subscription_info['order_subtotal']     = wc_format_decimal( $new_cart->subtotal, get_option( 'woocommerce_price_num_decimals' ) );
						$subscription_info['order_total']        = wc_format_decimal( $new_cart->total, get_option( 'woocommerce_price_num_decimals' ) );
						$subscription_info['line_subtotal']      = wc_format_decimal( $new_cart->cart_contents[$new_cart_item_key]['line_subtotal'] );
						$subscription_info['line_subtotal_tax']  = wc_format_decimal( $new_cart->cart_contents[$new_cart_item_key]['line_subtotal_tax'] );
						$subscription_info['line_total']         = wc_format_decimal( $new_cart->cart_contents[$new_cart_item_key]['line_total'] );
						$subscription_info['line_tax']           = wc_format_decimal( $new_cart->cart_contents[$new_cart_item_key]['line_tax'] );
						$subscription_info['line_tax_data']      = $new_cart->cart_contents[$new_cart_item_key]['line_tax_data'];

					}


					// Get shipping details
					if ( $product->needs_shipping() ) {

						if ( isset( $shipping['method'] ) && isset( WC()->shipping->packages[0]['rates'][ $shipping['method'] ] ) ) {

							$method                        = WC()->shipping->packages[0]['rates'][ $shipping['method'] ];
							$subscription_info['shipping'] = array(
								'name'      => $method->label,
								'method_id' => $method->id,
								'cost'      => wc_format_decimal( $method->cost ),
								'taxes'     => $method->taxes,
							);

							// Set session variables to original values and recalculate shipping for original order which is being processed now
							WC()->session->set( 'shipping_method_counts', $save_temp_session_values['shipping_method_counts'] );
							WC()->session->set( 'chosen_shipping_methods', $save_temp_session_values['chosen_shipping_methods'] );
							WC()->shipping->calculate_shipping( WC()->shipping->packages );
						}

					}

					//CALCULATE TAXES
					$taxes = $new_cart->get_cart_contents_taxes();
					$shipping_taxes = $new_cart->get_shipping_taxes();

					foreach ( $new_cart->get_tax_totals() as $rate_key => $rate ) {

						$rate_args = array(
							'name'                => $rate_key,
							'rate_id'             => $rate->tax_rate_id,
							'label'               => $rate->label,
							'compound'            => absint( $rate->is_compound ? 1 : 0 ),

						);

						if ( version_compare( WC()->version, '3.2.0', '>=' ) ) {
							$rate_args['tax_amount']          = wc_format_decimal( isset( $taxes[ $rate->tax_rate_id ] ) ? $taxes[ $rate->tax_rate_id ] : 0 );
							$rate_args['shipping_tax_amount'] = wc_format_decimal( isset( $shipping_taxes[ $rate->tax_rate_id ] ) ? $shipping_taxes[ $rate->tax_rate_id ] : 0 );
						} else {
							$rate_args['tax_amount']          = wc_format_decimal( isset( $new_cart->taxes[ $rate->tax_rate_id ] ) ? $new_cart->taxes[ $rate->tax_rate_id ] : 0 );
							$rate_args['shipping_tax_amount'] = wc_format_decimal( isset( $new_cart->shipping_taxes[ $rate->tax_rate_id ] ) ? $new_cart->shipping_taxes[ $rate->tax_rate_id ] : 0 );
						}

						$subscription_info['taxes'][] = $rate_args;
					}

					$subscription_info['payment_method'] = '';
					$subscription_info['payment_method_title'] = '';
					if ( isset( $posted['payment_method'] ) && $posted['payment_method'] ) {
						$enabled_gateways = WC()->payment_gateways->get_available_payment_gateways();

						if ( isset( $enabled_gateways[$posted['payment_method']] ) ) {
							$payment_method = $enabled_gateways[$posted['payment_method']];
							$payment_method->validate_fields();
							$subscription_info['payment_method']       = $payment_method->id;
							$subscription_info['payment_method_title'] = $payment_method->get_title();
						}
					}

					if ( isset( $this->cart_item_order_item[ $cart_item_key ] ) ) {
						$order_item_id =  $this->cart_item_order_item[ $cart_item_key ];
						$this->subscriptions_info['order'][$order_item_id] = $subscription_info;
						wc_add_order_item_meta( $order_item_id, '_subscription_info', $subscription_info, true );
					}

				}

			}

			WC()->session->set( 'cart', $this->actual_cart ) ;
		}

		/**
		 * Save some info if a subscription is in the cart
		 *
		 * @access public
		 *
		 * @param  $order_id int
		 * @param  $posted array
		 *
		 */
		public function get_extra_subscription_meta_before_wc3( $order_id, $posted ) {

			if ( ! SMMS_WC_Subscription()->cart_has_subscriptions() ) {
				return;
			}


			$this->actual_cart = WC()->cart;

			foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {

				$id      = ! empty( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : $cart_item['product_id'];
				$product = wc_get_product( $id );

				if ( SMMS_WC_Subscription()->is_subscription( $id ) ) {

					$new_cart = new WC_Cart();

					$subscription_info = array(
						'shipping' => array(),
						'taxes'    => array(),
					);

					if ( isset( $cart_item['variation'] ) ) {
						$subscription_info['variation'] = $cart_item['variation'];
					}


					$new_cart_item_key = $new_cart->add_to_cart(
						$cart_item['product_id'],
						$cart_item['quantity'],
						( isset( $cart_item['variation_id'] ) ? $cart_item['variation_id'] : '' ),
						( isset( $cart_item['variation'] ) ? $cart_item['variation'] : '' ),
						$cart_item
					);

					$new_cart = apply_filters( 'smapi_add_cart_item_data', $new_cart, $new_cart_item_key, $cart_item );

					$new_cart_item_keys = array_keys( $new_cart->cart_contents );

					$applied_coupons = WC()->cart->get_applied_coupons();

					foreach ( $new_cart_item_keys as $new_cart_item_key ) {
						//shipping
						if ( $new_cart->needs_shipping() && $product->needs_shipping() ) {
							if ( method_exists( WC()->shipping, 'get_packages' ) ) {
								$packages = WC()->shipping->get_packages();
								foreach ( $packages as $key => $package ) {

									if ( isset( $package['rates'][ $posted['shipping_method'][ $key ] ] ) ) {

										if ( isset( $package['contents'][ $cart_item_key ] ) || isset( $package['contents'][ $new_cart_item_key ] ) ) {
											// This shipping method has the current subscription

											$shipping['method']      = $posted['shipping_method'][ $key ];
											$shipping['destination'] = $package['destination'];

											break;
										}
									}

								}

								if ( ! isset( $shipping ) ) {
									continue;
								}

								// Get packages based on renewal order details
								$new_packages = apply_filters( 'woocommerce_cart_shipping_packages', array(
									0 => array(
										'contents'        => $new_cart->get_cart(),
										'contents_cost'   => isset( $new_cart->cart_contents[ $new_cart_item_key ]['line_total'] ) ? $new_cart->cart_contents[ $new_cart_item_key ]['line_total'] : 0,
										'applied_coupons' => $new_cart->applied_coupons,
										'destination'     => $shipping['destination'],
									),
								) );

								//subscription_shipping_method_temp

								$save_temp_session_values = array(
									'shipping_method_counts'  => WC()->session->get( 'shipping_method_counts' ),
									'chosen_shipping_methods' => WC()->session->get( 'chosen_shipping_methods' ),
								);

								WC()->session->set( 'shipping_method_counts', array( 1 ) );
								WC()->session->set( 'chosen_shipping_methods', array( $shipping['method'] ) );

								add_filter( 'woocommerce_shipping_chosen_method', array(
									$this,
									'change_shipping_chosen_method_temp'
								) );
								$this->subscription_shipping_method_temp = $shipping['method'];

								WC()->shipping->calculate_shipping( $new_packages );

								remove_filter( 'woocommerce_shipping_chosen_method', array(
									$this,
									'change_shipping_chosen_method_temp'
								) );

								unset( $this->subscription_shipping_method_temp );

							}


						}

						foreach ( $applied_coupons as $coupon_code ) {
							$coupon = new WC_Coupon( $coupon_code );
							$valid  = smapi_coupon_is_valid( $coupon, WC()->cart );
							if ( $valid && in_array( $coupon->discount_type, array(
									'recurring_percent',
									'recurring_fixed'
								) ) ) {

								$price     = $new_cart->cart_contents[ $new_cart_item_key ]['line_subtotal'];
								$price_tax = $new_cart->cart_contents[ $new_cart_item_key ]['line_subtotal_tax'];
								switch ( $coupon->discount_type ) {
									case 'recurring_percent':
										$discount_amount     = round( ( $price / 100 ) * $coupon->amount, WC()->cart->dp );
										$discount_amount_tax = round( ( $price_tax / 100 ) * $coupon->amount, WC()->cart->dp );
										break;
									case 'recurring_fixed':
										$discount_amount     = ( $price < $coupon->amount ) ? $price : $coupon->amount;
										$discount_amount_tax = 0;
										break;
								}

								$subscription_info['coupons'][] = array(
									'coupon_code'         => $coupon_code,
									'discount_amount'     => $discount_amount * $cart_item['quantity'],
									'discount_amount_tax' => $discount_amount_tax * $cart_item['quantity']
								);
								$new_cart->applied_coupons[]    = $coupon_code;
								$new_cart->coupon_subscription  = true;
							}
						}

						if ( ! empty( $new_cart->applied_coupons ) ) {
							WC()->cart->discount_cart       = 0;
							WC()->cart->discount_cart_tax   = 0;
							WC()->cart->subscription_coupon = 1;
						}

						$new_cart->calculate_totals();

						// Recalculate totals
						//save some order settings
						$subscription_info['order_shipping']     = wc_format_decimal( $new_cart->shipping_total );
						$subscription_info['order_shipping_tax'] = wc_format_decimal( $new_cart->shipping_tax_total );
						$subscription_info['cart_discount']      = wc_format_decimal( $new_cart->get_cart_discount_total() );
						$subscription_info['cart_discount_tax']  = wc_format_decimal( $new_cart->get_cart_discount_tax_total() );
						$subscription_info['order_discount']     = $new_cart->get_total_discount();
						$subscription_info['order_tax']          = wc_format_decimal( $new_cart->tax_total );
						$subscription_info['order_subtotal']     = wc_format_decimal( $new_cart->subtotal, get_option( 'woocommerce_price_num_decimals' ) );
						$subscription_info['order_total']        = wc_format_decimal( $new_cart->total, get_option( 'woocommerce_price_num_decimals' ) );
						$subscription_info['line_subtotal']      = wc_format_decimal( $new_cart->cart_contents[ $new_cart_item_key ]['line_subtotal'] );
						$subscription_info['line_subtotal_tax']  = wc_format_decimal( $new_cart->cart_contents[ $new_cart_item_key ]['line_subtotal_tax'] );
						$subscription_info['line_total']         = wc_format_decimal( $new_cart->cart_contents[ $new_cart_item_key ]['line_total'] );
						$subscription_info['line_tax']           = wc_format_decimal( $new_cart->cart_contents[ $new_cart_item_key ]['line_tax'] );
						$subscription_info['line_tax_data']      = $new_cart->cart_contents[ $new_cart_item_key ]['line_tax_data'];

					}


					// Get shipping details
					if ( $product->needs_shipping() ) {

						if ( isset( $shipping['method'] ) && isset( WC()->shipping->packages[0]['rates'][ $shipping['method'] ] ) ) {

							$method                        = WC()->shipping->packages[0]['rates'][ $shipping['method'] ];
							$subscription_info['shipping'] = array(
								'name'      => $method->label,
								'method_id' => $method->id,
								'cost'      => wc_format_decimal( $method->cost ),
								'taxes'     => $method->taxes,
							);

							// Set session variables to original values and recalculate shipping for original order which is being processed now
							WC()->session->set( 'shipping_method_counts', $save_temp_session_values['shipping_method_counts'] );
							WC()->session->set( 'chosen_shipping_methods', $save_temp_session_values['chosen_shipping_methods'] );
							WC()->shipping->calculate_shipping( WC()->shipping->packages );
						}


					}

					//CALCULATE TAXES
					foreach ( $new_cart->get_tax_totals() as $rate_key => $rate ) {
						$subscription_info['taxes'][] = array(
							'name'                => $rate_key,
							'rate_id'             => $rate->tax_rate_id,
							'label'               => $rate->label,
							'compound'            => absint( $rate->is_compound ? 1 : 0 ),
							'tax_amount'          => wc_format_decimal( isset( $new_cart->taxes[ $rate->tax_rate_id ] ) ? $new_cart->taxes[ $rate->tax_rate_id ] : 0 ),
							'shipping_tax_amount' => wc_format_decimal( isset( $new_cart->shipping_taxes[ $rate->tax_rate_id ] ) ? $new_cart->shipping_taxes[ $rate->tax_rate_id ] : 0 ),
						);
					}

					$subscription_info['payment_method']       = '';
					$subscription_info['payment_method_title'] = '';
					if ( isset( $posted['payment_method'] ) && $posted['payment_method'] ) {
						$enabled_gateways = WC()->payment_gateways->get_available_payment_gateways();

						if ( isset( $enabled_gateways[ $posted['payment_method'] ] ) ) {
							$payment_method = $enabled_gateways[ $posted['payment_method'] ];
							$payment_method->validate_fields();
							$subscription_info['payment_method']       = $payment_method->id;
							$subscription_info['payment_method_title'] = $payment_method->get_title();
						}
					}

					if ( isset( $this->cart_item_order_item[ $cart_item_key ] ) ) {
						$order_item_id                                       = $this->cart_item_order_item[ $cart_item_key ];
						$this->subscriptions_info['order'][ $order_item_id ] = $subscription_info;
						wc_add_order_item_meta( $order_item_id, '_subscription_info', $subscription_info, true );
					}

				}

			}

			WC()->session->set( 'cart', $this->actual_cart );
		}

		/**
		 * @param $order_id
		 * @param $posted
		 */
		/**
		 * Check in the order if there's a subscription and create it
		 *
		 * @access public
		 *
		 * @param  $order_id int
		 * @param  $posted   array
		 *
		 * @return void
		 */
		public function check_order_for_subscription( $order_id, $posted ) {

			$order          = wc_get_order( $order_id );
			$order_items    = $order->get_items();
			$order_args     = array();
			$user_id        = $order->get_customer_id();
			$order_currency = $order->get_currency();
			//check id the the subscriptions are created
			$subscriptions = smm_get_prop( $order, 'subscriptions', true );
           foreach ($order->get_items() as $item_id => $item) {
			    // Get the WC_Order_Item_Product object properties in an array
                $item_data = $item->get_data();
                // get the WC_Product object
                $product = wc_get_product($item['product_id']);
			    
			    if($product->is_type( 'simple' )){
			        
			    $input_text_box_radio_saved   = smm_get_prop( $product, 'locate_input_box' );
			    }// end of product type simple
			    
			    if($product->is_type( 'variable' )){
                        // Loop through the variation IDs

                    foreach( $product->get_children() as $key => $variation_id ) {
                        // Get an instance of the WC_Product_Variation Object
                        $variation = wc_get_product( $variation_id );
            
                        // Get meta of variation ID for radio button
                        $input_text_box_radio_saved = $variation->get_meta( 'locate_input_box' );
                        }
			    
			    }//end of product type variable
			  
			        if( $input_text_box_radio_saved == 'product' ) {
			
			            $name = smm_get_prop( $product, 'smm_custom_text_field_title' );
			            $smm_other_custom_field = get_post_meta($order_id, $name, true);
			            
			            wc_add_order_item_meta( $item_id, 'Entered', $smm_other_custom_field );
						}
			    
			}
           
           
           
			if ( empty( $order_items ) || ! empty( $subscriptions ) ) {
				return;
			}

			$subscriptions = smm_get_prop( $order, 'subscriptions', true );
			
			$subscriptions = is_array( $subscriptions ) ? $subscriptions : array();

			foreach ( $order_items as $key => $order_item ) { 

				
					$_product = $order_item->get_product();
				

				if ( $_product == false ) {
					continue;
				}

				$id = $_product->get_id();

				$args = array();

				if ( SMMS_WC_Subscription()->is_subscription( $id ) ) {


					if ( ! isset( $this->subscriptions_info['order'][ $key ] ) ) {
						continue;
					}

					$subscription_info = $this->subscriptions_info['order'][ $key ];

					$max_length        = smm_get_prop( $_product, '_smapi_max_length' );
					$price_is_per      = smm_get_prop( $_product, '_smapi_price_is_per' );
					$price_time_option = smm_get_prop( $_product, '_smapi_price_time_option' );
					$fee               = smm_get_prop( $_product, '_smapi_fee' );
					
                    //Adding customer choser frequency and duration
                    if( ( $data = WC()->session->get('subscribe_smm_data') ) ){
                        $price_time_option  = preg_replace('/\s+/', '', $data['price_time_option_string']);// days/weeks remove space
                        $price_is_per       = preg_replace('/\s+/', '', $data[$product->get_id()]);//frequency remove space
                        
                    }
                    $duration          = ( empty( $max_length ) ) ? '' : smapi_get_timestamp_from_option( 0, $max_length, $price_time_option );
					// DOWNGRADE PROCESS
					// Set a trial period for the new downgrade subscription so the next payment will be due at the expiration date of the previous subscription
					if ( get_user_meta( get_current_user_id(), 'smapi_trial_' . $id, true ) != '' ) {
						$trial_info        = get_user_meta( get_current_user_id(), 'smapi_trial_' . $id, true );
						$trial_period      = $trial_info['trial_days'];
						$trial_time_option = 'days';
					} else {
						$trial_period      = smm_get_prop( $_product, '_smapi_trial_per' );
						$trial_time_option = smm_get_prop( $_product, '_smapi_trial_time_option' );
					}

					//if this subscription is a downgrade the old subscription will be cancelled
					$subscription_to_update_id = get_user_meta( get_current_user_id(), 'smapi_downgrade_' . $id, true );
					if ( $subscription_to_update_id != '' ) {
						$args_cancel_subscription = array(
							'subscription_to_cancel' => $subscription_to_update_id,
							'process_type'           => 'downgrade',
							'product_id'             => $id,
							'user_id'                => get_current_user_id()
						);

						$order_args['_smapi_subscritpion_to_cancel'] = $args_cancel_subscription;
					}

					/****************/

					// UPGRADE PROCESS
					// if the we are in the upgrade process and the prorate must be done
					$subscription_old_id       = $pay_gap = '';
					$prorate_length            = smm_get_prop( $_product, '_smapi_prorate_length' );
					$gap_payment               = smm_get_prop( $_product, '_smapi_gap_payment' );
					$subscription_upgrade_info = get_user_meta( get_current_user_id(), 'smapi_upgrade_' . $id, true );

					if ( ! empty( $subscription_upgrade_info ) ) {
						$subscription_old_id = $subscription_upgrade_info['subscription_id'];
						$pay_gap             = $subscription_upgrade_info['pay_gap'];
						$trial_period        = '';

						//if this subscription is an upgrade the old subscription will be cancelled
						if ( $subscription_old_id != '' ) {
							$args_cancel_subscription = array(
								'subscription_to_cancel' => $subscription_old_id,
								'process_type'           => 'upgrade',
								'product_id'             => $id,
								'user_id'                => get_current_user_id()
							);

							$order_args['_smapi_subscritpion_to_cancel'] = $args_cancel_subscription;
						}

					}

					if ( $prorate_length == 'yes' && ! empty( $max_length ) && $subscription_old_id != '' ) {

						$old_sub         = smapi_get_subscription( $subscription_old_id );
						$activity_period = $old_sub->get_activity_period();

						if ( $price_time_option == $old_sub->price_time_option ) {
							$new_max_length = $max_length - ceil( $activity_period / smapi_get_timestamp_from_option( 0, 1, $old_sub->price_time_option ) );
						} else {
							$new_duration   = smapi_get_days( $duration - $activity_period );
							$new_max_length = $new_duration / smapi_get_timestamp_from_option( 0, 1, $price_time_option );
						}

						$max_length = abs( $new_max_length );
					}

					if ( $gap_payment == 'yes' && $pay_gap > 0 ) {
						//change the fee of the subscription adding the total amount of the previous rates
						$fee = $pay_gap;
					}


					/****************/

					// fill the array for subscription creation
					$args = array(
						'product_id'              => $order_item['product_id'],
						'variation_id'            => $order_item['variation_id'],
						'variation'               => ( isset( $subscription_info['variation'] ) ? $subscription_info['variation'] : '' ),
						'product_name'            => $order_item['name'],

						//order details
						'order_id'                => $order_id,
						'order_item_id'           => $key,
						'order_ids'               => array( $order_id ),
						'line_subtotal'           => $subscription_info['line_subtotal'],
						'line_total'              => $subscription_info['line_total'],
						'line_subtotal_tax'       => $subscription_info['line_subtotal_tax'],
						'line_tax'                => $subscription_info['line_tax'],
						'line_tax_data'           => $subscription_info['line_tax_data'],
						'cart_discount'           => $subscription_info['cart_discount'],
						'cart_discount_tax'       => $subscription_info['cart_discount_tax'],
						'coupons'                 => ( isset( $subscription_info['coupons'] ) ) ? $subscription_info['coupons'] : '',
						'order_total'             => $subscription_info['order_total'],
						'subscription_total'      => $subscription_info['order_total'],
						'order_tax'               => $subscription_info['order_tax'],
						'order_subtotal'          => $subscription_info['order_subtotal'],
						'order_discount'          => $subscription_info['order_discount'],
						'order_shipping'          => $subscription_info['order_shipping'],
						'order_shipping_tax'      => $subscription_info['order_shipping_tax'],
						'subscriptions_shippings' => $subscription_info['shipping'],
						'payment_method'          => $subscription_info['payment_method'],
						'payment_method_title'    => $subscription_info['payment_method_title'],
						'order_currency'          => $order_currency,
						
						'prices_include_tax'      => smm_get_prop( $order, '_prices_include_tax' ),
						//user details
						'quantity'                => $order_item['qty'],
						'user_id'                 => $user_id,
						'customer_ip_address'     => smm_get_prop( $order, '_customer_ip_address' ),
						'customer_user_agent'     => smm_get_prop( $order, '_customer_user_agent' ),
						//item subscription detail
						'price_is_per'            => $price_is_per,
						'price_time_option'       => $price_time_option,
						'max_length'              => $max_length,
						'trial_per'               => $trial_period,
						'trial_time_option'       => $trial_time_option,
						'fee'                     => $fee,
						'order_date'              => $order->get_date_created(),
						'num_of_rates'            => $price_is_per
						//'num_of_rates'            => ( $max_length && $price_is_per ) ? $max_length / $price_is_per : ''
					);

					$subscription = new SMAPI_Subscription( '', $args );

					//save the version of plugin in the order
					$order_args['_smapi_order_version'] = SMMS_SMAPI_VERSION;

					if ( $subscription->id ) {
						$subscriptions[]             = $subscription->id;
						$order_args['subscriptions'] = $subscriptions;
						/* translators: %d: search term */
						$order->add_order_note( sprintf( __( 'A new subscription #%d has been created from this order', 'smm-api' ), esc_attr($subscription->id) ) );

						$product_id = ( $subscription->variation_id ) ? $subscription->variation_id : $subscription->product_id;
						delete_user_meta( $subscription->user_id, 'smapi_trial_' . $product_id );
					}
				}
			}

			if ( $order_args ) {
				smm_save_prop( $order, $order_args, false, true );
			}
		}
		/** Order processing for api server call
		 * @param $order_id
		 */
        public function smm_api_order_trigger( $order_id ) {
                        $order         = wc_get_order( $order_id );
			            $subscriptions = smm_get_prop( $order, 'subscriptions', true );
			            
				        $subscription_order = ($subscriptions != '' ) ? $this->payment_complete( $order_id ):'0';
                        

			            // This is how to grab line items from the order 
	                    $line_items = $order->get_items();
						$order_attributes =array();
						
						
	                    // custom attribute for quantity valid for variations
	                    $SMMS_QTYLANG = (get_option('smmqty_attribute') != 'Quantity')?
                                 get_option('smmqty_attribute'):'Quantity';
                        if($SMMS_QTYLANG =='')$SMMS_QTYLANG = 'quantity';
						
	                    foreach ( $line_items as $item ){
	            
	                            $product = $item->get_product();
	                            $product_name = $product->get_name();
	                            // get order item meta data (in an unprotected array)
                                $product_id = $item->get_product_id(); 
                                $quantity = $item['qty'];
                            if($product->is_type( 'simple' )){
                
                            // FIND FOR SMM API CHECK BOX
                            $api_check_box_enabled = 
                            smm_get_prop( $product, '_smapi_api' ) == "yes" ? 1 : null ;
                
	                        //FIND CUSTOMER INPUT BASED ON RADIO BUTTON SELECT
	                        $other_plugin_meta_name = smm_get_prop( $product, 'smm_custom_text_field_title' );
	                        //take from order meta  or item line meta
	                        $other_plugin_meta_value = 
	                        smm_get_prop( $order, $other_plugin_meta_name ) != "" ? 
	                        smm_get_prop( $order, $other_plugin_meta_name ): 
	                        $item->get_meta($other_plugin_meta_name, true );
	                        //Cutomer INPUT $item_meta_data FOR API CALL for simple product
	                        $item_meta_data =
	                        smm_get_prop( $product, 'locate_input_box' ) =="product" ? 
	                        $item->get_meta('Entered', true ) : $other_plugin_meta_value;
	                        //********end of Cutomer INPUT *****************
	            
	                        //SERVER ID AND ITEM ID TAKEN FROM PRODUCT OBJECT
	                        $api_server_list_options_saved = 
	                        smm_get_prop( $product, '_smapi_server_name_option' );
	                        $api_item_list_options_saved   = smm_get_prop( $product, '_smapi_service_id_option' );          
	         
	                        }// end of product type simple
	         
	                        if($product->is_type( 'variation' )){
	                        
	                        $variation_id = $item->get_variation_id();
							// Get the variation attributes of product
                            $variation_attributes = $product->get_variation_attributes();
               
                            //FIND CUSTOMER INPUT BASED ON RADIO BUTTON SELECT
	                        $other_plugin_meta_name = get_post_meta( $variation_id, 'var_smm_customer_input_field_label', true );
	            
	                        //take from order meta  or item line meta
	                        $other_plugin_meta_value = 
	                        smm_get_prop( $order, $other_plugin_meta_name ) != "" ? 
	                        smm_get_prop( $order, $other_plugin_meta_name ): 
	                        $item->get_meta($other_plugin_meta_name, true );
	                        //Cutomer INPUT $item_meta_data FOR API CALL for VAR PRODUCT
	                        $item_meta_data =
	                        get_post_meta( $variation_id, 'locate_input_box', true ) =="product" ? 
	                        $item->get_meta('Entered', true ) : 
	                        $other_plugin_meta_value;
	                        //********end of Cutomer INPUT *****************
	            
	                        //SERVER ID AND ITEM ID TAKEN FROM VARIATIOON OBJECT 
                            $api_item_list_options_saved = 
                            get_post_meta( $variation_id, 'var_smapi_service_id_option', true );
                            $api_item_lists = explode(' ', $api_item_list_options_saved, 2);
                            $api_item_list_options_saved = $api_item_lists[0];
			                $api_server_list_options_saved = 
			                get_post_meta( $variation_id, 'var_smapi_server_name_option', true );
			    
			    
			    
			                $api_check_box_enabled = 
			                get_post_meta( $variation_id, 'variable_smm_api', true ) =='on' ? 1 : null;
	            
                                // Loop through each selected attributes
								$product_attributes = array();
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
                                    if (preg_match("/^$SMMS_QTYLANG/i" , $attribute_name))
                                    $quantity = $attribute_value * $quantity;
									//attributes and values to arr for order processing
									
                                }//end of selected attributes						
							if ( $item instanceof WC_Order_Item_Product ) {								
								if($item->get_meta_data()){								
						
									foreach ( $item->get_meta_data() as $meta ) {								
									if ( $meta->value != null) {
									$order_attributes[$meta->key] = $meta->value;								
									$debugdata[$meta->key] = $meta->key;
									$debugdata[$meta->value] = $meta->value;								
										}
									}//end of foreach metadata								
								}								
								}							
								} //end of product type variable
	                            $Count_repeat_order = 
	                            $this->find_repeated_order($item_meta_data,$product_id);
								// repeat order control
			                    $repeat_order_str = implode(",", $Count_repeat_order);
								//repeate order allowed for subscritions
			                    if($subscription_order == 1 || get_option('smmapi_duporder')  ==  'yes' )
			                    $Count_repeat_order = array();
	                            $int_api_item = 
	                            (int) filter_var($api_item_list_options_saved, FILTER_SANITIZE_NUMBER_INT);    
	                            $order_item_meta =   
	                            get_post_meta( $api_server_list_options_saved, $api_item_list_options_saved, true );
	        
	                            $order_item_meta_obj = json_decode($order_item_meta); 
		                            // get quantity from product name title
	                                $smm_title_pack  = mb_substr($product_name, 0, 5);
	                                $smm_result_num = filter_var($smm_title_pack, FILTER_SANITIZE_NUMBER_INT);
	            
	                                //quantity overrides the order qunatity
	                                if (is_numeric($smm_result_num))
	                                $quantity = $smm_result_num * $quantity;  
	            
		                            // qty based on MIN/MAX DATA
		                            if ($quantity < $order_item_meta_obj->f_min_order)
	                                $quantity = $order_item_meta_obj->f_min_order;
	                                if ($quantity > $order_item_meta_obj->f_max_order)
	                                $quantity = $order_item_meta_obj->f_max_order;
	                               
	                                if(count($Count_repeat_order) <= 1 && $api_check_box_enabled == 1){
	                                // API call based on server name as post ID
	                                        $new_api_order = 
	                                        new SMAPI_Api($api_server_list_options_saved);
	                                        $Order_response = 
	                                        $new_api_order->order($item_meta_data,  $int_api_item, $quantity, $order_attributes);
	        								/* translators: %s: search term */
                                            $message = sprintf('%s received for product', wp_json_encode($Order_response));
                                            if(is_int($Order_response))
                                            SMAPI_Subscription()->update_subscription( $order_id, $Order_response );
			                                $order->update_meta_data( 'Response', $message );
			                                $_POST['order_status'] = 'wc-processing';
                                            $order->save();
                                            // Testing Slot
	                                        }
	                                        elseif(count($Count_repeat_order) > 1 ){
	            
	                                        $message ='Please change the status for orders: ';
				                            $message1 = ' for calling API SERVER';
	                                        $order->update_meta_data( 'Result', $message.$repeat_order_str.$message1 );
                                            $order->save();
	                                        // wc_add_notice( $message, 'error' ); // Testing Slot
	                                        }
	                                        elseif(count($Count_repeat_order) < 1 ){
	            
	                                        $message ='Order has mossing api item: ';
				                            $message1 = ' for calling API SERVER';
	                                        $order->update_meta_data( 'Result', $message.$repeat_order_str.$message1 );
                                            $order->save();
	                                        // wc_add_notice( $message, 'error' ); // Testing Slot
	                                        }
	                                }//END OF EACH LINE ITEM ISIDE THE ORDER
            }// End of function
        //Check for Order repeating for single user
        public function find_repeated_order($item_meta_data, $product_id_check){
           ## ==> Define HERE the statuses of that orders 
				$order_statuses = array('wc-processing');
				//, 'wc-completed' 'wc-on-hold', 
			## ==> Define HERE the customer ID
				$customer_user_id = get_current_user_id(); // current user ID here for example

				// Getting current customer orders
			$customer_orders = wc_get_orders( array(
				'meta_key' => '_customer_user',
				'meta_value' => $customer_user_id,
				'post_status' => $order_statuses,
				'numberposts' => -1
				) );

			$product_id_ = array();
			// Loop through each customer WC_Order objects
			foreach($customer_orders as $order ){

			// Order ID (added WooCommerce 3+ compatibility)
			$order_id = $order->get_id();

			// Iterating through current orders items
			foreach($order->get_items() as $item_id => $item){

        // The corresponding product ID (Added Compatibility with WC 3+) 
			$product_id = method_exists( $item, 'get_product_id' ) ? 
			$item->get_product_id() : $item['product_id'];

        // Order Item data (unprotected on Woocommerce 3)
        
             if( $item_meta_data == wc_get_order_item_meta( $item_id, 'Entered', true ) && 
			 $product_id == $product_id_check)
				$product_id_[] = $order_id;

				}
			} 
			return $product_id_;
        }
		/**
		 * @param $order_id
		 */
		public function payment_complete( $order_id ) {

			$order         = wc_get_order( $order_id );
			$subscriptions = smm_get_prop( $order, 'subscriptions', true );
			if ( $subscriptions != '' ) {
				foreach ( $subscriptions as $subscription_id ) {
					
			        $sbs                 = smapi_get_subscription( $subscription_id );
				    $renew_order         = $sbs->subscription_meta_data['renew_order'];

					if ( $renew_order == 1 ) {
						$sbs->update_subscription( $order_id );
					} elseif ( $renew_order == 0 ) {
						$sbs->start_subscription( $order_id );
					}
				}
				return $renew_order;
			}
		}

		public function renew_order( $subscription_id ) {

			$subscription   = new SMAPI_Subscription( $subscription_id );
			$status         = 'renewed';
			
	
			$order = wc_create_order( $args = array(
				'status'      => 'renew',
				'customer_id' => $subscription->subscription_meta_data['user_id']
			) );

			$args = array(
				'subscriptions'  => array( $subscription_id ),
				'payment_method' => $subscription->subscription_meta_data['payment_method'],
				'order_currency' => $subscription->subscription_meta_data['order_currency']
			);
/*
				$customer_note = smm_get_prop( $parent_order, 'customer_note' );
				$args['customer_note'] = $customer_note;



			// get billing
			$billing_fields = $subscription->get_address_fields( 'billing' );
			// get shipping
			$shipping_fields = $subscription->get_address_fields( 'shipping' );

			$args = array_merge( $args, $shipping_fields, $billing_fields );

			if ( version_compare( WC()->version, '2.7.0', '>=' ) ) {

				foreach ( $billing_fields as $key => $field ) {
					$set = 'set_' . $key;
					//method_exists( $order, $set ) && $order->$set( $field );
				}

				foreach ( $shipping_fields as $key => $field ) {
					$set = 'set_' . $key;
				//	method_exists( $order, $set ) && $order->$set( $field );
				}

				smm_set_prop( $order, $args );

			}
*/
			$order_id = smm_get_order_id( $order );

			foreach ( $args as $key => $value  ) {
				if( $key == 'subscriptions'){
					add_post_meta( $order_id, $key, $value );
				}
				update_post_meta( $order_id, '_'.$key, $value );
			}

			$_product = wc_get_product( ( isset( $subscription->subscription_meta_data['variation_id'] ) && !empty( $subscription->subscription_meta_data['variation_id'] ) ) ? $subscription->subscription_meta_data['variation_id'] : $subscription->subscription_meta_data['product_id'] );

			$total = 0;
			$tax_total = 0;

			$variations = array();

			$item_id = $order->add_product(
				$_product,
				$subscription->subscription_meta_data['quantity'],
				array(
					'variation' => $variations,
					'totals'    => array(
						'subtotal'     => $subscription->subscription_meta_data['line_subtotal'],
						'subtotal_tax' => $subscription->subscription_meta_data['line_subtotal_tax'],
						'total'        => $subscription->subscription_meta_data['line_total'],
						'tax'          => $subscription->subscription_meta_data['line_tax'],
						'tax_data'     => maybe_unserialize($subscription->subscription_meta_data['line_tax_data'])
					)
				)
			);

			if ( !$item_id ) {
				throw new Exception( wp_sprintf(/* translators: %s: search term */ esc_html__( 'Error %s: unable to create the order. Please try again.', 'smm-api'), esc_attr($item_id )) );
			} else {
				$total     += floatval( $subscription->subscription_meta_data['line_total'] );
				$tax_total += floatval( $subscription->subscription_meta_data['line_tax'] );
				$metadata  = get_metadata( 'order_item', $subscription->subscription_meta_data['order_item_id'] );

				if ( $metadata ) {
					foreach ( $metadata as $key => $value ) {
						if ( apply_filters( 'smapi_renew_order_item_meta_data', is_array( $value ) 
						&& count( $value ) == 1, $subscription->order_item_id, $key, $value ) ) 
							add_metadata( 'order_item', $item_id, $key, maybe_unserialize( $value[0] ), true );
						
						if($key == "Entered")
							add_metadata( 'order_item', $item_id, $key, maybe_unserialize( $value[0]), true );


					}
				}
			}

			$shipping_cost = 0;

/*			//Shipping
			if ( ! empty( $subscription->subscription_meta_data['subscriptions_shippings'] ) ) {

				$shipping_item_id = wc_add_order_item( $order_id, array(
					'order_item_name' => $subscription->subscriptions_shippings['name'],
					'order_item_type' => 'shipping',
				) );

				$shipping_cost     = $subscription->subscriptions_shippings['cost'];
				$shipping_cost_tax = 0;

				wc_add_order_item_meta( $shipping_item_id, 'method_id', $subscription->subscriptions_shippings['method_id'] );
				wc_add_order_item_meta( $shipping_item_id, 'cost', wc_format_decimal( $shipping_cost ) );
				wc_add_order_item_meta( $shipping_item_id, 'taxes', $subscription->subscriptions_shippings['taxes'] );

				if ( ! empty( $subscription->subscriptions_shippings['taxes'] ) ) {
					foreach ( $subscription->subscriptions_shippings['taxes'] as $tax_cost ) {
						$shipping_cost_tax += $tax_cost;
					}
				}

				if ( version_compare( WC()->version, '2.7.0', '>=' ) ) {
					$order->set_shipping_total( $shipping_cost );
					$order->set_shipping_tax( $subscription->subscriptions_shippings['taxes'] );
					$order->save();
				} else {
					$order->set_total( wc_format_decimal( $shipping_cost ), 'shipping' );
				}
			}else{
				do_action('smapi_add_custom_shipping_costs', $order, $subscription );
			}

			$cart_discount_total = 0;
			$cart_discount_total_tax = 0;

			//coupons
			if( !empty( $subscription->coupons) ){
				foreach( $subscription->coupons  as $coupon ){
					$order->add_coupon( $coupon['coupon_code'], $coupon['discount_amount'],  $coupon['discount_amount_tax']);
					$cart_discount_total += $coupon['discount_amount'];
					$cart_discount_total_tax += $coupon['discount_amount_tax'];
				}
			}
			if ( version_compare( WC()->version, '2.7.0', '>=' ) ) {
				$order->set_discount_total($cart_discount_total);

				if ( isset( $subscription->subscriptions_shippings['taxes'] ) && $subscription->subscriptions_shippings['taxes'] ) {
					/**
					 * this fix the shipping taxes removed form WC settings
					 * if in a previous tax there was the taxes this will be forced
					 * even if they are disabled for the shipping

					add_action( 'woocommerce_find_rates', array( $this, 'add_shipping_tax'), 10 );
				}
				$order->update_taxes();
				$order->calculate_totals();
			}else{
				$order->set_total( $cart_discount_total, 'cart_discount' );
				$order->set_total( $cart_discount_total_tax, 'cart_discount_tax' );
				$order->update_taxes();
				$totals = $order->calculate_totals();
				$order->set_total($totals);
			}
*/
			$order_id = smm_get_order_id( $order );
			//attach the new order to the subscription
			$subscription->subscription_meta_data['order_ids'][] = $order_id;

			//update_post_meta( $subscription->id,'order_ids', $subscription->order_ids );

			$order->add_order_note( /* translators: %s: search term */ sprintf( __( 'This order has been created to renew subscription #%1$s for %2$d', 'smm-api' ), esc_url(admin_url( 'post.php?post=' . $subscription->id . '&action=edit' )), esc_attr($subscription->id)));

			//$subscription->set( 'renew_order', $order_id );
            
			smm_save_prop( $order, array( 'status' => $status, 'is_a_renew' => 'yes') );

			do_action( 'smapi_renew_subscription', $order_id, $subscription_id );

			return $order_id;

		}

		/**
		 * @param $subscription_id
		 *
		 * @return mixed
		 * @throws Exception
		 */
		public function renew_order_old( $subscription_id ) {

			$subscription   = new SMAPI_Subscription( $subscription_id );
			$subscription_meta = $subscription->get_subscription_meta();

			$order = wc_create_order( $args = array(
				'status'      => 'on-hold',
				'customer_id' => $subscription_meta['user_id']
			) );


			$args = array(
				'subscriptions'      => array( $subscription_id ),
				'billing_first_name' => $subscription_meta['billing_first_name'],
				'billing_last_name'  => $subscription_meta['billing_last_name'],
				'billing_company'    => $subscription_meta['billing_company'],
				'billing_address_1'  => $subscription_meta['billing_address_1'],
				'billing_address_2'  => $subscription_meta['billing_address_2'],
				'billing_city'       => $subscription_meta['billing_city'],
				'billing_state'      => $subscription_meta['billing_state'],
				'billing_postcode'   => $subscription_meta['billing_postcode'],
				'billing_country'    => $subscription_meta['billing_country'],
				'billing_email'      => $subscription_meta['billing_email'],
				'billing_phone'      => $subscription_meta['billing_phone'],

				'shipping_first_name' => $subscription_meta['shipping_first_name'],
				'shipping_last_name'  => $subscription_meta['shipping_last_name'],
				'shipping_company'    => $subscription_meta['shipping_company'],
				'shipping_address_1'  => $subscription_meta['shipping_address_1'],
				'shipping_address_2'  => $subscription_meta['shipping_address_2'],
				'shipping_city'       => $subscription_meta['shipping_city'],
				'shipping_state'      => $subscription_meta['shipping_state'],
				'shipping_postcode'   => $subscription_meta['shipping_postcode'],
				'shipping_country'    => $subscription_meta['shipping_country'],
			);

			foreach ( $args as $key => $value ) {
				smm_save_prop( $order, '_' . $key, $value );
			}


			$_product = wc_get_product( ( isset( $subscription_meta['variation_id'] ) && ! empty( $subscription_meta['variation_id'] ) ) ? $subscription_meta['variation_id'] : $subscription_meta['product_id'] );

			$total     = 0;
			$tax_total = 0;

			$variations = array();

			$order_id = smm_get_order_id( $order );
			$item_id = $order->add_product(
				$_product,
				$subscription_meta['quantity'],
				array(
					'variation' => $variations,
					'totals'    => array(
						'subtotal'     => $subscription_meta['line_subtotal'],
						'subtotal_tax' => $subscription_meta['line_subtotal_tax'],
						'total'        => $subscription_meta['line_total'],
						'tax'          => $subscription_meta['line_tax'],
						'tax_data'     => maybe_unserialize( $subscription_meta['line_tax_data'] )
					)
				)
			);

			if ( ! $item_id ) {
				throw new Exception(  wp_sprintf(/* translators: %s: search term */ esc_html__( 'Error %d: unable to create the order. Please try again.', 'smm-api' ), esc_attr($item_id) ) );
			} else {
				$total += $subscription_meta['line_total'];
				$tax_total += $subscription_meta['line_tax'];
			}

			$shipping_cost = 0;
			//Shipping
			if ( ! empty( $subscription_meta['subscriptions_shippings'] ) ) {
				foreach ( $subscription_meta['subscriptions_shippings'] as $ship ) {

					$shipping_item_id = wc_add_order_item( $order_id, array(
						'order_item_name' => $ship['method']->label,
						'order_item_type' => 'shipping',
					) );

					$shipping_cost += $ship['method']->cost;
					wc_add_order_item_meta( $shipping_item_id, 'method_id', $ship['method']->method_id );
					wc_add_order_item_meta( $shipping_item_id, 'cost', wc_format_decimal( $ship['method']->cost ) );
					wc_add_order_item_meta( $shipping_item_id, 'taxes', $ship['method']->taxes );
				}

				if ( version_compare( WC()->version, '2.7.0', '>=' ) ) {
					$order->set_shipping_total( $shipping_cost );
					$order->set_shipping_tax( $subscription->subscriptions_shippings['taxes'] );
				} else {
					$order->set_total( wc_format_decimal( $shipping_cost ), 'shipping' );
				}

			}

			if ( version_compare( WC()->version, '2.7.0', '>=' ) ) {
				$order->calculate_taxes();
				$order->calculate_totals();
			}else{
				$order->set_total( $total + $tax_total + $shipping_cost );
				$order->update_taxes();
			}

			//attach the new order to the subscription
			$subscription_meta['order_ids'][] = $order_id;
			$subscription->set('order_ids', $subscription_meta['order_ids']);

			$order->add_order_note(/* translators: %s: search term */ sprintf( __( 'This order has been created to renew the subscription #%d', 'smm-api' ), esc_attr($subscription_id )) );

			return $order_id;

		}

		/**
		 * @param $subscription SMAPI_Subscription
		 *
		 * @return string
		 */
		public function get_renew_order_status( $subscription = null ){

			$new_status = 'on-hold';

			if ( ! is_null( $subscription )) {
				$new_status = 'pending';
			}

			//the status must be register as wc status
			$status = apply_filters( 'smapi_renew_order_status', $new_status, $subscription );

			return $status;
		}
		
		

	}
}

/**
 * Unique access to instance of SMAPI_Subscription_Order class
 *
 * @return \SMAPI_Subscription_Order
 */
function SMAPI_Subscription_Order() {
	return SMAPI_Subscription_Order::get_instance();
}