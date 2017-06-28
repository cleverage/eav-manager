!function ($) {
    "use strict"; // jshint ;_;

    /**
     * Bind global events on a given element, used by ajax navigation and bootstrap collection
     */
    $(document).on('global.event', function (e) {
        if (e.target != document) {
            // TinyMCE
            initTinyMCE();
        }
    });
}(window.jQuery);
