<?php 

require("auth.php");

$dbhost = "db_sip";
$dbuser = "sip";
$dbpass = "sipps";
$db = "sip";

$db = new PDO('mysql:host='.$dbhost.';dbname='.$db, $dbuser, $dbpass) or die("Error connecting to the database");
$db->exec("set names utf8");

$result = $db->query("SELECT * FROM hydrants");
$hydrants = [];

foreach($result as $hydrant)
{
	$base = $hydrant["base"];
	unset($hydrant["base"]);
	$diameter = $hydrant["diameter"];
	unset($hydrant["diameter"]);
	$road = $hydrant["road"];
	unset($hydrant["road"]);
	$hydrant["details"] = ["base" => $base, "diameter" => $diameter, "road" => $road];
	$hydrants[] = $hydrant;
}

echo json_encode($hydrants);