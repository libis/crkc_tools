<?php
	require_once("/www/libis/html/ca_crkc/setup.php");

	require_once(__CA_LIB_DIR__.'/core/Db.php');
	require_once(__CA_MODELS_DIR__.'/ca_locales.php');
	require_once(__CA_MODELS_DIR__.'/ca_objects.php');
	require_once(__CA_MODELS_DIR__.'/ca_collections.php');
	require_once(__CA_MODELS_DIR__.'/ca_entities.php');
	require_once(__CA_MODELS_DIR__.'/ca_places.php');
	require_once(__CA_MODELS_DIR__.'/ca_storage_locations.php');
	require_once(__CA_MODELS_DIR__.'/ca_objects_x_collections.php');
	require_once(__CA_MODELS_DIR__.'/ca_objects_x_places.php');
	require_once(__CA_MODELS_DIR__.'/ca_objects_x_storage_locations.php');
	require_once(__CA_MODELS_DIR__.'/ca_objects_x_entities.php');
	require_once(__CA_LIB_DIR__.'/core/Parsers/DelimitedDataParser.php');
	
	$_ = new Zend_Translate('gettext', __CA_APP_DIR__.'/locale/en_US/messages.mo', 'nl_NL');
	
	$t_locale = new ca_locales();
	$pn_locale_id = $t_locale->loadLocaleByCode('nl_NL');		// default locale_id
	
	$t_list = new ca_lists();
	
	// object-type en source
	$pn_object_type_id = 	$t_list->getItemIDFromList('object_types', 'crkcObject');
	$pn_object_source_id = 	$t_list->getItemIDFromList('object_sources', 'crkcCollectie');
	// relaties
	$t_rel_types = new ca_relationship_types();

	// relatie object-collection
	$vn_objects_x_collections_id = $t_rel_types->getRelationshipTypeID('ca_objects_x_collections', 'part_of');
	print "relatie object-collectie: ".$vn_objects_x_collections_id."\n";
	$t_collectie = new ca_collections();

	// relatie object-plaats
	$vn_objects_x_places_id = $t_rel_types->getRelationshipTypeID('ca_objects_x_places', 'related');
	print "relatie object-plaats: ".$vn_objects_x_places_id."\n";
	$t_plaats = new ca_places();

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

	// relatie object-occurrences
//	$vn_objects_x_occurrence_id = $t_rel_types->getRelationshipTypeID('ca_objects_x_occurrences', 'related');
//	print "relatie object-occurrence: ".$vn_objects_x_occurrence_id."\n";
//	$t_occurrence = new ca_occurrences();
	
	// relatie object-bewaarplaats
	$vn_objects_x_stloc_id = $t_rel_types->getRelationshipTypeID('ca_objects_x_storage_locations', 'related');
	print "relatie object-occurrence: ".$vn_objects_x_stloc_id."\n";
	$t_stloc = new ca_storage_locations();
	 
//***************************
//****   Kunstvoorwerp  *****
//***************************

// want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t"); 

print "IMPORTING collections into an array \n";

$va_Collections = array();

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
	$vn_Enregistreur	= 	$o_tab_parser->getRowvalue(5);
	
	if ($vn_Enregistreur == 1) {
		$vs_Titel = 'CRKC.'.$vs_Titel;
	} elseif ($vn_Enregistreur == 2) {
		$vs_Titel = 'POV.'.$vs_Titel;
	}elseif ($vn_Enregistreur == 3) {
		$vs_Titel = 'PA.'.$vs_Titel;
	}elseif ($vn_Enregistreur == 6) {
		$vs_Titel = 'PWV.'.$vs_Titel;
	}
	
	$va_Collections[$vn_Collection_id] = $vs_Titel;

	$vn_c++;
}

print "IMPORTING kunstvoorwerpen \n";

//
// Step 2: Import
//
	
// ----------------------------------------------------------------------
// process main data (a tab delimited file)
// ----------------------------------------------------------------------
	
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_kunstvoorwerp.csv')) {
	die("Couldn't parse Kunstvoorwerpen data\n");	
}

$vn_c = 1;

$o_tab_parser->nextRow(); // skip first row

//-------------------------
// waarden inlezen
//-------------------------
	
