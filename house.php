<?php
if(isset($_GET['id']) && is_int((int)$_GET['id']))
  {
    $dbconn = pg_connect("dbname=themove user=themove") or die('Could not connect: ' . pg_last_error());
    
    pg_prepare('get_house', 'SELECT * FROM features.house AS h WHERE id = $1');
    $result = pg_execute('get_house', array($_GET['id'])) or die('Query failed: ' . pg_last_error());
    
    if(1 <> pg_num_rows($result))
      {
	die("Not exactly one row!");
      }
    
    $line = pg_fetch_array($result, null, PGSQL_ASSOC);
  }
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-us">
  <head>

  </head>
  <body>    

  <form method="POST" action="houseMod.php">
  <input type="hidden" name="id" value="<?= isset($_GET['id'])?$_GET['id']:'' ?>" />
  <input type="hidden" name="action" value="<?= isset($_GET['id'])?'update':'insert' ?>" />
  <table>
  <thead>
  </thead>
  <tbody>
  <tr>
    <td>Type</td>
    <td>
      <select name="type">
  <option name="type" />
  <option value="detatched" <?= isset($line)&&("detatched"==$line['type'])?"selected":"" ?> >Detatched</option>
  <option value="townhome" <?= isset($line)&&("townhome"==$line['type'])?"selected":"" ?> >Townhome</option>
  <option value="apartment" <?= isset($line)&&("apartment"==$line['type'])?"selected":"" ?> >Apartment</option>
    
      </select>
    </td>
  </tr>
  <?php
  $field = array(
		  "address" => "Address",
		  "address2" => "Address 2",
		  "city" => "City",
		  "state" => "State (2 character)",
		  "zipcode" => "Zip Code",
		  "n_bed" => "# of Bedrooms",
		  "n_bath" => "# of Bathrooms",
		  "url" => "Link",
		  "base_rent" => "Base Rent",
		  "hoa_fee" => "HOA Fee",
		  "other_fee" => "Other Fee (Util., etc.)",
	      	 );
  foreach($field as $key => $val)
  {
  ?>
  <tr>
    <td><?= $val ?></td>
    <td><input type="text" name="<?= $key ?>" value="<?= isset($line)?$line[$key]:"" ?>" /></td>
  </tr>
      <?php } ?>
  <tr>
     <td>Utilities Included?</td>
     <td><input type="checkbox" name="util_incl" <?php if(isset($line) && $line['util_incl']) echo "checked"; ?> /></td>
  </tr>

  <tr>
     <td>Notes</td>
     <td><textarea name="notes" rows="8" cols="50">
	    <?= isset($line)?$line[$key]:"" ?>
	 </textarea>
     </td>
  </tr>
										       
  <tr>
     <td>Needs geocoding?</td>
     <td><input type="checkbox" name="needs_geocoding" <?php if(!isset($_GET['id']) || !is_int((int)$_GET['id'])) echo "checked"; ?> /></td>
  </tr>

  <tr>
    <td>Verdict</td>
    <td>
      <select name="verdict">
  <option />
  <option value="1" <?= isset($line)&&("1-Yes"==$line['verdict'])?"selected":"" ?> >Yes</option>
  <option value="2" <?= isset($line)&&("2-Maybe"==$line['verdict'])?"selected":"" ?> >Maybe</option>
  <option value="3" <?= isset($line)&&("3-Maybe Not"==$line['verdict'])?"selected":"" ?> >Maybe Not</option>
  <option value="4" <?= isset($line)&&("4-No"==$line['verdict'])?"selected":"" ?> >No</option>
    
      </select>
    </td>
  </tr>

     
      </tbody>
    </table>
    <input type="submit" name="submit" value="Save" />
  </form>
  
  </body>
</html>
