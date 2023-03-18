<?php

$system_path = dirname(__FILE__) . '/nox-system/noxSystem.class.php';

if (!file_exists($system_path))
{
    die('NOX.CMS not found.');
}

require_once($system_path);
noxSystem::run();