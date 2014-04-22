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
$pn_entity_type_id =	$t_list->getItemIDFromList('entity_types', 'organization');	

//************************************
//****   DIRECTORY_GROUP__002   *****
//************************************

print "IMPORTING directory_group__002\n";

/****************
 * Step 2: Import
 ****************/

 // want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t"); 

// Read csv; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/Query_group_and_address.csv')) {
	die("Couldn't parse Query_group_and_address.csv data\n");	
}
	
print "READING Query_group_and_address.csv...\n";

$vn_c = 1;
	
$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------
while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_Instelling_id		=	$o_tab_parser->getRowValue(1);
	$vn_Bisdom_id			=	$o_tab_parser->getRowValue(2);
	$vs_Identificatie		=	$o_tab_parser->getRowValue(3);
	$vs_Afkorting			=	$o_tab_parser->getRowvalue(4);
	$vs_instellingOpmerking		=	$o_tab_parser->getRowvalue(5);
	// Get columns from tab file and put them into named variables - makes code easier to read
//	$vn_Personen_id_2		=	$o_tab_parser->getRowValue(6);
	$vn_TypeAdres_id		=	$o_tab_parser->getRowValue(7); // dropdown Adres type
	$vn_HuidigAdres_yn		=	$o_tab_parser->getRowValue(8); // drop
	$vs_Adressen_id			=	$o_tab_parser->getRowvalue(9); // CRKCAdresId 
	$vs_Identificatie_bis		=	$o_tab_parser->getRowvalue(10); //   Adreslijn1 
	$vs_Omschrijving		=	$o_tab_parser->getRowvalue(11); // 
	$vs_Adreslijn1			=	$o_tab_parser->getRowvalue(12); // samenvoegen in adres
	$vs_Adreslijn2			=	$o_tab_parser->getRowvalue(13); // samenvoegen in adres
	$vs_Postcode			=	$o_tab_parser->getRowvalue(14); // postcode
	$vs_Stad 			=	$o_tab_parser->getRowvalue(15);// stad-gemeente
	$vs_ProvincieStaat		=	$o_tab_parser->getRowvalue(16);//   bij stad-gemeente
	$vs_Land			=	$o_tab_parser->getRowvalue(17);// Land

print "creating organization ".$vn_Instelling_id.".".$vs_Identificatie." and adding labels for group. \n";

// ------------------------------------
// Waarden omvormen naar gepaste output
// ------------------------------------

	if (trim($vs_Afkorting)) {
		$vs_Afkorting_bis = $vs_Afkorting." (".$vs_Identificatie.")";
	} else {
		$vs_Afkorting_bis = $vs_Identificatie;
	}	

// bisdom_types: lijstwaarde bepalen
// bisdom Antwerpen is de default waarde

	if (($vn_Bisdom_id) == 2) {
		$vn_Bisdom_type	=	$t_list->getItemIDFromList('bisdom_types', 'bisdomAntwerpen');
	} elseif (($vn_Bisdom_id) == 3) {
		$vn_Bisdom_type  =       $t_list->getItemIDFromList('bisdom_types', 'bisdomMechelenBrussel');
	} elseif (($vn_Bisdom_id) == 4) {
		$vn_Bisdom_type  =       $t_list->getItemIDFromList('bisdom_types', 'bisdomBrugge');
	} elseif (($vn_Bisdom_id) == 5) {
		$vn_Bisdom_type  =       $t_list->getItemIDFromList('bisdom_types', 'bisdomHasselt');
	} elseif (($vn_Bisdom_id) == 6) {
		$vn_Bisdom_type  =       $t_list->getItemIDFromList('bisdom_types', 'bisdomGent');
	} elseif (($vn_Bisdom_id) == 8) {
		$vn_Bisdom_type  =       $t_list->getItemIDFromList('bisdom_types', 'bisdomSHertogenbosch');
	} else {
		$vn_Bisdom_type = null;
//		print "   - WARNING: geen Bisdom_type voor organisatie {$vs_Identificatie}, verbeter indien nodig \n";	
	} 


//================================================
//=========== Adres-informatie verwerken =========
//================================================
	
