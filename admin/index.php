<?php
require('auth.php');
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
		.info {
			position: absolute;
			z-index: 2;
			padding: 15px;
			border-radius: 10px;
			border: 1px solid #cccccc;
			top: 10px;
			right: 20px;
			min-width: 25%;
			max-width: 25%;
			min-height: 70%;
			max-height: 70%;
			visibility: hidden;
			background-color: white;
		}
		.info-closer {
            text-decoration: none;
            position: absolute;
            top: 10px;
            right: 16px;
        }
        .info-closer:after {
            content: "X";
            color: #c3c3c3;
		}
			
		.button {
            text-decoration: none;
			color: black;
			background-color: #c3c3c3;
			min-width: 60px;
			text-align: center;
			border-radius: 10px;
			border: 1px solid #cccccc;
			padding: 10px;
        }
		
		#buttons-box
		{
			position: absolute;
			text-align: center;
            bottom: 30px;
			min-width: 100%;
		}
    </style>
	
    <title>Mapa Hydrantów</title>
  </head>
  <body>
	<div id="new_hydrant" class="map">
		<h1>Nowy hydrant</h1>
		<form class="form-inline" action="addHydrant.php" method="post">
			<!--<label for="type">Długość: </label> -->
			<input type="hidden" autocomplete="off" id="Lon" name="Lon">
			<!--<label for="type">Szerokość: </label> -->
			<input type="hidden" id="Lat" name="Lat" autocomplete="off">
			<label for="type">Typ: </label>
			<select id="type" name="type">
				<option value="ground">Hydrant nadziemny</option>
				<option value="underground">Hydrant podziemny</option>
				<option value="prepared">Punkt czerpania wody</option>
				<option value="natural">Zbiornik wodny</option>
			</select><br />
			<label for="type">Nazwa: </label>
			<input type="text" id="name" name="name"><br />
			<label for="type">Nasady: </label>
			<input type="text" id="base" name="base"><br />
			<label for="type">Średnica: </label>
			<input type="text" id="diameter" name="diameter"><br />
			<label for="type">Dojazd: </label>
			<input type="text" id="road" name="road"><br />
			<label for="type">Opis: </label><br />
			<textarea id="description" name="description" rows="4" cols="40"></textarea><br />
			<input type="submit" value="Dodaj" id="Dodaj" autocomplete="off" disabled>
		</form>
		
	</div>
    <div id="map" class="map"></div>
	<div id="info" class="info">
		<a href="#" id="info-closer" class="info-closer"></a>
		<div id="buttons-box">
			<a href="#" class="button" id="delete-button">Skasuj</a>
			<a href="#" class="button" id="modify-button">Modyfikuj</a>
		</div>
		<div id="info-content"></div>
	</div>
	
    <script type="text/javascript">
		function showMap(markers, data){
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
				$('#Dodaj').prop('disabled', false); 
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
			
			//More info about hydrant
			map.on('singleclick', function (event) {
				if (map.hasFeatureAtPixel(event.pixel) === true) {
					map.forEachFeatureAtPixel(event.pixel, function(feature){
						var id = feature.getId();
						if(feature.get("name") == 'hydrant')
						{
							$(".info").css('visibility', "visible");
							var hydrantInformations = '<h2>' + data[id].name + '</h2><br />';
							if(data[id].details.base)
								hydrantInformations += '<b>Nasady:</b> ' + data[id].details.base + '<br />';
							if(data[id].details.diameter)
								hydrantInformations += '<b>Średnica:</b> ' + data[id].details.diameter + '<br />';
							if(data[id].details.road)
								hydrantInformations += '<b>Dojazd:</b> ' + data[id].details.road + '<br />';
							if(data[id].description)
								hydrantInformations += '<b>Opis:</b> ' + data[id].description;
							$("#info-content").html(hydrantInformations);
							
							$("#delete-button").attr("href", "/admin/deleteHydrant.php?id="+id);
							$("#modify-button").attr("href", "/admin/modify.php?id="+id);
							
						}
					});
				} else {
					$(".info").css('visibility', "hidden");
				}
			});
			
			$(".info-closer").click(function () {
				$(".info").css('visibility', "hidden");
				return false;
			});
			
		}
		function setHydrants(hydrants)
		{
			var markers = {};
			var info = {};
			hydrants.forEach(function(hydrant) {
				const tmp = new ol.Feature({
					geometry: new ol.geom.Point(ol.proj.fromLonLat([hydrant.Lon, hydrant.Lat])),
					name: 'hydrant',
				});
				tmp.setId(hydrant.id);
				if(markers[hydrant.type] == null)
					markers[hydrant.type] = [];
				markers[hydrant.type].push(tmp);
				info[hydrant.id] = hydrant;
			});
			
			showMap(markers, info);
		}
		
		$.getJSON( "../getHydrants.php", function(data) {
			setHydrants(data);
		})
		.fail(function() {
			console.log( "error" );
		});
		
	
	</script>
  </body>
</html>