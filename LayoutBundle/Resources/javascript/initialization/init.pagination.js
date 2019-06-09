!function ($) {
    "use strict"; // jshint ;_;

    function updateTargetUrl($target, url) {
        if (!url) {
            return;
        }
        if ($target.data('href')) {
            $target.data('href', url);

            return;
        }
        if ($target.is('form')) {
            $target.attr('action', url);
        }
    }

    $(document).on('click', '*[data-target-element] .pagination a:not([data-target-element])', function (e) {
        var $t = $(this);
        var $target = $t.closest('*[data-target-element]');
        $t.attr('data-target-element', $target.data('target-element'));
        updateTargetUrl($target, resolveUrl($t));
        e.preventDefault();
        e.stopPropagation();
        $t.trigger('click');
    });
}(window.jQuery);
