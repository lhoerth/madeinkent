<?php

	$location;
	
	// stop from using previous values.
	unset($location);
	
	$sql = "SELECT latitude, longitude FROM  `postcodes` WHERE `postcode` = UPPER('" . $address . "') AND `date_added` >  DATE_SUB(NOW(), INTERVAL 30 DAY)";
	//$sql = "SELECT `BUSINESS NAME`, `ADDRESS LINE 1`, `CITY, STATE & ZIPCODE` FROM `businesses`";
	
	if ($result = $dbh->query($sql))
	{
		if ($dbh->query('select count(*) from postcodes')->rowCount() > 0)
		{
			$geocode = $result->fetch(PDO::FETCH_ASSOC);
			if (!empty($geocode))
				$location = json_encode(array('lat' => floatval($geocode['latitude']), 'lng' => floatval($geocode['longitude'])));
		}
	}
?>