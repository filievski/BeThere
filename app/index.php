<?php
	require_once('./app.services/9292.php');
?><!DOCTYPE html>
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
	}

	#container {
		position:absolute;
		width:100%;
		height:100%;
		background-image:url('images/pukkelpop.jpg');
		background-size:cover;
		background-repeat:no-repeat;
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
	#footer {
		width:890px;
		height:70px;
		margin:0 auto;
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

	.travelOption div {
		display:block;
		border:1px solid #DDDDDD;
		padding:10px;
		margin:10px;
		float:left;
		cursor:pointer;
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
        	<span>Search</span>
        	<span class="divider">&#10148;</span>
        	<span>This Week</span>
        	<span class="divider">&#10148;</span>
        	<span>13 results</span>
        </div>
        <div id="body">
        	<h1>Pinkpop</h1>
            	<?php
					$minPrice = 9000;
					$maxPrice = 9000;
				?>
	            <h2>Tickets</h2>
                	<label class="ticketOption"><input type="radio" name="ticket" disabled /><div>Weekend<span class="soldout">Sold out!</span></div></label>
                	<label class="ticketOption"><input type="radio" name="ticket" disabled /><div>Saturday<span class="soldout">Sold out!</span></div></label>
                	<label class="ticketOption"><input type="radio" name="ticket" /><div>Sunday &euro;90</div></label>
                	<label class="ticketOption"><input type="radio" name="ticket" /><div>Monday &euro;90</div></label>
                    <br />
                    <br />
    	        <h2>Travel</h2>
					<?php
                    $locations_from = service_9292::getSuggestions('Graaf Lodewijkstraat, Arnhem');
                    $locations_to = service_9292::getSuggestions('Megaland, Landgraaf');
                    $routes = service_9292::getRoutes($locations_from[0], $locations_to[0]);
                    if(sizeof($routes))
                    {
						$minTravelPrice = NULL;
						$maxTravelPrice = NULL;
       	                echo '<b>Routes found:</b><br />';
						foreach($routes as $route)
						{
	                        $departure = strtotime($route['departure']);
    	                    $arrival = strtotime($route['arrival']);
                        	$price = intval($route['fareInfo']['fullPriceCents']);

							if($minTravelPrice === NULL)
							{
								$minTravelPrice = $price;
							}
							else if($price < $minTravelPrice)
							{
								$minTravelPrice = $price;
							}

							if($maxTravelPrice === NULL)
							{
								$maxTravelPrice = $price;
							}
							else if($price > $maxTravelPrice)
							{
								$maxTravelPrice = $price;
							}

        	                echo '<label class="travelOption">';
								echo '<input type="radio" name="travel" />';
	        	                echo '<div>';
	            		            echo 'Departure: '.date("H:i", $departure).'<br />';
    	    	        	        echo 'Departure: '.date("H:i", $arrival).'<br />';
    		                	    echo 'Transfers: '.$route['numberOfChanges'].'<br />';
		    	                    echo 'Price: &euro;'.number_format(($price / 100), 2);
	        	                echo '</div>';									
        	                echo '</label>';
						}

       	                echo '<div class="clearer"></div>';
						$minPrice += $minTravelPrice;
						$maxPrice += $maxTravelPrice;
                    }
                    else
                    {
                        echo 'No Routes found :(';
                    }
                    ?>
                    <br />
                    <br />
        	    <h2>Total</h2>
                	Minimum price: &euro;<?=number_format(($minPrice / 100), 2);?><br />
                	Maximum price: &euro;<?=number_format(($maxPrice / 100), 2);?>                    
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