/**
 * Handles all navigation events, loads data based on target class, display loader, etc.
 */
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
        if (e.target !== this) { // Prevent event bubbling
            return;
        }
        var $tg = $(e.target);
        if ($tg.attr('id') === 'tg_right') {
            $(document.body).addClass('tg-right-expanded');
        }
        $tg.prepend($('<div class="tg-loading">&nbsp;</div>'));
    });

    /**
     * Popup modal if target is a modal
     */
    $(document).on('before.ajaxloading', '.modal', function (e) {
        if (e.target !== this) { // Prevent event bubbling
            return;
        }
        $(e.target).modal({
            backdrop: 'static',
            keyboard: false
        });
    });

    /**
     * Loads the actual HTML response in the target div, only for autoload targets
     */
    $(document).on('success.ajaxloading', '.autoload', function (e) {
        if (e.target !== this) { // Prevent event bubbling
            return;
        }
        $(e.target).html(e.content);
    });

    /**
     * Loads the actual HTML response in the target div, only for autoload targets
     */
    $(document).on('fail.ajaxloading', '.autoload', function (e) {
        if (e.target !== this) { // Prevent event bubbling
            return;
        }
        $(e.target).html($('#error-template').html());
    });

    /**
     * Pushes the url of the clicked element inside the history stack if the target is in autoload and NOT a modal
     */
    $(document).on('complete.ajaxloading', '.autoload', function (e) {
        if (e.target !== this) { // Prevent event bubbling
            return;
        }
        var $tg = $(this);
        if ($tg.is('.modal') || $tg.is('.no-navigation')) {
            return;
        }

        function removeParam(key, sourceURL) {
            var rtn = sourceURL.split("?")[0],
                param,
                params_arr = [],
                queryString = (sourceURL.indexOf("?") !== -1) ? sourceURL.split("?")[1] : "";
            if (queryString !== "") {
                params_arr = queryString.split("&");
                for (var i = params_arr.length - 1; i >= 0; i -= 1) {
                    param = params_arr[i].split("=")[0];
                    if (param === key) {
                        params_arr.splice(i, 1);
                    }
                }
                rtn = rtn + "?" + params_arr.join("&");
            }
            return rtn;
        }

        var url = removeParam('target', e.url).replace(/\?$/, '');

        // Don't push new state if it's the same URL
        if (window.location.href.search(url.replace(/([()[{*+.$^\\|?])/g, '\\$1')) !== -1) {
            return;
        }

        var state = {
            previousState: history.state,
            previousTitle: document.title,
            previousUrl: window.location.href
        };
        history.pushState(state, $tg.find('h2.ajax-title').text(), url);
    });
}(window.jQuery);
