<?php

require_once("/www/libis/html/ca_crkc/setup.php");

require_once(__CA_LIB_DIR__.'/core/Db.php');
require_once(__CA_MODELS_DIR__.'/ca_locales.php');
require_once(__CA_MODELS_DIR__.'/ca_objects.php');
require_once(__CA_MODELS_DIR__.'/ca_objects_x_objects.php');
require_once(__CA_MODELS_DIR__.'/ca_sets.php');

require_once(__CA_LIB_DIR__.'/core/Parsers/DelimitedDataParser.php');
	
$_ = new Zend_Translate('gettext', __CA_APP_DIR__.'/locale/en_US/messages.mo', 'nl_NL');
	
$t_locale = new ca_locales();
$pn_locale_id = $t_locale->loadLocaleByCode('nl_NL');		// default locale_id
	
$t_list = new ca_lists(); 
$vn_Set_Type_id	= $t_list->getItemIDFromList('set_types', 'public_presentation');

// relaties
$t_rel_types = new ca_relationship_types();

// relatie object-object
$vn_objects_x_objects_id = $t_rel_types->getRelationshipTypeID('ca_objects_x_objects', 'related');
print "relatie object-object: ".$vn_objects_x_objects_id."\n";

//
// Step 1: Aanmaken Sets 
//
/*
$t_web = new ca_sets();
if (!$t_web->load(array('set_code' => 'PubliceerOpWeb'))) {
	$t_web->setMode(ACCESS_WRITE);
	$t_web->set('set_code', 'PubliceerOpWeb');
	$t_web->set('name_singular', 'Publiceren op het web');
	$t_web->set('name_plural', 'Publiceer op het web');
	$t_web->set('parent_id', null);
	$t_web->set('table_num', 57);
	$t_web->set('type_id', $vn_Set_Type_id );
	$t_web->set('status', 4);
	$t_web->set('access', 1);
	$t_web->set('user_id', 1);
	$t_web->insert();

	if ($t_web->numErrors()) {
		print "ERROR: couldn't create ca_sets row for Publiceer op het web: ".join('; ', $t_web->getErrors())."\n";
		die;
	}

	$t_web->addLabel(array('name' => 'Publiceer op het web'), $pn_locale_id, null, true);
}
$t_web->setMode(ACCESS_WRITE);
$vn_web_id = $t_web->getPrimaryKey();


$t_VenA = new ca_sets();
if (!$t_VenA->load(array('set_code' => 'VraagenAanbod'))) {
	$t_VenA->setMode(ACCESS_WRITE);
	$t_VenA->set('set_code', 'VraagenAanbod');
	$t_VenA->set('name_singular', 'Vraag en Aanbod ');
	$t_VenA->set('name_plural', 'Vraag en Aanbod');
	$t_VenA->set('parent_id', null);
	$t_VenA->set('table_num', 57);
	$t_VenA->set('type_id', $vn_Set_Type_id );
	$t_VenA->set('status', 4);
	$t_VenA->set('access', 1);
	$t_VenA->set('user_id', 1);
	$t_VenA->insert();

	if ($t_VenA->numErrors()) {
		print "ERROR: couldn't create ca_sets row for Vraag en Aanbod: ".join('; ', $t_VenA->getErrors())."\n";
		die;
	}

	$t_VenA->addLabel(array('name' => 'Vraag en Aanbod'), $pn_en_locale_id, null, true);
}
$t_VenA->setMode(ACCESS_WRITE);
$vn_VenA_id = $t_VenA->getPrimaryKey();
*/
/*
$t_web = new ca_sets();
$t_web->load(array('set_code' => 'PubliceerOpWeb'));
$t_web->setMode(ACCESS_WRITE);
$vn_web_id = $t_web->getPrimaryKey();
print "PubliceerOpWeb: {$vn_web_id} \n";

$t_VenA = new ca_sets();
$t_VenA->load(array('set_code' => 'VraagenAanbod'));
$t_VenA->setMode(ACCESS_WRITE);
$vn_VenA_id = $t_VenA->getPrimaryKey();
print "Vraag & Aanbod: {$vn_VenA_id} \n";
*/

