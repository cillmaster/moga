//Функции для работы с таблицами

//Добавляет клон строки с обнулейнием полей в таблицу
function tableAddRow(el, prepend) {
    //Таблица для добавления
    if ($(el)[0].tagName == 'TABLE') {
        var table = $(el);
    } else {
        var table = $(el).parents('table').first();
    }
    //Ищем тело таблицы, т.е. место для добавления новой строки
    var tbody = $('tbody', table);
    ;

    //Ищем строчку и создаем её клона
    var tr = $('tr', tbody).first().clone().removeClass('hidden');

    //Для каждого input поля
    tr.find('input').each(
        function () {
            var input = $(this);
            input.removeClass('hasDatepick');

            if (input.attr('name') == 'delete[]') {
                input.val(0);
            }
            else {
                //Если задан аттрибут значения по-умолчанию, то применяем его, либо пустое значение
                input.val(input.data('default') || '');
            }
        }
    );

    //Для каждого select поля
    tr.find('select').each(
        function () {
            var select = $(this);
            select.val(select.data('default') || '');
        }
    );

    //Добавляем либо в начало, либо в конец, в зависимости от параметра
    if (prepend) {
        tbody.prepend(tr);
    } else {
        tbody.append(tr);
    }
    ready();
    return false;
}

//Помечает строку как удаленную
function tableDeleteRow(el) {
    //Ищем родительскую строку, причем первую и удаляем
    var tr = $(el).parents('tr').first();

    if (tr.hasClass('deleted')) {
        tr.removeClass('deleted');
        tr.find('td').fadeTo('fast', 1);
        tr.find('input[name="delete[]"]').val(0);
    } else {
        tr.addClass('deleted');
        tr.find('td').fadeTo('fast', 0.3);
        tr.find('input[name="delete[]"]').val(1);
    }
    return false;
}
//Физически удаляет строку
function tableRemoveRow(el) {
    if (confirm('Вы уверены?')) {
        //Ищем родительскую строку, причем первую и удаляем
        $(el).parents('tr').first().remove();
    }
    return false;
}

//Перемещает строку вверх по списку
function tableUpRow(el) {
    var tr = $(el).parents('tr').first();

    var all = tr.parent().children();
    if (all.length <= 1)
        return false;
    var tr_i = all.index(tr);
    if (tr_i == 0) {
        all.eq(all.length - 1).after(tr);
    } else {
        all.eq(tr_i - 1).before(tr);
    }
    return false;
}
//Перемещает строку вниз по списку
function tableDownRow(el) {
    var tr = $(el).parents('tr').first();

    var all = tr.parent().children();

    if (all.length <= 1)
        return false;

    var tr_i = all.index(tr);
    if (tr_i == (all.length - 1)) {
        all.eq(0).before(tr);
    } else {
        all.eq(tr_i + 1).after(tr);
    }
    return false;
}