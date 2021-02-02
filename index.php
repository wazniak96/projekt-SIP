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
		#map {
			width: 100%;
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
			left: 0;
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
    </style>
	
    <title>Mapa Hydrantów</title>
  </head>
  <body>
    <div id="map" class="map"></div>
	<div id="info" class="info">
		<a href="#" id="info-closer" class="info-closer"></a>
		<div id="info-content"></div>
	</div>
	
    <script type="text/javascript">
		function showMap(markers, data){
			const map = new ol.Map({
				target: 'map',
				layers: [
					new ol.layer.Tile({
						source: new ol.source.OSM(),
					})
				],
				view: new ol.View({
					center: ol.proj.fromLonLat([17.67, 54.62]),
					zoom: 7.5
				})
			});
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
					name: hydrant.description,
				});
				tmp.setId(hydrant.id);
				if(markers[hydrant.type] == null)
					markers[hydrant.type] = [];
				markers[hydrant.type].push(tmp);
				info[hydrant.id] = hydrant;
			});
			
			showMap(markers, info);
		}
		
		$.getJSON( "getHydrants.php", function(data) {
			setHydrants(data);
		})
		.fail(function() {
			console.log( "error" );
		});
		
	
	</script>
  </body>
</html>