<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
	<title>Be &#10148; There!</title>
    <link rel="stylesheet" type="text/css" href="css/style.css" />
	<script type="text/javascript" src="js/jquery-1.11.0.min.js"></script>
	<script type="text/javascript" src="js/jquery-ui.min.js"></script>    
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
	       	<div class="searchForm">
				<input type="text" id="search_value" value="<?=(isset($_GET['artist']) ? $_GET['artist'] : '');?>" placeholder="Search for an artist" />
				<input id="confirmSearch" type="button" value="Search" /><br /><br />
				Traveling from: <input type="text" id="search_location" value="<?=(isset($_GET['location']) ? $_GET['location'] : '');?>" placeholder="Your location" />
                arriving about <input type="text" id="search_early" value="<?=(isset($_GET['early']) ? $_GET['early'] : '30');?>" /> minutes early                
			</div>
            <div id="event_results"></div>
            <div id="artist_results"></div>
            <div id="results_clearer"></div>
            <div id="ticket_results"></div>
            <div id="route_results"></div>
        </div>
    </div>
    <div id="footer">
    	<div>
	    	<span>Popular Searches</span>
			<a href="?artist=Amos">Amos</a>
			<a href="?artist=Justin">Justin</a>
			<a href="?artist=Zuiderwijk">Zuiderwijk</a>
			<a href="#">More ...</a>
        </div>
    	<div>
	    	<span>Popular artists</span>
			<a href="?artist=Justin%20Timberlake">Justin Timberlake</a>
			<a href="?artist=Stromae">Stromae</a>
			<a href="?artist=Cesar%20Zuiderwijk">Cesar Zuiderwijk</a>
			<a href="#">More ...</a>
        </div>
    </div>
</div>
</body>
</html>