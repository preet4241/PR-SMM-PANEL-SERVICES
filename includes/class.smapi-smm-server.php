<?php

if ( !defined( 'ABSPATH' ) || !defined( 'SMMS_SMAPI_VERSION' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Implements SMAPI_Server Class
 *
 * @class   SMAPI_Server
 * @package SMMS WooCommerce Subscription
 * @since   1.0.0
 * @author  SMMS
 **/
if ( !class_exists( 'SMAPI_Server' ) ) {

    class SMAPI_Server {



        protected $server_meta_data = array(
            
            'api-format'                        => 'JSON',
            'smapi-server'                      =>'smapi_server',
            
            'retrieve_status_query_handle'      => '',
            'api_add_action'                    => 'add',
            'api_status_action'                 => 'status',
            
            

            'smapi_free_version'     => SMMS_SMAPI_VERSION
        );

	    /**
	     * The Server (post) ID.
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
        public $duplicate = false;

        /**
         * Constructor
         *
         * Initialize plugin and registers actions and filters to be used
         *
         * @since  1.0.0
         * @author sam
         */
        public function __construct( $server_id = 0, $args = array() ) {
           // add_action( 'init', array( $this, 'register_post_type' ) );

	        //populate the servers if $server_id is defined 
	        if ( $server_id ) {
		        $this->id = $server_id;
		       //$this->populate();
	        }
	        if ( $server_id  && ! empty( $args )) {
		        $this->id = $server_id;
		        $meta     = apply_filters( 'smapi_add_server_args', wp_parse_args( $args, $this->get_default_meta_data() ), $this );
	            $this->update_server_meta( $meta );
	            // updating the post details
	            $smm_post = array(
                        'ID'            =>$server_id,
                        'post_title'    =>$meta['api_url'],
                        'post_content'  =>$meta['api_key'],
                        'post_excerpt'  =>$meta['api-format'],
                        'post_type'     =>$meta['smapi-server'],
                        'post_status'   =>$meta['enable_api'],
                        'post_date'     =>gmdate('Y/m/d')
                        );
 
            // Update the post into the database
             wp_update_post( $smm_post );
             
	        }

	        //add a new subscription if $args is passed
	        if ( $server_id == '' && ! empty( $args ) ) {
	            //checking for duplication of server by ttitle
	            //if(add_action('init', array($this, 'is_title_unmatched',$args,10,1)))
	            if($this->is_title_unmatched($args)){
		        $this->add_server( $args );
	            }
	            else $this->duplicate = true;
	        }

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

		    foreach ( $this->get_server_meta() as $key => $value ) {
			    $this->$key = $value;
		    }

		    do_action( 'smapi_server_loaded', $this );
	    }

	    /**
	     * @param $args
	     *
	     * @return int|WP_Error
	     */
	    public function add_server( $args ) {

            $server_id = wp_insert_post(array(
                'post_title'=>$args['api_url'],
                'post_content'=>$args['api_key'],
                'post_excerpt'  =>$this->server_meta_data['api-format'],
                'post_type'     =>$this->server_meta_data['smapi-server'],
                'post_status'   =>$args['enable_api'],
                'post_date'=>gmdate('Y/m/d')
                
                ));
            
           
            if( $server_id ){
	            $this->id = $server_id;
	            $meta     = apply_filters( 'smapi_add_server_args', wp_parse_args( $args, $this->get_default_meta_data() ), $this );
	            $this->update_server_meta( $meta );
	            $this->populate();
            }

            return $server_id;
        }
        public function is_title_unmatched($args){
            
            // getting Post data
            //global $post, $wp_query;
            //return false;
            $argsmm = array(
                            'post_type'      => 'smapi_server',
                            'numberposts' => 10,
                            'post_status'    => 'any',
                            );
                $the_title_check_smm = get_posts( $argsmm );

            // The Loop
                if ( $the_title_check_smm) {
                  //file_put_contents(plugin_dir_path( __FILE__ )."check.txt", serialize($the_title_check_smm));  
                //return false;
                foreach ( $the_title_check_smm as $post ) :
                        if (smms_getHost($args['api_url']) == smms_getHost($post->post_title))
                        return false;
                endforeach; 
                
                } 
                 return true;
                
            
        
                /* Restore original Post Data */
                    wp_reset_postdata();
                    }
            
        /**
         * Update post meta in subscription
         *
         *
         * @since  1.0.0
         * @author sam
         * @return void
         */
        function update_server_meta( $meta ){
            update_post_meta( $this->id, '_parameter_handle', wp_json_encode($meta));
            foreach( $meta as $key => $value ){
	           //update_post_meta( $this->id, $key, $value);
            }
        }

	    /**
	     * @param $order_id 
	     *
	     * @internal param $subscription_id
	     */
	    public function start_server( $order_id) {

		    $payed = $this->payed_order_list;

		    //do not nothing if this subscription has payed with this order
		    if ( ! empty( $payed ) && is_array( $payed ) && in_array( $order_id, $payed ) ) {
			    return;
		    }

		    $payed = empty( $payed ) ? array() : $payed;

		    $order       = wc_get_order( $order_id );
		    $new_status  = 'active';
		    $rates_payed = 1;
		    if ( $this->start_date == '' ) {
			    $this->set( 'start_date', current_time('timestamp') );
		    }

		    if ( $this->payment_due_date == '' ) {
			    //Change the next payment_due_date
			    $this->set( 'payment_due_date', $this->get_next_payment_due_date( 0, $this->start_date ) );
		    }

		    if ( $this->expired_date == '' && $this->max_length != '' ) {
			    $timestamp = smapi_get_timestamp_from_option( current_time('timestamp'), $this->max_length, $this->price_time_option );
			    $this->set( 'expired_date', $timestamp );
		    }

		    $this->set( 'status', $new_status );

		    do_action( 'smapi_customer_server_payment_done_mail', $this );

		    $payed[] = $order_id;

		    $this->set( 'rates_payed', $rates_payed );
		    $this->set( 'payed_order_list', $payed );

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
        public function update_server( $order_id ) {


            $payed = $this->payed_order_list;
            //do not nothing if this subscription has payed with this order
            if ( !empty( $payed ) && is_array( $payed ) && in_array( $order_id, $payed ) ) {
                return;
            }

            //Change the status to active
	        $this->set( 'status', 'active' );

            //Change the next payment_due_date
            $price_is_per      = $this->price_is_per;
            $price_time_option = $this->price_time_option;
            $timestamp         = smapi_get_timestamp_from_option( current_time('timestamp'), $price_is_per, $price_time_option );

	        $this->set( 'payment_due_date', $timestamp );
            //update _payed_order_list
            $payed[] = $order_id;
	        $this->set( 'payed_order_list', $payed );
	        $this->set( 'renew_order', 0);

        }


	    /**
	     * @return array
	     * @internal param $subscription_id
	     *
	     */
	    function get_server_meta(  ) {
            $server_meta = array();
            foreach ( $this->get_default_meta_data() as $key => $value ) {
            	$meta_value = get_post_meta( $this->id, $key, true );
            	
                $server_meta[$key] = empty($meta_value) ? 
                $this->get_server_post_meta( $key ) : $meta_value;
            }

            return $server_meta;
        }
        function get_server_post_meta( $key ) {
            $meta_value='';
          $meta_value =   get_post_meta( $this->id, '_parameter_handle', true );
          $meta_value_array = json_decode($meta_value, true);
          return $meta_value_array[$key];
        }
	    /**
	     * Return an array of all custom fields subscription 
	     *
	     * @return array
	     * @since  1.0.0
	     */
	    private function get_default_meta_data(){
		    return $this->server_meta_data;
	    }


	    /**
	     * @internal param $subscription_id
	     */
	    function cancel_server(){


            //Change the status to active

            $this->set( 'status', 'cancelled' );
            $this->set( 'cancelled_date', gmdate( "Y-m-d H:i:s" ) );

            do_action('smapi_server_cancelled', $this->id);

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
		    if ( $this->num_of_rates == '' || ( intval( $this->num_of_rates ) - intval( $this->rates_payed ) ) > 0 ) {
			    $payment_due_date = ( $this->payment_due_date == '' ) ?  $start_date : $this->payment_due_date;
			    if( $trial_period != 0){
				    $timestamp = $start_date + $trial_period;
			    }else{
				    $timestamp = smapi_get_timestamp_from_option( $payment_due_date, $this->price_is_per, $this->price_time_option );
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
		    $renew_order = $this->has_a_renew_order();

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
			    do_action( 'smapi_customer_server_payment_failed_mail', $this );
		    }
	    }


    }




}

