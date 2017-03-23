"use strict"; // jshint ;_;

!function ($) {
    "use strict"; // jshint ;_;

    function getMediaWidget(target) {
        return target.parents('.media-browser').first();
    }

    function selectMedia(target, mediaId, imageHtml) {
        var b = getMediaWidget(target);
        b.find('input[type="hidden"]').val(mediaId);
        b.find('.media-preview').html(imageHtml);
    }

    /**
     * Detach media from form input
     * @param target
     */
    function detachMedia(target) {
        var b = getMediaWidget(target);
        b.find('input[type="hidden"]').val('');
        b.find('.media-preview').html('');
    }

    /**
     * Binds all required events when adding an element to a bootstrap collection
     */
    $(document).on('click', '.media-detach', function (e) {
        detachMedia($(this));

        e.preventDefault();
        e.stopPropagation();
    });

    /**
     * Binds all required events when adding an element to a bootstrap collection
     */
    $(document).on('click', '.media-select', function (e) {
        var t = $(this);
        var inputId = t.data('input-id');
        var mediaId = t.data('media-id');
        var imageHtml = t.data('media-preview');

        selectMedia($('#' + inputId), mediaId, imageHtml);
        t.closest('.modal').html('').modal('hide');

        e.preventDefault();
        e.stopPropagation();
    });
}(window.jQuery);
