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
									dbpedia-owl:birthDate		?birthdate;
									dbpprop:shortDescription	?shortdesc;
									foaf:depiction				?image
					}';

		$data = array();
		$result = sparql_query($query);

		$fields = sparql_field_array($result);
		$dbpedia = $fields[0];
		$wikiid = $fields[1];
		$birth = $fields[2];
		$shortdesc = $fields[3];
		$image = $fields[4];

		while($row = sparql_fetch_array($result))
		{
			$data[] = array(
							'dblink' => htmlspecialchars(' <'.$row[$dbpedia].'> '),
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