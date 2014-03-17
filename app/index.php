<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Be &#10148; There!</title>
	<style type="text/css">
		* {
			padding:0px;
			margin:0px;
			font-family:Arial, Helvetica, sans-serif;
		}
	
		h1 {
			color:#646464;
		}
		h2 {
			color:#36A4B8;
			margin-top:25px;
		}

		#container {
			position:absolute;
			width:100%;
			height:100%;
			background-image:url('images/pukkelpop.jpg');
			background-size:cover;
			background-repeat:no-repeat;
			overflow-y:scroll;
			overflow-x:hidden;
		}
			#header {
				width:920px;
				height:102px;
				border:1px solid #646464;
				border-bottom:none;
				background-color:#FFFFFF;
				margin:45px auto 0px auto;
			}
				#logo {
					width:300px;
					height:72px;
					background-image:url('images/logo.png');
				}
				#menu {
					width:920px;
					height:30px;
					background-color:#36a4b8;
				}
					#menu span, #menu a {
						display:block;
						float:left;
						height:20px;
						padding:5px 20px;
						text-decoration:none;
						text-transform:uppercase;
						color:#FFFFFF;
					}
					#menu a:hover {
						background-color:#FFFFFF;
						color:#36a4b8;
					}
		#content {
			width:870px;
			margin:0 auto;
			padding:25px;
			background-color:#FFFFFF;
			border:1px solid #646464;
			border-top:none;
			border-bottom:none;
		}
			#content #crumblebar {
				font-size:12px;
				text-transform:uppercase;
				border-bottom:1px solid #CCCCCC;
			}
				#content #crumblebar span.divider {			
					color:#666666;
				}
				#content #crumblebar span {
					color:#333333;
					padding:2px 5px 2px 5px;
				}
			#content #searchForm {
				margin:10px 10px 10px 0px;
			}
			#content #searchForm input[type=text] {
				border:1px solid #CCCCCC;
				padding:3px;
				height:20px;
				width:200px;
				margin-right:10px;
			}
			#content #searchForm input[type=button] {
				border:1px solid #CCCCCC;
				height:28px;
				width:75px;
				cursor:pointer;
			}

		#footer {
			width:890px;
			height:70px;
			margin:0 auto 25px auto;
			padding:15px;
			background-color:#FFFFFF;
			border:1px solid #646464;
			border-top:1px solid #CCCCCC;
			font-size:12px;
		}
			#footer div {
				float:left;
				width:150px;
			}
				#footer div span, #footer div a {
					display:block;
				}
				#footer div span {
					font-weight:bold;
					color:#3F3F3F;
				}
				#footer div a {
					text-decoration:none;
					color:#6F6F6F;
				}
	
	
	
	
		.ticketOption div {
			display:block;
			border:1px solid #DDDDDD;
			padding:10px;
			margin:10px;
			cursor:pointer;
		}
			.ticketOption div span {
				font-weight:bold;
				display:block;
			}
		.ticketOption input[type=radio] {
			display:none;
		}
		.ticketOption :checked + div
		{
			border:2px solid #36A4B8;
			padding:9px;
		}
	
		.soldout {
			color:#FF0000;
			font-weight:bold;
			text-transform:uppercase;
			margin-left:10px;
		}
	
		.travelOption {
			display:block;
			float:left;
		}
		.travelOption div {
			border:1px solid #DDDDDD;
			padding:10px;
			margin:10px;
			cursor:pointer;
			width:160px;
			height:90px;
		}
			.travelOption div span {
				font-weight:bold;
				display:block;
			}
		.travelOption input[type=radio] {
			display:none;
		}
		.travelOption :checked + div
		{
			border:2px solid #36A4B8;
			padding:9px;
		}
	
		.clearer {
			width:100%;
			height:0px;
			clear:both;
		}
	</style>
	<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
    <script type="text/javascript">
		$(document).ready(	function()
							{
								$("#confirmSearch").click(	function()
															{
																var value = $("#search_value").val();
																if(value)
																{
																	searchEvents(value);
																}
															});
							});

		var loadedEvents = new Array();
		var eventRequest = null;
		var routeRequest = null;

		function searchEvents(value)
		{
			if(eventRequest)
			{
				eventRequest.abort();
				eventRequest = null;
			}

			$("#event_results").html('<img src="images/ajax-loader.gif" />');
			$("#route_results").html('');
			eventRequest = $.ajax({
									type: "POST",
									url: "asynch.php?command=getEvents",
									data: {keywords: value},
									dataType: "xml",
									timeout: 5000,
									success: function (responseText)
																		{
																			$("#event_results").html('');
																			if($(responseText).find("error").length > 0)
																			{
																				$(responseText).find("error").each(	function()
																													{
																														alert($(this).find("message").text());
																													});
																			}
																			else
																			{
																				$("#event_results").append('<h2>Events</h2>');
																				if($(responseText).children("results").children("event").length == 0)
																				{
																					$("#event_results").html('<h2>No events found</h2>');
																					$("#event_results").append('Unfortunately, no events were found.');
																				}
																				else
																				{
																					$(responseText).children("results").children("event").each(	function()
																																{
																																	var id = $(this).find("id").text();
																																	var eventGroupName = $(this).find("eventGroupName").text();
																																	var venueName = $(this).find("venue").find("name").text();
																																	var price = parseInt($(this).find("tickets").find("minPrice").text());
																																	var address = $(this).find("location").find("address").text();
																																	var city = $(this).find("location").find("city").text();
																																	var country = $(this).find("location").find("country").text();
																																	loadedEvents[id] = {
																																							price: price,
																																							location: {
																																										address: address,
																																										city: city,
																																										country: country
																																										}
																																						};
	
																																	var ticketOption = $('<label/>').addClass("ticketOption");
																																	var radio = $("<input/>").attr('type', 'radio').attr('name', 'ticket');
																																	var titleSpan = $("<span/>").html("&#10148; " + eventGroupName + " at " + venueName);																																
																																	var div = $("<div/>").append(titleSpan).append('Minimum price: &euro;' + (price / 100).toFixed(2));
																																	ticketOption.append(radio);
																																	ticketOption.append(div);
																																	$("#event_results").append(ticketOption);
																																	div.click(	function()
																																				{
																																					var location = $("#search_location").val();
																																					if(!location)
																																					{
																																						alert('Enter a location from which to travel');
																																						return false;
																																					}
																																					openEvent(id, location);
																																				});
																																});
																				}
																			}
																		},
									error:	function(request, status, err)
											{
												$("#event_results").html('');
												if(status != "abort")
												{
													if(status == "timeout")
													{
														alert('The server is not responding. Please try again.');
													}
													else
													{
														alert('An unknown error has occured. Please try again.');
													}
												}
											}
								});
		}

		function openEvent(id, location)
		{
			if(routeRequest)
			{
				routeRequest.abort();
				routeRequest = null;
			}

			var openEvent = loadedEvents[id];

			$("#route_results").html('<img src="images/ajax-loader.gif" />');
			routeRequest = $.ajax({
									type: "POST",
									url: "asynch.php?command=getRoutes",
									data: {location_start: location, address: openEvent.location.address, city: openEvent.location.city, country: openEvent.location.country},
									dataType: "xml",
									timeout: 5000,
									success: function (responseText)
																		{
																			$("#route_results").html('');
																			if($(responseText).find("error").length > 0)
																			{
																				$(responseText).find("error").each(	function()
																													{
																														alert($(this).find("message").text());
																													});
																			}
																			else
																			{
																				$("#route_results").append('<h2>Routes</h2>');
																				$(responseText).children("results").children("route").each(	function()
																															{
																																var titleHTML = '';
																																var bodyHTML = '';

																																var type = $(this).find("type").text();
																																var price = parseInt($(this).find("price").text());
																																if(type == "car")
																																{
																																	var distance = parseInt($(this).find("distance").text());

																																	titleHTML = 'By Car';
																																	bodyHTML += 'Distance: ' + distance + ' km<br />';
																																}
																																else
																																{
																																	titleHTML = 'Public Transport';
																																}
																																bodyHTML += 'Price: &euro;' + (price / 100).toFixed(2) + '<br />';
																																bodyHTML += 'Total price: &euro;' + ((openEvent.price + price) / 100).toFixed(2);

																																var travelOption = $('<label/>').addClass("travelOption");
																																var radio = $("<input/>").attr('type', 'radio').attr('name', 'route');
																																var titleSpan = $("<span/>").html(titleHTML);
																																var div = $("<div/>").append(titleSpan).append(bodyHTML);
																																travelOption.append(radio);
																																travelOption.append(div);
																																$("#route_results").append(travelOption);
																															});
																				$("#route_results").append('<div class="clearer"></div>');
																			}
																		},
									error:	function(request, status, err)
											{
												$("#route_results").html('');
												if(status != "abort")
												{
													if(status == "timeout")
													{
														alert('The server is not responding. Please try again.');
													}
													else
													{
														alert('An unknown error has occured. Please try again.');
													}
												}
											}
								});
		}
	</script>
