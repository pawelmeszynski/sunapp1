/**
 * @license Copyright (c) 2003-2019, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see https://ckeditor.com/legal/ckeditor-oss-license
 */

CKEDITOR.editorConfig = function( config ) {
    config.allowedContent = true;
    config.removePlugins = 'save';
    config.htmlEncodeOutput = false;
    config.entities = false;
};


CKEDITOR.on("instanceReady", function(event) {
    event.editor.on("beforeCommandExec", function(event) {
        if (event.data.name === "paste") {
            event.editor._.forcePasteDialog = true;
        } else if (event.data.name === "pastetext" && event.data.commandData.from === "keystrokeHandler") {
            event.cancel();
        }
    });

    if (event.editor.element.$.dataset && event.editor.element.$.dataset.source_only) {
        event.editor.execCommand( 'source' );
        setTimeout(function() {
            event.editor.getCommand( 'source' ).disable();
            event.editor.getCommand( 'preview' ).disable();
        }, 300);
    }
});

CKEDITOR.on('dialogDefinition', function (ev) {
    var dialogName = ev.data.name;
    var dialogDefinition = ev.data.definition;
    var dialog = ev.data.definition.dialog;
    var editor = ev.editor;

    if (dialogName === 'image') {
        dialogDefinition.onOk = function (e) {
            var image = e.sender.originalElement.$.src;
            var altField = dialog.getContentElement('info', 'txtAlt');
            var alt = altField.getValue();

            var width = dialog.getContentElement('info', 'txtWidth').getValue() || $element.width;
            var height = dialog.getContentElement('info', 'txtHeight').getValue() || $element.height;

            if (alt === 'undefined') {
                altField.setValue('');
            }

            var imgHtml = CKEDITOR.dom.element
                .createFromHtml('<img src="'+ removeDomain(image) +'" alt="'+ alt +'" width="'+width+'" height="'+height+'" />');
            editor.insertElement(imgHtml);
        };
    }
});

CKEDITOR.dtd['a']['div'] = 1;
CKEDITOR.dtd['a']['p'] = 1;
CKEDITOR.dtd['a']['h1'] = 1;
CKEDITOR.dtd['a']['h2'] = 1;
CKEDITOR.dtd['a']['h3'] = 1;
CKEDITOR.dtd['a']['h4'] = 1;
CKEDITOR.dtd['a']['ul'] = 1;
CKEDITOR.dtd['a']['ol'] = 1;

function removeDomain(url) {
    var domain = document.querySelector('meta[name="base-url"]');

    if (domain && url.indexOf(domain.content) > -1) {
        return url.replace(domain.content, '');
    }
    return url;
}
