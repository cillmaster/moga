<?php
/*
 * @author      <pa-nic@yandex.ru>
 * @version    1.0
-------------------------------------------
Версия 1.0 (09.09.2013)
Основано на php_libmail v 1.6.0 (09.12.2011) webi.ru
_______________________________________________
*/
// TODO: валидность емаил
// TODO: CC + BCC отправка через smtp
// TODO: прикрепление файлов
// TODO: Заголовок отписки
define('MAIL_FATAL_ERROR', 0);
define('MAIL_NOTICE', 1);
define('MAIL_LOG', 2);
define('MAIL_WARNING', 3);
define('MAIL_ENDL', "\r\n");

class Mail
{
    private $xheaders = array(
        'Mime-Version'  => '1.0',
        'X-Mailer'      => 'libMail.php 1.0'
    );                                          // Массив заголовков

    var $receipt            = 0;
    var $names_email        = array();          // имена для email адресов, чтобы делать вид ("сергей" <asd@wer.re>)

    private $_CC            = array();          // Массив с открыми получателями копий
    private $_BCC           = array();          // Массив со скрытыми получателями копий
    private $attach         = array();          // Массив с прикрепленными файлами

    public $delay           = 0;                //Задержка между отправкой писем
    private $showPassword   = true;             //Показывать ли в логах пароль smtp
    private $formatType     = "text/plain";     // формат письма. по умолчанию текстовый
    private $ctencoding     = "8bit";

    private $headers       = '';               //Сформированные заголовки
    private $body           = '';               // Текст письма
    private $fullBody       = '';               // Сформированное тело письма
    private $signature      = '';               // Футер письма

    private $charset,                           // Кодировка письма
        $checkAddress       = true,             // Проверять ли адреса на валидность
        $sendTo             = array(),          // Получатели
        $use_smtp           = 0,                // Использовать ли smtp
        $smtp               = array(),          // Массив с настройками подключения
        $_SMTP_CONNECTION   = 0,                // Соединение с сервером

        $priorities = array(
            1 => '1 (Highest)',
            2 => '2 (High)',
            3 => '3 (Normal)',
            4 => '4 (Low)',
            5 => '5 (Lowest)'
    );                                          // Приоритеты

    private $log_stack = array();               // Стек для сообщений

    /*
     * @return Mail
     */
    public function reset() {
        $needleHeaders = array();
        if(isset($this->xheaders['From'])) {
            $needleHeaders['From'] = $this->xheaders['From'];
            $namesFrom = $this->names_email['from'];
        }

        $this->_CC            = array();
        $this->_BCC           = array();
        $this->attach         = array();
        $this->names_email    = array();
        $this->attach         = array();
        $this->sendTo         = array();

        if(isset($namesFrom)) {
            $this->names_email['from'] = $namesFrom;
        }
        $this->xheaders = array(
            'Mime-Version'  => '1.0',
            'X-Mailer'      => 'libMail.php 1.0'
        );

        foreach($needleHeaders as $k=>$v) {
            $this->xheaders[$k] = $v;
        }

        return $this;
    }
    /*
    * @param string $message
    * Сообщение
    */
    private final function log($message, $type = MAIL_LOG) {
        $this->log_stack[] = array(
            'message' => $message,
            'type'    => $type,
            'time'    => date('H:i')
        );
    }

    /*
     * @param string $charset
     * Кодировка письма
    */

    public function __construct($charset = 'utf-8')
    {
        $this->boundary = "--" . md5(uniqid("myboundary"));
        $this->charset = strtolower($charset);
        $this->xheaders["X-Priority"] = $this->priorities[3];

        if ($this->charset == "us-ascii") {
            $this->ctencoding = "7bit";
        }
    }

    public function __destruct() {
        if($this->_SMTP_CONNECTION != 0) {
            $send = 'QUIT' . MAIL_ENDL;
            fputs($this->_SMTP_CONNECTION, $send);
            $this->log('I: ' . $send);

            $this->log('ANSWER: ' . $this->get_data($this->_SMTP_CONNECTION));
            fclose($this->_SMTP_CONNECTION);
        }
        $GLOBALS['mail_log'] = $this->getLog(true);
    }

