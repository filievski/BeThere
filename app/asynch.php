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
		case 'getData':
			$query = (isset($_POST['query']) ? $_POST['query'] : (isset($_GET['query']) ? $_GET['query'] : ''));
			if(strlen($query))
			{
				$data = service_sesame::getData($query);
	
				$vars = array();
				foreach($data->head->vars as $var)
				{
					$vars[] = $var;
				}
	
				foreach($data->results->bindings as $binding)
				{
					echo "\t".'<result>'."\n";
					foreach($vars as $var)
					{
						echo "\t"."\t".'<'.$var.'>'.$binding->$var->value.'</'.$var.'>'."\n";
					}
					echo "\t".'</result>'."\n";
				}
			}
			break;
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


				$query = '
							PREFIX rdfs:<http://www.w3.org/2000/01/rdf-schema#>
							PREFIX onto:<http://www.ontotext.com/>
							PREFIX owl:<http://www.w3.org/2002/07/owl#>
							PREFIX xsd:<http://www.w3.org/2001/XMLSchema#>
							PREFIX rdf:<http://www.w3.org/1999/02/22-rdf-syntax-ns#>

							SELECT
								DISTINCT
									?resource
							WHERE
							{
								?resource	rdf:type									<http://seatwave.com/resource/Event>;
											<http://seatwave.com/resource/hasArtist>	?artist.
								?artist		<http://xmlns.com/foaf/0.1/name>			"'.$artist.'";
							}
							';

				$eventResources = service_sesame::getData($query);

				foreach($eventResources->results->bindings as $eventResource)
				{
					$eventRes = $eventResource->resource->value;
					$query = '
								PREFIX rdfs:<http://www.w3.org/2000/01/rdf-schema#>
								PREFIX onto:<http://www.ontotext.com/>
								PREFIX owl:<http://www.w3.org/2002/07/owl#>
								PREFIX xsd:<http://www.w3.org/2001/XMLSchema#>
								PREFIX rdf:<http://www.w3.org/1999/02/22-rdf-syntax-ns#>
	
								SELECT
									DISTINCT
										?resource
										(SAMPLE(?artist) as ?artist)
										(SAMPLE(?artistName) as ?artistName)
										(SAMPLE(?artistBirthDate) as ?artistBirthDate)
										(SAMPLE(?artistDesc) as ?artistDesc)
										(SAMPLE(?artistImage) as ?artistImage)
										(SAMPLE(?date) as ?date)
										(SAMPLE(?venue) as ?venue)
										(SAMPLE(?venueName) as ?venueName)
										(SAMPLE(?venueAddress) as ?venueAddress)
										(SAMPLE(?venueCity) as ?venueCity)
										(SAMPLE(?venueCountry) as ?venueCountry)
										(SAMPLE(?town) as ?town)
										(SAMPLE(?tickets) as ?tickets)
										(SAMPLE(?price) as ?price)
								WHERE
								{
									<'.$eventRes.'>		<http://seatwave.com/resource/hasArtist>	?artist;
														<http://seatwave.com/resource/date>			?date;
														<http://seatwave.com/resource/venue>		?venue;
														<http://seatwave.com/resource/town>			?town;
														<http://seatwave.com/resource/tickets> 		?tickets;
														<http://seatwave.com/resource/price>		?price.
									?artist				<http://xmlns.com/foaf/0.1/name>			?artistName;
														<http://dbpedia.org/page/birthdate>			?artistBirthDate;
														<http://dbpedia.org/page/shortdesc>			?artistDesc;
														<http://dbpedia.org/page/image>				?artistImage.
									?venue				<http://foursquare.com/resource/name>		?venueName;
														<http://foursquare.com/resource/address>	?venueAddress;
														<http://foursquare.com/resource/city>		?venueCity;
														<http://foursquare.com/resource/country>	?venueCountry.
								}
								GROUP BY ?resource
								';
	
					$fields = array('date', 'town', 'tickets');
	
					$events = service_sesame::getData($query);

					foreach($events->results->bindings as $event)
					{
						echo '<event>'."\n";
							foreach($fields as $field)
							{
								echo '<'.$field.'><![CDATA['.$event->$field->value.']]></'.$field.'>'."\n";
							}
							echo '<price>'.intval(floatval($event->price->value) * 100).'</price>'."\n";
							echo '<artist>'."\n";
//								echo '<resource><![CDATA['.$event->artist->value.']]></resource>'."\n";
								echo '<name><![CDATA['.$event->artistName->value.']]></name>'."\n";
								echo '<birthDate><![CDATA['.$event->artistBirthDate->value.']]></birthDate>'."\n";
								echo '<desc><![CDATA['.$event->artistDesc->value.']]></desc>'."\n";
								echo '<image><![CDATA['.$event->artistImage->value.']]></image>'."\n";
							echo '</artist>'."\n";
							echo '<venue>'."\n";
//								echo '<resource><![CDATA['.$event->venue->value.']]></resource>'."\n";
								echo '<name><![CDATA['.$event->venueName->value.']]></name>'."\n";
								echo '<address><![CDATA['.$event->venueAddress->value.']]></address>'."\n";
								echo '<city><![CDATA['.$event->venueCity->value.']]></city>'."\n";
								echo '<country><![CDATA['.$event->venueCountry->value.']]></country>'."\n";
							echo '</venue>'."\n";
						echo '</event>'."\n";
					}
				}
/*
				foreach($events as $event)
				{
					$venue = $event['VenueName'];
					$town = $event['Town'];
					$address = $event['fs_address'];

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
*/
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
				$returnRoutes = array();
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

					$returnRoutes[] = array(
												'type' => 'public_transport',
												'departure' => $departure,
												'arrival' => $arrival,
												'transfers' => $route['numberOfChanges'],
												'price' => $price,
												);
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

					$returnRoutes[] = array(
												'type' => 'car',
												'price' => $price,
												'distance' => $distance
												);
				}

				$returnRoutesSorted = array();
				foreach($returnRoutes as $route)
				{
					$inserted = false;
					if(sizeof($returnRoutesSorted))
					{
						$newPrice = intval(floatval($route['price']) * 100);
						$i = 0;
						while(($i < sizeof($returnRoutesSorted)) && (!$inserted))
						{
							$price = intval(floatval($returnRoutesSorted[$i]['price']) * 100);
							if($price > $newPrice)
							{
								array_splice($returnRoutesSorted, $i, 0, array($route));
								$inserted = true;
							}
							$i++;
						}
					}
					if(!$inserted)
					{
						$returnRoutesSorted[] = $route;
					}
				}

				foreach($returnRoutesSorted as $route)
				{
					echo '<route>'."\n";
						echo '<type>'.$route['type'].'</type>'."\n";
						if($route['type'] == 'public_transport')
						{
							echo '<departure>'.$route['departure'].'</departure>'."\n";
							echo '<arrival>'.$route['arrival'].'</arrival>'."\n";
							echo '<transfers>'.$route['transfers'].'</transfers>'."\n";
						}
						else
						{
							echo '<distance>'.$route['distance'].'</distance>'."\n";
						}
						echo '<price>'.$route['price'].'</price>'."\n";							
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
