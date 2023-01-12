<?php
/**
 * noxActions
 *
 * Класс множественных действий модуля
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.2
 * @package    nox-system
 * @subpackage action
 */

class noxActions extends noxAction
{
    /**
     * Функция проверяет, существует ли закэшированный результат и, если
     * нет, то выполняет действие, иначе просто загружает данные из кэша
     *
     * @param string $action Имя действия для выполнения
     * @return int код ошибки
     */
    public function run($action = 'default')
    {
        $this->action = $action;
        return parent::run();
    }

    /**
     * Основная рабочая функция. При ошибке возращает числовой код ошибки,
     * при успешой работе не возвращает ничего, либо true
     *
     * @return int код ошибки
     */
    public function execute()
    {
        $actionName = 'action' . ucfirst($this->action);
        return $this->$actionName();
    }
}

?>