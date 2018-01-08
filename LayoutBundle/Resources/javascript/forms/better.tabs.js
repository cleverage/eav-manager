!function ($) {
    "use strict"; // jshint ;_;

    // store the currently selected tab in the hash value
    $(document).on('shown.bs.tab', function(e) {
        var url = window.location.href.replace(window.location.hash, '') + $(e.target).attr('href');

        var state = {
            previousState: history.state,
            previousTitle: document.title,
            previousUrl: window.location.href
        };
        history.pushState(state, document.title, url);
    });

    /**
     * When loading a page: switch to the previously selected tab
     */
    $(document).ready(function() {
        var hash = window.location.hash;
        $('a[href="' + hash + '"]').tab('show');
    });
}(jQuery);
