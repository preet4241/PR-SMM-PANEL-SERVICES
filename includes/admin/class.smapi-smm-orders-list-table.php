<?php
if ( !defined( 'ABSPATH' ) || !defined( 'SMMS_SMAPI_VERSION' ) ) {
    exit; // Exit if accessed directly
}

/**
 * ORDER List Table
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * @class   SMMS_SMAPI_Orders_List_Table
 * @package SMMS WooCommerce Subscription
 * @since   1.0.0
 * @author  SMMS
 */

class SMMS_SMAPI_Orders_List_Table extends WP_List_Table {

    public $post_type;
    public $passedaction;
	private $select_order;
	private $order_id_   = array();
	private $api_order_item_status_str;
    public function __construct( $args = array() ) {
        parent::__construct( array(
        'singular'  => 'order',     //singular name of the listed records
        'plural'    => 'orders',    //plural name of the listed records
        'ajax'      => true
        //does this table support ajax?
                          ) );

        $this->select_order = isset($_GET['order'])?sanitize_text_field($_GET['order']):'DESC';
       $this->process_bulk_action();
    }

    function get_columns() {
        $columns = array(
            'cb'                	   	=> '<input type="checkbox" />',
            'api_order'                	=> __( 'ORDER', 'smm-api' ),
			'api_item'                	=> __( 'ITEM', 'smm-api' ),
            'api_order_date'           	=> __( 'DATE', 'smm-api' ),
            'api_order_status'         	=> __( 'STATUS', 'smm-api' ),
            'api_pay_method'   		   	=> __( 'PAYMENT', 'smm-api' ),
            'api_pay_total'          	=> __( 'TOTAL', 'smm-api' ),
			'api_user'                	=> __( 'USER', 'smm-api' ),
            'api_server_order'         	=> __( 'API ORDER', 'smm-api' ),
            'api_server_status'  	   	=> __( 'API STATUS', 'smm-api' ),

        );
        return $columns;
    }

    function prepare_items() {
        global $wpdb, $_wp_column_headers;

        $screen = get_current_screen();

        $columns               = $this->get_columns();
        $hidden                = array();
        $sortable              = $this->get_sortable_columns();
        $this->_column_headers = array( $columns, $hidden, $sortable );
        
        
		$order_items = $this->find_api_order($this->select_order);
		$order_items_arr = (array) $order_items;
		
        $order_items_arr = apply_filters('smm_order_item_listing',$order_items_arr,$this->order_id_);
        //file_put_contents(plugin_dir_path( __FILE__ )."check1.txt", "Orders = ".serialize($order_items_arr), FILE_APPEND);
        
        $totalitems = count($order_items_arr) > 1 ? count($order_items_arr):15;
        $per_page = (get_option('smmpage_item') > 15)?get_option('smmpage_item'):15;
        //Which page is this?
        $paged = !empty( sanitize_text_field(@$_GET["paged"]) ) ? sanitize_text_field(@$_GET["paged"] ): '';
        //Page Number
        if ( empty( $paged ) || !is_numeric( $paged ) || $paged <= 0 ) {
            $paged = 1;
        }
        //How many pages do we have in total?
        $totalpages = ceil( $totalitems / $per_page );
        //adjust the query to take pagination into account
        if ( !empty( $paged ) && !empty( $per_page ) ) {
            $offset = ( $paged - 1 ) * $per_page;
           // $query .= ' LIMIT ' . (int) $offset . ',' . (int) $perpage;
        }

        /* -- Register the pagination -- */
        $this->set_pagination_args( array(
            "total_items" => $totalitems,
            "total_pages" => $totalpages,
            "per_page"    => $per_page,
        ) );
        //The pagination links are automatically built according to those parameters

        $_wp_column_headers[$screen->id] = $columns;
		$start = $paged * $per_page - $per_page;
		$slice = array_slice($order_items_arr, $start, $per_page);
        $this->items = (object)$slice;

    }

    function column_default( $item, $column_name ) {

		//$item2 = $item->get_items();
        switch( $column_name ) {


            case 'api_order':
	            $api_order = $item['api_order'];
                return $api_order;
                break;
			case 'api_item':
	            $api_item = $item['api_item'];
                return $api_item;
                break;
            case 'api_order_date':
                $api_order_date = $item['api_order_date'];
	            return $api_order_date;
                break;
                case 'api_order_status':
                $api_order_status = $item['api_order_status'];
                return $api_order_status;
                break;

            case 'api_pay_method':
				$api_pay_method = $item['api_pay_method'];
                return $api_pay_method;
	            break;
			case 'api_pay_total':
				$api_pay_total = $item['api_pay_total'];
                return $api_pay_total;
	            break;
			case 'api_user':
				$api_user = $item['api_user'];
                return $api_user;
	            break;
            case 'api_server_order':
                $api_server_order = $item['api_server_order'];
                return $api_server_order;
                break;
            case 'api_server_status':
                $api_server_status = $item['api_server_status'];
                return  $api_server_status;
				
                break;

            default:
                return '';
                //Show the whole array for troubleshooting purposes
        }
    }

