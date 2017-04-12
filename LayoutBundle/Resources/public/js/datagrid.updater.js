!function ($) {
    "use strict"; // jshint ;_;

    /**
     * Listens to edition, creation or deletion events to update concerned datagrids
     */
    $(document).on('create_admindata edit_admindata delete_admindata', function (e) {
        var formRef = 'form[data-admin-code="'+e.detail.admin+'"]';
        var $formRef = $(formRef);
        if (e.detail.success) {
            $formRef.trigger('submit');
        } else {
            var rowRef = '.datagrid-row[data-entity-id="'+e.detail.dataId+'"]';
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
