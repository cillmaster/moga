<?php
/**
 * noxTemplate
 *
 * Класс для обработки файлов шаблонов
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.2
 * @package    nox-system
 * @subpackage output
 */

class noxTemplate
{
    /**
     * Исходный код шаблона
     *
     * @var string
     */
    protected $templateText = '';

    /**
     * Имя файла шаблона
     *
     * @var string
     */
    public $fileName = '';

    /**
     * PHP код шаблона
     *
     * @var string
     */
    protected $templatePhp = '';

    /**
     * Переменные для шаблона
     *
     * @var string
     */
    public $vars = array();

    /**
     * Конструктор
     *
     * @param string $filename имя файла шаблона
     */
    public function __construct($filename = '')
    {
        //Получаем содержимое файла
        if (!empty($filename))
        {
            $this->loadFromFile($filename);
        }
        //$this->addVar('moduleFolder',  noxSystem::$moduleFolder);
        //$this->addVar('moduleUrl',  noxSystem::$moduleUrl);
        $this->addVar('version', noxSystem::$version);
        $this->addVar('ajax', noxSystem::$ajax);
        $this->addVar('url', noxSystem::$requestUrl);
        $this->addVar('requestUrl', noxSystem::$requestUrl);
        $this->addVar('requestPath', noxSystem::$requestPath);
        $this->addVar('baseUrl', noxSystem::$baseUrl);
        $this->addVar('moduleUrl', noxSystem::$moduleUrl);
        $this->addVar('actionUrl', noxSystem::$actionUrl);
        $this->addVar('fullUrl', noxSystem::$fullUrl);
        $this->addVar('urlArray', noxSystem::$urlArray);
        $this->addVar('domain', noxSystem::$domain);
        $this->addVar('locale', noxLocale::$locale);
        $this->addVar('theme', noxSystem::$theme);
        $this->addVar('themeFolder', noxSystem::$baseUrl. '/' . ltrim(noxSystem::$themeFolder,'/'));
        $this->addVar('commonFolder', noxSystem::$baseUrl.'/nox-themes/common');
        $this->addVar('fakeSearchTerm', '{search_term}');
    }

    /**
     * Загрузка шаблона из файла
     *
     * @param string $filename имя файла шаблона
     * @return bool
     */
    public function loadFromFile($filename)
    {
        //_d($filename);
        $filename = noxRealPath($filename);
        //Получаем содержимое файла
        if (file_exists($filename) && is_file($filename))
        {
            //Проверяем, существует ли кэш для этого файла?
            if ($this->templatePhp = noxSystemCache::get($filename, filemtime($filename), true))
            {
                $this->templateText = '';
            } else
            {
                $this->templateText = file_get_contents($filename);
                $this->templatePhp = '';
            }
            $this->fileName = $filename;
            $this->vars['templateFolder'] = @dirname($filename);
            return true;
        }
        return false;
    }

    /**
     * Добавляет переменную в список переменных
     *
     * @param string $name обозначение блока
     * @param string $text содержимое блока
     * @return noxTemplate
     */
    public function addVar($name, $text)
    {
        $this->vars[$name] = $text;
        return $this;
    }

    /**
     * Добавляет массив переменных в список переменных
     *
     * @param $array массив переменных
     * @return noxTemplate
     */
    public function addVars($array)
    {
        $this->vars = array_merge($this->vars, $array);
        return $this;
    }


