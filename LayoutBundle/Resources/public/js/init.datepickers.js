/**
 * Initialize datepickers inside a given element
 * @param target
 */
function initDatePickers(target) {
    // Datetime pickers
    $(target).find('[data-provider="datepicker"]').datetimepicker({
        autoclose: true,
        format: 'dd/mm/yyyy',
        language: 'fr',
        minView: 'month',
        pickerPosition: 'bottom-left',
        todayBtn: true,
        startView: 'month'
    });

    $(target).find('[data-provider="datetimepicker"]').datetimepicker({
        autoclose: true,
        format: 'dd/mm/yyyy hh:ii',
        language: 'fr',
        pickerPosition: 'bottom-left',
        todayBtn: true
    });

    $(target).find('[data-provider="timepicker"]').datetimepicker({
        autoclose: true,
        format: 'hh:ii',
        formatViewType: 'time',
        maxView: 'day',
        minView: 'hour',
        pickerPosition: 'bottom-left',
        startView: 'day'
    });

    // Restore value from hidden input
    $(target).find('.date').find('input[type=hidden]').each(function(){
        if($(this).val()) {
            $(this).parent().datetimepicker('setValue');
        }
    });
}
