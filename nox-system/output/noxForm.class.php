<?php
/**
 * noxHtmlForm
 *
 * Класс для создания HTML форм
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.0
 * @package    nox-system
 * @subpackage output
 */

class noxForm
{
    /**
     * Поля формы
     *
     * @var array
     */
    private $fields = array();

    /**
     * Результат вывода
     *
     * @var string
     */
    private $output = '';

    /**
     * Адрес страницы, куда будет отправлена форма
     *
     * @var string
     */
    public $action;

    /**
     * Метод отправки данных (POST/GET)
     *
     * @var string
     */
    public $method;

    /**
     * ID формы
     *
     * @var string
     */
    public $id;

    /**
     * Класс формы
     * @var string
     */
    public $class;

    /**
     * Добавочный знак к подписи поля
     *
     * @var string
     */
    public $label_add = ':';

    /**
     * Экранирует строку для вывода в форму
     * @param string $text
     * @return string
     */
    public static function escape($text)
    {
        return htmlspecialchars(trim($text));
    }

    /**
     * Конструктор
     * @param string $action
     * @param string $method
     * @param string $id
     * @param string $class
     */
    public function __construct($action = '', $method = 'POST', $id = '', $class = '')
    {
        $this->action = $action;
        $this->method = $method;
        $this->id = $id;
        $this->class = $class;
    }

    /**
     * Добавляет текст в виде абзаца
     *
     * @param string $text
     * @param string $id
     * @return noxForm
     */
    public function addText($text, $id = '')
    {
        $this->fields[] = '<p' . ($id ? ' id="' . $id . '"' : '') . '><span>' . $text . '</span></p>';
        $this->output = '';
        return $this;
    }

    /**
     * Добавляет группу элементов
     *
     * @param string $text
     * @param bool $slide Может сворачиваться или нет
     * @param string $id
     * @return noxForm
     */
    public function addGroup($text, $slide = true, $id = '')
    {
        $this->fields[] = '<fieldset' . ($id ? ' id="' . $id . '"' : '') . ($slide ? ' class="fieldset-slide"' : '') . '><legend' . ($id ? ' id="' . $id . '-legend"' : '') . '>' . $text . '</legend></p>';
        $this->output = '';
        return $this;
    }

    /**
     * Закрывает открытую группу
     *
     * @return noxForm
     */
    public function closeGroup()
    {
        $this->fields[] = '</fieldset>';
        $this->output = '';
        return $this;
    }

    /**
     * Добавляет скрытое текстовое поле в форму
     *
     * @param string $name  Имя поля
     * @param string $value Значение
     * @param string $id
     * @return noxForm
     */
    public function addHiddenField($name, $value = '', $id = '')
    {
        $this->fields[] = '<input type="hidden" name="' . $name . '" value="' . self::escape($value) . '"' . ($id ? ' id="' . $id . '"' : '') . ' />';
        $this->output = '';
        return $this;
    }

    /**
     * Добавляет поле в форму
     *
     * @param string $label   Подпись поля
     * @param string $content Содержмое поля
     * @return noxForm
     */
    public function addField($label, $content)
    {
        $this->fields[] = '<p><label>' . $label . $this->label_add . '</label> <span>' . $content . '</span></p>';
        $this->output = '';
        return $this;
    }

    /**
     * Добавляет текстовое поле в форму
     *
     * @param string $label     Подпись поля
     * @param string $name      Имя поля
     * @param string $value     Значение
     * @param string $id
     * @param bool $required    обязательное поле
     * @return noxForm
     */
    public function addTextField($label, $name, $value = '', $id = '', $required = false)
    {
        return $this->addField($label, '<input type="text" title="' . $label . '" placeholder="' . $label . '" name="' . $name . '" value="' . self::escape($value) . '"' . ($id ? ' id="' . $id . '"' : '') . ($required ? ' required="required"' : '') . '  />');
    }

    /**
     * Добавляет поле пароля в форму
     *
     * @param string $label    Подпись поля
     * @param string $name     Имя поля
     * @param string $value    Значение
     * @param string $id
     * @param $required        bool обязательное поле
     * @return noxForm
     */
    public function addPasswordField($label, $name, $value = '', $id = '', $required = false)
    {
        return $this->addField($label, '<input type="password" title="' . $label . '" placeholder="' . $label . '" name="' . $name . '" value="' . self::escape($value) . '"' . ($id ? ' id="' . $id . '"' : '') . ($required ? ' required="required"' : '') . '  />');
    }

    /**
     * Добавляет поле даты в форму
     *
     * @param string $label    Подпись поля
     * @param string $name     Имя поля
     * @param string $value    Значение
     * @param string $id
     * @param $required        bool обязательное поле
     * @return noxForm
     */
    public function addDateField($label, $name, $value = '', $id = '', $required = false)
    {
        return $this->addField($label, '<input type="date" title="' . $label . '" placeholder="' . $label . '"  name="' . $name . '" value="' . self::escape($value) . '"' . ($id ? ' id="' . $id . '"' : '') . ($required ? ' required="required"' : '') . ' class="date-editor" />');
    }

