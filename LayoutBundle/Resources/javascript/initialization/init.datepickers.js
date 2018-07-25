!function ($) {
    "use strict"; // jshint ;_;

    $(document).on('global.event', function (e) {
        initDatePickers(e.target);
    });
}(window.jQuery);
