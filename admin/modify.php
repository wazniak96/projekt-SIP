<?php
require("auth.php");
require("config.php");

$db = new PDO('mysql:host='.$dbhost.';dbname='.$db, $dbuser, $dbpass) or die("Error connecting to the database");
$db->exec("set names utf8");
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if(empty($_GET['id']))
	die('Nie podano wymaganych parametrów.');

try{
	$query = "SELECT * FROM hydrants WHERE id='".$_GET['id']."'";
	$q = $db->query($query);
	$data = $q->fetch();
} catch(PDOException $e) {
	echo($e->getMessage());
}

$selected[$data['type']] = 'selected';
if($data['type'] == "prepared" || $data['type'] == 'natural')
	$disabled = 'disabled';
?>

<!doctype html>
<html lang="pl-PL">
	
  <head>
	<META HTTP-EQUIV="content-type" CONTENT="text/html; charset=UTF-8">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.4.3/css/ol.css" type="text/css">
	<link href="https://cdn.jsdelivr.net/npm/ol-geocoder@latest/dist/ol-geocoder.min.css" rel="stylesheet">
	<script src="https://code.jquery.com/jquery-3.4.1.min.js" type="text/javascript"></script>
	<script src="https://cdn.jsdelivr.net/gh/openlayers/openlayers.github.io@master/en/v6.4.3/build/ol.js"></script>
	<script src="https://unpkg.com/ol-popup@4.0.0/dist/ol-popup.js"></script>
    <script src="https://unpkg.com/ol-geocoder"></script>
    <style>
		#new_hydrant {
			width: 25%;
			height: 90%;
			overflow: hidden;
			position: absolute;
			z-index: 1;
			top: 0;
			bottom: 0;
			left: 0;
			//background-color: #92a8d1;
			padding: 15px;
		}
		
		label {
			display: inline-block;
			width: 100px;
		}
		#map {
			width: 75%;
			height: 100%;
			overflow: hidden;
		}
		body {
			font: 1em/1.5 BlinkMacSystemFont, -apple-system, 'Segoe UI', 'Roboto', 'Oxygen', 'Ubuntu',
			  'Cantarell', 'Fira Sans', 'Droid Sans', 'Helvetica Neue', 'Helvetica', 'Arial', sans-serif;
			color: #222;
			font-weight: 400;
		}
		#map {
			position: absolute;
			z-index: 1;
			top: 0;
			bottom: 0;
			right: 0;
		}
    </style>
	
    <title>Mapa Hydrantów</title>
  </head>
  <body>
	<div id="new_hydrant" class="map">
		<h1>Modyfikuj hydrant</h1>
		<form class="form-inline" action="modifyHydrant.php" method="post">
			<input type="hidden" autocomplete="off" id="id" name="id" value="<? echo($data['id']) ?>">
			<input type="hidden" autocomplete="off" id="type_en" name="type_en" value="<? echo($data['type']) ?>">
			<input type="hidden" autocomplete="off" id="Lon" name="Lon" value="<? echo($data['Lon']) ?>">
			<input type="hidden" id="Lat" name="Lat" autocomplete="off" value="<? echo($data['Lat']) ?>">
			<label for="type">Typ: </label>
			<select id="type" name="type">
				<option value="ground" <? echo($selected['ground']) ?>>Hydrant nadziemny</option>
				<option value="underground" <? echo($selected['underground']) ?>>Hydrant podziemny</option>
				<option value="prepared" <? echo($selected['prepared']) ?>>Punkt czerpania wody</option>
				<option value="natural" <? echo($selected['natural']) ?>>Zbiornik wodny</option>
			</select><br />
			<label for="type">Nazwa: </label>
			<input type="text" id="name" name="name" value="<? echo($data['name']) ?>"><br />
			<label for="type">Nasady: </label>
			<input type="text" id="base" name="base" value="<? echo($data['base']) ?>" <? echo($disabled) ?>><br />
			<label for="type">Średnica: </label>
			<input type="text" id="diameter" name="diameter" value="<? echo($data['diameter']) ?>" <? echo($disabled) ?>><br />
			<label for="type">Dojazd: </label>
			<input type="text" id="road" name="road" value="<? echo($data['road']) ?>"><br />
			<label for="type">Opis: </label><br />
			<textarea id="description" name="description" rows="4" cols="40"><? echo($data['description']) ?></textarea><br />
			<input type="submit" value="Modyfikuj" id="modify" autocomplete="off">
		</form>
		
	</div>
    <div id="map" class="map"></div>
	
    <script type="text/javascript">
		
		function showMap(markers){
			var source = new ol.source.Vector({wrapX: false});

			var vector = new ol.layer.Vector({
			  source: source,
			});

			const map = new ol.Map({
				target: 'map',
				layers: [
					new ol.layer.Tile({
						source: new ol.source.OSM(),
					}),
					vector
				],
				view: new ol.View({
					center: ol.proj.fromLonLat([17.67, 54.62]),
					zoom: 7.5
				})
			});
			
			//DODAWANIE PUNKTOW
			var draw = null; // global so we can remove it later
			
			function addInteraction() {
				draw = new ol.interaction.Draw({
					source: source,
					type: "Point",
				});
				map.addInteraction(draw);
			}

			addInteraction();
			
			draw.on("drawstart", function(evt){
				source.clear();
				var feature = evt.feature;
				var coords = feature.getGeometry().getCoordinates();
				coords = ol.proj.transform(coords, 'EPSG:3857', 'EPSG:4326');
				$("#Lon").val(coords[0]);
				$("#Lat").val(coords[1]);
			});
			
			$('#type').on('change', function() {
				if(this.value == 'ground' || this.value == 'underground')
				{
					$('#base').prop('disabled', false);
					$('#diameter').prop('disabled', false);
				}
				else
				{
					$('#base').prop('disabled', true);
					$('#diameter').prop('disabled', true);
				}
			});
			
            //END OF DODAWANIE PUNKTOW
			for (const [type, hydrants] of Object.entries(markers)) {
				var layer = new ol.layer.Vector({
					source: new ol.source.Vector({
						features: hydrants
					}),
					style: new ol.style.Style({
						image: new ol.style.Icon({
							anchor: [0.5, 46],
							anchorXUnits: 'fraction',
							anchorYUnits: 'pixels',
							src: '/images/' + type + '.png',
							scale: 0.7
						})
					})
				});
				map.addLayer(layer);
			}
			
			//Geocoder 
			
			const popup = new ol.Overlay.Popup();

			// Instantiate with some options and add the Control
			const geocoder = new Geocoder('nominatim', {
				provider: 'osm',
				targetType: 'text-input',
				lang: 'pl',
				placeholder: 'Szukaj...',
				limit: 5,
				keepOpen: false,
			});

			map.addControl(geocoder);
			
			//End of Geocoder
			
		}
		function refreshHydrant()
		{
			var markers = {};
			
			const tmp = new ol.Feature({
				geometry: new ol.geom.Point(ol.proj.fromLonLat([$("#Lon").val(), $("#Lat").val()])),
				name: 'hydrant',
			});
			tmp.setId($("#id").val());
			if(markers[$("#type_en").val()] == null)
				markers[$("#type_en").val()] = [];
			markers[$("#type_en").val()].push(tmp);
			
			console.log(JSON.stringify(markers));
			
			showMap(markers);
		}
		
		$.getJSON( "../getHydrants.php", function(data) {
			refreshHydrant();
		})
		.fail(function() {
			console.log( "error" );
		});
		
	
	</script>
  </body>
</html>