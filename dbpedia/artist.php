<?php

        function make_db($stringy)
        {
                return htmlspecialchars('<http://dbpedia.org/' . $stringy . '> ');
        }


	require_once('sparqllib.php');
	$db = sparql_connect('http://dbpedia.org/sparql');
	$query = 'SELECT * WHERE { ?artist foaf:name "Justin Timberlake"@en ; rdf:type dbpedia-owl:Artist ; dbpedia-owl:wikiPageID ?id ; dbpedia-owl:birthDate ?birthdate ; dbpprop:shortDescription ?shortdesc ; foaf:depiction ?image}';

	$result = sparql_query($query);
	$fields = sparql_field_array($result);
	while($row = sparql_fetch_array($result))
	{
	//  foreach($fields as $field)
	//  {
		$dbpedia=$fields[0];
		$wikiid=$fields[1];
		$birth=$fields[2];
		$shortdesc=$fields[3];
		$image=$fields[4];
	//  }
		$resp=make_db($row[$wikiid]) . make_db("dblink") . htmlspecialchars('<'.$row[$dbpedia].'>') . " ; " . make_db("birthdate") . "\"" . $row[$birth] . "\" ; " . make_db("shortdesc") . "\"" . $row[$shortdesc] . "\" ; " . make_db("image") . "\"" . $row[$image] . "\" . ";
		echo $resp;

		//////////////////////////////////////////////////////////////////////////////////////////////////
		// Insert stuff into sesame
		$url = 'http://178.85.74.3:8080/openrdf-sesame/repositories/IWA_TEST/statements';

		// use key 'http' even if you send the request to https://...
		$options = array(
			'http' => array(
			'header'  => "Content-Type: text/turtle",
			'method'  => 'POST',
			'content' => htmlspecialchars_decode($resp)
			),
		);
		$context  = stream_context_create($options);
		$result = file_get_contents($url, false, $context);

		var_dump($result);

	}
?>
