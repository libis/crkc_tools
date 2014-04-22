<?php

/*
 * Step 1: Initialisation
 */
 
set_time_limit(36000);
require_once("/www/libis/html/ca_crkc/setup.php");

require_once(__CA_LIB_DIR__.'/core/Db.php');
require_once(__CA_MODELS_DIR__.'/ca_locales.php');
require_once(__CA_MODELS_DIR__.'/ca_occurrences.php');

require_once(__CA_LIB_DIR__.'/core/Parsers/DelimitedDataParser.php');


$_ = new Zend_Translate('gettext', __CA_APP_DIR__.'/locale/en_US/messages.mo', 'nl_NL');

$t_locale = new ca_locales();
$pn_locale_id = $t_locale->loadLocaleByCode('nl_NL');

$t_list = new ca_lists();
//occurrence_types
$pn_occurrence_type_id =	$t_list->getItemIDFromList('occurrence_types', 'reference');



//************************************
//****       PUBLICATIES         *****
//************************************

print "IMPORTING literatuur\n";

//
// Step 2: Import
//

 // want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t"); 

// Read csv; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_litterateur.csv')) {
	die("Couldn't parse new_literatuur.csv data\n");	
}
	
print "READING new_literatuur.csv...\n";

$vn_c = 1;
	
$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------

while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_Literatuur_id	=	$o_tab_parser->getRowValue(1);
	$vs_Korte_Ref		=	$o_tab_parser->getRowValue(2);
	$vs_Bib_Ref		=	$o_tab_parser->getRowvalue(3);
	$vn_Moderate		= 	$o_tab_parser->getRowvalue(5);
	$vs_Origine		=	$o_tab_parser->getRowvalue(8);
	
print "\n Creating  ".$vn_Literatuur_id.".".$vs_Korte_Ref." and adding labels for publicatie. \n"; 

// ------------------------------------
// Waarden omvormen naar gepaste output
// ------------------------------------


if (trim($vs_Origine) == 'CRKC') {
	$pn_occurrence_source_id =	$t_list->getItemIDFromList('occurrence_sources', 'os_crkc');
} elseif (trim($vs_Origine) == 'POV') {
	$pn_occurrence_source_id =	$t_list->getItemIDFromList('occurrence_sources', 'os_pov');
}elseif (trim($vs_Origine) == 'PA') {
	$pn_occurrence_source_id =	$t_list->getItemIDFromList('occurrence_sources', 'os_pa');
} else {
	$pn_occurrence_source_id =	null;
	print "   - WARNING: geen Bron opgegeven voor {$vs_KorteRef}, verbeter indien nodig \n";
}

//----------------		
// Create object
//----------------
// Korte beschrijving komt in Idno en Bibliografische beschrijving komt in preferred label

	$t_occur = new ca_occurrences();
	$t_occur->setMode(ACCESS_WRITE);
	$t_occur->set('type_id', $pn_occurrence_type_id);
	$t_occur->set('source_id', $pn_occurrence_source_id);
	$t_occur->set('idno', $vs_Korte_Ref);	
	$t_occur->set('access', 1);	//1:Accessible to public, 0:Not accessible to public
//	$t_occur->set('status', 4);	//4:Completed (zie ca_xxx.php

if ($vn_Moderate == 2) {
	$t_occur->set('status', 2);
} else {
	$t_occur->set('status', 4);
}

//-------------------------
// Waarden mappen      
//-------------------------

	$t_occur->addAttribute(array(
		'locale_id'	=>	$pn_locale_id,
		'idno'		=>	$vs_Korte_Ref
	), 'idno');
	

	$t_occur->addAttribute(array(
		'locale_id'	=>	$pn_locale_id,
		'name'		=>	$vs_Bib_Ref
	), 'name');
	
	// insert the object
	$t_occur->insert();

	if ($t_occur->numErrors()) {
		print "\tERROR INSERTING {$vn_Literatuur_id}/{$vs_Korte_Ref}: ".join('; ', $t_occur->getErrors())."\n";
		continue;
	}
	
	// --------------------------------------------------------------
	// Set a preferred label for the object
	// --------------------------------------------------------------
		
	$t_occur->addLabel(
		array('idno' => $vs_Korte_Ref,'name' => substr($vs_Bib_Ref,0,1023)),
		$pn_locale_id, null, true
	);
		
	if ($t_occur->numErrors()) {
		print "\tERROR ADD LABEL TO {$vn_Literatuur_id}/{$vs_Korte_Ref}: ".join('; ', $t_occur->getErrors())."\n";
		continue;
	}

	$vn_c++;

}

print "ENDED IMPORTING new_literatuur.csv\n";

?>
