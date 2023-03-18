<?php
/**
 * Страница поиска чертежа
 *
 * @author     <pa-nic@yandex.ru>
 * @version    1.0
 * @package    prints
 */

class printsSearchDefaultAction extends noxThemeAction
{
    public $model;

    public function execute()
    {
        $this->addMetaDescription('Search through 40\'000+ car blueprints and drawings online. Outlines '
            . 'is one of the largest car blueprints database and data bank on the Web. And the first to '
            . 'focus on cars only. Find exterior template of vehicle, sports car, bus or truck. Download free '
            . 'blueprints unlimited for art and design purposes.');
        $this->addMetaKeywords('search blueprints, find drawings, online catalogue, reference, database, catalog' . ', ' . tagBlueprintModel::$rasterAdsTags);

        if(isset($_GET['q']) && $_GET['q']) {
            setcookie('my_search_model', '', time() - 1000, '/');
            setcookie('my_search_q', $_GET['q'], 0, '/');
            $page = $_GET['page'];
            $onPage = 100;
            $this->model = new printsSearchModel();
            $search = $this->model->searchSphinx2($_GET['q'], false, ($page - 1) * $onPage, $onPage);
            $this->addVar('pager', (new kafPager('pager2.html'))->create2($search['total'], $onPage, 9, $page));
            $this->addVar('count', $search['total']);
            foreach ($search['sets'] as &$row) {
                $row['vector'] = (isset($row['preview']) && !preg_match('/vectors\/prepay/', $row['preview'])) ? 1 : 0;
                $row['preview'] = isset($row['preview']) ? noxSystem::$media->srcMini($row['preview']) : '';
            }
            foreach ($search['rest'] as &$row) {
                $row['preview'] = isset($row['preview']) ? noxSystem::$media->srcMini($row['preview']) : '';
            }
            $this->addVar('search', $search);
            $this->title = $this->caption = 'Search Results for "' . ucwords($_GET['q']) . '"';
            if(sizeof($words = explode(' ', $_GET['q'])) > 1){
                foreach ($words as $word){
                    if(!empty($word)){
                        $hints[] = '<a href="/search?q=' . $word . '">' . $word . '</a>';
                    }
                }
                if(!empty($hints)){
                    $this->addVar('hints', join(' or ', $hints));
                }
            }
        }
        else {
            $this->title =  'Search car blueprints on online drawing database of cars';
            $this->caption = 'Search blueprints, drawings and templates';
            $related = (new printsVectorModel())->getRand(4);
            foreach ($related as &$row){
                $row['preview'] = isset($row['preview']) ? noxSystem::$media->srcMini($row['preview']) : '';
            }
            $this->addVar('related', $related);
        }
    }
}
