<?php

/*
 * Step 1: Initialisation
 */
 
set_time_limit(36000);
require_once("/www/libis/html/ca_crkc/setup.php");

require_once(__CA_LIB_DIR__.'/core/Db.php');
require_once(__CA_MODELS_DIR__.'/ca_locales.php');
require_once(__CA_MODELS_DIR__.'/ca_occurrences.php');
require_once(__CA_MODELS_DIR__.'/ca_occurrences_x_occurrences.php');

require_once(__CA_LIB_DIR__.'/core/Parsers/DelimitedDataParser.php');


$_ = new Zend_Translate('gettext', __CA_APP_DIR__.'/locale/en_US/messages.mo', 'nl_NL');

$t_locale = new ca_locales();
$pn_locale_id = $t_locale->loadLocaleByCode('nl_NL');

// get occurrence_relationship types
$t_rel_types = new ca_relationship_types();
$vn_occurrences_x_occurrences_id =	$t_rel_types->getRelationshipTypeID('ca_occurrences_x_occurrences', 'related');

print "occurrences_relationship: {$vn_occurrences_x_occurrences_id} \n";

//********************************************
//****  tentoonstelling_has_literatuur   *****
//********************************************

print "IMPORTING tentoonstelling_has_litteratuur\n";

/*
// * Step 2: Import
*/

 // want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t"); 

// Read csv; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_tentoonstelling_has_literatuur_bis.csv')) {
	die("Couldn't parse new_tentoonstelling_has_literatuur_bis.csv data\n");	
}
	
print "READING new_tentoonstelling_has_literatuur_bis.csv...\n";

$vn_c = 1;
	
$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------
while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_Tentoonstelling_id	=	$o_tab_parser->getRowValue(1);
	$vs_Identificatie	=	$o_tab_parser->getRowValue(2);
	$vn_Literatuur_id	=	$o_tab_parser->getRowvalue(3);
	$vs_Bib_Ref		=	$o_tab_parser->getRowvalue(4);
	$vs_Commentaar		=	$o_tab_parser->getRowvalue(5);
	
	print "{$vs_Identificatie} / {$vs_Bib_Ref} \n";
	
// ------------------------------------
// Waarden omvormen naar gepaste output
// ------------------------------------

//Uit het identificatie veld verwijderen we de gemeente
	
//----------------		
// Create object
//----------------

	// -------------------------------------------
	// Maak link tussen occurrences
	//--------------------------------------------
	
	$t_occurrence = new ca_occurrences();
	$t_occurrence->setMode(ACCESS_WRITE);
	
	$va_occurrence_left_id = ($t_occurrence->getOccurrenceIDsByName($vs_Identificatie, null));
	
	$dimension_left = sizeof($va_occurrence_left_id);
	
	print " {$dimension_left} \n";
	
	$t_occurrence->set('occurrence_id',$va_occurrence_left_id[0]);
	
	$va_occurrence_right_id =($t_occurrence->getOccurrenceIDsByName(substr($vs_Bib_Ref,0,99), null));
	
	$dimension_right = sizeof($va_occurrence_left_id);
	
	print " {$dimension_right} \n";

//function addRelationship($pm_rel_table_name_or_num, $pn_rel_id, $pm_type_id=null, $ps_effective_date=null, $ps_source_info=null, $ps_direction=null, $pn_rank=null)	

	if ($va_occurrence_left_id == NULL) {
		print " TENTOONSTELLING {$vs_Referentie} niet gevonden \n";
	}else {
		if ($va_occurrence_right_id == NULL) {
			print " PUBLICATIE {$vs_Bib_Ref} niet gevonden \n";
		}else {
			
			$t_occurrence->addRelationship('ca_occurrences', $va_occurrence_right_id[0], $vn_occurrences_x_occurrences_id);
	
			if ($t_occurrence->numErrors()) {
				print "ERROR LINKING tentoonstelling ".$vs_Identificatie." TO Publicatie ".$vs_Bib_Ref." : ".join('; ', $t_occurrence->getErrors())."\n";
				continue;
			}
		}
	}
	

	$va_occurrence_right_id = null;
	$va_occurrence_left_id = null;
	
	$vn_c++;
}

print "ENDED IMPORTING new_tentoonstelling_has_literatuur_bis.csv\n";

?>
