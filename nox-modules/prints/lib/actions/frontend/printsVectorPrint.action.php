<?php
/**
 * Страница просмотра вектора
 *
 * @author     <pa-nic@yandex.ru>
 * @version    1.0
 * @package    prints
 */

class printsVectorPrintAction extends noxThemeAction
{
    public $category;
    public $subCategory;
    public $print;
    public $printDesc;

    public $vectorModel;
    public $paymentModel;

    public function execute()
    {
        $id = $this->getParam('vectorId', 0);
        $printUrl = urlencode($this->getParam('vectorUrl', ''));

        $categoryModel = new printsCategoryModel();

        $this->vectorModel = new printsVectorModel();

        $this->print = $this->vectorModel->getById($id);
        if(!$this->print) {
            return 404;
        }

        $this->print['withoutTop'] = !($this->print['views'] & 2);
        $img_url = noxSystem::$media -> src($this->print['preview']);
        $this->print['preview'] = $img_url;
        $this->addVar('img_url', $img_url);

        $seoUrl = Prints::getVectorUrlFromRaw($printUrl);
        if($seoUrl === false || $seoUrl['vectorUrl'] !== $this->print['url']) {
            $newUrl = Prints::createUrlForItem($this->print, Prints::VECTOR);
            (new errorActions)->action301($newUrl);
        }

        $this->addVar('canonical', preg_replace('/blueprints$/', 'drawings', explode('?', noxSystem::$fullUrl)[0]));

        $typeUrl = $seoUrl['typeUrl'];

        $this->vectorModel->exec('UPDATE ' . $this->vectorModel->table . ' SET views_count = views_count+1 WHERE id = ' . $id);
        ++$this->print['views_count'];
        $this->category = $categoryModel->getById($this->print['class_id']);

        $categoryClassTable = 'prints_class_' . $this->category['db_table'];
        $dataCategoryModel = new noxModel(false, $categoryClassTable);
        $this->printDesc = $dataCategoryModel->getById($this->print['item_id']);

        $breadcrumbs = [
            [
                'name' => 'drawings',
                'title' => 'drawings',
                'url'   => '/vector-drawings'
            ],
            [
                'name' => strtolower($this->category['name_singular']) . ' drawings',
                'title' => strtolower($this->category['name_singular']) . ' drawings',
                'url'   => '/' . $this->category['url'] . '-vector-drawings/'
            ]

        ];

        //$this->title = $this->caption = $this->print['full_name'] . ' ' . $typeUrl;
        $keywords = '';

        if(isset($dataCategoryModel->fields['make_id'])) {
            $makeModel = new printsMakeModel();
            $this->subCategory = $makeModel->getById($this->printDesc['make_id']);
            $mn = $this->subCategory['name'] . ' ' . $this->print['name'];

            if($this->subCategory) {
                $breadcrumbs[] = [
                    'name' => $this->subCategory['name'],
                    'title' => $this->subCategory['name'] . ' drawings',
                    'url'   => '/' . $this->category['url']  . '-vector-drawings/' . $this->subCategory['url']
                ];
            }

            foreach(explode(',', tagVectorModel::$vectorTags) as $tag) {
                $keywords .= $mn . ' ' . trim($tag) . ', ';
            }
            $useTags = (new tagCategoryUseModel)->getById($this->category['id']);
            foreach($useTags as $tag) {
                $keywords .= $mn . ' ' . $tag . ', ';
            }
            $keywords .= 'front view, top view, rear view, side view, ' . $this->print['full_name'] . ', ';
            $keywords .= implode(',', (new tagMakeModel)->getById($this->subCategory['id'])) . ', ';
        }
        else {
            $mn = $this->print['name'];
            //$this->title = $this->caption = $this->category['name'] . ' ' . $typeUrl;
            $letter = strtoupper(substr($this->print['name'], 0, 1));

            $breadcrumbs[] =
                [
                    'name' => $letter,
                    'title' => $letter . ' drawings',
                    'url'   => '/' . $this->category['url']  . '-vector-drawings/'  . $letter
                ];

            $useTags = (new tagCategoryUseModel)->getById($this->category['id']);
            foreach($useTags as $tag) {
                $keywords .= $mn . ' ' . $tag . ', ';
            }
            $keywords .= 'front view, top view, rear view, side view, ' . $this->print['full_name'] . ', ';
        }

        $set = (new printsSetModel())->getSetForVector($id);
        if($set){
            $this->print['set_id'] = $set['id'];
            $breadcrumbs[] = [
                'name' => $set['name_full'],
                'title' => $set['name_full'] . ' drawings',
                'url'   => '/sets/' . $set['url'] . '-' . $seoUrl['typeUrl']
            ];
        }
        $this->addVar('breadcrumbs', $breadcrumbs);
        $this->addVar('set', $set);

        $relVectors = $this->vectorModel->getRelatedForItem(12, array_merge($this->printDesc, $this->print));
        $this->addVar('relVectorsTop', array_splice($relVectors, 0, 4));
        $this->addVar('relVectors', $relVectors);

        $this->paymentModel = new paymentModel();

        $per = (isset($this->printDesc['year']) ? $this->printDesc['year'] . ' ' : '');
        $per2 = $per . '- ' . ($this->printDesc['end'] ? $this->printDesc['end'] : 'Present');
        $resArr = [$per, $mn, $this->print['name_version'], $this->print['name_spec']];
        $imgArr = [$per, $mn, $this->print['name_version']];
        $resName = trim(preg_replace('/\s{2,}/', ' ', join(' ', $resArr)));
        $imgName = trim(preg_replace('/\s{2,}/', ' ', join(' ', $imgArr)));
        if(isset($this->printDesc['body'])){
            $resName .= ' ' . $this->printDesc['body'];
            $imgName .= ' ' . $this->printDesc['body'];
        }
        $this->title = $resName . ' drawings - download vector blueprints';
        $this->caption = $resName . ' blueprint';
        $this->addVar('imageDownloadTitle', $resName . ' car blueprint');
        $this->addMetaDescription('Download ' . $resName . ' drawings and vector blueprints. '
            . 'Editable templates for car wrap. Purchase this blueprint or request any other. '
            . 'We also draw PDF, CAD, scalable outlines and clip arts for design studios.');
        $keywords .= implode(',', (new tagVectorModel)->getById($this->print['id']));
        $keywords .= ', ' . tagCategoryUseModel::$useTags;
        $this->addMetaKeywords($keywords);
        $purchase = $this->paymentModel->isBuyByUser('vector', $id);
        $this->addVar('purchase', !empty($purchase) && ($purchase['status'] === 'approved'));
        $this->addVar('category', $this->category);
        $this->addVar('subCategory', $this->subCategory);
        $this->addVar('print', $this->print);
        $this->addVar('printDesc', $this->printDesc);
        $this->addVar('per', $per);
        $this->addVar('per2', $per2);
        $this->addVar('mn', $mn);
        $this->addVar('resName', $resName);
        $this->addVar('seoUrl', $seoUrl);
        $this->addVar('seoImg', $imgName . ' blueprints and drawings');

        $this->addVar('prepay', $this->print['prepay']);
        if(!$this->print['prepay']){
            $this->addVar('fbq', [
                'id' => $this->print['id'],
                'name' => $per . $mn,
                'price' => $this->print['price'],
            ]);
            $config = noxConfig::getConfig();
            $this->addVar('schPr', [
                'id' => $this->print['id'],
                'name' => $per . $mn . ' car blueprint',
                'img' => $img_url,
                'brand' => 'Outlines Car blueprints',
                'description' => 'Get ' . trim(preg_replace('/\s{2,}/', ' ', join(' ', $resArr)))
                    . ' car blueprints, drawing and template of car',
                'price' => $this->print['price'],
                'url' => $config['protocol'] . $config['host'] . Prints::createUrlForItem($this->print, Prints::VECTOR),
            ]);
            $this->addVar('image_src', $img_url);
        }
        $this->addVar('fixAction', 'buy|' . $this->print['id'] . '|' . $this->print['price'] . '|'
            . $this->print['prepay']);

        setcookie('pay_real_price', '', time() - 1000, '/');
        setcookie('my_search_q', '', time() - 1000, '/');
        setcookie('my_search_model',
            $this->print['class_id'] . '::'
            . (isset($this->printDesc['make_id']) ? $this->printDesc['make_id'] : '') . '::'
            . $this->print['name'] . '::::'
            , 0, '/'
        );

        if(isset($_COOKIE['purchase_success'])) {
            $this->addVar('payment_prm', explode('::', $_COOKIE['purchase_success']));
            setcookie('purchase_success', '', time() - 1000, '/');
        }
        if(isset($_COOKIE['prepayment_success'])) {
            $this->addVar('prepayment_success', true);
            setcookie('prepayment_success', '', time() - 1000, '/');
        }
        if(isset($_COOKIE['forceBuy'])) {
            $this->addVar('forceBuy', true);
            setcookie('forceBuy', '', time() - 1000, '/');
        }
    }
}
