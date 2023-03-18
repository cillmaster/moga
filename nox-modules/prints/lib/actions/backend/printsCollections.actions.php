<?php
class printsCollectionsActions extends noxThemeActions {

    public $theme = 'administrator';
    public $cache = false;
    public $model;
    public $caption = 'Коллекции';
    private $post = null;

    public function execute() {
        if (!$this->haveRight('vector')) {
            return 401;
        }
        $this->model = new printsCollectionModel();
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if ($raw = file_get_contents('php://input')) {
                $this->post = json_decode($raw);
            }
        }
        return parent::execute();
    }

    public function actionDefault() {}

    public function actionCmd(){
        if($this->post){
            switch ($this->post->cmd){
                case 'remove':
                    $this->model->remove($this->post->cID, $this->post->vID);
                    break;
                case 'create':
                    $this->model->insert([
                        'name' => htmlspecialchars($this->post->name),
                        'url' => htmlspecialchars($this->post->url),
                        'caption' => htmlspecialchars($this->post->caption),
                        'title' => htmlspecialchars($this->post->title),
                        'text' => htmlspecialchars($this->post->text),
                        'description' => htmlspecialchars($this->post->description),
                    ]);
                    break;
                case 'edit':
                    $this->model->updateById($this->post->id, [
                        'name' => htmlspecialchars($this->post->name),
                        'url' => htmlspecialchars($this->post->url),
                        'caption' => htmlspecialchars($this->post->caption),
                        'title' => htmlspecialchars($this->post->title),
                        'text' => htmlspecialchars($this->post->text),
                        'description' => htmlspecialchars($this->post->description),
                    ]);
                    break;
            }
        }
        $this->actionData();
    }

    public function actionData(){
        $vectorModel = new printsVectorModel();
        $res = [
            'data' => $this->model->reset()->fetchAll(),
            'map' => $this->model->getMap(),
            'vectors' => $vectorModel->where(['id' => $this->model->getUniqueVectors()])->fetchAll('id', 'full_name'),
            'status' => 200
        ];
        echo json_encode($res);
        return 200;
    }

}
