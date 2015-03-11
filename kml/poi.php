<?php 
      header('Content-Type: application/vnd.google-earth.kml+xml kml');
      header('Content-Disposition: attachment; filename="poi.kml"');

      function echoPlacemark($name, $data, $style)
      {
            echo "\t\t<Placemark>\n";
      	    echo "\t\t\t<name>$name</name>\n";
	    if(null <> $style)
	    	    echo "\t\t\t$style\n";
	    echo "\t\t\t" . $data['kml'] . "\n";
	    echo "\t\t\t<description>\n";
	    foreach($data as $k => $v) 
	    {
		    if("kml" != $k)
	    	    	     echo "\t\t\t\t$k = $v <br/>\n";
            }
	    echo "\t\t\t</description>\n";
      	    echo "\t\t</Placemark>\n";
      }
?>
<?xml version="1.0" encoding="utf-8" ?>
<kml xmlns="http://www.opengis.net/kml/2.2">
<Document>
<Folder><name><?= htmlspecialchars($_GET['type']) ?></name>
<?php
$dbconn = pg_connect("host=localhost dbname=themove user=themove");

if(isset($_GET['epsg']))
  $epsg = $_GET['epsg'] + 1 - 1;
else
  $epsg = 0;

if(($epsg > 0) && is_int($epsg))
{
	$sql = "SELECT ST_AsKML(coords) AS kml, id, type, name FROM features.poi WHERE TRUE ";
}
else
{
	$sql = "SELECT ST_AsKML(ST_Transform(coords, $epsg)) AS kml, id, type, name FROM features.poi WHERE TRUE ";
}

if(isset($_GET['bbox']))
{
	$bbox = explode(",", $_GET['bbox']);
	$bboxInter = " AND ST_Intersects(coords, ST_MakeEnvelope($bbox[0], $bbox[1], $bbox[2], $bbox[3], 4326)) ";
	$sql .= $bboxInter;
}

if("school" == $_GET['type'])
  {
    $sql .= " AND (type = 'school') ";
  }
else if ("grocery" == $_GET['type'])
  {
    $sql .= " AND (type = 'grocery') ";
  }
else if ("target" == $_GET['type'])
  {
    $sql .= " AND (type = 'bigbox') AND (name = 'Target') ";
  }
else if ("walmart" == $_GET['type'])
  {
    $sql .= " AND (type = 'bigbox') AND (name = 'Walmart') ";
  }

$result = pg_query($dbconn, $sql) or die('Query failed: ' . pg_last_error());


$style['school']     = "<Style><IconStyle><scale>0.1</scale><Icon><href>icons/school.png</href></Icon></IconStyle></Style>";
$style['grocery']     = "<Style><IconStyle><scale>0.1</scale><Icon><href>icons/grocery.png</href></Icon></IconStyle></Style>";
$style['target']     = "<Style><IconStyle><scale>0.1</scale><Icon><href>icons/target.png</href></Icon></IconStyle></Style>";
$style['walmart']     = "<Style><IconStyle><scale>0.1</scale><Icon><href>icons/walmart.png</href></Icon></IconStyle></Style>";


while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
      echoPlacemark($line['name'], $line, $style[$_GET['type']]);
}

pg_free_result($result);

pg_close($dbconn);

?>
</Folder>
</Document>
</kml>