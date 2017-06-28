!function ($) {
    "use strict"; // jshint ;_;

    function initEditInline(target) {
        $(target).find('a[data-edit-in-place]').each(function () {
            var $link = $(this);
            var href = $link.attr('href');
            var $input = $link.closest('.input-group').find(':input');

            function updateLink(dataId) {
                if (dataId) {
                    $link.removeClass('disabled');
                    $link.attr('href', href.replace('__ID__', dataId));
                } else {
                    $link.addClass('disabled');
                }
            }
            // Trigger once on loading
            updateLink($input.val());

            // Trigger each time the selector is changed
            $input.on('change', function () {
                updateLink($input.val());
            });
        });
    }

    $(document).on('global.event', function (e) {
        initEditInline(e.target);
    });
}(window.jQuery);
