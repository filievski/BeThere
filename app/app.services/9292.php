<?php
class service_9292
{
	public static function getRoutes($from, $to, $arrival, $minutes_early)
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

		$substractDays = 0;
		$arrival = DateTime::createFromFormat('d-m-Y H-i', $arrival);
		$interval = $arrival->diff(new DateTime());
		if($interval->days > 30)
		{
			$substractDays = ($interval->days - 30);
			while(($substractDays % 7) != 0)
			{
				$substractDays++;
			}
		}
		$arrival->sub(new DateInterval('P0Y0M'.$substractDays.'DT0H'.$minutes_early.'M0S'));

		if(strlen($fromStr) && strlen($toStr))
		{
			$parameters = array();
			$parameters[] = 'before=2';
			$parameters[] = 'sequence=1';
			$parameters[] = 'byBus=true';
			$parameters[] = 'byFerry=true';
			$parameters[] = 'bySubway=true';
			$parameters[] = 'byTram=true';
			$parameters[] = 'byTrain=true';
			$parameters[] = 'from='.$fromStr;
			$parameters[] = 'dateTime='.$arrival->format('Y-m-d').'T'.$arrival->format('Hi');//date(2014-03-17T1430';
			$parameters[] = 'searchType=arrival';
			$parameters[] = 'interchangeTime=standard';
			$parameters[] = 'after=1';
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