<?php
header('Refresh: 1.5; URL=index.php');
require("auth.php");
require("config.php");

$db = new PDO('mysql:host='.$dbhost.';dbname='.$db, $dbuser, $dbpass) or die("Error connecting to the database");
$db->exec("set names utf8");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if(empty($_POST['type']) || empty($_POST['Lon']) || empty($_POST['Lat']) || empty($_POST['name']))
	die('Nie podano wymaganych parametrów.');

try{
	$query = "UPDATE hydrants SET type='".$_POST['type']."', Lon='".$_POST['Lon']."', Lat='".$_POST['Lat']."', name='".$_POST['name']."', base='".$_POST['base']."', diameter='".$_POST['diameter']."', road='".$_POST['road']."', description='".$_POST['description']."' WHERE id='".$_POST['id']."'";
	$db->exec($query);
} catch(PDOException $e) {
	echo($e->getMessage());
}
echo("Zmodyfikowano!");