<?php
class service_google_maps
{
	public static function getRoutes($locationFrom, $locationTo)
	{
		$routes = array();

		$fuelPrice = 1.5;
		//Typical Miles per gallon found at: http://www.umich.edu/~umtriswt/EDI_sales-weighted-mpg.html
		$typicalMpg = 25.2;
		$mpgToKmlConversionRate = 0.425143707;
		$typicalKml = $typicalMpg * $mpgToKmlConversionRate;

		$distanceM = service_google_maps::getDistanceMatrix($locationFrom, $locationTo);
		$distance = $distanceM['rows'][0]['elements'][0]['distance']['text'];
		$duration = (intval($distanceM['rows'][0]['elements'][0]['duration']['value']) / 60);
		$price = (($fuelPrice * ($distanceM['rows'][0]['elements'][0]['distance']['value'] / $typicalKml)) / 10);

		$routes[] = array(
							'distance' => $distance,
							'duration' => $duration,
							'price' => $price
							);

		return $routes;
	}

	private static function getDistanceMatrix($from, $to)
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
}
?>