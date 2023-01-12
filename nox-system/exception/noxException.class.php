<?php
/**
 * noxException
 *
 * Общий класс исключения.
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.2
 * @package    nox-system
 * @subpackage core
 */

class noxException extends Exception
{
    /**
     * Радиус строк
     *
     * @var Целое число (int)
     */
    const CONTEXT_RADIUS = 5;

    private function getFileContext()
    {
        $file = $this->getFile();
        $line_number = $this->getLine();
        $context = array();
        $i = 0;
        foreach (file($file) as $line)
        {
            $i++;
            if ($i >= $line_number - self::CONTEXT_RADIUS && $i <= $line_number + self::CONTEXT_RADIUS)
            {
                $line = trim($line);
                if ($i == $line_number)
                {
                    $context[] = ' >>' . $i . "\t" . $line;
                } else
                {
                    $context[] = '   ' . $i . "\t" . $line;
                }
            }
            if ($i > $line_number + self::CONTEXT_RADIUS)
            {
                break;
            }
        }
        //Подветка синтаксиса
        return "\n" . highlight_string("<?php \n" . implode("\n", $context) . "\n?>", true);
    }

    /**
     * Возвращает текстовое описание ошибки
     *
     * @return string Текст ошибки
     */
    public function __toString()
    {
        $message = nl2br('<div>Oops, we got some technical updates. Please refresh our website in next few minutes.</div><div style="color:#fff;">' . $this->getMessage() . '</div>');

        $adminResult = <<<HTML
<style type="text/css">
    body, p, ol, ul, td { font-family: verdana, arial, helvetica, sans-serif; font-size: 13px; line-height: 25px; }
    pre { background-color: #eee; padding: 10px; font-size: 11px; line-height: 18px; }
</style>
<div style="width:99%; position:relative">
<h2 id='Title'>{$message}</h2>
<div id="Context" style="display: block;"><h3>Ошибка в '{$this->getFile()}' в строке {$this->getLine()}:</h3><pre>{$this->getFileContext()}</pre></div>
<div id="Trace"><h2>Стек вызова</h2><pre>{$this->getTraceAsString()}</pre></div>
HTML;
        $adminResult .= '<div id="System"><h2>noxSystem</h2><pre>' . noxSystem::dump() . '</pre></div>';
        $adminResult .= '</div>';

        $config = noxConfig::getConfig();


        if ($config['debug'])
        {
            @file_put_contents(noxRealPath('nox-cache/log.html'), $adminResult, FILE_APPEND);
            $result = $adminResult;
        } else
        {
            $result = $message;
        }

        //Отправка сообщения об ошибке администратору
        if ($config['sendEmailOnException'] && $config['defaultEmail'])
        {
            (new kafMailer('exception'))->mail([
                'from' => 'noreply',
                'to' => $config['defaultEmail'],
                'domain' => noxSystem::$domain,
                'exception' => $adminResult
            ]);
        }

        return $result;
    }
}

?>