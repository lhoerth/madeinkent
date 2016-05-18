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
	
	// 
		
?> 
<!DOCTYPE html>

<html>
<head>
	<meta charset="UTF-8">
	<title>Made in Kent</title>
	
	
	<!-- Bootstrap -->
	<link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css" rel="stylesheet" type="text/css">
	<script src="https://code.jquery.com/jquery-2.2.3.min.js"   integrity="sha256-a23g1Nt4dtEYOj7bR+vTu7+T8VP13humZFBJNIYoEJo="   crossorigin="anonymous"></script>
	<!-- May use bootstrap js features later-->
	<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
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
    <div id="banner">
        
        <h1>Welcome to MadeInKent</h1>
        <p>See the interesting stuff people make here in the city of Kent.</p>
    </div>
    <!-- Nav bar-->

        <nav class="navbar navbar-inverse" role="navigation">
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
            </div>
            <div id="navbar" class="navbar-collapse collapse">
              <ul class="nav navbar-nav navbar-right navbar-collapse collapse">
                <li class="active"><a href="index.html">Home</a></li>
                <li><a href="menu.html">Menu</a></li>
                <li><a href="about.html">About</a></li>
              </ul>
            </div><!--/.nav-collapse -->
          </div><!-- Container-->
        </nav>          
    
    <!-- Nav bar Div
    <div class="navbar navbar-inverse" role="navigation">
        <div class="container">
            <div class="navbar-header">
              <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                <span class="sr-only">Toggle navigation</span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
                <span class="icon-bar"></span>
              </button>
              
            </div>
        </div>    
        <div class="collapse navbar-collapse">
          <ul class="nav navbar-nav">
            <li class="active"><a href="index.html">Home</a></li>
            <li><a href="content.html">Divisions</a></li>
            <li><a href="#projects">Projects</a></li>
            <li><a href="#faqs">FAQs</a></li>
            <li><a href="#resources">Resources</a></li>
            <li><a href="#contact">Contact Us</a></li>
          </ul>
        </div><!--/.nav-collapse -->
    <!--</div>-->





    <!-- Div to Display the map-->
    <div id="map"></div>
       <div id="over_map"> </div>


    <div id="infoPanel"></div>
    
   

<!-- Script to run the map. Markers should be the last thing to load on the page.--> 

<?php
	//error reporting
	ini_set('display_errors', 1);
	error_reporting(E_ALL);
	
	// FOR TESTING: LIMIT here is the max number of rows to provide 
	$sql = "SELECT `BUSINESS NAME`, `ADDRESS LINE 1`, `CITY, STATE & ZIPCODE` FROM `kentbiz` WHERE (`NAICS` RLIKE '^3[1-3].*' OR `SIC` RLIKE '^[23].*')";
	$result = $dbh->query($sql);
?>
	<script>
		var map;
		function initMap() {
			map = new google.maps.Map(document.getElementById('map'), {
				// centered on Kent valley (using creaive ice geocode)
				center: {lat: 47.4276132, lng: -122.2513806},
				zoom: 13,
				mapTypeId: google.maps.MapTypeId.HYBRID
			});
		
			var infoPanel = document.getElementById('infoPanel');
			var placeService = new google.maps.places.PlacesService(map);
			var geocoder = new google.maps.Geocoder();
			var infoWindow = new google.maps.InfoWindow({content:"Information"});
			var address;
			var markers = [];
			
			function placeDetails(place, status) {
			  if (status == google.maps.places.PlacesServiceStatus.OK) {
				console.log("Place details results: ");
				console.log(place);
				console.log(place.photos); 
				
				infoWindow.setContent("<h5>" + place.name + "</h5><p>" + place.formatted_address + "</p>" );				    
				infoPanel.innerHTML = "<h2>" + place.name + "</h2>" + 
				"<p>" + place.formatted_address + "</p>";
				
				if (typeof place.website != 'undefined') {
					infoPanel.innerHTML += "<a href=\"" + place.website + "\" target=\"_blank\">Website: " + place.website + "</a>";
				}
				 
				
				
				if (typeof place.photos != 'undefined') {
					infoPanel.innerHTML += "<div class=\"my-slider\"><ul id=\"slider\"></ul></div>";
					var slider = document.getElementById('slider');
					for (let photo of place.photos) {
						photoOpt = {'maxWidth': 400, 'maxHeight': 400};
					slider.innerHTML += "<li><img class=\"photo\" src=\"" + place.photos[0].getUrl(photoOpt) + "\"></li>";
					}
					$('.my-slider').unslider()
				}
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
			
			function addMarker(point, placeTitle, address) {
				var marker = new google.maps.Marker({
					map: map,
					position: new google.maps.LatLng(point),
					title: placeTitle,
					draggable: false
				});
				
				marker.addListener('click', function(){
					infoWindow.setContent("<h5>" + marker.getTitle() + "</h5><p>" + address + "</p>");
					infoPanel.innerHTML = infoWindow.content;
					//infoWindow.open(map, marker);
					
					// request place info
					var request = {
						location: point,
						radius: '1000',
						name: placeTitle
					};
					
					// placeInfo() handles response, callback to placeID() function
					placeService.nearbySearch(request, placeID);
				});
				
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
				
			
			
			function geocodeFromGoogle(title, address, token) {
				// TEST
				// cacheGeocode(title, address, {lat: 47.4276132, lng: -122.2513806}, token);
				geocoder.geocode({'address': address}, function(results, status){
					if (status === google.maps.GeocoderStatus.OK){
						//console.log(results[0].geometry.location.lat());
						cacheGeocode(address, results[0].geometry.location, token);
						addMarker(results[0].geometry.location.toJSON(), title, address);
					} else {
						console.log('Geocode was not successul for the following reason: ' + status);
					}
				});
			}
		
		// takes business address and instantiates marker at the calculated coordinates.
		<?php 
		
			foreach ($result as $row) { 
				unset($address);
				$address = $row['ADDRESS LINE 1'] . ', ' . $row['CITY, STATE & ZIPCODE'];
				$title = $row['BUSINESS NAME'];
		?>
				address = <?php echo '"', $address, '"'; ?>;
				title = <?php echo '"', $title, '"'; ?>;
		<?php
				include 'scripts/geocode.php';
				if (!empty($location)) {
					// cached geocode...
					echo 'console.log("Location From Cache: " + title);
					console.log(' . $location . ');
					addMarker(' . $location . ', title, address);';
				}
				else {
					// need to cache the coordinates...
					
					echo 'console.log(title + " not found in cache...");';
					
					$token = bin2hex(openssl_random_pseudo_bytes(16));
					$_SESSION["$token"] = 1;
					
					?>
					geocodeFromGoogle(title, address, <?='"', $token,'"';?>);
					<?php
				}
			 } 
		?>
			var markerCluster = new MarkerClusterer(map, markers);
        }
    </script>
	
	<script src="js/markerclusterer_compiled.js"></script>
    
    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyCVt8aXjF0XkGVkgeU0cyVAxkJT1EVulKc&libraries=places&callback=initMap"
    async defer></script>
	
    <!-- for sliding through Place photos-->
    <script src="js/unslider-master/dist/js/unslider-min.js"></script> 
	<script>$(function() { $('.my-slider').unslider() })</script>

</body>
</html>