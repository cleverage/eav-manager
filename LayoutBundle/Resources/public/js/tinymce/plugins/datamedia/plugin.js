tinymce.PluginManager.add('datamedia', function (editor) {
    var utilities = {
        isDatamedia: function (node) {
            return node.tagName === 'IMG' && editor.dom.getAttrib(node, 'data-media-id');
        },
        createMedia: function (dataId, filter, isResponsive) {
            var node = editor.selection.getNode();
            var media = null;
            if (this.isDatamedia(node)) {
                media = $(node);
            } else {
                media = $('<img>');
            }
            var url = Routing.generate('eavmanager_asset.media.url', {
                id: dataId,
                filter: filter
            });
            media.attr('src', url)
                .attr('data-mce-src', url)
                .attr('data-media-id', dataId)
                .attr('data-media-filter', filter);

            if (isResponsive) {
                media.attr('class', 'img-responsive');
            } else {
                media.attr('class', '');
            }

            if (!this.isDatamedia(node)) {
                editor.selection.collapse();
                editor.insertContent($('<div>').append(media).html());
            }
        }
    };

    function showDialog() {
        var selectedNode = editor.selection.getNode(), dataId = null, dataFilter = null, dataResponsive = null;

        if (utilities.isDatamedia(selectedNode)) {
            dataId = editor.dom.getAttrib(selectedNode, 'data-media-id');
            dataFilter = editor.dom.getAttrib(selectedNode, 'data-media-filter');
            if (editor.dom.getAttrib(selectedNode, 'class').indexOf("img-responsive") >= 0) {
                dataResponsive = 1;
            } else {
                dataResponsive = 0;
            }
        }

        editor.windowManager.open({
            title: 'Sélection d\'un média',
            url: Routing.generate('eavmanager_admin.wysiwyg.data_selector.media', {
                dataId: dataId,
                dataFilter: dataFilter,
                dataResponsive: dataResponsive
            }),
            width: 800,
            height: 600
        });
    }

    editor.addCommand('mceDatamedia', showDialog);

    editor.addButton('datamedia', {
        icon: 'image',
        text: 'Datamedia',
        tooltip: 'Insérer une image',
        onclick: showDialog,
        stateSelector: 'img[data-media-id]'
    });

    editor.addMenuItem('datamedia', {
        icon: 'image',
        text: 'Datamedia',
        context: 'insert',
        onclick: showDialog
    });

    return utilities;
});
