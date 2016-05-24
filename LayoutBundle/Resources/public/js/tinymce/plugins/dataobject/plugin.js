tinymce.PluginManager.add('dataobject', function (editor) {
    var utilities = {
        isDataobject: function (node) {
            return node.tagName == 'A' && editor.dom.getAttrib(node, 'data-object-id');
        },
        createObject: function (dataId, family) {
            var node = editor.selection.getNode();
            if (this.isDataobject(node)) {
                $(node)
                    .attr('data-object-id', dataId);
            } else {
                // editor.selection.getContent()
                var imgSrc = '/bundles/cleverageeavmanagerlayout/img/dataobject.png';
                editor.insertContent('<div class="dataobject" data-family="' + family + '" data-object-id="' + dataId + '"><img src="' + imgSrc + '" /></div>');
            }
        }
    };

    function showDialog() {
        var selectedNode = editor.selection.getNode(), dataId = null;

        if (utilities.isDataobject(selectedNode)) {
            dataId = editor.dom.getAttrib(selectedNode, 'data-object-id');
        }

        editor.windowManager.open({
            title: 'Sélection d\'un objet',
            url: Routing.generate('eavmanager_admin.wysiwyg.data_selector.object', {
                dataId: dataId
            }),
            width: 500,
            height: 300
        });
    }

    editor.addCommand('mceDataobject', showDialog);

    editor.addButton('dataobject', {
        icon: 'anchor',
        text: 'Dataobject',
        tooltip: 'Insérer un objet',
        onclick: showDialog,
        stateSelector: 'div[data-object-id]'
    });

    editor.addMenuItem('dataobject', {
        icon: 'anchor',
        text: 'Dataobject',
        context: 'insert',
        onclick: showDialog
    });

    return utilities;
});