while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_Kunstvoorwerp_id		=	$o_tab_parser->getRowValue(1); //
	$vn_VenAStatus			=	$o_tab_parser->getRowValue(2);			
	$vn_Collectie_id		=	$o_tab_parser->getRowValue(3); //			
	$vn_TypeToestanden_id		=	$o_tab_parser->getRowValue(4); //			
	$vn_Stijlen_id			=	$o_tab_parser->getRowValue(5);			
	$vn_Plaats_id 			=	$o_tab_parser->getRowValue(6); //			
	$vs_Identificatie		=	$o_tab_parser->getRowValue(7); //			
	$vs_Omschrijving		=	$o_tab_parser->getRowValue(8); //			
	$vs_crkcObjectnr		=	$o_tab_parser->getRowValue(9); //
	$vs_HistoriekOpenbaar		=	$o_tab_parser->getRowValue(10); //
	$vs_Titel			=	$o_tab_parser->getRowValue(11);
	$vn_AantalExemplaren		=	$o_tab_parser->getRowValue(12); //
	$vn_AantalOnderdelen		=	$o_tab_parser->getRowValue(13); //
	$vs_Signatuur			=	$o_tab_parser->getRowvalue(14); //
	$vs_Opschriften			=	$o_tab_parser->getRowvalue(15); //
	$vs_Merken			=	$o_tab_parser->getRowvalue(16); //
	$vs_Beschrijving		=	$o_tab_parser->getRowvalue(17); //
//	$vs_InventarisatieDatum		=	$o_tab_parser->getRowvalue(18);
//	$vs_RecordCreationDate		=	$o_tab_parser->getRowvalue(19);
//	$vd_RecordUpdateDate		=	$o_tab_parser->getRowvalue(20);
	$vs_ToestandVoorwerp		=	$o_tab_parser->getRowvalue(21); //
	$vs_TeRestaureren		=	$o_tab_parser->getRowvalue(22); //
	$vs_ArchiefenBronnen		=	$o_tab_parser->getRowvalue(23); //
	$vs_OpmerkingGesloten		=	$o_tab_parser->getRowvalue(24); //
	$vs_OpmerkingOpenbaar		=	$o_tab_parser->getRowvalue(25); //
//	$vs_ObjectNaam_id		=	$o_tab_parser->getRowvalue(26);
//	$vs_Datum			=	$o_tab_parser->getRowvalue(27);
//	$vs_AssociatieenRelaties	=	$o_tab_parser->getRowvalue(28);
//	$vn_Public			=	$o_tab_parser->getRowvalue(29);
//	$vn_CrkcLocaties_id		=	$o_tab_parser->getRowvalue(30);
//	$vs_EigenaarInstelling_id	=	$o_tab_parser->getRowvalue(31);
//	$vs_EigenaarPersoon_id		=	$o_tab_parser->getRowvalue(32);
//	$vs_Verwerving			=	$o_tab_parser->getRowvalue(33);
//	$vn_PubliceeropWeb		=	$o_tab_parser->getRowvalue(34);
//	$vn_BeschikbaarvoorObject	=	$o_tab_parser->getRowvalue(35);
//	$vs_Ontsluitingstekst		=	$o_tab_parser->getRowvalue(36);
	$vs_OudInventarisNr		=	$o_tab_parser->getRowvalue(37); //
//	$vs_BewaarplaatsInstelling_id	=	$o_tab_parser->getRowvalue(38);
//	$vs_BewaarplaatsTypeBevinden_id	=	$o_tab_parser->getRowvalue(39);
//	$vs_BewaarplaatsCommentaar	=	$o_tab_parser->getRowvalue(40);
//	$vn_ThesaurusTermObnectNaam_id	=	$o_tab_parser->getRowvalue(41);
	$vs_Foto			=	$o_tab_parser->getRowvalue(42); //
//	$vs_BeheerderInstelling_id	=	$o_tab_parser->getRowvalue(43);
//	$vs_BeheerderPersoon_id		=	$o_tab_parser->getRowvalue(44);
	$vs_moderate			=	$o_tab_parser->getRowvalue(47); //
	$vs_Registrator			=	$o_tab_parser->getRowvalue(50); //
//	$vs_Bewaarplaats_id		= 	$o_tab_parser->getRowvalue(51);
	$vs_Standplaats			=	$o_tab_parser->getRowvalue(54); //
	
	print "PROCESSING {$vn_Kunstvoorwerp_id}\n";

// ------------------------------------
// Waarden omvormen naar gepaste output
// ------------------------------------

// Construct a title for the object in case of blank title				
	if(!$vs_Identificatie) {
		$vs_Identificatie = $vs_crkcObjectnr."??? TITLE UNKNOWN ???";		// if all else fails make it obvious
		print "WARNING: Identificatie/titel {$vs_crkcObjectnr} undefined \n";
	}
