<?php

function geocode($street, $city, $state, $zip, $debug=false)
{

	$address = array
	(
		"street"     => $street,
		"city"       => $city,
		"state"      => $state,
		"postalcode" => $zip
	);

	// http://php.net/manual/en/function.file-get-contents.php
	$url = 'http://nominatim.openstreetmap.org/search?format=xml&' . http_build_query($address);
	if($debug)
	  echo "<!-- $url -->\n";
	
	$geoCodeXml = file_get_contents( $url );

	// http://php.net/manual/en/simplexml.examples.php
	$xml = simplexml_load_string($geoCodeXml);
	if($debug)
	  {
	    echo "<!-- ";
	    print_r($xml);
	    echo "-->\n";
	  }
	
	return array (
	       "lat" => $xml->place['lat'],
	       "lon" => $xml->place['lon']
	);
}

function geocodeOpenCage($street, $city, $state, $zip, $debug=false)
{
  $url = "http://api.opencagedata.com/geocode/v1/xml?query=" . urlencode($street . ' ' . $city . ' ' . $state . ' ' . $zip) . '&pretty=1&key=f29ce1dd97a39ce6b621e130a2506687';

  
  if($debug)
    echo "<!-- $url -->\n";
  
  $geoCodeXml = file_get_contents( $url );
  
  // http://php.net/manual/en/simplexml.examples.php
  $xml = simplexml_load_string($geoCodeXml);
  if($debug)
    {
      echo "<!-- ";
      print_r($xml);
      echo "-->\n";
    }

  return array('lat' => $xml->results->result[0]->geometry->lat,
	       'lon' => $xml->results->result[0]->geometry->lng
	       );
}

// Example:
//
// $res = geocode("2724 Towne Crest Dr.", "St. Louis", "MO", 63129);
// echo $res['lat'] . ' ' . $res['lon'];

//$res = geocodeOpenCage("12318 Cinnamon St ", "Woodbridge", "VA", 22192, true);
//echo $res['lat'] . ' ' . $res['lon'];
?>