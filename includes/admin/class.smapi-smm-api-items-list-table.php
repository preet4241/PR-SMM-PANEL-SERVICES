<?php
if ( !defined( 'ABSPATH' ) || !defined( 'SMMS_SMAPI_VERSION' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Server List Table
 *
 * @class   SMMS_SMAPI_SMMAPIITEM_List_Table
 * @package SMMS WooCommerce Subscription
 * @since   1.0.0
 * @author  SMMS 
 */

class SMMS_SMAPI_SMM_API_ITEMS_List_Table extends WP_List_Table {

    private $post_type;
    private $meta_key;
    private $smm_api_server_item;
    private $parameter_key;
    private $api_servers;
    private $total_servers;
    private $select_orderby;
    private $select_order;
	private $api_id_price = array();

    public function __construct( $args = array() ) {
        parent::__construct( array(
        'singular'  => 'item',     //singular name of the listed records
        'plural'    => 'items',    //plural name of the listed records
        'ajax'      => true   
        //does this table support ajax?
        ) );
        $this->post_type = 'smapi_server';
        $this->meta_key = '_item_%';
        $this->parameter_key= '_parameter_%';
        $this->smm_api_server_item = get_option('smm_api_server_item');
        $this->select_orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : '';
        $this->select_order = isset($_GET['order'])? sanitize_text_field($_GET['order']):'DESC';
    }

    function get_columns() {
        $columns = array(
            'cb'                		=> '<input type="checkbox" />',
            //'meta_id'                	=> __( 'ID', 'smm-api' ),
            'api_item_service_number'	=> __( 'SERVICE ID', 'smm-api' ),
            'api_item_description'   	=> __( 'DESCRIPTION', 'smm-api' ),
            'api_item_rate_per_1000' 	=> __( 'RATE/1000', 'smm-api' ),
            'api_item_min_order'     	=> __( 'MIN', 'smm-api' ),
            'api_item_max_order'     	=> __( 'MAX', 'smm-api' ),
            'api_item_status'        	=> __( 'STATUS', 'smm-api' ),
            'api_item_subscription'  	=> __( 'SUBSCRIPTION', 'smm-api' ),
            'api_item_display'       	=> __( 'VIEW', 'smm-api' ),
			'api_product_id'       	 	=> __( 'PRODUCT ID', 'smm-api' ),
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
        $query = new WP_Query( $args );

        $orderby = !empty( $this->select_orderby ) ? 'meta_id' : 'meta_id';
        
        $order   = $this->select_order;

        $link = '';
        $order_string = '';
        if ( !empty( $orderby ) & !empty( $order ) ) {
            //$order_string = 'ORDER BY  smapi_pm.meta_key ' . $order;
            switch ( $orderby ) {
                case 'enable':
                    $link = " AND ( smapi_p.post_status != 'publish' ) ";
                    break;
                default:
                   $order_string = ' ORDER BY ' . $orderby . ' ' . $order;
            }

        }
        
        $this->process_bulk_action();
        // API ITEM LISTING
        $query = $wpdb->prepare( "SELECT smapi_pm.* FROM " .$wpdb->prefix ."postmeta as smapi_pm INNER JOIN $wpdb->posts as smapi_p ON ( smapi_pm.post_id = %s )
        WHERE 1=1 %1s
        AND smapi_p.post_type = %s
        AND smapi_pm.meta_key LIKE %s
        GROUP BY smapi_pm.meta_id %1s",$this->smm_api_server_item, $link,  $this->post_type, $this->meta_key, $order_string
        );

        $totalitems = $wpdb->query($wpdb->prepare( "SELECT smapi_pm.* FROM " .$wpdb->prefix ."postmeta as smapi_pm INNER JOIN $wpdb->posts as smapi_p ON ( smapi_pm.post_id = %s )
        WHERE 1=1 %1s
        AND smapi_p.post_type = %s
        AND smapi_pm.meta_key LIKE %s
        GROUP BY smapi_pm.meta_id %1s",$this->smm_api_server_item,$link,  $this->post_type, $this->meta_key, $order_string
        ) );
//echo $query; // FOR TESTING
        $perpage = (get_option('smmpage_item') > 15)?get_option('smmpage_item'):15;
        //Which page is this?
        $paged = !empty( $_GET["paged"] ) ? sanitize_text_field($_GET["paged"] ): '';
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

       
                          
        $smm_api_items = $wpdb->get_results( $wpdb->prepare( "SELECT smapi_pm.* FROM " .$wpdb->prefix ."postmeta as smapi_pm INNER JOIN $wpdb->posts as smapi_p ON ( smapi_pm.post_id = %s )
        WHERE 1=1 %1s
        AND smapi_p.post_type = %s
        AND smapi_pm.meta_key LIKE %s
        GROUP BY smapi_pm.meta_id %1s",$this->smm_api_server_item, $link,  $this->post_type, $this->meta_key, $order_string
        ) );
        
        $this->items = $smm_api_items;
        
        
		$sm_api_ = array();
		//$smm_api_it = 
		foreach($smm_api_items as $sm_api_item){
		//foreach($sm_api_item as $sm_api_val)
		$sm_api_= json_decode($sm_api_item->meta_value,true);
		
		// arrypush will not give warning undefined key
		array_push($this->api_id_price,[$sm_api_['f_service_id'] => $sm_api_['f_item_price']]);
		}
       
    }

    function column_default( $item, $column_name ) {
		$smm_api_items = json_decode($item->meta_value,true);
		//new SMAPI_API_ITEM( $this->smm_api_server_item );
		
        switch( $column_name ) {
            case 'meta_id':
                return esc_html($item->meta_id);
                break;
            case 'api_item_service_number':
                $api_item_service_number =$smm_api_items['f_service_id'];
                return esc_html($api_item_service_number);
                break;
            case 'api_item_description':
	            $api_item_description = $smm_api_items['f_api_description'];
                return esc_html($api_item_description);
                break;
            case 'api_item_rate_per_1000':
                $api_item_rate_per_1000 = $smm_api_items['f_item_price'];
	            return esc_html($api_item_rate_per_1000);
                break;
            case 'api_item_min_order':
                $api_item_min_order = $smm_api_items['f_min_order'];
                return esc_html($api_item_min_order);
	            break;
            case 'api_item_max_order':
                $api_item_max_order = $smm_api_items['f_max_order'];
                return esc_html($api_item_max_order);
	            break;
            
            case 'api_item_status':
	            $api_item_status = $smm_api_items['f_item_status'];
	            
                return esc_html($api_item_status);
	            
	            break;
	       case 'api_item_subscription':
                $api_item_subscription = $smm_api_items['f_item_subscribe_check'];
                return esc_html($api_item_subscription);
                break;
	       case 'api_item_display':
               return  '<a href=#'.esc_attr($item->meta_key).' id="more_'.esc_attr($item->meta_key).'">More</a>';
                break;
		   case 'api_product_id':
                
				$api_product_id = $this->smm_api_products($this->smm_api_server_item , $item->meta_key);
                return esc_html($api_product_id);
                break;	

            default:
                return ''; //Show the whole array for troubleshooting purposes
        }
    }
	/**
	 * Sets product idsd on api item .
	 * @param string $item_meta_key. Possible values: _item_.
	 *  @return text
	*/
	// Get all products that use the api items
	function smm_api_products($_server_id = '', $_item_val =''){
			
		//===========================product ids that has smm_api===========
		
					$args = array(
					'post_type'      => 'product',
					'posts_per_page' => -1, // Get all matching products
					'meta_key'       => '_smapi_api',
					'meta_value'   	 => 'yes',
					//'compare'        => '=',
					'fields'       	 => 'ids', // Only retrieve product IDs
					'meta_query'     =>	array(
												'key'     => '_smapi_server_name_option',//one server
												'value'   => $_server_id,
												'compare' => '='
													
											)
					);
					$argv = array(
					'post_type'      => 'product_variation',
					'posts_per_page' => -1, // Get all matching products
					'meta_key'       => 'variable_smm_api',
					'meta_value'   	 => 'on',
					'fields'       	 => 'ids', // Only retrieve product IDs
					'meta_query'     =>	array(
												'key'     => 'var_smapi_server_name_option',//one server
												'value'   => $_server_id,
												'compare' => '='
													
											)
					);
					
					$product_api 	= array();
					
					$simple_products_ids 	= get_posts( $args );
					foreach($simple_products_ids as $simple_products_id){
						if($_item_val == get_post_meta( $simple_products_id, '_smapi_service_id_option', true ))
						array_push($product_api, 'S- '.$simple_products_id);
					}
					$variable_products_ids 	= get_posts( $argv );
					foreach($variable_products_ids as $variable_products_id){
						//Get first word from string strtok($value, " ");
						
						if($_item_val == strtok( get_post_meta( $variable_products_id, 'var_smapi_service_id_option', true ), " "))
						{
						$parent_id = wp_get_post_parent_id($variable_products_id);
						array_push($product_api, 'V- '.$parent_id);
						}
					}
					
					// Output product IDs

				//=====================get id ends=============
				$products_text = implode(', ', array_unique($product_api));//unique ids to string
				$products_text 	= $products_text == '' ? 'NA' : $products_text;
				return $products_text;
					
			
	}
	
    function get_bulk_actions(  ) {
       
        $actions = array(
            'delete'     => __( 'Delete', 'smm-api' )
        );

        return $actions;
    }
    function process_bulk_action(  ) {
        
        $actions = $this->current_action();
        if( !empty( $actions) && isset($_POST['smapi_meta_key'] )){

            $smm_items = array_map( 'sanitize_key', $_POST['smapi_meta_key'] );
            

            if( $actions == 'delete' ){
                $current_selection = get_option('smm_api_server_item');
                foreach ( $smm_items as $item_meta_key ) {
           
            
                global $wpdb;
                
                $PostIDs = $wpdb->get_results($wpdb->prepare("SELECT DISTINCT (post_id) 
                        FROM {$wpdb->prefix}postmeta
                        WHERE post_id IN (SELECT post_id 
                        FROM {$wpdb->prefix}postmeta 
                        WHERE meta_value = %s) 
                        And post_id IN (SELECT post_id 
                        FROM {$wpdb->prefix}postmeta 
                        WHERE meta_value = %s)",$current_selection, $item_meta_key) , ARRAY_A );
            
            //file_put_contents(plugin_dir_path( __FILE__ )."check.txt",serialize( $results).$current_selection);
                foreach ($PostIDs as $PostID)
                wp_delete_post( $PostID['post_id'], true );
                delete_post_meta( $this->smm_api_server_item, $item_meta_key );
                }
            }

           
        }
        
    }


    function get_sortable_columns() {
        $sortable_columns = array(
            'api_item_service_number' => array( 'SERVICE', false ),
            
        );
        return $sortable_columns;
    }

    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="smapi_meta_key[]" value="%s" />',  esc_attr($item->meta_key)
        );
    }
    //is rendered in any column with a name/slug of 'title'.
    
    function column_api_item_service_number($item) {
        $f_item_product = apply_filters('smm_api_item_service_number','n_item_product');
        $actions = array(
            'edit'      => sprintf('<a class="%s" href="%s">Edit</a>','f_item_edit', '#'.$item->meta_key),

            'delete'    => sprintf('<a class="%s" href="%s">Delete</a>','f_item_delete', '#'.$item->meta_key),
            'product'    => sprintf('<a class="%s" href="%s">Add Product</a>',$f_item_product, '#'.$item->meta_key),
        );
        $smm_api_items = json_decode($item->meta_value,true);
        return sprintf('%1$s %2$s', $smm_api_items['f_service_id'], $this->row_actions($actions) );
    }
   
 //SERVER ADD FORM IS TAILORED 
 function Smm_item_form() {
     
     global $wpdb;
     
     
        $this->api_servers  = $wpdb->get_results( $wpdb->prepare( "SELECT smapi_p.post_title, smapi_p.ID FROM $wpdb->posts as smapi_p INNER JOIN " . $wpdb->prefix . "postmeta as smapi_pm ON ( smapi_p.ID = smapi_pm.post_id )
        WHERE 1=1
        AND smapi_p.post_type = %s
        AND smapi_pm.meta_key LIKE %s", $this->post_type, $this->parameter_key
        ), ARRAY_A );
	 $this->total_servers = count($this->api_servers);

        

         ?>
         <div id="icon-tools" class="icon32"></div>
            <h1> AVAILABLE API ITEMS FOR <?php echo esc_html($this->total_servers)?> API SERVER 
            
            <select id = "f_api_server_list" name="f_api_server_list">
            <?php
            
            $api_server_list_options = array(); 
			$my_smm_result = $this->api_servers;
			(get_option('smm_api_server_item') == null) ?
			update_option( 'smm_api_server_item', $my_smm_result[0]['ID']):NULL;
			$current_selection = get_option('smm_api_server_item');
			$api_server_list_options_output='';
			foreach($my_smm_result as $sub_result){
			if ($current_selection == $sub_result['ID'])
			$smm_server_found = true;
			$api_server_list_options_output .= '<option ';
			$api_server_list_options_output .=
			($sub_result['ID'] == $current_selection ) ?
			'value="'.esc_attr($sub_result['ID']).'" selected': 
	        'value="'.esc_attr($sub_result['ID']).'"' ;
	        $api_server_list_options_output .= '>'
	        .esc_html(smms_getHost($sub_result['post_title']))
			. '</option>';
			}
			if (!$smm_server_found && $my_smm_result[0]['ID'])
			update_option( 'smm_api_server_item', $my_smm_result[0]['ID']);
	 		$allowed_html = array(
    								'option' => array(
        											'value' => array(),
												'selected'	=> array()
     												 ),
								);
			echo wp_kses($api_server_list_options_output, $allowed_html);
			echo '</select>';
			?>
			 </h1>
			<input id="add_new_api_item" type="button" value="Add Service" />
            
            <div class="item-entry-form">
            <form name="f_item_info" id="f_item_info">
            <table width="100%" border="0" cellpadding="4" cellspacing="0">
			<caption>API ITEM</caption>
            <tr>
            <td colspan="2" align="right"><a href="#" id="f_item_clear">Clear</a></td>
            <input type="hidden" name="f_post_id" value="<?php echo esc_attr($this->smm_api_server_item);?>">
		    <input type="hidden" name="f_meta_key" value="0">
            </tr>
            	<tr>
            		<td>SERVICE ID</td>
            		<td><input type="text" name="f_service_id"></td>
            	</tr>
            	<tr>
            		<td>DESCRIPTION</td>
            		<td><input type="text" name="f_api_description"></td>
            	</tr>
           	 
            	<tr>
            		<td>MIN ORDER</td>
            		<td><input type="text" name="f_min_order"></td>
            	</tr>
            	<tr>
            		<td>MAX ORDER</td>
            		<td><input type="text" name="f_max_order" value="10000"></td>
            	</tr>
            	<tr>
            		<td>RATE/1000 </td>
            		<td><input type="text" name="f_item_price"></td>
            	</tr>
            	<tr>
            		<td>API ITEM STATUS : </td>
            		<td><select id = "f_item_status" name="f_item_status">
            		<option value="active" selected>ACTIVE</option>
            		<option value="inactive">INACTIVE</option>
            		</select></td>
            	</tr>
					<tr>
					<td></td><td>SUBSCRIPTION</td></tr>
					<tr>
            		<td>POST COUNT</td>
            		<td><input type="text" name="f_item_post_count" Value="1"></td>
            	</tr>
					<tr>
            		<td>DELAY in Min</td>
            		<td><input type="text" name="f_item_post_delay" value="5"></td>
            	</tr>
					<tr>
            		<td>SUBCRIPTION METHOD</td>
            		<td><select id = "f_item_subscribe_check" name="f_item_subscribe_check">
			        <option selected="selected" value="disabled">DISABLED</option>
                    <option value="link">LINK</option>
                    <option value="username">USERNAME</option>;
			        </select></td>
            	</tr>
					<tr>
            		<td align="right"></td>
            		<td><input type="button" value="Save" id="f_item_save"><input type="button" value="Cancel" id="f_item_cancel"></td>
					<td><input type="button" value="Import" id="f_item_import"></td>
            	</tr>
            	</table>
            	</form>
            </div>
            <div class="item-display-table">
            
            <table width="100%" border="0" cellpadding="1" cellspacing="0">
			<caption>API ITEM</caption>
            <tr>
            <td>SERVICE ID :</td>
            <td id="display_service_id"></td>
            </tr>
            <tr>
            <td>DESCRIPTION :</td>
            <td id="display_api_description" width="80%"></td>
          
            </tr>
            <tr>
            <td>MIN ORDER :</td>
            <td id="display_min_order"></td>
            </tr>
            <tr>
            <td>MAX ORDER :</td>
            <td id="display_max_order"></td>
            </tr>
            <tr>
            <td>RATE/1000 :</td>
            <td id="display_item_price"></td>
            
            </tr>
            <tr>
            <td>API STATUS :</td>
            <td id="display_item_status"></td>
            </tr>
            
	    </table>
	    <table width="100%" border="0" cellpadding="4" cellspacing="0">
			<caption>SUBSCRIPTION</caption>
	    <tr>
            <td>POST COUNT <span> ( optional )</span> :</td>
            <td id="display_item_post_count"></td>
            </tr>
	    <tr>
            <td>DELAY  <span> ( in Min) :</span></td>
            <td id="display_item_post_delay"></td>
            </tr>
                                   
            <tr>
            <td>METHOD :</td>
            <td id="display_item_subscribe_check"></td>
            </tr>
            </table>
            
            </div>

<?php
}  
}//class end