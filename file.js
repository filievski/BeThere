$(document).ready(function(){

	function parseJsonDate (date, shortFormat) {
	    if (date != null) {
		var d = new Date(parseInt(date.substr(6)));
		    if (shortFormat) {
			return (d.getMonth() + 1) + '/' + d.getDate() + '/' +
				  d.getFullYear().toString().substr(2);
		    }
		    return d;
	    } else {
		    return null;
	    }
	}

	var GBPTOEUR=1.20;
        $(":button").click(function(){
		var raw_band=document.getElementById("txtBand").value;
		band=raw_band.trim().toLowerCase().replace(" ", "-");
		alert(band);
		$.getJSON("http://api-sandbox.seatwave.com/v2/discovery/genre/1/events?callback=?", {'apikey':'4739E4694D0E482A92C9D0B478D83934','what':band,'where':'netherlands'}, function(j){
//			var vjson=JSON.parse(j);
			var events=j["Events"];
			alert(JSON.stringify(events));
			var prefix="http://www.seatwave.com/";
			var resp="@prefix sw: <" + prefix + "> .\n\n";
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
//				resp+="Event " + (i+1) + " is on date " + date + " at the venue " + venue + " in " + town + ". Cheapest ticket is " + price + " euros. There are " + tickets + " tickets left!\n";
				resp+="sw:" + id + " sw:artist \"" + raw_band + "\" ;\nsw:date \"" + date + "\" ;\nsw:venue \"" + venue + "\" ;\nsw:town \"" + town + "\" ;\nsw:tickets " + tickets + " ;\nsw:price " + price + " .\n\n"
			}
			alert(resp);
		});
        });
});

