<?php
/**
 * noxTemplateAction
 *
 * Класс действия с шаблоном
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.2
 * @package    nox-system
 * @subpackage view-action
 */

class noxTemplateAction extends noxAction
{
    /**
     * Путь к шаблону действия (путь по-умолчанию задается автоматически)
     * @var string
     */
    protected $templateFileName = '';

    /**
     * Массив с переменными
     * @var array
     */
    public $vars = array();

    /**
     * Добавляет переменную в шаблон
     * @param string $name Имя переменной
     * @param mixed $value Значение переменной
     * @return noxTemplateAction
     */
    public function addVar($name, $value = false)
    {
        if (is_array($name))
        {
            $this->vars = array_merge($this->vars, $name);
        } else
        {
            $this->vars[$name] = $value;
        }
        return $this;
    }

    /**
     * Функция подготавливает строку результата (так же этот результат используется для кэширования)
     * @return string
     */
    public function run()
    {
        //1)Проверяем кеш
        $response = $this->loadFromCache();
        if ($response !== false)
        {
            echo $response;
            return 200;
        }

        //2) Если данных нет, выполняем действия

        $this->addVar('moduleFolder', $this->moduleFolder);

        //Загрузка локали
        noxLocale::add($this->moduleFolder.'/locale/'.noxLocale::$locale.'.php');

        //Выполняем действие
        ob_start();
        $code = $this->execute();
        if (!$code) $code = 200;

        if ($code == 200)
        {
            $response = ob_get_contents();

            //3) Обрабатываем шаблон, если вывода не было
            if (empty($response))
            {
                $template = new noxTemplate();
                //Если имя шаблона не определено
                if (empty($this->templateFileName))
                {
                    if (empty($this->section))
                    {
                        $this->action = strtolower($this->action);
                    }
                    //То составляем его. Если фронтенд или backend
                    $this->templateFileName = $this->moduleFolder . '/templates/' .
                        (noxSystem::$params['frontend'] ? 'frontend/' :
                            (noxSystem::$params['backend'] ? 'backend/' : '') )
                        .$this->section . $this->action . '.html';

                    if (!file_exists(noxRealPath($this->templateFileName)))
                    {
                        $this->templateFileName = $this->moduleFolder . '/templates/' . $this->section . $this->action . '.html';
                    }
                }
                //Загружаем шаблон
                $template->loadFromFile($this->templateFileName);

                //Добавляем к нему переменные
                $template->addVars($this->vars);
                //Выводим шаблон
                echo $template;
                //Получаем данные
                $response = ob_get_contents();
				ob_end_clean();
				
                $this->saveToCache($response);                
                echo $response;
            }
        } else
        {
            ob_end_clean();
        }
        return $code;
    }
}

?>