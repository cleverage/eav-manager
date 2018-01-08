!function ($) {
    "use strict"; // jshint ;_;

    function closeTarget($tg, previousUrl) {
        $tg.html('');
        if ($tg.hasClass('modal')) {
            $tg.modal('hide');
        } else {
            var previousState = {};
            var previousTitle = document.title;
            if (history.state.previousState) {
                previousState = history.state.previousState;
            }
            if (history.state.previousTitle) {
                previousTitle = history.state.previousTitle;
            }
            if (history.state.previousUrl) {
                previousTitle = history.state.previousUrl;
            }
            history.replaceState(previousState, previousTitle, previousUrl);
        }

        if ($tg.attr('id') == 'tg_right') {
            $(document.body).removeClass('tg-right-expanded');
        }
    }

    /**
     * Close the target when clicking on the close button
     */
    $(document).on('click', '[data-close-target]', function (e) {
        var $el = $(this);
        var $tg = $($el.data('close-target'));
        if (0 === $tg.length) {
            return;
        }

        closeTarget($tg, $el.attr('href'));

        e.preventDefault();
        e.stopPropagation();
    });

    /**
     * Close the target after deletion of an entity + resetPassword
     */
    $(document).on('clever_admindata', function (e) {
        if (!e.detail.success || -1 === ['delete', 'resetPassword'].indexOf(e.detail.action)) {
            return;
        }
        var $tg = $(e.target);
        if (0 === $tg.length) {
            return;
        }
        closeTarget($tg);
    });
}(jQuery);
