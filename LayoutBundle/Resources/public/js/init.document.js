"use strict"; // jshint ;_;

/**
 * Bind global events on a given element, used by ajax navigation and bootstrap collection
 * @param target
 */
function bindGlobalEvents(target) {
    // Autocomplete
    $(target).find('.select2').each(function () {
        $(this).samsonSelect2();
    });

    // DatePickers
    initDatePickers(target);

    // Sortable collections
    sortableCollections(target);

    if (target != document) {
        // TinyMCE
        initTinyMCE();

        // Sidus combo data selector (family + autocomplete)
        initComboSelector(target);
    }

    // File upload widget
    $(target).find('.fileupload-widget').each(function () {
        $(this).sidusFileUpload();
    });
}

!function ($) {
    "use strict"; // jshint ;_;

    /**
     * Binds all required events when adding an element to a bootstrap collection
     */
    $(document).on('collection.item.added', function (e, o) {
        bindGlobalEvents(o);
    });

    /**
     * Binds all events to the document on load
     */
    $(document).ready(function () {
        bindGlobalEvents(document);
    });

}(window.jQuery);
