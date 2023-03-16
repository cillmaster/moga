<?php
/**
 * Страница вывода категорий чертежа
 *
 * @author     <pa-nic@yandex.ru>
 * @version    1.0
 * @package    prints
 */

class printsVectorDefaultAction extends noxThemeAction
{
    public function execute()
    {
        $this->title =  $this->caption = 'Drawings';
        $this->addMetaDescription('Download drawings, buy or request scalable blueprints online. Online hi-res blueprints and outlines reference and catalog.');
        $this->addMetaKeywords(tagVectorModel::$vectorTags . ', ' . tagCategoryUseModel::$useTags);

        if($_GET['search'] != '') {
            $raw = $_GET['search'];
            $params = [];
            if(@$raw['category_id']) {
                $params['category_id'] = $raw['category_id'];

                if(@$raw['make_id']) {
                    $params['make_id'] = $raw['make_id'];
                }
            }

            if(@$raw['name']) {
                $params['name'] = $raw['name'];
            }
            if(@$raw['year']) {
                $params['year'] = $raw['year'];
            }

            $search = (new printsSearchModel())->search(false, $params);
            $this->addVar('search', $search);
        }

        $categories_active = (new printsCategoryModel())->getActiveAll();
        $this->addVar('categories_active', $categories_active);

        $categories = new noxTemplate($this->moduleFolder . '/templates/frontend/categoryOptions.html');
        $categories->addVar('selected', isset($params['category_id']) ? $params['category_id'] : false);
        $categories->addVar('res', $categories_active);
        $this->addVar('categories', $categories->__toString());

        if(isset($params['category_id'])){
            $res = (new printsMakeModel())->where('class_id', $params['category_id'])->select('id, name')->order('name')->fetchAll('id');

            $makesSelectOptions = new noxTemplate($this->moduleFolder . '/templates/frontend/makeOptions.html');
            $makesSelectOptions->addVar('selected', isset($params['make_id']) ? $params['make_id'] : false);
            $makesSelectOptions->addVar('res', $res);
            $this->addVar('makesSelectOptions', $makesSelectOptions->__toString());

            $makesDataOptions = new noxTemplate($this->moduleFolder . '/templates/frontend/optionsDatalistMake.html');
            $makesDataOptions->addVar('res', $res);
            $this->addVar('makesDataOptions', $makesDataOptions->__toString());
        }

        setcookie('my_search_q', '', time() - 1000, '/');
        setcookie('my_search_model',
            (isset($params['category_id']) ? $params['category_id'] : '') . '::'
            . (isset($params['make_id']) ? $params['make_id'] : '') . '::'
            . (isset($params['name']) ? $params['name'] : '') . '::::'
            . (isset($params['year']) ? $params['year'] : '')
            , 0, '/'
        );

        $this->addVar('makes', (new printsMakeModel())->getActiveAll(Prints::VECTOR, true));
        $this->addVar('relVectors', (new printsVectorModel)->getRelated(1));

        if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'chrome') === false){
            $this->addVar('brouser', 'brouser-not-like-chrome');
        }else{
            $this->addVar('brouser', 'brouser-like-chrome');
        }
    }
}
