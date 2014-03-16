<?php
	function make_sw($stringy)
	{
	//	return '<http://seatwave.com/' . $stringy . '> ';
		return htmlspecialchars('<http://seatwave.com/' . $stringy . '> ');
	}

	function make_fs($stringy)
	{
	//	return '<http://seatwave.com/' . $stringy . '> ';
		return htmlspecialchars('<http://foursquare.com/' . $stringy . '> ');
	}

	function getEvents($band)
	{
		$parameters = array();
		$parameters[] = 'apikey=af5b9092ea3846e0be3bdae3eb4bee88';
		$parameters[] = 'what='.$band;
		$parameters[] = 'where=netherlands';
	
		$url = 'http://api-sandbox.seatwave.com/v2/discovery/genre/1/events?'.implode('&', $parameters);
		try
		{
			$content = file_get_contents($url);
			if($content !== false)
			{
				$json = json_decode($content, true);
				return $json['Events'];
			}
		}
		catch(Exception $e)
		{
		}
		return array();
	}

	function getVenueAddress($venue, $town)
	{
		$params=array();
		$params[]='v=2014021';
		$params[]='oauth_token=1L3VQUZJIZ3R1XA5OMOUHIM2PYIFIMLYBYEB0I1MQKKVYAUX';
		$params[]='near='.$town.',NL';
		$params[]='query='.$venue;

		$fs_url = 'https://api.foursquare.com/v2/venues/explore?'.implode('&', $params);
		try
		{
			$fs_resp = file_get_contents($fs_url);
			if($fs_resp !== false)
			{
				$json = json_decode($fs_resp, true);
				$fs_venue = $json["response"]["groups"][0]["items"][0]["venue"]["location"]["address"];
				return $fs_venue;
			}
		}
		catch(Exception $e)
		{
		}
		return array();
	}

	$GBPTOEUR=1.20;
	$raw_band = '';
	if(isset($_POST['raw_band']))
	{
		$raw_band = $_POST['raw_band'];
	}
?>
<form method="POST">
	<input type="text" name="raw_band" placeholder="Enter artist/category name" value="<?=$raw_band;?>" /><br />
	<input type="submit" value="Search events" />
</form>
<?php
if(strlen($raw_band))
{
	$band=str_replace(' ', '-', strtolower(trim($raw_band)));
	$events = getEvents($band);
//	$prefix = htmlspecialchars("<http://www.seatwave.com/>", ENT_QUOTES);

//	$resp='@prefix sw: ' . $prefix . ' .<br/><br/>';
	$resp_sw='';
	$resp_fs='';
	foreach ($events as $ev)
	{
		$id=$ev["Id"];
		$raw_date=$ev['Date'];
		preg_match('/[0-9]+/', $raw_date, $matches);
		$date=date('Y-m-d H:i:s', $matches[0]/1000);

		$venue=$ev["VenueName"];
		$town=$ev["Town"];
		$tickets=$ev["TicketCount"];
		$price=$ev["MinPrice"];
		if ($ev["Currency"]=='GBP'){
			$price*=$GBPTOEUR;
		}


		$resp_sw.=make_sw($id) . make_sw("artist") . "\"" . $raw_band . "\" ; " . make_sw("date") . "\"" . $date . "\" ; " . make_sw("venue") . "\"" . $venue . "\" ; " . make_sw("town") . "\"" . $town . "\" ; " . make_sw("tickets") . $tickets . " ; " . make_sw("price") . $price . " . ";
		$fs_address=getVenueAddress($venue,$town);

		$resp_fs.=make_fs(rand(1,1000000)) . make_fs("name") . "\"" . $venue . "\" ; " . make_fs("address") . "\"" . $fs_address . "\" . ";
	}
	echo $resp_fs;
//////////////////////////////////////////////////////////////////////////////////////////
	// Read stuff from sesame
/*	
	$sesame_url = 'http://178.85.74.3:8080/openrdf-sesame/repositories/IWA_TEST?query=' . rawurlencode('select ?o where {?s <http://seatwave.com/price> ?o } limit 1');
	$triples = file_get_contents($sesame_url);
	echo $triples;
*/
/////////////////////////////////////////////////////////////////////////////////////////

	// Insert stuff into sesame
	$url = 'http://178.85.74.3:8080/openrdf-sesame/repositories/IWA_TEST/statements';

	// use key 'http' even if you send the request to https://...
	$options = array(
		'http' => array(
	        'header'  => "Content-Type: text/turtle",
        	'method'  => 'POST',
	        'content' => htmlspecialchars_decode($resp_fs)
		),
	);
	$context  = stream_context_create($options);
	$result = file_get_contents($url, false, $context);

	var_dump($result);

//////////////////////////////////////////////////////////////////////////////////////////

}
?>
