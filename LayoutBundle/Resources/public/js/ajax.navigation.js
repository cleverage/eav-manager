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

    if (event.isDefaultPrevented()) {
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

        if (event.isDefaultPrevented()) {
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

        if (event.isDefaultPrevented()) {
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
        if (e.target !== this) { // Prevent error bubbling
            return;
        }
        ajaxLoading($, $(this), e);
    });

    $(document).on('submit', 'form[data-target-element]', function (e) {
        if (e.target !== this) { // Prevent error bubbling
            return;
        }
        ajaxLoading($, $(this), e);
    });

}(window.jQuery);
