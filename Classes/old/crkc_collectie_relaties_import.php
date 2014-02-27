<?php

/*
 * Step 1: Initialisation
 */
 
set_time_limit(36000);
require_once("/www/libis/html/ca_crkc/setup.php");

require_once(__CA_LIB_DIR__.'/core/Db.php');
require_once(__CA_MODELS_DIR__.'/ca_locales.php');
require_once(__CA_MODELS_DIR__.'/ca_entities.php');
require_once(__CA_MODELS_DIR__.'/ca_collections.php');
require_once(__CA_MODELS_DIR__.'/ca_storage_locations.php');
require_once(__CA_MODELS_DIR__.'/ca_entities_x_collections.php');
require_once(__CA_MODELS_DIR__.'/ca_collections_x_storage_locations.php');

require_once(__CA_LIB_DIR__.'/core/Parsers/DelimitedDataParser.php');


$_ = new Zend_Translate('gettext', __CA_APP_DIR__.'/locale/en_US/messages.mo', 'nl_NL');

$t_locale = new ca_locales();
$pn_locale_id = $t_locale->loadLocaleByCode('nl_NL');

$t_list = new ca_lists();

// get relationship types
$t_rel_types = new ca_relationship_types();
$vn_entities_x_collections_id_eigenaar =	$t_rel_types->getRelationshipTypeID('ca_entities_x_collections', 'eigenaarRelatie');
$vn_entities_x_collections_id_beheerder =	$t_rel_types->getRelationshipTypeID('ca_entities_x_collections', 'beheerderRelatie');

$vn_collections_x_storage_locations_id = $t_rel_types->getRelationshipTypeID('ca_collections_x_storage_locations', 'related');

$t_location = new ca_storage_locations();

// print "relationship: {$vn_entities_x_collections_id} \n";

$t_collectie = new ca_collections();
$t_collectie->setMode(ACCESS_WRITE);

//***************************
//****   COLLECTIONS    *****
//***************************

 // want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t"); 

//
// Step 2: Import
//

print "IMPORTING bewaarplaats_bis into an array \n";

// Read csv; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_bewaarplaats_bis.csv')) {
	die("Couldn't parse new_bewaarplaats_bis.csv data\n");	
}

$vn_c = 1;
	
$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------

while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_Id			=	$o_tab_parser->getRowValue(1); 
	$vn_Identificatie 	=	$o_tab_parser->getRowValue(2); 
	
	$va_Bewaarplaats[$vn_Id] = $vn_Identificatie;

	$vn_c++;
}

print "IMPORTING collecties \n";

// Read csv; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_collectie.csv')) {
	die("Couldn't parse new_collectie.csv data\n");	
}
	
print "READING new_collectie.csv...\n";

$vn_c = 1;
	
$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------

while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_Collection_id	=	$o_tab_parser->getRowValue(1); //id (niet gebruiken)
	$vs_Titel	 	=	$o_tab_parser->getRowValue(2); //bevat nummer (nummer + "-" + titel wordt idno
	$vs_Omschrijving	=	$o_tab_parser->getRowvalue(3); //titel
	$vn_Public		= 	$o_tab_parser->getRowvalue(4); //statuur
	$vn_Enregistreur	= 	$o_tab_parser->getRowvalue(5);

	// 5 velden overslaan
	$vn_bewaarplaats_id		=	$o_tab_parser->getRowvalue(10);
	$vn_eigenaar_persoon_id		=	$o_tab_parser->getRowvalue(11);
	$vn_beheerder_persoon_id	=	$o_tab_parser->getRowvalue(12);
	$vn_beheerder_instelling_id	=	$o_tab_parser->getRowvalue(13);
	$vn_eigenaar_instelling_id	=	$o_tab_parser->getRowvalue(14);
	
// ------------------------------------
// Waarden omvormen naar gepaste output
// ------------------------------------

if ($vn_Enregistreur == 1) {
	$vs_Titel = 'CRKC.'.$vs_Titel;
} elseif ($vn_Enregistreur == 2) {
	$vs_Titel = 'POV.'.$vs_Titel;
}elseif ($vn_Enregistreur == 3) {
	$vs_Titel = 'PA.'.$vs_Titel;
} 

// idno :
// Sam: Aangepast titel heeft nu de vorm AFKORTING_INSTELLING.NUMMER
$vs_Collectienummer = $vs_Titel;

