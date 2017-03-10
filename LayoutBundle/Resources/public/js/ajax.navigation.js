/**
 * This function handles events on HTML elements like links or forms.
 * It requires a data-target-element that will define the DOM selector used to fetch the target element.
 * For forms it will use the data-href or the action attribute to compute the endpoint of the remote environment.
 * For links it will use the data-href or the href attribute.
 * For any other element it will use the data-href attribute.
 * You can then listen to various events in order to use the loaded data.
 *
 * before.ajaxloading: Before any action
 * success.ajaxloading: When the result of the request is available
 * fail.ajaxloading: In case of an error during the request
 * complete.ajaxloading: In all cases at the end of the request
 *
 * @param {function} $  jQuery instance
 * @param {element}  el The element from which the events originates
 * @param {Event}    e  The event object that triggered the call
 */
function ajaxLoading($, el, e) {
    if (e.ctrlKey) {
        return; // Prevent ajax loading
    }
    if (el instanceof $) {
        el = el[0];
    }
    var $el = $(el);
    var $targets = $($el.data('target-element'));
    if (0 === $targets.length) {
        return;
    }
    var response;
    var url = $el.data('href'); // Test data-href

    if ($el.is('a') && !url) {
        url = $el.attr('href'); // Fallback to href
    }
    if ($el.is('form') && !url) {
        url = $el.attr('action'); // Fallback to action
    }

    var event = jQuery.Event('before.ajaxloading', {
        relatedTarget: el,
        url: url,
        parentEvent: e
    });

    // Iterate over each $targets
    $targets.each(function (index, target) {
        event.index = index;
        event.target = target;
        event.currentTarget = target;
        $(target).trigger(event);
    });

    if (event.isPropagationStopped()) {
        return;
    }

    if (e) {
        e.preventDefault();
        e.stopPropagation();
    }

    // This means this links is not meant to be loaded in ajax
    if (event.url.substr(0, 1) == '#') {
        return;
    }

    if ($el.is('form')) {
        var method = $el.attr('method');
        response = $.ajax({
            method: method ? method : 'POST',
            data: $el.serialize(),
            url: event.url
        });
    } else {
        response = $.ajax({
            method: 'GET',
            url: event.url
        });
    }

    response.done(function (content) {
        event.type = 'success';
        event.content = content;
        try {
            $targets.each(function (index, target) {
                event.index = index;
                event.target = target;
                event.currentTarget = target;
                $(target).trigger(event);
            });
        } catch (error) {
            if ($el.is('a')) {
                // Redirect to real URL on uncaught exceptions for link elements
                window.location.href = event.url;
            }
        }

        if (event.isPropagationStopped()) {
            return;
        }
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
    });

    response.fail(function (error) {
        event.type = 'fail';
        event.error = error;
        try {
            $targets.each(function (index, target) {
                event.index = index;
                event.target = target;
                event.currentTarget = target;
                $(target).trigger(event);
            });
        } catch (newError) {
            if ($el.is('a')) {
                // Redirect to real URL on uncaught exceptions for link elements
                window.location.href = event.url;
            }
        }

        if (event.isPropagationStopped()) {
            return;
        }

        if ($el.is('a')) {
            // Redirect to real URL on uncaught exceptions for link elements
            window.location.href = event.url;
        }
    });

    response.always(function () {
        event.type = 'complete';
        // No error catching in this case because it's over
        $targets.each(function (index, target) {
            event.index = index;
            event.target = target;
            event.currentTarget = target;
            $(target).trigger(event);
        });
    });
}

window.onpopstate = function () {
    window.location.replace(window.location.href);
};

!function ($) {
    "use strict"; // jshint ;_;

    $(document).on('click', '[data-target-element]:not(form)', function (e) {
        ajaxLoading($, $(this), e);
    });

    $(document).on('submit', 'form[data-target-element]', function (e) {
        ajaxLoading($, $(this), e);
    });

}(window.jQuery);


// TO SEPARATE IN A DIFFERENT FILE !!!!

!function ($) {
    "use strict"; // jshint ;_;

    /**
     * Displays a loading mask on top of the target
     */
    $(document).on('before.ajaxloading', '.with-loader', function (e) {
        $(e.target).prepend($('<div class="tg-loading">&nbsp;</div>'));
    });

    /**
     * Locks navigation if target contains changes
     */
    $(document).on('before.ajaxloading', function (e) {
        checkOnBeforeLoad(e, e.target);
    });

    /**
     * Append target parameter to URL
     */
    $(document).on('before.ajaxloading', function (e) {
        var targetId = $(e.target).attr('id');
        if (!targetId) {
            return;
        }
        if (e.url.search('\\?') === -1) {
            e.url += '?target=%23' + targetId;
        } else {
            e.url += '&target=%23' + targetId;
        }
    });

    /**
     * Appends a hidden input with value corresponding to the clicked button (or input)
     */
    $(document).on('before.ajaxloading', function (e) {
        var $el = $(e.relatedTarget);
        if (!$el.is('form')) {
            return;
        }
        // Fixes jQuery default behavior when serializing form without sending data about the clicked button
        if (document.activeElement) {
            var a = $(document.activeElement);
            if (a.attr('name') && a.is('input[type="button"],input[type="submit"],button')) {
                $el.append($('<input type="hidden">')
                    .attr('name', a.attr('name'))
                    .val(a.val() ? a.val() : 1));
            }
        }
    });

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
        if (document.activeDataGridRowRef) {
            $(document.activeDataGridRowRef).removeClass('info');
            document.activeDataGridRowRef = null;
        }
        history.pushState({}, '@todo find a way to fetch the title of the page', $el.attr('href'));
        e.preventDefault();
        e.stopPropagation();
    });

    $(document).on('success.ajaxloading', '.autoload', function(e) {
        $(e.target).html(e.content);
    });

    $(document).on('complete.ajaxloading', '.autoload', function(e) {
        history.pushState({}, '@todo find a way to fetch the title of the page', e.url);
    });


    /**
     * Expand target
     */
    $(document).on('click', '.expand-target', function (e) {
        var t = $(this);
        if (t.data('target-selector')) {
            $(document.body).toggleClass(t.data('target-selector') + '-expanded');
        }
        e.preventDefault();
        e.stopPropagation();
    });

    /**
     * Special case for pagination: we don't want to inject data-target for each link
     * @todo fix me
     */
    $(document).on('click', '#tg_center ul.pagination a, #tg_modal ul.pagination a', function (e) {
        var t = $(this);
        if (t.data('target')) {
            return;
        }
        var target = '#' + t.parents('#tg_center, #tg_modal').first().attr('id');
        t.attr('data-target-element', target);
        e.preventDefault();
        e.stopPropagation();
        t.trigger('click');
    });

}(window.jQuery);
