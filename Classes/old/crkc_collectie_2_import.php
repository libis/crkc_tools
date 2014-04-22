<?php

/*
 * Step 1: Initialisation
 */
 
set_time_limit(36000);
require_once("/www/libis/html/ca_crkc/setup.php");

require_once(__CA_LIB_DIR__.'/core/Db.php');
require_once(__CA_MODELS_DIR__.'/ca_locales.php');
require_once(__CA_MODELS_DIR__.'/ca_collections.php');
require_once(__CA_MODELS_DIR__.'/ca_entities_x_collections.php');
require_once(__CA_MODELS_DIR__.'/ca_entities.php');

require_once(__CA_LIB_DIR__.'/core/Parsers/DelimitedDataParser.php');


$_ = new Zend_Translate('gettext', __CA_APP_DIR__.'/locale/en_US/messages.mo', 'nl_NL');

$t_locale = new ca_locales();
$pn_locale_id = $t_locale->loadLocaleByCode('nl_NL');

$t_list = new ca_lists();

// get relationship types
$t_rel_types = new ca_relationship_types();
$vn_entities_x_collections_id =	$t_rel_types->getRelationshipTypeID('ca_entities_x_collections', 'related');

print "relationship: {$vn_entities_x_collections_id} \n";

$t_entity = new ca_entities();

//***************************
//****   COLLECTIONS    *****
//***************************

print "IMPORTING collecties \n";

//
// Step 2: Import
//

 // want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t"); 

// Read csv; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_collectie_bis.csv')) {
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
	
print "\n Creating  ".$vn_Collection_id.".".$vs_Titel." - ".$vs_Omschrijving." and adding collection. \n"; 

// ------------------------------------
// Waarden omvormen naar gepaste output
// ------------------------------------

// idno :
// Sam: Aangepast titel heeft nu de vorm AFKORTING_INSTELLING.NUMMER


//collection type
/*
if (strpos($vs_Collectienummer, 'CRKC') !== false) {
	$pn_collection_type_id =	$t_list->getItemIDFromList('collection_types', 'internal');
} elseif (strpos($vs_Collectienummer, 'POV') !== false) {
	$pn_collection_type_id =	$t_list->getItemIDFromList('collection_types', 'povCollectie');
}elseif (strpos($vs_Collectienummer, 'PA') !== false) {
	$pn_collection_type_id =	$t_list->getItemIDFromList('collection_types', 'paCollectie');
} else {
	$pn_collection_type_id =	null;
	print "   - WARNING: geen Bron opgegeven voor {$vs_Collectienummer}, verbeter indien nodig \n";
}
*/

if ($vn_Enregistreur == 1) {
	$vs_Titel = 'CRKC.'.$vs_Titel;
	$pn_collection_type_id =	$t_list->getItemIDFromList('collection_types', 'internal');
} elseif ($vn_Enregistreur == 2) {
	$vs_Titel = 'POV.'.$vs_Titel;
	$pn_collection_type_id =	$t_list->getItemIDFromList('collection_types', 'povCollectie');
}elseif ($vn_Enregistreur == 3) {
	$vs_Titel = 'PA.'.$vs_Titel;
	$pn_collection_type_id =	$t_list->getItemIDFromList('collection_types', 'paCollectie');
}elseif ($vn_Enregistreur == 6) {
	$vs_Titel = 'PWV.'.$vs_Titel;
	$pn_collection_type_id =	$t_list->getItemIDFromList('collection_types', 'pwvCollectie');
} else {
	$pn_collection_type_id =	null;
	print "   - WARNING: geen Bron opgegeven voor {$vs_Collectienummer}, verbeter indien nodig \n";
}

// idno :
// Sam: Aangepast titel heeft nu de vorm AFKORTING_INSTELLING.NUMMER
$vs_Collectienummer = $vs_Titel;

// collectie statuut: privé (1) of publiek (0))
if ($vn_Public == 2) {
	$vn_Public_id = $t_list ->  getItemIDFromList('collectieStatuut', 'collectiePrive'); 
} else {
	$vn_Public_id = $t_list -> getItemIDFromList('collectieStatuut', 'collectiePubliek');
}	

//----------------		
// Create object
//----------------

	$t_collectie = new ca_collections();
	$t_collectie->setMode(ACCESS_WRITE);
	$t_collectie->set('locale_id', $pn_locale_id);
	$t_collectie->set('type_id', $pn_collection_type_id);
	$t_collectie->set('idno', $vs_Collectienummer);
	$t_collectie->set('source_id', 'i1');	
	$t_collectie->set('access', 1);	//1:Accessible to public, 0:Not accessible to public
	$t_collectie->set('status', 4);	//4:Completed (zie ca_xxx.php

