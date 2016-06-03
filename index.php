<?php
	// php header
	session_start();
    	require '/home/logan/creds/dblogin.php';
		
	try {
		$dbh = new PDO("mysql:host=$hostname;
		dbname=logan_madeinkent", $username, $password);
		//echo "Connected to database.";
	}
	catch (PDOException $e) {
		echo $e->getMessage(); 
	}
	
	//error reporting
	ini_set('display_errors', 1);
	error_reporting(E_ALL);		
?>
<!DOCTYPE html>

<html>
<head>
	<meta charset="UTF-8">
	<title>Made in Kent</title>
	
	
	<!-- Bootstrap -->
	<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" type="text/css">
	
	<!-- Adding jQuery for unslider to work -->
	<script src="https://code.jquery.com/jquery-2.2.3.min.js" integrity="sha256-a23g1Nt4dtEYOj7bR+vTu7+T8VP13humZFBJNIYoEJo=" crossorigin="anonymous"></script>
	
	<!-- May use bootstrap js features later-->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
	
	<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCVt8aXjF0XkGVkgeU0cyVAxkJT1EVulKc&libraries=places"></script>
	
	<script src="js/maplabel-compiled.js"></script>
	
	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css">
	<link rel="stylesheet" href="style/style.css" type="text/css">
	
	<link rel="stylesheet" href="js/unslider-master/dist/css/unslider.css">
</head>

<body>
    
    <!--
    <div class="masthead">
        <img src="images/web-banner-start.png"/>

    </div>-->
        <!-- Display the banner for businesses-->
    
    <!-- Nav bar-->

	<nav class="navbar navbar-default" id="nav_bar" role="navigation">
	  <div class="container">
		<div class="navbar-header">
		  <button type="button" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar" aria-expanded="false" aria-controls="navbar">
			<span class="sr-only">Toggle navigation</span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
			<span class="icon-bar"></span>
		  </button>
			<a class="navbar-brand" href="#">
				<img alt="Brand" src="images/kent-wa-logo.png">
			</a>
			<div class="navbar-intro">
				<h3>Welcome to MadeInKent</h3>
				<p>See the interesting stuff made in the city of Kent, Washington</p>
			</div>
		</div>
		<div id="navbar" class="navbar-collapse collapse ">
		  <ul class="nav navbar-nav navbar-right navbar-collapse collapse pull-xs-right">
			<li class="active"><a href="index.php">Home</a></li>
			
			<li><a href="about.html">About</a></li>
			
			<li id="actBtn" class="navbar-btn">
				<a href="http://kentwa.gov/content.aspx?id=2102" target="_blank" class="btn btn-default ">Add your business!</a>
			</li>
		  </ul>
		</div><!--/.nav-collapse -->
	  </div><!-- Container-->
	</nav>          

	<!-- Panel left of map -->
	<!--
	<div id="lpanel" class="col-md-1"></div>
	-->
	
	<div class="content-wrapper">
		<div class="map-wrapper">
			<!-- Div to Display the map-->
			<div id="map"></div>
		</div>

		<div id="infoPanel">
			<div id="ipHeader">
				<h2>Click a pin on the map!</h2>
				<p>See more info about a business by clicking its pin.</p>
			</div>
		</div>
	</div>
	
	<div id="legend">
	  <p>Legend </p>
	</div>
	
	<div id="myModal" class="modal fade" role="dialog">
		<div class="modal-dialog">
		
			<!-- Modal content-->
			<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal">×</button>
				<h4 class="modal-title">Manufacturer Info</h4>
			</div>
				<div class="modal-body">
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
			</div>
		</div>	
	  </div>
	</div>

<!-- Script to run the map. Markers should be the last thing to load on the page.--> 

	<script>
		String.prototype.toCamel = function(){
			var re = /(\b[a-z](?!\s))/g;
			return this.toLowerCase().replace(re, function(x){
				return x.toUpperCase();
			});
		};

		function addClass(el, className) {
		  if (el.classList)
			el.classList.add(className)
		  else if (!hasClass(el, className)) el.className += " " + className
		}
		
		function removeClass(el, className) {
		  if (el.classList)
			el.classList.remove(className)
		  else if (hasClass(el, className)) {
			var reg = new RegExp('(\\s|^)' + className + '(\\s|$)')
			el.className=el.className.replace(reg, ' ')
		  }
		}
		
		var map;
		
		function initMap() {
			
			map = new google.maps.Map(document.getElementById('map'), {
				// centered on Kent valley 
				center: new google.maps.LatLng(47.40924755801687, -122.24910480642092),
				zoom: 14,
				mapTypeId: google.maps.MapTypeId.HYBRID
			});
			
			map.controls[google.maps.ControlPosition.LEFT_BOTTOM].push(
				document.getElementById('legend'));
			
			var mapLabel = new MapLabel({
				  text: 'Kent',
				  position: new google.maps.LatLng(47.40802772670872, -122.23185283803713),
				  map: map,
				  fontSize: 25,
				  align: 'right',
				});
				mapLabel.set('position', new google.maps.LatLng(47.40802772670872, -122.23185283803713));
			
			// uncomment to make label draggable and log dragend coords
			/*
			var zmarker = new google.maps.Marker();
			zmarker.bindTo('map', mapLabel);
			zmarker.bindTo('position', mapLabel);
			zmarker.setDraggable(true);
			zmarker.addListener('dragend',function(){
				console.log('LAT LNG: '); 
				console.log(zmarker.getPosition());
			});
			*/
		
			var infoPanel = document.getElementById('infoPanel');
			var infoModal = document.getElementsByClassName('modal-body')[0];
			var placeService = new google.maps.places.PlacesService(map);
			var geocoder = new google.maps.Geocoder();
			var infoWindow = new google.maps.InfoWindow({content:"Information"});
			var address = "";
			var title = "";
			//var nEmployees = 0;
			var companySize = "Unknown";
			var sic;
			var naics;
			
			// for clusterer
			var markers = [];
			
			// URLs for marker icons
			var iconBase = 'http://maps.google.com/mapfiles/kml/';
			var icons = {
			// SIC codes (20 - 39)
			  '20': {
				icon: iconBase + 'paddle/A.png'
			  },
			  '21': {
				icon: iconBase + 'paddle/B.png'
			  },
			  '22': {
				icon: iconBase + 'paddle/C.png'
			  },
			  '23': {
				icon: iconBase + 'paddle/D.png'
			  },
			  '24': {
				icon: iconBase + 'paddle/E.png'
			  },
			  '25': {
				icon: iconBase + 'paddle/F.png'
			  },
			  '26': {
				icon: iconBase + 'paddle/G.png'
			  },
			  '27': {
				icon: iconBase + 'paddle/H.png'
			  },
			  '28': {
				icon: iconBase + 'paddle/I.png'
			  },
			  '29': {
				icon: iconBase + 'paddle/J.png'
			  },
			  '30': {
				icon: iconBase + 'paddle/K.png'
			  },
			  '31': {
				icon: iconBase + 'paddle/L.png'
			  },
			  '32': {
				icon: iconBase + 'paddle/M.png'
			  },
			  '33': {
				icon: iconBase + 'paddle/N.png'
			  },
			  '34': {
				icon: iconBase + 'paddle/O.png'
			  },
			  '35': {
				icon: iconBase + 'paddle/P.png'
			  },
			  '36': {
				icon: iconBase + 'paddle/Q.png'
			  },
			  '37': {
				icon: iconBase + 'paddle/R.png'
			  },
			  '38': {
				icon: iconBase + 'paddle/S.png'
			  },
			  '39': {
				icon: iconBase + 'paddle/T.png'
			  },
			// NAICS codes
			  '311': { // A
				icon: iconBase + 'pal5/icon48.png'
			  },
			  '312': { // B
				icon: iconBase + 'pal5/icon49.png'
			  },
			  '313': { // C
				icon: iconBase + 'pal5/icon50.png'
			  },
			  '314': { // D
				icon: iconBase + 'pal5/icon51.png'
			  },
			  '315': { // E
				icon: iconBase + 'pal5/icon52.png'
			  },
			  '316': { // F
				icon: iconBase + 'pal5/icon53.png'
			  },
			  '321': { // G
				icon: iconBase + 'pal5/icon54.png'
			  },
			  '322': { // H
				icon: iconBase + 'pal5/icon55.png'
			  },
			  '323': { // I
				icon: iconBase + 'pal5/icon40.png'
			  },
			  '324': { // J
				icon: iconBase + 'pal5/icon41.png'
			  },
			  '325': { // K
				icon: iconBase + 'pal5/icon42.png'
			  },
			  '326': { // L
				icon: iconBase + 'pal5/icon43.png'
			  },
			  '327': { // M
				icon: iconBase + 'pal5/icon44.png'
			  },
			  '331': { // N
				icon: iconBase + 'pal5/icon45.png'
			  },
			  '332': { // O
				icon: iconBase + 'pal5/icon46.png'
			  },
			  '333': { // P
				icon: iconBase + 'pal5/icon47.png'
			  },
			  '334': { // Q
				icon: iconBase + 'pal5/icon24.png'
			  },
			  '335': { // R
				icon: iconBase + 'pal5/icon25.png'
			  },
			  '336': { // S
				icon: iconBase + 'pal5/icon26.png'
			  },
			  '337': { // T
				icon: iconBase + 'pal5/icon27.png'
			  },
			  '339': { // U
				icon: iconBase + 'pal5/icon28.png'
			  }
			};
			
			var legend = document.getElementById('legend');
			for (var i in icons) {
				var name = i;
				var icon = icons[i].icon;
				var div = document.createElement('div');
				div.innerHTML = '<img src="' + icon + '"> ' + name;
				legend.appendChild(div);
			}
			
			function placeDetails(place, status) {
			  if (status == google.maps.places.PlacesServiceStatus.OK) {
				console.log("Place details results: ");
				console.log(place);
				console.log(place.photos); 
				
				//infoWindow.setContent();				    
				infoModal.innerHTML = 
					"<h2>" + place.name + "</h2>" + 
					"<p>" + place.formatted_address + "</p>" + 
					"<h4>Company size: " + companySize + "</h4>" +
					"<div class=\"form\">";
				
				
				
				if (typeof place.website != 'undefined') {					
					infoModal.innerHTML += 
					"<fieldset class=\"form-group\">" + 
						"<legend>Website: </legend>" + 
						"<p><a href=\"" + place.website + "\" target=\"_blank\">" + place.website + "</a>"
						+ "</p>" + 
						"<p><a target=\"_blank\" href=\"" + place.website + "\"><img " + 
							"alt=\"" + place.name + " - website thumbnail\" " + 
							"src=\"http://free.pagepeeker.com/v2/thumbs.php?size=l&url=" 
							+ place.website.replace(/^https?\:\/\//i, "") + "\"></a>" + 
						"</p>" + 
					"</fieldset>";
				}
				
				 
				
				/* (images slider) */
				if (typeof place.photos != 'undefined') {
					infoModal.innerHTML += "<div class=\"my-slider form-group\"><ul id=\"slider\"></ul></div>";
					//var slider = document.getElementById('slider');
					//let photo;
					for (var photo in place.photos) {
						photoOpt = {'maxWidth': 400, 'maxHeight': 400};
						slider.innerHTML += "<li><img class=\"photo\" src=\"" + place.photos[0].getUrl(photoOpt) + "\"></li>";
					}
					$('.my-slider').unslider()
				}
				
				infoModal.innerHTML += "</div>";
			  }
			}
			
			function placeID(results, status) {
				// parse response and update div
				if (status == google.maps.places.PlacesServiceStatus.OK) {
					console.log("Place search results: ");
					console.log(results); 
					//.icon is a thing... .name is place's name
					// also: .types type(s) of place it is
					// place_id is what we want.
					
					//results[0].place_id;
					
					placeService.getDetails({placeId: results[0].place_id}, placeDetails);
				}
			}
			
			function addMarker(point, placeTitle, address, coSize, bizCode) {
				var marker = new google.maps.Marker({
					map: map,
					position: new google.maps.LatLng(point),
					title: placeTitle,
					draggable: false,
					//,icon: icons[bizCode.substring(0,2)].icon
				});
				
				var bizid = 'bizid-' + markers.length;
				var bizDiv = document.createElement("div");
				
				var nDivs = markers.length;
				
				function openModal(){
					// WHAT DOES THIS LINE ACCOMPLISH?!?!?!?! (don't delete it)
					companySize = coSize;
					
					//infoWindow.setContent("<h5>" + marker.getTitle() + "</h5><p>" + address + "</p>");
					//console.log(placeTitle.toCamel());
					infoModal.innerHTML =  
						"<h2>" + placeTitle.toCamel() + "</h2>" + 
						"<p>" + address + 
						"</p>" + 
						"<h4>Company size: " + coSize + "</h4>";
					
					$('#myModal').modal('show'); 
					
					//infoWindow.open(map, marker);
					
					// request place info
					var request = {
						location: point,
						radius: '1000',
						name: placeTitle
					};
					
					// placeInfo() handles response, callback to placeID() function
					placeService.nearbySearch(request, placeID);
				}
				
				//console.log("bizid: " + bizid);
				
				if (typeof icons[bizCode] != 'undefined'){
					marker.setIcon(icons[bizCode].icon);
					console.log("marker.getIcon()" + marker.getIcon());
				}
				else {
					console.log("Marker for " + placeTitle + " undefined.");
				}
				
				//console.log("bizCode: " + bizCode);
				
				bizDiv.id = bizid;
				bizDiv.innerHTML = 
					"<h4>" + placeTitle.toCamel() + "</h4>" + 
					"<p>" + address + 
					"</p>" + 
					"<p>Company size: " + coSize + "</p>";
				
				infoPanel.appendChild(bizDiv);
				
				// Highlighting corresponding controls
					
				// this happens whenever the mouse moves over a listing entry in the info panel
				bizDiv.addEventListener('mouseenter', function(){
					marker.setIcon(iconBase + "shapes/target.png");
					//console.log("hovered on: " + this);
				});
				
				//console.log("bizDiv.attributes after adding event listener: ");
				//console.log(bizDiv.attributes);
				
				// This reverts what the mouseover did when the mouse exits the listing entry div
				bizDiv.addEventListener('mouseleave', function(){
					marker.setIcon(icons[bizCode].icon);
					//console.log("mouseout: " + placeTitle);
				});
				
				marker.addListener('mouseover', function(){
					//console.log('nDivs: ' + nDivs + ' bizDiv height: ' + bizDiv.getBoundingClientRect().height)
					infoPanel.scrollTop = nDivs * (bizDiv.getBoundingClientRect().height + 10);
					addClass(bizDiv, 'ipFocus');
				});
				
				marker.addListener('mouseout', function(){
					removeClass(bizDiv, 'ipFocus');
				});
 
				// Triggering the Modal:
				
				bizDiv.addEventListener('click', openModal);
				
				// this happens whenever you click a marker.
				marker.addListener('click', openModal);
				
				markers.push(marker);
			}
			
			function cacheGeocode(address, location, token){
				console.log("Caching geocode for " + address + "...");
				//console.log(location);
				//console.log(location.lat());
				var xmlHttp = new XMLHttpRequest();
				var url = "scripts/cache.php";
				url += "?postcode=" + encodeURIComponent(address);
				url += "&latitude=" + location.lat();
				url += "&longitude=" + location.lng();
				url += "&token=" + token;
				 
				xmlHttp.onreadystatechange = function() { 
					if (xmlHttp.readyState == 4 && xmlHttp.status == 200)
							console.log(xmlHttp.responseText);
				}
				xmlHttp.open("GET", url, true); // true for asynchronous 
				xmlHttp.send(null);
			}			
			
			function geocodeFromGoogle(title, address, token, coSize) {	
				// TEST
				// cacheGeocode(title, address, {lat: 47.4276132, lng: -122.2513806}, token);
				geocoder.geocode({'address': address}, function(results, status){
					if (status === google.maps.GeocoderStatus.OK){
						//console.log(results[0].geometry.location.lat());
						cacheGeocode(address, results[0].geometry.location, token);
						addMarker(results[0].geometry.location.toJSON(), title, address, coSize, bizCode);
					} else {
						console.log('Geocode was not successul for the following reason: ' + status);
					}
				});
			}
		
		// takes business address and instantiates marker at the calculated coordinates.
		<?php 
			// FOR TESTING: LIMIT here will set the max number of rows to provide 
			$sql = "SELECT `BUSINESS NAME`, `ADDRESS LINE 1`, `CITY, STATE & ZIPCODE`, `FULL`, `PART`, `SIC`, `NAICS` FROM `kentbiz` WHERE (`NAICS` RLIKE '^3[1-3].*' OR `SIC` RLIKE '^[23].*')";
			$result = $dbh->query($sql);
			
			foreach ($result as $row) { 
				unset($address);
				$address = $row['ADDRESS LINE 1'] . ', ' . $row['CITY, STATE & ZIPCODE'];
				$title = $row['BUSINESS NAME'];
				$companySize = "Unknown number of";
				$sic = $row['SIC'];
				$naics = $row['NAICS'];
				
				// No. of employees = No. of fulltime + No. of parttime.
				$nEmployees = intval($row['FULL']) + intval($row['PART']);
				
				if ($nEmployees < 50){
					$companySize = "0 - 49";
				}
				else if ($nEmployees < 100){
					$companySize = "50 - 99";
				}
				else if ($nEmployees < 150){
					$companySize = "100 - 149";
				}
				else if ($nEmployees < 200){
					$companySize = "150 - 199";
				}
				else if ($nEmployees >= 200){
					$companySize = "200+";
				}
				
				$companySize = $companySize . " employees.";
		?>
				address = <?php echo '"', $address, '"'; ?>;
				title = <?php echo '"', $title, '"'; ?>;
				companySize = <?php echo '"', $companySize, '"'; ?>;
				naics = <?php echo '"', $naics, '"'; ?>;
				sic = <?php echo '"', $sic, '"'; ?>;
				
				/*
				console.log("full and part: ");
				console.log(<?php echo '"', intval($row['FULL']), '"'; ?>);
				console.log(<?php echo '"', intval($row['PART']), '"'; ?>);
				console.log(<?php echo '"', $nEmployees, '"'; ?>)
				*/
				
				// console.log("sic: " + sic + " naics: " + naics);
				var bizCode = sic.substring(0,2);
				
				var naicsPrefix = naics.substring(0,2);
				if (naics != '' && (naicsPrefix == '31' || naicsPrefix == '32' || naicsPrefix == '33')){
					bizCode = naics.substring(0,3);
				}
				
		<?php
				include 'scripts/geocode.php';
				if (!empty($location)) {
					// cached geocode...
					echo 'console.log("Location From Cache: " + title);
					console.log(' . $location . ');
					addMarker(' . $location . ', title, address, companySize, bizCode);';
				}
				else {
					// need to cache the coordinates...
					
					echo 'console.log(title + " not found in cache...");';
					
					$token = bin2hex(openssl_random_pseudo_bytes(16));
					$_SESSION["$token"] = 1;
					
					?>
					geocodeFromGoogle(title, address, <?='"', $token,'"';?>, companySize, bizCode);
					<?php
				}
			 } 
		?>
			
			// UNCOMMENT to cluster the markers
			var markerCluster = new MarkerClusterer(map, markers);
			
			// Uncomment to aggregate and log stats about business codes
			for (var i in icons) {
				//console.log("ICON ELEMENT!");
				console.log("ICON ELEMENT:  " + icons[i].icon + " bizCode: " + i);
				var count = 0;
				for (var j = 0; j < markers.length; j++) {
					//console.log("marker: ");
					//console.log(markers[j].getIcon());
					if (markers[j].getIcon() == icons[i].icon) {
						count++;
					}
				}
				//console.log("AH AH AH!");
				console.log(count);
			}
        }
		google.maps.event.addDomListener(window, 'load', initMap);
    </script>
	
	<script src="js/markerclusterer_compiled.js"></script>
	
    <!-- for sliding through Place photos-->
    <script src="js/unslider-master/dist/js/unslider-min.js"></script> 
	<script>$(function() { $('.my-slider').unslider() })</script>

</body>
</html>