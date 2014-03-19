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
																															var id = loadedEvents.length;
																															var resource = $(this).children("resource").text();
																															var name = $(this).children("artist").children("name").text();
																															var venueName = $(this).children("venue").children("name").text();
																															var price = parseInt($(this).children("price").text());
																															var address = $(this).find("venue").find("address").text();
																															var city = $(this).find("venue").find("city").text();
																															var country = $(this).find("venue").find("country").text();
																															loadedEvents[id] = {
																																					resource: resource,
																																					price: price,
																																					location: {
																																								address: address,
																																								city: city,
																																								country: country
																																								}
																																				};

																															var ticketOption = $('<label/>').addClass("ticketOption");
																															var radio = $("<input/>").attr('type', 'radio').attr('name', 'ticket');
																															var titleSpan = $("<span/>").html("&#10148; " + name + " at " + venueName);																																
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
																														bodyHTML += 'Price: &euro;' + (price * 0.01).toFixed(2) + '<br />';
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

function executeQuery(query)
{
	var query = 'select ?a ?b ?c where {?a ?b ?c } LIMIT 10'
	
}