<?php
    require_once('init.php');
    require_once(noxRealPath('nox-modules/prints/lib/models/printsRequestVector.model.php'));
    require_once(noxRealPath('nox-modules/system/lib/actions/systemAdministratorSitemapSEO.action.php'));

    (new systemAdministratorSitemapAction)->requests();
