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

//**************************************************
//**** KUNSTVOORWERP_HAS_TREFWOORDICONOGRAFIE  *****
//**************************************************

print "IMPORTING kunstvoorwerp has trefwoord iconografie \n";

/****************
 * Step 2: Import
 ****************/

 // want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t"); 
/*Ik denk dat we dit niet nodig hebben !!!!
// Read csv; line by line till end of file
// and put information in an array
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_trefwoord.csv')) {
	die("Couldn't parse new_trefwoord.csv data\n");	
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
//	$vs_Type	=	$o_tab_parser->getRowValue(2);
	
	$va_Thesaurus[$vn_Id] = $vs_Term;
	
	$vn_c++;
}
*/
//print_r($va_Stijlen);

$vn_c = 1;
	
// Read csv; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_kunstvoorwerp_has_trefwoordiconografie.csv')) {
	die("Couldn't parse kunstvoorwerp_has_trefwoordiconografie.csv data\n");	
}
	
print "READING new_kunstvoorwerp_has_trefwoordiconografie.csv...\n";

$vn_c = 1;

$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------
while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_Kunstvoorwerp_id	=	$o_tab_parser->getRowValue(1);
	$vn_Term_id		=	$o_tab_parser->getRowValue(2);
	

	print "creating trefwoord-iconografie voor kunstvoorwerp ".$vn_Kunstvoorwerp_id.".".$vn_Term_id."  \n";

// ------------------------------------
// Waarden omvormen naar gepaste output
// ------------------------------------

//$vs_Term_waarde = $va_Thesaurus[$vn_Term_id];

	if ($vn_Term_waarde = $t_list->getItemIDFromList('iconografie_lijst', $vn_Term_id)) {
		Print " Iconografisch Trefwoord: {$vn_Term_id} / {$vn_Term_waarde} \n";
	
	//----------------		
	// Create object
	//----------------
	
		$t_object = new ca_objects();
		$t_object->setMode(ACCESS_WRITE);
		       
		$va_Kunstvoorwerp_keys = ($t_object->getObjectIDsByidnoPart(("%KV_".$vn_Kunstvoorwerp_id.")"),null) );
		
		$dimensions = sizeof($va_Kunstvoorwerp_keys[0]);
			
		print "{$dimensions}\n";
			
		if ($dimensions > 0) {
		
			$vn_Kunstvoorwerp_key = $va_Kunstvoorwerp_keys[0];
			
			print "Primary Kunstvoorwerp-key: ".$vn_Kunstvoorwerp_key." \n";
			
			$t_object->load($vn_Kunstvoorwerp_key);
			
			if ($t_object->numErrors()) {
				print "\tERROR LOADING OBJECT {$vn_Kunstvoorwerp_id}: ".join('; ', $t_object->getErrors())."\n";
				continue;
			}
		
			$t_object->getPrimaryKey();
			
			//-------------------------
			// Waarden mappen      
			//-------------------------
			
			$t_object->addAttribute(array(
				'locale_id'			=>	$pn_locale_id,
				'objectIconografieType'		=>	$vn_Term_waarde
			), 'objectIconografieType');
		
			$t_object->update();
		
			if ($t_object->numErrors()) {
				print "\tERROR UPDATING OBJECT {$vn_Kunstvoorwerp_id}: ".join('; ', $t_object->getErrors())."\n";
				continue;
			} else {
				print "update succesvol\n";
			}
		
		
		} else {
			print "Kunstvoorwerp bestaat (nog) NIET \n";
		}
	
	} else {
		
		print " Trefwoord  {$vn_Term_waarde} voor {$vn_Kunstvoorwerp_id} niet gevonden in iconografie_lijst -  skipping record \n";
		
	}


	print "==={$vn_c}==========volgend record============== \n\n";
	
	$vn_c++;

}

print "END IMPORTING kunstvoorwerp_has_trefwoordiconografie \n";

?>