if (trim($vs_Adressen_id)) {	
		
	print "creating address for organization ".$vn_Instelling_id.".".$vs_Identificatie." and adding labels for address \n";
		
// TypeAdres_id: lijstwaarde bepalen (cfr. tabel dml_typeadres.csv)
// de defaultwaarde is mainAddress

	if (($vn_TypeAdres_id) == 0) {
		$vn_Address_type	=	$t_list->getItemIDFromList('address_type', 'bewaarplaats');
	} elseif (($vn_TypeAdres_id) == 1) {
		$vn_Address_type  =       $t_list->getItemIDFromList('address_type', 'mainAddress');
	} elseif (($vn_TypeAdres_id) == 3) {
    		$vn_Address_type  =       $t_list->getItemIDFromList('address_type', 'provinciaalHuis');
	} elseif (($vn_TypeAdres_id) == 4) {
		$vn_Address_type  =       $t_list->getItemIDFromList('address_type', 'generaalHuis');
	} elseif (($vn_TypeAdres_id) == 5) {
		$vn_Address_type  =       $t_list->getItemIDFromList('address_type', 'work');
	} elseif (($vn_TypeAdres_id) == 6) {
		$vn_Address_type  =       $t_list->getItemIDFromList('address_type', 'home');
	} else {
		$vn_Address_type  =       $t_list->getItemIDFromList('address_type', 'mainAddress');
		print "   - WARNING: geen Adrestype voor organization, gebruiken default: Hoofdadres. \n";
	} 

// Adreslijnen samenvoegen
// Identificatie en Adreslijn1 bevatten bijna altijd dezelfde info
// Adreslijn1 is heel vaak blanco - Adreslijn2 is bijna altijd blanco
// Stellen adres samen op basis van Identificatie en Omschrijving
// kan nog verfijnd worden ?????????

	$vs_Adres = (trim($vs_Identificatie_bis));

	if (trim($vs_Omschrijving)) {
		$vs_Adres_new = (trim($vs_Omschrijving))."/".(trim($vs_Adres)); 
	}else {
		$vs_Adres_new = (trim($vs_Adres));
	}

// Stad en ProvincieStad worden samengebracht in het veld StadGemeente

	if (!trim($vs_ProvincieStaat)) {
		$vs_StadGemeente = trim($vs_Stad);
	} else {
		$vs_StadGemeente = (trim($vs_Stad))." - ".(trim($vs_ProvincieStaat));
	}

}

//----------------		
// Create object
//----------------

	$t_organization = new ca_entities();
	$t_organization->setMode(ACCESS_WRITE);
	$t_organization->set('type_id', $pn_entity_type_id);  //personen
	$t_organization->set('idno', "org_".$vn_Instelling_id);
	$t_organization->set('status', 4);
	$t_organization->set('access', 1);
	
//-------------------------
// Waarden mappen      
//-------------------------

if ($vn_Bisdom_type) {
	$t_organization->addAttribute(array(
		'locale_id'	=>	$pn_locale_id,
		'bisdom'	=>	$vn_Bisdom_type
	), 'bisdom');
}

	$t_organization->addAttribute(array(
		'locale_id'	=>	$pn_locale_id,
		'surname'	=>	$vs_Identificatie
	), 'surname');

	$t_organization->addAttribute(array(
		'locale_id'	=>	$pn_locale_id,
		'displayname'	=>	$vs_Afkorting_bis
	), 'displayname');

	if (trim($vs_instellingOpmerking)) {
		$t_organization->addAttribute(array(
		'locale_id'			=>	$pn_locale_id,
		'instellingOmschrijving'	=>	$vs_instellingOpmerking
	), 'instellingOmschrijving');

	}
	
//	print "{$vs_Identificatie} - {$vs_Afkorting} - {$vs_Afkorting_bis} - {$vs_instellingOpmerking}\n";
	
	if (trim($vs_Adressen_id)) {
		
//		print "   Adres Type:".$vn_TypeAdres_id."/".$vn_Address_type."\n";
//		print "   {$vs_Identificatie_bis} - {$vs_Adres} - {$vs_Omschrijving} - {$vs_Stad} - {$vs_ProvincieStaat}\n" ;
//		print "   {$vs_Adres_new} - {$vs_Adressen_id} - {$vn_Address_type} - {$vs_StadGemeente} - {$vs_Postcode} - {$vs_Land} \n";
	
		
		$t_organization->addAttribute(array(
				'adres'		=> $vs_Adres_new,
				'crkcAdresId'	=> $vs_Adressen_id,
				'adresType'	=> $vn_Address_type,
				'city'		=> $vs_StadGemeente,
				'placePostcode'	=> $vs_Postcode,
				'country'	=> $vs_Land,
				'locale_id'	=> $pn_locale_id
		),'address');
	}

//	vat_dump($t_organization);
	// insert the object
	$t_organization->insert();

	if ($t_organization->numErrors()) {
		print "\tERROR INSERTING {$vn_Instelling_id}/{$vs_Identificatie}: ".join('; ', $t_organization->getErrors())."\n";
		continue;
	}


	// --------------------------------------------------------------
	// Set a preferred label for the object
	// --------------------------------------------------------------
		
	// DON'T FORGET TO GIVE EVERYTHING A LABEL! This is the value used for display in search results
	// and lots of other places. If you don't define a value it will be hard to distinguish this row
	// for others


	$t_organization->addLabel(
		array('surname' => substr($vs_Identificatie,0,99),
			'displayname' => substr($vs_Afkorting_bis,0,500)),
		$pn_locale_id, null, true
	);
		
	if ($t_organization->numErrors()) {
		print "\tERROR ADD LABEL TO {$vn_Instelling_id}/{$vs_Identifictatie}: ".join('; ', $t_organization->getErrors())."\n";
		continue;
	}

	$vs_Identificatie_bis = "";
	$vs_Adres = "";
	$vs_Omschrijving = "";
	$vs_Stad = "";
	$vs_ProvincieStaat = "";
	$vs_Adres_new = "";
	$vs_Adressen_id = "" ;
	$vn_Address_type = "";
	$vs_StadGemeente = "";
	$vs_Postcode = "";
	$vs_Land = "";
	
	$vn_c++;
}

print "ENDED IMPORTING directory_group__002\n";

?>
