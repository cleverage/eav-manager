!function ($) {
    "use strict"; // jshint ;_;

    /**
     * Listens to edition, creation or deletion events to update concerned datagrids
     */
    $(document).on('create.admindata edit.admindata delete.admindata', function (e) {
        var formRef = 'form[data-admin-code="'+e.admin+'"]';
        var $formRef = $(formRef);
        if (e.success) {
            $formRef.trigger('submit');
        } else {
            var rowRef = '.datagrid-row[data-entity-id="'+e.dataId+'"]';
            var $row = $(rowRef);
            if (document.activeDataGridRowRef) {
                $(document.activeDataGridRowRef).removeClass('info');
            }
            $row.addClass('info');
            document.activeDataGridRowRef = rowRef;
        }
    });

    /**
     * This is a real simplified trick to unselect the active datagrid row, this wont work in many cases where multiple
     * targets are loaded in the same page
     */
    $(document).on('click', '[data-close-target]', function (e) {
        if (document.activeDataGridRowRef) {
            $(document.activeDataGridRowRef).removeClass('info');
            document.activeDataGridRowRef = null;
        }
    });
}(jQuery);
