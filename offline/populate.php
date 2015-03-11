<?php

include ("geocode.php");

$dbconn = pg_connect("dbname=themove user=themove") or die('Could not connect: ' . pg_last_error());

$query = "SELECT id, address, city, state, zipcode FROM features.poi WHERE coords IS NULL";
$result = pg_query($query) or die('Query failed: ' . pg_last_error());


while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {

  $geo = geocodeOpenCage($line['address'], $line['city'], $line['state'], $line['zipcode']);

  $sql = "UPDATE features.poi SET coords =  ST_SetSRID(ST_MakePoint(" . $geo['lon'] . ", " . $geo['lat'] . "), 4326) WHERE id = " . $line['id'];

  pg_query($sql) or die("Failed to execute the update {" . $sql . "}");

  sleep(1);
  
}

?>
