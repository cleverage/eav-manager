!function ($) {
    "use strict"; // jshint ;_;

    /**
     * Initialize datepickers inside a given element
     * @param target
     */
    function initDatePickers(target) {
        // Datetime pickers
        $(target).find('[data-provider="datepicker"]').datetimepicker({
            fontAwesome: true,
            autoclose: true,
            format: 'dd/mm/yyyy',
            language: 'fr',
            minView: 'month',
            pickerPosition: 'bottom-left',
            todayBtn: true,
            startView: 'month',
            clearBtn: true
        });

        $(target).find('[data-provider="datetimepicker"]').datetimepicker({
            fontAwesome: true,
            autoclose: true,
            format: 'dd/mm/yyyy hh:ii',
            language: 'fr',
            pickerPosition: 'bottom-left',
            todayBtn: true,
            clearBtn: true
        });

        $(target).find('[data-provider="timepicker"]').datetimepicker({
            fontAwesome: true,
            autoclose: true,
            format: 'hh:ii',
            formatViewType: 'time',
            maxView: 'day',
            minView: 'hour',
            pickerPosition: 'bottom-left',
            startView: 'day',
            clearBtn: true
        });

        // Restore value from hidden input
        $(target).find('.date').find('input[type=hidden]').each(function () {
            if ($(this).val()) {
                $(this).parent().datetimepicker('setValue');
            }
        });
    }

    $(document).on('global.event', function (e) {
        initDatePickers(e.target);
    });
}(window.jQuery);
