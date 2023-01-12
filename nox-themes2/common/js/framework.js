//Файл с общими скриптами для обработки форм и т.п.

/* Транслит */

var translitArray = {
    'а':'a', 'б':'b', 'в':'v', 'г':'g', 'д':'d', 'е':'e', 'ё':'yo', 'ж':'zh', 'з':'z', 'и':'i', 'й':'y', 'к':'k', 'л':'l', 'м':'m', 'н':'n', 'о':'o', 'п':'p', 'р':'r', 'с':'s', 'т':'t', 'у':'u', 'ф':'f', 'х':'h', 'ч':'ch', 'ц':'c', 'ш':'sh', 'щ':'sch', 'ъ':'', 'ы':'y', 'ь':'', 'э':'e', 'ю':'yu', 'я':'ya',
    ' ':'-', '-':'-',
    'a':'a', 'b':'b', 'c':'c', 'd':'d', 'e':'e', 'f':'f', 'g':'g', 'h':'h', 'i':'i', 'j':'j', 'k':'k', 'l':'l', 'm':'m', 'n':'n', 'o':'o', 'p':'p', 'q':'q', 'r':'r', 's':'s', 't':'t', 'u':'u', 'v':'v', 'w':'w', 'x':'x', 'y':'y', 'z':'z',
    '1':'1', '2':'2', '3':'3', '4':'4', '5':'5', '6':'6', '7':'7', '8':'8', '9':'9', '0':'0' };

function toTranslit(str) {
    //В нижний регистр
    str = str.toLowerCase();
    var res = '';
    var len = str.length;

    for (var i = 0; i < len; i++) {
        if (translitArray[str[i]] != undefined) {
            if ((translitArray[str[i]] == '-') && (res[res.length - 1] == '-'))
                continue;
            res += translitArray[str[i]];
        }
    }
    if (res[res.length - 1] == '-')
        res = res.substr(0, res.length - 1);
    return res;
}