    /*
     * @param bool $check
     * @return Mail
     * Проверять ли на валидность email адреса
    */
    public function autoCheck($check)
    {
        $this->checkAddress = (bool)$check;
        return $this;
    }

    /*
     * @param bool $html
     * @return Mail
     * Формат письма - простой текст или html
    */
    public function html($html)
    {
        $this->formatType = ((bool)$html) ? 'text/html' : 'text/plain';
        return $this;
    }

    /*
     * @param string $subject
     * @return Mail
     * Добавляет тему письма
    */
    function Subject($subject)
    {
        $subject = trim($subject);
        if (!is_string($subject) || $subject == '') {
            return false;
        }
        $this->xheaders['Subject'] = "=?" . $this->charset . "?Q?" . str_replace("+", "_", str_replace("%", "=", urlencode(strtr($subject, "\r\n", "  ")))) . "?=";
        return $this;
    }

    /*
     * @param Array $from Адрес отправителя
     * @return Mail || false
     * Добавляет заголовок автора письма
    */

    function From($from)
    {
        if ($this->use_smtp) {
            $this->log('Невозможно сменить отправителя при использовании SMTP', MAIL_NOTICE);
            return $this;
        }
        if(is_array($from)) {
            $name = $from['name'];
            $from = $from['from'];
        }
        if (!is_string($from) || !$this->isValidEmail($from)) {
            $this->log('Неверные данные адреса отправителя! ' . $from, MAIL_FATAL_ERROR);
            return false;
        }
        if(!empty($name)) {
            $this->names_email['from'] = $name;
        }
        else {
            $this->names_email['from'] = '';
        }
        $this->xheaders['From'] = $from;
        return $this;
    }

    /*
     * @param string $to Адрес для ответа
     * @param string $name [optional] Имя получателя ответа
     * @return Mail || false
     * Добавляет адрес для ответа на письмо
    */
    function ReplyTo($to, $name = false)
    {
        if (!is_string($to)) {
            return false;
        }

        if($name && is_string($name)) {
            $this->names_email['Reply-To'] = $name;
        }
        else {
            $this->names_email['Reply-To'] = '';
        }
        $this->xheaders['Reply-To'] = $to;
        return $this;
    }

    /*
     * @param bool $add
     * @return Mail
     * Добавление заголовка для получения уведомления о прочтении.
     * Обратный адрес берется из "From" (или из "Reply-To" если указан)
    */
    function Receipt($add)
    {
        $this->receipt = (bool)$add;
        return $this;
    }

    /*
     * @param string || bool $to
     * @return Mail
     * Добавление получателей письма.
    */
    function To($to)
    {
        if(is_string($to)) {
            $to = array($to);
        }

        if(is_array($to)) {
            foreach ($to as $value) // перебираем массив и добавляем в массив для отправки через smtp
            {
                $arrTemp = explode('; ', $value); // разбиваем по разделителю для выделения имени
                if (sizeof($arrTemp) == 2)        // если удалось разбить на два элемента
                {
                    $this->names_email['To'][$arrTemp[1]] = $arrTemp[0]; // имя
                    $this->sendTo[$arrTemp[1]] = $arrTemp[1]; //почта
                } else // или если имя не определено
                {
                    $this->names_email['To'][$value] = '';
                    $this->sendTo[$value] = $value;
                }
            }
        }
        else {
            return false;
        }
        return $this;
    }

    /*
     * @param mixed string || array $cc
     * @return mixed Mail || false
     * Добавление открытых получателей письма (Заголовок CC).
     * Получатели будут видеть, куда ушла копия
    */
    function CC($cc)
    {
        if (is_array($cc)) {
            foreach ($cc as $value)
            {
                $this->_CC[$value] = $value;
                //$this->sendTo[$value] = $value; // ключи и значения одинаковые, чтобы исключить дубли адресов
            }
        } elseif(is_string($cc)) {
            $this->_CC[$cc] = $cc;
            //$this->sendTo[$cc] = $cc; // ключи и значения одинаковые, чтобы исключить дубли адресов
        }
        else {
            return false;
        }
        return $this;
    }

