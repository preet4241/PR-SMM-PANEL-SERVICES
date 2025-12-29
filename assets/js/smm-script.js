jQuery(document).ready(function(){
jQuery(".n_item_product").click(function(){
        var href = jQuery(this).attr("href").split("#");
        ajax("n_item_product", href[1]);
        });


jQuery("[id*=parameter_]").click(function(){
    var href = jQuery(this).attr("href").split("#");
    ajax("server_display", href[1]);
		jQuery(".server-display-table").fadeIn("fast");
	});
jQuery("[id*=json_]").click(function(){
        var href = jQuery(this).attr("href").split("#");
        ajax("api_build", href[1]);

     jQuery(".api-build-form").fadeIn("fast");

    });
jQuery(".server-display-table").click(function(){
    jQuery(".server-display-table").fadeOut("fast");
    });
jQuery("[id*=more_]").click(function(){
    var href = jQuery(this).attr("href").split("#");

jQuery(".item-display-table").fadeIn("fast");
    ajax("f_item_display", href[1]);
	});
jQuery(".item-display-table").click(function(){
    jQuery(".item-display-table").fadeOut("fast");
    });
jQuery('#f_api_server_list').on('change', function() {
    var value = jQuery(this).val();
	
    ajax("server_list", value);
});
jQuery('#_smapi_server_name_option').on('change', function() {
    var value = jQuery(this).val();
    var smmid = jQuery('.smapi_server_name').attr('id');
    ajax("server_product_list", value, smmid);
});


jQuery("#server_save").click(function(){

		ajax("server_save");
	});
jQuery("#server_demo").click(function(){

		ajax("server_demo");
	});

jQuery(".server_edit").click(function(){
        var href = jQuery(this).attr("href").split("#");
        ajax("server_edit", href[1]);

     jQuery(".server-entry-form").fadeIn("fast");

    });
jQuery(".server_delete").click(function(){
        var href = jQuery(this).attr("href").split("#");
        ajax("server_delete", href[1]);
        });
jQuery("#f_item_save").click(function(){
		ajax("f_item_save");
	});
jQuery("#server_clear").click(function(){
		jQuery(".server-entry-form").find("input[type=text], textarea").val("");
		jQuery("input[name=fsid]").val("");
	});
jQuery("#server_cancel").click(function(){
		jQuery(".server-entry-form").fadeOut("fast");
	});
jQuery(".close_query").click(function(){
		jQuery(".api-build-form").fadeOut("fast");
	});
jQuery("#close_qform").click(function(){
		jQuery(".api-build-form").fadeOut("fast");
	});
jQuery("#add_new_server").click(function(){

		jQuery(".server-entry-form").fadeIn("fast");
		jQuery("input[name=fsid]").val("");

	});
jQuery(".f_item_edit").click(function(){
        var href = jQuery(this).attr("href").split("#");
        ajax("f_item_edit", href[1]);

     jQuery(".item-entry-form").fadeIn("fast");

    });
jQuery(".f_item_delete").click(function(){
        var href = jQuery(this).attr("href").split("#");
        ajax("f_item_delete", href[1]);
        });


jQuery("#f_item_clear").click(function(){
		jQuery(".item-entry-form").find("input[type=text], textarea").val("");
		jQuery("input[name=fid]").val("");
	});


jQuery("#f_item_cancel").click(function(){
		jQuery(".item-entry-form").fadeOut("fast");
	});
jQuery("#f_item_import").click(function(){
		jQuery(".item-entry-form").fadeOut("fast");
		ajax("f_item_import");
	});

jQuery("#add_new_api_item").click(function(){

		jQuery(".item-entry-form").fadeIn("fast");
		jQuery("input[name=f_meta_id]").val("");

	});

	function ajax(action,id,num=0){
		if(action =="f_item_save")
			data = jQuery("#f_item_info").serialize()+"&action="+action;
		else if(action == "f_item_edit"){
			data = "action="+action+"&f_meta_key="+id;

		}
		else if(action == "f_item_import"){
			data = "action="+action;

		}
		else if(action == "n_item_product"){
					data = "action="+action+"&f_meta_key="+id;
					}
		else if(action == "f_item_delete"){
			data = "action="+action+"&f_meta_key="+id;

		}
		else if(action == "f_item_display"){
			data = "action="+action+"&f_meta_key="+id;

		}
		else if(action == "server_product_list"){
			data = "action="+action+"&f_smapi_server_name_option="+id+"&smmid="+num;

		}
		else if(action == "var_server_product_list"){
			data = "action="+action+"&var_smapi_server_name_option="+id;

		}
		else if(action == "var_service_span_data"){
			data = "action="+action+"&var_smapi_service_id_option="+id;

		}
		else if(action == "server_list"){
			data = "action="+action+"&f_api_server_list="+id;

		}
		else if(action == "server_save"){
			data = jQuery("#server_info").serialize()+"&action="+action;
		}
		else if(action == "server_delete"){
			data = "action="+action+"&item_id="+id;
		}
		else if(action == "server_edit"){
			data = "action="+action+"&item_id="+id;

		}
		else if(action == "api_build"){
			data = "action="+action+"&item_id="+id;

		}
		
		else if(action == "server_display"){
			data = "action="+action+"&item_id="+id;

		}
		else if(action == "server_demo"){
			data = "action="+action;

		}

// Clear any onbeforeunload warning
    window.onbeforeunload = null;

		jQuery.ajax({
			type: "POST",
			url: ajaxurl,
			data : data,
			dataType: "json",
			success: function(response){

				if(response.success == "1"){

					if(action == "f_item_save"){
					SmmAdminNotice(response.notice, response.color);
					jQuery(".item-entry-form").fadeOut("fast",function(){location.reload();});
					}
					else if(action == "f_item_delete"){
					SmmAdminNotice(response.notice, response.color);
					location.reload();
					}
					else if(action == "n_item_product"){
					SmmAdminNotice(response.notice, response.color);
					}
					else if(action == "f_item_import"){
					//alert(response.table_api_service);
					var newdata = response.table_api_service;
                    var table = jQuery('.wp-list-table').DataTable({
                    data: newdata,
                    columns: [
                                { data: 'cb' },
                                { data: 'service' },
                                { data: 'name' },
                                { data: 'rate' },
                                { data: 'min' },
                                { data: 'max' },
                                { data: 'status' },
                                { data: 'sub' },
                                { data: 'view' },
								{ data: 'product' }
                                ],
                    columnDefs: [ {
                                orderable: false,
                                className: 'select-checkbox',
                                targets:   0
                                } ],
                    select: {
                                style:    'single',
                                selector: 'td:first-child'
                                },
                    order: [[ 1, 'asc' ]] }
                                );
                    table.on( 'select', function ( e, dt, type, indexes ) {
                    if ( type === 'row' ) {
                        var rowdata = table.row( { selected: true } ).data();
                        rowdata.action = 'f_item_import';
                        var idstr = rowdata.service;
                        var strarray = idstr.split('<');
						var sid = strarray[0];
						rowdata.productid = jQuery('#' + sid).is(":checked");
                        rowdata.service = sid;
         //alert(JSON.stringify(rowdata));
                            jQuery.ajax({
                                            url: ajaxurl,
                                            type: 'POST',
                                            data: rowdata,
        //{ json: JSON.stringify(rowdata+data)},
                                            dataType: 'json',
                                            success: function(response){
                            SmmAdminNotice(response.notice, response.color);
                                        }
                    });


        // do something with the ID of the selected items
                    }
                    } );
					}

		else if(action == "f_item_edit"){
			jQuery('input[name=f_post_id]').val(response.f_post_id);
            jQuery('input[name=f_meta_key]').val(response.f_meta_key);
            jQuery('input[name=f_service_id]').val(response.f_service_id);
			jQuery('input[name=f_api_description]').val(response.f_api_description);
			jQuery('input[name=f_min_order]').val(response.f_min_order);
			jQuery('input[name=f_max_order]').val(response.f_max_order);
			jQuery('input[name=f_item_price]').val(response.f_item_price);

			jQuery('select[name=f_item_status]').val(response.f_item_status);


    		jQuery('input[name=f_item_post_count]').val(response.f_item_post_count);
			jQuery('input[name=f_item_post_delay]').val(response.f_item_post_delay);
					//jQuery('input[name=f_item_post_ex_date]').val(response.f_item_post_ex_date);

			jQuery('select[name=f_item_subscribe_check]').val(response.f_item_subscribe_check);


					}
		else if(action == "f_item_display"){

    					jQuery('#display_service_id').html(response.display_service_id) ;
    					jQuery('#display_api_description').html(response.display_api_description) ;
    					jQuery('#display_min_order').html(response.display_min_order) ;
    					jQuery('#display_max_order').html(response.display_max_order) ;
    					jQuery('#display_item_price').html(response.display_item_price) ;
    					jQuery('#display_item_status').html(response.display_item_status) ;
    					jQuery('#display_item_post_count').html(response.display_item_post_count) ;
    					jQuery('#display_item_post_delay').html(response.display_item_post_delay) ;
    					//jQuery('#display_api_item_post_ex_date').html(response.display_api_item_post_ex_date) ;
    					jQuery('#display_item_subscribe_check').html(response.display_item_subscribe_check) ;

    					}
    	else if(action == "server_product_list"){
					jQuery('select[name=_smapi_server_name_option]').val(response.f_smapi_server_name_option) ;
                    var sdata = response.option_data;
                    //alert(sdata);
                    jQuery('select[name=_smapi_service_id_option]').empty();
                    jQuery('select[name=_smapi_service_id_option]').append(sdata);
					
					}
		else if(action == "var_server_product_list"){
					//jQuery('select[name=var_smapi_server_name_option_'+num).val(response.var_smapi_server_name_option) ;
                    var sdata = response.option_data;
                    //alert(response.var_smapi_server_name_option);
                    //jQuery('select[name=var_smapi_service_id_option['+num+']').empty();
                    jQuery('#var_smapi_service_id_option_'+num).empty();
                    jQuery('#var_smapi_service_id_option_'+num).append(sdata);
					jQuery('#var_smapi_service_span_option_'+num).text(response.span_data);
					}
		else if(action == "var_service_span_data"){
					//changes span data for item selected
					var sdata = response.option_data;
                    
                   
					jQuery('#var_smapi_service_span_option_'+num).text(response.span_data);
					}			
    	else if(action == "server_list"){
					jQuery('select[name=f_api_server_list]').val(response.f_api_server_list) ;

					location.reload();
					}
		else if(action == "server_edit"){
			var auto_price_enable = response.smm_price_update;
			jQuery('input[name=fsid]').val(response.id);
			jQuery('input[name=fapi_url]').val(response.api_url);
			jQuery('input[name=fapi_key_handle]').val(response.api_key_handle);
			jQuery('input[name=fapi_key]').val(response.api_key);
		    jQuery('input[name=fapi_link_handle]').val(response.api_link_handle);
	        jQuery('input[name=fapi_service_handle]').val(response.api_service_handle);
            jQuery('input[name=fapi_quantity_handle]').val(response.api_quantity_handle);
            jQuery('input[name=fapi_order_response_handle]').val(response.api_order_response_handle);
			jQuery('input[name=fapi_error_response_handle]').val(response.api_error_response_handle);
			jQuery('input[name=fapi_retrieve_status_query]').val(response.api_retrieve_status_query);
			jQuery('input[name=fapi_status_order_handle]').val(response.api_status_order_handle);
			jQuery('select[name=fapi_server_status]').val(response.api_server_status);
			jQuery('select[name=fapi_price_update]').val(response.api_price_update);
			if (auto_price_enable == "1") {
				jQuery('#fsmm_price_update').prop("checked", true);
			} else {
				jQuery('#fsmm_price_update').prop("checked", false);
			}
		}
		else if(action == "api_build"){
			jQuery('#api_server_id').text(response.id);
			jQuery('input[name=api_baseurl]').val(response.api_baseurl);
			jQuery('input[name=url_end_points]').val(response.url_end_points);
			
			if(response.api_key_handle != null)
			jQuery('input[name=bapi_key_handle]').val(response.api_key_handle);
			if(response.api_key != null)
			jQuery('input[name=bapi_key]').val(response.api_key);
				
			if(response.api_link_handle != null && ! jQuery('input[name=bapi_link_handle]').length )
			{
				jQuery("#addParam").before(`
								<div class="queryParam">
								<input type="text" class="paramKey" name="bapi_link_handle" placeholder="handle">
								<input type="text" class="paramValue" name="bapi_link" placeholder="Enter URL OR Username">
								<span class="linkParam">♾️</span>
								</div>`
								);
			jQuery('input[name=bapi_link_handle]').val(response.api_link_handle);
			if( response.api_baseurl ==='https://seoclerks.in')
			jQuery('input[name=bapi_link]').val('https://www.softnwords.com');
			else
			jQuery('input[name=bapi_link]').val(response.api_link);	
			}
			if(response.api_service_handle != null && ! jQuery('input[name=bapi_service_handle]').length )
			{
				jQuery("#addParam").before(`
								<div class="queryParam">
								<input type="text" class="paramKey" name="bapi_service_handle" placeholder="handle">
								<input type="text" class="paramValue" name="bapi_service" placeholder="Enter Service ID">
								<span class="serviceParam">#️⃣</button>
								</div>`
								);
			jQuery('input[name=bapi_service_handle]').val(response.api_service_handle);
			if( response.api_baseurl ==='https://seoclerks.in')
			jQuery('input[name=bapi_service]').val('1');
			else
			jQuery('input[name=bapi_service]').val(response.api_service);	
			}
			if(response.api_quantity_handle != null && ! jQuery('input[name=bapi_quantity_handle]').length )
			{
				jQuery("#addParam").before(`
								<div class="queryParam">
								<input type="text" class="paramKey" name="bapi_quantity_handle" placeholder="handle">
								<input type="text" class="paramValue" name="bapi_quantity" placeholder="Enter Quantity">
								<span class="quantityParam">⚖︎</span>
								</div>`
								);
			jQuery('input[name=bapi_quantity_handle]').val(response.api_quantity_handle);
			if( response.api_baseurl ==='https://seoclerks.in')
			jQuery('input[name=bapi_quantity]').val('1000');
			else	
			jQuery('input[name=bapi_quantity]').val(response.api_quantity);			
			}
			if(response.api_action_add_order != null && ! jQuery('input[name=bapi_action_add_order]').length )
			{
				jQuery("#addParam").before(`
								<div class="queryParam">
								<input type="text" class="paramKey" name="bapi_action_handle" placeholder="Enter Action Handle">
								<input type="text" class="paramValue" name="bapi_action_add_order" placeholder="Enter Action Param">
								<span class="actionParam">🎬</span>
								</div>`
								);
			jQuery('input[name=bapi_action_add_order]').val(response.api_action_add_order);
			jQuery('input[name=bapi_action_handle]').val(response.api_action_handle);
			}
			if(response.api_addparam){
				let apiaddparam = response.api_addparam;
				Object.keys(apiaddparam).forEach(function(key){
				if(!jQuery(`input[name=${key}]`).length)
				jQuery("#addParam").before(`
												<div class="queryParam">
												<input type="text" class="paramKey" name="${key}" value="${key}" placeholder="Parameter">
												<input type="text" class="paramValue" value="${apiaddparam[key]}" placeholder="value">
												<button class="removeParam">X</button>
												</div>
												`);
				
				});
			}
            if(response.api_order_response_handle != null && ! jQuery('input[name=bresult_handle]').length )
			{
				jQuery("#resultParam").before(`
								<div class="resultParam">
								<input type="text" class="paramKey" name="bresult_handle" placeholder="handle">
								<input type="text" class="orderparamValue" placeholder="Order#" disabled>
								<button class="removeResult">X</button>
								</div>`
								);
			jQuery('input[name=bresult_handle]').val(response.api_order_response_handle);
			}
			if(response.api_error_response_handle != null && ! jQuery('input[name=bapi_error_response_handle]').length )
			{
				jQuery("#resultParam").before(`
								<div class="resultParam">
								<input type="text" class="paramKey" name="bapi_error_response_handle" placeholder="handle">
								<input type="text" class="paramValue" placeholder="error#" disabled>
								<button class="removeResult">X</button>
								</div>`
								);
			jQuery('input[name=bapi_error_response_handle]').val(response.api_error_response_handle);
			}
			if(response.api_resultorder){
				let apiresultorder = response.api_resultorder;
				Object.keys(apiresultorder).forEach(function(key){
				let result = key + 'result';
				if(!jQuery(`input[name=${result}]`).length  )
				jQuery("#resultOrder").before(`
												<div class="resultOrder">
												<input type="text" class="paramKey" name="${result}" value="${key}" placeholder="Parameter">
												<input type="text" class="paramValue" value="${apiresultorder[key]}" placeholder="#">
												<button class="removeParam">X</button>
												</div>
												`);
				
				});
			}
			if(response.api_retrieve_status_query != null && ! jQuery('input[name=bapi_retrieve_status_query]').length )
			{
				jQuery("#statusParam").before(`
								<div class="statusParam">
								<input type="text" class="paramKey" name="bbase_url" value="base_url" disabled>
								<input type="text" class="paramValue" name="bapi_retrieve_status_query" placeholder="Enter URL">
								<span class="urlParam">↪</span>
								</div>`
								);
			jQuery('input[name=bapi_retrieve_status_query]').val(response.api_retrieve_status_query);
			}
			if(response.api_statusparam){
				let apistatusparam = response.api_statusparam;
				Object.keys(apistatusparam).forEach(function(key){
				let status = key + 'status';
				if(!jQuery(`input[name=${status}]`).length && key != response.api_key_handle  )
				jQuery("#statusParam").before(`
												<div class="statusParam">
												<input type="text" class="paramKey" name="${status}" value="${key}" placeholder="Parameter">
												<input type="text" class="paramValue" value="${apistatusparam[key]}" placeholder="Enteer Value">
												<button class="removeParam">X</button>
												</div>
												`);
				
				});
			}
			if(response.api_status_order_handle != null && ! jQuery('input[name=bapi_status_order_handle]').length )
			{
				jQuery("#statusParam").before(`
								<div class="statusParam">
								<input type="text" class="paramKey"  name="bapi_status_order_handle" placeholder="Enter orderParameter">
								<input type="text" class="paramValue" placeholder="Enter Order Number">
								<span class="orderParam">📝</span>
								</div>`
								);
			jQuery('input[name=bapi_status_order_handle]').val(response.api_status_order_handle);
			}
			if(response.api_action_order_status != null && ! jQuery('input[name=bapi_action_order_status]').length )
			{
				jQuery("#statusParam").before(`
								<div class="statusParam">
								<input type="text" class="paramKey" name="bapi_action_handle_status" placeholder="Enter Action Handle">
								<input type="text" class="paramValue" name="bapi_action_order_status" placeholder="Enter Action Param">
								<span class="actionParam">🎬</span>
								</div>`
								);
			jQuery('input[name=bapi_action_order_status]').val(response.api_action_order_status);
			jQuery('input[name=bapi_action_handle_status]').val(response.api_action_handle);
			}
			if(response.api_resultstatus){
				let apiresultstatus = response.api_resultstatus;
				Object.keys(apiresultstatus).forEach(function(key){
				let result = key + 'rstatus';
				if(!jQuery(`input[name=${result}]`).length  )
				jQuery("#resultStatus").before(`
												<div class="resultStatus">
												<input type="text" class="paramKey" name="${result}" value="${key}" placeholder="Parameter">
												<input type="text" class="paramValue" value="${apiresultstatus[key]}" placeholder="#">
												<button class="removeParam">X</button>
												</div>
												`);
				
				});
			}
			if(response.api_action_get_services != null && ! jQuery('input[name=bapi_action_get_services]').length )
			{
				jQuery("#servicesParam").before(`
								<div class="servicesParam">
								<input type="text" class="paramKey" name="bapi_action_handle_services" placeholder="Enter Action Handle">
								<input type="text" class="paramValue" name="bapi_action_get_services" placeholder="Enter Action Param">
								<span class="actionParam">🎬</span>
								</div>`
								);
			jQuery('input[name=bapi_action_get_services]').val(response.api_action_get_services);
			jQuery('input[name=bapi_action_handle_services]').val(response.api_action_handle);
			}
			if(response.api_servicesparam){
				let apiservicesparam 	= response.api_servicesparam;
				Object.keys(apiservicesparam).forEach(function(key){
				let services 			= key + "services";
				if(!jQuery(`input[name=${services}]`).length && key != response.api_key_handle)
				jQuery("#servicesParam").before(`
												<div class="servicesParam">
												<input type="text" class="paramKey" name="${services}" value="${key}" placeholder="Parameter">
												<input type="text" class="paramValue" value="${apiservicesparam[key]}" placeholder="value">
												<button class="removeParam">X</button>
												</div>
												`);
				
				});
			}
			if(response.api_resultservices){
				let api_resultservices = response.api_resultservices;
				Object.keys(api_resultservices).forEach(function(key){
				let result = key + 'rservices';
				if(!jQuery(`input[name=${result}]`).length  )
				jQuery("#resultServices").before(`
												<div class="resultServices">
												<input type="text" class="paramKey" name="${result}" value="${key}" placeholder="Parameter">
												<input type="text" class="paramValue" value="${api_resultservices[key]}" placeholder="#">
												<button class="removeParam">X</button>
												</div>
												`);
				
				});
			}
            
			
		}
		else if(action == "server_save"){
					SmmAdminNotice(response.notice, response.color);
					
					jQuery(".server-entry-form").fadeOut("fast",function(){});
					}
					else if(action == "server_demo"){
					SmmAdminNotice(response.notice, response.color);
					jQuery(".server-entry-form").fadeOut("fast",function(){location.reload();});
					}
					else if(action == "server_delete"){
					SmmAdminNotice(response.notice, response.color);
					location.reload();
					}

    	else if(action == "server_display"){

    					jQuery('#display_api_url').html(response.display_api_url) ;
    					jQuery('#display_api_key_handle').html(response.display_api_key_handle) ;
    					jQuery('#display_api_key').html(response.display_api_key) ;
    					jQuery('#display_api_link_handle').html(response.display_api_link_handle) ;
    					jQuery('#display_api_service_handle').html(response.display_api_service_handle) ;
    					jQuery('#display_api_quantity_handle').html(response.display_api_quantity_handle) ;
    					jQuery('#display_api_order_response_handle').html(response.display_api_order_response_handle) ;
    					jQuery('#display_api_error_response_handle').html(response.display_api_error_response_handle) ;
    					jQuery('#display_api_retrieve_status_query').html(response.display_api_retrieve_status_query) ;
    					jQuery('#display_api_status_order_handle').html(response.display_api_status_order_handle) ;
    					jQuery('#display_api_server_status').html(response.display_api_server_status) ;
    					}

					}
			        if(response.success == "0"){
					var res= "";
					var str3 = "\n";
    					jQuery.each( response, function( key, value ) {
  					res +=  key + ": " + value + str3 ;
					});
				alert("EMPTY FIELDS ARE NOT ACCEPTABLE."+ str3 + res);
					}
				},
			error: function(xhr, status, error) {
                    
                    alert(xhr.responseText + data);
                    }
			});
			}
			/**
 * Create and show a dismissible admin notice
 */
    function SmmAdminNotice( msg, colour ) {


    /* create notice div  notice-info notice-success notice-error notice-warning*/

    var div = document.createElement( 'div' );
    div.classList.add( 'notice', 'inline', colour , 'is-dismissible');

    /* create paragraph element to hold message */

    var p = document.createElement( 'p' );

    /* Add message text */

    p.appendChild( document.createTextNode( msg ) );

    // Optionally add a link here

    /* Add the whole message to notice div */

    div.appendChild( p );

    /* Create Dismiss icon */

    var b = document.createElement( 'button' );
    b.setAttribute( 'type', 'button' );
    b.classList.add( 'notice-dismiss' );

    /* Add screen reader text to Dismiss icon */

    var bSpan = document.createElement( 'span' );
    bSpan.classList.add( 'screen-reader-text' );
    bSpan.appendChild( document.createTextNode( 'Dismiss this notice' ) );
    b.appendChild( bSpan );

    /* Add Dismiss icon to notice */

    div.appendChild( b );

    /* Insert notice after the first h1 */

    var h1 = document.getElementsByClassName( 'tablenav' )[0];
    h1.parentNode.insertBefore( div, h1.nextSibling);


    /* Make the notice dismissable when the Dismiss icon is clicked */

    p.addEventListener( 'click', function () {
        div.parentNode.removeChild( div );
    });
	b.addEventListener( 'click', function () {
        div.parentNode.removeChild( div );
    });


}
jQuery(document).on('change', 'select', function(){
    
						
    var smm_str         = jQuery(this).attr('id');
    var itemspan        = jQuery('option:selected', this).attr('data-desc');
    var item            = jQuery(this).find(":selected").val();
    if(itemspan == null){  
    var itemsret        = item .split("data-desc");
        itemspan        = itemsret[1];
    };
    var prefix          = "var_smapi_server_name_option_";
    var prefixitem      = "var_smapi_service_id_option_";
    var num             = parseInt(smm_str.substring(prefix.length), 10);
    var numb            = parseInt(smm_str.substring(prefixitem.length), 10);
    if (num >= 0 ) {
    ajax("var_server_product_list", item, num);
    }
    if (numb >= 0 ) {
    ajax("var_service_span_data", itemspan, numb);
    }
    
    
            
        });
		jQuery(document).on('click', '[class^="smm_select"]', function(){
    
						jQuery(this).select2({width: 'resolve'});
    
    
    
            
        });
		jQuery("#search_id-search-input").on("keyup", function() {// api item search box function
				var value = jQuery(this).val();

				jQuery("table tr").each(function(index) {
					if (index === 0) return; // skip header row

					let secondCol = jQuery(this).find("td").eq(1).text();// second column check
					$row = jQuery(this);
					var id = $row.find("td:first").text();// first column check
					if ( secondCol.toLowerCase().indexOf(value.toLowerCase()) !== -1 || id.indexOf(value) === 0) {
					$row.show();
					}
					else {
					$row.hide();
					}
				
				});
		});// end of search box function
		

  // API BUILD Add Param row
 jQuery("#addParam").click(function() {
    jQuery("#addParam").before(`
      <div class="queryParam">
        <input type="text" class="paramKey" placeholder="Parameter">
        <input type="text" class="paramValue" placeholder="value">
        <button class="removeParam">X</button>
      </div>
    `);
  });
  // Remove Param row
  jQuery(document).on("click", ".removeParam", function() {
    jQuery(this).parent().remove();
  });
  jQuery("#resultOrder").click(function() {
    jQuery(this).before(`
      <div class="resultOrder">
        <input type="text" class="paramKey" placeholder="Parameter">
        <input type="text" class="paramValue" placeholder="value">
        <button class="removeResult">X</button>
      </div>
    `);
  });
  // Remove Param row
  jQuery(document).on("click", ".removeResult", function() {
    jQuery(this).parent().remove();
  });
  // Status Query Params
   jQuery("#statusParam").click(function() {
    jQuery(this).before(`
      <div class="statusParam">
        <input type="text" class="paramKey" placeholder="Parameter">
        <input type="text" class="paramValue" placeholder="value">
        <button class="removeStatus">X</button>
      </div>
    `);
  });
  // Remove Param row
  jQuery(document).on("click", ".removeStatus", function() {
    jQuery(this).parent().remove();
  });
  // Status Query Result Params 
   jQuery("#resultStatus").click(function() {
    jQuery(this).before(`
      <div class="resultStatus">
        <input type="text" class="paramKey" placeholder="Parameter">
        <input type="text" class="paramValue" placeholder="value">
        <button class="removeSback">X</button>
      </div>
    `);
  });
  // Remove Param row
  jQuery(document).on("click", ".removeSback", function() {
    jQuery(this).parent().remove();
  });


  // Services Param
  jQuery("#servicesParam").click(function() {
    jQuery(this).before(`
      <div class="servicesParam">
        <input type="text" class="paramKey" placeholder="Parameter">
        <input type="text" class="paramValue" placeholder="value">
        <button class="removeServices">X</button>
      </div>
    `);
  });
  // Remove services Param row
  jQuery(document).on("click", ".removeServices", function() {
    jQuery(this).parent().remove();
  });
  // Service Back Parameter
  jQuery("#resultServices").click(function() {
    jQuery(this).before(`
      <div class="resultServices">
        <input type="text" class="paramKey" placeholder="Parameter">
        <input type="text" class="paramValue" placeholder="value">
        <button class="removeSerback">X</button>
      </div>
    `);
  });
  // Remove services back Param row
  jQuery(document).on("click", ".removeSerback", function() {
    jQuery(this).parent().remove();
  });
  // Add Header row
    jQuery("#addHeader").click(function() {
    jQuery(this).before(`
      <div class="headerRow">
        <input type="text" class="headerKey" placeholder="Header Name">
        <input type="text" class="headerValue" placeholder="Header Value">
        <button class="removeHeader">X</button>
      </div>
    `);
  });
  // Remove Header row
  jQuery(document).on("click", ".removeHeader", function() {
    jQuery(this).parent().remove();
  });
// Drop down Pane
const dropdownButtons 	= document.querySelectorAll('.dropdown-button');
// Toggle drop pane open/close by button only
dropdownButtons.forEach(btn => {
  btn.addEventListener('click', (e) => {
  e.stopPropagation();
  //next class and find class inside btn
  let mypane 	= jQuery(btn).next('.dropdown-pane');
  let indicator	= jQuery(btn).find('.toggle-indicator');
    if(mypane.is(':visible')){
      mypane.slideUp();
	  indicator.removeClass('active');
    }
    else{
      mypane.slideDown(); 
	  indicator.addClass('active');
    }  
 });
}); 
    // Build ADD Order Query
	jQuery("#buildAddQuery").click(function() {
	  var query_url = build_query("add_order");
  	jQuery("#requestBody").text(query_url);
    });
	// Build order status Query
	jQuery("#buildStatusQuery").click(function() {
	  var query_url = build_query("get_status");
  	jQuery("#requestBody").text(query_url);
    });
	// Build Services Query
	jQuery("#buildServicesQuery").click(function() {
	  var query_url = build_query("get_services");
  	jQuery("#requestBody").text(query_url);
    });
	// Send API request for new order
	jQuery("#sendAddRequest").click(function() {
	  var server_id	= jQuery("#api_server_id").text();
	  var query_url = build_query("add_order");
	  //let parts = query_url.split('?');
	  //let baseUrl = parts[0];
	  var queryParams = "action=api_server_request"+"&server_id="+server_id+"&sendapirequest="+encodeURIComponent(query_url);
	  //ajax("api_server_request",queryParams);
		send_ajax_post(queryParams);
    });
	// Send API request for status
	jQuery("#sendStatusRequest").click(function() {
	  var server_id	= jQuery("#api_server_id").text();
	  var query_url = build_query("get_status");
	  //let parts = query_url.split('?');
	  //let baseUrl = parts[0];
	  var queryParams = "action=api_server_request"+"&server_id="+server_id+"&sendapirequest="+encodeURIComponent(query_url);
	  //ajax("api_server_request",queryParams);
		send_ajax_post(queryParams);
    });
	
	// Send API request for get services reset_query
	jQuery("#sendServicesRequest").click(function() {
	  var server_id	= jQuery("#api_server_id").text();
	  var query_url = build_query("get_services");
	  var queryParams = "action=api_server_request"+"&server_id="+server_id+"&sendapirequest="+encodeURIComponent(query_url);
	  send_ajax_post(queryParams);
    });
	// Button reset_query
	jQuery("#reset_query").click(function() {
			jQuery("#apiResponse").text('Json...');
			jQuery("#requestBody").text('Api Url...');
		
    });
	// Button save_query
	jQuery("#save_query").click(function() {
			jQuery("#apiResponse").text('Json...');
			var add_query_url 		= build_query("add_order");
			var result_order		= build_query("result_order");// for order result
			var status_query_url 	= build_query("get_status");
			var result_status		= build_query("result_status");// for status result
			var service_query_url 	= build_query("get_services");
			var result_services		= build_query("result_services");// for service result
			var server_id			= jQuery("#api_server_id").text();
			var queryParams 		= "action=api_server_request"+"&server_id="+server_id+
		"&add_query_url="+encodeURIComponent(add_query_url)+
		"&result_order="+encodeURIComponent(result_order)+
		"&status_query_url="+encodeURIComponent(status_query_url)+
		"&result_status="+encodeURIComponent(result_status)+
		"&service_query_url="+encodeURIComponent(service_query_url)+
		"&result_services="+encodeURIComponent(result_services);

	  //ajax("api_server_request",queryParams);
		send_ajax_post(queryParams);
    });
		// Button delete_query
	jQuery("#delete_query").click(function() {
			jQuery("#apiResponse").text('Json...');
			jQuery("#requestBody").text('Api Url...');
			var server_id	= jQuery("#api_server_id").text();
		var queryParams = "action=api_server_request"+"&server_id="+server_id;
	  //ajax("api_server_request",queryParams);
		send_ajax_post(queryParams);
    });

// send Post ajax request to API server-display-table
function send_ajax_post(queryParam){
	// Send POST request
	
	var header 	= {};
	header		= get_headers();
	
    jQuery.ajax({
      url: ajaxurl,
      method: "POST",
      
      data: queryParam,
      dataType: "json",
      success: function(response) {
        jQuery("#apiResponse").text(JSON.stringify(response.result, null, 2));
		var key = jQuery('input[name=bresult_handle]').val();
		if(response.result[key] !="")
		jQuery(".orderparamValue").val(response.result[key]);
      },
      error: function(xhr) {
        jQuery("#apiResponse").text("❌ Error: " + xhr.status + " " + xhr.statusText + "\n" + xhr.responseText);
      }
    });
}
//get headers
function get_headers(){
	// Collect headers
    var headers = {};
    jQuery(".headerRow").each(function() {
      var key = jQuery(this).find(".headerKey").val();
      var value = jQuery(this).find(".headerValue").val();
      if (key) {
        headers[key] = value;
      }
    });
	return headers;
}
// Build Add Order Query
function build_query(item_url){
	var baseUrl 	= jQuery("#baseUrl").val();
    var endpoint 	= jQuery("#endpoint").val();
	var server_id	= jQuery("#api_server_id").text();
    // Clean slashes
    if (baseUrl.endsWith("/")) baseUrl = baseUrl.slice(0, -1);
    if (endpoint && !endpoint.startsWith("/")) endpoint = "/" + endpoint;

    // Build query string
    var queryParams = [];
	if(item_url === "add_order")
    jQuery(".queryParam").each(function() {
      var key = jQuery(this).find(".paramKey").val();
      var value = jQuery(this).find(".paramValue").val();
      if (key) {
        queryParams.push(encodeURIComponent(key) + "=" + encodeURIComponent(value));
      }
    });
	if(item_url === "result_order")
    jQuery(".resultOrder").each(function() {
      var key = jQuery(this).find(".paramKey").val();
      var value = jQuery(this).find(".paramValue").val();
      if (key) {
        queryParams.push(encodeURIComponent(key) + "=" + encodeURIComponent(value));
      }
    });
	if(item_url === "get_status")
	{
		baseUrl 	= jQuery('input[name=bapi_retrieve_status_query]').val();
		endpoint	= '';
		var pkey 	= jQuery('input[name=bapi_key_handle]').val();
		var pvalue 	= jQuery('input[name=bapi_key]').val();
	queryParams.push(encodeURIComponent(pkey) + "=" + encodeURIComponent(pvalue));
    jQuery(".statusParam").each(function() {
      var key = jQuery(this).find(".paramKey").val();
      var value = jQuery(this).find(".paramValue").val();
      if (key != 'base_url') {
        queryParams.push(encodeURIComponent(key) + "=" + encodeURIComponent(value));
      }
    });
	}
	if(item_url === "result_status")
    jQuery(".resultStatus").each(function() {
      var key = jQuery(this).find(".paramKey").val();
      var value = jQuery(this).find(".paramValue").val();
      if (key) {
        queryParams.push(encodeURIComponent(key) + "=" + encodeURIComponent(value));
      }
    });
	if(item_url === "get_services")
	{
		
		var pkey 	= jQuery('input[name=bapi_key_handle]').val();
		var pvalue 	= jQuery('input[name=bapi_key]').val();
		
	queryParams.push(encodeURIComponent(pkey) + "=" + encodeURIComponent(pvalue));
    jQuery(".servicesParam").each(function() {
      var key = jQuery(this).find(".paramKey").val();
      var value = jQuery(this).find(".paramValue").val();
      if (key) {
        queryParams.push(encodeURIComponent(key) + "=" + encodeURIComponent(value));
      }
    });
	}
	if(item_url === "result_services")
    jQuery(".resultServices").each(function() {
      var key = jQuery(this).find(".paramKey").val();
      var value = jQuery(this).find(".paramValue").val();
      if (key) {
        queryParams.push(encodeURIComponent(key) + "=" + encodeURIComponent(value));
      }
    });
    var queryString = queryParams.length ? "?" + queryParams.join("&") : "?" + "no_param";
    var url = baseUrl + endpoint + queryString;
    return url;
}
});