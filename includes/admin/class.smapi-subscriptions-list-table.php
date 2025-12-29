<?php

if ( !defined( 'ABSPATH' ) || !defined( 'SMMS_SMAPI_VERSION' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Subscription List Table
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * @class   SMMS_SMAPI_Subscriptions_List_Table
 * @package SMMS WooCommerce Subscription
 * @since   1.0.0
 * @author  SMMS
 */

class SMMS_SMAPI_Subscriptions_List_Table extends WP_List_Table {

    private $post_type;

    public function __construct( $args = array() ) {
        parent::__construct( array() );
        $this->post_type = 'smapi_subscription';
        $this->process_bulk_action();
    }

    function get_columns() {
        $columns = array(
            'cb'         => '<input type="checkbox" />',
            'id'         => __( 'ID', 'smm-api' ),
            'status'     => __( 'Status', 'smm-api' ),
            'post_title' => __( 'Product', 'smm-api' ),
            'recurring'  => __( 'Recurring', 'smm-api' ),
            'order'      => __( 'Order', 'smm-api' ),
            'date'       => __( 'Date', 'smm-api' ),
            'user'       => __( 'User', 'smm-api' ),
            'expired'    => __( 'Expires', 'smm-api' ),
            'next_order' => __( 'Next Due', 'smm-api')
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

        $args  = array(
            'post_type' => $this->post_type
        );
        
        $orderby = !empty( sanitize_key(@$_GET["orderby"] )) ? sanitize_key(@$_GET["orderby"]) : 'ID';
        $order   = !empty( sanitize_key(@$_GET["order"] )) ? sanitize_key(@$_GET["order"] ): 'DESC';
        $query = '';
        $link         = '';
        $order_string = '';
        if ( !empty( $orderby ) & !empty( $order ) ) {
            $order_string = 'ORDER BY smapi_pm.meta_value ' . $order;
            switch ( $orderby ) {
                case 'status':
                    $link = " AND ( smapi_pm.meta_key = '_status' ) ";
                    break;
                default:
                    $order_string = ' ORDER BY ' . $orderby . ' ' . $order;
            }

        }

        

        $totalitems = $wpdb->query( $wpdb->prepare( "SELECT smapi_p.* FROM $wpdb->posts as smapi_p INNER JOIN " . $wpdb->prefix . "postmeta as smapi_pm ON ( smapi_p.ID = smapi_pm.post_id )
        WHERE 1=1 %1s
        AND smapi_p.post_type = %s
        GROUP BY smapi_p.ID %1s", $link, $this->post_type, $order_string
        ) );

        $perpage = (get_option('smmpage_item') > 15)?get_option('smmpage_item'):15;
        //Which page is this?
        $paged = !empty( sanitize_key(@$_GET["paged"] )) ? sanitize_key(@$_GET["paged"] ): '';
        //Page Number
        if ( empty( $paged ) || !is_numeric( $paged ) || $paged <= 0 ) {
            $paged = 1;
        }
        //How many pages do we have in total?
        $totalpages = ceil( $totalitems / $perpage );
        //adjust the query to take pagination into account
        if ( !empty( $paged ) && !empty( $perpage ) ) {
            $offset = ( $paged - 1 ) * $perpage;
            $query .= ' LIMIT ' . (int) $offset . ',' . (int) $perpage;
        }

        /* -- Register the pagination -- */
        $this->set_pagination_args( array(
            "total_items" => $totalitems,
            "total_pages" => $totalpages,
            "per_page"    => $perpage,
        ) );
        //The pagination links are automatically built according to those parameters

        $_wp_column_headers[$screen->id] = $columns;
        $this->items                     = $wpdb->get_results( $wpdb->prepare( "SELECT smapi_p.* FROM $wpdb->posts as smapi_p INNER JOIN " . $wpdb->prefix . "postmeta as smapi_pm ON ( smapi_p.ID = smapi_pm.post_id )
        WHERE 1=1 %1s
        AND smapi_p.post_type = %s
        GROUP BY smapi_p.ID %1s", $link, $this->post_type, $order_string
        ));

    }

    function column_default( $item, $column_name ) {
		//$subscription = new SMAPI_Subscription( $item->ID );
        $subscription = smapi_get_var_sub($item->ID,'subscribed');
        switch( $column_name ) {
            case 'id':
                return $item->ID;
                break;
            case 'status':
                $status = $subscription['status'];
                $status .= " ".$subscription['rates_payed'].' / '.$subscription['price_is_per'];
                return $status.' on '.date_i18n( wc_date_format(), $subscription['start_date']);
                break;
            case 'post_title':
	            $product_name = $subscription['product_name'];
                $quantity = $subscription['quantity'];
	            $qty = ( $quantity > 1) ? ' x '. $quantity : '';
                return $product_name.$qty;
                break;
            case 'user':
                $user_data = get_userdata( $subscription['user_id']);
	            if( !empty($user_data)) {
		            return '<a href="' . admin_url( 'profile.php?user_id=' . $subscription['user_id'] ) . '">' . esc_html($user_data->user_nicename) . '</a>';
	            }
                break;
            case 'recurring':
                $recurring = $subscription['line_total'];
	            $currency = wc_price( $recurring, array('currency' => $subscription['order_currency']) );
                return  $currency.' / '.substr($subscription['price_time_option'], 0, -1).' for '.$subscription['price_is_per'].' '.$subscription['price_time_option'];
                break;
            case 'order':
                $order_ids = $subscription['order_ids'];
	            if( !empty($order_ids)) {
		            $last_order = end( $order_ids );
		            return '#' . $last_order;
	            }
                break;
            case 'date':
                $order_date = date_i18n( wc_date_format(), $subscription['order_date']['date']);
	            
		            return $order_date;
	            
                break;
            case 'expired':
	            $expired_date = $subscription['expired_date'];
	            $expired_date = ( $expired_date != '' ) ? $expired_date : '';
                //$expired_date = $subscription->price_time_option." ".$subscription->price_is_per;
                //return $expired_date; 
	            return ( $expired_date ) ? date_i18n( wc_date_format(), $expired_date ) : __( 'Error', 'smm-api' );
	            break;
	       case 'next_order':
	            $rates_payed = $subscription['rates_payed'];
	            $next_order = smapi_get_timestamp_from_option( $subscription['start_date'], $rates_payed, $subscription['price_time_option'] );
			    
                //$expired_date = $subscription->price_time_option." ".$subscription->price_is_per;
                //return $expired_date; 
	            return date_i18n( wc_date_format(), $next_order);
	            break;

            default:
                return ''; //Show the whole array for troubleshooting purposes
        }
    }

    function get_bulk_actions(  ) {

        $actions = $this->current_action();
        if( !empty( $actions) && isset($_POST['smapi_subscription_ids'] )){
			// Good idea to make sure things are set before using them
			$smapi_subscription_ids = isset( $_POST['smapi_subscription_ids'] ) ? (array) wc_clean($_POST['smapi_subscription_ids']) : array();

		// Any of the WordPress data sanitization functions can be used here
			$smapi_subscription_ids = array_map( 'sanitize_key', $smapi_subscription_ids );
            $subscriptions = $smapi_subscription_ids;

            if( $actions == 'delete' ){
                foreach ( $subscriptions as $subscriptions_id ) {
                    wp_delete_post( $subscriptions_id, true );
                }
            }

            
        }

        $actions = array(
            'delete'     => __( 'Delete', 'smm-api' )
        );

        return $actions;
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'id' => array( 'ID', false ),
            
        );
        return $sortable_columns;
    }

    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="smapi_subscription_ids[]" value="%s" />',  esc_attr($item->ID)
        );
    }
    
}
