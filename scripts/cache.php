<?php
session_start(); 
//if($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest') {
  //Request identified as ajax request
//	error_log("XMLHttpRequest was set", 3, "my-errors.log");
  if(@isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER']=="http://logan.greenrivertech.net/IT/madeinkent/"){
  	error_log("HTTP_REFERER was set".$_SERVER['REMOTE_ADDR']."\n", 3, "my-errors.log");
   //HTTP_REFERER verification
    if(isset($_GET['token']) && isset($_SESSION[$_GET['token']])) {
    	unset($_SESSION[$_GET['token']]);
    	error_log("Token Verified:". $_GET['token'] . "\n" , 3, "my-errors.log");
	require '/home/logan/creds/dblogin.php';
	$postcode = $_GET['postcode'];
	$lat = $_GET['latitude'];
	$lon = $_GET['longitude'];
	
	try {
		$dbh = new PDO("mysql:host=$hostname; dbname=logan_madeinkent", $username, $password);
		echo "Connected to database.";
		error_log("Connected to Database!\n", 3, "my-errors.log");
	}
	catch (PDOException $e) {
		echo $e->getMessage(); 
	}
	
	$sql = "INSERT into `postcodes` (`postcode`, `latitude`, `longitude`, `date_added`) VALUES (UPPER(:postcode), :lat, :lon, NOW())";
	
	$statement = $dbh->prepare($sql);
	
	error_log(implode(", ", $dbh->errorInfo()),3,"my-errors.log");
	
	$statement->bindParam(':postcode', $postcode, PDO::PARAM_STR);
	$statement->bindParam(':lat', $lat, PDO::PARAM_STR);
	$statement->bindParam(':lon', $lon, PDO::PARAM_STR);
	$statement->execute();
	
	error_log(implode(", ", $dbh->errorInfo()),3,"my-errors.log");
    }
    else {
    	if (isset($_GET['token'])){
  		error_log("FAILURE! Token received was:". $_GET['token'] . "\n" , 3, "my-errors.log");
  		error_log("Session token:". $_SESSION['token'] . "\n" , 3, "my-errors.log");
  	}
  	else{
		error_log("token NOT received!\n", 3, "my-errors.log");
	}
    }
  }
  else {
  	if (isset($_SERVER['HTTP_REFERER']))
  		error_log("HTTP_REFERER:". $_SERVER['HTTP_REFERER'] . "\n" , 3, "my-errors.log");
  	else
		error_log("HTTP_REFERER was NOT set\n", 3, "my-errors.log");
  }
/*
}
else {
	error_log("XMLHttpRequest was NOT set\n", 3, "my-errors.log");
}*/
  	
  
?>