<?php

class printsSetSetAction extends noxThemeAction
{
    public $set;
    public $setModel;

    public function execute()
    {
        $setUrl = $this->getParam('setUrl', '');

        $seoUrl = Prints::getVectorUrlFromRaw($setUrl);
        if(!$seoUrl) return 404;

        $this->setModel = new printsSetModel();
        $this->set = $this->setModel->getByField('url', urlencode($seoUrl['vectorUrl']));
        if(!$this->set) return 404;
        $this->set['name_search'] = urlencode($this->set['name_full']);

        $vectorModel = new printsVectorModel();
        $vectors = $vectorModel->where('id', $this->setModel->setVectorModel->getVectorsIdBySet($this->set['id']))->order('`prepay` ASC, `full_name` DESC')->fetchAll();
        $ind = 0;
        foreach ($vectors as &$row) {
            if($ind++){
                $img_url = noxSystem::$media->srcMini($row['preview']);
            } else {
                $img_url = noxSystem::$media->src($row['preview']);
                if((int)$row['prepay']){
                    $config = noxConfig::getConfig();
                    $this->addVar('image_src', $config['protocol'] . $config['host'] . '/nox-themes/default/images/prepay-preview448.png');
                } else {
                    $this->addVar('image_src', $img_url);
                }
            }
            $row['preview'] = $img_url;
            if(!isset($og_img_url) && ($row['prepay'] == '0')){
                $this->addVar('img_url', $og_img_url = $img_url);
            }
        }

        $this->addVar('vectors', $vectors);
        $this->addVar('set', $this->set);
        $this->addVar('seoUrl', $seoUrl);

        //$this->title = $this->set['name_full'] . ' drawings and vector blueprints';
        //$this->title = $this->set['name_full'] . ' ' . $seoUrl['typeTitle'];

        $categoryModel = new printsCategoryModel();
        $category = $categoryModel->getById($vectors[0]['class_id']);
        $categoryItemModel = new printsCategoryItemModel($category['id']);
        $categoryItemData = $categoryItemModel->getById($vectors[0]['item_id']);

        $keywords = '';

        $breadcrumbs = [
            [
                'name' => 'drawings',
                'title' => 'drawings',
                'url'   => '/vector-drawings'
            ],
            [
                'name' => strtolower($category['name_singular']) . ' drawings',
                'title' => strtolower($category['name_singular']) . ' drawings',
                'url'   => '/' . $category['url'] . '-vector-drawings/'
            ]
        ];

        if($categoryItemModel->hasField('make_id')) {
            $make = (new printsMakeModel())->getById($categoryItemData['make_id']);
            $breadcrumbs[] = [
                'name' => strtolower($make['name']) . ' drawings',
                'title' => strtolower($make['name']) . ' drawings',
                'url'   => '/' . $category['url'] . "-vector-drawings/" . $make['url']
            ];
            if($category['url'] == 'car'){
                $this->addVar('bp_url', '/car-blueprints/' . $make['url']);
            }
            $this->addVar('bp_name', $make['name']);
            $this->addVar('subCategory', $make);
            $mn = $make['name'] . ' ' . $vectors[0]['name'];

            foreach(explode(',', tagVectorModel::$vectorTags) as $tag) {
                $keywords .= $mn . ' ' . trim($tag) . ', ';
            }
            $useTags = (new tagCategoryUseModel)->getById($category['id']);
            foreach($useTags as $tag) {
                $keywords .= $mn . ' ' . $tag . ', ';
            }
            $keywords .= 'front view, top view, rear view, side view, ';
            $keywords .= implode(',', (new tagMakeModel)->getById($make['id'])) . ', ';
        }
        else {
            $mn = $vectors[0]['name'];
            $useTags = (new tagCategoryUseModel)->getById($category['id']);
            foreach($useTags as $tag) {
                $keywords .= $mn . ' ' . $tag . ', ';
            }
            $keywords .= 'front view, top view, rear view, side view, ';
        }

        $keywords .= ', ' . tagCategoryUseModel::$useTags;
        $this->addMetaKeywords($keywords);

        $this->title = $mn . ' drawings all generations';
        $this->addMetaDescription('Download ' . $mn . ' drawings, high resolution original drawings and scalable. '
            . 'Editable templates for ' . $mn . ' wrap, vehicle branding and corporate design wrapping. '
            . 'Use it for T-shirt, birthday cake, poster or whatever.');
        $this->caption = $mn . ' drawings';

        $this->addVar('breadcrumbs', $breadcrumbs);
        $this->addVar('category', $category);

        setcookie('my_search_q', '', time() - 1000, '/');
        setcookie('my_search_model',
            $category['id'] . '::'
            . (isset($make['id']) ? $make['id'] : '') . '::'
            . $vectors[0]['name'] . '::::'
            , 0, '/'
        );
    }
}