//-------------------------
// Waarden mappen      
//-------------------------

	$t_collectie->addAttribute(array(
		'locale_id'	=>	$pn_locale_id,
		'name_singular'	=>	substr(trim($vs_Omschrijving),0,255),
		'name_plural'	=>	substr(trim($vs_Omschrijving),0,255)
	), 'name');

	$t_collectie->addAttribute(array(
		'locale_id'				=>	$pn_locale_id,
		'collectie_algemeneBeschrijving'	=>	$vs_Omschrijving
	), 'collectie_algemeneBeschrijving');

	if (isset($vn_Public_id)) {
		$t_collectie->addAttribute(array(
			'locale_id'			=>	$pn_locale_id,
			'juridischStatuutCollectie'	=>	$vn_Public_id
		), 'juridischStatuutCollectie');
	}
	
	// insert the object
	$t_collectie->insert();

	if ($t_collectie->numErrors()) {
		print "\tERROR INSERTING ".$vn_Collection_id.".".$vs_Titel." - ".$vs_Omschrijving.join('; ', $t_collectie->getErrors())."\n";
		continue;
	} else {
		print "insert succesvol\n";
	}
	
	// --------------------------------------------------------------
	// Set a preferred label for the object
	// --------------------------------------------------------------
		
	$t_collectie->addLabel(
		array('name' => substr($vs_Omschrijving,0,255)),
		$pn_locale_id, null, true
	);
		
	if ($t_collectie->numErrors()) {
		print "\tERROR ADD LABEL TO ".$vn_Collection_id.".".$vs_Titel." - ".$vs_Omschrijving.join('; ', $t_collectie->getErrors())."\n";
		continue;
	} else {
		print "addLabel succesvol\n";
	}
//
// mogelijk moeten we hier nu nog een aantal relaties leggen
	
//	$va_left_id = ($t_collectie -> getCollectionIDsByName(substr($vs_Collectienummer,0,99) ) );
//	print "left_id:".$va_left_id[0]."\n";
//	$t_collectie->set('collection_id', $va_left_id[0]);


/*
	$vn_left_id = $t_collectie -> getPrimarykey();
	print "left_id:".$vn_left_id."\n";
	$t_collectie->set('collection_id', $vn_left_id);
	
	
if (($vn_eigenaar_persoon_id) <> '0') {
	if (($vn_eigenaar_persoon_id) <> '25') {
		$va_right_id = $t_entity->getEntityIDsByidno('pers_'.(($vn_eigenaar_persoon_id) ) );
		print "right_id_1:".$va_right_id[0]."\n";
		if ($va_right_id == NULL) {
			print " ENTITY {$vn_eigenaar_persoon_id} toevoegen \n";
		}else{
			$t_collectie -> addRelationship('ca_entities', $va_right_id[0], $vn_entities_x_collections_id);
			print "done_1\n";
			if ($t_collectie->numErrors()) {
				print "ERROR LINKING collection and Entity : ".join('; ', $t_collectie->getErrors())."\n";
				continue;
			} else {
				print "relatie succesvol\n";
			}
		}
	}
}
	
if (($vn_beheerder_persoon_id) <> '0') {
	if (($vn_beheerde_persoon_id) <> '25' ) {
		$va_right_id = $t_entity->getEntityIDsByidno('pers_'.($vn_beheerder_persoon_id) );
		print "right_id_2:".$va_right_id[0]."\n";
		if ($va_right_id == NULL) {
			print " ENTITY {$vn_beheerder_persoon_id} toevoegen \n";
		}else{
			$t_collectie -> addRelationship('ca_entities', $va_right_id[0], $vn_entities_x_collections_id);
			print "done_2\n";
			if ($t_collectie->numErrors()) {
				print "ERROR LINKING collection and Entity : ".join('; ', $t_collectie->getErrors())."\n";
				continue;
			} else {
				print "relatie succesvol\n";
			}
		}
	}
}
	
if (($vn_beheerder_instelling_id) <> '0') {
	if (($vn_beheerder_instelling_id) <> '12' ) {
		$va_right_id = $t_entity->getEntityIDsByidno('org_'.(($vn_beheerder_instelling_id) ) );
		print "right_id_3:".$va_right_id[0]."\n";
		if ($va_right_id == NULL) {
			print " ENTITY {$vn_beheerder_instelling_id} toevoegen \n";
		}else{
			$t_collectie -> addRelationship('ca_entities', $va_right_id[0], $vn_entities_x_collections_id);
			print "done_3\n";
			if ($t_collectie->numErrors()) {
				print "ERROR LINKING collection and Entity : ".join('; ', $t_collectie->getErrors())."\n";
				continue;
			}
		}
	}
}
	
if (($vn_eigenaar_instelling_id) <> '0') {
	if (($vn_eigenaar_instelling_id) <> '12' ) {
		$va_right_id = $t_entity->getEntityIDsByidno('org_'.(($vn_eigenaar_instelling_id) ) );
		print "right_id_4:".$va_right_id[0]."\n";
		if ($va_right_id == NULL) {
			print " ENTITY {$vn_eigenaar_instelling} toevoegen \n";
		}else{
			$t_collectie -> addRelationship('ca_entities', $va_right_id[0], $vn_entities_x_collections_id);
			print "done_4\n";
			if ($t_collectie->numErrors()) {
				print "ERROR LINKING collection and Entity : ".join('; ', $t_collectie->getErrors())."\n";
				continue;
			} else {
				print "relatie succesvol\n";
			}
		}
	}
}
*/

unset($vn_Colletienummer);
unset($vn_Public_id);
$vn_c++;
		
		print "=================Einde verwerking collectie ============================= \n \n";
	}

print "gedaan";
?>
