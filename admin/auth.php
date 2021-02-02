<?php

$login = "admin"; // tu wpisz swój login
$password = "pass"; // a tu swoje hasło

if(!isset($_SERVER['PHP_AUTH_USER']) or strcmp($_SERVER['PHP_AUTH_USER'],$login) or strcmp($_SERVER['PHP_AUTH_PW'],$password)) {
    header("WWW-Authenticate: Basic realm=strefa chroniona");
    header("HTTP/1.0 401 Unauthorized");
    die("Brak uprawnień do przeglądania strony");
}

?>