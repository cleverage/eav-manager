tinymce.PluginManager.add('datalink', function (editor) {
    var utilities = {
        isDatalink: function (node) {
            return node.tagName === 'A' && editor.dom.getAttrib(node, 'data-link-id');
        },
        createLink: function (dataId) {
            var node = editor.selection.getNode();
            if (this.isDatalink(node)) {
                $(node).attr('data-link-id', dataId);
            } else {
                editor.editorCommands.execCommand('unlink');
                editor.insertContent('<a href="#data_' + dataId + '" data-link-id="' + dataId + '">' +
                    editor.selection.getContent() +
                    '</a>');
            }
        }
    };

    function showDialog() {
        var selectedNode = editor.selection.getNode(), dataId = null;

        if (utilities.isDatalink(selectedNode)) {
            dataId = editor.dom.getAttrib(selectedNode, 'data-link-id');
        }

        editor.windowManager.open({
            title: 'Sélection d\'une donnée',
            url: Routing.generate('eavmanager_admin.wysiwyg.data_selector', {
                configName: 'datalink',
                dataId: dataId
            }),
            width: 500,
            height: 300
        });
    }

    editor.addCommand('mceDatalink', showDialog);

    editor.addButton('datalink', {
        icon: 'anchor',
        text: 'Datalink',
        tooltip: 'Datalink',
        onclick: showDialog,
        stateSelector: 'a[data-link-id]'
    });

    editor.addMenuItem('datalink', {
        icon: 'anchor',
        text: 'Datalink',
        context: 'insert',
        onclick: showDialog
    });

    return utilities;
});
