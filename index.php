<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-us">
  <head>
    <link rel="stylesheet" href="/js/tablesorter/themes/blue/style.css" type="text/css" id="" media="print, projection, screen" />
    
    <script type="text/javascript" src="/js/tablesorter/jquery-latest.js"></script>
    <script type="text/javascript" src="/js/tablesorter/jquery.tablesorter.js"></script>
    <script type="text/javascript">
	$(function() {
	    $("#housetbl").tablesorter({sortList:[[14,1], [15,0]], widgets: ['zebra']});
	  });
    </script>

    <link rel="stylesheet" href="http://openlayers.org/en/v3.2.1/css/ol.css" type="text/css">
       <style>
   .map {
 height: 400px;
 width: 100%;
 }
    </style>
        <script src="http://openlayers.org/en/v3.2.1/build/ol.js" type="text/javascript"></script>
<!--       <script src="http://openlayers.org/en/v3.2.1/resources/jquery.min.js" type="text/javascript"></script> -->
   
</head>

<body>

<div style="text-align: right"><a href="house.php">Add new house</a></div>
<hr />

<div id="map" class="map"></div>
<div id="info"></div>
<hr />
<script type="text/javascript">

var projection = ol.proj.get('EPSG:3857');

var hostname = '<?= $_SERVER['SERVER_NAME'] ?>';

   var vector = new ol.layer.Vector({
     source: new ol.source.KML({
       projection: projection,
	   url: 'http://' + hostname + '/themove/kml/house.php?epsg=3857&'
	   })
	 });

   var vectorSchool = new ol.layer.Vector({
     source: new ol.source.KML({
       projection: projection,
	   url: 'http://' + hostname + '/themove/kml/poi.php?type=school&epsg=3857&'
	   })
	 });

   var vectorGrocery = new ol.layer.Vector({
     source: new ol.source.KML({
       projection: projection,
	   url: 'http://' + hostname + '/themove/kml/poi.php?type=grocery&epsg=3857&'
	   })
	 });

   var vectorTarget = new ol.layer.Vector({
     source: new ol.source.KML({
       projection: projection,
	   url: 'http://' + hostname + '/themove/kml/poi.php?type=target&epsg=3857&'
	   })
	 });

   var vectorWalmart = new ol.layer.Vector({
     source: new ol.source.KML({
       projection: projection,
	   url: 'http://' + hostname + '/themove/kml/poi.php?type=walmart&epsg=3857&'
	   })
	 });

   var map = new ol.Map({
     target: 'map',
	 layers: [
		  //new ol.layer.Tile({ source: new ol.source.MapQuest({layer: 'sat'}) }),
		  new ol.layer.Tile({ source: new ol.source.OSM() }),
		  vector,
		  vectorSchool,
		  vectorGrocery,
		  vectorTarget,
		  vectorWalmart
		  ],
	 view: new ol.View({
	   center: ol.proj.transform([ -77.1794,  38.751998 ], 'EPSG:4326', 'EPSG:3857'),
	       zoom: 11
	       })
	 });

map.addControl(new ol.control.OverviewMap());
map.addControl(new ol.control.ScaleLine());

var displayFeatureInfo = function(pixel) {
  var features = [];
  map.forEachFeatureAtPixel(pixel, function(feature, layer) {
      features.push(feature);
    });
  if (features.length > 0) {
    var info = [];
    var i, ii;
    for (i = 0, ii = features.length; i < ii; ++i) {
      info.push(features[i].get('name'));
    }
    document.getElementById('info').innerHTML = info.join(' / ') || '(unknown)';
    //map.getTarget().style.cursor = 'pointer';
  } else {
    document.getElementById('info').innerHTML = '&nbsp;';
    //map.getTarget().style.cursor = '';
  }
};

map.on('pointermove', function(evt) {
    if (evt.dragging) {
      return;
    }
    var pixel = map.getEventPixel(evt.originalEvent);
    displayFeatureInfo(pixel);
  });

</script>		   
   
<table id="housetbl" class="tablesorter" border="0" cellpadding="0" cellspacing="1">
<?php
$dbconn = pg_connect("dbname=themove user=themove") or die('Could not connect: ' . pg_last_error());

$query = "SELECT *, round(0.000621371*ST_Distance(coords::geography, (ST_SetSRID(ST_MakePoint(-77.1794,  38.751998), 4326)::geography))::numeric,1) AS distance FROM features.house";
$result = pg_query($query) or die('Query failed: ' . pg_last_error());

$nonprinting = array("id", "coords", "notes", "url");

echo "<thead>\n";
echo "\t<tr>\n";
echo "\t<th>Details</th>\n";
echo "\t<th>Listing</th>\n";
$i = pg_num_fields($result);
for ($j = 0; $j < $i; $j++) {
  $fn = pg_field_name($result, $j);
  if(! in_array($fn, $nonprinting) )
    echo "\t\t<th>$fn</th>\n";
}
echo "\t<th>Edit</th>\n";
echo "\t</tr>\n";
echo "</thead>\n";

echo "<tbody>\n";
while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
      echo "\t<tr>\n";
      $id = $line['id'];
      echo "\t<td><a href=\"details.php?id=$id\" target=\"_blank\">House $id</a></td>\n";
      if(strlen($line['url']))
	echo "\t<td><a href=\"" . $line['url'] . "\" target=\"blank\">Link</a></td>\n";
      else
	echo "\t<td>n/a</td>\n";
      	   foreach ($line as $col_key => $col_value) {
                if(! in_array($col_key, $nonprinting) )
  	   	    echo "\t\t<td>$col_value</td>\n";
	   }
      echo "\t<td><a href=\"house.php?id=" . $line['id'] . "\">Edit</a></td>\n";
      echo "\t</tr>\n";
}
echo "</tbody>\n";
?>

<?php
?>
</table>
</body>
</html>
