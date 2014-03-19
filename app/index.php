<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Be &#10148; There!</title>
    <link rel="stylesheet" type="text/css" href="css/style.css" />
	</style>
	<script src="http://code.jquery.com/jquery-1.11.0.min.js"></script>
    <script type="text/javascript" src="js/search.js"></script>
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