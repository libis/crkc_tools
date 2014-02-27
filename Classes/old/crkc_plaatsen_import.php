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

//place_type: lijstwaarden bepalen

		if ($vn_Field2 == 0 ) {
			$vn_place_id	=	$t_list->getItemIDFromList('place_types', $vs_place_type);
		} elseif ($vn_Field2  == 1 ) {
			$vn_place_id	=	$t_list->getItemIDFromList('place_types', $vs_place_type);
		} elseif ($vn_Field2  == 2 ) {
			$vn_place_id	=	$t_list->getItemIDFromList('place_types', $vs_place_type);
		} elseif ($vn_Field2  == 3 ) {
			$vn_place_id	=	$t_list->getItemIDFromList('place_types', $vs_place_type);
		} elseif ($vn_Field2  == 4 ) {
			$vn_place_id	=	$t_list->getItemIDFromList('place_types', $vs_place_type);
		} elseif ($vn_Field2  == 5 ) {
			$vn_place_id	=	$t_list->getItemIDFromList('place_types', $vs_place_type);
		} elseif ($vn_Field2  == 6 ) {
			$vn_place_id	=	$t_list->getItemIDFromList('place_types', $vs_place_type);
		} 	
		
//Parent-id bepalen		
		if (trim($vs_Land) == "") {
			// invoegen onder de root
			$vn_root = null;
		} else {
			// invoegen onder opgegeven land/stad ...)
			$va_root = $t_place -> getPlaceIDsByName($vs_Land);
//			$vn_root = $t_place->getRootIDForHierarchy($va_root[0]);
		}


//Wat met de postcode???

		if (trim($vs_place_type) == "city" and trim($vs_Postcode) <> "") {
				$vs_Identificatie_bis = $vs_Identificatie." - ".$vs_Postcode;
		} else {
				$vs_Identificatie_bis = $vs_Identificatie;
		}
		
		print " Place_type : {$vn_place_id} - Parent_id : {$va_root[0]}\n";
//----------------		
// Create object
//----------------

 print "creating term ".$vs_Identificatie." and adding labels for term \n\n";

 
	
	$t_place->setMode(ACCESS_WRITE);
	$t_place->set('parent_id', $va_root[0]);
	$t_place->set('locale_id', $pn_locale_id);
	$t_place->set('type_id', $vn_place_id);
	$t_place->set('source_id', NULL);
	$t_place->set('hierarchy_id', $vn_hierarchy_id);
	$t_place->set('idno', "plaats_".$vn_idPlaatsen);

	$t_place->addAttribute(array(
		'locale_id'		=>	$pn_locale_id,
		'name_singular'		=>	$vs_Identificatie_bis,
		'name_plural'		=>	$vs_Identificatie_bis
	), 'name');

		// insert the object
		$t_place->insert();

		if ($t_place->numErrors()) {
			print "\tERROR INSERTING {$vs_Identificatie_bis}: ".join('; ', $t_place->getErrors())."\n";
			continue;
		}

		// --------------------------------------------------------------
		// Set a preferred label for the object
		// --------------------------------------------------------------
		
		$t_place->addLabel(
			array('name' => ($vs_Identificatie)),
			$pn_locale_id, null, true
		);
		
		if ($t_place->numErrors()) {
			print "\tERROR ADD LABEL TO {$vs_Identificatie}: ".join('; ', $t_place->getErrors())."\n";
			continue;
		}
			
		$vn_c++;
	}
?>
