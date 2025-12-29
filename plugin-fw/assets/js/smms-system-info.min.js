(function ($) {
    $(document).on('click', '.notice-dismiss', function () {
        var t = $(this),
            wrapper_id = t.parent().attr('id');

        if (wrapper_id === 'smms-system-alert') {
            var cname = 'hide_smms_system_alert',
                cvalue = 'yes';

            document.cookie = cname + "=" + cvalue + ";path=/";
        }
    });
})(jQuery);