    /*
     * @param mixed string || array $bcc
     * @return mixed Mail || false
     * Добавление открытых получателей письма (Заголовок CC).
     * Получатели будут видеть, куда ушла копия
    */
    function BCC($bcc)
    {
        if (is_array($bcc)) {
            foreach ($bcc as $value) // перебираем массив и добавляем в массив для отправки через smtp
            {
                $this->_BCC[$value] = $value;
                $this->sendTo[$value] = $value; // ключи и значения одинаковые, чтобы исключить дубли адресов
            }
        } elseif(is_string($bcc)) {
            $this->_BCC[$bcc] = $bcc;
            $this->sendTo[$bcc] = $bcc; // ключи и значения одинаковые, чтобы исключить дубли адресов
        }
        return $this;
    }

    /*
     * @param string $body
     * "return mixed Mail || false
     * Добавляет тело сообщения
     */
    function Body($body)
    {
        if(is_string($body)) {
            $this->body = $body;
            return $this;
        }
        return false;
    }

    /*
     * @param string $text
     * "return mixed Mail || false
     * Добавляет подпись к телу каждого сообщения перед отправкой
     */
    function Footer($text)
    {
        if(is_string($text)) {
            $this->signature = $text;
            return $this;
        }
        return false;
    }

    /*
     * @param string $org
     * return mixed Mail || false;
     */
    function Organization($org)
    {
        $org = trim($org);
        if ($org != "") {
            $this->xheaders['Organization'] = $org;
            return $this;
        }
        else {
            return false;
        }
    }

    /*
     * @param integer $priority
     * @return mixed Mail || false
     */
    function Priority($priority)
    {
        if (!isset($this->priorities[$priority])){
            $this->log('Неверное значение приоритета письма!', MAIL_NOTICE);
        }
        else {
            $this->xheaders["X-Priority"] = $this->priorities[$priority];
        }
        return $this;
    }


    /*
     * @param string $file  // путь к файлу, который надо отправить
     * @param string $name  // реальное имя файла
     * @param string $type  //MIME-тип файла
     * @param string        //как отображать прикрепленный файл ("inline") как часть письма или ("attachment") как прикрепленный файл
    */
    function Attach($file, $name = "", $type = "application/x-unknown-content-type", $disposition = "attachment")
    {
        $this->attach[] = array(
            'file' => $file,
            'name' => $name,
            'type' => $type,
            'disposition' => $disposition
        );
    }

