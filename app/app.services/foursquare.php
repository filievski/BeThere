<?php
class service_foursquare
{
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
				return $json["response"]["groups"][0]["items"][0]["venue"]["location"];
			}
		}
		catch(Exception $e)
		{
		}
		return array();
	}
}
?>