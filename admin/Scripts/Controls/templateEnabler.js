$(document).ready(function(){
    $('textarea.codeMirror').parent().next('label').find('input#custom').each(function(){
        $(this).parent().prev('label').find('div.CodeMirror').toggle($(this).is(':checked'));
        $(this).change(function(e){
            $(this).parent().prev('label').find('div.CodeMirror').toggle($(this).is(':checked'));
        });
    });
});