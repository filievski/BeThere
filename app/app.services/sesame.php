<?php
define('GBPTOEUR', 1.20);
define('STATIC', false);

class service_sesame
{
	private static function getRepos()
	{
		if(false)
		{
			return 'http://178.85.74.3:8080/openrdf-sesame/repositories/IWA_FINAL_STATIC';
		}
		else
		{
			return 'http://178.85.74.3:8080/openrdf-sesame/repositories/IWA_FINAL';
		}
	}

	private static function addPrefix($object, $prefix = NULL)
	{
		$url = '';
		switch(strtolower($prefix))
		{
			case 'db':
			case 'dbpedia':
				$url = 'http://dbpedia.org/page/';
				break;
			case 'fs':
			case 'fsq':
			case 'foursquare':
				$url = 'http://foursquare.com/resource/';
				break;
			case 'sw':
			case 'seatwave':
				$url = 'http://seatwave.com/resource/';
				break;
			case 'foaf':
				$url = 'http://xmlns.com/foaf/0.1/';
				break;
		}
		$object = '<'.$url.$object.'>';
		return htmlspecialchars(' '.str_replace(' ', '_', $object).' ');
	}

	public static function insertData($events)
	{
		$triples = '';
		$artists = array();
		$return = array();
		foreach($events as $event)
		{
			$artistQuery = $event['EventGroupName'];
			$artist = NULL;
			if(isset($artists[$artistQuery]))
			{
				$artist = $artists[$artistQuery];
			}
			else
			{
				$artist = service_dbpedia::getArtistInfo($artistQuery);
				if(sizeof($artist))
				{
					$artist = $artist[0];
					$artists[$artistQuery] = $artist;
					$return[] = $artist['name'];
					$triples .= service_sesame::getArtistTriples($artist);
				}
			}
			$triples .= service_sesame::getEventTriples($artist, $event);
			$triples .= service_sesame::getAddressTriples($event['VenueName'], $event['fs_address']);
		}

		service_sesame::insertTriples($triples);
		return $return;
	}

	private static function getArtistTriples($artist)
	{
		$resp = '';
		$resp .= htmlspecialchars($artist['dblink']);
		$resp .= service_sesame::addPrefix('name', 'foaf').'"'.$artist['name'].'"'."\n";
		if(isset($artist['birthdate']))
		{
			$resp .= ";\n".service_sesame::addPrefix("birthdate", 'db').'"'.$artist['birthdate'].'"';
		}
		if(isset($artist['shortdesc']))
		{
			$resp .= ";\n".service_sesame::addPrefix("shortdesc", 'db').'"'.$artist['shortdesc'].'"';
		}
		if(isset($artist['image']))
		{
			$resp .= ";\n".service_sesame::addPrefix("image", 'db').'"'.$artist['image'].'"';
		}
		$resp .= '.'."\n";

		return $resp;
	}

	private static function getEventTriples($artist, $event)
	{
		$id = "e".$event["Id"];
		$raw_date = $event['Date'];
		preg_match('/[0-9]+/', $raw_date, $matches);
		$date = date('d-m-Y H:i', ($matches[0] / 1000));
		$venue = $event["VenueName"];
		$venueId = "v".$event["VenueId"];
		$town = $event["Town"];
		$tickets = $event["TicketCount"];
		$price = $event["MinPrice"];
		$ticketURL = $event["SwURL"];
		if($event["Currency"] == 'GBP')
		{
			$price *= GBPTOEUR;
		}
 
		$resp = '';
		$resp .= service_sesame::addPrefix($id, 'sw');
		$resp .= " a ".service_sesame::addPrefix("Event", 'sw').';'."\n";
		$resp .= service_sesame::addPrefix("hasArtist", 'sw').service_sesame::addPrefix($artist['name'], 'sw').';'."\n";
		$resp .= service_sesame::addPrefix("date", 'sw').'"'.$date.'";'."\n";
		$resp .= service_sesame::addPrefix("venue", 'sw').service_sesame::addPrefix($venueId, 'sw').';'."\n";
		$resp .= service_sesame::addPrefix("town", 'sw').'"'.$town.'";'."\n";
		$resp .= service_sesame::addPrefix("tickets", 'sw').$tickets.";\n";
		$resp .= service_sesame::addPrefix("price", 'sw').$price.";"."\n";
		$resp .= service_sesame::addPrefix("ticketurl", 'sw').service_sesame::addPrefix($ticketURL)."."."\n";
		$resp .= service_sesame::addPrefix($venueId, 'sw').service_sesame::addPrefix("venueName", 'sw').'"'.$venue.'".'."\n";
		$resp .= service_sesame::addPrefix($artist['name'], 'sw').' a '.service_sesame::addPrefix("artist", 'sw').';'."\n";
		$resp .= service_sesame::addPrefix("artistName", 'sw').'"'.$artist['name'].'".'."\n";
		return $resp;
	}

	private static function getAddressTriples($venueName, $address)
	{
		$resp = '';
		$resp .= service_sesame::addPrefix(str_replace(' ', '_', trim(trim($address['address'].' '.$address['city']).' '.$address['cc'])), 'fs').service_sesame::addPrefix("name", 'fs').'"'.$venueName.'";'."\n";
		$resp .= service_sesame::addPrefix("address", 'fs').'"'.$address['address'].'";'."\n";
		$resp .= service_sesame::addPrefix("city", 'fs').'"'.$address['city'].'";'."\n";
		$resp .= service_sesame::addPrefix("country", 'fs').'"'.$address['country'].'".'."\n";
		return $resp;
	}

	private static function insertTriples($triples)
	{
		$url = service_sesame::getRepos().'/statements';

		//Use key 'http' even if you send the request to https://...
		$options = array(
						'http' => array(
										'header'  => "Content-Type: text/turtle",
										'method'  => 'POST',
										'content' => htmlspecialchars_decode($triples)
										)
						);
		$context  = stream_context_create($options);
		return file_get_contents($url, false, $context);
	}

	public static function getData($query)
	{
		$sesame_url = service_sesame::getRepos().'?query='.urlencode($query).'&Accept=application/sparql-results%2Bjson';
		return json_decode(file_get_contents($sesame_url));
	}
}
?>