<?php

if ( !defined( 'ABSPATH' ) || !defined( 'SMMS_SMAPI_VERSION' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Server List Table 
 * License: GPLv3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * @class   SMMS_SMAPI_Servers_List_Table
 * @package SMMS WooCommerce Subscription
 * @since   1.0.0
 * @author  SMMS
 */

class SMMS_SMAPI_Servers_List_Table extends WP_List_Table {

    public $post_type;
    public $passedaction;

    public function __construct( $args = array() ) {
        parent::__construct( array(
        'singular'  => 'server',     //singular name of the listed records
        'plural'    => 'servers',    //plural name of the listed records
        'ajax'      => true   
        //does this table support ajax?
                          ) );
        
        $this->post_type = 'smapi_server'; 
        //$this->passedaction = $args['action'];
       $this->process_bulk_action();  
    }

    function get_columns() {
        $columns = array(
            'cb'                => '<input type="checkbox" />',
            'id'                => __( 'ID', 'smm-api' ),
            'api_url'           => __( 'API URL', 'smm-api' ),
            'api_key'           => __( 'API KEY', 'smm-api' ),
            'response_format'   => __( 'FORMAT', 'smm-api' ),
            'enable'            => __( 'STATUS', 'smm-api' ),
            'api_dated'         => __( 'DATE', 'smm-api' ),
            'parameter_handle'  => __( 'PARAMETER', 'smm-api' ),
            
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
        

        $orderby = !empty( $_GET["orderby"] ) ? sanitize_text_field($_GET["orderby"]) : 'ID';
        $order   = !empty( $_GET["order"] ) ? sanitize_text_field($_GET["order"]) : 'DESC';

        $link         = '';
        $order_string = '';
        if ( !empty( $orderby ) & !empty( $order ) ) {
            $order_string = 'ORDER BY  smapi_pm.post_id ' . $order;
            switch ( $orderby ) {
                case 'enable':
                    $link = " AND ( smapi_p.post_status != 'publish' ) ";
                    break;
                default:
                    $order_string = ' ORDER BY ' . $orderby . ' ' . $order;
            }

        }

       $query='';

        $totalitems = $wpdb->query( $wpdb->prepare( "SELECT smapi_p.* FROM $wpdb->posts as smapi_p INNER JOIN " . $wpdb->prefix . "postmeta as smapi_pm ON ( smapi_p.ID = smapi_pm.post_id )
        WHERE 1=1 %1s
        AND smapi_p.post_type = %s
        GROUP BY smapi_p.ID %1s", $link, $this->post_type, $order_string
        ) );

        $perpage = (get_option('smmpage_item') > 15)?get_option('smmpage_item'):15;
        
        $paged = !empty( $_GET["paged"] ) ? sanitize_text_field($_GET["paged"]) : '';
        
        if ( empty( $paged ) || !is_numeric( $paged ) || $paged <= 0 ) {
            $paged = 1;
        }
        
        $totalpages = ceil( $totalitems / $perpage );
        
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
        ) );
        
    }

    function column_default( $item, $column_name ) {
		$server = new SMAPI_Server( $item->ID );

        switch( $column_name ) {
            case 'id':
                return $item->ID;
                break;
            
            case 'api_url':
	            $api_url_name = $item->post_title;
                return $api_url_name;
                break;
            case 'api_key':
                $api_key = $item->post_content;
	            return $api_key;
                break;
                case 'response_format':
                $status = $item->post_excerpt;
                return '<a href=#'.esc_attr($item->ID).' id="json_'.esc_attr($item->ID).'">'.$status.'</a>';
                break;
            
            case 'api_dated':
                return $item->post_date;
	            break;
            case 'enable':
                $api_enable = $item->post_status;
                return $api_enable;
                break;
            case 'parameter_handle':
                
                return  '<a href=#'.esc_attr($item->ID).' id="parameter_'.esc_attr($item->ID).'">VIEW</a>';
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
        if( !empty( $actions) && isset($_POST['smapi_server_ids'] )){

            $servers = (array) sanitize_text_field($_POST['smapi_server_ids']);

            if( $actions == 'delete' ){
                foreach ( $servers as $servers_id ) {
                    wp_delete_post( $servers_id, true );
                    
                }
            }

            //$this->prepare_items();
        }
        
    }

    function get_sortable_columns() {
        $sortable_columns = array(
            'id' => array( 'ID', false ),
            
        );
        return $sortable_columns;
    }

    function column_cb($item) {
        return sprintf(
            '<input type="checkbox" name="smapi_server_ids[]" value="%s" />',  esc_attr($item->ID)
        );
    }
    //is rendered in any column with a name/slug of 'title'.
    
    function column_id($item) {
        $actions = array(
            'edit'      => sprintf('<a class="%s" href="%s">Edit</a>','server_edit', '#'.esc_attr($item->ID)),

            'delete'    => sprintf('<a class="%s" href="%s">Delete</a>','server_delete', '#'.esc_attr($item->ID)),
        );

        return sprintf('%1$s %2$s', $item->ID, $this->row_actions($actions) );
    }
 //SERVER ADD FORM IS TAILORED

 function SmmServerform() {
//echo serialize($_POST).$this->passedaction; 
         

         ?>
            <div id="icon-tools" class="icon32"></div>
            <input id="add_new_server" type="button" value="Add Server" />

            
            <div class="server-entry-form">
            <form name="server_info" id="server_info">
            <table width="100%" border="0" cellpadding="4" cellspacing="0">
	        <caption>Method: New API Server</caption>
            <tr>
            <td colspan="2" align="right"><a href="#" id="server_clear">Clear</a></td>
		    <input type="hidden" name="fsid" value="0">
            </tr>
            <tr>
            <td>PLACE ORDER URL</td>
            <td><input type="text" name="fapi_url" placeholder="QUERY FOR NEW ORDER"></td>
            </tr>
            <tr>
            <td>API KEY PARAMETER</td>
            <td><input type="text" name="fapi_key_handle" placeholder="API KEY HANDLE"></td>
            </tr>
            <tr>
            <td>API KEY</td>
            <td><input type="text" name="fapi_key" placeholder="UNIQUE API KEY"></td>
            </tr>
            <tr>
            <td>LINK PARAMETER</td>
            <td><input type="text" name="fapi_link_handle" placeholder="LINK HANDLE"></td>
            </tr>
            <tr>
            <td>SERVICE PARAMETER</td>
            <td><input type="text" name="fapi_service_handle" placeholder="SERVICE / TYPE HANDLE"></td>
            
            </tr>
            <tr>
            <td>QUANTITY PARAMETER</td>
            <td><input type="text" name="fapi_quantity_handle" placeholder="QUANTITY HANDLE"></td>
            </tr>
            <tr>
            <td>ORDER PARAMETER <span> ( order handle )</span></td>
            <td><input type="text" name="fapi_order_response_handle" placeholder="SERVER RESPONSE HANDLE"></td> 
            </tr>
	        <tr>
            <td>ERROR PARAMETER <span> ( status or error handle )</span></td>
            <td><input type="text" name="fapi_error_response_handle" placeholder="SERVER RESPONSE"></td>
            </tr>
	    </table>
	    <table width="100%" border="0" cellpadding="4" cellspacing="0">
	    <caption>Method: Retrieve Api Order Status</caption>
	    <tr>
            <td>RETRIEVE STATUS QUERY URL</td>
            <td><input type="text" name="fapi_retrieve_status_query" placeholder="RETRIEVE ORDER DATA URL"></td>
            </tr>
            <tr>
            <td>ORDER ID PARAMETER</td>
            <td><input type="text" name="fapi_status_order_handle" placeholder="ORDER ID HANDLE"></td>
            </tr>
            <tr>
            <td>SERVER STATUS</td>
            <td><select name="fapi_server_status">
            <option value="enabled">ENABLED</option>
            <option value="disabled">DISABLED</option>
            </select>
			</td>
			</tr>
			<tr>
            <td>Auto Price Update</td>
            <td><select name="fapi_price_update">
            <option value="daily">Daily</option>
            <option value="weekly">Weekly</option>
			<option value="monthly">Monthly</option>
            </select>
			<input type="checkbox"  name="fsmm_price_update" id="fsmm_price_update" />
			Enable
			</td>
            </tr>
            <tr>
            <td align="right"></td>
            <td><input type="button" value="Save" id="server_save"><input type="button" value="cancel" id="server_cancel"></td>
            <td><input type="button" value="DEMO" id="server_demo"></td>
			</tr>
            </table>
            </form>
            </div>
            <div class="server-display-table">
            
            <table width="100%" border="0" cellpadding="4" cellspacing="0">
	        <caption>Method: New API Server</caption>
            
            <tr>
            <td>PLACE ORDER URL :</td>
            <td id="display_api_url"></td>
            </tr>
            <tr>
            <td>API KEY PARAMETER :</td>
            <td id="display_api_key_handle"></td>
            </tr>
            <tr>
            <td>API KEY :</td>
            <td id="display_api_key"></td>
            </tr>
            <tr>
            <td>LINK PARAMETER :</td>
            <td id="display_api_link_handle"></td>
            </tr>
            <tr>
            <td>SERVICE PARAMETER :</td>
            <td id="display_api_service_handle"></td>
            
            </tr>
            <tr>
            <td>QUANTITY PARAMETER :</td>
            <td id="display_api_quantity_handle"></td>
            </tr>
            <tr>
            <td>RESPONSE PARAMETER <span> ( order handle )</span> :</td>
            <td id="display_api_order_response_handle"></td>
            </tr>
	    <tr>
            <td>ERROR PARAMETER <span> ( status or error handle ) :</span></td>
            <td id="display_api_error_response_handle"></td>
            </tr>
	    </table>
	    <table width="100%" border="0" cellpadding="4" cellspacing="0">
	    <caption>Method: Retrieve Api Order Status</caption>
	    <tr>
            <td>RETRIEVE STATUS QUERY URL :</td>
            <td id="display_api_retrieve_status_query"></td>
            </tr>
            <tr>
            <td>ORDER ID PARAMETER :</td>
            <td id="display_api_status_order_handle"></td>
            </tr>
            <tr>
            <td>SERVER STATUS</td>
            <td id="display_api_server_status"></td>
            </tr>
            <tr>
            <td align="right"></td>
            <td></td>
            </tr>
            </table>
            
            </div>
			<div class="api-build-form">
			<button class="close_query">X</button>
			<h4>API Builder / Tester - Advanced Users Only</h4>
			

<label>Base URL For Server ID#:</label><label id="api_server_id"></label><br>
<input type="text" id="baseUrl" name="api_baseurl" placeholder="https://api.example.com" disabled><br>

<label>Endpoint:</label><br>
<input type="text" id="endpoint" name="url_end_points" placeholder="/resource"><br>

<!-- Query Params for New Order-->
<div id="queryParamsContainer" class="dropdown-container">
		<div class="dropdown-button">
		<label>Add New Order Parameters:</label>
		<span class="toggle-indicator">▼</span>
		</div>
		<div class="dropdown-pane">
		<div class="queryParam">
			<input type="text" class="paramKey" name ="bapi_key_handle" placeholder="key">
			<input type="text" class="paramValue" name ="bapi_key" placeholder="value">
			<span class="keyParam">🗝️</span>
		</div>
		<button id="addParam">+ Add Param</button><br><br>
		</div>
</div>
<!-- Params for results from API server-->
<div id="resultOrderContainer" class="dropdown-container">
		<div class="dropdown-button">
  		<label>Result Parameters for New Order:</label>
		<span class="toggle-indicator">▼</span>
		</div>
		<div class="dropdown-pane">
		<button id="resultOrder">+ Add Param</button>
		<button id="buildAddQuery">⚙️ Build Query</button>
		<button id="sendAddRequest">🚀 Send API Request</button><br><br>
		</div>
</div>
<!-- Query Params for status from API server-->
<div id="statusParamsContainer" class="dropdown-container">
		<div class="dropdown-button">
		<label>Order Status Parameters:</label>
		<span class="toggle-indicator">▼</span>
		</div>
			<div class="dropdown-pane">
  			<button id="statusParam">+ Add Param</button>
			
			</div>
</div>
<div id="resultStatusContainer" class="dropdown-container">
		<div class="dropdown-button">
		<label>Order Status Results Parameters:</label>
		<span class="toggle-indicator">▼</span>
		</div>
			<div class="dropdown-pane">
  			<button id="resultStatus">+ Add Param</button>
			<button id="buildStatusQuery">⚙️ Build Query</button>
			<button id="sendStatusRequest">🚀 Send API Request</button><br><br>
			</div>
</div>
<!-- Query Params for services from API server-->
<div id="servicesParamsContainer" class="dropdown-container">
		<div class="dropdown-button">
		<label>Get Services Parameters:</label>
		<span class="toggle-indicator">▼</span>
		</div>
			<div class="dropdown-pane">
  			<button id="servicesParam">+ Add Param</button>
			</div>
</div>
<div id="resultServicesContainer" class="dropdown-container">
		<div class="dropdown-button">
		<label>Services Result Parameters:</label>
		<span class="toggle-indicator">▼</span>
		</div>
			<div class="dropdown-pane">
  			<button id="resultServices">+ Add Param</button>
			<button id="buildServicesQuery">⚙️ Build Query</button>
			<button id="sendServicesRequest">🚀 Send API Request</button><br><br>
			</div>
</div>
<!-- Headers -->
<div id="headersContainer" class="dropdown-container">
		<div class="dropdown-button">
		<label>Header Parameters:</label>
		<span class="toggle-indicator">▼</span>
		</div>
			<div class="dropdown-pane">
			<div class="headerRow">	
			<input type="text" class="headerKey" placeholder="Header Name">
			<input type="text" class="headerValue" placeholder="Header Value">
			<button class="removeHeader">X</button>
			</div>
			<button id="addHeader" class="addPara">+ Add Header</button><br><br>
			</div>
</div>
<!-- JSON Body -->
<div id="QueryContainer">
<label>API Request Query for New Order  (URL):</label><br>
<textarea id="requestBody" rows="4" cols="40" placeholder="API Url..."></textarea>

</div>
<h3>Response:</h3>
<textarea id="apiResponse" rows="4" cols="40" placeholder="Json Formated..."></textarea><br><br>
<button id="save_query">💾 SAVE</button>
<button id="delete_query">🗑️ DELETE</button>
<button id="reset_query">🏠︎ RESET</button>
<button id="close_qform">❌ CLOSE</button>
 
 </div>
<?php
}     
}