    /**
     * Добавляет область с тектом в форму
     *
     * @param string $label     Подпись поля
     * @param string $name      Имя поля
     * @param string $value     Значение
     * @param string $id
     * @param $required         bool обязательное поле
     * @return noxForm
     */
    public function addTextArea($label, $name, $value = '', $id = '', $required = false)
    {
        return $this->addField($label, '<textarea title="' . $label . '" placeholder="' . $label . '" name="' . $name . '"' . ($id ? ' id="' . $id . '"' : '') . ($required ? ' required="required"' : '') . '>' . self::escape($value) . '</textarea>');
    }

    /**
     * Добавляет редактор текста в форму
     *
     * @param string $label Подпись поля
     * @param string $name  Имя поля
     * @param string $value Значение
     * @param string $id
     * @return noxForm
     */
    public function addTextEditor($label, $name, $value = '', $id = '', $required = false)
    {
        return $this->addField($label, '<textarea title="' . $label . '" placeholder="' . $label . '" name="' . $name . '"' . ($id ? ' id="' . $id . '"' : '') . ' class="text-editor">' . self::escape($value) . '</textarea>');
    }

    /**
     * Возвращает группу checkbox
     *
     * @param string $name    Имя поля
     * @param string $checked Текущее значение
     * @param array $value    Значения в виде значение=>описание
     * @return string
     */
    public static function getRadioButtons($name, $checked = '', $values = array())
    {
        if (!$values)
        {
            $values = array(1 => 'Да',
                            0 => 'Нет');
        }
        $content = '';
        foreach ($values as $value => $caption)
        {
            $content .= '<label><input type="radio" name="' . $name . '" value="' . self::escape($value) . '"' . ($value == $checked ? ' checked="checked"' : '') . ' /> ' . $caption . '</label>';
        }
        return $content;
    }

    /**
     * Добавляет группу checkbox в форму
     *
     * @param string $label   Подпись поля
     * @param string $name    Имя поля
     * @param string $checked Текущее значение
     * @param array $value    Значения в виде значение=>описание
     * @return noxForm
     */
    public function addRadioButtons($label, $name, $checked = '', $values = array())
    {
        return $this->addField($label, self::getRadioButtons($name, $checked, $values));
    }

    /**
     * Возвращает select с однозначным выбором
     *
     * @param string $name     Имя поля
     * @param string $selected Текущее значение
     * @param array $values     Значения в виде значение=>описание
     * @param string $id       ID
     * @param string $empty    Текст для пустого параметра
     * @return string
     */
    public static function getSelect($name, $selected = '', $values = array(), $id = '', $empty = '', $attrs = false)
    {
        if (!$values)
        {
            $values = array(1 => 'Да',
                            0 => 'Нет');
        }
        $content = '';
        if ($empty)
        {
            $content .= '<option value="0">' . $empty . '</option>';
        }
        foreach ($values as $value => $caption)
        {
            $content .= '<option value="' . self::escape($value) . '"' . ($value == $selected ? ' selected="selected"' : '') . '>' . $caption . '</option>';
        }
        $html = '<select name="' . $name . '"' . ($id ? ' id="' . $id . '"' : '');
        if(is_array($attrs)) {
            foreach ($attrs as $k=>$v) {
                $html .= ' ' . $k . '="' . $v . '"';
            }

        }
        $html .= '>' . $content . '</select>';

        return $html;
    }

    /**
     * Добавляет select с однозначным выбором в форму
     *
     * @param string $name     Имя поля
     * @param string $selected Текущее значение
     * @param array $value     Значения в виде значение=>описание
     * @param string $id       ID
     * @param string $empty    Текст для пустого параметра
     * @return noxForm
     */
    public function addSelect($label, $name, $selected = '', $values = array(), $id = '', $empty = '')
    {
        return $this->addField($label, self::getSelect($name, $selected, $values, $id, $empty));
    }


    /**
     * Добавляет кнопку отправки
     *
     * @param string $value Значение и надпись на кнопке
     * @param string $name  Имя поля кнопки
     * @param string $id
     * @return noxForm
     */
    public function addSubmitButton($value = 'Submit', $name = 'Submit', $id = '')
    {
        $this->fields[] = '<p><span><input type="submit" name="' . $name . '" value="' . self::escape($value) . '"' . ($id ? ' id="' . $id . '"' : '') . ' /></span></p>';
        $this->output = '';
        return $this;
    }

    /**
     * Добавляет кнопку
     *
     * @param string $value Значение и надпись на кнопке
     * @param string $name  Имя поля кнопки
     * @param string $id
     * @return noxForm
     */
    public function addButton($value, $name, $id = '')
    {
        $this->fields[] = '<p><span><input type="button" name="' . $name . '" value="' . self::escape($value) . '"' . ($id ? ' id="' . $id . '"' : '') . ' /></span></p>';
        $this->output = '';
        return $this;
    }

    /**
     * Возвращает код элемента
     *
     * @return string
     */
    public function __toString()
    {
        if (empty($this->output))
        {
            $this->output = '<form action="' . $this->action . '" method="' . $this->method . '"' . ($this->id ? ' id="' . $this->id . '"' : '') . '>';
            $this->output .= implode('', $this->fields);
            $this->output .= '</form>';
        }
        return $this->output;
    }
}

?>