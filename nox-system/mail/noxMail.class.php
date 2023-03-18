<?php
/**
 * noxMail
 *
 * Класс для отправки писем как на основе шаблонов, так и в виде простого текста
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.2
 * @package    nox-system
 * @subpackage mail
 */

class noxMail extends noxTemplate
{
    /**
     * Отправка HTML кода или простого текста
     */
    private $html = true;

    /**
     * Получатели
     */
    private $to = array();

    /**
     * От кого письмо
     */
    private $from = '';

    /**
     * Тема
     */
    private $subject = '';

    /**
     * Вложения
     */
    private $attach = array();

    private $smtp = false;

    /**
     * Конструктор
     *
     * @param string $filename имя файла шаблона
     */
    public function __construct($filename = '')
    {
        parent::__construct($filename);
        $this->from = 'noreply@' . $_SERVER['SERVER_NAME'];
        $this->addVar('content', '');

        //Настрока для отправки почты
        $config = noxConfig::getConfig();
        if (!empty($config['smtp_server']))
        {
            $this->smtp = [
                'smtp_server' => $config['smtp_server'],
                'smtp_from' => $config['smtp_from'],
                'smtp_login' => $config['smtp_login'],
                'smtp_password' => $config['smtp_password'],
                'smtp_port' => $config['smtp_port']
            ];
        } // используя эу команду отправка пойдет через smtp

    }

    /**
     * Выбирает режим HTML или текст
     *
     * @param bool $html
     * @return noxMail
     */
    public function html($html = true)
    {
        $this->html = $html;
        return $this;
    }

    /**
     * Задает тему письма
     *
     * @param string Тема
     * @return noxMail
     */
    public function subject($subject)
    {
        $this->subject = $subject;
        return $this;
    }

    /**
     * Задает главный блок - блок контента.
     *
     * @param $text string Содержимое блока
     * @return noxMail
     */
    public function content($text)
    {
        $this->vars['content'] = $text;
        return $this;
    }

    /**
     * Задает отправителя письма
     *
     * @param text
     * @return noxMail
     */
    public function from($from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * Добавляет получателя письма
     *
     * @param string $to  email получателя
     * @param bool $clear очистить предыдущих получателей (по-умолчанию false)
     * @return noxMail
     */
    public function to($to, $clear = false)
    {
        if ($clear)
        {
            $this->to = array($to => $to);
        } else
        {
            if (is_array($to))
            {
                foreach ($to as $m)
                {
                    $this->to[$m] = $m;
                }
            } else
            {
                $this->to[$to] = $to;
            }
        }

        return $this;
    }

    /**
     * Добавляет вложение в письмо
     *
     * @param string $filename имя файла, которое необходимо вложить
     * @param bool $clear      очистить предыдущих получателей (по-умолчанию false)
     * @return noxMail
     */
    public function attach($filename, $clear = false)
    {
        if ($clear)
        {
            $this->attach = array();
        }
        $this->attach[] = $filename;
        return $this;
    }

    /**
     * Формирует содержимое письма и возвращает его
     *
     * @return string
     */
    public function form()
    {
        $text = trim($this->__toString());

        return $text;
    }

    public function setSmtp($smtp) {
        $this->smtp = $smtp;
        return $this;
    }

    /**
     * Отправляет письмо и возвращает результат
     *
     * @return bool
     */
    public function send(){
        require_once(dirname(__FILE__) . '/libmail.php');

        $m = new Mail('utf-8'); // можно сразу указать кодировку, можно ничего не указывать ($m= new Mail;)
        $m->Body($this->form());
        $m->From($this->from); // от кого
        $m->To($this->to); // кому
        $m->Subject($this->subject);
        $m->Priority(4); // установка приоритета
        $m->html($this->html);

        if ($this->attach and (count($this->attach) > 0)){
            foreach ($this->attach as $attach){
                $realfilename = noxFileSystem::getRealPathFromUrl($attach);
                $m->Attach($realfilename, "");
            }
        }

        if ($this->smtp){
            $m->smtp_on($this->smtp['smtp_from'], $this->smtp['smtp_server'], $this->smtp['smtp_login'], $this->smtp['smtp_password'], $this->smtp['smtp_port'], 10);
        }

        $m->Send();

        $GLOBALS['mail'] = $m->getLog(1);
        return true;
    }

    /**
     * Возвращает весь вывод
     *
     * @return string Результат
     */
    public function __toString()
    {
        //Если шаблона нет, то просто возвращаем текст
        if (empty($this->fileName))
        {
            return $this->vars['content'];
        }
        else
        {
            return parent::__toString();
        }
    }
}

?>