    /*
     * Формирование письма
    */
    function BuildMail()
    {
        $this->headers = "";

        //Установка заголовков по умолчанию
        if ($this->charset != "") {
            $this->xheaders["Content-Type"] = $this->formatType . '; charset=' . $this->charset;
            $this->xheaders["Content-Transfer-Encoding"] = $this->ctencoding;
        }

        // создание заголовка TO.
        // добавление имен к адресам
        $temp_mass = array();
        foreach ($this->sendTo as $value) {
            if (isset($this->names_email['To'][$value]) && strlen($this->names_email['To'][$value])) {
                $temp_mass[] = "=?" . $this->charset . "?Q?" . str_replace("+", "_", str_replace("%", "=", urlencode(strtr($this->names_email['To'][$value], "\r\n", "  ")))) . "?= <" . $value . ">";
            }
            else $temp_mass[] = $value;
        }

        $this->xheaders['To'] = implode(", ", $temp_mass); // этот заголовок будет не нужен при отправке через mail()

        if (sizeof($this->_CC) > 0) {
            $this->xheaders['CC'] = implode(", ", $this->_CC);
        }

        if (sizeof($this->_BCC) > 0){
            $this->xheaders['BCC'] = implode(", ", $this->_BCC);
        }


        if ($this->receipt) {
            if (isset($this->xheaders["Reply-To"]))
                $this->xheaders["Disposition-Notification-To"] = $this->xheaders["Reply-To"];
            else
                $this->xheaders["Disposition-Notification-To"] = $this->xheaders['From'];
        }

        //TODO: files
        // вставаляем файлы
        if (sizeof($this->attach) > 0) {
            $this->_build_attachement();
        } else {
            $this->fullBody = $this->body;
        }

        $this->fullBody .= $this->signature;


        // создание заголовков если отправка идет через smtp
        if ($this->use_smtp) {

            // разбиваем (FROM - от кого) на юзера и домен. домен понадобится в заголовке
            $user_domen = explode('@', $this->xheaders['From']);

            $this->headers = 'Date: ' . date("D, j M Y G:i:s") . ' +0700' . MAIL_ENDL;
            $this->headers = 'Date: ' . date("D, j M Y G:i:s") . ' +0700' . MAIL_ENDL;
            $this->headers .= 'Message-ID: <' . rand() . '.' . date("YmjHis") . '@' . $user_domen[1] . '>' . MAIL_ENDL;


            reset($this->xheaders);
            while (list($hdr, $value) = each($this->xheaders)) {
                if ($hdr == "From" and strlen($this->names_email['from'])) $this->headers .= $hdr . ": =?" . $this->charset . "?Q?" . str_replace("+", "_", str_replace("%", "=", urlencode(strtr($this->names_email['from'], "\r\n", "  ")))) . "?= <" . $value . ">\r\n";
                elseif ($hdr == "Reply-To" and strlen($this->names_email['Reply-To'])) $this->headers .= $hdr . ": =?" . $this->charset . "?Q?" . str_replace("+", "_", str_replace("%", "=", urlencode(strtr($this->names_email['Reply-To'], "\r\n", "  ")))) . "?= <" . $value . ">\r\n"; elseif ($hdr != "BCC") $this->headers .= $hdr . ": " . $value . "\r\n"; // пропускаем заголовок для отправки скрытой копии

            }


        } // создание заголовоков, если отправка идет через mail()
        else {
            reset($this->xheaders);
            while (list($hdr, $value) = each($this->xheaders)) {
                if ($hdr == "From" and strlen($this->names_email['from'])) $this->headers .= $hdr . ": =?" . $this->charset . "?Q?" . str_replace("+", "_", str_replace("%", "=", urlencode(strtr($this->names_email['from'], "\r\n", "  ")))) . "?= <" . $value . ">\r\n";
                elseif ($hdr == "Reply-To" and strlen($this->names_email['Reply-To'])) $this->headers .= $hdr . ": =?" . $this->charset . "?Q?" . str_replace("+", "_", str_replace("%", "=", urlencode(strtr($this->names_email['Reply-To'], "\r\n", "  ")))) . "?= <" . $value . ">\r\n"; elseif ($hdr != "Subject" and $hdr != "To") $this->headers .= "$hdr: $value\n"; // пропускаем заголовки кому и тему... они вставятся сами
            }
        }


    }

    /*
     * @param string $server
     * @param string $login
     * @param string $password
     * @param integer $port
     * @param integer $timeout
     * @return Mail
    */
    public function smtp_on($from, $server, $login, $password, $port = 25, $timeout = 5)
    {

        $q = $this->From($from);

        if($q) {
            $this->use_smtp = true; // включаем отправку через smtp
            $this->smtp['server'] = $server;
            $this->smtp['login'] = $login;
            $this->smtp['password'] = $password;
            $this->smtp['port'] = $port;
            $this->smtp['timeout'] = $timeout;
        }
        return $q;
    }

    private function get_data($connection)
    {
        $data = "";
        while ($str = fgets($connection, 515)) {
            $data .= $str;
            if (substr($str, 3, 1) == " ") {
                break;
            }
        }
        return $data;
    }

