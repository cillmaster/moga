<?php
/**
 * noxRssAction
 *
 * Класс действия для вывода RSS ленты
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.2
 * @package    nox-system
 * @subpackage action
 */

class noxRssAction extends noxAction
{
    /**
     * Язык ленты
     * @var string
     */
    public $lang = 'ru';

    /**
     * Ссылка ленты
     * @var string
     */
    public $link = '';

    /**
     * Название ленты
     * @var string
     */
    public $title = '';

    /**
     * Описание ленты
     * @var string
     */
    public $description = '';

    /**
     * Иконка ленты
     * @var string
     */
    public $image = '';

    /**
     * Элементы
     * @var array
     */
    public $items = array();

    /**
     * Функция проверяет, существует ли закэшированный результат и, если
     * нет, то выполняет действие, иначе просто загружает данные из кэша
     *
     * @return int код ошибки
     */
    public function run()
    {
        //Выводим заголовок
        header('Content-type: text/xml; charset=utf-8', true);

        //1)Проверяем кеш
        $response = $this->loadFromCache();
        if ($response !== false)
        {
            echo $response;
            return 200;
        }

        //2) Если данных нет, выполняем действия

        //Начинаем буферизацию
        $code = $this->execute();
        if (!$code) $code = 200;

        if ($code == 200)
        {

            $response = '<?xml version="1.0" encoding="utf-8" ?>' . "\r\n" . '<rss version="2.0">' . "\r\n" . '<channel>' . "\r\n";

            $response .= '<title>' . $this->title . '</title>' . "\r\n";
            $response .= '<link>' . $this->link . '</link>' . "\r\n";
            $response .= '<description>' . $this->description . '</description>' . "\r\n";

            if (!empty($this->image))
            {
                $response .= '<image>' . "\r\n\t" .
                    '<url>' . @$this->image . '</url>' . "\r\n\t" .
                    '<title>' . @$this->title . '</title>' . "\r\n\t" .
                    '<link>' . @$this->link . '</link>' . "\r\n" .
                    '</image>' . "\r\n";
            }

            if (is_array($this->items) && (count($this->items) > 0))
            {
                foreach ($this->items as $ar)
                {
                    $response .= '<item>' . "\r\n\t";

                    if (empty($ar['link']))
                    {
                        $ar['link'] = noxSystem::$fullUrl;
                    }
                    if (empty($ar['pubDate']))
                    {
                        $ar['pubDate'] = noxDate::toSql();
                    }
                    if (empty($ar['guid']))
                    {
                        $ar['guid'] = $ar['link'];
                    }
                    $response .= '<guid>' . $ar['guid'] . '</guid>' . "\r\n\t";
                    $response .= '<pubDate>' . date(DATE_RSS, strtotime($ar['pubDate'])) . '</pubDate>' . "\r\n\t";
                    $response .= '<title>' . strip_tags(trim(@$ar['title'])) . '</title>' . "\r\n\t";
                    $response .= '<link>' . $ar['link'] . '</link>' . "\r\n\t";
                    $response .= '<description><![CDATA[' . @$ar['description'] . ']]></description>' . "\r\n\t";

                    if (!empty($ar['image']))
                    {
                        $response .= '<image>' . "\r\n\t\t" .
                            '<url>' . $ar['image'] . '</url>' . "\r\n\t\t" .
                            '<title>' . $ar['title'] . '</title>' . "\r\n\t\t" .
                            '<link>' . $ar['link'] . '</link>' . "\r\n\t" .
                            '</image>' . "\r\n";
                    }

                    $response .= '</item>' . "\r\n";
                }
            }
            $response .= '</channel>' . "\r\n" . '</rss>';

            $this->saveToCache($response);
        }
        echo $response;
        return $code;
    }
}

?>