<?php
    require_once('init.php');
    require_once(noxRealPath('nox-modules/prints/lib/models/printsBlueprint.model.php'));
    require_once(noxRealPath('nox-modules/prints/lib/models/printsCategory.model.php'));
    require_once(noxRealPath('nox-modules/prints/lib/models/printsMake.model.php'));
    require_once(noxRealPath('nox-modules/prints/lib/models/printsRequestVector.model.php'));
    require_once(noxRealPath('nox-modules/prints/lib/models/printsSet.model.php'));
    require_once(noxRealPath('nox-modules/prints/lib/models/printsVector.model.php'));
    require_once(noxRealPath('nox-modules/system/lib/actions/systemAdministratorSitemap.action.php'));

    (new systemAdministratorSitemapAction)->execute();