    /*
    * Отправка письма
    */
    public function Send()
    {
        $this->BuildMail();

        //TODO: проверка, не пустой ли список для отправки
        // если отправка без использования smtp
        if (!$this->use_smtp) {
            return mail(implode(", ", $this->sendTo), $this->xheaders['Subject'], $this->fullBody, $this->headers);
        } else // если через smtp
        {
            if(!$this->smtp['server'] OR !$this->smtp['login'] OR !$this->smtp['password'] OR !$this->smtp['port']) {
                $this->log("Не заданы необходимые параметры для соединения!", MAIL_FATAL_ERROR);
                return false;
            }

            // разбиваем (FROM - от кого) на юзера и домен. юзер понадобится в приветствии с сервером
            $user_domain = explode('@', $this->xheaders['From']);

            if($this->_SMTP_CONNECTION == 0) {
                $this->_SMTP_CONNECTION = fsockopen($this->smtp['server'], $this->smtp['port'], $errno, $errstr, $this->smtp['timeout']);

                if(!$this->_SMTP_CONNECTION) {
                    $this->log('Connection to "' . $this->smtp['server'] . '" failed: <Errno ' . $errno . '> ' . $errstr);
                }

                $data = $this->get_data($this->_SMTP_CONNECTION);
                $this->log('ANSWER: ' . $data);

                $send = "EHLO " . $user_domain[0] . MAIL_ENDL;
                fputs($this->_SMTP_CONNECTION, $send);
                $this->log("I: " . $send);

                $data = $this->get_data($this->_SMTP_CONNECTION);
                $this->log('ANSWER: ' . $data);

                $code = (int)substr($data, 0, 3); // получаем код ответа

                if ($code != 250) {
                    $this->log('Ошибка приветсвия EHLO: Response code='.$code, MAIL_FATAL_ERROR);
                    fclose($this->_SMTP_CONNECTION);
                    return false;
                }

                $send = 'AUTH LOGIN' . MAIL_ENDL;
                fputs($this->_SMTP_CONNECTION, $send);
                $this->log("I: " . $send);

                $data = $this->get_data($this->_SMTP_CONNECTION);
                $this->log('ANSWER: ' . $data);
                $code = (int)substr($data, 0, 3);

                if ($code != 334) {
                    $this->log('SMTP: Сервер не разрешил начать авторизацию', MAIL_FATAL_ERROR);
                    fclose($this->_SMTP_CONNECTION);
                    return false;
                }

                $send = base64_encode($this->smtp['login']) . MAIL_ENDL;
                fputs($this->_SMTP_CONNECTION, $send);
                $this->log("I: " . $send);

                $data = $this->get_data($this->_SMTP_CONNECTION);
                $this->log('ANSWER: ' . $data);
                $code = (int)substr($data, 0, 3);

                if ($code != 334) {
                    $this->log('SMTP: Пользователь не найден', MAIL_FATAL_ERROR);
                    fclose($this->_SMTP_CONNECTION);
                    return false;
                }

                $send = base64_encode($this->smtp['password']) . MAIL_ENDL;
                fputs($this->_SMTP_CONNECTION, $send);
                $this->log('I: ' . (($this->showPassword == true) ? $send : '*Password*'));

                $data = $this->get_data($this->_SMTP_CONNECTION);
                $this->log('ANSWER: ' . $data);
                $code = (int)substr($data, 0, 3);

                if ($code != 235) {
                    $this->log('SMTP: Неверный пароль', MAIL_FATAL_ERROR);
                    fclose($this->_SMTP_CONNECTION);
                    return false;
                }
            }

            $smtp_connection = $this->_SMTP_CONNECTION;

            if (!$smtp_connection) {
                $this->log('SMTP: Не удалось соединиться с сервером. (ERRNO: {' . $errno . '}, ERRSTR: ' . $errstr);
                return false;
            }

            $send = "MAIL FROM:<" . $this->xheaders['From'] . "> SIZE=" . strlen($this->headers . MAIL_ENDL . $this->fullBody) . MAIL_ENDL;
            fputs($smtp_connection, $send);
            $this->log('I: ' . $send);

            $data = $this->get_data($smtp_connection);
            $this->log('ANSWER: ' . $data);
            $code = (int)substr($data, 0, 3);

            if ($code != 250) {
                $this->log('SMTP: Сервер отказал в команде MAIL FROM', MAIL_FATAL_ERROR);
                fclose($smtp_connection);
                return false;
            }

            foreach ($this->sendTo as $email) {
                $send = 'RCPT TO:<' . $email . '>' . MAIL_ENDL;
                fputs($smtp_connection, $send);
                $this->log('I: ' . $send);

                $data = $this->get_data($smtp_connection);
                $this->log('ANSWER: ' . $data);
                $code = (int)substr($data, 0, 3);

                if ($code != 250 AND $code != 251) {
                    $this->log('SMTP: Сервер не принял команду RCPT TO', MAIL_FATAL_ERROR);
                    fclose($smtp_connection);
                    return false;
                }
            }

            $send = 'DATA' . MAIL_ENDL;
            fputs($smtp_connection, $send);
            $this->log('I: ' . $send);

            $data = $this->get_data($smtp_connection);
            $this->log('ANSWER: ' . $data);
            $code = (int)substr($data, 0, 3);

            if ($code != 354) {
                $this->log('Сервер не принял команду DATA', MAIL_FATAL_ERROR);
                fclose($smtp_connection);
                return false;
            }

            $send = $this->headers . MAIL_ENDL . $this->fullBody . MAIL_ENDL . '.' . MAIL_ENDL;
            fputs($smtp_connection, $send);
            $this->log('I: ' . $send);

            $data = $this->get_data($smtp_connection);
            $this->log('ANSWER: ' . $data);
            $code = (int)substr($data, 0, 3);

            if ($code != 250) {
                $this->log('SMTP: Ошибка отправки письма', MAIL_FATAL_ERROR);
                fclose($smtp_connection);
                return false;
            }

            return true;
        }
    }

