<?php
    require_once('init.php');
    require_once(noxRealPath('nox-modules/prints/lib/models/printsVector.model.php'));
    require_once(noxRealPath('nox-modules/system/lib/actions/systemAdministratorFacebookFeed.action.php'));
    require_once(noxRealPath('nox-system/output/kafMedia.class.php'));

    (new systemAdministratorFacebookFeedAction())->execute();