/*
//Inventarisatiedatum

	if (trim($vs_InventarisatieDatum) == '0/0/0') {
		$vs_InventarisatieDatum = '';
	}
	
	if(strstr($vs_InventarisatieDatum,'00/')) {
		$vs_InventarisatieDatum = str_replace("00/", "01/",$vs_InventarisatieDatum);
	} 
	if(strstr($vs_InventarisatieDatum,'0/')) {
		$vs_InventarisatieDatum = str_replace("0/", "01/",$vs_InventarisatieDatum);
	} 
	
	print "Inventarisatiedatum {$vs_InventarisatieDatum} \n";

//Datum
	
	if (trim($vs_Datum) == '0/0/0') {
		$vs_Datum = '';
	}
	
	if(strstr($vs_Datum,'00/')) {
		$vs_Datum = str_replace("00/", "01/",$vs_Datum);
	} 
	if(strstr($vs_Datum,'0/')) {
		$vs_Datum = str_replace("0/", "01/",$vs_Datum);
	} 
	
	print "datum {$vs_Datum} \n";

*/	
//toestanden: lijstwaarden bepalen

	if (trim($vn_TypeToestanden_id) == 4 ) {
		$vn_Toestand	=	$t_list->getItemIDFromList('toestand', 'poor');
	} elseif ((trim($vn_TypeToestanden_id)) == 2 ) {
		$vn_Toestand	=	$t_list->getItemIDFromList('toestand', 'fair');
	} elseif ((trim($vn_TypeToestanden_id)) == 3 ) {
		$vn_Toestand	=	$t_list->getItemIDFromList('toestand', 'moderate');
	} elseif ((trim($vn_TypeToestanden_id)) == 1 ) {
		$vn_Toestand	=	$t_list->getItemIDFromList('toestand', 'good');
	} elseif ((trim($vn_TypeToestanden_id)) == 0 ) {
		$vn_Toestand	=	null;
		print "WARNING: TypeToestanden {$vs_crkcObjectnr}/{$vs_Kunstvoorwerp_id} undefined (=0)\n";
	} else { 
		$vn_Toestand	=	null;
		print "WARNING: TypeToestanden {$vs_crkcObjectnr}/{$vs_Kunstvoorwerp_id} undefined (=BLANCO)\n";
	}	
		

//stijlen: lijstwaarden bepalen - niet mogelijk ahv info in deze tabel 
// todo: omzettingstabel maken in Access
/*
	if (!$t_list->getItemIDFromList('stijl_lijst', $vn_Stijlen_id)) {
		$vn_Stijlen	=	$t_list->getItemIDFromList('stijl_lijst', 'undefined');
		print "Stijl {$vs_crkcObjectnr} undefined (=BLANCO)\n";
	} elseif ($vn_Stijlen_id == 0 ) {
		$vn_Stijlen	= 	$t_list->getItemIDFromList('stijl_lijst', $vn_Stijlen_id);
		print "Stijl {$vs_crkcObjectnr} undefined (=0) \n";
	} else {
		$vn_Stijlen	= 	$t_list->getItemIDFromList('stijl_lijst', $vn_Stijlen_id);
	}
*/

// bepalen wat in ObjectNaam moet komen: -> apart programma

//$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$$
		
//----------------		
// Create object
//----------------

	$t_object = new ca_objects();
	$t_object->setMode(ACCESS_WRITE);
	$t_object->set('type_id', $pn_object_type_id);
	$t_object->set('source_id', $vn_object_source_id );
	$t_object->set('idno', $vs_crkcObjectnr." - (KV_".$vn_Kunstvoorwerp_id.")");
	$t_object->set('access', 1);
	
	if ($vs_moderate = 0) {
		$t_object->set('status', 0);
	} else {
		$t_object->set('status', 4);
	}

//-------------------------
// Waarden mappen      
//-------------------------
	// ObjectTitel -> open container - geen if - preferred-label
//xxxxxx//vraag: 2712 records zonder identificatie: 25 met ? info uit veld 7
	// Hoe toch invullen?
	$t_object->addAttribute(array(
		'locale_id'	=>	$pn_locale_id,
		'name_singular'	=>	$vs_Identificatie,
		'name_plural'	=>	$vs_Identificatie
	), 'name');
		
//xxxxxx// ObjectNaam -> objectnaam uit am-Move; info uit drie velden samenvoegn: 
	// Titel - idObjectnaam en idThesaurusTermObjectnaam (11 - 26 - 41)
	// gesloten container - vaak in geen vd drie velden info