    /*
     * @param bool $html = true
     * @return string
    */
    public function getLog($html = true, $rawArray = false)
    {
        $style = array(
            MAIL_FATAL_ERROR => 'color: red; font-weight: bold',
            MAIL_WARNING => 'color: red',
            MAIL_LOG => 'color: green',
            MAIL_NOTICE => 'color: blue'
        );
        $type = array(
            MAIL_FATAL_ERROR => 'FATAL ERROR: ',
            MAIL_WARNING => 'WARNING: ',
            MAIL_LOG => '',
            MAIL_NOTICE => 'NOTICE: '
        );

        if($rawArray) {
            return $this->log_stack;
        }
        elseif($html) {
            $html = '';
            foreach($this->log_stack as $ar) {
                $html .= '<pre style="margin: 5px 0;' . $style[$ar['type']] . '">' . htmlspecialchars($ar['message']) . '</pre>';
            }
            return '<div style="border: 1px solid green; background: black; padding: 8px 15px;">' . $html . '</div>';
        }
        else {
            $text = '';
            foreach($this->log_stack as $ar) {
                $text .= $type[$ar['type']] . $ar['message'];
            }
            return $text;
        }
    }

    /*
     * @param string $address
     * @return bool
     * Проверка валидности адреса
    */
    public final function isValidEmail($address)
    {
        return filter_var($address, FILTER_VALIDATE_EMAIL);
    }

    /*
    сборка файлов для отправки
    */

    function _build_attachement()
    {
        // TODO: файлы
        $this->xheaders["Content-Type"] = "multipart/mixed;\n boundary=\"" . $this->boundary . "\"";

        $this->fullBody = "This is a multi-part message in MIME format.\n--$this->boundary\n";
        $this->fullBody .= 'Content-Type: ' . $this->formatType . '; charset=' . $this->charset . MAIL_ENDL . 'Content-Transfer-Encoding: ' . $this->ctencoding;

        $sep = chr(13) . chr(10);

        $ata = array();
        $k = 0;

        // перебираем файлы
        $filesCount = sizeof($this->attach);
        for ($i = 0; $i < $filesCount; $i++) {

            $filename = $this->aattach[$i];

            $webi_filename = $this->webi_filename[$i]; // имя файла, которое может приходить в класс, и имеет другое имя файла
            if (strlen($webi_filename)) $basename = basename($webi_filename); // если есть другое имя файла, то оно будет таким
            else $basename = basename($filename); // а если нет другого имени файла, то имя будет выдернуто из самого загружаемого файла

            $ctype = $this->actype[$i]; // content-type
            $disposition = $this->adispo[$i];

            if (!file_exists($filename)) {
                echo "ошибка прикрепления файла : файл $filename не существует";
                exit;
            }
            $subhdr = "--$this->boundary\nContent-type:
            $ctype;\n name=\"$basename\"\nContent-Transfer-Encoding: base64\nContent-Disposition: $disposition;\n
            filename=\"$basename\"\n";

            $ata[$k++] = $subhdr;

            // non encoded line length
            $linesz = filesize($filename) + 1;
            $fp = fopen($filename, 'r');
            $ata[$k++] = chunk_split(base64_encode(fread($fp, $linesz)));
            fclose($fp);
        }
        $this->fullBody .= implode($sep, $ata);
    }
}
