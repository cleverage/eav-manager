!function ($) {
    "use strict"; // jshint ;_;

    /**
     * Locks navigation if target contains changes
     */
    $(document).on('before.ajaxloading', function (e) {
        checkOnBeforeLoad(e, e.target);
    });

    /**
     * Append target parameter to URL
     * &
     * Appends a hidden input with value corresponding to the clicked button (or input)
     */
    $(document).on('before.ajaxloading', function (e) {
        var $tg = $(e.target);
        var $el = $(e.relatedTarget);

        // Automatically append the target id to the link
        var targetId = $tg.attr('id');
        if (targetId) {
            if (e.url.search('\\?') === -1) {
                e.url += '?target=%23' + targetId;
            } else {
                e.url += '&target=%23' + targetId;
            }
        }

        // Automatically set the modal parameter if target is a modal
        if ($tg.hasClass('modal')) {
            if (e.url.search('\\?') === -1) {
                e.url += '?modal=1';
            } else {
                e.url += '&modal=1';
            }
            e.redirectFallback = false;
        }

        // If the element that triggered the action is a form and a submit button was clicked
        if ($el.is('form') && document.activeElement) {
            // Fixes jQuery default behavior when serializing form without sending data about the clicked button
            var a = $(document.activeElement);
            if (a.attr('name') && a.is('input[type="button"],input[type="submit"],button')) {
                $el.append($('<input type="hidden">')
                    .attr('name', a.attr('name'))
                    .val(a.val() ? a.val() : 1));
            }
        }

        // If the element that triggered the action has an data-input-id, copy it on the target element
        if ($el.data('input-id')) {
            $tg.data('input-id', $el.data('input-id'));
        }
        // Same for medias (although the "picking" logic is a little bit different)
        if ($el.data('media-input-id')) {
            $tg.data('media-input-id', $el.data('media-input-id'));
        }
    });

    /**
     * Displays a loading mask on top of the target
     */
    $(document).on('before.ajaxloading', '.with-loader', function (e) {
        if (e.target !== this) { // Prevent error bubbling
            return;
        }
        $(e.target).prepend($('<div class="tg-loading">&nbsp;</div>'));
    });

    /**
     * Popup modal if target is a modal
     */
    $(document).on('before.ajaxloading', '.modal', function (e) {
        if (e.target !== this) { // Prevent error bubbling
            return;
        }
        $(e.target).modal('show');
    });

    /**
     * Loads the actual HTML response in the target div, only for autoload targets
     */
    $(document).on('success.ajaxloading', '.autoload', function(e) {
        if (e.target !== this) { // Prevent error bubbling
            return;
        }
        $(e.target).html(e.content);
    });

    /**
     * Pushes the url of the clicked element inside the history stack if the target is in autoload and NOT a modal
     */
    $(document).on('complete.ajaxloading', '.autoload:not(.modal)', function(e) {
        if (e.target !== this) { // Prevent error bubbling
            return;
        }

        // Don't push new state if it's the same URL
        if (window.location.href.search(e.url.replace(/([()[{*+.$^\\|?])/g, '\\$1')) != -1) {
            return;
        }

        var $tg = $(e.target);
        var state = {
            previousState: history.state,
            previousTitle: document.title,
            previousUrl: window.location.href
        };
        history.pushState(state, $tg.find('h2.ajax-title').text(), e.url);
    });

    /*
     * Events not directly related to ajax navigation
     */

    /**
     * Appends a hidden input with value corresponding to the clicked button (or input)
     */
    $(document).on('click', '[data-close-target]', function (e) {
        var $el = $(this);
        var $tg = $($el.data('close-target'));
        if (0 === $tg.length) {
            return;
        }
        $tg.html('');
        if ($tg.hasClass('modal')) {
            $tg.modal('hide');
        } else {
            var previousState = {};
            var previousTitle = document.title;
            var previousUrl = $el.attr('href');
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
        e.preventDefault();
        e.stopPropagation();
    });

    // /**
    //  * Special case for pagination: we don't want to inject data-target for each link
    //  * @todo fix me
    //  */
    // $(document).on('click', '#tg_center ul.pagination a, #tg_modal ul.pagination a', function (e) {
    //     var t = $(this);
    //     if (t.data('target')) {
    //         return;
    //     }
    //     var target = '#' + t.parents('#tg_center, #tg_modal').first().attr('id');
    //     t.attr('data-target-element', target);
    //     e.preventDefault();
    //     e.stopPropagation();
    //     t.trigger('click');
    // });

}(window.jQuery);
