<?php
define('__CA_DONT_DO_SEARCH_INDEXING__',true);

require_once("/www/libis/html/ca_crkc/setup.php");
$_SERVER['HTTP_HOST'] = 'localhost';

require_once(__CA_LIB_DIR__.'/core/Db.php');
require_once(__CA_MODELS_DIR__.'/ca_locales.php');
require_once(__CA_MODELS_DIR__.'/ca_objects.php');
require_once(__CA_MODELS_DIR__.'/ca_collections.php');
require_once(__CA_MODELS_DIR__.'/ca_entities.php');
require_once(__CA_MODELS_DIR__.'/ca_places.php');
require_once(__CA_MODELS_DIR__.'/ca_lists.php');
require_once(__CA_MODELS_DIR__.'/ca_storage_locations.php');
require_once(__CA_MODELS_DIR__.'/ca_objects_x_collections.php');
require_once(__CA_MODELS_DIR__.'/ca_objects_x_places.php');
require_once(__CA_MODELS_DIR__.'/ca_objects_x_storage_locations.php');
require_once(__CA_MODELS_DIR__.'/ca_objects_x_entities.php');
require_once(__CA_MODELS_DIR__.'/ca_objects_x_objects.php');
//require_once(__CA_MODELS_DIR__.'/ca_sets.php');

require_once(__CA_LIB_DIR__.'/core/Parsers/DelimitedDataParser.php');
	
$_ = new Zend_Translate('gettext', __CA_APP_DIR__.'/locale/en_US/messages.mo', 'nl_NL');

$t_locale = new ca_locales();
$pn_locale_id = $t_locale->loadLocaleByCode('nl_NL');		// default locale_id

$t_list = new ca_lists(); 
$vn_Set_Type_id	= $t_list->getItemIDFromList('set_types', 'public_presentation');

// object-type en source
$pn_object_type_id = 	$t_list->getItemIDFromList('object_types', 'crkcObject');
$pn_object_source_id = 	$t_list->getItemIDFromList('object_sources', 'crkcCollectie');

// relaties
$t_rel_types = new ca_relationship_types();

//TODO - info uit veld halen
// relatie object-object
$vn_objects_x_objects_id = $t_rel_types->getRelationshipTypeID('ca_objects_x_objects', 'related');
print "relatie object-object: ".$vn_objects_x_objects_id."\n";

// relatie object-entities (beheerder)
$vn_objects_x_beheerder_id = $t_rel_types->getRelationshipTypeID('ca_objects_x_entities', 'beheerderRelatie');
print "relatie object-beheerder: ".$vn_objects_x_beheerder_id."\n";

// relatie object-entities (eigenaar)
$vn_objects_x_eigenaar_id = $t_rel_types->getRelationshipTypeID('ca_objects_x_entities', 'eigenaarRelatie');
print "relatie object-eigenaar: ".$vn_objects_x_eigenaar_id."\n";

$t_entity = new ca_entities();

// relatie object-bewaarplaats
$vn_objects_x_location_id = $t_rel_types->getRelationshipTypeID('ca_objects_x_storage_locations', 'related');
print "relatie object-occurrence: ".$vn_objects_x_location_id."\n";
$t_location = new ca_storage_locations();

// aanmaken sets
$t_web = new ca_sets();
$t_web->load(array('set_code' => 'PubliceerOpWeb'));
$t_web->setMode(ACCESS_WRITE);
$vn_web_id = $t_web->getPrimaryKey();

$t_VenA = new ca_sets();
$t_VenA->load(array('set_code' => 'VraagenAanbod'));
$t_VenA->setMode(ACCESS_WRITE);
$vn_VenA_id = $t_VenA->getPrimaryKey();

// objecten
$t_object = new ca_objects();
$t_object->setMode(ACCESS_WRITE);

//***************************
//****   Kunstvoorwerp  *****
//***************************

// want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t"); 

print "IMPORTING kunstvoorwerp_has_typerelatietotobject into an array \n";

// Read ; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_kunstvoorwerp_has_typerelatietotobject.csv')) {
	die("Couldn't parse new_kunstvoorwerp_has_typerelatietotobject. data\n");	
}

$vn_c = 1;
	
$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------

while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_Id			=	$o_tab_parser->getRowValue(1); 
	$vn_TypeRelatie_id 	=	$o_tab_parser->getRowValue(2); 
	$vn_HuidigeRelatie_id	=	$o_tab_parser->getRowvalue(5); // bevat waarden 0 of 1 
	
	$va_TypeRelatie[$vn_Id] = array($vn_TypeRelatie_id, $vn_HuidigeRelatie_id);

	$vn_c++;
}

print "IMPORTING bewaarplaats_bis into an array \n";

// Read ; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_bewaarplaats_bis.csv')) {
	die("Couldn't parse new_bewaarplaats_bis. data\n");	
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


//
// Step 2: Import
//


