<?php

/*
 * Step 1: Initialisation
 */
 
set_time_limit(36000);
require_once("/www/libis/html/ca_crkc/setup.php");

require_once(__CA_LIB_DIR__.'/core/Db.php');
require_once(__CA_MODELS_DIR__.'/ca_locales.php');
require_once(__CA_MODELS_DIR__.'/ca_entities.php');

require_once(__CA_LIB_DIR__.'/core/Parsers/DelimitedDataParser.php');


$_ = new Zend_Translate('gettext', __CA_APP_DIR__.'/locale/en_US/messages.mo', 'nl_NL');

$t_locale = new ca_locales();
$pn_locale_id = $t_locale->loadLocaleByCode('nl_NL');

$t_list = new ca_lists();
$pn_entity_type_id =	$t_list->getItemIDFromList('entity_types', 'vervaardiger');	

//************************************
//****        KUNSTENAAR         *****
//************************************

print "IMPORTING kunstenaar\n";

/****************
 * Step 2: Import
 ****************/

 // want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t"); 

// Read csv; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_kunstenaar.csv')) {
	die("Couldn't parse new_kunstenaar.csv data\n");	
}
	
print "READING new_kunstenaar.csv...\n";

$vn_c = 1;
	
$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------
while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_Kunstenaar_id	=	$o_tab_parser->getRowValue(1);
	$vs_Identificatie	=	$o_tab_parser->getRowValue(2);
	$vs_Omschrijving	=	$o_tab_parser->getRowvalue(3);
	$vs_AATtermnummer	=	$o_tab_parser->getRowvalue(4);
	$vn_Moderate		=	$o_tab_parser->getRowvalue(6);
	$vs_MoreInfos		=	$o_tab_parser->getRowvalue(9);
	$vs_Origine		=	$o_tab_parser->getRowvalue(10);

	
print "\n Creating  ".$vn_Kunstenaar_id.".".$vs_Identificatie." and adding labels for kunstenaar. \n"; 
 
// ------------------------------------
// Waarden omvormen naar gepaste output
// ------------------------------------

// Van het veld Omschrijving gaan we het deel na de laatste komma afzonderen, 
// en vervolgens opzoeken in de lijstVvervaardiger(historischPersoonType)
// Wordt waarde niet gevonden -> WARNING met gegevens naar log schrijven.

	$lengte = strlen($vs_Omschrijving);
	$beginpos = strrpos($vs_Omschrijving,",");
	
if ($beginpos <> 0) {
//	$vs_temp = (trim(substr($vs_Omschrijving,$beginpos+1,$lengte-($beginpos+1))));
	$vs_Vervaardiger = strtolower(trim(substr($vs_Omschrijving,$beginpos+1,$lengte-($beginpos+1))))."item";

//	print "{$vs_Omschrijving}\n";
//	print "{$vs_Vervaardiger}\n";
		
	if ($t_list->getItemIDFromList('historischPersoonType', $vs_Vervaardiger)) {
		$vn_Vervaardiger_id =	$t_list->getItemIDFromList('historischPersoonType', $vs_Vervaardiger);
//		print "{$vn_Vervaardiger_id}\n";
	}else {
		
		print "WARNING : {$vs_Vervaardiger} niet gevonden in lijst Vervaardiger \n";
		unset($vs_Vervaardiger);
		$beginpos = 0;
	}
	
} 

// Uit Omschrijving ook geboorteplaats+datum en sterfteplaats+datum halen !!!!!
// We slaan dit voorlopig over 
/*
//$rest = substr($vs_Omschrijving,0,$beginpos-1);

//print "{$vs_Omschrijving} \n";

$aantal_kommas = substr_count($vs_Omschrijving, ',');

//print "aantal kommas: {$aantal_kommas} \n";

$eerste_komma = strpos($vs_Omschrijving, ',', 0);

if ($eerste_komma === false) {

//	print "tekst bevat geen kommas\n";
	$deel_een = substr($vs_Omschrijving, 0, $lengte);
	$vs_print = $deel_een;
	
} else {

	$deel_een = substr($vs_Omschrijving, 0, $eerste_komma);
	$tweede_komma = strpos($vs_Omschrijving, ',', $eerste_komma+1);
	
	$vs_print = $deel_een;
	
	if ($tweede_komma === false) {
		$deel_twee = trim(substr($vs_Omschrijving, $eerste_komma +1, $lengte-($eerste_komma+1) ) );
		
		$vs_print = $vs_print."\t".$deel_twee;
		
	} else {
		
		$deel_twee = trim(substr($vs_Omschrijving, $eerste_komma+1, $tweede_komma-$eerste_komma-1));
		$derde_komma = strpos($vs_Omschrijving, ',', $tweede_komma+1);
		
		$vs_print = $vs_print."\t".$deel_twee;
	
		if ($derde_komma === false) {
			$deel_drie = trim(substr($vs_Omschrijving, $tweede_komma +1, $lengte-($tweede_komma+1) ) );
		
			$vs_print = $vs_print."\t".$deel_drie;
		
		} else {
		
			$deel_drie = trim(substr($vs_Omschrijving, $tweede_komma+1, $derde_komma-$tweede_komma-1) );
			$vierde_komma = strpos($vs_Omschrijving, ',', $derde_komma+1);
		
			$vs_print = $vs_print."\t".$deel_drie;
	
			if ($vierde_komma === false) {
				$deel_vier = trim(substr($vs_Omschrijving, $derde_komma +1, $lengte-($derde_komma+1) ) );
		
				$vs_print = $vs_print."\t".$deel_vier;
		
			} else {
		
				$deel_vier = trim(substr($vs_Omschrijving, $derde_komma+1, $vierde_komma-$derde_komma-1) );
				$vijfde_komma = strpos($vs_Omschrijving, ',', $vierde_komma+1);
		
				$vs_print = $vs_print."\t".$deel_vier;
	
				if ($vijfde_komma === false) {
					$deel_vijf = trim(substr($vs_Omschrijving, $vierde_komma +1, $lengte-($vierde_komma+1) ) );
		
					$vs_print = $vs_print."\t".$deel_vijf;
		
				} else {
		
					$deel_vijf = trim(substr($vs_Omschrijving, $vierde_komma+1, $vijfde_komma-$vierde_komma-1) );
					$vs_print = $vs_print."\t".$deel_vijf;
					$deel_zes = trim(substr($vs_Omschrijving, $vijfde_komma+1, $lengte-$vijfde_komma-1) );
					$vs_print = $vs_print."\t".$deel_zes;
				}
				
		
			}
		
		}
	}
}

// print "{$vs_print} \n";

*/
	if (trim($vs_AATtermnummer) == "/") {
		$vs_AATtermnummer_new = "";
	} else {
		$vs_AATtermnummer_new = trim($vs_AATtermnummer);
	}

	
