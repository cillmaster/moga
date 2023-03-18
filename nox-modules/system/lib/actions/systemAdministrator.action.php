<?php

class systemAdministratorAction extends noxThemeAction
{
    public $cache = false;

    public $theme = 'administrator';

    public $caption = 'Добро пожаловать!';

    public function execute()
    {
        if(!noxSystem::authorization() || !$this->haveRight('panel-access')) {
            return 401;
        }
    }
}