print "IMPORTING kunstvoorwerpen \n";
	
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_kunstvoorwerpen.csv')) {
	die("Couldn't parse Kunstvoorwerpen data\n");	
}
	
$vn_c = 1;
	
$o_tab_parser->nextRow(); // skip first row

//-------------------------
// waarden inlezen
//-------------------------
	
while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
		$vn_Kunstvoorwerp_id		=	$o_tab_parser->getRowValue(1);
		$vn_VenAStatus			=	$o_tab_parser->getRowValue(2); // voor aanmaken set			
//		$vn_Collectie_id		=	$o_tab_parser->getRowValue(3);			
//		$vn_TypeToestanden_id		=	$o_tab_parser->getRowValue(4);			
//		$vn_Stijlen_id			=	$o_tab_parser->getRowValue(5);			
//		$vn_Plaats_id 			=	$o_tab_parser->getRowValue(6);			
//		$vs_Identificatie		=	$o_tab_parser->getRowValue(7);			
//		$vs_Omschrijving		=	$o_tab_parser->getRowValue(8);			
		$vs_crkcObjectnr		=	$o_tab_parser->getRowValue(9);
//		$vs_HistoriekOpenbaar		=	$o_tab_parser->getRowValue(10);
//		$vs_Titel			=	$o_tab_parser->getRowValue(11);
//		$vn_AantalExemplaren		=	$o_tab_parser->getRowValue(12);
//		$vn_AantalOnderdelen		=	$o_tab_parser->getRowValue(13);
//		$vs_Signatuur			=	$o_tab_parser->getRowvalue(14);
//		$vs_Opschriften			=	$o_tab_parser->getRowvalue(15);
//		$vs_Merken			=	$o_tab_parser->getRowvalue(16);
//		$vs_Beschrijving		=	$o_tab_parser->getRowvalue(17);
//		$vs_InventarisatieDatum		=	$o_tab_parser->getRowvalue(18);
//		$vs_RecordCreationDate		=	$o_tab_parser->getRowvalue(19);
//		$vd_RecordUpdateDate		=	$o_tab_parser->getRowvalue(20);
//		$vs_ToestandVoorwerp		=	$o_tab_parser->getRowvalue(21);
//		$vs_TeRestaureren		=	$o_tab_parser->getRowvalue(22);
//		$vs_ArchiefenBronnen		=	$o_tab_parser->getRowvalue(23);
//		$vs_OpmerkingGesloten		=	$o_tab_parser->getRowvalue(24);
//		$vs_OpmerkingOpenbaar		=	$o_tab_parser->getRowvalue(25);
//		$vs_ObjectNaam_id		=	$o_tab_parser->getRowvalue(26);
//		$vs_Datum			=	$o_tab_parser->getRowvalue(27);
		$vs_AssociatieenRelaties	=	$o_tab_parser->getRowvalue(28); // gerelateerde objecten
//		$vn_Public			=	$o_tab_parser->getRowvalue(29);
		$vn_crkcLocaties_id		=	$o_tab_parser->getRowvalue(30);
		$vn_EigenaarInstelling_id	=	$o_tab_parser->getRowvalue(31);
		$vn_EigenaarPersoon_id		=	$o_tab_parser->getRowvalue(32);
//		$vs_Verwerving			=	$o_tab_parser->getRowvalue(33);
		$vn_PubliceeropWeb		=	$o_tab_parser->getRowvalue(34); // set
		$vn_BeschikbaarvoorObject	=	$o_tab_parser->getRowvalue(35); // set
//		$vs_Ontsluitingstekst		=	$o_tab_parser->getRowvalue(36);
//		$vs_OudInventarisNr		=	$o_tab_parser->getRowvalue(37);
		$vn_BewaarplaatsInstelling_id	=	$o_tab_parser->getRowvalue(38);
		$vn_BewaarplaatsTypeBevinden_id	=	$o_tab_parser->getRowvalue(39);
		$vs_BewaarplaatsCommentaar	=	$o_tab_parser->getRowvalue(40);
//		$vn_ThesaurusTermObnectNaam_id	=	$o_tab_parser->getRowvalue(41);
//		$vs_Foto			=	$o_tab_parser->getRowvalue(42);
		$vn_BeheerderInstelling_id	=	$o_tab_parser->getRowvalue(43);
		$vn_BeheerderPersoon_id		=	$o_tab_parser->getRowvalue(44);
//		$vs_Registrator			=	$o_tab_parser->getRowvalue(50);
		$vn_Bewaarplaats_id		=	$o_tab_parser->getRowvalue(51);
//		$vs_Standplaats			=	$o_tab_parser->getRowvalue(54);
		
print "====={$vn_c}===== PROCESSING {$vn_Kunstvoorwerp_id}  / {$vs_crkcObjectnr} \n";

