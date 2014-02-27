<?php
define('__CA_DONT_DO_SEARCH_INDEXING__',true);
/*
 * Step 1: Initialisation
 */
set_time_limit(36000);
require_once("/www/libis/html/ca_crkc/setup.php");

require_once(__CA_LIB_DIR__.'/core/Db.php');
require_once(__CA_MODELS_DIR__.'/ca_locales.php');
require_once(__CA_MODELS_DIR__.'/ca_objects.php');

require_once(__CA_LIB_DIR__.'/core/Parsers/DelimitedDataParser.php');


$_ = new Zend_Translate('gettext', __CA_APP_DIR__.'/locale/en_US/messages.mo', 'nl_NL');

$t_locale = new ca_locales();
$pn_locale_id = $t_locale->loadLocaleByCode('nl_NL');

$t_list = new ca_lists();

$t_object = new ca_objects();
$t_object->setMode(ACCESS_WRITE);

//***************************************
//**** KUNSTVOORWERP_HAS_TECHNIEK  *****
//***************************************

print "IMPORTING kunstvoorwerp has techniek \n";

/****************
 * Step 2: Import
 ****************/

 // want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t"); 

// Read csv; line by line till end of file
// and put information in an array
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_thesaurus.csv')) {
	die("Couldn't parse new_thesaurus.csv data\n");	
}
$va_Thesaurus = array();
	
$vn_c = 1;

$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------
while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_Id		=	$o_tab_parser->getRowValue(1);
	$vs_Term	=	$o_tab_parser->getRowValue(2);
	$vs_Type	=	$o_tab_parser->getRowValue(3);
	
	$va_Thesaurus[$vn_Id] = array($vs_Term, $vs_Type);
	
	$vn_c++;
}

//print_r($va_Stijlen);

$vn_c = 1;
	
// Read csv; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_kunstvoorwerp_has_technieken.csv')) {
	die("Couldn't parse kunstvoorwerp_has_technieken.csv data\n");	
}
	
print "READING new_kunstvoorwerp_has_technieken.csv...\n";

$vn_c = 1;

$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------
while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_Kunstvoorwerp_id	=	$o_tab_parser->getRowValue(1);
	$vs_Commentaar		=	$o_tab_parser->getRowValue(3);
	$vn_Term_id		=	$o_tab_parser->getRowValue(4);

	print "creating techniek voor kunstvoorwerp ".$vn_Kunstvoorwerp_id.".".$vn_Term_id."  \n";

	// ------------------------------------
	// Waarden omvormen naar gepaste output
	// ------------------------------------

	$va_Term_waarde = $va_Thesaurus[$vn_Term_id];

	$vs_Term_Text = $va_Term_waarde[0];
	$vs_Term_Type = $va_Term_waarde[1];
	
	$vn_Term_waarde = $t_list->getItemIDFromListByLabel('move_techniek', trim($va_Term_waarde[0]));
	print "waarde_id {$vn_Term_waarde} \n" ;
	if ($vn_Term_waarde) {
		
		print " gevonden: techniek: {$vn_Term_id} / {$va_Term_waarde[0]} / {$va_Term_waarde[1]} / {$vn_Term_waarde} \n";
		
		//--------------------------------		
		// Primary key van Object opzoeken
		//--------------------------------
               
		$va_Kunstvoorwerp_keys = ($t_object->getObjectIDsByidnoPart(("%KV_".$vn_Kunstvoorwerp_id.")"),null) );
	
		$dimensions = sizeof($va_Kunstvoorwerp_keys[0]);
		
		print "{$dimensions}\n";
		
		if ($dimensions > 0) {
	
			$vn_Kunstvoorwerp_key = $va_Kunstvoorwerp_keys[0];
	
			print "Primary Kunstvoorwerp-key: ".$vn_Kunstvoorwerp_key." \n";
	
			$t_object->load($vn_Kunstvoorwerp_key);
	
			// Object in geheugen laden

			if ($t_object->numErrors()) {
				print "\tERROR LOADING OBJECT {$vn_Kunstvoorwerp_id}: ".join('; ', $t_object->getErrors())."\n";
				continue;
			} else {
				
				print "object in geheugen geladen \n";

				// Primary key opvragen
		
				$t_object->getPrimaryKey();

				// Nieuwe Waarden mappen      

				$t_object->addAttribute(array(
					'locale_id'			=>	$pn_locale_id,
					'objectTechniekType'		=>	$vn_Term_waarde,
					'objectTechniekOpmerking'	=>	$vs_Commentaar
				), 'objectTechniekInfo');
		
				// Update record
				$t_object->update();
		
				if ($t_object->numErrors()) {
					print "\tERROR UPDATING OBJECT {$vn_Kunstvoorwerp_key}: ".join('; ', $t_object->getErrors())."\n";
					continue;
				} else {
					print "update succesvol\n";
				}
			}

		} else {
			print "Kunstvoorwerp bestaat (nog) NIET \n";
			
		}

	} else {
		print " techniek {$va_Term_waarde[0]} voor {$vn_Kunstvoorwerp_id} niet gevonden in move_techniek  \n";
		print " plaatsen gezochte tekst in opmerkingen veld \n";
		$vn_Term_waarde = $t_list->getItemIDFromList('move_techniek', 'blank');
		print " waarde_id {$vn_Term_waarde} \n ";
		
		if (strlen(trim($vs_Commentaar))> 0 and (trim($vs_Commentaar) != "/")){
			
			$vs_Commentaar = $va_Term_waarde[0]." - ".$vs_Commentaar;
		
		} else {
			
			$vs_Commentaar = $va_Term_waarde[0];
		}
			
		$vn_Kunstvoorwerp_key = $va_Kunstvoorwerp_keys[0];

		print "Primary Kunstvoorwerp-key: ".$vn_Kunstvoorwerp_key." \n";

		$t_object->load($vn_Kunstvoorwerp_key);
		
		// Object in geheugen laden

		if ($t_object->numErrors()) {
			print "\tERROR LOADING OBJECT {$vn_Kunstvoorwerp_id}: ".join('; ', $t_object->getErrors())."\n";
			continue;
		} else {
			
			print "object in geheugen geladen \n";
		
			// Primary key opvragen

			$t_object->getPrimaryKey();

			// Nieuwe Waarden mappen      

			$t_object->addAttribute(array(
				'locale_id'			=>	$pn_locale_id,
				'objectTechniekType'		=>	$vn_Term_waarde,
				'objectTechniekOpmerking'	=>	$vs_Commentaar
			), 'objectTechniekInfo');

			// Update record			
			$t_object->update();

			if ($t_object->numErrors()) {
				print "\tERROR UPDATING OBJECT {$vn_Kunstvoorwerp_key}: ".join('; ', $t_object->getErrors())."\n";
				continue;
			} else {
				print "update succesvol\n";
			}
		}
	}

print "==={$vn_c}==========volgend record============== \n\n";
	
$vn_c++;

}

print "END IMPORTING kunstvoorwerp_has_technieken \n";



?>
