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
	$query = "INSERT INTO hydrants (type, Lon, Lat, name, base, diameter, road, description) VALUES ('".$_POST['type']."', '".$_POST['Lon']."', '".$_POST['Lat']."','".$_POST['name']."', '".$_POST['base']."', '".$_POST['diameter']."','".$_POST['road']."','".$_POST['description']."')";
	$db->exec($query);
} catch(PDOException $e) {
	echo($e->getMessage());
}
echo("Dodano!");