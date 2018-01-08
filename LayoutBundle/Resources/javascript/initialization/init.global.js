/**
 * One Event for the loading of Ajax content
 * One when adding an item in a Bootstrap collection
 * One for the first initialization of the document
 * One for the global event, in the EAV Manager
 * One Event to rule them all, One Event to find them,
 * One Event to bring them all, and in the javascript bind them
 */
!function ($) {
    "use strict"; // jshint ;_;

    /**
     * Binds all required events when loading ajax content
     */
    $(document).on('complete.ajaxloading', '.autoload', function (e) {
        if (e.target !== this) {
            return;
        }
        var event = $.Event('global.event', {
            relatedTarget: e.relatedTarget,
            parentEvent: e,
            target: e.target,
            currentTarget: e.target
        });

        $(e.target).trigger(event);
    });

    /**
     * Binds all required events when adding an element to a bootstrap collection
     */
    $(document).on('collection.item.added', function (e, o) {
        var event = $.Event('global.event', {
            parentEvent: e,
            target: o,
            currentTarget: o
        });

        $(o).trigger(event);
    });

    /**
     * Binds all events to the document on load
     */
    $(document).ready(function (e) {
        var event = $.Event('global.event', {
            parentEvent: e,
            target: document,
            currentTarget: document
        });

        $(document).trigger(event);
    });
}(window.jQuery);
