<?php

require_once("/www/libis/html/ca_crkc/setup.php");

require_once(__CA_LIB_DIR__.'/core/Db.php');
require_once(__CA_MODELS_DIR__.'/ca_locales.php');
require_once(__CA_MODELS_DIR__.'/ca_objects.php');
//require_once(__CA_MODELS_DIR__.'/ca_objects_x_objects.php');
//require_once(__CA_MODELS_DIR__.'/ca_sets.php');

require_once(__CA_LIB_DIR__.'/core/Parsers/DelimitedDataParser.php');
	
$_ = new Zend_Translate('gettext', __CA_APP_DIR__.'/locale/en_US/messages.mo', 'nl_NL');
	
$t_locale = new ca_locales();
$pn_locale_id = $t_locale->loadLocaleByCode('nl_NL');		// default locale_id
	
$t_list = new ca_lists(); 

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
//	$vs_Datum			=	$o_tab_parser->getRowvalue(27);
//	$vs_AssociatieenRelaties	=	$o_tab_parser->getRowvalue(28); // gerelateerde objecten
//	$vn_PubliceeropWeb		=	$o_tab_parser->getRowvalue(34); // set
		
	print "PROCESSING {$vn_Kunstvoorwerp_id}  / {$vs_crkcObjectnr} \n";
	print "Inventarisatiedatum_org: {$vs_InventarisatieDatum} \n";
	print "Datum_org: {$vs_Datum} \n";
	
//if (($vn_PubliceeropWeb == 1) or ($vn_VenAStatus == 1)) {
if ((strlen(trim($vs_InventarisatieDatum)) > 0 )) {
	
	if (trim($vs_InventarisatieDatum) == '0/0/0') {
		$vs_InventarisatieDatum = '';
	}
	if(strstr($vs_InventarisatieDatum,'00/')) {
		$vs_InventarisatieDatum = str_replace("00/", "01/",$vs_InventarisatieDatum);
	} 
	if(strstr($vs_InventarisatieDatum,'0/')) {
		$vs_InventarisatieDatum = str_replace("0/", "01/",$vs_InventarisatieDatum);
	} 
	
	print "Inventarisatiedatum {$vs_InventarisatieDatum} \n";

	if (check_date(trim($vs_InventarisatieDatum))) {
		continue;
	}else{
		
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

		// De verwerking: weet niet of dit zal werken ???

		
		if (check_date(trim($vs_InventarisatieDatum))) {
		
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

				print "Inventarisatiedatum {$vs_InventarisatieDatum} \n";
				$t_object->addAttribute(array(
					'locale_id'			=>	$pn_locale_id,
					'objectInventarisatieDatum'	=>	trim($vs_InventarisatieDatum)
				), 'objectInventarisatieDatum');

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
		
	}
}	

print "==={$vn_c}==============Einde verwerking kunstwerk {$vs_crkcObjectnr} ============================= \n \n";

$vn_c++;
		
}

print "gedaan ermee \n";

//==============================================================================

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
