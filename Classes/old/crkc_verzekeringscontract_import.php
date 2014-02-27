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
$pn_occurrence_type_id =	$t_list->getItemIDFromList('occurrence_types', 'insurance');


//************************************
//****   VERZEKERINGSCONTRACT    *****
//************************************

print "IMPORTING verzekeringscontract \n";

//
// Step 2: Import
//

 // want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t"); 

// Read csv; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_verzekeringscontract.csv')) {
	die("Couldn't parse new_verzekeringscontract.csv data\n");	
}
	
print "READING new_verzekeringscontract.csv...\n";

$vn_c = 1;
	
$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------

while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_Verzekering_id	=	$o_tab_parser->getRowValue(1);
	$vs_Polisnummer 	=	$o_tab_parser->getRowValue(3);
	$vs_Omschrijving	=	$o_tab_parser->getRowvalue(4);
	$vs_BeginDatum		= 	$o_tab_parser->getRowvalue(5);
	$vs_EindDatum		=	$o_tab_parser->getRowvalue(6);
	
print "\n Creating  ".$vn_Verzekering_id.".".$vs_Polisnummer." and adding labels for verzekeringscontract. \n"; 

// ------------------------------------
// Waarden omvormen naar gepaste output
// ------------------------------------

//Datumvelden samenvoegen

if (trim($vs_Begindatum) == "" and trim($vs_Einddatum) == "") {
	$vs_Datum = "";
} elseif (trim($vs_Begindatum) == "" and trim($vs_Einddatum) <> "") {
	$vs_Datum = $vs_Einddatum;	
} elseif (trim($vs_Begindatum) <> "" and trim($vs_Einddatum) == "") {
	$vs_Datum = $vs_Begindatum;
} else {
	$vs_Datum = $vs_Begindatum." - ".$vs_Einddatum;
}


//----------------		
// Create object
//----------------
// Korte beschrijving komt in Idno en Bibliografische beschrijving komt in preferred label

	$t_occur = new ca_occurrences();
	$t_occur->setMode(ACCESS_WRITE);
	$t_occur->set('type_id', $pn_occurrence_type_id);
	$t_occur->set('source_id', null);
	$t_occur->set('idno', "polis_".$vs_Polisnummer);	
	$t_occur->set('access', 1);	//1:Accessible to public, 0:Not accessible to public
	$t_occur->set('status', 4);	//4:Completed (zie ca_xxx.php

//-------------------------
// Waarden mappen      
//-------------------------

if (trim($vs_Omschrijving)) {
	$t_occur->addAttribute(array(
		'locale_id'	=>	$pn_locale_id,
		'name'		=>	$vs_Omschrijving
	), 'name');
} else {
	print "Omschrijving mag niet blanco zijn, nemen Polisnummer over\n";
	$vs_Omschrijving = "verzekeringscontract ".$vs_Polisnummer;
	$t_occur->addAttribute(array(
		'locale_id'	=>	$pn_locale_id,
		'name'		=>	$vs_Omschrijving
	), 'name');
}

if (trim($vs_Datum)) {
	$t_occur->addAttribute(array(
		'locale_id'	=>	$pn_locale_id,
		'insuranceDate'	=>	$vs_Datum
	), 'insuranceDate');
}
	
	// insert the object
	$t_occur->insert();

	if ($t_occur->numErrors()) {
		print "\tERROR INSERTING {$vn_Verzekering_id}/{$vs_Polisnummer}: ".join('; ', $t_occur->getErrors())."\n";
		continue;
	}
	
	// --------------------------------------------------------------
	// Set a preferred label for the object
	// --------------------------------------------------------------
		
	$t_occur->addLabel(
		array('name' => substr($vs_Omschrijving,0,99)),
		$pn_locale_id, null, true
	);
		
	if ($t_occur->numErrors()) {
		print "\tERROR ADD LABEL TO {$vn_Verzekering_id}/{$vs_Polisnummer}: ".join('; ', $t_occur->getErrors())."\n";
		continue;
	}

	$vn_c++;

}

print "ENDED IMPORTING new_verzekeringscontract.csv\n";


?>
