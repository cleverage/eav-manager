"use strict"; // jshint ;_;

/**
 * Bind global events on a given element, used by ajax navigation and bootstrap collection
 * @param target
 */
function bindGlobalEvents(target) {
    // Autocomplete
    $(target).find('.select2').each(function () {
        var $t = $(this);
        if ($t.data('query-uri')) {
            $t.select2({
                ajax: {
                    delay: 250,
                    url: $t.data('query-uri'),
                    processResults: function (data) {
                        return data;
                    }
                }
            });
        } else {
            $t.select2();
        }
    });

    // DatePickers
    initDatePickers(target);

    // Sortable collections
    sortableCollections(target);

    if (target != document) {
        // TinyMCE
        initTinyMCE();

        // Sidus combo data selector (family + autocomplete)
        initComboSelector(target);
    }

    // File upload widget
    $(target).find('.fileupload-widget').each(function () {
        $(this).sidusFileUpload();
    });

    // Tooltips
    $(target).find('[data-toggle="tooltip"]').tooltip();

    // Auto select all / none : @todo refactor me
    $(target).find('button[data-select-all]').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var formName = $(this).data('select-all');
        $('input[type=checkbox][name^="'+formName+'"]').prop('checked', true);
    });
    $(target).find('button[data-select-none]').on('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        var formName = $(this).data('select-none');
        $('input[type=checkbox][name^="'+formName+'"]').prop('checked', false);
    });
}

!function ($) {
    "use strict"; // jshint ;_;

    /**
     * Binds all required events when loading ajax content
     */
    $(document).on('complete.ajaxloading', '.autoload', function (e) {
        if (e.target !== this) {
            return;
        }
        bindGlobalEvents(e.target);
    });

    /**
     * Binds all required events when adding an element to a bootstrap collection
     */
    $(document).on('collection.item.added', function (e, o) {
        bindGlobalEvents(o);
    });

    /**
     * Binds all events to the document on load
     */
    $(document).ready(function () {
        bindGlobalEvents(document);
    });

}(window.jQuery);
