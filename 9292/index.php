<?php
	function getRoutes($from, $to)
	{
		$parameters = array();
		$parameters[] = 'before=1';
		$parameters[] = 'sequence=1';
		$parameters[] = 'byBus=true';
		$parameters[] = 'byFerry=true';
		$parameters[] = 'bySubway=true';
		$parameters[] = 'byTram=true';
		$parameters[] = 'byTrain=true';
		$parameters[] = 'from='.$from;
		$parameters[] = 'dateTime=2014-03-15T1430';
		$parameters[] = 'searchType=departure';
		$parameters[] = 'interchangeTime=standard';
		$parameters[] = 'after=5';
		$parameters[] = 'to='.$to;
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
		return array();
	}

	function getSuggestions($query)
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

	$locationFromSuggestion = '';
	$locationFrom = '';
	if(isset($_POST['location_from']))
	{
		$locationFromSuggestion = $_POST['location_from'];
	}
	else if(isset($_GET['location_from_suggestion']))
	{
		$locationFromSuggestion = $_GET['location_from_suggestion'];
	}
	if(isset($_GET['location_from']))
	{
		$locationFrom = $_GET['location_from'];
	}

	$locationToSuggestion = '';
	$locationTo = '';
	if(isset($_POST['location_to']))
	{
		$locationToSuggestion = $_POST['location_to'];
	}
	else if(isset($_GET['location_to_suggestion']))
	{
		$locationToSuggestion = $_GET['location_to_suggestion'];
	}
	if(isset($_GET['location_to']))
	{
		$locationTo = $_GET['location_to'];
	}
?>
<form method="POST">
	<input type="text" name="location_from" placeholder="Get route from" value="<?=$locationFromSuggestion;?>" /><br />
	<input type="text" name="location_to" placeholder="Get route to" value="<?=$locationToSuggestion;?>" /><br />
	<input type="submit" value="Search routes" />
</form>
<?php
if(strlen($locationFrom) && strlen($locationTo))
{
	$routes = getRoutes($locationFrom, $locationTo);
	echo sizeof($routes).' routes found';
}
else if(strlen($locationFromSuggestion) && strlen($locationToSuggestion))
{
	$locationsFrom = getSuggestions($locationFromSuggestion);
	$locationsTo = getSuggestions($locationToSuggestion);

	$baseUrl = '?location_from_suggestion='.$locationFromSuggestion.'&location_to_suggestion='.$locationToSuggestion;

	if(!strlen($locationFrom))
	{
		if(sizeof($locationsFrom) == 0)
		{
			echo 'No start locations found';
		}
		else if(sizeof($locationsFrom) > 1)
		{
			echo 'Following starting locations found:<br />';
			foreach($locationsFrom as $locationFromObj)
			{
				echo '- <a href="'.$baseUrl.'&location_from='.$locationFromObj['url'].'&location_to='.$locationTo.'">'.$locationFromObj['displayname'].' ('.$locationFromObj['subType'].')</a><br />';
			}
		}
		echo '<br /><br /><br />';
	}

	if(!strlen($locationTo))
	{
		if(sizeof($locationsTo) == 0)
		{
			echo 'No end locations found';
		}
		else if(sizeof($locationsTo) > 1)
		{
			echo 'Following end locations found:<br />';
			foreach($locationsTo as $locationToObj)
			{
				echo '- <a href="'.$baseUrl.'&location_from='.$locationFrom.'&location_to='.$locationToObj['url'].'">'.$locationToObj['displayname'].' ('.$locationToObj['subType'].')</a><br />';
			}
		}
	}
}
?>