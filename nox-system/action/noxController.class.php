<?php
/**
 * noxController
 *
 * Класс контроллера. Позволяет управлять маршрутизацией более расширено.
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.2
 * @package    nox-system
 * @subpackage action
 */

class noxController
{
    /**
     * Конструктор класса задает начальные параметры и создает переменные
     */
    public function __construct()
    {
        $this->params = noxSystem::$params;
    }

    /**
     *  Выполняет контроллер
     *
     * @return int код ошибки
     */
    public function run()
    {
        //Если данных нет, выполняем действия
        return $this->execute();
    }

    /**
     * Основная рабочая функция. При ошибке возращает числовой код ошибки,
     * при успешой работе не возвращает ничего, либо true
     *
     * @return int код ошибки
     */
    public function execute()
    {

    }

    /**
     * Вызвает перенаправление на определенный адрес
     * @param string $url
     */
    public function redirect($url = '')
    {
        noxSystem::location($url);
    }

    /**
     * Выполняет действие
     *
     * @param noxAction $actionObject Объект действия для выполнения
     * @param string $actionName      Имя дейсвтия для выполнения (для множественных действий
     * @return int Код ошибки
     */
    public function executeAction($actionObject, $actionName = '')
    {
        if (empty($actionName))
        {
            $error = $actionObject->run();
        } else
        {
            $error = $actionObject->run($actionName);
        }
        return $error;
    }

    /**
     * Проверяет есть ли данное право у пользователя
     * @param string $right Право
     * @param int $user_id  ID пользователя или 0 для текущего пользователя
     * @return bool
     */
    public function haveRight($right, $user_id = 0)
    {
        return noxSystem::$userControl->haveRight($this->moduleName, $right, $user_id);
    }

    /**
     * Возвращает параметр по имени с приведением к типу и значению по-умолчанию
     * @param string $name   Имя параметра
     * @param mixed $default Значение по-умолчанию
     * @return mixed
     */
    public function getParam($name, $default = 0)
    {
        return getParam(@$this->params[$name], $default);
    }
}



?>