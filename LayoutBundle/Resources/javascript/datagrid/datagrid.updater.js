!function ($) {
    "use strict"; // jshint ;_;

    /**
     * Listens to edition, creation or deletion events to update concerned datagrids
     */
    $(document).on('clever_admindata', function (e) {
        if (e.detail.success) {
            var formRef = 'form[data-admin-code="' + e.detail.admin + '"]';
            var $formRef = $(formRef);
            if ($formRef.length && e.detail.success) {
                $formRef.trigger('submit');
            } else {
                var $row = $('[data-admin-code="' + e.detail.admin + '"][data-is-target]');
                $row.each(function () {
                    var $t = $(this);
                    if (!$t.data('href')) {
                        return;
                    }
                    ajaxLoading($, $t[0], new Event('autoload'));
                });
            }
        }

        var rowRef = '.datagrid-row[data-entity-id="' + e.detail.dataId + '"]';
        var $row = $(rowRef);
        if (document.activeDataGridRowRef) {
            $(document.activeDataGridRowRef).removeClass('info');
        }
        $row.addClass('info');
        document.activeDataGridRowRef = rowRef;
    });

    /**
     * This is a real simplified trick to unselect the active datagrid row, this wont work in many cases where multiple
     * targets are loaded in the same page
     */
    $(document).on('click', '[data-close-target]', function () {
        if (document.activeDataGridRowRef) {
            $(document.activeDataGridRowRef).removeClass('info');
            document.activeDataGridRowRef = null;
        }
    });
}(jQuery);
