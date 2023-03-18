<?php
/**
 * Страница запроса чертежа
 *
 * @author     <pa-nic@yandex.ru>
 * @version    1.0
 * @package    prints
 */

class printsRequestVectorAction extends noxThemeAction
{
    public $requestVectorModel;
    public $request;

    public function execute()
    {
        $id = $this->getParam('id', 0);
        $url = urlencode($this->getParam('url', ''));
        $seoUrl = Prints::getVectorUrlFromRaw($url);
        if(!$seoUrl) return 404;
        $this->addVar('url', $seoUrl);

        $this->requestVectorModel = new printsRequestVectorModel();
        $this->request = $this->requestVectorModel->where(['id' => $id, 'url' => $seoUrl['vectorUrl']])->fetch();

        if(!$this->request) return 404;

        $this->requestVectorModel->exec('update ' . $this->requestVectorModel->table . ' SET views_count = views_count+1 WHERE id = ' . $id);
        $this->request['views_count']++;

        $vectorModel = new printsVectorModel();
        if($this->request['vector_id'] !== NULL) {
            $vector = $vectorModel->getById($this->request['vector_id']);
            _headerMovedPermanently(Prints::createUrlForItem($vector, Prints::VECTOR));
        }

        $categoryModel = new printsCategoryModel();
        $this->addVar('category', $categoryModel->getById($this->request['category_id']));

        if($this->request['make_id']) {
            $this->addVar('subCategory', (new printsMakeModel())->getById($this->request['make_id']));
        }

        $breadcrumbs = [
            [
                'name' => 'requests',
                'title' => 'requests',
                'url' => '/requests'
            ]
        ];
        $this->addVar('request', $this->request);
        $this->addVar('breadcrumbs', $breadcrumbs);

        $this->caption = $this->title = $this->request['full_name'] . ' drawings and vector blueprints request';
        //$this->caption = $this->title = $this->request['full_name'] . ' ' . $seoUrl['typeTitle'] . ' request';

        $mn = $this->request['sort_name'];
        $this->addMetaDescription('Request a quotation, order and get ' . $mn . ' drawings and vector blueprints '
            . 'in a few work days. Editable templates for ' . $this->request['name'] . ' wrap, vehicle branding and '
            . 'corporate design wrapping. make pre-payment and get it in premium quality. Make 3D model. Use it '
            . 'for T-shirt, birthday cake, poster or whatever. Request this exact model or any other ' . $mn
            . ' blueprint. We also offer PDF, CAD, scalable outlines and clip arts for design studios.');

        $keywords = '';
        foreach(explode(',', tagVectorModel::$vectorTags) as $tag) {
            $keywords .= $mn . ' ' . trim($tag) . ', ';
        }
        $useTags = (new tagCategoryUseModel)->getById($this->request['category_id']);
        foreach($useTags as $tag) {
            $keywords .= $mn . ' ' . $tag . ', ';
        }
        $keywords .= 'front view, top view, rear view, side view, ' . $mn . ', ';
        if($this->request['make_id']) {
            $keywords .= implode(',', (new tagMakeModel)->getById($this->request['make_id'])) . ', ';
        }
        $keywords .= ', ' . tagCategoryUseModel::$useTags;
        $this->addMetaKeywords($keywords);

        if($this->request['category_id'] == 1){
            $this->request['class_id'] = $this->request['category_id'];
            $this->addVar('relVectors', $vectorModel->getRelatedForItem(12, $this->request));
        }

        if(noxSystem::authorization()) {
            $vote = (new printsRequestVoteModel())
                ->where(['user_id' => noxSystem::getUserId(), 'request_id' => $id, 'request_type' => Prints::REQUEST_VECTOR])
                ->fetch();
            $this->addVar('vote', $vote);
            if(isset($_COOKIE['vote_email'])) {
                $this->addVar('vote_email', $_COOKIE['vote_email']);
                setcookie('vote_email', '', time() - 1000, '/');
            }
        }
        else {
            $this->addVar('vote', null);
            if(isset($_COOKIE['unauthorized_request'])) {
                $this->addVar('unauthorized_request', $_COOKIE['unauthorized_request']);
                setcookie('unauthorized_request', '', time() - 1000, '/');
            }
        }
    }
}