//----------------		
// Create object
//----------------

	$t_person = new ca_entities();
	$t_person->setMode(ACCESS_WRITE);
	$t_person->set('type_id', $pn_entity_type_id);  //kunstenaar
	$t_person->set('idno', "verv_".$vn_Kunstenaar_id);
//	$t_person->set('status', 4);
	$t_person->set('access', 1);

// veld moderate gebruiken om status te bepalen

if ($vn_Moderate == 2) {
	$t_person->set('status', 2);
} else {
	$t_person->set('status', 4);
}

	
//-------------------------
// Waarden mappen      
//-------------------------

	$t_person->addAttribute(array(
		'locale_id'	=>	$pn_locale_id,
		'surname'	=>	$vs_Identificatie
	), 'surname');

	if (trim($vs_MoreInfos)) {
		$t_person->addAttribute(array(
		'locale_id'			=>	$pn_locale_id,
		'vervaardigerOpmerkingen'	=>	$vs_MoreInfos
	), 'vervaardigerOpmerkingen');

	}

	if (trim($vs_Omschrijving)) {
		$t_person->addAttribute(array(
		'locale_id'		 	=>	$pn_locale_id,
		'kunstenaarOmschrijving'	=>	$vs_Omschrijving
	), 'kunstenaarOmschrijving');

	}

	if ($beginpos <> 0) {
		$t_person->addAttribute(array(
		'locale_id'		=>	$pn_locale_id,
		'kunstenaarType'	=>	$vn_Vervaardiger_id
	), 'kunstenaarType');

	}

// Deze AATtermnummers kunnen (momenteel) niet opgezocht worden in een databank
// en dienen gewoon overgenomen te worden.

	if (trim($vs_AATtermnummer_new)) {
		$t_person->addAttribute(array(
		'locale_id'		=>	$pn_locale_id,
		'kunstenaarRKDArtistID'	=>	$vs_AATtermnummer_new
	), 'kunstenaarRKDArtistID');

	}

//Todo: deze info zit in één string - zie deel in commentaar
//'vervaardigerGeboortePlaats' -> link naar ca_places maar zonder x-relatie
//'vervaardigerSterfPlaats' -> link naar ca_places maar zonder x-relatie
	
	
	// insert the object
	$t_person->insert();

	if ($t_person->numErrors()) {
		print "\tERROR INSERTING {$vn_Kunstenaar_id}/{$vs_Identificatie}: ".join('; ', $t_person->getErrors())."\n";
		continue;
	}


	// --------------------------------------------------------------
	// Set a preferred label for the object
	// --------------------------------------------------------------
		
	// DON'T FORGET TO GIVE EVERYTHING A LABEL! This is the value used for display in search results
	// and lots of other places. If you don't define a value it will be hard to distinguish this row
	// for others


	$t_person->addLabel(
		array('surname' => $vs_Identificatie),
		$pn_locale_id, null, true
	);
		
	if ($t_person->numErrors()) {
		print "\tERROR ADD LABEL TO {$vn_Kunstenaar_id}/{$vs_Identificatie}: ".join('; ', $t_person->getErrors())."\n";
		continue;
	}

	$lengte = 0;
	$beginpos = 0;
	unset($vs_Vervaardiger);
	unset($vn_Vervaardiger_id);
	$vs_AATtermnummer_bis = "";
	
	$vn_c++;
}

print "ENDED IMPORTING new_kunstenaar.csv\n";
	
?>
