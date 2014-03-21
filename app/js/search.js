$(document).ready(	function()
					{
						$("#search_value").keypress(	function(e)
														{
															//If enter is pressed while search_value is in focus, submit
															if(e.which == 13)
															{
																searchEvents();
															}
														});
						$("#confirmSearch").click(	function()
													{
														searchEvents();
													});
					});

//Timeout of 2 seconds
var global_timeout = 2 * 60 * 1000;

//Keep track of loaded events and AJAX requests to be able to abort when needed
var loadedEvents = new Array();
var eventRequest = null;
var routeRequest = null;

//Cache request parameters to prevent duplicate subsequent requests
var lastQuery = null;

function searchEvents(value)
{
	var value = $("#search_value").val().trim();

	if(value)
	{
		//If a request is running
		if(eventRequest)
		{
			//Abort it and start a new one
			eventRequest.abort();
			eventRequest = null;
		}
		//If the last requests finished and the same query is requested
		else if(value == lastQuery)
		{
			//"Highlight" results by hiding them and letting them fade in
			$("#event_results").hide();
			$("#artist_results").hide();
			$("#event_results").fadeIn("slow");
			$("#artist_results").fadeIn("slow");
			return;
		}
		lastQuery = value;

		//Show loader
		$("#event_results").html('<div class="loader"></div>');
		$("#artist_results").hide();
		$("#route_results").hide();
		$("#ticket_results").hide();

		//Get events from back end
		eventRequest = $.ajax({
								type: "POST",
								url: "asynch.php?command=getEvents",
								data: {keywords: value},
								dataType: "xml",
								timeout: global_timeout,
								success: function (responseText)
																	{
																		//Hide result holders while we fill them
																		$("#event_results").hide();
																		$("#event_results").html('');
																		$("#artist_results").html('');
																		//If any errors are returned, show error messages
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
																				$("#artist_results").html('');
																			}
																			else
																			{
																				var artists = new Array();

																				//For each event
																				$(responseText).children("results").children("event").each(	function()
																															{
																																//Get data
																																var id = loadedEvents.length;
																																var resource = $(this).children("resource").text();
																																var date = $(this).children("date").text();
																																var name = $(this).children("artist").children("name").text().trim();
																																var birthDate = $(this).children("artist").children("birthDate").text();
																																var desc = $(this).children("artist").children("desc").text();
																																var image = $(this).children("artist").children("image").text();
																																var venueName = $(this).children("venue").children("name").text();
																																var price = parseInt($(this).children("price").text());
																																var address = $(this).children("venue").children("address").text();
																																var city = $(this).children("venue").children("city").text();
																																var country = $(this).children("venue").children("country").text();
																																var url = $(this).children("url").text();

																																//Keep loaded events in an array for searching for routes	
																																loadedEvents[id] = {
																																						dateTime: date.replace(':', '-'),
																																						resource: resource,
																																						price: price,
																																						location: {
																																									address: address,
																																									city: city,
																																									country: country
																																									},
																																						ticketURL : url
																																					};
	
																																//Create DOM elements for event information presentation
																																var ticketOption = $('<label/>').addClass("ticketOption");
																																var radio = $("<input/>").attr('type', 'radio').attr('name', 'ticket');
																																var titleSpan = $("<span/>").addClass('ticketTitle').html("&#10148; " + name + " at " + venueName);
																																var div = $("<div/>").append(titleSpan);
																																div.append('<span class="datetime">' + date.replace(' ', '<br />') + '</span>');
																																div.append('<span class="clearerLeft"></span>');
																																div.append('<span class="ticketPrice">&euro;' + (price * 0.01).toFixed(2) + '</span>');
																																div.append('<span class="clearer"></span>');

																																ticketOption.append(radio);
																																ticketOption.append(div);
																																$("#event_results").append(ticketOption);

																																//If event's artist was not printed earlier, print it
																																if($.inArray(name, artists) == -1)
																																{
																																	$("#artist_results").append("<h2>" + name + "</h2>");
																																	if(image)
																																	{
																																		$("#artist_results").append('<img src="' + image + '" style="max-height:100px;max-width:100px;" />');
																																	}
																																	if(birthDate)
																																	{
																																		$("#artist_results").append('<b>Born:</b> ' + birthDate + '<br />');
																																	}
																																	if(desc)
																																	{
																																		$("#artist_results").append(desc);
																																	}
																																	$("#artist_results").append('<div style="clear:both;"></div>');

																																	//Add artist name to array to prevent double printing
																																	artists[artists.length] = name;
																																}

																																//Apply click action on event DOM container element to allow for route searching
																																div.click(	function()
																																			{
																																				var location = $("#search_location").val().trim();
																																				if(!location)
																																				{
																																					alert('Enter a location from which to travel');
																																					$("#search_location").focus();
																																					return false;
																																				}
																																				openEvent(id, location);
																																			});
																															});
																			}
																			eventRequest = null;
																			$("#event_results").fadeIn("slow");
																			$("#artist_results").fadeIn("slow");
																		}
																	},
								error:	function(request, status, err)
										{
											//Proccess any errors that might occur
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
	else
	{
		$("#search_value").focus();
	}
}

//Cache request parameters to prevent duplicate subsequent requests
var lastId = null;
var lastLocation = null;
var lastMinutes = null;

function openEvent(id, location)
{
	minutes = parseInt($("#search_early").val().trim());

	//If a request is running
	if(routeRequest)
	{
		//Abort it and start a new one
		routeRequest.abort();
		routeRequest = null;
	}
	//If the last requests finished and the same query is requested
	else if((id == lastId) && (location == lastLocation) && (minutes == lastMinutes))
	{
		//"Highlight" results by hiding them and letting them fade in
		$("#ticket_results").hide();
		$("#ticket_results").fadeIn("slow");
		$("#route_results").hide();
		$("#route_results").fadeIn("slow");
		return;
	}
	lastId = id;
	lastLocation = location;
	lastMinutes = minutes;

	//Use local data from previous request
	var openEvent = loadedEvents[id];

	$("#ticket_results").hide();
	$("#ticket_results").html('');
	$("#ticket_results").append('<h2>Tickets</h2>');
	$("#ticket_results").append('Click <a href="' + openEvent.ticketURL + '" target="_blank">here</a> to buy tickets');
	$("#ticket_results").fadeIn("slow");

	//Show loader
	$("#route_results").html('<div class="loader"></div>');
	routeRequest = $.ajax({
							type: "POST",
							url: "asynch.php?command=getRoutes",
							data: {location_start: location, address: openEvent.location.address, city: openEvent.location.city, country: openEvent.location.country, dateTime: openEvent.dateTime, minutes: minutes},
							dataType: "xml",
							timeout: global_timeout,
							success: function (responseText)
																{
																	$("#route_results").hide();
																	$("#route_results").html('');

																	//If any errors are returned, show error messages
																	if($(responseText).find("error").length > 0)
																	{
																		$(responseText).find("error").each(	function()
																											{
																												alert($(this).find("message").text());
																											});
																	}
																	else
																	{
																		//Present routes
																		$("#route_results").append('<h2>Routes</h2>');
																		$("#route_results").append('<h3 style="width:100px;">Total Price</h3><h3>Type of transportation</h3>');
																		$("#route_results").append('<div class="clearer"></div>');
																		$(responseText).children("results").children("route").each(	function()
																													{
																														//Get data
																														var type = $(this).find("type").text();
																														var price = parseInt($(this).find("price").text());
																														var duration = $(this).find("duration").text();

																														//Compile HTML string for presentation
																														var bodyHTML = '';
																														bodyHTML += '<span class="routeInfoPrice">&euro;' + ((openEvent.price + price) * 0.01).toFixed(2) + '</span>';
																														bodyHTML += '<span class="routeInfoOther">';
																															if(type == "car")
																															{
																																var distance = parseInt($(this).find("distance").text());
																																bodyHTML += '<span class="routeTitle">By Car</span><br />';
																																bodyHTML += 'Distance: ' + distance + ' km<br />';
																															}
																															else
																															{
																																var transfers = $(this).find("transfers").text();
																																var arrival = $(this).find("arrival").text();
																																var arrivalInfo = arrival.split('T');
																																bodyHTML += '<span class="routeTitle">Public Transport</span><br />';
																																bodyHTML += 'Transfers: ' + transfers + '<br />';
																																bodyHTML += 'Arrival: ' + arrivalInfo[1] + '<br />';
																															}
																															bodyHTML += 'Travel time: ' + duration + '<br />';
																															bodyHTML += ((type == "car") ? "Fuel price" : "price") + ': &euro;' + (price * 0.01).toFixed(2);
																														bodyHTML += '</span>';

																														//Compose DOM container elements
																														var travelOption = $('<label/>').addClass("travelOption");
																														var radio = $("<input/>").attr('type', 'radio').attr('name', 'route');
																														var div = $("<div/>").append(bodyHTML);
																														travelOption.append(radio);
																														travelOption.append(div);

																														//Add to document
																														$("#route_results").append(travelOption);
																													});
																		$("#route_results").append('<div class="clearer"></div>');
																		routeRequest = null;
																	}
																	$("#route_results").fadeIn("slow");
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