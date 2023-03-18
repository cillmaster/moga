<?php

class printsCollectionDefaultAction extends noxThemeAction
{
    public $collection;
    public $collectionModel;

    public function execute()
    {
        $collectionUrl = $this->getParam('collectionUrl', '');

        $seoUrl = Prints::getVectorUrlFromRaw($collectionUrl);
        if(!$seoUrl) return 404;

        $this->collectionModel = new printsCollectionModel();
        $this->collection = $this->collectionModel->getByField('url', $collectionUrl);
        if(!$this->collection) return 404;

        $vectorModel = new printsVectorModel();
        $vectors = $vectorModel->where('id', $this->collectionModel->collectionVectorModel->getVectorsIdByCollection($this->collection['id']))->order('`sort_name` ASC')->fetchAll();
        foreach ($vectors as &$row) {
            $img_url = noxSystem::$media->srcMini($row['preview']);
            $row['preview'] = $img_url;
            if(!isset($og_img_url) && ($row['prepay'] == '0')){
                $this->addVar('img_url', $og_img_url = $img_url);
            }
        }

        $this->addVar('vectors', $vectors);
        $this->addVar('details', $this->collection['text']);
        $this->addVar('seoUrl', $seoUrl);


        $this->title = $this->collection['title'];
        $this->addMetaDescription($this->collection['description']);
        $this->caption = $this->collection['caption'];
    }
}
