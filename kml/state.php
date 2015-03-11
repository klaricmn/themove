<?php 
      header('Content-Type: application/vnd.google-earth.kml+xml kml');
      header('Content-Disposition: attachment; filename="state.kml"');

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
<Document id="root_doc">
<Folder><name>State</name>
<?php
$dbconn = pg_connect("host=localhost dbname=themove user=themove");

$epsg = $_GET['epsg'] + 1 - 1;

if(($epsg > 0) && is_int($epsg))
{
	$sql = "SELECT ST_AsKML(coords) AS kml, name FROM boundary.state WHERE (stusps = 'VA') ";
}
else
{
	$sql = "SELECT ST_AsKML(ST_Transform(coords, $epsg)) AS kml, name FROM boundary.state WHERE (stusps = 'VA') ";
}

if(isset($_GET['bbox']))
{
	$bbox = explode(",", $_GET['bbox']);
	$bboxInter = " AND ST_Intersects(coords, ST_MakeEnvelope($bbox[0], $bbox[1], $bbox[2], $bbox[3], 4326)) ";
	$sql .= $bboxInter;
}

$result = pg_query($dbconn, $sql) or die('Query failed: ' . pg_last_error());


$redStyle     = "<Style><LineStyle><color>ff0000ff</color></LineStyle><PolyStyle><color>440000ff</color><fill>1</fill></PolyStyle></Style>";
$yellowStyle  = "<Style><LineStyle><color>ff00ffff</color></LineStyle><PolyStyle><color>4400ffff</color><fill>1</fill></PolyStyle></Style>";
$blueStyle    = "<Style><LineStyle><color>ffff0000</color></LineStyle><PolyStyle><color>44ff0000</color><fill>1</fill></PolyStyle></Style>";
$greenStyle   = "<Style><LineStyle><color>ff00ff00</color></LineStyle><PolyStyle><color>4400ff00</color><fill>1</fill></PolyStyle></Style>";

while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
      echoPlacemark($line['name'], $line, $blueStyle);
}

pg_free_result($result);

pg_close($dbconn);

?>
</Folder>
</Document>
</kml>