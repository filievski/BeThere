<?php
require_once('sparqllib.php');
$db = sparql_connect('http://dbpedia.org/sparql');
$query = "SELECT ?film
WHERE { ?film <http://purl.org/dc/terms/subject> <http://dbpedia.org/resource/Category:French_films> } LIMIT 1";

$result = sparql_query($query);
$fields = sparql_field_array($result);
while($row = sparql_fetch_array($result))
{
  foreach($fileds as $field)
  {
    print"$row[$field] \n";
  }
}
?>
