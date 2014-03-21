<?php
class service_dbpedia
{
	public static function getArtistInfo($artist)
	{
		//Open SPARQL library
		$db = sparql_connect('http://dbpedia.org/sparql');

		//Construct query for retrieving artist information. Search for dbpedia-owl:Artist and dbpedia-owl:Band. Use optionals for various possibly unset fields
		$query = '	SELECT
						*
					WHERE
					{
						{
							?artist		foaf:name					?name;
										rdf:type					dbpedia-owl:Artist.
							OPTIONAL
							{
								?artist		dbpedia-owl:birthDate		?birthdate.
							}
							OPTIONAL
							{
								?artist		dbpprop:shortDescription	?shortdesc.
							}
							OPTIONAL
							{
								?artist		foaf:depiction		?image.
							}
							FILTER regex(?name, "'.$artist.'", "i")
						}
						UNION
						{
							?artist		foaf:name					?name;
										rdf:type					dbpedia-owl:Band.
							OPTIONAL
							{
								?artist		dbpedia-owl:birthDate		?birthdate.
							}
							OPTIONAL
							{
								?artist		dbpprop:shortDescription	?shortdesc.
							}
							OPTIONAL
							{
								?artist		foaf:depiction		?image.
							}
							FILTER regex(?name, "'.$artist.'", "i")
						}
					}';

		$data = array();
		
		//Execute SPARQL query
		$result = sparql_query($query);

		//Convert to simple array
		while($row = sparql_fetch_array($result))
		{
			$record = array(
							'dblink' => ' <'.$row['artist'].'> ',
							'name' => $row['name'],
							);
			if(isset($row['birthdate']))
			{
				$record['birthdate'] = $row['birthdate'];
			}
			if(isset($row['shortdesc']))
			{
				$record['shortdesc'] = $row['shortdesc'];
			}
			if(isset($row['image']))
			{
				$record['image'] = $row['image'];
			}
			$data[] = $record;
		}
		return $data;
	}
}
?>