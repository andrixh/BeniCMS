$(document).ready(function () {
    _d('ready for content!');
    $('fieldset.contentViews').each(function () {
        var codeMirrorEditor;
        var textarea;
        var titles = [];
        var values = [];
        var plus = $('<a href="#" class="btn add">+</a>');
        var tabs = $(this).find('ul.tabs');
        var editor = $(this).find('div.editor');

        tabs.find('li').each(function (index) {
            titles.push($(this).find('input').val());
            $(this).append('<a href="#" class="title">' + $(this).find('input').val() + '</a><a href="#" class="btn delete">&times;</a>');
            if (index == 0) {
                $(this).find('a.delete').remove();
            }
        });
        $(this).find('ul.tabs').append(plus);

        $(this).find('div.editor textarea').each(function (index) {
            values.push($(this).val());
        });

        tabs.find('li').eq(0).addClass('selected');


        codeMirrorEditor = CodeMirror.fromTextArea($(this).find('div.editor textarea.selected')[0], {
            mode: {name: 'twig', alignCDATA: true},
            lineNumbers: true,
            tabSize: 2,
            fixedGutter: true
        });

        $(this).on('click', 'ul.tabs a.add', function (e) {
            e.preventDefault();

            titles.push('new');
            values.push(values[0]);
            tabs.find('li').removeClass('selected');
            tabs.find('li:last-of-type').after('<li class="selected"><a href="#" class="title">' + titles[titles.length - 1] + '</a><a href="#" class="btn delete">&times;</a></li>');

            var tI = $('<input type="hidden">');
            tI.val(titles[titles.length - 1]);
            tI.attr('name',$(tabs).find('li').eq(0).find('input[type=hidden]').attr('name'));
            tabs.find('li:last-of-type').prepend(tI);

            codeMirrorEditor.toTextArea();
            codeMirrorEditor = null;
            var ta = $('<textarea class="hidden selected"></textarea>');
            ta.attr('name', (editor.find('textarea').eq(0).attr('name')));
            ta.val(editor.find('textarea').eq(0).val());
            editor.find('textarea').removeClass('selected');
            editor.append(ta);
            codeMirrorEditor = CodeMirror.fromTextArea(editor.find('textarea.selected')[0], {
                mode: {name: 'twig', alignCDATA: true},
                lineNumbers: true,
                tabSize: 2,
                fixedGutter: true
            });
        });

        $(this).on('click', 'ul.tabs>li>a.delete', function (e) {
            e.preventDefault();
            if (!window.confirm('Delete Component View?')){
                return;
            }
            codeMirrorEditor.toTextArea();
            codeMirrorEditor = null;
            var i = $(this).parent().index();

            $(this).parent().remove();
            editor.find('textarea').eq(i).remove();

            titles.splice(i,1);
            values.splice(i,1);

            if (i == values.length) {
                i--;
            }

            editor.find('textarea').eq(i).addClass('selected');
            tabs.find('input[type=hidden]').eq(i).addClass('selected');
            tabs.find('li').eq(i).addClass('selected');
            codeMirrorEditor = CodeMirror.fromTextArea(editor.find('textarea.selected')[0], {
                mode: {name: 'twig', alignCDATA: true},
                lineNumbers: true,
                tabSize: 2,
                fixedGutter: true
            });
        });

        $(this).on('click','ul.tabs>li:not(.selected)',function(e){
            e.stopPropagation();
            e.preventDefault();
            codeMirrorEditor.toTextArea();
            codeMirrorEditor = null;

            var i = $(this).index();
            tabs.find('li').removeClass('selected');
            editor.find('textarea').removeClass('selected');
            tabs.find('input[type=hidden]').removeClass('selected');
            editor.find('textarea').eq(i).addClass('selected');
            tabs.find('input[type=hidden]').eq(i).addClass('selected');
            tabs.find('li').eq(i).addClass('selected');

            codeMirrorEditor = CodeMirror.fromTextArea(editor.find('textarea.selected')[0], {
                mode: {name: 'twig', alignCDATA: true},
                lineNumbers: true,
                tabSize: 2,
                fixedGutter: true
            });
        });

        $(this).on('click','ul.tabs>li.selected>a.title',function(e){
            e.preventDefault();
            if ($(this).parent().index() == 0) {
                return;
            }
           var w = $(this).width();
            var titleEdit = $('<input type="text" class="labeller" />');
            titleEdit.val($(this).text());
            titleEdit.css('width',w+'px');
            $(this).hide();
            $(this).after(titleEdit);
            titleEdit.focus();
        });

        $(this).on('change blur', 'input.labeller',function(e){
            var i = $(this).parent().index();
            var val = $(this).val();
            var p = $(this).parent();
            $(this).remove();
            titles[i] = val;
            p.find('input[type=hidden]').val(val);
            p.find('a.title').text(val).show();

        });
    });

});