//	$t_object->addAttribute(array(
//		'locale_id'	=>	$pn_locale_id,
//		'objectNaam'	=>	$vs_Identificatie
//	), 'objectNaam');
	
//xxxxxx//Object Nummer -> open container: crkcObjectnr - komt in idno - info uit veld 9
	
//xxxxxx//Object Collectie -> open container: -> hier moet de collectienaam komen -> relatie
	//kan pas gelegd worden na de insert - info uit veld 3 

//xxxxxx//Status: drop-down: komt hier bij upload ooit andere waarde in dan 4
	
//xxxxxx//Toegang: drop-down: komt hier bij upload ooit andere waarde in dan 1
	
//xxxxxx//Datum - gesloten container met twee velden: datum en datum opmerking
	//hier moet info uit veld 27:
	//waar vinden we DatumOpmerking ->
	//kunstvoorwerp_has_periodecreation OpmerkingOpenbaar
/*	
if (trim($vs_Datum)) {
	$t_object->addAttribute(array(
		'locale_id'		=>	$pn_locale_id,
		'objectDatum'		=>	$vs_Datum
//		'objectDatumOpmerking'	=>	'???????????????????????????'
	), 'objectDatumInfo');
}
*/
//xxxxxx//Omschrijving: gesloten container - werken met if 
	//hier komt info uit veld 8
if (trim($vs_Omschrijving)) {
	$t_object->addAttribute(array(
		'locale_id'		=>	$pn_locale_id,
		'objectOmschrijving'	=>	trim($vs_Omschrijving)
	), 'objectOmschrijving');
}

//xxxxxx//Historiek: gesloten container - werken met if
	//hier komt info uit veld 10
if (trim($vs_HistoriekOpenbaar)) {
	$t_object->addAttribute(array(
		'locale_id'		=>	$pn_locale_id,
		'objectHistoriek'	=>	trim($vs_HistoriekOpenbaar)
	), 'objectHistoriek');
}

//xxxxxx//Plaats Ontstaan - open container - relatie objects-places
	//kan pas gelegd worden na de insert - info uit veld 6
	
//xxxxxx//Aantal exemplaren - gesloten container - werken met if
	//hier komt info uit veld 12
if ($vn_AantalExemplaren) {
	$t_object->addAttribute(array(
		'locale_id'			=>	$pn_locale_id,
		'objectAantalExemplaren'	=>	$vn_AantalExemplaren
	), 'objectAantalExemplaren');
}

//xxxxxx//Aantal onderdelen - gesloten container - werken met if
	//hier komt info uit veld 13
if ($vn_AantalOnderdelen) {
	$t_object->addAttribute(array(
		'locale_id'			=>	$pn_locale_id,
		'objectAantalOnderdelen'	=>	$vn_AantalOnderdelen
	), 'objectAantalOnderdelen');
}

//xxxxxx//Signatuur - gesloten container - wreken met if
	//hier komt info uit veld 14
if (trim($vs_Signatuur)) {
	$t_object->addAttribute(array(
		'locale_id'		=>	$pn_locale_id,
		'objectSignatuur'	=>	trim($vs_Signatuur)
	), 'objectSignatuur');
}

//xxxxxx//Opschriften - gesloten container - werken met if
	//hier komt info uit veld 15
if (trim($vs_Opschriften)) {
	$t_object->addAttribute(array(
		'locale_id'		=>	$pn_locale_id,
		'objectOpschriften'	=>	trim($vs_Opschriften)
	), 'objectOpschriften');
}

//xxxxxx//Andere merken - gesloten container - werken met if
	//hier komt info uit veld 16
if (trim($vs_Merken) and (trim($vs_Merken) != '/')) {
	$t_object->addAttribute(array(
		'locale_id'		=>	$pn_locale_id,
		'objectMerken'		=>	trim($vs_Merken)
	), 'objectMerken');
}

//xxxxxx//Beschrijving iconografie - gesloten container - werken met if
	//hier komt info uit veld 17
if (trim($vs_Beschrijving)) {
	$t_object->addAttribute(array(
		'locale_id'		=>	$pn_locale_id,
		'objectBeschrijving'	=>	trim($vs_Beschrijving)
	), 'objectBeschrijving');
}

//xxxxxx//Ander trefwoord - gesloten container
	//uit kunstvoorwerp_has_anderetrefwoord en anderetrefwoord
	
//xxxxxx//Object Categorie - gesloten container
	// uit kunstvoorwerp_has_trefwoord en trefwoord
	
