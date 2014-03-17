<?php
define('GBPTOEUR', 1.20);

class service_sesame
{
	private static function addPrefix($object, $prefix)
	{
		$url = NULL;
		switch(strtolower($prefix))
		{
			case 'db':
			case 'dbpedia':
				$url = 'http://dbpedia.org/page/';
				break;
			case 'fs':
			case 'fsq':
			case 'foursquare':
				$url = 'http://foursquare.com/';
				break;
			case 'sw':
			case 'seatwave':
				$url = 'http://seatwave.com/';
				break;
		}
		if($url)
		{
			$object = '<'.$url.$object.'>';
		}
		return htmlspecialchars(' '.$object.' ');
	}

	public static function insertData($artistQuery, $events)
	{
		$triples = '';

		$artist = service_dbpedia::getArtistInfo($artistQuery);
		if(sizeof($artist))
		{
			$artist = $artist[0];
			$triples .= service_sesame::getArtistTriples($artist);
		}
		else
		{
			$artist = NULL;
		}

		foreach($events as $event)
		{
			$triples .= service_sesame::getEventTriples($artist, $event);
			$triples .= service_sesame::getAddressTriples($event['VenueName'], $event['fs_address']);
		}
//echo $triples;
		service_sesame::insertTriples($triples);
	}

	private static function getArtistTriples($artist)
	{
		$resp = '';
		$resp .= $artist['dblink'];
		$resp .= service_sesame::addPrefix("birthdate", 'db').'"'.$artist['birth'].'";'."\n";
		$resp .= service_sesame::addPrefix("shortdesc", 'db').'"'.$artist['shortdesc'].'";'."\n";
		$resp .= service_sesame::addPrefix("image", 'db').'"'.$artist['image'].'".'."\n";

		return $resp;
	}

	private static function getEventTriples($artist, $event)
	{
		$id = "e".$event["Id"];
		$raw_date = $event['Date'];
		preg_match('/[0-9]+/', $raw_date, $matches);
		$date=date('Y-m-d H:i:s', ($matches[0] / 1000));

		$venue = $event["VenueName"];
		$venueId = "v".$event["VenueId"];
		$town = $event["Town"];
		$tickets = $event["TicketCount"];
		$price = $event["MinPrice"];
		if($event["Currency"] == 'GBP')
		{
			$price *= GBPTOEUR;
		}

		$resp = '';
		$resp .= service_sesame::addPrefix($id, 'sw');
		$resp .= " a ".service_sesame::addPrefix("Event", 'sw').';'."\n";
		$resp .= service_sesame::addPrefix("artist", 'sw').$artist['dblink'].';'."\n";
		$resp .= service_sesame::addPrefix("date", 'sw').'"'.$date.'";'."\n";
		$resp .= service_sesame::addPrefix("venue", 'sw').service_sesame::addPrefix($venueId, 'sw').';'."\n";
		$resp .= service_sesame::addPrefix("town", 'sw').'"'.$town.'";'."\n";
		$resp .= service_sesame::addPrefix("tickets", 'sw').$tickets.";\n";
		$resp .= service_sesame::addPrefix("price", 'sw').$price.".";
		$resp .= service_sesame::addPrefix($venueId, 'sw').service_sesame::addPrefix("venueName", 'sw').'"'.$venue.'".';
		return $resp;
	}

	private static function getAddressTriples($venueName, $address)
	{
		$resp = '';
		$resp .= service_sesame::addPrefix(rand(1,1000000), 'fs').service_sesame::addPrefix("name", 'fs').'"'.$venueName.'";'."\n";
		$resp .= service_sesame::addPrefix("address", 'fs').'"'.$address['address'].'".'."\n";
		return $resp;
	}

	private static function insertTriples($triples)
	{
		$url = 'http://178.85.74.3:8080/openrdf-sesame/repositories/IWA_TEST/statements';

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

	public static function getData()
	{
		$sesame_url = 'http://178.85.74.3:8080/openrdf-sesame/repositories/IWA_TEST?query='.rawurlencode('select ?o where {?s <http://seatwave.com/price> ?o }');
		$options = array(
			'http' => array(
		        'header'  => "Content-Type: text/turtle","Accept:application/sparql-results+json",//, */*;q=0.5",
        		'method'  => 'GET',
			),
		);
		$context  = stream_context_create($options);
		$triples = file_get_contents($sesame_url, false, $context);

		echo $triples;
	}
}
?>