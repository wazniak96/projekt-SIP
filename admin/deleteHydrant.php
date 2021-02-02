<?php
header('Refresh: 1.5; URL=index.php');
require("auth.php");
require("config.php");

$db = new PDO('mysql:host='.$dbhost.';dbname='.$db, $dbuser, $dbpass) or die("Error connecting to the database");
$db->exec("set names utf8");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if(empty($_GET['id']))
	die('Nie podano wymaganych parametrów.');

try{
	$query = "DELETE FROM hydrants WHERE id='".$_GET['id']."'";
	$db->exec($query);
} catch(PDOException $e) {
	echo($e->getMessage());
}
echo("Skasowano!");