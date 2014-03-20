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

var global_timeout = 30000;

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
	$("#event_results").html('<div class="loader"></div>');
	$("#artist_results").html('');
	$("#route_results").html('');
	eventRequest = $.ajax({
							type: "POST",
							url: "asynch.php?command=getEvents",
							data: {keywords: value},
							dataType: "xml",
							timeout: global_timeout,
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
																			$("#artist_results").html('');
																		}
																		else
																		{
																			$(responseText).children("results").children("event").each(	function()
																														{
																															var id = loadedEvents.length;
																															var resource = $(this).children("resource").text();
																															var name = $(this).children("artist").children("name").text();
																															var birthDate = $(this).children("artist").children("birthDate").text();
																															var desc = $(this).children("artist").children("desc").text();
																															var image = $(this).children("artist").children("image").text();
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
																															var titleSpan = $("<span/>").addClass('ticketTitle').html("&#10148; " + name + " at " + venueName);																																
																															var div = $("<div/>").append(titleSpan).append('<span class="ticketPrice">Price: &euro;' + (price * 0.01).toFixed(2) + '</span>');
																															ticketOption.append(radio);
																															ticketOption.append(div);
																															$("#event_results").append(ticketOption);

																															var artistHtml = "";
																															if(image)
																															{
																																artistHtml += '<img src="' + image + '" style="width:100px;" />';
																															}
																															artistHtml += '<b>' + name + '</b>' + '<br />';
																															if(birthDate)
																															{
																																artistHtml += '<b>Born:</b> ' + birthDate + '<br />';
																															}
																															if(desc)
																															{
																																artistHtml += desc;
																															}
																															if(artistHtml)
																															{
																																$("#artist_results").html("<h2>Artist</h2>");
																																$("#artist_results").append(artistHtml);
																															}
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

	$("#route_results").html('<div class="loader"></div>');
	routeRequest = $.ajax({
							type: "POST",
							url: "asynch.php?command=getRoutes",
							data: {location_start: location, address: openEvent.location.address, city: openEvent.location.city, country: openEvent.location.country},
							dataType: "xml",
							timeout: global_timeout,
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
																		$("#route_results").append('<h3 style="width:100px;">Total Price</h3><h3>Type of transportation</h3>');
																		$("#route_results").append('<div class="clearer"></div>');
																		$(responseText).children("results").children("route").each(	function()
																													{
																														var titleHTML = '';
																														var bodyHTML = '';

																														var type = $(this).find("type").text();
																														var price = parseInt($(this).find("price").text());

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
																																bodyHTML += '<span class="routeTitle">Public Transport</span><br />';
																																bodyHTML += 'Transfers: ' + transfers + '<br />';
																															}
																															bodyHTML += 'Travel time: <br />';
																															bodyHTML += 'Price: &euro;' + (price * 0.01).toFixed(2);
																														bodyHTML += '</span>';

																														var travelOption = $('<label/>').addClass("travelOption");
																														var radio = $("<input/>").attr('type', 'radio').attr('name', 'route');
																														var div = $("<div/>").append(bodyHTML);
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
