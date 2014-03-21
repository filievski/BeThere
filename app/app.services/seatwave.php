<?php
class service_seatwave
{
	public static function getEvents($band)
	{
		$band = str_replace(' ', '-', strtolower(trim($band)));

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

	public static function getAddress($venue, $town)
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
				$fs_address = $json["response"]["groups"][0]["items"][0]["venue"]["location"]["address"];
				return $fs_address;
			}
		}
		catch(Exception $e)
		{
		}
		return array();
	}
}
?>