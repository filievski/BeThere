<?php
	set_time_limit(120);

	//Include libraries
	require_once('./app.includes/sparqllib.php');

	//Include connectors for external data sources
	require_once('./app.services/9292.php');
	require_once('./app.services/seatwave.php');
	require_once('./app.services/foursquare.php');
	require_once('./app.services/google_maps.php');
	require_once('./app.services/dbpedia.php');
	require_once('./app.services/sesame.php');

	//Output XML
	header("Content-type: text/xml; charset=utf-8");
	echo '<?xml version="1.0"?>'."\n";
	echo '<results>'."\n";

	//Respond to requests comming in
	$command = (isset($_GET['command']) ? $_GET['command'] : '');
	switch($command)
	{
		case 'getData':
			//Generic triple store querying
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
			//Get events from keywords
			$artist = (isset($_POST['keywords']) ? $_POST['keywords'] : (isset($_GET['keywords']) ? $_GET['keywords'] : ''));

			if(strlen($artist))
			{
				//Get seatwave events
				$events = service_seatwave::getEvents($artist);

				for($i = 0; $i < sizeof($events); $i++)
				{
					$venue = $events[$i]['VenueName'];
					$town = $events[$i]['Town'];
					//Get Foursquare address for venue of each event
					$events[$i]['fs_address'] = service_foursquare::getAddress($venue, $town);
				}

				if(sizeof($events))
				{
					//Insert events into triple store and get artists name
					$artists = service_sesame::insertData($events);

					if(sizeof($artists))
					{
						//Build query to retrieve events from triple store
						$unions = array();
						foreach($artists as $artist)
						{
							$unions[] = '
											{
											?resource	rdf:type									<http://seatwave.com/resource/Event>;
														<http://seatwave.com/resource/hasArtist>	?artist.
											?artist		<http://xmlns.com/foaf/0.1/name>			?artistName;
											FILTER regex(?artistName, "'.$artist.'", "i")
											}
											';
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
										'.implode('UNION', $unions).'
									}
									';

						$eventResources = service_sesame::getData($query);

						foreach($eventResources->results->bindings as $eventResource)
						{
							//For each event, retrieve information about artist and venue
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
												(SAMPLE(?url) as ?url)
										WHERE
										{
											<'.$eventRes.'>		<http://seatwave.com/resource/hasArtist>	?artist;
																<http://seatwave.com/resource/date>			?date;
																<http://seatwave.com/resource/venue>		?venue;
																<http://seatwave.com/resource/town>			?town;
																<http://seatwave.com/resource/tickets> 		?tickets;
																<http://seatwave.com/resource/price>		?price;
																<http://seatwave.com/resource/ticketurl>	?url.
											?artist				<http://xmlns.com/foaf/0.1/name>			?artistName.
											OPTIONAL
											{
												?artist			<http://dbpedia.org/page/birthdate>			?artistBirthDate.
											}
											OPTIONAL
											{
												?artist			<http://dbpedia.org/page/shortdesc>			?artistDesc.
											}
											OPTIONAL
											{
												?artist			<http://dbpedia.org/page/image>				?artistImage.
											}
											?venue				<http://foursquare.com/resource/name>		?venueName;
																<http://foursquare.com/resource/address>	?venueAddress;
																<http://foursquare.com/resource/city>		?venueCity;
																<http://foursquare.com/resource/country>	?venueCountry.
										}
										GROUP BY ?resource
										';
	
							$fields = array('date', 'town', 'tickets', 'url');
	
							$events = service_sesame::getData($query);
							foreach($events->results->bindings as $event)
							{
								if($event)
								{
									//Print events to XML
									$date = DateTime::createFromFormat('d-m-Y H:i', $event->date->value);
			
									echo '<event>'."\n";
										foreach($fields as $field)
										{
											echo '<'.$field.'><![CDATA['.$event->$field->value.']]></'.$field.'>'."\n";
										}
										if($date)
										{
											echo '<time>'.$date->format('H:i').'</time>'."\n";
										}
										echo '<price>'.intval(floatval($event->price->value) * 100).'</price>'."\n";
										echo '<artist>'."\n";
//											echo '<resource><![CDATA['.$event->artist->value.']]></resource>'."\n";
											echo '<name><![CDATA['.$event->artistName->value.']]></name>'."\n";
											if(isset($event->artistBirthDate->value))
											{
												echo '<birthDate><![CDATA['.$event->artistBirthDate->value.']]></birthDate>'."\n";
											}
											if(isset($event->artistDesc->value))
											{
												echo '<desc><![CDATA['.$event->artistDesc->value.']]></desc>'."\n";
											}
											if(isset($event->artistImage->value))
											{
												echo '<image><![CDATA['.$event->artistImage->value.']]></image>'."\n";
											}
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
						}
					}
				}
			}
			break;
		case 'getRoutes':
			//Get routes from location_start to address, city, country. Minutes indicates the number of minutes to be early of arrival
			$location_start = (isset($_POST['location_start']) ? $_POST['location_start'] : '');
			$address = (isset($_POST['address']) ? $_POST['address'] : '');
			$city = (isset($_POST['city']) ? $_POST['city'] : '');
			$country = (isset($_POST['country']) ? $_POST['country'] : '');
			$arrival = (isset($_POST['dateTime']) ? $_POST['dateTime'] : '');
			$minutes = (isset($_POST['minutes']) ? intval($_POST['minutes']) : 30);

			//If location and at least address of destination of set
			if(strlen($location_start) && strlen($address))
			{
				//Calculate minimum and maximum of travel
				$minTravelPrice = NULL;
				$maxTravelPrice = NULL;

				$locationFromQuery = $location_start;
				$locationToQuery = $address.(strlen($city) ? ', ' : '').$city.(strlen($country) ? ', ' : '').$country;

				//Retrieve and use 9292's best guess for departure and arrival location
				$locations_from = service_9292::getSuggestions($locationFromQuery);
				$locations_to = service_9292::getSuggestions($locationToQuery);
				$routes = service_9292::getRoutes($locations_from[0], $locations_to[0], $arrival, $minutes);

				$returnRoutes = array();
				foreach($routes as $route)
				{
					$departure = $route['departure'];
					$arrival = $route['arrival'];
					$price = intval($route['fareInfo']['fullPriceCents']);
					$duration = intval((strtotime($arrival) - strtotime($departure)) / 60);

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

					//Put in array for easy sorting
					$returnRoutes[] = array(
												'type' => 'public_transport',
												'departure' => $departure,
												'arrival' => $arrival,
												'transfers' => $route['numberOfChanges'],
												'price' => $price,
												'duration' => $duration
												);
				}

				//Get car routes from Google maps
				$routes = service_google_maps::getRoutes($locationFromQuery, $locationToQuery);
				foreach($routes as $route)
				{
					$distance = $route['distance'];
					$duration = $route['duration'];
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

					//Put in array for easy sorting
					$returnRoutes[] = array(
												'type' => 'car',
												'price' => $price,
												'distance' => $distance,
												'duration' => $duration
												);
				}

				//Sort routes from lowest price to highest
				$returnRoutesSorted = array();
				//For each unsorted item
				foreach($returnRoutes as $route)
				{
					$inserted = false;
					//If items are present in the sorted array
					if(sizeof($returnRoutesSorted))
					{
						//Get the price of the unsorted item
						$newPrice = intval(floatval($route['price']) * 100);
						$i = 0;
						//Itterate over sorted items
						while(($i < sizeof($returnRoutesSorted)) && (!$inserted))
						{
							//Get sorted item proce
							$price = intval(floatval($returnRoutesSorted[$i]['price']) * 100);
							//If the sorted price is higher then the unsorted price
							if($price > $newPrice)
							{
								//Insert unsorted item before current sorted item
								array_splice($returnRoutesSorted, $i, 0, array($route));

								//End loop over sorted items
								$inserted = true;
							}
							$i++;
						}
					}
					//If no unsorted item was inserted, add to end of sorted array
					if(!$inserted)
					{
						$returnRoutesSorted[] = $route;
					}
				}

				//Output the sorted items to XML
				foreach($returnRoutesSorted as $route)
				{
					//Convert duration to human readble format
					$duration = intval($route['duration']);
					$hours = 0;
					if($duration > 61)
					{
						$hours = floor($duration / 60);
					}
					$minutes = ($duration % 60);

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
						echo '<duration>'.($hours ? $hours.' hours and ' : '').$minutes.' minutes</duration>'."\n";
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
