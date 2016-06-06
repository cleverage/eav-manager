!function ($) {

    "use strict"; // jshint ;_;

    /**
     * Displays a loading mask on top of the target
     */
    function loading(el) {
        el.prepend($('<div class="tg-loading">&nbsp;</div>'));
    }

    /**
     * Lock navigation if target contains changes
     * @param e
     * @param tg
     * @returns {boolean}
     */
    function checkOnBeforeLoad(e, tg) {
        if (typeof window.onbeforeunload == 'function') {
            var val = window.onbeforeunload({target: tg});
            if (val) {
                if (!confirm(val)) {
                    e.preventDefault();
                    e.stopPropagation();
                    return true;
                } else {
                    unLockNavigation();
                }
            }
        }
    }

    /**
     * Compute destination URL, appending target parameter
     * @param t
     * @param attr
     * @returns {string}
     */
    function getUrl(t, attr) {
        var href = t.attr(attr);
        if (!href) {
            return href;
        }
        if (href.search('\\?') === -1) {
            href += '?target=' + t.data('target');
        } else {
            href += '&target=' + t.data('target');
        }
        return href;
    }

    /**
     * Append a hidden input with value corresponding to the clicked button (or input)
     * @param form
     */
    function fixJQuerySubmit(form) {
        // Fixes jQuery default behavior when serializing form without sending data about the clicked button
        if (document.activeElement) {
            var a = $(document.activeElement);
            if (a.attr('name') && a.is('input[type="button"],input[type="submit"],button')) {
                form.append($('<input type="hidden">')
                    .attr('name', a.attr('name'))
                    .val(a.val() ? a.val() : 1));
            }
        }
    }

    /**
     * Add loading mask, check if target is modal, prevent event default
     * @param tg
     * @param e
     * @param action
     */
    function finish(tg, e, action) {
        if (!action) {
            action = 'show';
        }
        if (tg.hasClass('modal')) {
            tg.modal(action);
        } else if (action == 'show') {
            loading(tg);
        }
        e.preventDefault();
        e.stopPropagation();
    }

    /**
     * Handle clicks on links with a data-target and load the url in the target div if it exists.
     * Fallback to standard non-ajax navigation in case of error.
     */
    $(document).on('click', 'a[data-target]', function (e) {
        var t = $(this);
        var tg = $('#' + t.data('target'));
        if (tg.length == 0) {
            return;
        }
        if (checkOnBeforeLoad(e, tg)) {
            return;
        }
        var href = getUrl(t, 'href');
        if (!href) { // Fallback to data-href
            href = getUrl(t, 'data-href');
        }
        if (!href) {
            throw 'Empty href attribute';
        }
        $.ajax(href).done(function (content) {
            tg.html(content);
            bindGlobalEvents(tg);
        }).fail(function (e) {
            tg.html(e.responseText);
        });
        finish(tg, e);
    });

    /**
     * Handle form submission on form with a data-target and submit the form and load the content in the target div if it exists.
     * Fallback to standard non-ajax submission in case of error.
     */
    $(document).on('submit', 'form[data-target]', function (e) {
        var t = $(this);
        var tg = $('#' + t.data('target'));
        if (tg.length == 0) {
            return;
        }
        fixJQuerySubmit(t);
        $.ajax(getUrl(t, 'action'), {
            method: t.attr('method'),
            data: t.serialize()
        }).done(function (content) {
            tg.html(content);
            bindGlobalEvents(tg);
        }).fail(function (e) {
            tg.html(e.responseText);
        });
        finish(tg, e);
    });

    /**
     * Handle closing of targets (only empty the HTML inside)
     */
    $(document).on('click', 'a[data-close-target],button[data-close-target]', function (e) {
        var t = $(this);
        var tg = $('#' + t.data('close-target'));
        if (checkOnBeforeLoad(e, tg)) {
            return;
        }
        if (tg.length == 0) {
            return;
        }
        tg.html('');
        if (document.activeDataGridRowRef) {
            var a = $(document.activeDataGridRowRef);
            a.removeClass('info');
            document.activeDataGridRowRef = null;
        }
        finish(tg, e, 'hide');
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
     */
    $(document).on('click', '#tg_center ul.pagination a, #tg_modal ul.pagination a', function (e) {
        var t = $(this);
        if (t.data('target')) {
            return;
        }
        var target = t.parents('#tg_center, #tg_modal').first().attr('id');
        t.attr('data-target', target);
        e.preventDefault();
        e.stopPropagation();
        t.trigger('click');
    });

}(window.jQuery);