// opzoeken record
               
	$va_Kunstvoorwerp_keys = ($t_object->getObjectIDsByidnoPart(("%KV_".$vn_Kunstvoorwerp_id.")"),null) );
	$dimensions = sizeof($va_Kunstvoorwerp_keys);
	// print "{$dimensions}\n";
		
	if ($dimensions > 0) {
	
		$vn_Kunstvoorwerp_key = $va_Kunstvoorwerp_keys[0];
		//print "Primary Kunstvoorwerp-key: ".$vn_Kunstvoorwerp_key." \n";
		$t_object->load($vn_Kunstvoorwerp_key);
		// Primary key opvragen
		$vn_left_id = $t_object->getPrimaryKey();
		
//******************************************************************************
// Wat moet allemaal uitgevoerd worden
//******************************************************************************

$vb_EigenaarInstelling = false;
$vb_EigenaarPersoon = false;
$vb_BeheerderInstelling = false;
$vb_BeheerderPersoon = false;
$vb_TypeVanZichBevinden = false;
$vb_TypeRelatieTotObject = false;
$vb_BewaarplaatsCommentaar = false;
$vb_Bewaarplaats_Extern = false;
$vb_Bewaarplaats_Intern = false;

// pers_25 = Nihil

if (($vn_EigenaarInstelling_id != null) and ($vn_EigenaarInstelling_id != 0)) {
	print "EigenaarInstelling bevat een geldige waarde\n";
	$vb_EigenaarInstelling = true;
}
if (($vn_EigenaarPersoon_id != null) and ($vn_EigenaarPersoon_id != 0) and ($vn_EigenaarPersoon_id) != 25) {
	print "EigenaarPersoon bevat een geldige waarde \n";
	$vb_EigenaarPersoon = true;
}

if (($vn_BeheerderInstelling_id != null) and ($vn_BeheerderInstelling_id != 0)) {
	print "BeheerderInstelling bevat een geldige waarde\n";
	$vb_BeheerderInstelling = true;
}
if (($vn_BeheerderPersoon_id != null) and ($vn_BeheerderPersoon_id != 0) and ($vn_BeheerderPersoon_id != 25)) {
	print "BeheerderPersoon bevat een geldige waarde \n";
	$vb_BeheerderPersoon = true;
}


if (($vn_BewaarplaatsTypeBevinden_id != null) and ($vn_BewaarplaatsTypeBevinden_id != 0)) {
	$vb_TypeVanZichBevinden = true;
}

if (($vn_Bewaarplaats_id != null) and ($vn_Bewaarplaats_id != 0)) {
	print "Externe Bewaarplaats bevat een geldige waarde \n";
	$vb_Bewaarplaats_Extern = true;
}

if (($vn_crkcLocaties_id != null) and ($vn_crkcLocaties_id != 0)) {
	print "Interne Bewaarplaats bevat een geldige waarde \n";
	$vb_Bewaarplaats_Intern = true;
}


if ((strlen(trim($vs_BewaarplaatsCommentaar)) != 0 )  and (trim($vs_BewaarplaatsCommentaar) != '0' )) {
	print "BewaarplaatsCommentaar bevat een geldige waarde \n";
	$vb_BewaarplaatsCommentaar = true;
}

$va_Term_Waarde = $va_TypeRelatie[$vn_Kunstvoorwerp_id];
$aantal = sizeof($va_Term_Waarde);

if ($aantal>0) {
	$vn_TypeRelatieTotObject_id = $va_Term_Waarde[0];
	$vn_HuidigeRelatieTotObject_id = $va_Term_Waarde[1];
	$vb_TypeRelatieTotObject = true;	
}

//******************************************************************************
// De uitvoering zelf
//******************************************************************************

if (($vb_EigenaarInstelling) or ($vb_EigenaarPersoon)) {
	if ($vb_EigenaarInstelling) {
		$va_right_id = $t_entity->getEntityIDsByidno('org_'.($vn_EigenaarInstelling_id));
		$dims = sizeof($va_right_id);
		if ($dims > 0) {
			$t_entity->load($va_right_id[0]);
			$t_entity->getPrimaryKey();
			$vs_Naam_EigenaarInstelling = $t_entity->getLabelForDisplay();
			//print "right_id_1:".$va_right_id[0]."/".$vs_Naam_EigenaarInstelling."\n";
			$t_object -> addRelationship('ca_entities', $va_right_id[0], $vn_objects_x_eigenaar_id);
			if ($t_object->numErrors()) {
				print "ERROR LINKING object {$vs_crkcObjectnr} and entity Eigenaar Instelling : ".join('; ', $t_object->getErrors())."\n";
				continue;
			} else {
				print "link object-eigenaar-instelling succesvol\n";
			}
		} else {
			print " ERROR: INSTELLING {$vn_EigenaarInstelling_id} toevoegen \n";
		}
	}
	if ($vb_EigenaarPersoon) {
		$va_right_id = $t_entity->getEntityIDsByidno('pers_'.($vn_EigenaarPersoon_id));
		$dims = sizeof($va_right_id);
		if ($dims > 0) {
			$t_entity->load($va_right_id[0]);
			$t_entity->getPrimaryKey();
			$vs_Naam_EigenaarPersoon = $t_entity->getLabelForDisplay();
			//print "right_id_1:".$va_right_id[0]."/".$vs_Naam_EigenaarPersoon."\n";
			$t_object -> addRelationship('ca_entities', $va_right_id[0], $vn_objects_x_eigenaar_id);
			if ($t_object->numErrors()) {
				print "ERROR LINKING {$vs_crkcObjectnr} object and entity Eigenaar Persoon : ".join('; ', $t_object->getErrors())."\n";
				continue;
			} else {
				print "link object-eigenaar-persoon succesvol\n";
			}
		} else {
			print " ERROR: PERSOON {$vn_EigenaarPersoon_id} toevoegen \n";
		}
	}
	$vs_EigenaarInstelling = $vs_Naam_EigenaarInstelling;
	$vs_EigenaarPersoon = $vs_Naam_EigenaarPersoon;
	$vn_HuidigeStatusEigenaar = $t_list->getItemIDFromList('HuidigeStatus_list', 'yes');
	$vn_StatuutStatusEigenaar = $t_list->getItemIDFromList('object_eigenaar_statuut', 'blank');
	$vs_StatuutOpmerking_Eigenaar = null; // $vs-BewaarplaatsCommentaar
}


if (($vb_BeheerderInstelling) or ($vb_BeheerderPersoon)) {
	if ($vb_BeheerderInstelling) {
		$va_right_id = $t_entity->getEntityIDsByidno('org_'.($vn_BeheerderInstelling_id));
		$dims = sizeof($va_right_id);
		if ($dims > 0) {
			$t_entity->load($va_right_id[0]);
			$t_entity->getPrimaryKey();
			$vs_Naam_BeheerderInstelling = $t_entity->getLabelForDisplay();
			//print "right_id_1:".$va_right_id[0]."/".$vs_Naam_BeheerderInstelling."\n";
			$t_object -> addRelationship('ca_entities', $va_right_id[0], $vn_objects_x_beheerder_id);
			if ($t_object->numErrors()) {
				print "ERROR LINKING object {$vs_crkcObjectnr} and entity Beheerder Instelling : ".join('; ', $t_object->getErrors())."\n";
				continue;
			} else {
				print "link object-Beheerder-instelling succesvol\n";
			}
		} else {
			print " ERROR: INSTELLING {$vn_BeheerderInstelling_id} toevoegen \n";
		}
	}
	if ($vb_BeheerderPersoon) {
		$va_right_id = $t_entity->getEntityIDsByidno('pers_'.($vn_BeheerderPersoon_id));
		$dims = sizeof($va_right_id);
		if ($dims > 0) {
			$t_entity->load($va_right_id[0]);
			$t_entity->getPrimaryKey();
			$vs_Naam_BeheerderPersoon = $t_entity->getLabelForDisplay();
			//print "right_id_1:".$va_right_id[0]."/".$vs_Naam_BeheerderPersoon."\n";
			$t_object -> addRelationship('ca_entities', $va_right_id[0], $vn_objects_x_beheerder_id);
			if ($t_object->numErrors()) {
				print "ERROR LINKING object {$vs_crkcObjectnr} and entity Beheerder Persoon : ".join('; ', $t_object->getErrors())."\n";
				continue;
			} else {
				print "link object-Beheerder-persoon succesvol\n";
			}
		} else {
			print " ERROR: PERSOON {$vn_BeheerderPersoon_id} toevoegen \n";
		}
	}
	$vs_BeheerderInstelling = $vs_Naam_BeheerderInstelling;
	$vs_BeheerderPersoon = $vs_Naam_BeheerderPersoon;
	$vn_HuidigeStatusBeheerder = $t_list->getItemIDFromList('HuidigeStatus_list', 'yes');
	$vn_StatuutStatusBeheerder = $t_list->getItemIDFromList('object_beheerder_statuut', 'blank');
	$vs_StatuutOpmerking_Beheerder = null; // $vs-BewaarplaatsCommentaar
}

// nu gaan we de invloed van TypeVanZichBevinden toevoegen

if ($vb_TypeVanZichBevinden) {
	if (($vn_BewaarplaatsTypeBevinden_id) == 1 ) {
		// 1 = in bruikleen -> Bij Beheerder Status : drop-down Verwervingsmethode op 'in langdurige bruikleen'
		if ($vn_StatuutStatusBeheerder) {
			$vn_StatuutStatusBeheerder = $t_list->getItemIDFromList('object_beheerder_statuut', 'objectInBruikleen');
		} else {
			print "WARNING: Er is geen object-beheerder-relatie\n";
		}
	} elseif (($vn_BewaarplaatsTypeBevinden_id) == 2 ) {
		// 2 = bij eigenaar -> bewaarplaatscommentaar bij statuutstatusEigenaar  en drop down op blank -> niks te doen
		if ($vn_StatuutStatusEigenaar) {
			$vn_StatuutStatusEigenaar = $t_list->getItemIDFromList('object_eigenaar_statuut', 'blank');
		} else {
			print "WARNING: Er is geen object-eigenaar-relatie\n";
		}
	} elseif (($vn_BewaarplaatsTypeBevinden_id) == 3 ) {
		// 3 = in bewaring -> Bij Beheerder Status : drop-down Verwervingsmethode op 'in bewaring'
		if ($vn_StatuutStatusBeheerder) {
			$vn_StatuutStatusBeheerder = $t_list->getItemIDFromList('object_beheerder_statuut', 'objectInBewaring');
		} else {
			print "WARNING: Er is geen object-beheerder-relatie\n";
		}

	} elseif (($vn_BewaarplaatsTypeBevinden_id) == 4 ) {
		// 4 = in restauratie -> Bij Beheerder Status : drop-down Verwervingsmethode op '-'
		// In Opmerkingveld: tekst 'In restauratie opnemen'
		if ($vn_StatuutStatusBeheerder) {
			$vn_StatuutStatusBeheerder = $t_list->getItemIDFromList('object_beheerder_statuut', 'blank');
			$vs_StatuutOpmerking_Beheerder = 'In restauratie';
		} else {
			print "WARNING: Er is geen object-beheerder-relatie\n";
		}
	
	} elseif (($vn_BewaarplaatsTypeBevinden_id) == 5 ) {
		// 5 = op tentoonstelling -> Bij tentoonstelling Opmerking: 'Op tentoonstelling' + inhoud veld BewaarPlaatsCommentaar
		if (!($vb_BewaarplaatsCommentaar)) {
			$vs_Tentoonstelling_Opmerking = "Op tentoonstelling";
		} else {
			$vs_Tentoonstelling_Opmerking = "Op tentoonstelling - ".$vs_BewaarplaatsCommentaar;
		}
		$t_object->addAttribute(array(
			'locale_id'			=>	$pn_locale_id,
			'tentoonstellingOpmerking'	=>	$vs_Tentoonstelling_Opmerking
		), 'tentoonstellingOpmerking');
	
	} elseif (($vn_BewaarplaatsTypeBevinden_id) == 6 ) {
		// bij beheerder -> bewaarplaatscommentaar bij statuutstatusBeheerder en drop down op blank
		if ($vn_StatuutStatusBeheerder) {
			$vn_StatuutStatusBeheerder = $t_list->getItemIDFromList('object_beheerder_statuut', 'blank');
		} else {
			print "WARNING: Er is geen object-beheerder-relatie\n";
		}
	}
}

if ($vb_BewaarplaatsCommentaar) {
	if (($vb_EigenaarInstelling) or ($vb_EigenaarPersoon)) {
		if (strlen(trim($vs_StatuutOpmerking_Eigenaar)) == 0 ) {
			$vs_StatuutOpmerking_Eigenaar = $vs_BewaarplaatsCommentaar;
		} else {
			$vs_StatuutOpmerking_Eigenaar = $vs_StatuutOpmerking_Eigenaar." - ".$vs_BewaarplaatsCommentaar;
		}
	}
	if (($vb_BeheerderInstelling) or ($vb_BeheerderPersoon)) {
		if (strlen(trim($vs_StatuutOpmerking_Beheerder)) == 0 ) {
			$vs_StatuutOpmerking_Beheerder = $vs_BewaarplaatsCommentaar;
		} else {
			$vs_StatuutOpmerking_Beheerder = $vs_StatuutOpmerking_Eigenaar." - ".$vs_BewaarplaatsCommentaar;
		}
	}
	
	$t_object->addAttribute(array(
		'locale_id'			=>	$pn_locale_id,
		'objectBewaarplaatsOpmerking'	=>	trim($vs_BewaarplaatsCommmentaar)
	), 'objectBewaarplaatsOpmerking');

}

if ($vb_Bewaarplaats_Extern) {
	
	$vs_Bewaarplaats_Naam = $va_Bewaarplaats[$vn_Bewaarplaats_id];
	
	if (strlen(trim($vs_Bewaarplaats_Naam)) != 0 ) {
		$va_right_id = $t_location->getLocationIDsByName(trim($vs_Bewaarplaats_Naam), null);
		$dims = sizeof($va_right_id);
		if ($dims > 0) {
			//print "right_id_1:".$va_right_id[0]."/".$vs_Bewaarplaats_Naam."\n";
			$t_object -> addRelationship('ca_storage_locations', $va_right_id[0], $vn_objects_x_location_id);
			if ($t_object->numErrors()) {
				print "ERROR LINKING object {$vs_crkcObjectnr} and storage location : ".join('; ', $t_object->getErrors())."\n";
				continue;
			} else {
				print "link object-storage location succesvol\n";
			}
		} else {
			print " ERROR: Storage Location {$vn_Bewaarplaats_id} bestaat niet \n";
		}
	}
	
}

if ($vb_Bewaarplaats_Intern) {
	
	$vs_Bewaarplaats_Naam = $va_Bewaarplaats[$vn_Bewaarplaats_id];
	
	if (strlen(trim($vs_Bewaarplaats_Naam)) != 0 ) {
		$va_right_id = $t_location->getLocationIDsByName(trim($vs_Bewaarplaats_Naam), null);
		$dims = sizeof($va_right_id);
		if ($dims > 0) {
			//print "right_id_1:".$va_right_id[0]."/".$vs_Bewaarplaats_Naam."\n";
			$t_object -> addRelationship('ca_storage_locations', $va_right_id[0], $vn_objects_x_location_id);
			if ($t_object->numErrors()) {
				print "ERROR LINKING object {$vs_crkcObjectnr} and storage location : ".join('; ', $t_object->getErrors())."\n";
				continue;
			} else {
				print "link object-storage location succesvol\n";
			}
		} else {
			print " ERROR: Storage Location {$vn_Bewaarplaats_id} bestaat niet \n";
		}
	}
}

$vb_Eigenaar_2 = false;
$vb_Beheerder_2 = false;

if ($vb_TypeRelatieTotObject) {
	
	$va_right_id = $t_entity->getEntityIDsByidno('org_3');
	$dims = sizeof($va_right_id);
	if ($dims > 0) {
		$t_entity->load($va_right_id[0]);
		$t_entity->getPrimaryKey();
		$vs_Naam_HuidigeEigenaar = $t_entity->getLabelForDisplay();
		$vs_Naam_HuidigeBeheerder = $t_entity->getLabelForDisplay();
		//print "right_id_1:".$va_right_id[0]."/".$vs_Naam_HuidigeEigenaar."\n";
	} else {
		print " ERROR: INSTELLING org_3 (CRKC) toevoegen \n";
	}

	if (($vn_TypeRelatieTotObject_id == 5 ) and ($vn_HuidigeRelatieTotObject_id == 1)) {
		if (($vb_EigenaarInstelling) or ($vb_EigenaarPersoon)) {
			$vn_HuidigeStatusEigenaar = $t_list->getItemIDFromList('HuidigeStatus_list', 'no');
		}
		$t_object -> addRelationship('ca_entities', $va_right_id[0], $vn_objects_x_eigenaar_id);
		if ($t_object->numErrors()) {
			print "ERROR LINKING object {$vs_crkcObjectnr} and entity Eigenaar Instelling : ".join('; ', $t_object->getErrors())."\n";
			continue;
		} else {
			print "link object-huidige-eigenaar-instelling succesvol\n";
		}
		$vb_Eigenaar_2 = true;
		$vs_EigenaarInstelling_2 = $vs_Naam_HuidigeEigenaar;
		$vn_HuidigeStatusEigenaar_2 = $t_list->getItemIDFromList('HuidigeStatus_list', 'yes');
		$vn_StatuutStatusEigenaar_2 = $t_list->getItemIDFromList('object_eigenaar_statuut', 'blank');
		$vs_StatuutOpmerking_Eigenaar_2 = ''; 
	}
	
	if (($vn_TypeRelatieTotObject_id == 5 ) and ($vn_HuidigeRelatieTotObject_id == 0)) {
		$vb_Eigenaar_2 = true;
		$vs_EigenaarInstelling_2 = "Vorige eigenaar ".$vs_Naam_HuidigeEigenaar;
		$vn_HuidigeStatusEigenaar_2 = $t_list->getItemIDFromList('HuidigeStatus_list', 'no');
		$vn_StatuutStatusEigenaar_2 = $t_list->getItemIDFromList('object_eigenaar_statuut', 'blank');
		$vs_StatuutOpmerking_Eigenaar_2 = ''; 
	}
	
	if (($vn_TypeRelatieTotObject_id == 2 ) and ($vn_HuidigeRelatieTotObject_id == 1)) {
		if (($vb_BeheerderInstelling) or ($vb_BeheerderPersoon)) {
			$vn_HuidigeStatusBeheerder = $t_list->getItemIDFromList('HuidigeStatus_list', 'no');
		}
		$t_object -> addRelationship('ca_entities', $va_right_id[0], $vn_objects_x_beheerder_id);
		if ($t_object->numErrors()) {
			print "ERROR LINKING object {$vs_crkcObjectnr} and entity Beheerder Instelling : ".join('; ', $t_object->getErrors())."\n";
			continue;
		} else {
			print "link object-huidige-beheerder-instelling succesvol\n";
		}
		$vb_Beheerder_2 = true;
		$vs_BeheerderInstelling_2 = $vs_Naam_HuidigeBeheerder;
		$vn_HuidigeStatusBeheerder_2 = $t_list->getItemIDFromList('HuidigeStatus_list', 'yes');
		$vn_StatuutStatusBeheerder_2 = $t_list->getItemIDFromList('object_beheerder_statuut', 'objectInBruikleen');
		$vs_StatuutOpmerking_Beheerder_2 = "Vorige beheerder ".$vs_Naam_HuidigeBeheerder; 
	}
	if ($vn_TypeRelatieTotObject_id == 3 )  {  //Afgestoten
		if (($vb_EigenaarInstelling) or ($vb_EigenaarPersoon)) {
			$vn_HuidigeStatusEigenaar = $t_list->getItemIDFromList('HuidigeStatus_list', 'no');
			$vn_StatuutStatusEigenaar = $t_list->getItemIDFromList('object_eigenaar_statuut', 'blank');
		}
		$vn_AfstotingType_id = $t_list->getItemIDFromList('object_afgestoten_lijst', 'blank');
		$t_object->addAttribute(array(
			'locale_id'		=>	$pn_locale_id,
			'afstotingType'		=>	$vn_AfstotingType_id,
			'afstotingReden'	=>	"Afgestoten"
		), 'afgestotenInfo');
	}
	if ($vn_TypeRelatieTotObject_id == 1 )  {  //Niet in Eigendom CRKC
		$vn_AfstotingType_id = $t_list->getItemIDFromList('object_afgestoten_lijst', 'blank');
		$t_object->addAttribute(array(
			'locale_id'		=>	$pn_locale_id,
			'afstotingType'		=>	$vn_AfstotingType_id
//			'afstotingReden'	=>	""
		), 'afgestotenInfo');
	}
}

if (($vb_EigenaarInstelling) or ($vb_EigenaarPersoon)) {
	$t_object->addAttribute(array(
		'locale_id'			=>	$pn_locale_id,
		'eigenaarInstelling'		=>	$vs_EigenaarInstelling,
		'eigenaarPersoon'		=>	$vs_EigenaarPersoon,
		'statuutStatusEigenaar'		=>	$vn_StatuutStatusEigenaar,
//		'statusBeheerderDatum'		=>	$vs_Opmerking,
		'huidigeStatusEigenaar'		=>	$vn_HuidigeStatusEigenaar,	
		'statuutEigenaarOpmerking'	=>	$vs_StatuutOpmerking_Eigenaar
	), 'statuutStatusEigenaarInfo');
}

if (($vb_BeheerderInstelling) or ($vb_BeheerderPersoon)) {
	$t_object->addAttribute(array(
		'locale_id'			=>	$pn_locale_id,
		'beheerderInstelling'		=>	$vs_BeheerderInstelling,
		'beheerderPersoon'		=>	$vs_BeheerderPersoon,
		'statuutStatusBeheerder'	=>	$vn_StatuutStatusBeheerder,
//		'statusBeheerderDatum'		=>	$vs_Opmerking,
		'huidigeStatusBeheerder'	=>	$vn_HuidigeStatusBeheerder,
		'statuutBeheerderOpmerking'	=>	$vs_StatuutOpmerking_Beheerder
	), 'statuutStatusBeheerderInfo');
}

// update the object - I

		$t_object->update();

		if ($t_object->numErrors()) {
			print "ERROR UPDATING {$vs_crkcObjectnr} : ".join('; ', $t_object->getErrors())."\n";
			continue;
		} else {
			print "update 1 succesvol\n";	
		}

if (($vb_Eigenaar_2)) {
	$t_object->addAttribute(array(
		'locale_id'			=>	$pn_locale_id,
		'eigenaarInstelling'		=>	$vs_EigenaarInstelling_2,
//		'eigenaarPersoon'		=>	$vs_EigenaarPersoon,
		'statuutStatusEigenaar'		=>	$vn_StatuutStatusEigenaar_2,
//		'statusBeheerderDatum'		=>	$vs_Opmerking,
		'huidigeStatusEigenaar'		=>	$vn_HuidigeStatusEigenaar_2,	
		'statuutEigenaarOpmerking'	=>	$vs_StatuutOpmerking_Eigenaar_2
	), 'statuutStatusEigenaarInfo');
}

if ($vb_Beheerder_2) {
	$t_object->addAttribute(array(
		'locale_id'			=>	$pn_locale_id,
		'beheerderInstelling'		=>	$vs_BeheerderInstelling_2,
//		'beheerderPersoon'		=>	$vs_BeheerderPersoon,
		'statuutStatusBeheerder'	=>	$vn_StatuutStatusBeheerder_2,
//		'statusBeheerderDatum'		=>	$vs_Opmerking,
		'huidigeStatusBeheerder'	=>	$vn_HuidigeStatusBeheerder_2,
		'statuutBeheerderOpmerking'	=>	$vs_StatuutOpmerking_Beheerder_2
	), 'statuutStatusBeheerderInfo');
}

// update the object - II

		$t_object->update();

		if ($t_object->numErrors()) {
			print "ERROR UPDATING {$vs_crkcObjectnr} : ".join('; ', $t_object->getErrors())."\n";
			continue;
		} else {
			print "update 2 succesvol\n";	
		}
				
unset($vs_EigenaarInstelling);
unset($vs_EigenaarPersoon);
unset($vn_HuidigeStatusEigenaar);
unset($vn_StatuutStatusEigenaar);
unset($vs_StatuutOpmerking_Eigenaar);

unset($vs_EigenaarInstelling_2);
unset($vn_HuidigeStatusEigenaar_2);
unset($vn_StatuutStatusEigenaar_2);
unset($vs_StatuutOpmerking_Eigenaar_2);

unset($vs_BeheerderInstelling);
unset($vs_BeheerderPersoon);
unset($vn_HuidigeStatusBeheerder);
unset($vn_StatuutStatusBeheerder);
unset($vs_StatuutOpmerking_Beheerder);

unset($vs_BeheerderInstelling_2);
unset($vn_HuidigeStatusBeheerder_2);
unset($vn_StatuutStatusBeheerder_2);
unset($vs_StatuutOpmerking_Beheerder_2);
/*
		if (($vn_PubliceeropWeb == 1)) {
			$vn_web_id = $t_web->getPrimaryKey();
			$vn_web_add_id =$t_web->addItem($vn_left_id);
			$t_web->update();
			if ($t_web->numErrors()) {
				print "ERROR UPDATING PUBLICEER OP WEB SET: {$vn_Kunstvoorwerp_id}: ".join('; ', $t_web->getErrors())."\n";
				continue;
			} else {
				print "update Publiceer op web set succesvol\n";
			}		
		}
		
		if (($vn_VenAStatus == 1)) {
			$vn_VenA_id = $t_VenA->getPrimaryKey();
			$vn_VenA_add_id =$t_VenA->addItem($vn_left_id);
			$t_VenA->update();
			if ($t_VenA->numErrors()) {
				print "ERROR UPDATING VRAAG & AANBOD SET: {$vn_Kunstvoorwerp_id}: ".join('; ', $t_VenA->getErrors())."\n";
				continue;
			} else {
				print "update Vraag en Aanbod set succesvol\n";
			}		
		}
		
		if (strlen(trim($vs_AssociatieenRelaties)) > 0) {
			
			$t_object->addAttribute(array(
				'locale_id'				=>	$pn_locale_id,
				'gerelateerdObjectOpmerking'		=>	$vs_AssociatieenRelaties
			), 'gerelateerdObjectOpmerking');
			
			$t_object->update();
	
			if ($t_object->numErrors()) {
				print "\tERROR UPDATING OBJECT {$vn_Kunstvoorwerp_id}: ".join('; ', $t_object->getErrors())."\n";
				continue;
			} else {
				print "update succesvol\n";
			}

// nog te (her)bekijken

			$aantal_crkc = 0;
			$aantal_pov = 0;
			$aantal_pa = 0;
			$aantal_pwv = 0;
			$aantal_crkc = substr_count(trim($vs_AssociatieenRelaties),'CRKC');
			$aantal_pov = substr_count(trim($vs_AssociatieenRelaties),'POV');
			$aantal_pa = substr_count(trim($vs_AssociatieenRelaties),'PA');
			$aantal_pwv = substr_count(trim($vs_AssociatieenRelaties),'PWV');
			
			if ($aantal_crkc > 0 ) {
				
				for ($i= 1; $i <= $aantal_crkc; $i++) {
					$pos[$i] = strpos(trim($vs_AssociatieenRelaties), 'CRKC');
					if ($pos[$i]) {
						$object[$i] = substr(trim($vs_AssociatieenRelaties), $pos_een, 14);
						print " Object_{$i} : {$object[$i]} \n";
						$vs_AssociatieenRelaties = substr(trim($vs_AssociatieenRelaties), 14, strlen(trim($vs_AssociatieenRelaties)));
						if (substr(trim($object[$i]),4, 1) != '.') {
							$object[$i] = str_replace(' ','.', $object[$i]);
						}	
						$va_Relaties_keys = ($t_object->getObjectIDsByidnoPart(($object[$i]."%"),null) );
						$dim = sizeof($va_Relaties_keys);
						print "{$dim}\n";
		
						if ($dim > 0) {
	
							$vn_Relaties_key = $va_Relaties_keys[0];
							$t_object->addRelationship('ca_objects', $vn_Relaties_key, $vn_objects_x_objects_id);
							print "done {$object[$i]} \n";
		
							if ($t_object->numErrors()) {
								print "ERROR LINKING object and object : ".join('; ', $t_object->getErrors())."\n";
								continue;
							} else {
								print "link object-object succesvol\n";
							}	
						} else {
							print "ERROR: object {$object[$i]} niet gevonden \n";
							
						}
						
					}
				}
			} else {
				print "Geen herkenbaar patroon in string {$vs_AssociatieenRelaties} \n";
			
			}
			
		}
*/

	} else {	
		print "ERROR: object {$vn_Kunstvoorwerp_id} / {$vs_crkcObjectnr} niet gevonden \n";
	}
	
print "=================Einde verwerking kunstwerk {$vs_CrkcObjectnr} ============================= \n \n";

$vn_c++;

}

print "gedaan";

?>

