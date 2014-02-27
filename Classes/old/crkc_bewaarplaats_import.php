<?php

/*
 * Step 1: Initialisation
 */
 
set_time_limit(36000);
require_once("/www/libis/html/ca_crkc/setup.php");

require_once(__CA_LIB_DIR__.'/core/Db.php');
require_once(__CA_MODELS_DIR__.'/ca_locales.php');
require_once(__CA_MODELS_DIR__.'/ca_storage_locations.php');
require_once(__CA_MODELS_DIR__.'/ca_entities.php');
require_once(__CA_MODELS_DIR__.'/ca_entities_x_storage_locations.php');

require_once(__CA_LIB_DIR__.'/core/Parsers/DelimitedDataParser.php');


$_ = new Zend_Translate('gettext', __CA_APP_DIR__.'/locale/en_US/messages.mo', 'nl_NL');

$t_locale = new ca_locales();
$pn_locale_id = $t_locale->loadLocaleByCode('nl_NL');

$t_list = new ca_lists();
$pn_storage_location_type_extern_id 	=	$t_list->getItemIDFromList('storage_location_types', 'externeBewaarplaats');
$pn_storage_location_type_crkc_id	=	$t_list->getItemIDFromList('storage_location_types', 'crkcDepot');
$pn_bewaarplaats_type_extern_id 	=	$t_list->getItemIDFromList('bewaartypes_types', 'externeBewaarplaats');
$pn_bewaarplaats_type_crkc_id 		=	$t_list->getItemIDFromList('bewaartypes_types', 'externeBewaarplaats');
$vn_Address_type		=	$t_list->getItemIDFromList('address_type', 'bewaarplaats');
$pn_entity_type_id		=	$t_list->getItemIDFromList('entity_types', 'organization');
$pn_entity_source_id		=	$t_list->getItemIDFromList('entity_sources', 'i1');


// get entity_relationship types
$t_rel_types = new ca_relationship_types();
$vn_entity_x_storage_location_id =	$t_rel_types->getRelationshipTypeID('ca_entities_x_storage_locations', 'bewaarplaatsRelatie');

// Tussenvoegen blok om bewaarplaatsen hierarchisch te maken en crkcLocaties toe te voegen

$t_storage = new ca_storage_locations();
$t_storage->setMode(ACCESS_WRITE);
//parent CRKC Depot
$t_storage->set('type_id', $pn_storage_location_type_crkc_id);
$t_storage->set('parent_id', null);
$t_storage->set('idno', "stloc_crkcDepot");
$t_storage->set('status', 4);

	$t_storage->addAttribute(array(
		'locale_id'	=>	$pn_locale_id,
		'name'		=>	"CRKC Depot"
	), 'name');

	$t_storage->addAttribute(array(
		'locale_id'		=>	$pn_locale_id,
		'bewaarplaatsType'	=>	$pn_bewaarplaats_type_crkc_id
	), 'bewaarplaatsType');

	$t_storage->insert();

	if ($t_storage->numErrors()) {
		print "ERROR INSERTING CRKC Depot: ".join('; ', $t_storage->getErrors())."\n";
		continue;
	}
	
	$crkc_parent = $t_storage->getPrimaryKey();
	
	$t_storage->addLabel(
		array('name' => "CRKC Depot"),
		$pn_locale_id, null, true
	);
		
	if ($t_storage->numErrors()) {
		print "ERROR ADDING LABEL TO CRKC Depot: ".join('; ', $t_storage->getErrors())."\n";
		continue;
	}
	
// De twee crkcLocaties
$va_crkcLocaties = array();
$va_crkcLocaties[] = array('nr' => 1, 'Id' => 'DOLV', 'Omschrijving' => 'Onze-Lieve-Vrouw Waver');
$va_crkcLocaties[] = array('nr' => 2, 'Id' => 'DB', 'Omschrijving' => 'Bierbeek, Sint-Camillus');

