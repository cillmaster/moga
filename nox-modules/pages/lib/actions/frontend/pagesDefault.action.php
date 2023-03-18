<?php
/**
 * Действие отображения страницы
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.1
 * @package    pages
 */

class pagesDefaultAction extends noxThemeAction
{
    public $cache = false;

    public function execute()
    {
        $model = new pagesModel();

        //Ищем страницу по URL
        $page = $model->getByUrl(noxSystem::$requestPath);

        //Если не найдено, то ошибка 404
        if (!$page)
        {
            return 404;
        }

        switch ($page['published'])
        {
            //Страница выключена
            case 0:
                return 404;
                break;
            //Доступна только администраторам
            case 1:
                if (!$this->haveRight('control'))
                {
                    return 401;
                }
                break;
            //Доступна только зарегистрированным
            case 2:
                if (!noxSystem::authorization())
                {
                    return 401;
                }
                break;
        }

        $raw = explode("\n", $page['text'], 2);
        $page['text'] = $raw[1] ?: $raw[0];
        $page['crumb'] = isset($raw[1]) ? $raw[0] : '';

        $this->setTheme($page['theme']);
        if($page['theme'] == 'default'){
            $this->templateFileName = $this->moduleFolder . '/templates/frontend/' . $page['template'] . '.html';
        }
        $this->title = $page['title'];
        $this->caption = $page['caption'];
        $this->addMetaKeywords($page['meta_keywords']);
        $this->addMetaDescription($page['meta_description']);
        $this->addVar('page', $page);
        $this->addVar('locale', $page['locale']);
    }
}