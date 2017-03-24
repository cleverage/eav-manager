!function ($) {
    "use strict"; // jshint ;_;

    /**
     * Listens to successful edition or creation when the target has an input-id set.
     * This is part of the "create" button on the data selector widgets
     */
    $(document).on('edit.admindata create.admindata', function (e) {
        var $tg = $(e.target);
        if (!$tg.data('input-id') || !e.success) {
            return;
        }

        var $input = $('#'+$tg.data('input-id'));
        if ($input.length) {
            if ($input.is('select') && 0 == $input.find('option[value="' + e.dataId + '"]').length) {
                $input.append($('<option>', {value: e.dataId}).text(e.dataLabel));
            }
            $input.val(e.dataId);
        }
        if ($tg.hasClass('modal')) {
            $tg.html('').modal('hide');
        }
    });
}(jQuery);
