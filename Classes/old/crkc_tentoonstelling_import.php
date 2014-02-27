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

//require_once(__CA_LIB_DIR__.'/core/Parsers/TimeExpressionParser.php');
//$o_date_parser = new TimeExpressionParser();
//$o_date_parser->setLanguage('nl_NL',__PHPWEBLIB_DIR__);
//$vs_Datum_bis	=	$o_date_parser->preprocess($vs_Datum);

$_ = new Zend_Translate('gettext', __CA_APP_DIR__.'/locale/en_US/messages.mo', 'nl_NL');

$t_locale = new ca_locales();
$pn_locale_id = $t_locale->loadLocaleByCode('nl_NL');

$t_list = new ca_lists();
$pn_occurrence_type_id =	$t_list->getItemIDFromList('occurrence_types', 'exhibitions');	

//************************************
//****     TENTOONSTELLINGEN     *****
//************************************

print "IMPORTING tentoonstelling\n";

/*
// * Step 2: Import
*/

 // want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t"); 

// Read csv; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_tentoonstelling.csv')) {
	die("Couldn't parse new_tentoonstelling.csv data\n");	
}
	
print "READING new_tentoonstelling.csv...\n";

$vn_c = 1;
	
$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------
while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_Tentoonstelling_id	=	$o_tab_parser->getRowValue(1);
	$vs_Identificatie	=	$o_tab_parser->getRowValue(2);
	$vs_Omschrijving	=	$o_tab_parser->getRowvalue(3);
	$vs_Plaats		=	$o_tab_parser->getRowvalue(4);
	$vs_Begindatum		=	$o_tab_parser->getRowvalue(5);
	$vs_Einddatum		=	$o_tab_parser->getRowvalue(6);
	$vs_Origine		=	$o_tab_parser->getRowvalue(11);

	// opkuisen van begin en einddatum
	if(strstr($vs_Begindatum,'00/'))
	{
		$vs_Begindatum = str_replace("00/", "01/",$vs_Begindatum);
	} 
	if(strstr($vs_Einddatum,'00/')){
		$vs_Einddatum = str_replace("00/", "01/",$vs_Einddatum);
	}
	
print "\n Creating  ".$vn_Tentoonstelling_id.".".$vs_Identificatie." and adding labels for tentoonstelling. \n"; 
 
// ------------------------------------
// Waarden omvormen naar gepaste output
// ------------------------------------

//Datumveld: Begindatum - Einddatum

if (trim($vs_Begindatum) == "" and trim($vs_Einddatum) == "") {
	$vs_Datum = "";
} elseif (trim($vs_Begindatum) == "" and trim($vs_Einddatum) <> "") {
	$vs_Datum = $vs_Einddatum;	
} elseif (trim($vs_Begindatum) <> "" and trim($vs_Einddatum) == "") {
	$vs_Datum = $vs_Begindatum;
} else {
	$vs_Datum = $vs_Begindatum." - ".$vs_Einddatum;
}

print "Datum : ".$vs_Datum."\n";

if (trim($vs_Origine) == 'CRKC') {
	$pn_occurrence_source_id =	$t_list->getItemIDFromList('occurrence_sources', 'os_crkc');
} elseif (trim($vs_Origine) == 'POV') {
	$pn_occurrence_source_id =	$t_list->getItemIDFromList('occurrence_sources', 'os_pov');
}elseif (trim($vs_Origine) == 'PA') {
	$pn_occurrence_source_id =	$t_list->getItemIDFromList('occurrence_sources', 'os_pa');
} else {
	$pn_occurrence_source_id =	null;
	print "   - WARNING: geen Bron opgegeven voor {$vs_Identificatie}, verbeter indien nodig \n";
}


//----------------		
// Create object
//----------------

	$t_occur = new ca_occurrences();
	$t_occur->setMode(ACCESS_WRITE);
	$t_occur->set('type_id', $pn_occurrence_type_id);  	//tentoonstelling
	$t_occur->set('source_id', $pn_occurrence_source_id);
	$t_occur->set('idno', "tent_".$vn_Tentoonstelling_id);
	$t_occur->set('access', 1);
	$t_occur->set('status', 4);
//-------------------------
// Waarden mappen      
//-------------------------

	$t_occur->addAttribute(array(
		'locale_id'	=>	$pn_locale_id,
		'name'		=>	$vs_Identificatie
	), 'name');

	if (trim($vs_Omschrijving)) {
		$t_occur->addAttribute(array(
		'locale_id'			=>	$pn_locale_id,
		'tentoonstellingOmschrijving'	=>	$vs_Omschrijving
	), 'tentoonstellingOmschrijving');

	}

	if (trim($vs_Plaats)) {
		$t_occur->addAttribute(array(
		'locale_id'		 	=>	$pn_locale_id,
		'tentoonstellingPlaats'		=>	$vs_Plaats
	), 'tentoonstellingPlaats');

	}

	if ($vs_Datum) {
		$t_occur->addAttribute(array(
		'locale_id'		=>	$pn_locale_id,
		'tentoonstellingDatum'	=>	$vs_Datum
	), 'tentoonstellingDatum');

	}

	// insert the object
	$t_occur->insert();

	if ($t_occur->numErrors()) {
		print "\tERROR INSERTING {$vn_Tentoonstelling_id}/{$vs_Identificatie}: ".join('; ', $t_occur->getErrors())."\n";
		continue;
	}


	// --------------------------------------------------------------
	// Set a preferred label for the object
	// --------------------------------------------------------------
		
	// DON'T FORGET TO GIVE EVERYTHING A LABEL! This is the value used for display in search results
	// and lots of other places. If you don't define a value it will be hard to distinguish this row
	// for others


	$t_occur->addLabel(
		array('name' => $vs_Identificatie),
		$pn_locale_id, null, true
	);
		
	if ($t_occur->numErrors()) {
		print "\tERROR ADD LABEL TO {$vn_Tentoonstelling_id}/{$vs_Identificatie}: ".join('; ', $t_occur->getErrors())."\n";
		continue;
	}

/*	$lengte = 0;
	$beginpos = 0;
	unset($vs_Vervaardiger);
	unset($vn_Vervaardiger_id);
	$vs_AATtermnummer_bis = "";
*/

	$vn_c++;
}

print "ENDED IMPORTING new_tentoonstelling.csv\n";

?>
