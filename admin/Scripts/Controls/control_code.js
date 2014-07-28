var codeMirrors = [];
$(document).ready(function () {
    $('textarea.codeMirror').each(function () {
        var self = this;
        var name = $(this).attr('id');
        var myCodeMirror = CodeMirror.fromTextArea(this, {
            mode: {name: $(this).attr('type'), alignCDATA: true},
            lineNumbers: true,
            tabSize: 2,
            fixedGutter: true
        });
        codeMirrors[name] = myCodeMirror;
    });
});
