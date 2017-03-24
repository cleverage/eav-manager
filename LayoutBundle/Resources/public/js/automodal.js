!function ($) {
    "use strict"; // jshint ;_;

    $(document).on('click', 'a[data-auto-modal]', function () {
        var target = $(this).data('auto-modal');
        if ($('#' + target).length) {
            return;
        }
        var modal = $('<div/>', {
            'class': 'modal fade with-loader autoload',
            'id': target,
            'role': 'dialog'
        });
        $(document.body).append(modal);
    });

    function resolveCurrentModal(e) {
        var t = $(this);
        if (t.data('target-element') == '_CURRENT_MODAL') {
            var currentModal = t.closest('.modal');
            if (currentModal.length && currentModal.attr('id')) {
                t.data('target-element', '#' + currentModal.attr('id'));
            }
        }
    }

    $(document).on('click', '[data-target-element]:not(form)', resolveCurrentModal);
    $(document).on('submit', 'form[data-target-element]', resolveCurrentModal);

}(window.jQuery);