//
// Step 2: Import
//

// want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t"); 

print "IMPORTING kunstvoorwerpen \n";
	
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/ani_kunstvoorwerp.csv')) {
	die("Couldn't parse Kunstvoorwerpen data\n");	
}
	
$t_object = new ca_objects();
$t_object->setMode(ACCESS_WRITE);

$vn_c = 1;
	
$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------
while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_Kunstvoorwerp_id		=	$o_tab_parser->getRowValue(1);
//	$vn_VenAStatus			=	$o_tab_parser->getRowValue(2); // voor aanmaken set			
	$vs_crkcObjectnr		=	$o_tab_parser->getRowValue(9);
	$vs_InventarisatieDatum		=	$o_tab_parser->getRowvalue(18);
	$vs_Datum			=	$o_tab_parser->getRowvalue(27);
//	$vs_AssociatieenRelaties	=	$o_tab_parser->getRowvalue(28); // gerelateerde objecten
//	$vn_PubliceeropWeb		=	$o_tab_parser->getRowvalue(34); // set
		
	print "PROCESSING {$vn_Kunstvoorwerp_id}  / {$vs_crkcObjectnr} \n";
	print "Inventarisatiedatum_org: {$vs_InventarisatieDatum} \n";
	print "Datum_org: {$vs_Datum} \n";
	
