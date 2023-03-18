<?php
/**
 * Действия модуля для вывода сообщений об ошибках
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.0
 * @package    away
 * @subpackage away
 */

class awayDefaultAction extends noxThemeAction
{
    public $cache = false;

    /**
     * Вывод сообщения
     *
     */
    public function execute()
    {
        $link = urldecode($_GET['to']);
        //Выводим контент
        $this->caption = 'Переход по внешней ссылке';
        echo '<h2>Переход по внешней ссылке</h2><p>Вы пытаетесь перейти по ссылке <strong>' . $link . '</strong>.</p>
		<p>Напоминаем, что администрация  не несет ответственности за содержимое сайта <strong>' . $link . '</strong>.</p>
		<p>Переход по ссылке на внешний адрес может быть опасен.</p>
		<p>Если Вы все же хотите перейти по ссылке, то нажмите на <a href="' . $link . '"><strong>' . $link . '</strong></a></p>
		<p>Если Вы не хотите рисковать безопасностью Вашего компьютера, нажмите <a href="javascript:window.close()"><strong>отмена</strong></a>.</p>';
    }
}

?>