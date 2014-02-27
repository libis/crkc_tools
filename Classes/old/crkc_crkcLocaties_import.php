<?php
define('__CA_DONT_DO_SEARCH_INDEXING__',true);

require_once("/www/libis/html/ca_crkc/setup.php");
$_SERVER['HTTP_HOST'] = 'localhost';

require_once(__CA_LIB_DIR__.'/core/Db.php');
require_once(__CA_MODELS_DIR__.'/ca_locales.php');
require_once(__CA_MODELS_DIR__.'/ca_objects.php');
require_once(__CA_MODELS_DIR__.'/ca_lists.php');
require_once(__CA_MODELS_DIR__.'/ca_storage_locations.php');
require_once(__CA_MODELS_DIR__.'/ca_objects_x_storage_locations.php');
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

// relatie object-bewaarplaats
$vn_objects_x_location_id = $t_rel_types->getRelationshipTypeID('ca_objects_x_storage_locations', 'related');
print "relatie object-occurrence: ".$vn_objects_x_location_id."\n";
$t_location = new ca_storage_locations();

// objecten
$t_object = new ca_objects();
$t_object->setMode(ACCESS_WRITE);

$va_crkcLocaties = array();
$va_crkcLocaties[] = array('nr' => 1, 'Id' => 'DOLV', 'Omschrijving' => 'Onze-Lieve-Vrouw Waver');
$va_crkcLocaties[] = array('nr' => 2, 'Id' => 'DB', 'Omschrijving' => 'Bierbeek, Sint-Camillus');


//***************************
//****   Kunstvoorwerp  *****
//***************************

// want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t"); 

//
// Step 2: Import
//

print "IMPORTING kunstvoorwerpen \n";
	
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/org_kunstvoorwerp.csv')) {
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
//		$vn_VenAStatus			=	$o_tab_parser->getRowValue(2); // voor aanmaken set			
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
//		$vs_AssociatieenRelaties	=	$o_tab_parser->getRowvalue(28); // gerelateerde objecten
//		$vn_Public			=	$o_tab_parser->getRowvalue(29);
		$vn_crkcLocaties_id		=	$o_tab_parser->getRowvalue(30);
//		$vn_EigenaarInstelling_id	=	$o_tab_parser->getRowvalue(31);
//		$vn_EigenaarPersoon_id		=	$o_tab_parser->getRowvalue(32);
//		$vs_Verwerving			=	$o_tab_parser->getRowvalue(33);
//		$vn_PubliceeropWeb		=	$o_tab_parser->getRowvalue(34); // set
//		$vn_BeschikbaarvoorObject	=	$o_tab_parser->getRowvalue(35); // set
//		$vs_Ontsluitingstekst		=	$o_tab_parser->getRowvalue(36);
//		$vs_OudInventarisNr		=	$o_tab_parser->getRowvalue(37);
//		$vn_BewaarplaatsInstelling_id	=	$o_tab_parser->getRowvalue(38);
//		$vn_BewaarplaatsTypeBevinden_id	=	$o_tab_parser->getRowvalue(39);
//		$vs_BewaarplaatsCommentaar	=	$o_tab_parser->getRowvalue(40);
//		$vn_ThesaurusTermObnectNaam_id	=	$o_tab_parser->getRowvalue(41);
//		$vs_Foto			=	$o_tab_parser->getRowvalue(42);
//		$vn_BeheerderInstelling_id	=	$o_tab_parser->getRowvalue(43);
//		$vn_BeheerderPersoon_id		=	$o_tab_parser->getRowvalue(44);
//		$vs_Registrator			=	$o_tab_parser->getRowvalue(50);
		$vn_Bewaarplaats_id		=	$o_tab_parser->getRowvalue(51);
//		$vs_Standplaats			=	$o_tab_parser->getRowvalue(54);
		
print "====={$vn_c}===== PROCESSING {$vn_Kunstvoorwerp_id}  / {$vs_crkcObjectnr} \n";

//******************************************************************************
// Wat moet allemaal uitgevoerd worden
//******************************************************************************

$vb_Bewaarplaats_Intern = false;

if (($vn_crkcLocaties_id != null) and ($vn_crkcLocaties_id != 0) ) {
	print "Interne Bewaarplaats bevat een geldige waarde \n";
	$vb_Bewaarplaats_Intern = true;
}

//******************************************************************************
// De uitvoering zelf
//******************************************************************************
$vs_Identificatie_bis = '';

if ($vb_Bewaarplaats_Intern) {
	
//	$vs_Identificatie_bis = $va_crkcLocaties[$vn_crkcLocaties_id]['Id']." - ".$va_crkcLocaties[$vn_crkcLocaties_id]['Omschrijving']." (stloc_".$va_crkcLocaties[$vn_crkcLocaties_id]['nr'].")";
if ($vn_crkcLocaties_id == 2) {
	$vs_Identificatie_bis = "DB - Bierbeek, Sint-Camillus (stloc_2)";
} elseif ($vn_crkcLocaties_id == 1) {
	$vs_Identificatie_bis = "DOLV - Onze-Lieve-Vrouw Waver (stloc_1)";
}

	print "op te zoeken {$vs_Identificatie_bis} \n";
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
	
		$va_right_id = $t_location->getLocationIDsByName($vs_Identificatie_bis, null);
		$dims = sizeof($va_right_id);
		if ($dims > 0) {
			//print "right_id_1:".$va_right_id[0]."/".$vs_Bewaarplaats_Naam."\n";
			$t_object->addRelationship('ca_storage_locations', $va_right_id[0], $vn_objects_x_location_id);
			if ($t_object->numErrors()) {
				print "ERROR LINKING object {$vs_crkcObjectnr} and storage location : ".join('; ', $t_object->getErrors())."\n";
				continue;
			} else {
				print "link object-storage location succesvol\n";
			}
		} else {
			print " ERROR: Storage Location {$vn_crkcLocaties_id} bestaat niet \n";
		}

	} else {	
		print "ERROR: object {$vn_Kunstvoorwerp_id} / {$vs_crkcObjectnr} niet gevonden \n";
	}
	
}
	
print "=================Einde verwerking kunstwerk {$vs_CrkcObjectnr} ============================= \n \n";

$vn_c++;

}

print "gedaan";

?>

