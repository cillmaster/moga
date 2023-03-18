<?php

class printsOldBpRedirectAction extends noxAction
{
    public function execute()
    {
        $id = $this->getParam('id');

        $model = new printsBlueprintModel();
        $bp = $model->getById($id);

        if(!$bp) {
            noxSystem::location('/');
        }
        else {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: ' . Prints::createUrlForItem($bp, Prints::BLUEPRINT) . '?' . http_build_query($_GET));
            exit();
        }
    }
}