foreach($va_crkcLocaties as $i => $value) {
	$vs_Identificatie_bis = $va_crkcLocaties[$i]['Id']." - ".$va_crkcLocaties[$i]['Omschrijving']." (stloc_".$va_crkcLocaties[$i]['nr'].")";
	$t_storage->set('type_id', $pn_storage_location_type_crkc_id);
	$t_storage->set('parent_id', $crkc_parent);
	$t_storage->set('idno', "stloc_".$va_crkcLocaties[$i]['nr']);
	
	$t_storage->addAttribute(array(
		'locale_id'	=>	$pn_locale_id,
		'name'		=>	$vs_Identificatie_bis
	), 'name');

	$t_storage->addAttribute(array(
		'locale_id'		=>	$pn_locale_id,
		'bewaarplaatsType'	=>	$pn_bewaarplaats_type_crkc_id
	), 'bewaarplaatsType');

	$t_storage->insert();

	if ($t_storage->numErrors()) {
		print "ERROR INSERTING {$vs_Identificatie_bis} : ".join('; ', $t_storage->getErrors())."\n";
		continue;
	}
	
	$t_storage->addLabel(
		array('name' => $vs_Identificatie_bis),
		$pn_locale_id, null, true
	);
		
	if ($t_storage->numErrors()) {
		print "ERROR ADDING LABEL TO {$vs_Identificatie_bis}: ".join('; ', $t_storage->getErrors())."\n";
		continue;
	}
	
	// -------------------------------------------
	// Maak link naar entities -> verwijderd cfr mail Sam 4 juli 2012
	//--------------------------------------------
/*	
	$vn_Storage_id = $t_storage->getPrimaryKey();
	
	$vn_entity_id = getEntityID($vs_Identificatie_bis, $pn_locale_id);
	
	print "vn_entity_id {$vn_entity_id}\n";
	
	if ($vn_entity_id == NULL) {
		print " ENTITY toevoegen \n";
	}else{
		$t_storage -> addRelationship('ca_entities', $vn_entity_id, $vn_entity_x_storage_location_id);
	
		if ($t_storage->numErrors()) {
			print "ERROR LINKING Storage Location ".$vs_Identificatie_bis." TO Entity : ".join('; ', $t_storage->getErrors())."\n";
			continue;
		}
	}
*/	

	$lengte = 0;
	$vs_Gemeente = "";
	$vs_Identificatie_bis = "";
}
	
//Parent Externe Bewaarplaatsen
$t_storage->set('type_id', $pn_storage_location_type_extern_id);
$t_storage->set('parent_id', null);
$t_storage->set('idno', "stloc_extern");
$t_storage->set('status', 4);

	$t_storage->addAttribute(array(
		'locale_id'	=>	$pn_locale_id,
		'name'		=>	"Externe Bewaarplaatsen"
	), 'name');

	$t_storage->addAttribute(array(
		'locale_id'		=>	$pn_locale_id,
		'bewaarplaatsType'	=>	$pn_bewaarplaats_type_extern_id
	), 'bewaarplaatsType');

	$t_storage->insert();

	if ($t_storage->numErrors()) {
		print "ERROR INSERTING EXTERNE Bewaarplaatsen: ".join('; ', $t_storage->getErrors())."\n";
		continue;
	}
	
	$extern_parent = $t_storage->getPrimaryKey();
	
	$t_storage->addLabel(
		array('name' => "Externe Bewaarplaatsen"),
		$pn_locale_id, null, true
	);
		
	if ($t_storage->numErrors()) {
		print "ERROR ADD LABEL TO EXTERNE Bewaarplaatsgen: ".join('; ', $t_storage->getErrors())."\n";
		continue;
	}

// Einde blok
	
//print "entity_relationship: {$vn_entity_x_storage_location_id} \n";
//print "entity_type_id : {$pn_bewaarplaats_type_id} \n";
//print "enity_source_id : {$pn_entity_source_id}\n";

//************************************
//****       BEWAARPLAATSEN      *****
//************************************

print "IMPORTING bewaarplaats\n";

/*
// * Step 2: Import
*/

 // want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t"); 