//xxxxxx//Inventarisatiedatum - gesloten container - werken met if
	//hier komt info uit veld 18
/*
if (strlen(trim($vs_InventarisatieDatum)) > 0 ) {
	$t_object->addAttribute(array(
		'locale_id'			=>	$pn_locale_id,
		'objectInventarisatieDatum'	=>	trim($vs_InventarisatieDatum)
	), 'objectInventarisatieDatum');
}
*/

//xxxxxx//Archivarische Bronnen - gesloten container - werken met if
	//hier komt info uit veld 23 ArchiefenBronnen
if (trim($vs_ArchiefenBronnen)) {
	$t_object->addAttribute(array(
		'locale_id'			=>	$pn_locale_id,
		'objectDocumentatieBronnen'	=>	trim($vs_ArchiefenBronnen)
	), 'objectDocumentatieBronnen');
}

//xxxxxx//Bibliografische referentie - open container - relatie object/occurrence
	//zie kunstvoorwerp_has_litterateur
	//dit bestand bevat ook de info voor de volgende container
	
//xxxxxx//Literatuur info - gesloten container - twee velden: pagina en Opmerking
	//zie opmerking vorig veld
	
	// waarom zijn deze twee niet samengevoeg
	
//xxxxxx//Referentiefoto - gesloten container - werken met if
	//hier komt info uit veld 42
if (trim($vs_Foto)) {
	$t_object->addAttribute(array(
		'locale_id'		=>	$pn_locale_id,
		'objectReferentieFoto'	=>	trim($vs_Foto)
	), 'objectReferentieFoto');
}
	
//xxxxxx//Registrator - gesloten container - werken met if
	//hier komt info uit veld 45
if (trim($vs_Registrator)) {
	$t_object->addAttribute(array(
		'locale_id'		=>	$pn_locale_id,
		'objectRegistrator'	=>	trim($vs_Registrator)
	), 'objectRegistrator');
}

//xxxxxx//Oud inventarisnummer - gesloten container - werken met if
	//hier komt info uit veld 37
if (trim($vs_OudInventarisNr)) {
	$t_object->addAttribute(array(
		'locale_id'			=>	$pn_locale_id,
		'objectOudInventarisnummer'	=>	trim($vs_OudInventarisNr)
	), 'objectOudInventarisnummer');
}

//xxxxxx//Materiaal - gesloten container - twee velden am-Move-Materiaal en Opmerking
	//uit kunstvoorwerpen_has_materiaal
	
//xxxxxx//Afmeting - gesloten container - H/B/D/Dia/Type/Opm
	// uit kunstvoorwerp_has_typesvanafmetingen
	
//xxxxxx//Stijl - gesloten container - lijst + opmerking
	// uit kunstvoorwerp_has_stijlen
	
//xxxxxx//Techniek - gesloten container - am-Move-techniek + opmerking
	// uit kunstvoorwerp_has_technieken
	
//xxxxxx//Iconografie - gesloten container - enkel voor PA en POV
	// uit kunstvoorwerp_has_trefwoordiconografie
	
//xxxxxx//Goud- en zilvermerk - open container - relatie object-object
	// uit kunstvoorwerp_has_zilvermerk
	
//xxxxxx//Zilvermerk Opmerkingen - gesloten container
	// uit kunstvoorwerp_has_zilvermerk

	// waarom zijn deze twee niet samengevoegd.
	
//xxxxxx//Waardering - gesloten container - nieuw veld

//xxxxxx//Vervaardiger - open container - relatie object-occurrence
	// uit kunstvoorwerp_has_kunstenaar
	
//---------TOESTAND-------------------------------------------------------------

//xxxxxx//Toestand algemeen - gesloten container - dropdownlist - werken met if
	// hier komt info uit veld 4
if ($vn_Toestand) {
	$t_object->addAttribute(array(
		'locale_id'		=>	$pn_locale_id,
		'objectToestand'	=>	$vn_Toestand
	), 'objectToestand');
}

//xxxxxx//Toestand specifiek - gesloten container - werken met if
	// hier komt info uit veld 21 - ToestandVoorwerp
if (trim($vs_ToestandVoorwerp)) {
	$t_object->addAttribute(array(
		'locale_id'			=>	$pn_locale_id,
		'objectToestandspecifiek'	=>	trim($vs_ToestandVoorwerp)
	), 'objectToestandspecifiek');
}

//xxxxxx//Toestand Opmerking - gesloten container - werken met if
	// hier komt info uit velden 24 en 25 samengevoegd
