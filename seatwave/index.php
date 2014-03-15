<?php
	function getEvents($band)
	{
		$parameters = array();
		$parameters[] = 'apikey=af5b9092ea3846e0be3bdae3eb4bee88';
		$parameters[] = 'what='.$band;
		$parameters[] = 'where=netherlands';
	
		$url = 'http://api-sandbox.seatwave.com/v2/discovery/genre/1/events?'.implode('&', $parameters);
		$content = file_get_contents($url);
		$json = json_decode($content, true);
		return $json['Events'];
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
	$prefix = htmlspecialchars("<http://www.seatwave.com/>", ENT_QUOTES);

	$resp='@prefix sw: ' . $prefix . ' .<br/><br/>';
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
		$resp.="sw:" . $id . " sw:artist \"" . $raw_band . "\" ;<br/>sw:date \"" . $date . "\" ;<br/>sw:venue \"" . $venue . "\" ;<br/>sw:town \"" . $town . "\" ;<br/>sw:tickets " . $tickets . " ;<br/>sw:price " . $price . " .<br/><br/>";
	}
	echo $resp;
/*
for (var i=0; i<events.length; i++){
                                var ev=events[i];
                                var id=ev["Id"];
                                var date=parseJsonDate(ev["Date"]);
                                var venue=ev["VenueName"];
                                var town=ev["Town"];
                                var tickets=ev["TicketCount"];
                                var price=ev["MinPrice"];
                                if (ev["Currency"]=="GBP"){
                                        var price=price*GBPTOEUR;
                                }
                                resp+="sw:" + id + " sw:artist \"" + raw_band + "\" ;\nsw:date \"" + date + "\" ;\nsw:venue \"" + venue + "\" ;\nsw:town \"" + town + "\" ;\nsw:tickets " + tickets + " ;\nsw:price " + price + " .\n\n"
                        }


*/
//	echo sizeof($events).' events found';
}
?>