// Read csv; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_bewaarplaats_bis.csv')) {
	die("Couldn't parse new_bewaarplaats_bis.csv data\n");	
}
	
print "READING new_bewaarplaats_bis.csv...\n";

$vn_c = 1;
	
$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------
while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_Bewaarplaats_id	=	$o_tab_parser->getRowValue(1);
	$vs_Identificatie	=	$o_tab_parser->getRowValue(2);
	$vs_Straat		=	$o_tab_parser->getRowvalue(3);
	$vs_VioeId		=	$o_tab_parser->getRowvalue(4);
	$vs_idPlaatsen		=	$o_tab_parser->getRowvalue(5);
	
	$vs_Land		= 	$o_tab_parser->getRowvalue(10);
	$vs_Postcode		=	$o_tab_parser->getRowvalue(11);
	
	print "\n ====={$vn_c}===== Creating  ".$vn_Bewaarplaats_id.".".$vs_Identificatie." and adding labels for bewaarplaats. \n"; 
 
// ------------------------------------
// Waarden omvormen naar gepaste output
// ------------------------------------

//Uit het identificatie veld verwijderen we de gemeente
// CRKC wil niet dat deze gemeente verwijderd wordt - dus 'name' veld wordt $vs_Identificatie !!! (cfr mail Sam 6 juli 2012)
	$lengte = strpos($vs_Identificatie,",");
	$vs_Gemeente = trim(substr($vs_Identificatie,0,$lengte));
	$vs_Identificatie_bis = trim(substr($vs_Identificatie,$lengte+1,strlen($vs_Identificatie)))." (stloc_".$vn_Bewaarplaats_id.")";
	
	//print " {$vs_Gemeente} / {$vs_Identificatie} \n";
	
//----------------		
// Create object
//----------------

	$t_storage->set('type_id', $pn_storage_location_type_extern_id);
	$t_storage->set('parent_id', $extern_parent);
	$t_storage->set('idno', "stloc_".$vn_Bewaarplaats_id);
	$t_storage->set('status', 4);
	
//-------------------------
// Waarden mappen      
//-------------------------

	$t_storage->addAttribute(array(
		'locale_id'	=>	$pn_locale_id,
		'name'		=>	$vs_Identificatie
	), 'name');

	$t_storage->addAttribute(array(
		'adres'		=> $vs_Straat,
		'adresType'	=> $vn_Address_type,
		'city'		=> $vs_Gemeente,
		'placePostcode'	=> $vs_Postcode,
		'country'	=> $vs_Land,
		'locale_id'	=> $pn_locale_id
	),'address');
	
	$t_storage->addAttribute(array(
		'locale_id'		=>	$pn_locale_id,
		'bewaarplaatsType'	=>	$pn_bewaarplaats_type_extern_id
	), 'bewaarplaatsType');
	
	if (trim($vs_VioeId) == '' or trim($vs_VioeId) =='/') {
	} else {
		if(strstr($vs_VioeId, '(?)') or strstr($vs_VioeId, '?'))
		{
			$vs_VioeId = str_replace('(?)', '', $vs_VioeId);
			$vs_VioeId = str_replace('?', '', $vs_VioeId);
		}
		$t_storage->addAttribute(array(
			'locale_id'		=>	$pn_locale_id,
			'bewaarplaatsVioeId'	=>	'https://inventaris.onroerenderfgoed.be/dibe/relict/' . $vs_VioeId
		), 'bewaarplaatsVioeId');
	}
		
	// insert the object
	$t_storage->insert();

	if ($t_storage->numErrors()) {
		print "ERROR INSERTING {$vn_Bewaarplaats_id}/{$vs_Identificatie}: ".join('; ', $t_storage->getErrors())."\n";
		continue;
	}

	// --------------------------------------------------------------
	// Set a preferred label for the object
	// --------------------------------------------------------------
		
	// DON'T FORGET TO GIVE EVERYTHING A LABEL! This is the value used for display in search results
	// and lots of other places. If you don't define a value it will be hard to distinguish this row
	// for others

	$t_storage->addLabel(
		array('name' => $vs_Identificatie),
		$pn_locale_id, null, true
	);
		
	if ($t_storage->numErrors()) {
		print "ERROR ADDING LABEL TO {$vn_Bewaarplaats_id}/{$vs_Identificatie}: ".join('; ', $t_person->getErrors())."\n";
		continue;
	}

	// -------------------------------------------
	// Maak link naar entities -> verwijderd cfr mail Sam 4 juli 2012
	//--------------------------------------------