    /**
     * Callback функция для замены блока на код получения этого блока
     *
     * @callback
     * @param array $matches
     * @return string
     */
    public function getBlock($matches)
    {
        $t = $matches[1];

        //Находим имя блока
        preg_match('/^\[([^\[\]]*?)\](.*)/is', $t, $ar);
        //Сохраняем имя блока
        $blockName = $ar[1];

        $params = '';

        //Разбираем параметры
        if (!empty($ar[2]))
        {
            $k = 0;
            //Добавляем к началу и концу символы для разделителя
            foreach (explode('][', ']' . $ar[2] . '[') as $v)
            {
                if ($v === '')
                {
                    continue;
                }
                //Если переменная, то выводим без кавычек
                if ($v[0] == '$')
                {
                    $params .= $k . ' => ' . $v . ',';
                } else
                {
                    $params .= $k . ' => \'' . $v . '\',';
                }
                $k++;
            }
            $params = "\t\t" . '$action->params = array_merge($action->params, array(' . $params . '));';
        }

        $text = '<?php ' .
            '
		$blocks = noxConfig::getBlocks();
		if (isset($blocks[\'' . $blockName . '\']))
		{
			$block = $blocks[\'' . $blockName . '\'];
			$actionName = @$block[\'module\'].ucfirst(@$block[\'section\']).ucfirst(@$block[\'action\']).\'Action\';

			$initPath = noxRealPath(\'nox-modules/\' . @$block[\'module\'] . \'/lib/config/init.php\');
            //Проверяем, существует ли файл инициализации
            if (file_exists($initPath))
            {
                    include_once($initPath);
            }

			if (class_exists($actionName, true))
			{
				$action = new $actionName();
                $action->params[\'block\'] = true;
		' . $params . '
				$action->run();
			} else
			{
			    $actionName = $block[\'module\'].ucfirst($block[\'section\']).\'Actions\';
			    if (class_exists($actionName, true))
                {
                    $action = new $actionName();
            ' . $params . '
                    $action->run($block[\'action\']);
                }
			}
		} ?>';
        return $text;
    }

    /**
     * Функция обрабатывает шаблон и возвращает его в виде PHP кода
     *
     * Все теги щаблонизатора прописаны здесь
     * @return string
     */
    public function getPhpCode()
    {
        //Если код пустой (шаблон ещё не обрабатывался)
        if (empty($this->templatePhp))
        {
            //Обрабатываем теги, заменяя их на PHP код. Заполняем шаблоны для замены

            //[field] => ['field']
            //$search[] = '/\{([^\t\r\n\{\}]*)\[([^\$\"`\'\n\r\{\}\[\]]*?)\]([^\t\r\n\{\}]*)\}/is';
            //$replace[] = '{$1[\'$2\']$3}';

            $search[] = '/(\$[^\t\r\n\{\}]*)\[([^\$\"`\'\n\r\{\}\[\]]*?)\]/is';
            $replace[] = '$1[\'$2\']';

            $search[] = '/(\$[^\t\r\n\{\}]*?)\[([^\$\"`\'\n\r\{\}\[\]]*?)\]/is';
            $replace[] = '$1[\'$2\']';

            //{foreach $array as $element}
            $search[] = '/\{foreach ([^ ]*?) as ([^ ]*?)\}/is';
            $replace[] = '<?php if (isset($1) && $1) foreach ($1 as $2) { ?>';

            //{for $i=a to b}
            $search[] = '/\{for ([^ ]*?)=([^ ]*?) to ([^ ]*?)\}/is';
            $replace[] = '<?php for ($1=$2; $1<=$3; $1++) { ?>';

            //{for $i=a to b step c}
            $search[] = '/\{for ([^ ]*?)=([^ ]*?) to ([^ ]*?) step ([^ ]*?)\}/is';
            $replace[] = '<?php for ($1=$2; $1<=$3; $1+=$4) { ?>';

            //{for $i=a downto b}
            $search[] = '/\{for ([^ ]*?)=([^ ]*?) downto ([^ ]*?)\}/is';
            $replace[] = '<?php for ($1=$2; $1>=$3; $1--) { ?>';

            //{for $i=a downto b step c}
            $search[] = '/\{for ([^ ]*?)=([^ ]*?) downto ([^ ]*?) step ([^ ]*?)\}/is';
            $replace[] = '<?php for ($1=$2; $1>=$3; $1-=$4) { ?>';

            //{if $value}
            $search[] = '/\{if (.*?)\}/is';
            $replace[] = '<?php if (@($1)) { ?>';

            //{elseif $value}
            $search[] = '/\{elseif (.*?)\}/is';
            $replace[] = '<?php } elseif (@($1)) { ?>';

            //{function ...}
            $search[] = '/\{function ([^\{\}]*?)\}/is';
            $replace[] = '<?php function $1 { ?>';

            //{eval ...}
            $search[] = '/\{eval (.*?)\}/is';
            $replace[] = '<?php $1; ?>';

            //{end}
            $search[] = '/\{end\}/is';
            $replace[] = '<?php } ?>';

            //{break}
            $search[] = '/\{break\}/is';
            $replace[] = '<?php break; ?>';

            //{continue}
            $search[] = '/\{continue\}/is';
            $replace[] = '<?php continue; ?>';

            //{else}
            $search[] = '/\{else\}/is';
            $replace[] = '<?php } else { ?>';

            //{year}
            $search[] = '/\{year\}/is';
            $replace[] = date('Y', time());

            //{$element[field]}
            $search[] = '/([^\{])\{([^\t\n\r\{\}<\?\>]*?)\}/is';
            $replace[] = '$1<?php echo @$2; ?>';


            /*Оптимизация: вырезка последовательных тегов ?><?php */
            $search[] = '/\?>([\t\r\n]*)<\?php/is';
            $replace[] = '';


            /*Оптимизация: вырезка кучи ненужных табов */

            $search[] = '/\?>([\t]*?)$/is';
            $replace[] = '?>';
            $search[] = '/^([\t]*?)<\?php/is';
            $replace[] = '<?php';


            $search[] = '/([\t]{2,})/is';
            $replace[] = '';

            /*
            //Из-за этого не работают некоторые JS вставки
            $search[] = '/\?>([\n\r]{2,})(.*?)([\n\r]{2,})<\?php/is';
            $replace[] = '?>$2<?php';

            //Делаем дополнительный перенос строки, чтобы php его не "съедал"
            /*$search[] = '/\?>\r\n/i';
            $replace[] = "?>\r\n\r\n";
            */

            //Обрабатывает шаблон с помощью регулярного выражения
            $this->templateText = preg_replace_callback('/\{\$blocks(\[.*?)\}/is', array($this, 'getBlock'), $this->templateText);

            //Заменяем
            $this->templatePhp = preg_replace($search, $replace, $this->templateText);

            noxSystemCache::create($this->fileName, $this->templatePhp);
            /***************************************
            Сделать кэширование результата обработки шаблона с проверкой даты изменения файлов
             ***************************************/
        }
        return $this->templatePhp;
    }

    /**
     * Возвращает весь вывод в виде, обрабатывая теги
     *
     * @return string
     */
    public function __toString()
    {
        /*
        //Делаем заданные переменные локальными
        if (isset($GLOBALS['vars']))
        {
            foreach ($GLOBALS['vars'] as $name => $value)
            {
                $$name = $value;
            }
        }
        foreach ($this->vars as $name => $value)
        {
            $$name = $value;
        }
        */
        if (isset($GLOBALS['vars']) && is_array($GLOBALS['vars']))
        {
            extract($GLOBALS['vars'], EXTR_OVERWRITE);
        }
        if (isset($this->vars) && is_array($this->vars))
        {
            extract($this->vars, EXTR_OVERWRITE);
        }

        //_d($this->templatePhp);

        //Начинаем буферизацию
        ob_start();
        //Выполняем код из шаблона
        eval('?>' . $this->getPhpCode());
        $text = ob_get_contents();
        ob_end_clean();

        //Заменяем перевод
        $text = preg_replace_callback('/\[`(.+?)`]/is', create_function('$matches', 'return _t($matches[1]);'), $text);

        //Возвращаем результат
        return $text;
    }
}

?>