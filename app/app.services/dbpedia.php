<?php
class service_dbpedia
{
	public static function getArtistInfo($artist)
	{
		$db = sparql_connect('http://dbpedia.org/sparql');
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
		$result = sparql_query($query);
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