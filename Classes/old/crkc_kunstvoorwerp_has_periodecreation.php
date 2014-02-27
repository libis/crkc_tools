<?php

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

//*********************************************
//**** KUNSTVOORWERP_HAS_PERIODECREATION  *****
//*********************************************

//print "IMPORTING kunstvoorwerp has periodecreation \n";

/****************
 * Step 2: Import
 ****************/

 // want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t"); 

$vn_c = 1;

$va_Periode_Creation = array();

// Read csv; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_periodecreation.csv')) {
	die("Couldn't parse periodecreation.csv data\n");
}
	
print "READING new_periodecreation.csv...\n";

$vn_c = 1;

$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------
while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_Id			=	$o_tab_parser->getRowValue(1);
	$vs_Identificatie	=	$o_tab_parser->getRowValue(2);
	$vs_Omschrijving	=	$o_tab_parser->getRowValue(3);

	$va_Periode_Creation[$vn_Id] = array($vs_Identificatie, $vs_Omschrijving);
	
	$vn_c++;
}

// Read csv; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_kunstvoorwerp_has_periodecreation.csv')) {
	die("Couldn't parse kunstvoorwerp_has_periodecreation.csv data\n");	
}
	
print "READING new_kunstvoorwerp_has_periodecreation.csv...\n";

$vn_c = 1;

$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------
while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_Kunstvoorwerp_id	=	$o_tab_parser->getRowValue(1);
	$vn_Term_id		=	$o_tab_parser->getRowValue(2);
	$vs_Commentaar		=	$o_tab_parser->getRowValue(3);
	
print "creating materiaal voor kunstvoorwerp ".$vn_Kunstvoorwerp_id.".".$vn_Term_id."  \n";

// ------------------------------------
// Waarden omvormen naar gepaste output
// ------------------------------------

$va_Periode = $va_Periode_Creation[$vn_Term_id];

$vs_Term_Identificatie = $va_Periode[0];
$vs_Term_Omschrijving = $va_Periode[1];

if ($vs_Term_Identificatie) {
	
//----------------		
// Create object
//----------------

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
			'locale_id'		=>	$pn_locale_id,
			'objectDatum'		=>	$vs_Term_Identificatie,
			'objectDatumOpmerking'	=>	$vs_Term_Omschrijving."  ".$vs_Commentaar
		), 'objectDatumInfo');
		
		$t_object->update();
		
		if ($t_object->numErrors()) {
			print "\tERROR UPDATING OBJECT {$vn_Kunstvoorwerp_key}: ".join('; ', $t_object->getErrors())."\n";
			continue;
		} else {
			print "update succesvol\n";
		}
	
	
	} else {
		print "Kunstvoorwerp bestaat (nog) NIET \n";
	}
	
} else {
	print " periodecreation niet gevonden -  skipping record \n";

}


print "==={$vn_c}==========volgend record============== \n\n";
	
$vn_c++;

}

print "ENDED IMPORTING kunstvoorwerp_has_periodecreation \n";

?>
