!function ($) {
    "use strict"; // jshint ;_;

    function addPlaceholder($col) {
        var $newElement = $col.find('[data-autoload]').filter(function () {
            return $(this).data('autoload') === '__PLACEHOLDER__';
        });
        var $add = $col.closest('.embed-multi-family').find('a[data-addfield]').first().hide();
        if ($newElement.length === 0) {
            $add.trigger('click');
        }
        if ($newElement.length > 1) {
            $newElement.each(function(i) {
                if (i > 0) {
                    $(this).closest('li').remove(); // Remove extra placeholders
                }
            });
        }
    }

    function initEmbedMultiFamily(target) {
        var $tg = $(target);

        // Check if the collection has an empty item, if not trigger the creation of a new one
        if ($tg.is('.embed-multi-family-item')) {
            var $col = $tg.closest('.bootstrap-collection');
            addPlaceholder($col);
        }

        // Find "add" button, hide it and trigger id to add a new empty item
        $tg.find('.embed-multi-family').each(function () {
            var $t = $(this);
            var $col = $t.find('.bootstrap-collection').first();
            addPlaceholder($col);
        });
    }

    $(document).on('global.event', function (e) {
        initEmbedMultiFamily(e.target);
    });
}(window.jQuery);
