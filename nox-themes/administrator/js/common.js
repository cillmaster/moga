//Файл с общими скриптами для обработки форм и т.п.

$(document).ready(ready);

function ready() {
    //Включаем редакторы текста
    var opts = {
        lang:'ru', // set your language
        styleWithCSS:false,
        height:400,
        toolbar:'maxi',
        absoluteURLs: false,
        allowSource: true,
        fmAllow: true,
        fmOpen: openFileManagerForEditor
    };
    // create editor
    $('.text-editor').elrte(opts);

    //Файловый менеджер
    $('.file-editor').fileEditor();

    $('.date-editor').datepick();

    //Сворачиваемые панели
    $('.fieldset-slide').fieldsetSlide();

    //Перемотка страниц
    $('.pages').pages();

    /* $('.ajax').click(
        function () {
            var link = this;
            $('#ajax-container').removeClass('error').removeClass('ok').addClass('loading').load(
                $(link).attr('href'),
                function () {
                    ready();
                    $('#ajax-container').removeClass('loading').prepend('<h3>' + $(link).attr('title') + '</h3>');
                    $('html').scrollTop($('#ajax-container').offset().top);
                    history.pushState(null, null, $(link).attr('href'));
                }
            );
            return false;
        }
    ); */

    $('table a.delete:not(.not-ajax)').click(
        function () {
            if (confirm('Вы уверены?')) {
                $.get($(this).attr('href'));
                $(this).parent().parent().remove();
            }
            return false;
        }
    );
    $('table a.delete.not-ajax').click(
        function () {
            return (confirm('Вы уверены?'));
        }
    );
    $('.confirm').click(
        function () {
            return (confirm('Вы уверены?'));
        }
    );

    $('.domains ul li a.delete').click(function () {

        var a = $(this);
        var li = a.parent();

        li.prev().mouseenter();

        $('#' + li.attr('id') + 'Route').remove();
        li.remove();
        return false;
    });

    $('.domains a.add').click(function () {

        var a = $(this);
        $('#routes-form-submit').click();
        return false;
    });

    $('table.sort').sortTable();

    $('.enabled').click(
        function () {
            var link = this;
            if ($(link).hasClass('on')) {
                $.get($(link).attr('href') + '&enabled=0').success(function () {
                    $(link).removeClass('on').addClass('off');
                });
            } else {
                $.get($(link).attr('href') + '&enabled=1').success(function () {
                    $(link).removeClass('off').addClass('on');
                });
            }

            return false;
        }
    );
}

function openFileManagerForEditor(callback)
{
    $('#file-editor-dialog').remove();
	
	var dlg = $('<div id="file-editor-dialog"><div id="file-editor-div"></div></div>');
    $('body').prepend(dlg);
	
	var div = $('#file-editor-div').css('width', '585').css('height', '300px');
    div.
        delegate(' .edit-file', 'click',
        function () {
            callback($(this).data('path'));
            div.empty().hide();
			dlg.dialog( "close" );
            return false;
        }
    );

	dlg.dialog({ 	
		title: 'Файловый менеджер',
		height: 340,
		width: 600 });

    div.
        show().
        addClass('loading').
        load('/administrator/filemanager/?editor=1&folder=/nox-data',
        function () {
            div.removeClass('loading');
        }
    );
}

//Плагин для файлового менеджера
jQuery.fn.fileEditor = function () {

    var all = $(this);
    all.parent().css('position', 'relative');

    $('#file-editor-div').remove();
    var div = $('<div id="file-editor-div"></div>').hide();
    $('body').prepend(div);
    div.css('position', 'absolute').
        delegate(' .edit-file', 'click',
        function () {
            var i = $(div.data('input'));
            i.val($(this).data('path')).change();
            div.empty().hide();
            //$(document).unbind('click');
            return false;
        }
    );

    $(document).click(function (event) {
            if ($(event.target).closest('#file-editor-div').length > 0) return;
            $('#file-editor-div').empty().hide();
            //$(document).unbind('click');
            event.stopPropagation();
        }
    );

    all.off('click').click(
        function () {
            var input = $(this);
            var folder = input.val() || input.attr('data-folder') || input.data('default') || '/nox-data/';

            div.css('top', input.offset().top + input.outerHeight(false)).
                css('left', input.offset().left).
                data('input', input).
                show().addClass('loading').
                load('/administrator/filemanager/?editor=1&folder=' + folder,
                function () {
                    div.removeClass('loading');
                }
            );
            return false;
        }
    );
}