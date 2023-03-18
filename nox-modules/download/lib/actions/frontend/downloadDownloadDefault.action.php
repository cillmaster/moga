<?php

class downloadDownloadDefaultAction extends noxAction {

    public $fileName;
    public $model;
    public $print;

    public function execute() {
        $type = $this->getParam('type', '');
        $id = $this->getParam('id', 0);

        if($type === 'blueprint') {
            $this->model = new printsBlueprintModel();
        }
        elseif($type === 'vector') {
            $this->model = new printsVectorModel();
        }
        else {
            noxSystem::location('/');
        }
        $this->print = $this->model->getById($id);
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

        if($type === 'vector') {
            $model = new paymentModel();
            if(!($model->isBuyByUser('vector', $id) || $model->isEditor($id))) {
                noxSystem::location(Prints::createUrlForItem($this->print, Prints::VECTOR));
            }
        }

        $this->model->downloaded($id);
        error_log("File not found: {$this->fileName}");
        if(!file_exists($this->fileName)) {
            echo 'Sorry, but we can\'t find vector drawing. Please contact webmaster@getoutlines.com for solve problem';
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
} 