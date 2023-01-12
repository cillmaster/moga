<?php
/**
 * noxThemeAction
 *
 * Класс действия с шаблоном и темой
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.0
 * @package    nox-system
 * @subpackage view-action
 */

class noxThemeAction extends noxTemplateAction
{
    /**
     * Название темы для вывода
     * @var string
     */
    public $theme = '';

    /**
     * Шаблон темы
     * @var noxTemplate
     */
    private $themeTemplate;

    /**
     * Название страницы
     * @var string
     */
    public $caption = '';

    /**
     * Заголовок окна страницы
     * @var string
     */
    public $title = '';

    /**
     * Конструктор класса задает начальные параметры и создает переменные
     */
    public function __construct()
    {
        parent::__construct();

        //Получаем настройки сайта
        $config = noxConfig::getConfig();
        $this->addVar('siteTitle', $config['defaultTitle']);
        $this->themeTemplate = new noxTemplate();
        if (!$this->theme)
        {
            $this->theme = $config['defaultTheme'];
        }
        $this->vars['keywords'] = '';
        $this->vars['description'] = '';
        $this->vars['meta'] = '';
        $this->vars['js'] = array();
        $this->vars['css'] = array();
        $this->setTheme($this->theme);
    }

    /**
     * Задает тему для вывода
     * @param $theme
     * @throws noxException
     */
    public function setTheme($theme)
    {
        $this->theme = $theme;

        //Получаем темы
        $themes = noxConfig::getThemes();

        if (!isset($themes[$theme]))
        {
            throw new noxException('Тема &quot;' . $theme . '&quot; не найдена!');
        }

        $theme = $themes[$theme];

        //Загружаем файл шаблона
        if (!$this->themeTemplate->loadFromFile($theme['filename']))
        {
            throw new noxException('Файл темы &quot;' . $theme['filename'] . '&quot; не найден!');
        }
        $this->vars['themeFolder'] = noxSystem::$baseUrl . '/' . ltrim($theme['folder'], '/');

        //Сохраняем тему
        noxSystem::$theme = $theme;
        noxSystem::$themeFolder = noxSystem::$baseUrl . $this->vars['themeFolder'];
    }

    /**
     * Добавляет контент в блок head
     *
     * @param $meta string Контент
     * @return noxThemeAction
     */
    public function addMeta($meta)
    {
        $this->vars['meta'] .= $meta;
        return $this;
    }

    /**
     * Добавляет ключевые слова к странице
     *
     * @param $keywords string Ключевые слова
     * @return noxThemeAction
     */
    public function addMetaKeywords($keywords)
    {
        if($this->vars['keywords']) $this->vars['keywords'] .=  ', ';
        $this->vars['keywords'] .=  $keywords;
        return $this;
    }

    /**
     * Добавляет описание к странице
     *
     * @param $description string Описание
     * @return noxThemeAction
     */
    public function addMetaDescription($description)
    {
        $this->vars['description'] .= ' ' . $description;
        return $this;
    }

    /**
     * Добавляет к списку включений файл JavaScript
     *
     * @param mixed $fileName
     * @return noxThemeAction
     */
    public function addJs($fileName)
    {
        if (is_array($fileName))
        {
            $this->vars['js'] = array_merge($this->vars['js'], $fileName);
        }
        else
        {
            $this->vars['js'][] = $fileName;
        }
        return $this;
    }

    /**
     * Добавляет к списку включений файл CSS
     *
     * @param mixed $fileName FILE
     * @return noxThemeAction
     */
    public function addCss($fileName)
    {
        if (is_array($fileName))
        {
            $this->vars['css'] = array_merge($this->vars['css'], $fileName);
        }
        else
        {
            $this->vars['css'][] = $fileName;
        }
    }