if (trim($vs_OpmerkingGesloten)) {
	$t_object->addAttribute(array(
		'locale_id'			=>	$pn_locale_id,
		'objectToestandNietPubliek'	=>	trim($vs_OpmerkingGesloten)
	), 'objectToestandNietPubliek');
}

if (trim($vs_OpmerkingOpenbaar)) {
	$t_object->addAttribute(array(
		'locale_id'			=>	$pn_locale_id,
		'toestandOpmerkingen'	=>	trim($vs_OpmerkingOpenbaar)
	), 'toestandOpmerkingen');
}

//xxxxxx//Conservatieadvies - gesloten container - werken met if
	// hier komt info uit veld 22 Te Restaureren
if (trim($vs_TeRestaureren)) {
	$t_object->addAttribute(array(
		'locale_id'			=>	$pn_locale_id,
		'objectRestauratieAdvies'	=>	trim($vs_TeRestaureren)
	), 'objectRestauratieAdvies');
}

//-------------TENTOONSTELLING--------------------------------------------------

//xxxxxx//Tentoonstelling - open container - relatie object-occurrence
	// uit kunstvoorwerp_has_tentoonstelling

//xxxxxx//Tentoonstelling Opmerking - gesloten container
	// uit kunstvoorwerp_has_tentoonstelling
	
	// waarom zijn deze twee niet samengevoegd ?
	
//------------MEDIA en BIJLAGEN-------------------------------------------------

//xxxxxx//Bijlagen - gesloten container - nieuw veld

//xxxxxx/Bestandstype - gesloten container - nieuw veld

//------------BEWAARPLAATS------------------------------------------------------

//xxxxxx//Huidige bewaarplaats - open container - relatie object-storage location
	// op basis van de info in veld 38 en 39
	// kan pas ingevuld na insert
	
//xxxxxx//Bewaarplaats Opmerking - gesloten container
	// hier komt veld 40 - naar bis

//xxxxxx//Standplaats - gesloten container
	// hier komt veld 50
if (trim($vs_Standplaats)) {
	$t_object->addAttribute(array(
		'locale_id'			=>	$pn_locale_id,
		'objectStandplaats'		=>	trim($vs_Standplaats)
	), 'objectStandplaats');
}

// XXXXX vs_Bewaarplaats_id zou nog gimplementeerd moeten worden

//--------------STATUS-EIGENAAR-------------------------------------------------
//xxxxxx//Eigenaar - open container - relatie object-entity
	// hier komt info uit veld 31 en 32 id_Eigenaar_Instelling en id_Eigenaar_Persoon
	
//xxxxxx//Eigenaar Status Informatie - gesloten container 
	// 4 velden: Verwervingsmethode (drop-down) - Datum Status Eigenaar
	// Huidige status eignaar (ja/nee) - Statuut opmerking (veld 33)
	// uit kunstvoorwerp_has_typerelatietotobject
	
//xxxxxx//Afgesloten informatie - gesloten container
	// 2 velden: type afstoting (drop-down) - reden van afstoting
	
//--------------STATUS-BEHEERDER------------------------------------------------

//xxxxxx//Beheerder - open container - relatie object-entity
	// hier komt info uit veld 43 en 44 id_Beheerder_Instelling en id_Beheerder_Persoon
	
//xxxxxx//Beheerder Status Informatie - gesloten container
	// 4 velden: Verwervingsmethode (drop-down) - Datum Status Beheerder
	// Huidige status beheerder (ja/nee) - Statuut opmerking
	// uit kunstvoorwerp_has_typerelatietotobject
	
//xxxxxx//Tijdelijke Bruikleen - open container - relatie object-loans
	// uit kunstvoorwerp_has_bruikleen
	
//xxxxxx//Verzekering - open container - relatie object-occurrence
	// kunstvoorwerp_has_verzekeringscontract

//xxxxxx//Verzekering Opmerking - gesloten container
	// uit kunstvoorwerp_has_verzekeringscontract
	
	// waarom zijn deze twee niet samengevoegd?


	// insert the object
	$t_object->insert();

	if ($t_object->numErrors()) {
		
		print "ERROR INSERTING {$$vn_Kunstvoorwerp_id}: ".join('; ', $t_object->getErrors())."\n";
		continue;
	} else {
		print "insert succesvol\n";	
	}

	$t_object->getPrimaryKey();
	// --------------------------------------------------------------
	// Set a preferred label for the object
	// --------------------------------------------------------------
	
	// DON'T FORGET TO GIVE EVERYTHING A LABEL! This is the value used for display in search results
	// and lots of other places. If you don't define a value it will be hard to distinguish this row
	// for others
	$t_object->addLabel(
		array('name' => $vs_Identificatie),
		$pn_locale_id, null, true
	);
	
	if ($t_object->numErrors()) {
		print "\tERROR ADD LABEL TO {$vn_Kunstvoorwerp_id}: ".join('; ', $t_object->getErrors())."\n";
	} else {
		print "AddLabel succesvol\n";
	}
