<?php
	function getDistanceMatrix($from, $to)
	{
		$parameters = array();
		$parameters[] = 'travelMode=driving';
		$parameters[] = 'unitSystem=metric';
		$parameters[] = 'avoidHighways=false';
		$parameters[] = 'avoidTolls=false';
		$parameters[] = 'sensor=false';
		$parameters[] = 'origins='.urlencode ($from);
		$parameters[] = 'destinations='.urlencode ($to);

		//   http://maps.googleapis.com/maps/api/distancematrix/json?travelMode=driving&unitSystem=metric&avoidHighways=false&avoidTolls=false&origins=amsterdam&destinations=eindhoven
		$url = 'http://maps.googleapis.com/maps/api/distancematrix/json?'.implode('&', $parameters);

		$content = file_get_contents($url);
		$json = json_decode($content, true);
		
		return $json;
	}

	$locationFrom = '';	
	if(isset($_POST['location_from']))
	{
		$locationFrom = $_POST['location_from'];
	}
	
	$locationTo = '';
	if(isset($_POST['location_to']))
	{
		$locationTo = $_POST['location_to'];
	}

	$fuelPrice = 1.5;
	//Typical Miles per gallon found at: http://www.umich.edu/~umtriswt/EDI_sales-weighted-mpg.html
	$typicalMpg = 25.2;
	$mpgToKmlConversionRate = 0.425143707;
	$typicalKml = $typicalMpg*$mpgToKmlConversionRate;
?>
<form method="POST">
	<input type="text" name="location_from" placeholder="Get route from" value="<?=$locationFrom;?>" /><br />
	<input type="text" name="location_to" placeholder="Get route to" value="<?=$locationTo;?>" /><br />
	<input type="submit" value="Get distance and price" />
</form>
<?php
if(strlen($locationFrom) && strlen($locationTo))
{
	$distanceM = getDistanceMatrix($locationFrom, $locationTo);
	$distance = $distanceM['rows'][0]['elements'][0]['distance']['text'];
	echo $distance.' away'.'<br/>';
	$price = $fuelPrice*($distanceM['rows'][0]['elements'][0]['distance']['value']/$typicalKml)/1000;
	echo "Costs ".$price ." euro".'<br/>';
}

?>
