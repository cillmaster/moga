<?php
    require_once('init.php');
    require_once(noxRealPath('nox-modules/pages/lib/models/pages.model.php'));
    require_once(noxRealPath('nox-modules/system/lib/actions/systemAdministratorSitemapSEO.action.php'));

    (new systemAdministratorSitemapAction)->stat();
