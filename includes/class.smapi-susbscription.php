<?php

if ( !defined( 'ABSPATH' ) || !defined( 'SMMS_SMAPI_VERSION' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Implements SMAPI_Subscription Class
 *
 * @class   SMAPI_Subscription
 * @package SMMS WooCommerce Subscription
 * @since   1.0.0
 * @author  SMMS
 */
if ( !class_exists( 'SMAPI_Subscription' ) ) {

    class SMAPI_Subscription {



        public $subscription_meta_data = array(
            'status'                 => 'pending',
            'start_date'             => '',
            'payment_due_date'       => '',
            'expired_date'           => '',
            'cancelled_date'         => '',
            'payed_order_list'       => array(),
            'product_id'             => '',
            'variation_id'           => '',
            'product_name'           => '',
            'quantity'               => '',
            'line_subtotal'          => '',
            'line_total'             => '',
            'line_subtotal_tax'      => '',
            'line_tax'               => '',
            'line_tax_data'          => '',

            'cart_discount'          => '',
            'cart_discount_tax'      => '',

            'order_total'            => '',
            'order_currency'         => '',
            'renew_order'            => 0,

            'prices_include_tax'     => '',

            'payment_method'         => '',
            'payment_method_title'   => '',

            'subscriptions_shippings'=> '',

            'price_is_per'           => '',
            'price_time_option'      => '',
            'max_length'             => '',

            'order_ids'              => array(),
            'order_id'               => '',
            'user_id'                => 0,
            'customer_ip_address'    => '',
            'customer_user_agent'    => '',

            'billing_first_name'     => '',
            'billing_last_name'      => '',
            'billing_company'        => '',
            'billing_address_1'      => '',
            'billing_address_2'      => '',
            'billing_city'           => '',
            'billing_state'          => '',
            'billing_postcode'       => '',
            'billing_country'        => '',
            'billing_email'          => '',
            'billing_phone'          => '',
            'rates_payed'            => 0,
            'shipping_first_name'    => '',
            'shipping_last_name'     => '',
            'shipping_company'       => '',
            'shipping_address_1'     => '',
            'shipping_address_2'     => '',
            'shipping_city'          => '',
            'shipping_state'         => '',
            'shipping_postcode'      => '',
            'shipping_country'       => '',
            'num_of_rates'           => 0,
            'subscription_id'        => 0,
            'api_order_ids'          => array(),
            'smapi_free_version'     => SMMS_SMAPI_VERSION
        );

	    /**
	     * The subscription (post) ID.
	     *
	     * @var int
	     */
	    public $id = 0;


	    /**
	     * @var string
	     */
	    public $price_time_option;

	    /**
	     * @var int
	     */
	    public $variation_id;

	    /**
	     * $post Stores post data
	     *
	     * @var $post WP_Post
	     */
	    public $post = null;

	    /**
	     * $post Stores post data
	     *
	     * @var string
	     */
	    public $status;


        /**
         * Constructor
         *
         * Initialize plugin and registers actions and filters to be used
         *
         * @since  1.0.0
         * @author sam
         */
        public function __construct( $subscription_id = 0, $args = array() ) {
            add_action( 'init', array( $this, 'register_post_type' ) );
            add_action( 'init', array( $this,'smm_register_manual_order_status') );
            add_filter( 'wc_order_statuses', array( $this, 'add_manual_to_order_statuses') );
	        //populate the subscription if $subscription_id is defined
	        if ( $subscription_id ) {
		        $this->id = $subscription_id;
		        $subscription_meta_arr = $this->get_var_sub( 'subscribed'); 
		        
		        if(empty($subscription_meta_arr)) // load values to class)
		        // save values to post meta
		        $this->set_var_sub( 'subscribed', $this->subscription_meta_data );
		        else   
		        $this->subscription_meta_data = wp_parse_args( $subscription_meta_arr, $this->subscription_meta_data );
		        $this->populate();
	        }

	        //add a new subscription if $args is passed
	        if ( $subscription_id == '' && ! empty( $args ) ) {
		         
		        $this->id = $this->add_subscription( $args );
		        $this->subscription_meta_data['subscription_id'] = $this->id;
		        $this->set_var_sub( 'subscribed', $this->subscription_meta_data );
	        }

        }
        // Register new status
        public function smm_register_manual_order_status() {
            if( get_option('smmapi_manual')  !=  'no' ){
                register_post_status( 'wc-manual', array(
        'label'                     => 'Manual',
        'public'                    => true,
        'show_in_admin_status_list' => true,
        'show_in_admin_all_list'    => true,
        'exclude_from_search'       => false,/* translators: search here */
        'label_count'               => _n_noop( 'Manual (%s)', 'Manuals (%s)', 'smm-api' )
             ) );// phpcs:ignore
             
            }
        }
        // Add custom status to order status list
        public function add_manual_to_order_statuses( $order_statuses ) {
            $new_order_statuses = array();
            if( get_option('smmapi_manual')  !=  'no' )
                foreach ( $order_statuses as $key => $status ) {
                    $new_order_statuses[ $key ] = $status;
                        if ( 'wc-processing' === $key ) {
                        $new_order_statuses['wc-manual'] = 'Manual';
                        
                        }
                }
            return $new_order_statuses;
            }
	    /**
	     * __get function.
	     *
	     * @param string $key
	     *
	     * @return mixed
	     */
	    public function __get( $key ) {
		    $value = get_post_meta( $this->id, $key, true );

		    if ( ! empty( $value ) ) {
			    $this->$key = $value;
		    }

		    return $value;
	    }
	    // get values from meta text string decoded
        public function get_var_sub( $meta_key ){ 
            $string_data = get_post_meta( $this->id, $meta_key, true );
            return json_decode($string_data,true);
            
            
        }
        // set array values as meta string encoded
        public function set_var_sub( $meta_key, $meta_arr ){
            
            $meta_text = update_post_meta( $this->id, $meta_key, wp_json_encode($meta_arr));
            
        }
	    /**
	     * set function.
	     *
	     * @param string $property
	     * @param mixed  $value
	     *
	     * @return bool|int
	     */
	    public function set( $property, $value ) {

		    $this->$property = $value;

		    return update_post_meta( $this->id, $property, $value );
	    }

	    /**
	     * get function.
	     *
	     * @param string $property
	     * @param mixed  $value
	     *
	     * @return bool|int
	     */
	    public function get( $property ) {
		    return isset( $this->$property ) ? $this->$property : '';
	    }

	    public function __isset( $key ) {
		    if ( ! $this->id ) {
			    return false;
		    }

		    return metadata_exists( 'post', $this->id, $key );
	    }

	    /**
	     * Populate the subscription
	     *
	     * @return void
	     * @since  1.0.0
	     */
	    public function populate() {

		    $this->post = get_post( $this->id );

		    foreach ( $this->get_subscription_meta() as $key => $value ) {
			    $this->$key = $value;
		    }

		    do_action( 'smapi_subscription_loaded', $this );
	    }

	    /**
	     * @param $args
	     *
	     * @return int|WP_Error
	     */
	    public function add_subscription( $args ) {

            $subscription_id = wp_insert_post( array(
                'post_status' => 'publish',
	            'post_type'   => 'smapi_subscription',
            ) );

            if( $subscription_id ){
	            $this->id = $subscription_id;
	            $meta     = apply_filters( 'smapi_add_subcription_args', wp_parse_args( $args, $this->get_default_meta_data() ), $this );
	            $this->update_subscription_meta( $meta );
	            $this->populate();
            }

            return $subscription_id;
        }

        /**
         * Update post meta in subscription
         *
         *
         * @since  1.0.0
         * @author sam
         * @return void
         */
        function update_subscription_meta( $meta ){
            foreach( $meta as $key => $value ){
	            //update_post_meta( $this->id, $key, $value);
	           $this->subscription_meta_data[$key] = $value;
            }
            $this->set_var_sub( 'subscribed', $this->subscription_meta_data );
        }

	    /**
	     * @param $order_id
	     *
	     * @internal param $subscription_id
	     */
	    public function start_subscription( $order_id) {
	        $order         = wc_get_order( $order_id );
			$subscriptions = smm_get_prop( $order, 'subscriptions', true );
			if ( $subscriptions != '' ) 
				foreach ( $subscriptions as $subscription_id ){
				    
                $this->id                     = $subscription_id;
                $this->subscription_meta_data = $this->get_var_sub( 'subscribed');
                      
				}                 
		    $payed = $this->subscription_meta_data['payed_order_list'];

		    //do not nothing if this subscription has payed with this order
		    if ( ! empty( $payed ) && is_array( $payed ) && in_array( $order_id, $payed ) ) {
			    //return;
		    }

		    $payed = empty( $payed ) ? array() : $payed;

		    
		    $new_status  = 'active';
		    $rates_payed = 1;
		    if ( empty($this->subscription_meta_data['start_date'])) {
			     $this->subscription_meta_data['start_date'] = current_time('timestamp');
			    
		    }

		    if ( empty($this->subscription_meta_data['payment_due_date'])) {
			    $this->subscription_meta_data['payment_due_date'] = 
		        $this->get_next_payment_due_date( 0, $this->subscription_meta_data['start_date'] );
		         
		    }

		    if (empty( $this->subscription_meta_data['expired_date'] ) && (int)$this->subscription_meta_data['max_length']) {
			    $timestamp = smapi_get_timestamp_from_option( current_time('timestamp'), (int) $this->subscription_meta_data['price_is_per'], $this->subscription_meta_data['price_time_option'] );
			   
		        $this->subscription_meta_data['expired_date'] = $timestamp;
		        
		    }

		    
            $this->subscription_meta_data['status'] = $new_status;
		    do_action( 'smapi_customer_subscription_payment_done_mail', $this );

		    $payed[] = $order_id;

		    
		    $this->subscription_meta_data['rates_payed'] = $rates_payed;
		    $this->subscription_meta_data['payed_order_list'] = $payed;
		    $this->subscription_meta_data['renew_order'] = 1;
            $this->set_var_sub( 'subscribed', $this->subscription_meta_data );
             $args = array();
                $args['ID'] = $this->id;
                $args['post_title' ] = 'active'; // status of sub post
                //new due date for next order 1 iteration price is per =1
                $args['post_content' ] = smapi_get_timestamp_from_option( current_time('timestamp'), 1, $this->subscription_meta_data['price_time_option'] );
			    
                $args['post_excerpt' ] = (int)$this->subscription_meta_data['price_is_per'] - 1; // trial count for api items
                wp_update_post( $args );
            
        }


        /**
         * Update the subscription if a payment is done manually from user
         *
         * order_id is the id of the last order created
         *
         * @since  1.0.0
         * @author sam
         * @return void
         */
        public function update_subscription( $order_id, $api_order_ids  ) {
            $order         = wc_get_order( $order_id );
			$subscriptions = smm_get_prop( $order, 'subscriptions', true );
			if ( $subscriptions != '' ) 
				foreach ( $subscriptions as $subscription_id ){
				    
                $this->id                     = $subscription_id;
                $this->subscription_meta_data = $this->get_var_sub( 'subscribed');
                      
				}   
            $api_order_id = empty($this->subscription_meta_data['api_order_ids'])?
            array():$this->subscription_meta_data['api_order_ids'];
            $api_order_id[]= $api_order_ids;
            $this->subscription_meta_data['api_order_ids'] = $api_order_id;
            
            //Change the status to renewed after api trigger
                
                $args                   = array();
                $args['ID']             = $this->id ;
                $args['post_title' ]    = 'renewed';
                wp_update_post( $args );
           
            $this->set_var_sub( 'subscribed', $this->subscription_meta_data );
        }


        /**
         * Renew the subscription if trial count set in sub post <>0
         *
         * order_id is the id of the last order created
         *
         * @since  1.0.0
         * @author sam
         * @return void
         */
        public function renew_subscription($sub_id) {

            $payed = $this->subscription_meta_data['payed_order_list'];
            //Change the next payment_due_date & count
            $price_is_per      = (int) get_the_excerpt($sub_id);
            $price_time_option = $this->subscription_meta_data['price_time_option'];
            //new due date for next order 1 iteration price is per =1
            $timestamp         = smapi_get_timestamp_from_option( current_time('timestamp'), 1, $price_time_option );
                $args = array();
                if($price_is_per > 0 ){
                $args['ID']            = $sub_id;
                $args['post_title' ]   = 'renew';
                //new due date for next order 1 iteration price is per =1
                $args['post_content' ] = $timestamp;
			    
                $args['post_excerpt' ] =  $price_is_per - 1; // trial count for api items
                $args['post_title' ]   = $args['post_excerpt' ] == 0 ? 'pending' :'renew';
                wp_update_post( $args );
                }
                else
                $timestamp = current_time('time_stamp');
                
                $this->subscription_meta_data['rates_payed'] = $this->subscription_meta_data['rates_payed'] +1;
                $this->subscription_meta_data['start_date']  = $timestamp;
                $this->subscription_meta_data['status']      = $args['post_title' ];
                $this->set_var_sub( 'subscribed', $this->subscription_meta_data );
                
                return "sub renewed";
               
        }



	    /**
	     * @return array
	     * @internal param $subscription_id
	     *
	     */
	    function get_subscription_meta(  ) {
            $subscription_meta = array();
            foreach ( $this->get_default_meta_data() as $key => $value ) {
            	$meta_value = get_post_meta( $this->id, $key, true );
                $subscription_meta[$key] = empty($meta_value) ? get_post_meta( $this->id, '_'.$key, true ) : $meta_value;
            }

            return $subscription_meta;
        }

	    /**
	     * Return an array of all custom fields subscription
	     *
	     * @return array
	     * @since  1.0.0
	     */
	    private function get_default_meta_data(){
		    return $this->subscription_meta_data;
	    }


	    /**
	     * @internal param $subscription_id
	     */
	    function cancel_subscription(){


            //Change the status to active

            //$this->set( 'status', 'cancelled' );
           // $this->set( 'cancelled_date', date( "Y-m-d H:i:s" ) );

            do_action('smapi_subscription_cancelled', $this->id);

            //if there's a pending order for this subscription change the status of the order to cancelled
            $order_in_pending = $this->renew_order;
            if( $order_in_pending ){
                $order = wc_get_order( $order_in_pending );
                if( $order ){
                    $order->update_status('failed');
                }
            }

        }

	    /**
	     * Return the next payment due date if there are rates not payed
	     *
	     * @param int $trial_period
	     *
	     * @since  1.0.0
	     * @author sam
	     * @return array
	     */
	    public function get_next_payment_due_date( $trial_period = 0, $start_date = 0) {

		    $start_date = ( $start_date ) ? $start_date : current_time('timestamp');
		    if ( $this->num_of_rates == 0 || ( intval( $this->subscription_meta_data['num_of_rates'] ) - intval( $this->subscription_meta_data['rates_payed'] ) ) > 0 ) {
			    ///$payment_due_date = ( $this->payment_due_date == '' ) ?  $start_date : $this->payment_due_date;
			    $payment_due_date = ( $this->payment_due_date == '' ) ?  $start_date : $start_date;
			    if( $trial_period != 0){
				    $timestamp = $start_date + $trial_period;
			    }else{
				    $timestamp = smapi_get_timestamp_from_option( $payment_due_date, $this->subscription_meta_data['price_is_per'], $this->subscription_meta_data['price_time_option']);
			    
			         }

			    return $timestamp;
		    }

		    return false;

	    }

	    /**
	     * Get the order object.
	     *
	     * @return
	     * @author sam softnwords
	     */
	    public function get_order(){
		    $this->order =  ( $this->order instanceof WC_Order ) ? $this->order : wc_get_order( $this->_order_id );

		    return $this->order;
	    }

	    /**
	     * Get billing customer email
	     *
	     * @return string
	     */
	    public function get_billing_email() {
		    $billing_email = ! empty( $this->_billing_email ) ? $this->_billing_email : smm_get_prop( $this->get_order(), '_billing_email');
		    return apply_filters( 'smapi_customer_billing_email', $billing_email, $this );
	    }

	    /**
	     * Get billing customer email
	     *
	     * @return string
	     */
	    public function get_billing_phone() {
		    $billing_phone = ! empty( $this->_billing_phone ) ? $this->_billing_phone : smm_get_prop( $this->get_order(), '_billing_phone' );
		    return apply_filters( 'smapi_customer_billing_phone', $billing_phone, $this );
	    }

	    /**
	     * Get subscription customer billing or shipping fields.
	     *
	     * @param string  $type
	     * @param boolean $no_type
	     *
	     * @return array
	     */
	    public function get_address_fields( $type = 'billing', $no_type = false, $prefix = '' ) {

		    $fields = array();

		    $value_to_check = '_'.$type.'_first_name';
		    if( ! isset( $this->$value_to_check ) ){
			    $fields = $this->get_address_fields_from_order( $type, $no_type, $prefix );
		    }else{
			    $order = $this->get_order();
			    $meta_fields = $order->get_address( $type );

			    foreach ( $meta_fields as $key => $value ) {
				    $field_name           = '_' . $type . '_' . $key;
				    $field_key            = $no_type ? $key : $type . '_' . $key;
				    $fields[ $prefix.$field_key ] = $this->$field_name;
			    }
		    }

		    return $fields;
	    }

	    /**
	     * Return the fields billing or shipping of the parent order
	     *
	     * @param string $type
	     * @param bool $no_type
	     *
	     * @return array
	     * @author sam softnwords
	     */
	    public function get_address_fields_from_order( $type = 'billing', $no_type = false, $prefix = '' ) {
		    $fields = array();
		    $order  = $this->get_order();
		    if ( $order ) {
			    $meta_fields = $order->get_address( $type );

			    if ( is_array( $meta_fields ) ) {
				    foreach ( $meta_fields as $key => $value ) {
					    $field_key            = $no_type ? $key : $type . '_' . $key;
					    $fields[ $prefix.$field_key ] = $value;
				    }
			    }
		    }

		    return $fields;
	    }

	    /**
	     * Return if the subscription can be cancelled by user
	     *
	     * @return  bool
	     * @since   1.0.0
	     */
	    public function can_be_cancelled() {
		    $status = array( 'pending', 'cancelled' );

		    //the administrator and shop manager can switch the status to cancelled
		    $post_type_object = get_post_type_object( SMMS_WC_Subscription()->post_name );
		    if ( current_user_can( $post_type_object->cap->delete_post, $this->ID ) ) {
			    $return = true;
		    } else if ( ! in_array( $this->status, $status ) && get_option( 'smapi_allow_customer_cancel_subscription' ) == 'yes' ) {
			    $return = true;
		    } else {
			    $return = false;
		    }

		    return apply_filters( 'smapi_can_be_cancelled', $return, $this );
	    }

	    /**
	     * Return if the subscription can be reactivate by user
	     *
	     * @return  bool
	     * @since   1.0.0
	     */
	    public function can_be_create_a_renew_order() {
		    $status = array( 'pending', 'expired' );

		    // exit if no valid subscription status
		    if ( in_array( $this->status, $status ) || $this->payment_due_date == $this->expired_date ) {
			    return false;
		    }

		    //check if the subscription have a renew order
		    //$renew_order = $this->has_a_renew_order();

		    // if order doesn't exist, or is cancelled, we create order
		    if ( ! $renew_order || ( $renew_order && ( $renew_order->get_status() == 'cancelled' ) ) ) {
			    $result = true;
		    } // otherwise we return order id
		    else {
			    $result =  smm_get_order_id( $renew_order );
		    }

		    return apply_filters( 'smapi_can_be_create_a_renew_order', $result, $this );
	    }

	    /**
	     * Return the renew order if exists
	     *
	     * @return  bool|WC_Order
	     * @since   1.1.5
	     */
	    public function has_a_renew_order() {

		    $return         = false;
		    $renew_order_id = $this->renew_order;

		    if ( $renew_order_id ) {
			    $order = wc_get_order( $renew_order_id );
			    $order && $return = $order;
		    }

		    return $return;
	    }

	    /**
	     * Add failed attemp
	     *
	     * @param bool $attempts
	     * @param bool $latest_attemp if is the last attemp doesn't send email
	     *
	     * @since  1.1.3
	     * @author sam softnwords
	     */
	    public function register_failed_attemp( $attempts = false, $latest_attemp = false ) {

		    $order = wc_get_order( $this->order_id );

		    if ( false === $attempts ) {
			    $failed_attemp = smm_get_prop( $order, 'failed_attemps' );
			    $attempts      = intval( $failed_attemp ) + 1;
		    }

		    if ( ! $latest_attemp ) {
			    smm_save_prop( $order, 'failed_attemps', $attempts, false, true );
			    do_action( 'smapi_customer_subscription_payment_failed_mail', $this );
		    }
	    }


    }




}

