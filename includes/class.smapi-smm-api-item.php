<?php

if ( !defined( 'ABSPATH' ) || !defined( 'SMMS_SMAPI_VERSION' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Implements SMAPI_smm_api_item Class
 *
 * @class   SMAPI_smm_api_item
 * @package SMMS WooCommerce Subscription
 * @since   1.0.0
 * @author  SMMS
 */
if ( !class_exists( 'SMAPI_API_ITEM' ) ) { 

    class SMAPI_API_ITEM {



        protected $smm_api_item_meta_data = array(
            

            'smapi_free_version'     => SMMS_SMAPI_VERSION
        );
        // getting post id from option db
        public $smm_server_post_id=0;
	    /**
	     * The smm_api_item (post) post_id.
	     *
	     * @var int
	     */
	    public $post_id = 0;
 /**
	     * The smm_api_item (post-meta) meta_id.
	     *
	     * @var int
	     */
	    public $meta_id = 0;

	    /**
	     * @var string
	     */
	    public $meta_item_group;

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
        public function __construct( $smm_meta_id = 0, $args = array() ) {
            //add_action( 'init', array( $this, 'register_post_type' ) );

	        //populate the smm_api_item if $smm_meta_id is defined
	        if ( $smm_meta_id ) {
		        $this->meta_id = $smm_meta_id;
		        $this->smm_server_post_id = get_option('smm_api_server_item');
		        $this->meta_item_group = $this->populate();
	        }
            if ( $smm_meta_id && ! empty( $args )) {
		        $this->meta_id = $smm_meta_id;
		        //$meta     = apply_filters( 'smapi_add_server_args', wp_parse_args( $args, $this->get_default_meta_data() ), $this );
	            
	        }
	        //add a new smm_api_item if $args is passed
	        if ( empty($smm_meta_id) && ! empty( $args ) ) {
	            
	            $this->smm_server_post_id = get_option('smm_api_server_item');
		        $this->add_smm_api_item( $args );
		        
	        }

        }

	    
	    /**
	     * Populate the smm_api_item
	     *
	     * @return void
	     * @since  1.0.0
	     */
	    public function populate() {

		    //$this->post = get_post( $this->post_id );
		    //$smm_api_item_display = array();
            $smm_api_item_meta = $this->get_smm_api_item_meta();
            //$smm_api_item_display = array('hi' => array(1=>1,2=>2)); 
		    foreach ( $smm_api_item_meta as $key => $value ) {
			    
    $smm_api_item_display = array($key => $value);
             //$smm_api_item_display = array('hi' => array(1=>1,2=>2));  
		    }
            return $smm_api_item_display; 
		    //do_action( 'smapi_smm_api_item_loaded', $this );
	    }

	    /**
	     * @param $args
	     *
	     * @return int|WP_Error
	     */
	    public function add_smm_api_item( $args ) {

            

            
	            $meta     = apply_filters( 'smapi_add_subcription_args', wp_parse_args( $args, $this->get_default_meta_data() ), $this );
	            
	            
	            $meta_value = $this->update_smm_api_item_meta( $meta );
	            
	            
	            // Adding post meta for each items
	            $meta_key = '_item_'.$meta['f_service_id'];
	            $_title_item_meta_key = '_title_item_'.$meta['f_service_id'];
	            $_title_item_meta_id = update_post_meta( $this->smm_server_post_id, $_title_item_meta_key, $meta['f_api_description'], $unique = false ); 
	            
	          $this->meta_id = update_post_meta( $this->smm_server_post_id, $meta_key, $meta_value, $unique = false );
            $this->variation_id = $this->meta_id;
            
            
            //return $this->get_default_meta_data().'data';
	          
            

            //return $metakey;
        }

        /**
         * Update post meta for smm_api_item
         *
         *
         * @since  1.0.0
         * @author sam
         * @return void
         */
        function update_smm_api_item_meta( $meta ){
            return wp_json_encode($meta);
            
        }

	    
	    /**
	     * @return array
	     * @internal param $smm_meta_id
	     *
	     */
	    function get_smm_api_item_meta(  ) {
            $smm_api_item_meta = array();
            
            $smm_api_item_meta_raw = get_post_meta( $this->smm_server_post_id);
            foreach ( $smm_api_item_meta_raw as $key => $value ) {
            	
              if(substr($key, 0, 6) == '_item_')  
        array_push($smm_api_item_meta, [$key => $value]);
            }

            return $smm_api_item_meta;
        }

	    /**
	     * Return an array of all custom fields smm_api_item
	     *
	     * @return array
	     * @since  1.0.0
	     */
	    private function get_default_meta_data(){
		    return $this->smm_api_item_meta_data;
	    }


	    


    }




}

