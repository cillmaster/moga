<?php

class downloadVectorActions extends noxAction {

    public $fileName;
    public $model;
    public $print;

    public function execute() {
        if (!$this->haveRight('control')) {
            return 401;
        }
        $this->model = new printsVectorModel();
        $this->print = $this->model->getById($_GET['id']);
        $this->fileName = noxRealPath($this->print['filename']);

        if(function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $this->fileName, FILEINFO_MIME_TYPE);
            finfo_close($finfo);
        }
        elseif(function_exists('mime_content_type')) {
            $mimeType = mime_content_type($this->fileName);
        }
        else {
            $mimeType = 'application/octet-stream';
        }

        header($_SERVER["SERVER_PROTOCOL"] . ' 200 OK');

        if(!$this->print) {
            return 404;
        }

        if(!file_exists($this->fileName)) {
            exit;
        }

        header('Content-Type: ' . $mimeType);
        header('Last-Modified: ' . gmdate('r', filemtime($this->fileName)));
        header('ETag: ' . sprintf('%x-%x-%x', fileinode($this->fileName), filesize($this->fileName), filemtime($this->fileName)));
        header('Content-Length: ' . (filesize($this->fileName)));
        header('Connection: close');
        header('Content-Disposition: attachment; filename="' . basename($this->fileName) . '";');
        echo file_get_contents($this->fileName);
    }

    public function actionDefault(){}
}