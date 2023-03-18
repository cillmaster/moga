<?php
    require_once('init.php');
    require_once(noxRealPath('nox-modules/prints/lib/models/printsVector.model.php'));
    require_once(noxRealPath('nox-modules/system/lib/actions/systemAdministratorImages.action.php'));

    (new systemAdministratorImagesAction())->previewsDownWidthTo448();
