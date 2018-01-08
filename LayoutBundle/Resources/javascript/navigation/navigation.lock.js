"use strict"; // jshint ;_;

/**
 * Lock browser navigation with an confirmation message
 * Optional target to allow ajax navigation on other targets
 * @param message
 * @param target
 */
function lockNavigation(message, target) {
    document.__lockedTarget = target;
    window.onbeforeunload = function (e) {
        if (e.target &&
            document.__lockedTarget &&
            !$(e.target).is(document) &&
            !$(e.target).is(document.__lockedTarget)
        ) {
            return false;
        }
        if ($(e.target).is('.no-navigation') || $(e.target).is('.modal')) {
            return false;
        }

        return message;
    };
}

/**
 * Disable navigation lock
 */
function unLockNavigation() {
    document.__lockedTarget = null;
    window.onbeforeunload = null;
}

/**
 * When any input in the form is changed, lock navigation with given message
 * Optional target to allow ajax navigation on other targets
 * @param form
 * @param message
 * @param target
 */
function lockNavigationOnChange(form, message, target) {
    $(form).one('change keyup', ':input', function () {
        lockNavigation(message, target);
    });
    $(form).on('submit', function () {
        unLockNavigation();
    });
}

/**
 * Trigger changes on editor's target element
 * @param editor
 */
function callback_tinymce_init(editor) {
    editor.on('change', function () {
        $(editor.targetElm).trigger('change');
    });
}

/**
 * Locks navigation if target contains changes
 *
 * @param {Event}   e
 * @param {element} tg
 *
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
