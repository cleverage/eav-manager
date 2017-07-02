!function ($) {
    "use strict"; // jshint ;_;

    function initEmbedMultiFamily(target) {
        var $tg = $(target);

        // Check if the collection has an empty item, if not trigger the creation of a new one
        if ($tg.is('.embed-multi-family-item')) {
            var $col = $tg.closest('.bootstrap-collection');
            var $newElement = $col.find('[data-autoload]').filter(function () {
                return $(this).data('autoload') == '__PLACEHOLDER__';
            });
            if ($newElement.length > 0) {
                return;
            }
            var $add = $col.closest('.embed-multi-family').find('a[data-addfield]').first();
            $add.trigger('click');
        }

        // Find "add" button, hide it and trigger id to add a new empty item
        $tg.find('.embed-multi-family').each(function () {
            var $t = $(this);
            var $add = $t.find('a[data-addfield]').first();
            $add.hide().trigger('click');
        });
    }

    $(document).on('global.event', function (e) {
        initEmbedMultiFamily(e.target);
    });
}(window.jQuery);