/*	
	$vn_Storage_id = $t_storage->getPrimaryKey();
	
	$vn_entity_id = getEntityID($vs_Identificatie_bis, $pn_locale_id);
	
	print "vn_entity_id {$vn_entity_id}\n";
	
	if ($vn_entity_id == NULL) {
		print " ENTITY toevoegen \n";
	}else{
		$t_storage -> addRelationship('ca_entities', $vn_entity_id, $vn_entity_x_storage_location_id);
	
		if ($t_storage->numErrors()) {
			print "ERROR LINKING Storage Location ".$vs_Identificatie_bis." TO Entity : ".join('; ', $t_storage->getErrors())."\n";
			continue;
		}
	}
*/	

	$lengte = 0;
	$vs_Gemeente = "";
	$vs_Identificatie_bis = "";
	

	
	$vn_c++;
}

print "ENDED IMPORTING new_bewaarplaats.csv\n";

// ----------------------------------------------------------------------	
	function getEntityID($ps_name, $pn_locale_id) {
		
		global $pn_entity_type_id, $pn_entity_source_id, $vn_Bewaarplaats_id, $vs_Identificatie_bis;
		global $vs_Straat, $vn_Address_type, $vs_Gemeente, $vs_Postcode, $vs_Land;
/*		
		print "locale_id : {$pn_locale_id}\n";
		print "entity_type_id : {$pn_entity_type_id} \n";
		print "entity_source_id : {$pn_entity_source_id} \n";
		print "idno : {$vn_Bewaarplaats_id}\n";
		print "name : {$ps_name}\n";
		print "land : {$vs_Land}\n";
*/		
		$t_entity = new ca_entities();
		if (sizeof($va_entity_ids = $t_entity->getEntityIDsByName('',$ps_name))== 0 ) {
			print "\t\t no ENTITY {$ps_name} FOUND \n";
			// insert storage location as an organization
			$t_entity->setMode(ACCESS_WRITE);
			$t_entity->set('locale_id', $pn_locale_id);
			$t_entity->set('source_id', $pn_entity_source_id);
			$t_entity->set('type_id', $pn_entity_type_id);  //bewaarPlaats
			$t_entity->set('idno', "stloc_".$vn_Bewaarplaats_id);
			$t_entity->set('access', 1);
			$t_entity->set('status', 4);
						
			$t_entity->addAttribute(array(
				'locale_id'	=>	$pn_locale_id,
				'surname'	=>	$ps_name
			), 'name');

			$t_entity->addAttribute(array(
				'adres'		=> $vs_Straat,
				'adresType'	=> $vn_Address_type,
				'city'		=> $vs_Gemeente,
				'placePostcode'	=> $vs_Postcode,
				'country'	=> $vs_Land,
				'locale_id'	=> $pn_locale_id
			),'address');
			
			$t_entity->insert();
			
			if ($t_entity->numErrors()) {
				print "ERROR INSERTING entity {$ps_name}: ".join('; ', $t_entity->getErrors())."\n";
				return null;
			}
			$t_entity->addLabel(
				array('surname' => substr($ps_name,0,99)),
				$pn_locale_id, null, true
			);
			
			$vn_entity_id = $t_entity->getPrimaryKey();
			
//			print "{$vn_entity_id} \n";
			
		} else {
			$vn_entity_id = array_shift($va_entity_ids);
		}
		
		return $vn_entity_id;
	}
	// ----------------------------------------------------------------------


?>
