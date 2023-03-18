<?php

class printsRequestSearchAction extends noxAction {

    public function execute(){
        $page = $_GET['page'] ? ('?page=' . $_GET['page']) : '';
        header('HTTP/1.1 301 Moved Permanently');
        header('Location: /requests' . $page);
        exit();
    }
}
