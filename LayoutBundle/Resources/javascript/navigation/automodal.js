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

    function resolveCurrentModal() {
        var t = $(this);
        if (t.data('target-element') === '_CURRENT_MODAL') {
            var currentModal = t.closest('.modal');
            if (currentModal.length && currentModal.attr('id')) {
                t.data('target-element', '#' + currentModal.attr('id'));
            }
        }
    }

    function fixMultiModal() {
        if ($('.modal:visible').length) {
            $(document.body).addClass('modal-open');
        }
    }

    $(document).on('click', '[data-target-element]:not(form)', resolveCurrentModal);
    $(document).on('submit', 'form[data-target-element]', resolveCurrentModal);
    $(document).on('hidden.bs.modal', fixMultiModal);

}(window.jQuery);