    /**
     * Функция подготавливает строку результата (так же этот результат используется для кэширования)
     * @return string
     */
    public function run()
    {
        //1)Проверяем кеш
        $response = $this->loadFromCache();

        //2) Если данных нет, выполняем действия
        if ($response === false)
        {
            //Начинаем буферизацию
            ob_start();

            $this->addVar('moduleFolder', $this->moduleFolder);

            //Загрузка локали
            noxLocale::add($this->moduleFolder . '/locale/' . noxLocale::$locale . '.php');

            //Выполняем действие
            $code = $this->execute();
            if (!$code) $code = 200;

            if ($code == 200)
            {

                $this
                    ->addVar('caption', $this->caption)
                    ->addVar('title', (empty($this->title)) ? $this->caption : $this->title);

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

                        if (!file_exists($this->templateFileName))
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
                }
                unset($template);

                ob_end_clean();
                $this->saveToCache($response);
			} else
			{
                //Exit
				ob_end_clean();
                return $code;
			}
		}

		//4) Обрабатывает тему
		//Если это не ajax запрос, то обрабатываем страницу
		if ($this->params['ajax'])
		{
			echo $response;
		}
		else
		{
            if(noxConfig::getConfig()['is_console'])
                $response = noxSystem::$console->write()  . $response;

            $this->vars['content'] = $response;

            if(!isset($this->vars['img_url'])){
                $this->vars['img_url'] = 'https://' . noxSystem::$domain . '/nox-themes/default/images/outlines-logo.png';
            }

			if(!isset($this->vars['locale'])){
			    $this->vars['locale'] = 'en';
            }

            //Составляем заголовок HTML файла
			$this->vars['head'] =
				'<meta name="keywords" content="' . $this->vars['keywords'] . '" />' . "\n" .
					'<meta name="description" content="' . trim($this->vars['description']) . '" />';

            //Добавляем canonical, если есть
			if(isset($this->vars['canonical'])){
                $this->vars['head'] .= "\n" . '<link rel="canonical" href="' . $this->vars['canonical'] . '"  />';
            }
            //Добавляем prev и next, если есть
            if(isset($this->vars['prevPage'])){
                $this->vars['head'] .= "\n" . '<link rel="prev" href="' . $this->vars['prevPage'] . '"  />';
            }
            if(isset($this->vars['nextPage'])){
                $this->vars['head'] .= "\n" . '<link rel="next" href="' . $this->vars['nextPage'] . '"  />';
            }
            //Добавляем image_src, если есть
            if(isset($this->vars['image_src'])){
                $this->vars['head'] .= "\n" . '<link rel="image_src" href="' . $this->vars['image_src'] . '"  />';
            }
			//Добавляем CSS, если есть
			if (count($this->vars['css']) > 0)
			{
				foreach ($this->vars['css'] as $file)
				{
					$this->vars['head'] .= "\n" . '<link rel="stylesheet" type="text/css" href="' . $file . '"  />';
				}
			}

			//Cart
            $this->vars['cartTotal'] = count(array_keys(noxSystem::$cart->getCartDetails()));

            // Angular controller
            $app = noxSystem::$application;
            $angularCtrl = sprintf('/nox-themes/default/app/ctrl/%s.%s.%s.js', $app->moduleName, strtolower($app->sectionName), strtolower($app->actionName));
            file_exists(noxRealPath($angularCtrl)) && $this->addJs($angularCtrl);
			//Добавляем JS, если есть
			if (count($this->vars['js']) > 0)
			{
				foreach ($this->vars['js'] as $file)
				{
					$this->vars['head'] .= "\n" . '<script type="text/javascript" src="' . $file . '"></script>';
				}
			}
			$this->vars['head'] .= $this->vars['meta'];
			$this->themeTemplate->addVars($this->vars);
            $this->themeTemplate->addVar('makeTopCars', (new printsMakeModel())->where([
                'class_id' => 1,
                'top' => 1
            ])->order('name')->fetchAll());

			echo $this->themeTemplate;
		}

        return 200;
    }
}

?>
