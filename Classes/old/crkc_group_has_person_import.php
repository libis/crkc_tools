<?php

/*
 * Step 1: Initialisation
 */

set_time_limit(36000);
require_once("/www/libis/html/ca_crkc/setup.php");

require_once(__CA_LIB_DIR__.'/core/Db.php');
require_once(__CA_MODELS_DIR__.'/ca_locales.php');
require_once(__CA_MODELS_DIR__.'/ca_entities.php');
require_once(__CA_MODELS_DIR__.'/ca_entities_x_entities.php');

require_once(__CA_LIB_DIR__.'/core/Parsers/DelimitedDataParser.php');


$_ = new Zend_Translate('gettext', __CA_APP_DIR__.'/locale/en_US/messages.mo', 'nl_NL');

$t_locale = new ca_locales();
$pn_locale_id = $t_locale->loadLocaleByCode('nl_NL');

$t_list = new ca_lists();

// get entities_relationship types
$t_rel_types = new ca_relationship_types();
$vn_entities_x_entities_id =	$t_rel_types->getRelationshipTypeID('ca_entities_x_entities', 'related');

print "entities_relationship: {$vn_entities_x_entities_id} \n";

//***********************************
//****   GROUP_AND_PERSON       *****
//***********************************

print "IMPORTING group and person \n";

/****************
 * Step 2: Import
 ****************/

 // want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t"); 

// Read csv; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/Query_group_has_person.csv')) {
	die("Couldn't parse Query_group_has_person.csv data\n");	
}
	
print "READING Query_group_and_person.csv...\n";

$vn_c = 1;

// om ids van verwerkte personen op te nemen, om te vermijden dat de info 
// dubbel in de databank komt
$va_Personen_ids = array();

$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------
while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_Person_id		=	$o_tab_parser->getRowValue(1);
	$vs_Person_Naam		=	$o_tab_parser->getRowValue(2);
	$vs_Person_VoorNaam	=	$o_tab_parser->getRowValue(3);
	$vn_Org_id		=	$o_tab_parser->getRowValue(4);
	$vs_Identificatie	=	$o_tab_parser->getRowvalue(5);
	$vn_Relatie_id		=	$o_tab_parser->getRowvalue(6);
	$vn_Huidig_Relatie_id	=	$o_tab_parser->getRowvalue(7);
	$vs_Opmerking		=	$o_tab_parser->getRowvalue(8);
	$vs_BeginDatum		=	$o_tab_parser->getRowvalue(9);
	$vs_EindDatum		=	$o_tab_parser->getRowvalue(10);
	

print "creating functiegegevens voor persoon ".$vn_Person_id.".".$vs_Person_Naam."  \n";

// ------------------------------------
// Waarden omvormen naar gepaste output
// ------------------------------------

// $vn_Relatie_id (getal) via dml_typeRelatietotPersoon (tabel) omvormen tot
// waarde uit lijst persoonFunctie_lijst

	if (($vn_Relatie_id) == 1) {
		$vn_Type_Relatie	=	$t_list->getItemIDFromList('persoonFunctie_lijst', 'overste');
	} elseif (($vn_Relatie_id) == 4) {
		$vn_Type_Relatie  	=       $t_list->getItemIDFromList('persoonFunctie_lijst', 'provinciaalOverste');
	} elseif (($vn_Relatie_id) == 5) {
		$vn_Type_Relatie  	=       $t_list->getItemIDFromList('persoonFunctie_lijst', 'prior');
	} elseif (($vn_Relatie_id) == 9) {
		$vn_Type_Relatie  	=       $t_list->getItemIDFromList('persoonFunctie_lijst', 'algemeenOverste');
	} elseif (($vn_Relatie_id) == 13) {
		$vn_Type_Relatie  	=       $t_list->getItemIDFromList('persoonFunctie_lijst', 'medewerker');
	} elseif (($vn_Relatie_id) == 16) {
		$vn_Type_Relatie  	=       $t_list->getItemIDFromList('persoonFunctie_lijst', 'pastoor');
	} elseif (($vn_Relatie_id) == 17) {
		$vn_Type_Relatie  	=       $t_list->getItemIDFromList('persoonFunctie_lijst', 'voorzitterKerkfabriek');
	} elseif (($vn_Relatie_id) == 18) {
		$vn_Type_Relatie  	=       $t_list->getItemIDFromList('persoonFunctie_lijst', 'secretarisKerkfabriek');
	} elseif (($vn_Relatie_id) == 19) {
		$vn_Type_Relatie  	=       $t_list->getItemIDFromList('persoonFunctie_lijst', 'verantwoordelijkeKunstpatrimonium');
	} elseif (($vn_Relatie_id) == 20) {
		$vn_Type_Relatie  	=       $t_list->getItemIDFromList('persoonFunctie_lijst', 'pastoraalWerker');
	} elseif (($vn_Relatie_id) == 21) {
		$vn_Type_Relatie  	=       $t_list->getItemIDFromList('persoonFunctie_lijst', 'raadslidKerkfabriek');
	} elseif (($vn_Relatie_id) == 22) {
		$vn_Type_Relatie  	=       $t_list->getItemIDFromList('persoonFunctie_lijst', 'andere');
	} elseif (($vn_Relatie_id) == 24) {
		$vn_Type_Relatie  	=       $t_list->getItemIDFromList('persoonFunctie_lijst', 'deken');
	} elseif (($vn_Relatie_id) == 25) {
		$vn_Type_Relatie  	=       $t_list->getItemIDFromList('persoonFunctie_lijst', 'lidKerkfabriek');
	} else {
		$vn_Type_Relatie 	= 	$t_list->getItemIDFromList('persoonFunctie_lijst', 'blank');
//		print "   - WARNING: geen type_contact voor organisatie {$vs_Person_Naam}, verbeter indien nodig \n";	
	} 

