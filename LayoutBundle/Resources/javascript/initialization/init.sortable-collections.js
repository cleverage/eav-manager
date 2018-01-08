!function ($) {
    "use strict"; // jshint ;_;

    /**
     * Init sortable bootstrap collections
     */
    $(document).on('global.event', function (e) {
        sortableCollections($(e.target));
    });
}(window.jQuery);
