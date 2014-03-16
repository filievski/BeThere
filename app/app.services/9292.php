<?php
class service_9292
{
	public static function getRoutes($from, $to)
	{
		$fromStr = '';
		$toStr = '';

		if(is_array($from) && isset($from['url']))
		{
			$fromStr = $from['url'];
		}
		else
		{
			$fromStr = (string)$from;
		}

		if(is_array($to) && isset($to['url']))
		{
			$toStr = $to['url'];
		}
		else
		{
			$toStr = (string)$to;
		}
		
		if(strlen($fromStr) && strlen($toStr))
		{
			$parameters = array();
			$parameters[] = 'before=1';
			$parameters[] = 'sequence=1';
			$parameters[] = 'byBus=true';
			$parameters[] = 'byFerry=true';
			$parameters[] = 'bySubway=true';
			$parameters[] = 'byTram=true';
			$parameters[] = 'byTrain=true';
			$parameters[] = 'from='.$fromStr;
			$parameters[] = 'dateTime=2014-03-17T1430';
			$parameters[] = 'searchType=departure';
			$parameters[] = 'interchangeTime=standard';
			$parameters[] = 'after=5';
			$parameters[] = 'to='.$toStr;
			$parameters[] = 'lang=nl-NL';

			$url = 'http://api.9292.nl/0.1/journeys?'.implode('&', $parameters);
			try
			{
				$content = file_get_contents($url);
				if($content !== false)
				{
					$json = json_decode($content, true);
					return $json['journeys'];
				}
			}
			catch(Exception $e)
			{
			}
		}
		return array();
	}

	public static function getSuggestions($query)
	{
		$url = 'http://9292.nl/suggest?q='.urlencode($query);
		try
		{
			$content = file_get_contents($url);
			if($content !== false)
			{
				$json = json_decode($content, true);
				return $json['locations'];
			}
		}
		catch(Exception $e)
		{
		}
		return array();
	}
}
?>