   function get_bulk_actions(  ) {


        $actions = array(
            'delete'     => __( 'Delete', 'smm-api' )
        );

        return $actions;
    }
    function process_bulk_action(  ) {

        $actions = $this->current_action();
        //$this->passedaction = $actions;
        if( !empty( $actions) && isset($_POST['smapi_order_ids'] )){

            $orders = (array) wc_clean($_POST['smapi_order_ids']);

            if( $actions == 'delete' ){
                foreach ( $orders as $orders_id ) {
                    wp_delete_post( $orders_id, true );
                    
                }
            }


        }

    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'api_order' => array( 'ORDER', false ),

        );
        return $sortable_columns;
    }

    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="smapi_order_ids[]" value="%s" />',  esc_attr($item['api_order'])
        );
    }
    //is rendered in any column with a name/slug of 'title'.

    function column_api_order($item) {
        $actions = array(


            'delete'    => sprintf('<a class="%s" href="%s">Delete</a>','order_delete', '#'.esc_attr($item['api_order'])),
        );

        return sprintf('%1$s %2$s', $item['api_order'], $this->row_actions($actions) );
    }

	//Check for Order repeating for single user
        function find_api_order($select_order){

           ## ==> Define HERE the statuses of that orders
			$order_statuses = array('wc-processing');
			//, 'wc-completed' 'wc-on-hold',
			## ==> Define HERE the customer ID
			$customer_user_id = get_current_user_id(); // current user ID here for example

        
			// Getting current customer orders
			$customer_orders = wc_get_orders( array(

			'type'        => 'shop_order',
			'post_status' => $order_statuses,
			'orderby' => 'ID',
			'order' => $select_order,
			'numberposts' => -1
				) );

			$product_id_ = array();
            
			// Loop through each customer WC_Order objects
		foreach($customer_orders as $order ){
			$api_server_status = NULL;
			// Order ID (added WooCommerce 3+ compatibility)
			$order_id = $order->get_id();
       // file_put_contents(plugin_dir_path( __FILE__ )."check1.txt", "Orders = ".$order->get_id(), FILE_APPEND);
        $smm_plugin_result = array();
        //GET Copon details
        $smm_plugin_result = apply_filters('smm_order_coupon_details',$smm_plugin_result, $order);
        
    // Iterating through current orders items
    foreach($order->get_items() as $item_id => $item){
        $product = wc_get_product($item->get_product_id()?$item->get_product_id():$item['product_id']);
		// The corresponding product ID (Added Compatibility with WC 3+)
        $product_id = $item->get_product_id();
      // file_put_contents(plugin_dir_path( __FILE__ )."check1.txt", "product = ".$product_id, FILE_APPEND);
       if($product):
        
       
        // for prosuct is sinple
        if($product->is_type( 'simple' )){
		$api_server_list_options_saved =
	        smm_get_prop( $product, '_smapi_server_name_option' );
			//file_put_contents(plugin_dir_path( __FILE__ )."check1.txt", "Orders = ".$api_server_list_options_saved);
        }//end of simple
        // for product variable
        if($product->is_type( 'variable' )){
	            $variation_id = $item->get_variation_id();
	    $api_server_list_options_saved = get_post_meta( $variation_id, 'var_smapi_server_name_option', true );
	    
        }

        // Order Item data (unprotected on Woocommerce 3)
				$response_tag = $order->get_meta( 'Response' );
		//file_put_contents(plugin_dir_path( __FILE__ )."check1.txt", "Orders = ".$order->get_meta( 'Response' ), FILE_APPEND);
             if($response_tag)
			 {
			
			$response_tag =	 substr($response_tag, 0, strpos((string)$response_tag, "received"));
			//$api_server_order = filter_var($response_tag, FILTER_SANITIZE_NUMBER_INT);
			$api_server_order_obj = json_decode($response_tag);
				//file_put_contents(plugin_dir_path( __FILE__ )."check1.txt", "Orders = ".serialize($api_server_order_obj),FILE_APPEND);
            $GO_Premium = isset($smm_plugin_result['user']) ?
            "Server Data Missing" :"GO Premium*";
			$product_id_[] = array(
			'api_order'        	=> $order_id,
			'api_item'        	=> $item->get_name(),
			'api_order_date'   	=> $order->get_date_created(),
			'api_order_status' 	=> $order->get_status(),
			'api_pay_method'   	=> isset($smm_plugin_result['coupon']) ?                                     $smm_plugin_result['coupon']:
			                       'GO Premium*' ,
			'api_pay_total'   	=> isset($smm_plugin_result['total']) ?                                     $smm_plugin_result['total']:
			                       'GO Premium*' ,
			'api_user'   		=> isset($smm_plugin_result['user']) ?                                     $smm_plugin_result['user']:
			                       'GO Premium*' ,
			'api_server_order' 	=> (!empty($api_server_order_obj->order))?
									$api_server_order_obj->order:$api_server_order_obj->error,
			'api_server_status'	=> (!empty($api_server_status))? 
									$api_server_status : $GO_Premium
			);
            if(@is_numeric($api_server_order_obj->order) && empty($api_server_status) && defined( 'SMM_API_PREMIUM' ))
			$this->order_id_[] = array(
			'product_server'    =>$api_server_list_options_saved,
			'product_order'     =>$order_id,	
			'api_order_id'      =>$api_server_order_obj->order
			);
                 
			}
         endif;
			}//end of foreach item
		}
    return $product_id_;

        }

}