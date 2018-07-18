!function ($) {
    "use strict"; // jshint ;_;

    function initAutoload(target) {
        $(target).find('[data-autoload]').each(function () {
            var $t = $(this);
            if ($t.data('autoload') === '__PLACEHOLDER__') {
                return;
            }
            ajaxLoading($, this, new Event('autoload'));
        });
    }

    $(document).on('global.event', function (e) {
        initAutoload(e.target);
    });

    /**
     * Listens to
     */
    $(document).on('clever_admindata', function (e) {
        if (-1 === ['create', 'edit', 'delete'].indexOf(e.detail.action) || !e.detail.success) {
            return;
        }
        var $tg = $(e.target);
        if (!$tg.data('input-id')) {
            return;
        }

        var $input = $('#' + $tg.data('input-id'));
        if (0 === $input.length) {
            return;
        }
        
        
        var $autoloadTarget = $('#' + $tg.data('input-id') + '_embed_target');
        if (0 === $autoloadTarget.length) {
            return;
        }

        if (e.detail.action === 'delete') {
            $autoloadTarget.closest('li').remove();

            return;
        }

        $autoloadTarget.each(function () {
            var $t = $(this);
            if ($t.data('autoload') === '__PLACEHOLDER__') {
                $t.data('href', $t.data('autoload-url').replace('__ID__', e.detail.dataId));
                $t.data('autoload', '1');
            }
            ajaxLoading($, this, new Event('autoload'));
        });
    });
}(window.jQuery);
