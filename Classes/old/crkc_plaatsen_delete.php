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
$o_tab_parser->nextRow(); // skip second row (onbekend)
$o_tab_parser->nextRow(); // skip third row (onbekend)
$o_tab_parser->nextRow(); // skip fourth row (onbekend)

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
		
	print "\nPROCESSING {$vn_idPlaatsen}\n";

	$t_place->setMode(ACCESS_WRITE);
	
	$va_right_id = $t_place->getPlaceIDsByName(strtolower($vs_Identificatie), null);
	
	$dimensions = sizeof($va_right_id);
		
	print "{$dimensions}\n";
	
	while($dimensions > 0) {	
		$vn_right_id = $va_right_id[0];
	
		$t_place->load($vn_right_id);
	
		if ($t_place->numErrors()) {
			print "\tERROR LOADING OBJECT {$vn_right_id}: ".join('; ', $t_place->getErrors())."\n";
			$dimensions = 0;
			continue;
		}

		$t_place->getPrimaryKey();
		
		$t_place->delete(true,array('hard' => true));
	
		if ($t_place->numErrors()) {
			print "\tERROR INSERTING {$vs_Identificatie_bis}: ".join('; ', $t_place->getErrors())."\n";
			$dimensions = 0;
			continue;
		} else {
			print "Delete succesvol\n";
		}
		
		$dimensions = $dimensions -1;
		$va_right_id = array_shift($va_right_id);
}
			
$vn_c++;
}
?>
