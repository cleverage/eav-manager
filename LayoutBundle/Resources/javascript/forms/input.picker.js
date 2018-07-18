!function ($) {
    "use strict"; // jshint ;_;

    /**
     * Listens to successful edition or creation when the target has an input-id set.
     * This is part of the "create" button on the data selector widgets
     */
    $(document).on('clever_admindata', function (e) {
        if (-1 === ['create', 'edit'].indexOf(e.detail.action)) {
            return;
        }
        var $tg = $(e.target);
        if (!$tg.data('input-id') || !e.detail.success) {
            return;
        }

        var $input = $('#' + $tg.data('input-id'));
        if (0 === $input.length) {
            return;
        }
        if ($input.is('select')) {
            var $options = $input.find('option[value="' + e.detail.dataId + '"]');
            var $option;
            if (0 === $options.length) {
                $option = $('<option>', {value: e.detail.dataId});
                $input.append($option);
            } else {
                $option = $options.first();
            }
            $option.text(e.detail.dataLabel);
        }
        if ($input.is(':input')) {
            $input.val(e.detail.dataId);
        } else {
            return;
        }
        $input.trigger('change');

        if ($tg.hasClass('modal')) {
            $tg.html('').modal('hide');
        }
    });
}(jQuery);
