<?php
/**
 * noxJsonAction
 *
 * Класс действия отображающего на экране модуля
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.2
 * @package    nox-system
 * @subpackage action
 */

class noxJsonAction extends noxAction
{
    /**
     * Данные, которые будут преобразованы в формат Json и выведен
     *
     * @var mixed
     */
    public $data;

    /**
     * Функция проверяет, существует ли закэшированный результат и, если
     * нет, то выполняет действие, иначе просто загружает данные из кэша
     *
     * @return int код ошибки
     */
    public function run()
    {
        //Выводим заголовок
        header('Content-type: application/json; charset: utf-8', true);

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
            $response = json_encode($this->data);
            if (isset($_GET['callback']))
            {
                $response = $_GET['callback'] . '(' . $response . ');';
            }
            $this->saveToCache($response);
        }
        echo $response;
        return $code;
    }
}

?>