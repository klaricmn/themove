<?php

include('geocode.php');

$debug = false;

if($debug)
  print_r($_POST);

if(isset($_POST['id']) && is_int((int)$_POST['id']))
  {
    $dbconn = pg_connect("dbname=themove user=themove") or die('Could not connect: ' . pg_last_error());

    $verdict = null;
    switch($_POST['verdict'])
      {
      case 1:
	$verdict = "1-Yes";
	break;
      case 2:
	$verdict = "2-Maybe";
	break;
      case 3:
	$verdict = "3-Maybe Not";
	break;
      case 4:
	$verdict = "4-No";
	break;	
      }
    
    if("insert" == $_POST['action'])
      {
	if("on" == $_POST['needs_geocoding'])
	  {
	    $coordObj = geocodeOpenCage($_POST['address'], $_POST['city'], $_POST['state'], $_POST['zipcode'], $debug);

	    if($debug)
	      print_r($coordObj);

	    $params = array(
			    $_POST['address'],
			    $_POST['address2'],
			    $_POST['city'],
			    $_POST['state'],
			    $_POST['zipcode'],
			    $coordObj['lon'],
			    $coordObj['lat'],
			    (int)$_POST['n_bed'],
			    (double)$_POST['n_bath'],
			    $_POST['type'],
			    $_POST['notes'],
			    $_POST['url'],
			    $_POST['base_rent'],
			    $_POST['hoa_fee'],
			    $_POST['other_fee'],
			    isset($_POST['util_incl'])?"TRUE":"FALSE",
			    $verdict
			    );
	    
	    pg_prepare('add_house', 'INSERT INTO features.house VALUES (DEFAULT, $1, $2, $3, $4, $5, ST_SetSRID(ST_MakePoint($6, $7), 4326), $8, $9, $10, $11, $12, $13, $14, $15, $16, $17)');
	    $result = pg_execute('add_house', $params) or die('Query failed: ' . pg_last_error());
	    
	  } // end needs geocoding
	else
	  {
	    $params = array(
			    $_POST['address'],
			    $_POST['address2'],
			    $_POST['city'],
			    $_POST['state'],
			    $_POST['zipcode'],
			    $_POST['n_bed'],
			    $_POST['n_bath'],
			    $_POST['type'],
			    $_POST['notes'],
			    $_POST['url'],
			    $_POST['base_rent'],
			    $_POST['hoa_fee'],
			    $_POST['other_fee'],
			    $_POST['util_incl'],
			    $verdict
			    );
	    
	    pg_prepare('add_house_no_geo', 'INSERT INTO features.house VALUES (DEFAULT, $1, $2, $3, $4, $5, NULL, $6, $7, $8, $9, $10, $11, $12, $13, $14, $15)');
	    $result = pg_execute('add_house_no_geo', $params) or die('Query failed: ' . pg_last_error());
	  } // ends else for no geocoding
	
      } // ends insert
    else if("update" == $_POST['action'])
      {
	if("on" == $_POST['needs_geocoding'])
	  {
	    $coordObj = geocodeOpenCage($_POST['address'], $_POST['city'], $_POST['state'], $_POST['zipcode'], $debug);

	    if($debug)
	      print_r($coordObj);
	    $params = array(
			    $_POST['address'],
			    $_POST['address2'],
			    $_POST['city'],
			    $_POST['state'],
			    $_POST['zipcode'],
			    $coordObj['lon'],
			    $coordObj['lat'],
			    (int)$_POST['n_bed'],
			    (double)$_POST['n_bath'],
			    $_POST['type'],
			    $_POST['notes'],
			    $_POST['url'],
			    $_POST['base_rent'],
			    $_POST['hoa_fee'],
			    $_POST['other_fee'],
			    isset($_POST['util_incl'])?"TRUE":"FALSE",
			    $verdict,
			    $_POST['id']
			    );
	    
	    pg_prepare('edit_house', 'UPDATE features.house SET
                                       address = $1, 
                                       address2 = $2, 
                                       city = $3, 
                                       state = $4, 
                                       zipcode = $5, 
                                       coords = ST_SetSRID(ST_MakePoint($6, $7), 4326), 
                                       n_bed = $8, 
                                       n_bath = $9, 
                                       type = $10, 
                                       notes = $11, 
                                       url = $12, 
                                       base_rent = $13, 
                                       hoa_fee = $14, 
                                       other_fee = $15, 
                                       util_incl = $16,
                                       verdict = $17
                                       WHERE id = $18');
	    $result = pg_execute('edit_house', $params) or die('Query failed: ' . pg_last_error());

	  } // ends needs geocoding
	else
	  {
	    $params = array(
			    $_POST['address'],
			    $_POST['address2'],
			    $_POST['city'],
			    $_POST['state'],
			    $_POST['zipcode'],
			    (int)$_POST['n_bed'],
			    (double)$_POST['n_bath'],
			    $_POST['type'],
			    $_POST['notes'],
			    $_POST['url'],
			    $_POST['base_rent'],
			    $_POST['hoa_fee'],
			    $_POST['other_fee'],
			    isset($_POST['util_incl'])?"TRUE":"FALSE",
			    $verdict,
			    $_POST['id']
			    );
	    
	    pg_prepare('edit_house_no_geo', 'UPDATE features.house SET
                                       address = $1, 
                                       address2 = $2, 
                                       city = $3, 
                                       state = $4, 
                                       zipcode = $5, 
                                       n_bed = $6, 
                                       n_bath = $7, 
                                       type = $8, 
                                       notes = $9, 
                                       url = $10, 
                                       base_rent = $11, 
                                       hoa_fee = $12, 
                                       other_fee = $13, 
                                       util_incl = $14,
                                       verdict = $15
                                       WHERE id = $16');
	    $result = pg_execute('edit_house_no_geo', $params) or die('Query failed: ' . pg_last_error());

	  } // ends else for no geocoding
      } // ends update
  } // ends isset

if(! $debug )
  {
    $path = "http://" . $_SERVER['HTTP_HOST'] . '/' . pathinfo($_SERVER['PHP_SELF'])['dirname'] . '/index.php';
    header("Location: " . $path);    
  }
?>