/*
	if (trim($vs_Datum)) {
		$t_object->addAttribute(array(
			'locale_id'		=>	$pn_locale_id,
			'objectDatum'		=>	$vs_Datum
//			'objectDatumOpmerking'	=>	$vs_Datum
		), 'objectDatumInfo');
	}

	$t_object->update();
	
	if ($t_object->numErrors()) {
		print "ERROR UPDATING VS_DATUM {$vn_Kunstvoorwerp_id}: ".join('; ', $t_object->getErrors())."\n";
		
		if (trim($vs_Datum)) {
			$t_object->addAttribute(array(
				'locale_id'		=>	$pn_locale_id,
				'objectDatum'		=>	null,
				'objectDatumOpmerking'	=>	$vs_Datum
			), 'objectDatumInfo');
		}

		$t_object->update();
		
		if ($t_object->numErrors()) {
			print "TWEEDE ERROR UPDATING VS_DATUM {$vn_Kunstvoorwerp_id}: ".join('; ', $t_object->getErrors())."\n";
		} else {
			print "insert vs_datm succesvol als opmerking bij tweede poging \n";
		}	
		
	} else {
		print "insert vs_datum succesvol  \n";
	}
			
	if (strlen(trim($vs_InventarisatieDatum)) > 0 ) {
		$t_object->addAttribute(array(
			'locale_id'			=>	$pn_locale_id,
			'objectInventarisatieDatum'	=>	trim($vs_InventarisatieDatum)
		), 'objectInventarisatieDatum');
	}
	
	$t_object->update();
	
	if ($t_object->numErrors()) {
		print "ERROR UPDATING INVENTARISATIEDATUM {$vn_Kunstvoorwerp_id}: ".join('; ', $t_object->getErrors())."\n";
	} else {
		print "insert vs_inventarisatiedatum succesvol \n";
	}
*/
	// relaties 
	$vn_left_id = $t_object->getPrimaryKey();
	print "left_id:".$vn_left_id."\n";
	$t_object->set('object_id', $vn_left_id);

//xxxxxx// object-collectie -> Probleem: dit nummer is nergens opgeslaan bij de collectie 
	// benodigde info werd in array $va_Collections gestoken
