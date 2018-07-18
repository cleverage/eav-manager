"use strict"; // jshint ;_;

!function ($) {
    "use strict"; // jshint ;_;

    /**
     * Fetch media widget associated to target
     *
     * @param target
     *
     * @returns {*}
     */
    function getMediaWidget(target) {
        return target.parents('.media-browser').first();
    }

    /**
     * Select media and append preview to widget
     *
     * @param target
     * @param mediaId
     * @param imageHtml
     */
    function selectMedia(target, mediaId, imageHtml) {
        var b = getMediaWidget(target);
        b.find('input[type="hidden"]').val(mediaId);
        b.find('.media-preview').html(imageHtml);
    }

    /**
     * Detach media from form input
     *
     * @param target
     */
    function detachMedia(target) {
        var b = getMediaWidget(target);
        b.find('input[type="hidden"]').val('');
        b.find('.media-preview').html('');
    }

    /**
     * Detach media in widget
     */
    $(document).on('click', '.media-detach', function (e) {
        detachMedia($(this));

        e.preventDefault();
        e.stopPropagation();
    });

    /**
     * Select a media in widget
     */
    $(document).on('click', '.media-select', function (e) {
        var t = $(this);
        var modal = t.closest('.modal');
        var inputId = modal.data('media-input-id');
        var mediaId = t.data('media-id');
        var imageHtml = t.data('media-preview');

        selectMedia($('#' + inputId), mediaId, imageHtml);
        modal.html('').modal('hide');

        e.preventDefault();
        e.stopPropagation();
    });
}(window.jQuery);
