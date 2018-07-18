!function ($) {
    "use strict"; // jshint ;_;

    /**
     * Initialize lightbox with additional download link in header
     */
    $(document).on('click', '[data-toggle="lightbox"]', function(event) {
        event.preventDefault();
        $(this).ekkoLightbox({
            alwaysShowClose: true,
            onShown: function() {
                var a = this.element();
                var title = a.attr('title');
                var downloadUri = a.data('original-uri');
                if (downloadUri) {
                    if (!title) {
                        title = 'Download';
                    }
                    var downloadLink = $('<a target="_blank">')
                        .attr('href', downloadUri)
                        .text(title);
                    this
                        .modal()
                        .find('.modal-title')
                        .html(downloadLink);
                } else if (title) {
                    this
                        .modal()
                        .find('.modal-title')
                        .text(title);
                }
            }
        });
    });
}(window.jQuery);
