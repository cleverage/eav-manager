!function ($) {
    "use strict"; // jshint ;_;

    $(document).on('click', 'a[data-auto-modal]', function () {
        var target = $(this).data('auto-modal');
        if ($('#'+target).length) {
            return;
        }
        var modal = $('<div/>', {
            'class': 'modal fade with-loader autoload',
            'id': target,
            'role': 'dialog'
        });
        $(document.body).append(modal);
    });

}(window.jQuery);
