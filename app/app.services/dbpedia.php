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
						?artist		foaf:name					"'.$artist.'"@en;
									rdf:type					dbpedia-owl:Artist;
									dbpedia-owl:wikiPageID		?id;
									foaf:name					?name;
									dbpedia-owl:birthDate		?birthdate;
									dbpprop:shortDescription	?shortdesc;
									foaf:depiction				?image.
					}';

		$data = array();
		$result = sparql_query($query);

		$fields = sparql_field_array($result);
		$name = $fields[2];
		$dbpedia = $fields[0];
		$wikiid = $fields[1];
		$birth = $fields[3];
		$shortdesc = $fields[4];
		$image = $fields[5];

		while($row = sparql_fetch_array($result))
		{
			$data[] = array(
							'dblink' => ' <'.$row[$dbpedia].'> ',
							'name' => $row[$name],
							'wikiid' => $row[$wikiid],
							'birth' => $row[$birth],
							'shortdesc' => $row[$shortdesc],
							'image' => $row[$image]
							);
		}
		return $data;
	}
}
?>