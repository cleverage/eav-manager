!function ($) {
    "use strict"; // jshint ;_;

    /**
     * Bind global events on a given element, used by ajax navigation and bootstrap collection
     */
    $(document).on('global.event', function (e) {
        // Auto select all / none : @todo refactor me
        $(e.target).find('button[data-select-all]').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var formName = $(this).data('select-all');
            $('input[type=checkbox][name^="' + formName + '"]').prop('checked', true);
        });
        $(e.target).find('button[data-select-none]').on('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var formName = $(this).data('select-none');
            $('input[type=checkbox][name^="' + formName + '"]').prop('checked', false);
        });
    });
}(window.jQuery);
