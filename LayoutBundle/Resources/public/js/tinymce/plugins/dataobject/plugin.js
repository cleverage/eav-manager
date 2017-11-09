var script = document.currentScript;
var fullUrl = script.src;
var pathArray = fullUrl.split( '/' );
var protocol = pathArray[0];
var host = pathArray[2];
var currentScriptBaseUrl = protocol + '//' + host;

tinymce.PluginManager.add('dataobject', function (editor) {
    var utilities = {
        isDataobject: function (node) {
            return node.tagName == 'IMG' && editor.dom.getAttrib(node, 'data-object-id');
        },
        createObject: function (dataId) {
            var node = editor.selection.getNode();
            if (this.isDataobject(node)) {
                $(node).attr('data-object-id', dataId);
            } else {
                editor.insertContent('<img class="dataobject" data-object-id="' + dataId + '" src="' + currentScriptBaseUrl + '/bundles/cleverageeavmanagerlayout/img/dataobject.png" />');
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
            url: Routing.generate('eavmanager_admin.wysiwyg.data_selector', {
                configName: 'dataobject',
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
        stateSelector: 'img[data-object-id]'
    });

    editor.addMenuItem('dataobject', {
        icon: 'anchor',
        text: 'Dataobject',
        context: 'insert',
        onclick: showDialog
    });

    return utilities;
});
