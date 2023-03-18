<?php
    ini_set('display_errors', 1);
    date_default_timezone_set('UTC');
    mb_internal_encoding('UTF-8');

    $_SERVER['REQUEST_METHOD'] = 'POST';
    $GLOBALS['cron'] = true;

    $root = implode('/', array_slice(explode('/', str_replace('\\', '/', dirname(__FILE__))), 0, -2)) . '/';
    /** @noinspection PhpIncludeInspection */
    require_once($root . 'nox-system/noxSystem.class.php');
    require_once(noxRealPath('nox-system/application/noxApplication.class.php'));
    require_once(noxRealPath('nox-config/application.class.php'));
    require_once(noxRealPath('nox-system/cache/noxCache.class.php'));
    require_once(noxRealPath('nox-system/cache/noxSystemCache.class.php'));
    require_once(noxRealPath('nox-system/config/noxConfig.class.php'));
    require_once(noxRealPath('nox-system/db/noxDbAdapter.class.php'));
    require_once(noxRealPath('nox-system/db/noxDbConnector.class.php'));
    require_once(noxRealPath('nox-system/db/noxDbMySQLiAdapter.class.php'));
    require_once(noxRealPath('nox-system/db/noxDbQuery.class.php'));
    require_once(noxRealPath('nox-system/helpers/PaginatorDatasource.php'));
    require_once(noxRealPath('nox-system/db/noxModel.class.php'));
    require_once(noxRealPath('nox-system/action/noxAction.class.php'));

