<?php
/**
 * Модель страницы
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.1
 * @package    pages
 */

class pagesModel extends noxModel
{
    /**
     * Таблица модели
     * @var string
     */
    var $table = 'pages_page';

    /**
     * Возвращает массив со значениями полей по-умолчанию
     *
     * @return array
     */
    public function getEmptyFields()
    {
        $res = parent::getEmptyFields();
        //Получаем настройки сайта
        $config = noxConfig::getConfig();
        $res['published'] = 3;
        $res['theme'] = $config['defaultTheme'];
        $res['locale'] = $config['defaultLocale'];
        return $res;
    }

    /**
     * Выборка по URL
     *
     * @param string значение url
     * @return array
     */
    public function getByUrl($url)
    {
        $this->where('url', $url);
        $res = array();
        while ($ar = $this->fetch())
        {
            if ($ar['locale'] == noxLocale::$locale)
            {
                return $ar;
            } else
            {
                $res = $ar;
            }
        }
        return $res;
    }
}

?>