//if (($vn_PubliceeropWeb == 1) or ($vn_VenAStatus == 1)) {
if ((strlen(trim($vs_Datum)) > 0 ) or (strlen(trim($vs_InventarisatieDatum)) > 0 )) {

//opzoeken object_id
	$va_Kunstvoorwerp_keys = ($t_object->getObjectIDsByidnoPart(("%KV_".$vn_Kunstvoorwerp_id.")"),null) );
	$dimensions = sizeof($va_Kunstvoorwerp_keys);
	print "{$dimensions}\n";
		
	if ($dimensions > 0) {
	
		$vn_Kunstvoorwerp_key = $va_Kunstvoorwerp_keys[0];
		print "Primary Kunstvoorwerp-key: ".$vn_Kunstvoorwerp_key." \n";
		$t_object->load($vn_Kunstvoorwerp_key);
// Primary key opvragen
		$vn_left_id = $t_object->getPrimaryKey();

// De sets aanmaken
/*
		if (($vn_PubliceeropWeb == 1)) {
			$vn_web_id = $t_web->getPrimaryKey();
			$vn_web_add_id =$t_web->addItem($vn_left_id);
			$t_web->update();
			if ($t_web->numErrors()) {
				print "ERROR UPDATING PUBLICEER OP WEB SET: {$vn_Kunstvoorwerp_id}: ".join('; ', $t_web->getErrors())."\n";
				continue;
			} else {
				print "update Publiceer op web set succesvol\n";
			}		
		}
		
		if (($vn_VenAStatus == 1)) {
			$vn_VenA_id = $t_VenA->getPrimaryKey();
			$vn_VenA_add_id =$t_VenA->addItem($vn_left_id);
			$t_VenA->update();
			if ($t_VenA->numErrors()) {
				print "ERROR UPDATING VRAAG & AANBOD SET: {$vn_Kunstvoorwerp_id}: ".join('; ', $t_VenA->getErrors())."\n";
				continue;
			} else {
				print "update Publiceer op web set succesvol\n";
			}		
		}

// Links naar ander objecten		
		
			
			$t_object->addAttribute(array(
				'locale_id'				=>	$pn_locale_id,
				'gerelateerdObjectOpmerking'		=>	$vs_AssociatieenRelaties
			), 'gerelateerdObjectOpmerking');
			
			$t_object->update();
	
			if ($t_object->numErrors()) {
				print "\tERROR UPDATING OBJECT {$vn_Kunstvoorwerp_id}: ".join('; ', $t_object->getErrors())."\n";
				continue;
			} else {
				print "update succesvol\n";
			}
			
			// nu gaan we proberen de bruikbare object-ids op te zoeken en links te leggen
			
			$aantal_crkc = 0;
			$aantal_pov = 0;
			$aantal_pa = 0;
			$aantal_pwv = 0;
			
			$pattern = '/(CRKC\.)(....)(\.)(....)/';
			$aantal_crkc = custom_preg_match_all($pattern, $vs_AssociatieenRelaties);

			$pattern = '/(POV\.)(.*)(\.)(.*)/';
			$aantal_pov = custom_preg_match_all($pattern, $vs_AssociatieenRelaties);
			
			$pattern = '/(PA\.)(.*)(\.)(.*)/';
			$aantal_pa = custom_preg_match_all($pattern, $vs_AssociatieenRelaties);
			
			$pattern = '/(PWV\.)(.*)(\.)(.*)/';
			$aantal_pwv = custom_preg_match_all($pattern, $vs_AssociatieenRelaties);
			
			print "aantallen: crkc {$aantal_crkc} / pov {$aantal_pov} / pa {$aantal_pa} / pwv {$aantal_pwv} \n";
*/		
		
// De datum-velden - we vangen een aantal veel voorkomende fouten op
//Inventarisatiedatum

		if (trim($vs_InventarisatieDatum) == '0/0/0') {
			$vs_InventarisatieDatum = '';
		}
		
		if(strstr($vs_InventarisatieDatum,'00/')) {
			$vs_InventarisatieDatum = str_replace("00/", "01/",$vs_InventarisatieDatum);
		} 
		if(strlen($vs_InventarisatieDatum) == 9) {
			$pattern = '/\.|\/|-/i';    // . or / or -
			preg_match($pattern, $vs_InventarisatieDatum, $char);
		 
			$array = preg_split($pattern, $vs_InventarisatieDatum, -1, PREG_SPLIT_NO_EMPTY); 
		
			if(strlen($array[0]) == 1) {
				$day = "0".$array[0];
				$month = $array[1];
				$year = $array[2];
			}
			if(strlen($array[1]) == 1) {
				$day = $array[0];
				$month = "0".$array[1];				
				$year = $array[2];
			}
			$vs_InventarisatieDatum = $day."/".$month."/".$year;
		}
		if(strlen($vs_InventarisatieDatum) == 8) {
			$pattern = '/\.|\/|-/i';    // . or / or -
			preg_match($pattern, $vs_InventarisatieDatum, $char);
		 
			$array = preg_split($pattern, $vs_InventarisatieDatum, -1, PREG_SPLIT_NO_EMPTY); 
		
			if(strlen($array[0]) == 1 and strlen($array[1]) == 1) {
				$day = "0".$array[0];
				$month = "0".$array[1];
				$year = $array[2];
			}
			$vs_InventarisatieDatum = $day."/".$month."/".$year;
		}
		print "Inventarisatiedatum_new: {$vs_InventarisatieDatum} \n";

//Datum
	
		if (trim($vs_Datum) == '0/0/0') {
			$vs_Datum = '';
		}
		
		if(strstr($vs_Datum,'00/')) {
			$vs_Datum = str_replace("00/", "01/",$vs_Datum);
		} 
				
		

		
		// De verwerking: weet niet of dit zal werken ???
	
		if (strlen(trim($vs_Datum)) > 0 ) { 
			if (check_date(trim($vs_Datum))) {
				print "datum {$vs_Datum} \n";
				$t_object->addAttribute(array(
					'locale_id'		=>	$pn_locale_id,
					'objectDatum'		=>	$vs_Datum
		//			'objectDatumOpmerking'	=>	$vs_Datum
				), 'objectDatumInfo');
			} else {  // geen geldige datum -> in opm
				$t_object->addAttribute(array(
					'locale_id'		=>	$pn_locale_id,
					'objectDatum'		=>	null,
					'objectDatumOpmerking'	=>	$vs_Datum
				), 'objectDatumInfo');
			}
		}
		if (strlen(trim($vs_InventarisatieDatum)) > 0 ) {
			if (check_date(trim($vs_InventarisatieDatum))) {
				print "Inventarisatiedatum {$vs_InventarisatieDatum} \n";
				$t_object->addAttribute(array(
					'locale_id'			=>	$pn_locale_id,
					'objectInventarisatieDatum'	=>	trim($vs_InventarisatieDatum)
				), 'objectInventarisatieDatum');
			}
		}
		
		$t_object->update();
		
		if ($t_object->numErrors()) {
			print "ERROR UPDATING DATA {$vn_Kunstvoorwerp_id}: ".join('; ', $t_object->getErrors())."\n";
			continue;
		} else {
			print "insert vs_datum succesvol  \n";
		}

		
	} else {	
		print "ERROR: object {$vn_Kunstvoorwerp_id} / {$vs_crkcObjectnr} niet gevonden \n";
	}
}