//	print "persoonFunctie_lijst: {$vn_Relatie_id}/{$vn_Type_Relatie}\n";

// $vn_Huidig_Relatie_id (getal: 0/1) omvormen tot
// waarde uit lijst huidigeTypeRelatie_list

	if (($vn_Huidig_Relatie_id) == 1) {
		$vn_Huidige_Relatie  	=       $t_list->getItemIDFromList('huidigeTypeRelatie_list', 'yes');
	} elseif (($vn_Huidig_Relatie_id) == 0) {
		$vn_Huidige_Relatie  	=       $t_list->getItemIDFromList('huidigeTypeRelatie_list', 'no');
	} else {
		$vn_Huidige_Relatie = null;
		print "   - WARNING: geen hudigeTypeRelatie voor persoon {$vs_Person_Naam}, verbeter indien nodig \n";	
	} 
	
	print "huidigeTypeRelatie_list: {$vn_Huidig_Relatie_id}/{$vn_Huidige_Relatie}\n";
	
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

print "datum: {$vs_Datum} \n";

//----------------		
// Create object
//----------------

	$t_pers = new ca_entities();
	$t_pers->setMode(ACCESS_WRITE);
               
	if($vs_Person_Naam == "") {
		$va_Person_keys = ($t_pers->getEntityIDsByidno("pers_" . $vn_Person_id) );
	} else {
		$va_Person_keys = ($t_pers->getEntityIDsByName($vs_Person_VoorNaam,$vs_Person_Naam) );
	}
	
	$dimension = sizeof($va_Person_keys);
	
	$vn_Person_key = $va_Person_keys[0];
	
	print "aantal: ".$dimension."\n";
	print "Primary Person-key: ".$vn_Person_key." \n";
	
	$t_pers->load($vn_Person_key);
	
	if ($t_pers->numErrors()) {
		print "\tERROR LOADING {$vn_Person_id}/{$vs_Person_Naam}: ".join('; ', $t_pers->getErrors())."\n";
		continue;
	}

	$t_pers->getPrimaryKey();

	if ($t_pers->numErrors()) {
		print "\tERROR PRIM_KEY {$vn_Person_id}/{$vs_Person_Naam}: ".join('; ', $t_pers->getErrors())."\n";
		continue;
	}

//-------------------------
// Waarden mappen      
//-------------------------

if (!(in_array($vn_Person_id, $va_Personen_ids))) {
	$t_pers->addAttribute(array(
		'locale_id'			=>	$pn_locale_id,
		'persoonFunctie'		=>	$vn_Type_Relatie,
		'persoonFunctieDatum'		=>	$vs_Datum,
		'persoonHuidigeTypeRelatie'	=>	$vn_Huidige_Relatie,
		'persoonOpmerkingen'		=>	$vs_Opmerking
	), 'persoonInfo');

	$t_pers->update();
	
	$va_Personen_ids[] = $vn_Person_id;

	if ($t_pers->numErrors()) {
		print "\tERROR UPDATING {$vn_Person_id}/{$vs_Person_Naam}: ".join('; ', $t_pers->getErrors())."\n";
		continue;
	}
}
//--------------------------------------
// Maak link tussen twee entities
//--------------------------------------
// Het gaat hier om een link tussen twee entities:
// De id van Person hebben we al: $vn_Person_key 
// Nu nog de id van Org: $vn_Org_key
// en verbinden

//	$t_org = new ca_entities();
//	$t_org->setMode(ACCESS_WRITE);

	$t_pers->set('entity_id', $vn_Person_key);

	$va_Org_keys = ($t_pers->getEntityIDsByName(null,$vs_Identificatie) );
	
	$dimension = sizeof($va_Org_keys);
	
	$vn_Org_key = $va_Org_keys[0];
	
	print "aantal: ".$dimension."\n";
	print "Primary Org_key: ".$vn_Org_key." \n";
	
	if ($vn_Person_key == null) {
		print " PERSON {$vs_Person_Naam} niet gevonden \n";
	}else {
		if ($vn_Org_key == null) {
			print " ORGANISATIE {$vs_Identificatie} niet gevonden \n";
		}else {
			
			$t_pers->addRelationship('ca_entities', $vn_Org_key, $vn_entities_x_entities_id);
	
			if ($t_pers->numErrors()) {
				print "ERROR LINKING person ".$vs_Person_Naam." TO organisatie ".$vs_Identificatie." : ".join('; ', $t_pers->getErrors())."\n";
				continue;
			}
		}
	}

	$va_Person_keys = null;
	$vn_Person_key = null;
	$va_Org_keys = null;
	$vn_Org_key = null;
	
	print "volgend record \n\n";
	
	$vn_c++;

}

print "ENDED IMPORTING group_and_contact \n";

?>
