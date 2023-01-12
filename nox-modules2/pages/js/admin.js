//Файл со скриптами для модуля страниц

$(document).ready(
    function () {
        $('#page-title').live('keydown',
            function (event) {
                $(this).data('sync', false);
            }
        );
        $('#page-url').live('keydown',
            function (event) {
                $(this).data('sync', false);
            }
        );


        $('#page-caption').live('keydown',
            function (event) {
                var title = $('#page-title');
                if (( $(title).val() == '' ) || ( $(title).val() == $(this).val() )) {
                    $(title).data('sync', true);
                }

                var url = $('#page-url');
                var value = '/' + toTranslit($(this).val()) + '.html';

                if (($(url).val() == '') || ($(url).val() == value)) {
                    $(url).data('sync', true);
                }

            }
        ).live('keyup',
            function (event) {
                var title = $('#page-title');

                if ($(title).data('sync')) {
                    $(title).val($(this).val());
                }

                var url = $('#page-url');

                if ($(url).data('sync')) {
                    $(url).val('/' + toTranslit($(this).val()) + '.html');
                }
            }
        );

        $('#pages-list .publish').click(
            function () {
                var link = this;
                if ($(link).hasClass('on')) {
                    $.get($(link).attr('href') + '&value=off').success(function () {
                        $(link).removeClass('on').addClass('off');
                    });
                } else {
                    $.get($(link).attr('href') + '&value=on').success(function () {
                        $(link).removeClass('off').addClass('on');
                    });
                }

                return false;
            }
        );
        $('#pages-list .delete').click(
            function () {
                if (confirm('Вы уверены?')) {
                    $.get($(this).attr('href'));
                    $(this).parent().parent().remove();
                }
                return false;
            }
        );

    }
);