function htmlSpecialChars(html) {
    // Сначала необходимо заменить &
    html = html.replace(/&/g, "&amp;");
    // А затем всё остальное в любой последовательности
    html = html.replace(/</g, "&lt;");
    html = html.replace(/>/g, "&gt;");
    html = html.replace(/"/g, "&quot;");
    // Возвращаем полученное значение
    return html;
}

/* Только уже кодированная строка. Не должно быть символов "<" и т.п. */
function htmlSpecialCharsDecode(str) {
    var d = $('<div/>').html(str);
    var res = d.text();
    d.remove();
    return res;
}


//Прагин автозагружаемых блоков
jQuery.fn.autoLoad = function () {

    $(this).each(function () {
        //Контейнер страниц
        var container = $(this);
        //URL
        var url = container.data('url');

        if (!url)
            return false;

        container.append('<br /><br />').addClass('loading');

        container.load(url, function () {
            container.removeClass('loading');
        });
    });
}

//Плагин для сворачиваемых панелей
jQuery.fn.fieldsetSlide = function () {

    $(this).each(function () {

        var legend = $('legend', this);
        var fieldset = this;
        var h = $(fieldset).css('height');

        $(fieldset).animate({height:0}).addClass('closed');

        $(legend).addClass('active').click(
            function () {
                if ($(fieldset).hasClass('closed')) {
                    $(fieldset).animate({height:h}).removeClass('closed');
                } else {
                    $(fieldset).animate({height:0}).addClass('closed');
                }
            }
        );

    });
}

//Плагин для бегущей строки
jQuery.fn.runString = function (speed) {

    //Двигаемая часть
    var container = $(this);
    var move = $('div', this);
    var min_l = -move.width();
    var max_l = container.width();

    move.data('left', 0);
    setInterval(function () {
            var left = move.data('left');
            left = left - speed;
            //Если элемент скрылся за страницей
            if (left < min_l) {
                left = max_l;
            }
            move.data('left', left);
            //Прибавляем скорость
            move.css('left', (left) + 'px');
        },
        30
    );
}

//Функция смены типа поля INPUT с пароля на текст и обратно
function togglePassword(showPassword, input) {
    var input = $(input);
    var type = showPassword ? 'text' : 'password';
    //Создаем новый input
    //var newInput = $('<input>').attr('type', type).attr('id', input.attr('id')).val(input.val());

    var newInput = input.clone(true).attr('type', type);

    input.after(newInput).remove();
}

//Плагин для переключателей страниц
jQuery.fn.pages = function () {

    //Контейнер страниц
    var container = $(this);
    //Родитель, которого будем обновлять
    var parent = container.parent();
    //Ссылки - переключатели страниц
    var links = container.find('a');

    //Применяем "вечное" событие
    links.live('click', function () {
        var link = $(this);

        if (link.hasClass('active'))
            return false;

        links.removeClass('active');
        link.addClass(link);

        //Меняем URL
        history.pushState(null, null, link.attr('href'));

        parent.addClass('loading');

        parent.load(link.attr('href'), function () {
            parent.removeClass('loading');
        });

        return false;
    });
}


//Плагин для автозагрузки
jQuery.fn.autoLoad = function () {

    $(this).not('.loaded').each(function () {

        //Контейнер страниц
        var container = $(this);

        if (container.hasClass('loaded')) {
            alert('HasClass!');
        }

        //URL
        var url = container.data('url');

        if (!url)
            return false;

        container.append('<br /><br />').addClass('loading');

        container.load(url, function () {
            container.addClass('loaded').removeClass('loading');
            ready();
        });
    });
}

//Плагин сортировки таблицы
jQuery.fn.sortTable = function () {

    $(this).each(function () {

        var table = $(this);
        //Заголовки
        var header = table.find('thead th:not(.not-sort)');
        var rows = table.find('tbody > tr');

        var indexArray = new Object();
        indexArray.length = rows.length;
        //Создаем индексный массив
        for (var i = 0; i < rows.length; i++) {
            var cells = rows.eq(i).children('td:not(.not-sort)');
            indexArray[i] = new Object();
            indexArray[i].length = cells.length;
            for (var j = 0; j < cells.length; j++) {
                indexArray[i][j] = cells[j];
            }
        }

        header.off('click').click(function () {
            var th = $(this);

            var index = header.index(th);

            header.not(th).removeClass('down').removeClass('up');

            var cmpFunction;

            if (th.hasClass('down')) {
                th.removeClass('down').addClass('up');
                //Сортировка по убыванию
                cmpFunction = (function (a, b) {
                    var a1 = a[index].textContent;
                    var b1 = b[index].textContent;
                    if (!isNaN(a1)) {
                        a1 = Number(a1);
                        b1 = Number(b1);
                    }
                    return (a1 < b1);
                });
            } else {
                th.removeClass('up').addClass('down');
                //Сортировка по возрастанию
                cmpFunction = (function (a, b) {
                    var a1 = a[index].textContent;
                    var b1 = b[index].textContent;
                    if (!isNaN(a1)) {
                        a1 = Number(a1);
                        b1 = Number(b1);
                    }
                    return (a1 > b1);
                });
            }

            var n = indexArray.length;
            var d = 1;
            while (d<n/2)
            {
                d *= 2;
            }

            for (; d >= 1; d = d/2)
            {
                //console.log(d);
                for (var i=0; i<d; i++)
                {
                    for (var j=i; (j+d)<n; j+=d)
                    {
                        for (var l=j+d; l<n; l+=d)
                        {
                            if (cmpFunction(indexArray[j], indexArray[l])) {
                                //alert(indexArray[i][index].textContent + ' > ' + indexArray[j][index].textContent);
                                for (var k = 0; k < indexArray[j].length; k++) {
                                    t = indexArray[j][k].innerHTML;
                                    indexArray[j][k].innerHTML = indexArray[l][k].innerHTML;
                                    indexArray[l][k].innerHTML = t;
                                }
                            }
                        }
                    }
                }
            }

            return false;
        });

    });
}