if (($vn_Collectie_id) != 0 and ($vn_Collectie_id) != null) {
	$va_right_id = $t_collectie->getCollectionIDsByidno($va_Collections[$vn_Collectie_id]);
	print "right_id_1:".$va_right_id[0]."\n";
	if ($va_right_id == NULL) {
		print " ERROR: COLLECTIE {$va_Collections[$vn_Collectie_id]} toevoegen \n";
	}else{
		$t_object->addRelationship('ca_collections', $va_right_id[0], $vn_objects_x_collections_id);
		print "done\n";
		if ($t_object->numErrors()) {
			print "ERROR LINKING object and collection : ".join('; ', $t_object->getErrors())."\n";
		} else {
			print "link object-collection succesvol\n";
		}
	}
}
//xxxxxx// object-plaats 
if (($vn_Plaats_id) == 0 || ($vn_Plaats_id) == null) {
} else {
	$va_right_id = $t_plaats->getPlaceIDsByidno('plaats_'.($vn_Plaats_id), null);
	print "right_id_1:".$va_right_id[0]."\n";
	if ($va_right_id == NULL) {
		print " ERROR: PLAATS plaats_{$vn_Plaats_id} toevoegen \n";
	}else{
		$t_object->addRelationship('ca_places', $va_right_id[0], $vn_objects_x_places_id);
		print "done\n";
		if ($t_object->numErrors()) {
			print "ERROR LINKING object and place : ".join('; ', $t_object->getErrors())."\n";
		} else {
			print "link object-plaats succesvol\n";
		}
	}
}
//xxxxxx// object-storage location -> eveneens een probleem --- voorlopig NEEN
/*
	$va_right_id = $t_stloc->getLocationIDsByidno('stloc_'.($vs_BewaarplaatsInstelling_id), null);
	print "right_id_1:".$va_right_id[0]."\n";
	if ($va_right_id == NULL) {
		print " ERROR: STORAGE LOCATION {$vs_BewaarplaatsInstelling}toevoegen \n";
	}else{
		$t_object -> addRelationship('ca_storage_locations', $va_right_id[0], $vn_objects_x_stloc_id);
		print "done\n";
		if ($t_object->numErrors()) {
			print "ERROR LINKING object and storage location : ".join('; ', $t_object->getErrors())."\n";
			continue;
		} else {
			print "link object-storage location succesvol\n";
		}
	}

//xxxxxx//object-entities - eigenaar
	//$vs_EigenaarInstelling_id
if (($vs_EigenaarInstelling_id == '0') || ($vs_EigenaarInstelling_id) == '') {
}else {
	$va_right_id = $t_entity->getEntityIDsByidno('org_'.($vs_EigenaarInstelling_id));
	print "right_id_1:".$va_right_id[0]."\n";
	if ($va_right_id == NULL) {
		print " ERROR: INSTELLING {$vs_EigenaarInstelling_id} toevoegen \n";
	}else{
		$t_object -> addRelationship('ca_entities', $va_right_id[0], $vn_objects_x_eigenaar_id);
		print "done\n";
		if ($t_object->numErrors()) {
			print "ERROR LINKING object and entity Eigenaar Instelling : ".join('; ', $t_object->getErrors())."\n";
			continue;
		} else {
			print "link object-eigenaar-instelling succesvol\n";
		}
	}
}
	//$vs_EigenaarPersoon_id
if (($vs_EigenaarPersoon_id == '0') || ($vs_EigenaarPersoon_id) == '') {
}else {
	$va_right_id = $t_entity->getEntityIDsByidno('pers_'.($vs_EigenaarPersoon_id));
	print "right_id_1:".$va_right_id[0]."\n";
	if ($va_right_id == NULL) {
		print " ERROR: PERSOON {$vs_EigenaarPersoon_id} toevoegen \n";
	}else{
		$t_object -> addRelationship('ca_entities', $va_right_id[0], $vn_objects_x_eigenaar_id);
		print "done\n";
		if ($t_object->numErrors()) {
			print "ERROR LINKING object and entity Eigenaar Persoon : ".join('; ', $t_object->getErrors())."\n";
			continue;
		} else {
			print "link object-eigenaar persoon succesvol\n";
		}
	}
}	
//xxxxxx//object-entities - beheerder
	//$vs_BeheerderInstelling_id
if (($vs_BeheerderInstelling_id == '0') || ($vs_BeheerderInstelling_id) == '') {
}else {
	$va_right_id = $t_entity->getEntityIDsByidno('org_'.($vs_BeheerderInstelling_id), null);
	print "right_id_1:".$va_right_id[0]."\n";
	if ($va_right_id == NULL) {
		print " ERROR: INSTELLING {$vs_BeheerderInstelling_id} toevoegen \n";
	}else{
		$t_object -> addRelationship('ca_entities', $va_right_id[0], $vn_objects_x_beheerder_id);
		print "done\n";
		if ($t_object->numErrors()) {
			print "ERROR LINKING object and entity Beheerder Instelling : ".join('; ', $t_object->getErrors())."\n";
			continue;
		}else {
			print "link object-beheerder instelling succesvol\n";
		}
	}
}
	//$vs_BeheerderPersoon_id
if (($vs_BeheerderPersoon_id == '0') || ($vs_BeheerderPersoon_id) == '') {
}else {
	$va_right_id = $t_entity->getEntityIDsByidno('pers_'.($vs_BeheerderPersoon_id) );
	print "right_id_1:".$va_right_id[0]."\n";
	if ($va_right_id == NULL) {
		print " ERROR: PERSOON {$vs_BeheerderPersoon_id} toevoegen \n";
	}else{
		$t_object -> addRelationship('ca_entities', $va_right_id[0], $vn_objects_x_beheerder_id);
		print "done\n";
		if ($t_object->numErrors()) {
			print "ERROR LINKING object and entity Beheerder Persoon : ".join('; ', $t_object->getErrors())."\n";
			continue;
		} else {
			print "link object-beheerder persoon succesvol\n";
		}
	}
}

//xxxxxx//object-occurrences -> allemaal via has-bestanden

//xxxxxx//object-object -> $vs_AssociatiesenRelaties
	//info in dit veld is NIET gestructureerd - bekijken met Sam
*/	

print "==={$vn_c}==============Einde verwerking kunstwerk {$vs_CrkcObjectnr} ============================= \n \n";

$vn_c++;
		

}

print "gedaan";
?>

