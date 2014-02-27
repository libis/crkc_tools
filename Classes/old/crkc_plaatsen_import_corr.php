<?php

//***************************
//****     PLAATSEN     *****
//***************************

/*
 * Step 1: Initialisation
 */
 
require_once("/www/libis/html/ca_crkc/setup.php");

require_once(__CA_LIB_DIR__.'/core/Db.php');
require_once(__CA_MODELS_DIR__.'/ca_locales.php');
require_once(__CA_MODELS_DIR__.'/ca_places.php');

require_once(__CA_LIB_DIR__.'/core/Parsers/DelimitedDataParser.php');

	
$_ = new Zend_Translate('gettext', __CA_APP_DIR__.'/locale/en_US/messages.mo', 'nl_NL');
	
$t_locale = new ca_locales();
$pn_locale_id = $t_locale->loadLocaleByCode('nl_NL');		// default locale_id
	
$t_list = new ca_lists();
$vn_hierarchy_id = $t_list->getItemIDFromList('place_hierarchies', 'places');

print "\n Hierarchy id : {$vn_hierarchy_id} \n";

$t_place = new ca_places();

$o_db = new Db();
$o_config = Configuration::load();

/*
 * Step 2: Import
 */

print "IMPORTING plaatsen \n";

 // want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t"); 

// Read csv; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/New_plaatsen_bis.csv')) {
	die("Couldn't parse New_plaatsen_bis.csv data\n");	
}

$vn_c = 1;
	
$o_tab_parser->nextRow(); // skip first row (headings)

//-------------------------
// waarden inlezen
//-------------------------
	
while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_idPlaatsen			=	$o_tab_parser->getRowValue(1);
	$vs_Identificatie		=	$o_tab_parser->getRowValue(2);			
	$vs_Omschrijving		=	$o_tab_parser->getRowValue(3);			
	$vs_Origine			=	$o_tab_parser->getRowValue(4);			
	$vs_Land			=	$o_tab_parser->getRowValue(5);			
	$vs_Postcode 			=	$o_tab_parser->getRowValue(6);			
	$vs_place_type			=	$o_tab_parser->getRowValue(7);			
	$vn_Field2			=	$o_tab_parser->getRowValue(8);			
		
	print "\nPROCESSING {$vs_Identificatie}\n";

// ------------------------------------
// Waarden omvormen naar gepaste output
// ------------------------------------

	if (trim($vs_place_type) == "historischePlaats" ) {
		$vs_place_type = "other";
	}

	$vn_place_id	=	$t_list->getItemIDFromList('place_types', trim($vs_place_type));
	
	$idno = "plaats_".$vn_idPlaatsen;
	
	print " Place_type : {$vs_place_type} / {$vn_place_id} \n ";

	$qr = "UPDATE ca_places SET type_id = $vn_place_id WHERE idno = '$idno' ";
	
	print "query: {$qr}\n";
				
	$result = $o_db->query($qr);
	
	if ($o_db->numErrors()) {
		//$o_db->getTransaction()->rollback();
		print "ERROR UPDATING ?\n";
		continue;
	} else {
		//$o_db->getTransaction()->commit();
		print "commit succesvol\n";
	}			
	
$vn_c++;
	
}
?>
