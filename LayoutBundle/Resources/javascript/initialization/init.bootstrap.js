!function ($) {
    "use strict"; // jshint ;_;

    /**
     * Bind global events on a given element, used by ajax navigation and bootstrap collection
     */
    $(document).on('global.event', function (e) {
        if (e.target !== document) {
            // Autocomplete
            initAutocompleteSelector($, e.target);
            // Sidus combo data selector (family + autocomplete)
            initComboSelector($, e.target);
        }

        // File upload widget
        $(e.target).find('.fileupload-widget').each(function () {
            $(this).sidusFileUpload();
        });

        // Tooltips
        $(e.target).find('[data-toggle="tooltip"]').tooltip();
    });
}(window.jQuery);
