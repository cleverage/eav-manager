!function ($) {
    "use strict"; // jshint ;_;

    /**
     * Listens to successful edition or creation when the target has an input-id set.
     * This is part of the "create" button on the data selector widgets
     */
    $(document).on('edit_admindata create_admindata', function (e) {
        var $tg = $(e.target);
        if (!$tg.data('input-id') || !e.detail.success) {
            return;
        }

        var $input = $('#'+$tg.data('input-id'));
        if ($input.length) {
            if ($input.is('select') && 0 == $input.find('option[value="' + e.detail.dataId + '"]').length) {
                $input.append($('<option>', {value: e.detail.dataId}).text(e.detail.dataLabel));
            }
            $input.val(e.detail.dataId);
        }
        if ($tg.hasClass('modal')) {
            $tg.html('').modal('hide');
        }
    });
}(jQuery);
