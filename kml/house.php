<?php 
      header('Content-Type: application/vnd.google-earth.kml+xml kml');
      header('Content-Disposition: attachment; filename="house.kml"');

      function echoPlacemark($name, $descr, $kml, $style)
      {
            echo "\t\t<Placemark>\n";
      	    echo "\t\t\t<name>$name</name>\n";
	    if(null <> $style)
	    	    echo "\t\t\t$style\n";
	    echo "\t\t\t" . $kml . "\n";
	    echo "\t\t\t<description>\n";
	    echo $descr;
	    echo "\t\t\t</description>\n";
      	    echo "\t\t</Placemark>\n";
      }
?>
<?xml version="1.0" encoding="utf-8" ?>
<kml xmlns="http://www.opengis.net/kml/2.2">
<Document id="root_doc">
<Folder><name>Houses</name>
<?php
$dbconn = pg_connect("host=localhost dbname=themove user=themove");

$epsg = $_GET['epsg'] + 1 - 1;

if(($epsg > 0) && is_int($epsg))
{
	$sql = "SELECT address, verdict, ST_AsKML(h.coords) AS kml_house, id, ST_AsKML(bg.coords) AS kml_bg, fid FROM features.house AS h INNER JOIN boundary.va_bg AS bg ON (ST_Intersects(h.coords,bg.coords)) WHERE (verdict <> '4-No' OR verdict IS NULL) ";
}
else
{
	$sql = "SELECT address, verdict, ST_AsKML(ST_Transform(h.coords, $epsg)) AS kml_house, id, ST_AsKML(bg.coords) AS kml_bg, fid FROM features.house AS h INNER JOIN boundary.va_bg AS bg ON (ST_Intersects(h.coords,bg.coords)) WHERE (verdict <> '4-No' OR verdict IS NULL) ";
}

if(isset($_GET['bbox']))
{
	$bbox = explode(",", $_GET['bbox']);
	$bboxInter = " AND ST_Intersects(h.coords, ST_MakeEnvelope($bbox[0], $bbox[1], $bbox[2], $bbox[3], 4326)) ";
	$sql .= $bboxInter;
}

$result = pg_query($dbconn, $sql) or die('Query failed: ' . pg_last_error());


$houseStyle = "<Style><IconStyle><scale>0.25</scale><Icon><href>icons/house.png</href></Icon></IconStyle></Style>";

$houseMultiStyle['1-Yes']         = "<Style><IconStyle><scale>0.25</scale><Icon><href>icons/house-green.png</href></Icon></IconStyle></Style>";
$houseMultiStyle['2-Maybe']       = "<Style><IconStyle><scale>0.25</scale><Icon><href>icons/house-yellow.png</href></Icon></IconStyle></Style>";
$houseMultiStyle['3-Maybe Not']   = "<Style><IconStyle><scale>0.25</scale><Icon><href>icons/house-red.png</href></Icon></IconStyle></Style>";
$houseMultiStyle['']              = "<Style><IconStyle><scale>0.25</scale><Icon><href>icons/house-gray.png</href></Icon></IconStyle></Style>";


$redStyle     = "<Style><LineStyle><color>ff0000ff</color></LineStyle><PolyStyle><color>440000ff</color><fill>1</fill></PolyStyle></Style>";
$yellowStyle  = "<Style><LineStyle><color>ff00ffff</color></LineStyle><PolyStyle><color>4400ffff</color><fill>1</fill></PolyStyle></Style>";
$blueStyle    = "<Style><LineStyle><color>ffbb5500</color><width>2</width></LineStyle><PolyStyle><color>44ff0000</color><fill>1</fill></PolyStyle></Style>";
$greenStyle   = "<Style><LineStyle><color>ff00ff00</color></LineStyle><PolyStyle><color>4400ff00</color><fill>1</fill></PolyStyle></Style>";


while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
  
  echoPlacemark($line['address'], "", $line['kml_house'], $houseMultiStyle[$line['verdict']]);


  echoPlacemark($line['fid'], "", $line['kml_bg'], $blueStyle);
}

pg_free_result($result);

pg_close($dbconn);

?>
</Folder>
</Document>
</kml>