(function ($) {
    $(document).ready(function () {

        // Hide all
        $(".var_smm_customer_input_field_label").css("display", "none");

        // Change
        $("input.variation_id").change(function () {
            // Hide all
            $(".var_smm_customer_input_field_label").css("display", "none");

            if ($("input.variation_id").val() !== "") {
                var var_id = $("input.variation_id").val();

                // Display current
                $(".var_smm_customer_input_field_label-" + var_id).css("display", "block");
                if ($(".var_smm_customer_input_field_label-" + var_id).length <= 0)
                    var_id = 0;
                $.ajax(
                    {
                        type: 'POST',
                        url: prefix_vars.ajaxurl,
                        data: {
                            action: 'prefix_selected_variation_id',
                            security: $("input.variation_id").val(),

                            cart_id: var_id
                        },
                        success: function (response) {
                            //alert(response.text);
                        }
                    }
                )

            }
        });
        $(window).on('load', function () {
            $(".smm_form__label").each(function () {
                if ($(this).text() != "") {
                    var t = $(this).text();
                    var v = $(this).data('desc');
var vin =  $(this).parent(".smm_form__group").find('.smm_form__field').val();
					
					if (vin == undefined)
            $(this).parent(".smm_form__group").prepend($('<input />', { 'class': 'smm_form__field', 'placeholder': t, 'id': 'smm-form', 'name': 'smm-custom-form', 'value': v })
                    );
            
                };
           });
    $(".smm_form__field").focusout( function () {
            var t = $('.smm_form__field').val();
            var h = $(this);
            var id = $('.smm_form__label').data('id');
            if ((t == null) || t === "")
                $(".wc-proceed-to-checkout").css("display", "none");
            else
                $.ajax(
                    {
                        type: 'POST',
                        url: prefix_vars.ajaxurl,
                        data: {
                            action: 'custom_input_data_id',
                            cartkey: id,
                            security: id,
                            customer_data: t
                        },
                        success: function (response) {
                            //alert(response.text);
                            $(h).text(t).css("color", "black");
                            $(".wc-proceed-to-checkout").css("display", "block");
                        }
                    }
                );

        });
     });
    
     $(document).on('ajaxComplete', function(){
                    $(".smm_form__label").each(function () {
                if ($(this).text() != "") {
                    var t = $(this).text();
                    var v = $(this).data('desc');
					var vin =  $(this).parent(".smm_form__group").find('.smm_form__field').val();
					
					if (vin == undefined)
            $(this).parent(".smm_form__group").prepend($('<input />', { 'class': 'smm_form__field', 'placeholder': t, 'id': 'smm-form', 'name': 'smm-custom-form', 'value': v })
                    );
            
                };
           });
		  $(".smm_form__field").focusout( function () {
            var t = $('.smm_form__field').val();
            var h = $(this);
            var id = $('.smm_form__label').data('id');
            if ((t == null) || t === "")
                $(".wc-proceed-to-checkout").css("display", "none");
            else
                $.ajax(
                    {
                        type: 'POST',
                        url: prefix_vars.ajaxurl,
                        data: {
                            action: 'custom_input_data_id',
                            cartkey: id,
                            security: id,
                            customer_data: t
                        },
                        success: function (response) {
                            //alert(response.text);
                            $(h).text(t).css("color", "black");
                            $(".wc-proceed-to-checkout").css("display", "block");
                        }
                    }
                );

        });

    });
        
    

    });
    
    $(".quantity input[name=quantity]").on('change', function (e) {
        var setprice;
        setprice = $(this).val() - ($(this).val() % $(this).attr("min"));
        if ($(this).val() % $(this).attr("min") != 0) {

            $(this).val(setprice);
            show_error($(".min-max-qty"), "Quantity set by multiple of " + $(this).attr("min"));
            if (setprice > $(this).attr("max"))
                $(this).val($(this).attr("max"));
            if (setprice < $(this).attr("min"))
                $(this).val($(this).attr("min"));
            //alert(setprice);
        } else {
            if (setprice > $(this).attr("max"))
                $(this).val($(this).attr("max"));
            remove_error($(this).parent(".min-max-qty"));
        }
    });
    function show_error($field, $mesg) {
        if ($field.prev('.error_msg').length) {
            $field.prev('.error_msg').html('<p>' + $mesg + '</p>');
        } else {
            $('<div id="toast"><div id="desc">' + $mesg + '</div></div>').insertAfter($field);
            var x = document.getElementById("toast")
            x.className = "show";
            setTimeout(function () { x.className = x.className.replace("show", ""); }, 5000);
        }
    }
    function remove_error($field) {
        if ($field.prev('.error_msg').length) {
            $field.prev('.error_msg').remove();
        }
    }
    // basic paging logic to demo the buttons
    var pr = $('.paginate.left');
    var pl = $('.paginate.right');

    pr.click(slide.bind(this, -1));
    pl.click(slide.bind(this, 1));

    var index = 0, total = 5;
    var current_index = 0;
    function slide(offset) {
        index = Math.min(Math.max(index + offset, 0), total - 1);
        $.ajax(
            {
                type: 'POST',
                url: prefix_vars.ajaxurl,
                data: {
                    action: 'subscription_select_data',
                    sub_smm_data: index + 1,
                    sub_smm_text: $('.smm-counter').html(),
                    smm_session_product: pr.attr('smm_session_product')
                },
                success: function (response) {
                    //alert(response.text);
                    $('.smm-counter').html('subscribe ' + (response.sub_smm_data) + response.sub_smm_cycle);
                    current_index = index + 1;

                }
            }
        );
        pr.attr('data-state', index === 0 ? 'disabled' : index + 1);
        pl.attr('data-state', index === total - 1 ? 'disabled' : index + 1);
    }
    $('.smm-counter').click(function () {
        if ($('.smm-counter').html().includes('day'))
            $('.smm-counter').html($('.smm-counter').html().replace(/day/, 'week'));
        else if ($('.smm-counter').html().includes('week'))
            $('.smm-counter').html($('.smm-counter').html().replace(/week/, 'month'));
        else if ($('.smm-counter').html().includes('month'))
            $('.smm-counter').html($('.smm-counter').html().replace(/month/, 'day'));
        $.ajax(
            {
                type: 'POST',
                url: prefix_vars.ajaxurl,
                data: {
                    action: 'subscription_select_data',
                    sub_smm_data: current_index,
                    sub_smm_text: $('.smm-counter').html(),
                    smm_session_product: pr.attr('smm_session_product')
                },
                success: function (response) {
                    //alert(response.text);
                    //$( '.smm-counter' ).html('subscribe '+( response.sub_smm_data) + response.sub_smm_cycle);
                }
            }
        );
    });
    slide(0);


})(jQuery);