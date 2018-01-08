!function ($) {
    "use strict"; // jshint ;_;

    $(document).on('click', 'form[data-target-element] .pagination a:not([data-target-element])', function (e) {
        var $t = $(this);
        var $form = $t.closest('form[data-target-element]');
        // $t.data('target-element', $form.data('target-element'));
        $t.attr('data-target-element', $form.data('target-element'));
        e.preventDefault();
        e.stopPropagation();
        $t.trigger('click');
    });
}(window.jQuery);
