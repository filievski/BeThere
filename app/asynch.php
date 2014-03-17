<?php
	set_time_limit(120);

	require_once('./app.includes/sparqllib.php');

	require_once('./app.services/9292.php');
	require_once('./app.services/seatwave.php');
	require_once('./app.services/foursquare.php');
	require_once('./app.services/google_maps.php');
	require_once('./app.services/dbpedia.php');
	require_once('./app.services/sesame.php');

	header("Content-type: text/xml; charset=utf-8");
	echo '<?xml version="1.0"?>'."\n";
	echo '<results>'."\n";

	$command = (isset($_GET['command']) ? $_GET['command'] : '');
	switch($command)
	{
		case 'getEvents':
			$artist = (isset($_POST['keywords']) ? $_POST['keywords'] : (isset($_GET['keywords']) ? $_GET['keywords'] : ''));

			if(strlen($artist))
			{
				$events = service_seatwave::getEvents($artist);

				for($i = 0; $i < sizeof($events); $i++)
				{
					$venue = $events[$i]['VenueName'];
					$town = $events[$i]['Town'];
					$events[$i]['fs_address'] = service_foursquare::getAddress($venue, $town);
				}

				if(sizeof($events))
				{
					service_sesame::insertData($artist, $events);
				}

				foreach($events as $event)
				{
					$venue = $event['VenueName'];
					$town = $event['Town'];
					$address = service_foursquare::getAddress($venue, $town);

					echo '<event>'."\n";
						echo '<id>'.$event['Id'].'</id>'."\n";
						echo '<eventGroupName>'.$event['EventGroupName'].'</eventGroupName>'."\n";
//						echo '<data><![CDATA['.print_r($event, true).']]></data>'."\n";
						echo '<date><![CDATA['.$event['Date'].']]></date>'."\n";
						echo '<venue>'."\n";
							echo '<name><![CDATA['.$venue.']]></name>'."\n";
							echo '<town><![CDATA['.$town.']]></town>'."\n";							
						echo '</venue>'."\n";
						echo '<location>'."\n";
							echo '<address>'.$address['address'].'</address>'."\n";
							echo '<lat>'.$address['lat'].'</lat>'."\n";
							echo '<lng>'.$address['lng'].'</lng>'."\n";
							echo '<postalCode>'.$address['postalCode'].'</postalCode>'."\n";
							echo '<cc>'.$address['cc'].'</cc>'."\n";
							echo '<city>'.$address['city'].'</city>'."\n";
							echo '<country>'.$address['country'].'</country>'."\n";
						echo '</location>'."\n";							
						echo '<tickets>'."\n";
							echo '<count>'.$event['TicketCount'].'</count>'."\n";
							echo '<minPrice>'.intval(floatval($event['MinPrice']) * 100).'</minPrice>'."\n";
						echo '</tickets	>'."\n";
					echo '</event>'."\n";
				}
			}
			break;
		case 'getRoutes':
			$location_start = (isset($_POST['location_start']) ? $_POST['location_start'] : '');
			$address = (isset($_POST['address']) ? $_POST['address'] : '');
			$city = (isset($_POST['city']) ? $_POST['city'] : '');
			$country = (isset($_POST['country']) ? $_POST['country'] : '');

			if(strlen($location_start) && strlen($address))
			{
				$minTravelPrice = NULL;
				$maxTravelPrice = NULL;

				//Public transport
				$locationFromQuery = $location_start;
				$locationToQuery = $address.(strlen($city) ? ', ' : '').$city.(strlen($country) ? ', ' : '').$country;

				$locations_from = service_9292::getSuggestions($locationFromQuery);
				$locations_to = service_9292::getSuggestions($locationToQuery);
				$routes = service_9292::getRoutes($locations_from[0], $locations_to[0]);
				foreach($routes as $route)
				{
					$departure = strtotime($route['departure']);
					$arrival = strtotime($route['arrival']);
					$price = intval($route['fareInfo']['fullPriceCents']);

					if($minTravelPrice === NULL)
					{
						$minTravelPrice = $price;
					}
					else if($price < $minTravelPrice)
					{
						$minTravelPrice = $price;
					}

					if($maxTravelPrice === NULL)
					{
						$maxTravelPrice = $price;
					}
					else if($price > $maxTravelPrice)
					{
						$maxTravelPrice = $price;
					}

					echo '<route>'."\n";
						echo '<type>public_transport</type>'."\n";
						echo '<departure>'.$departure.'</departure>'."\n";
						echo '<arrival>'.$arrival.'</arrival>'."\n";
						echo '<transfers>'.$route['numberOfChanges'].'</transfers>'."\n";
						echo '<price>'.$price.'</price>'."\n";							
					echo '</route>'."\n";
				}

				//Car routes
				$routes = service_google_maps::getRoutes($locationFromQuery, $locationToQuery);
				foreach($routes as $route)
				{
					$distance = $route['distance'];
					$price = intval($route['price']);

					if($minTravelPrice === NULL)
					{
						$minTravelPrice = $price;
					}
					else if($price < $minTravelPrice)
					{
						$minTravelPrice = $price;
					}

					if($maxTravelPrice === NULL)
					{
						$maxTravelPrice = $price;
					}
					else if($price > $maxTravelPrice)
					{
						$maxTravelPrice = $price;
					}

					echo '<route>'."\n";
						echo '<type>car</type>'."\n";
						echo '<distance>'.$distance.'</distance>'."\n";
						echo '<price>'.$price.'</price>'."\n";							
					echo '</route>'."\n";
				}

				echo '<meta>'."\n";
					echo '<minPrice>'.$minTravelPrice.'</minPrice>';
					echo '<maxPrice>'.$maxTravelPrice.'</maxPrice>';
				echo '</meta>'."\n";
			}
		break;
	}
	echo '</results>'."\n";
?>