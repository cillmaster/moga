<?php
/**
 * noxThemeActions
 *
 * Класс множественных действий модуля
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.2
 * @package    nox-system
 * @subpackage action
 */

class noxThemeActions extends noxThemeAction
{
    /**
     * Функция проверяет, существует ли закэшированный результат и, если
     * нет, то выполняет действие, иначе просто загружает данные из кэша
     *
     * @param string $action Имя действия для выполнения
     * @return int код ошибки
     */
    public function run($action = 'Default')
    {
        $this->action = ucfirst($action);
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
        $actionName = 'action' . $this->action;
        return $this->$actionName();
    }
}

?>