</head>
<body>
<div id="container">
	<div id="header">
		<div id="logo"></div>
       	<div id="menu">
        	<span></span>
			<a href="#">Search events</a>
            <a href="#">My Events</a>
            <a href="#">My Profile</a>
   	    </div>
	</div>
    <div id="content">
    	<div id="crumblebar">
        	<span class="divider">&#10148;</span>        
        	<span>Search events</span>
        </div>
        <div id="body">
	       	<div id="searchForm">
    	    	<input type="text" id="search_location" value="" placeholder="Your location" />
    	    	<input type="text" id="search_value" value="" placeholder="Search for an artist" />
				<input id="confirmSearch" type="button" value="Search" />
            </div>
            <div id="event_results"></div>
            <div id="route_results"></div>
        </div>
    </div>
    <div id="footer">
    	<div>
	    	<span>Events</span>
			<a href="#">This week</a>
			<a href="#">This Month</a>
			<a href="#">This Year</a>
			<a href="#">More ...</a>
        </div>
    	<div>
	    	<span>Artists</span>
			<a href="#">This week</a>
			<a href="#">This Month</a>
			<a href="#">This Year</a>
			<a href="#">More ...</a>            
        </div>
    	<div>
	    	<span>Music</span>
			<a href="#">Rock</a>
			<a href="#">Metal</a>
			<a href="#">Dubstep</a>
			<a href="#">More ...</a>
        </div>
    	<div>
	    	<span>Art</span>
			<a href="#">This week</a>
			<a href="#">This Month</a>
			<a href="#">This Year</a>
			<a href="#">More ...</a>
        </div>
    </div>
</div>
</body>
</html>