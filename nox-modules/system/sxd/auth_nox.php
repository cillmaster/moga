<?php
//Авторизация для NOX.CMS

//Проверяем сессию
if (isset($_GET['db']) && isset($_COOKIE['nox_sid']))
{
    $name = htmlspecialchars($_GET['db']);
    $login = htmlspecialchars($_GET['login']);
    $password = htmlspecialchars($_GET['password']);
    $host = htmlspecialchars($_GET['host']);

    if ($this->connect($host, 3306, $login, $password))
    {
        $auth = 1;
    }
}

?>