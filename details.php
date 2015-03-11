<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-us">
  <head>
    <link rel="stylesheet" href="/js/tablesorter/themes/blue/style.css" type="text/css" id="" media="print, projection, screen" />
    
    <!-- <script type="text/javascript" src="/js/jquery-2.1.3.js"></script> -->
    <script type="text/javascript" src="/js/tablesorter/jquery-latest.js"></script>
    <script type="text/javascript" src="/js/tablesorter/jquery.tablesorter.js"></script>
    <script type="text/javascript">
	$(function() {
		     $("#incometbl").tablesorter({widgets: ['zebra']});
		     $("#familiestbl").tablesorter({widgets: ['zebra']});
		     $("#housingtbl").tablesorter({widgets: ['zebra']});
		     $("#educationaltbl").tablesorter({widgets: ['zebra']});

		     $("#nearby_schoolstbl").tablesorter({widgets: ['zebra']});
		     $("#nearby_grocerytbl").tablesorter({widgets: ['zebra']});
		     $("#nearby_bigboxtbl").tablesorter({widgets: ['zebra']});

	  });
    </script>
</head>

<body>
  <?php

$dbconn = pg_connect("dbname=themove user=themove") or die('Could not connect: ' . pg_last_error());

pg_prepare('get_bg_fid', 'SELECT bg.fid FROM boundary.va_bg AS bg INNER JOIN features.house AS h ON (ST_Intersects(bg.coords, h.coords)) WHERE id = $1');
$result = pg_execute('get_bg_fid', array($_GET['id'])) or die('Query failed: ' . pg_last_error());

if(1 <> pg_num_rows($result))
{
  die("Not exactly one row!");
}

$line = pg_fetch_array($result, null, PGSQL_ASSOC);
$fid = $line['fid'];

echo "<!-- fid = $fid -->\n";

function makeTable($longName, $shortName, $tableName, &$xlat, $fid)
{
  echo "<div><h3>$longName</h3></div>\n";
  echo '<table id="' . $shortName . 'tbl" class="tablesorter" border="0" cellpadding="0" cellspacing="1">';

$query = "SELECT z.* 
FROM acs.va_2013_5yr_bg AS bg 
INNER JOIN acs.$tableName AS z 
ON ('15000US'||bg.geoid = z.geoid) 
WHERE bg.fid = $fid";
$result = pg_query($query) or die('Query failed: ' . pg_last_error());

echo "<thead>\n";
echo "\t<tr>\n";
echo "\t\t<th>Item</th>\n";
echo "\t\t<th>Value</th>\n";
echo "\t</tr>\n";
echo "</thead>\n";

echo "<tbody>\n";
$nonprinting = array("fid", "geoid");

while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
    foreach ($line as $col_key => $col_value) {

            if( (!array_key_exists($col_key, $nonprinting)) && array_key_exists($col_key, $xlat)) {

                echo "\t<tr>\n";
   	        echo "\t\t<td>$xlat[$col_key]</td>\n";
   	        echo "\t\t<td>$col_value</td>\n";
                echo "\t</tr>\n";
	    }
    }
}
echo "</tbody>\n";
echo "</table>\n";

} // END FUNCTION makeTable

function makeProximiatePoiTable($longName, $shortName, $featureType, $fid, $nResults=5)
{
  echo "<div><h3>$longName</h3></div>\n";
  echo '<table id="' . $shortName . 'tbl" class="tablesorter" border="0" cellpadding="0" cellspacing="1">';

  $sql = 'WITH zzz AS (SELECT p.name, p.type, p.coords, round((ST_Distance(h.coords::geography,p.coords::geography)*0.000621371)::numeric, 1) AS dist, rank() OVER (PARTITION BY p.type ORDER BY ST_Distance(h.coords::geography,p.coords::geography) ASC) FROM features.house AS h, features.poi AS p WHERE h.id = $1 ) SELECT rank, name, dist FROM zzz WHERE rank <= $2 AND type = $3';
  pg_prepare('prox_poi_get_'.$featureType, $sql);
  $result = pg_execute('prox_poi_get_'.$featureType, array($fid, $nResults, $featureType));

  echo "<thead>\n";
  echo "\t<tr>\n";
  $i = pg_num_fields($result);
  for ($j = 0; $j < $i; $j++) {
    $fn = pg_field_name($result, $j);
    echo "\t\t<th>$fn</th>\n";
  }
  echo "\t</tr>\n";
  echo "</thead>\n";

  echo "<tbody>\n";
  while ($line = pg_fetch_array($result, null, PGSQL_ASSOC)) {
    echo "\t<tr>\n";
    
    foreach ($line as $col_value) {
      
      echo "\t\t<td>$col_value</td>\n";
    }
    echo "\t</tr>\n";

  }
 
  echo "</tbody>\n";
  echo "</table>\n";
  
} // END FUNCTION makeProximiatePoiTable

/////////////////////////////////////////////////
/////////////////////////////////////////////////

$incomeXlat = array (
  "b19013e1" => "Median Income",
  "b19013m1" => "Median Income (margin of error)",
  "b19301e1" => "Per Capita Income",
  "b19301m1" => "Per Capita Income (margin of error)"
);

$householdXlat = array (
  "b11001e1" => "Households",
  "b11001m1" => "Households (margin of error)",
  "b11001e2" => "Family Households",
  "b11001m2" => "Family Households (margin of error)",
  "b11001e3" => "Married-couple Family Households",
  "b11001m3" => "Married-couple Family Households (margin of error)"
);

$educationalXlat = array (
  "b15002e2" => "Total : Male",
  "b15002e19" => "Total : Female",

  "b15002e15" => "Bachelor's : Male",
  "b15002e16" => "Master's : Male",
  "b15002e17" => "Professional : Male",
  "b15002e18" => "Doctorate : Male",

  "b15002e32" => "Bachelor's : Female",
  "b15002e33" => "Master's : Female",
  "b15002e34" => "Professional : Female",
  "b15002e35" => "Doctorate : Female"
);

$housingXlat = array (
  "b25002e1" => "Housing Units",
  "b25002e2" => "Housing Units: Occupied",
  "b25002e3" => "Housing Units: Vacant",

  "b25006e2" => "Race: White alone",
  "b25006e3" => "Race: Black alone",

  "b25064e1" => "Median Gross Rent",
  "b25064m1" => "Median Gross Rent (margin of error)",

  "b25071e1" => "Median Gross Rent as a % of Income",
  "b25071m1" => "Median Gross Rent as a % of Income (margin of error)",
);

makeProximiatePoiTable("Nearest Schools", "nearby_schools", "school", $_GET['id'], 5);
makeProximiatePoiTable("Nearest Grocery Stores", "nearby_grocery", "grocery", $_GET['id'], 5);
makeProximiatePoiTable("Nearest Big Box Stores", "nearby_bigbox", "bigbox", $_GET['id'], 5);

makeTable("Income", "income", "va_2013_5yr_bg_x19_income", $incomeXlat, $fid);

makeTable("Household Families", "families", "va_2013_5yr_bg_x11_household_family_subfamilies", $householdXlat, $fid);

makeTable("Educational Attainment", "educational", "va_2013_5yr_bg_x15_educational_attainment", $educationalXlat, $fid);

makeTable("Housing Characteristics", "housing", "va_2013_5yr_bg_x25_housing_characteristics", $housingXlat, $fid);

?>

</body>
</html>
