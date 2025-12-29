/**
 * admin.js
 *
 * @author Softnwords Themes
 * @package SMMS WooCommerce Subscription
 * @version 1.0.0
 */

jQuery(document).ready( function($) {
    "use strict";

    function toggle_product_editor_single_product(){
        if( $('#_smapi_subscription').prop('checked')){
            $('.smapi_price_is_per, .smapi_max_length').show();
        }else{
            $('.smapi_price_is_per, .smapi_max_length').hide();
        }
        if( $('#_smapi_api').prop('checked')){
            $('.smapi_server_name').show();
        }else{
            $('.smapi_server_name').hide();
        }
    }
    $('#_smapi_subscription').on('change', function(){
        toggle_product_editor_single_product();
    });
    $('#_smapi_api').on('change', function(){
        toggle_product_editor_single_product();
    });
    toggle_product_editor_single_product();

    $('#_smapi_price_time_option').on('change', function(){
        $('.smapi_max_length .description span').text( $(this).val() );
        var selected = $(this).find(':selected'),
            max_value = selected.data('max');
        $('.smapi_max_length .description .max-l').text( max_value );
    });

});