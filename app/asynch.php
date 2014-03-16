<?php
	require_once('./app.services/9292.php');
	require_once('./app.services/seatwave.php');
	require_once('./app.services/foursquare.php');
	require_once('./app.services/google_maps.php');

	header("Content-type: text/xml; charset=utf-8");
	echo '<?xml version="1.0"?>'."\n";
	echo '<results>'."\n";
	$metaData = '';

	$command = (isset($_GET['command']) ? $_GET['command'] : '');
	switch($command)
	{
		case 'getEvents':
			$artist = (isset($_POST['keywords']) ? $_POST['keywords'] : '');

			if(strlen($artist))
			{
				$GBPTOEUR = 1.20;
				$events = service_seatwave::getEvents($artist);

				foreach($events as $ev)
				{
					$venue = $ev['VenueName'];
					$town = $ev['Town'];
					$address = service_foursquare::getAddress($venue, $town);

					echo '<event>'."\n";
						echo '<id>'.$ev['Id'].'</id>'."\n";
						echo '<eventGroupName>'.$ev['EventGroupName'].'</eventGroupName>'."\n";
						echo '<data><![CDATA['.print_r($ev, true).']]></data>'."\n";
						echo '<date><![CDATA['.$ev['Date'].']]></date>'."\n";
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
							echo '<count>'.$ev['TicketCount'].'</count>'."\n";
							echo '<minPrice>'.intval(floatval($ev['MinPrice']) * 100).'</minPrice>'."\n";
						echo '</tickets	>'."\n";
					echo '</event>'."\n";
				}
			}
			break;
		case 'getRoutes':
			$address = (isset($_POST['address']) ? $_POST['address'] : '');
			$city = (isset($_POST['city']) ? $_POST['city'] : '');
			$country = (isset($_POST['country']) ? $_POST['country'] : '');

			if(strlen($address))
			{
				$minTravelPrice = NULL;
				$maxTravelPrice = NULL;

				//Public transport
				$locationFromQuery = 'Vrije Universiteit Amsterdam';
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