print "==={$vn_c}==============Einde verwerking kunstwerk {$vs_crkcObjectnr} ============================= \n \n";

$vn_c++;
		
}

print "gedaan ermee \n";

//==============================================================================

function custom_preg_match_all($pattern,$subject)
{

global $t_object;
global $i;
global $vn_objects_x_objects_id;

	$offset = 0;
	$match_count = 0;
	while(preg_match($pattern, $subject, $matches, PREG_OFFSET_CAPTURE, $offset))
	{
		// Increment counter
		$match_count++;
     
		// Get byte offset and byte length (assuming single byte encoded)
		$match_start = $matches[0][1];
		$match_length = strlen($matches[0][0]);
 
		// (Optional) Transform $matches to the format it is usually set as (without PREG_OFFSET_CAPTURE set)
		//foreach($matches as $k => $match) $newmatches[$k] = $match[0];
		//$matches = $new_matches;
     
		// Your code here
		
		echo "Match number $match_count, at byte offset $match_start, $match_length bytes long: ".$matches[0]."\r\n";
		print_r($matches);
		$object = $matches[0][0];
		print "gevonden string {$object} \n";
		
		$va_Relaties_keys = ($t_object->getObjectIDsByidnoPart(($object."%"),null) );
		$dim = sizeof($va_Relaties_keys);
		print "aantal objecten {$dim}\n";
		
		if ($dim > 0) {

			$vn_Relaties_key = $va_Relaties_keys[0];
			$t_object->addRelationship('ca_objects', $vn_Relaties_key, $vn_objects_x_objects_id);
			
			print "done {$object} \n";

			if ($t_object->numErrors()) {
				print "ERROR LINKING object and object : ".join('; ', $t_object->getErrors())."\n";
			} else {
				print "link object-object succesvol\n";
			}	
			
		} else {
			print "ERROR: object {$object} niet gevonden \n";
			
		}
             
		// Update offset to the end of the match
		$offset = $match_start + $match_length;
	}
 
	return $match_count;
}

function check_date($date) {
	if(strlen($date) == 10) {
		$pattern = '/\.|\/|-/i';    // . or / or -
		preg_match($pattern, $date, $char);
		 
		$array = preg_split($pattern, $date, -1, PREG_SPLIT_NO_EMPTY); 
		
		if(strlen($array[2]) == 4) {
		// dd.mm.yyyy || dd-mm-yyyy
			if($char[0] == "."|| $char[0] == "-") {
				$month = $array[1];
				$day = $array[0];
				$year = $array[2];
			 }
			 // mm/dd/yyyy    # Common U.S. writing
			 if($char[0] == "/") {
				$month = $array[0];
				$day = $array[1];
				$year = $array[2];
			 }
		}
		// yyyy-mm-dd    # iso 8601
		if(strlen($array[0]) == 4 && $char[0] == "-") {
			$month = $array[1];
			$day = $array[2];
			$year = $array[0];
		}
		if(checkdate($month, $day, $year)) {    //Validate Gregorian date
			return TRUE;
			 
		} else {
			return FALSE;
		}
	}else{
		return FALSE;    // more or less 10 chars
	}
 }
 
?>
