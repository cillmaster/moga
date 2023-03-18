<?php
/**
 * Страница просмотра чертежа
 *
 * @author     <pa-nic@yandex.ru>
 * @version    1.0
 * @package    prints
 */

class printsBlueprintsPrintAction extends noxThemeAction
{
    public $category;
    public $subCategory;
    public $print;
    public $printDesc;

    public $blueprintModel;

    public function execute()
    {
        $id = $this->getParam('printId', 0);
        $printUrl = urlencode($this->getParam('printUrl', ''));

        $categoryModel = new printsCategoryModel();

        $this->blueprintModel = new printsBlueprintModel();

        $this->print = $this->blueprintModel->where(['id' => $id])->fetch();
        $this->blueprintModel->exec('UPDATE ' . $this->blueprintModel->table . ' SET views_count = views_count+1 WHERE id = ' . $id);
        if(!$this->print) {
            return 404;
        } elseif ($this->print['url'] != $printUrl){
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: /blueprints/' . $id . '/' . $this->print['url'] . '-blueprints');
            exit();
        }

        $filename = noxSystem::$media -> src($this->print['filename']);
        $this->print['filename'] = $filename;
        $this->addVar('img_url', $filename);
        $this->addVar('image_src', $filename);

        ++$this->print['views_count'];
        $this->category = $categoryModel->getById($this->print['class_id']);

        $categoryClassTable = 'prints_class_' . $this->category['db_table'];
        $dataCategoryModel = new noxModel(false, $categoryClassTable);
        $this->printDesc = $dataCategoryModel->getById($this->print['item_id']);

        $breadcrumbs = [
            [
                'name' => 'blueprints',
                'title' => 'blueprints',
                'url'   => '/blueprints'
            ],
            [
                'name' => strtolower($this->category['name_singular']) . ' blueprints',
                'title' => strtolower($this->category['name_singular']) . ' blueprints',
                'url'   => '/' . $this->category['url'] . '-blueprints'
            ]
        ];

        $ver = ($this->print['ver'] > 1) ? (' v' . $this->print['ver']) : '';
        $this->title = $this->print['full_name'] . $ver . ' blueprints free';
        $this->caption = $this->print['full_name'] . ' blueprints free';
        $keywords = '';

        $printsVectorModel = new printsVectorModel();
        if(isset($dataCategoryModel->fields['make_id'])) {
            $makeModel = new printsMakeModel();
            $this->subCategory = $makeModel->getById($this->printDesc['make_id']);
            $breadcrumbs[] = [
                'name' => $this->subCategory['name'],
                'title' => $this->subCategory['name'] . ' blueprints',
                'url'   => '/' . $this->category['url'] . '-blueprints/' . $this->subCategory['url']
            ];
            $mn = $this->subCategory['name'] . ' ' . $this->print['name'];

            foreach(explode(',', tagBlueprintModel::$rasterTags) as $tag) {
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
            $letter = strtoupper(substr($this->print['name'], 0, 1));
            $breadcrumbs[] =
                [
                    'name' => $letter,
                    'title' => $letter . ' blueprints',
                    'url'   => '/' . $this->category['url'] . '-blueprints/' . $letter
                ];

            $useTags = (new tagCategoryUseModel)->getById($this->category['id']);
            foreach($useTags as $tag) {
                $keywords .= $mn . ' ' . $tag . ', ';
            }
            $keywords .= 'front view, top view, rear view, side view, ' . $this->print['full_name'] . ', ';
        }
        $this->addVar('relVectors', $printsVectorModel->getRelatedForItem(12, array_merge($this->printDesc, $this->print)));
        $this->addMetaDescription('Download free ' . $this->print['full_name'] . $ver. ' blueprints.' . tagBlueprintModel::$rasterMetaDescriptionAdditional);
        $keywords .= implode(',', (new tagBlueprintModel)->getById($this->print['id']));
        $keywords .= ', ' . tagCategoryUseModel::$useTags . ', ' . tagBlueprintModel::$rasterAdsTags;
        $this->addMetaKeywords($keywords);
        $this->addVar('breadcrumbs', $breadcrumbs);
        $this->addVar('category', $this->category);
        $this->addVar('subCategory', $this->subCategory);
        $this->addVar('print', $this->print);
        $this->addVar('printDesc', $this->printDesc);
        $this->addVar('mn', $mn);

        $this->addVar('canonical', explode('?', noxSystem::$fullUrl)[0]);

        $relatedVectorId = (new printsRelationModel())->getVectorIdForBlueprint($id);
        if($relatedVectorId) {
            $v = (new printsVectorModel())->getById($relatedVectorId);
            $v['preview'] = noxSystem::$media -> src($v['preview']);
            $this->addVar('relatedVector', $v);
        }

        setcookie('my_search_q', '', time() - 1000, '/');
        setcookie('my_search_model',
            $this->category['id'] . '::'
            . (isset($this->subCategory['id']) ? $this->subCategory['id'] : '') . '::::::'
            , 0, '/'
        );
    }
}