//----------------		
// Find object
//----------------

	$t_entity = new ca_entities();
	$t_entity->setMode(ACCESS_WRITE);

	$va_right_id = $t_collectie->getCollectionIDsByidno($vs_Collectienummer);
	// print "right_id_1:".$va_right_id[0]."\n";

	if (sizeof($va_right_id) == 0) {
		print " ERROR: COLLECTIE {$vn_Collectie_id}/{$vs_Collectienummer} toevoegen \n";
		continue;
	} else {
		if (($vn_eigenaar_persoon_id != null) and ($vn_eigenaar_persoon_id != 0) and ($vn_eigenaar_persoon_id != 25)) {
			$va_Entity_keys = ($t_entity->getEntityIDsByidno('pers_'.$vn_eigenaar_persoon_id));
			$dimensions = sizeof($va_Entity_keys);
			print " {$dimensions} \n";	
			if ($dimensions > 0) {	
				$vn_Entity_key = $va_Entity_keys[0];
				// print "Primary Entity-key: ".$vn_Entity_key." \n";
				$t_entity->load($vn_Entity_key);
				$vn_left_id = $t_entity -> getPrimarykey();
				$t_entity->addRelationship('ca_collections', $va_right_id[0], $vn_entities_x_collections_id_eigenaar);
				
				if ($t_entity->numErrors()) {
					print "ERROR LINKING entity and collection : ".join('; ', $t_entity->getErrors())."\n";
					
				} else {
					print "link eigenaar-persoon-collection succesvol\n";
				}
			} else {
				print "Eigenaar-persoon {$vn_eigenaar_persoon_id} niet gevonden \n";
			}
		} 
		if (($vn_beheerder_persoon_id != null) and ($vn_beheerder_persoon_id != 0) and ($vn_beheerder_persoon_id != 25 )) {
			$va_Entity_keys = ($t_entity->getEntityIDsByidno('pers_'.$vn_beheerder_persoon_id));
			$dimensions = sizeof($va_Entity_keys);
			print " {$dimensions} \n";	
			if ($dimensions > 0) {	
				$vn_Entity_key = $va_Entity_keys[0];
				// print "Primary Entity-key: ".$vn_Entity_key." \n";
				$t_entity->load($vn_Entity_key);
				$vn_left_id = $t_entity -> getPrimarykey();
				$t_entity->addRelationship('ca_collections', $va_right_id[0], $vn_entities_x_collections_id_beheerder);
				
				if ($t_entity->numErrors()) {
					print "ERROR LINKING entity and collection : ".join('; ', $t_entity->getErrors())."\n";
					
				} else {
					print "link beheerder-persoon-collection succesvol\n";
				}
			} else {
				print "Beheerder-persoon {$vn_beheerder_persoon_id} niet gevonden \n";
			}
		}
		if (($vn_eigenaar_instelling_id != null) and ($vn_eigenaar_instelling_id != 0) and ($vn_eigenaar_instelling_id != 12 )) {
			$va_Entity_keys = ($t_entity->getEntityIDsByidno('org_'.$vn_eigenaar_instelling_id));
			$dimensions = sizeof($va_Entity_keys);
			print " {$dimensions} \n";
			if ($dimensions > 0) {	
				$vn_Entity_key = $va_Entity_keys[0];
				// print "Primary Entity-key: ".$vn_Entity_key." \n";
				$t_entity->load($vn_Entity_key);
				$vn_left_id = $t_entity -> getPrimarykey();
				$t_entity->addRelationship('ca_collections', $va_right_id[0], $vn_entities_x_collections_id_eigenaar);
				
				if ($t_entity->numErrors()) {
					print "ERROR LINKING entity and collection : ".join('; ', $t_entity->getErrors())."\n";
					
				} else {
					print "link eigenaar-instelling-collection succesvol\n";
				}
			} else {
				print "Eigenaar-instelling {$vn_eigenaar_instelling_id} niet gevonden \n";
			}
		}
		if (($vn_beheerder_instelling_id != null) and ($vn_beheerder_instelling_id != 0) and ($vn_beheerder_instelling_id != 12)) {
			$va_Entity_keys = ($t_entity->getEntityIDsByidno('org_'.$vn_beheerder_instelling_id));
			$dimensions = sizeof($va_Entity_keys);
			print " {$dimensions} \n";
			if ($dimensions > 0) {	
				$vn_Entity_key = $va_Entity_keys[0];
				// print "Primary Entity-key: ".$vn_Entity_key." \n";
				$t_entity->load($vn_Entity_key);
				$vn_left_id = $t_entity -> getPrimarykey();
				$t_entity->addRelationship('ca_collections', $va_right_id[0], $vn_entities_x_collections_id_beheerder);
				
				if ($t_entity->numErrors()) {
					print "ERROR LINKING entity and collection : ".join('; ', $t_entity->getErrors())."\n";
					
				} else {
					print "link beheerder-instelling-collection succesvol\n";
				}
			} else {
				print "Beheerder-instelling {$vn_beheerder_instelling_id} niet gevonden \n";
			}
		}
		print "{$vn_bewaarplaats_id}\n";
		if (($vn_bewaarplaats_id != null) and ($vn_bewaarplaats_id != 0)) {
			print "{$vs_Collectienummer}\n";
			$vn_collectie_key = $va_right_id[0];
			$t_collectie->load($vn_collectie_key);
			$vn_left_id = $t_collectie->getPrimaryKey();
			print "{$vn_left_id} \n";
			//$vs_Bewaarplaats_Naam = $va_Bewaarplaats[$vn_bewaarplaats_id];
			//print "{$vn_Bewaarplaats_Naam}\n";
			$vs_Bewaarplaats_Naam = $va_Bewaarplaats[$vn_bewaarplaats_id];
			print "{$vs_Bewaarplaats_Naam}\n";
			if (strlen(trim($vs_Bewaarplaats_Naam)) > 0 ) {
				print "nu zijn we hier";
				$va_right_id = $t_location->getLocationIDsByName(trim($vs_Bewaarplaats_Naam), null);
				$dims = sizeof($va_right_id);
				print "{$dims}\n";
				if ($dims > 0) {
					//print "right_id_1:".$va_right_id[0]."/".$vs_Bewaarplaats_Naam."\n";
					$t_collectie->addRelationship('ca_storage_locations', $va_right_id[0], $vn_collections_x_storage_locations_id);
					if ($t_collectie->numErrors()) {
						print "ERROR LINKING collection en bewaarplaats: ".join('; ', $t_collectie->getErrors())."\n";
						continue;
					} else {
						print "link collectie-storage location succesvol\n";
					}
				} else {
					print " ERROR: Storage Location {$vn_Bewaarplaats_id} bestaat niet \n";
				}
				
			} else {
				print "Bewaarplaats {$vn_bewaarplaats_id} niet gevonden \n";
			}
		}
	
	}

print "====={$vn_c}====={$vs_Collectienummer}=======Einde verwerking collectie ============================= \n \n";
	
$vn_c++;

unset($vs_Collectienummer);
		
}

print "gedaan";


?>
