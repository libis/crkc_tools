<?php

/*
 * Step 1: Initialisation
 */
 
set_time_limit(36000);
require_once("/www/libis/html/ca_crkc/setup.php");

require_once(__CA_LIB_DIR__.'/core/Db.php');
require_once(__CA_MODELS_DIR__.'/ca_locales.php');
require_once(__CA_MODELS_DIR__.'/ca_objects.php');
require_once(__CA_MODELS_DIR__.'/ca_places.php');
require_once(__CA_MODELS_DIR__.'/ca_objects_x_places.php');

require_once(__CA_LIB_DIR__.'/core/Parsers/DelimitedDataParser.php');


$_ = new Zend_Translate('gettext', __CA_APP_DIR__.'/locale/en_US/messages.mo', 'nl_NL');

$t_locale = new ca_locales();
$pn_locale_id = $t_locale->loadLocaleByCode('nl_NL');

$t_list = new ca_lists();
//object_type
$pn_object_type_id =	$t_list->getItemIDFromList('object_types', 'crkcZilvermerk');

$t_rel_types = new ca_relationship_types();
$vn_objects_x_places_id = $t_rel_types->getRelationshipTypeID('ca_objects_x_places', 'related');
print "relatie: ".$vn_objects_x_places_id."\n";

$t_plaats = new ca_places();

	//***************************
//****   Zilvermerken    *****
//***************************

print "IMPORTING zilvermerken \n";

//
// Step 2: Import
//

 // want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t"); 

// Read csv; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_zilvermerken.csv')) {
	die("Couldn't parse new_zilvermerken.csv data\n");	
}
	
print "READING new_zilvermerken.csv...\n";

$vn_c = 1;
	
$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------

while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_Zilvermerken_id	=	$o_tab_parser->getRowValue(1); 
	$vs_Naam	 	=	$o_tab_parser->getRowValue(2); 
	$vn_ZM_Vorm		=	$o_tab_parser->getRowvalue(3); 
	$vn_ZM_Type		= 	$o_tab_parser->getRowvalue(4);
	$vn_Plaats_id		= 	$o_tab_parser->getRowvalue(5);
	$vs_Datum		= 	$o_tab_parser->getRowvalue(6);
	$vs_Opmerking		= 	$o_tab_parser->getRowvalue(7);
	
	print "\n Creating  ".$vn_Zilvermerken_id.".".$vs_Naam." and adding object. \n"; 

// ------------------------------------
// Waarden omvormen naar gepaste output
// ------------------------------------

// Datum-veld, bevat eigenaardige tekens (!) en (?) -> verwijderen
// (gesloten container - openen bij invullen

	$vs_Datum = str_replace('(!)', '', $vs_Datum);
	$vs_Datum = str_replace('(?)', '', $vs_Datum);

//zilvermerken Vorm en Stijl
// (gesloten container - openen bij invullen

//zilvermerkVorm_lijst -> zelfgemaakte lijst met nrs als ID

	$vn_ZM_Vorm_id = $t_list ->  getItemIDFromList('zilvermerkVorm_lijst', $vn_ZM_Vorm);

//zilvermerkType_lijst -> zelfgemaakte lijst met nrs als ID)

	$vn_ZM_Type_id = $t_list ->  getItemIDFromList('zilvermerkType_lijst', $vn_ZM_Type);

// zilvermerken Opmerking
// (gesloten container - openen bij invullen -> verwerken in if

// zilvermerken Plaats - open container -> opzoekactie in ca_Places
// de nrs zijn overgenomen voorafgegaan door 'plaats_'
	$t_place = new ca_places();
	$vm_Place_ids = $t_place -> getPlaceIDsByidno('plaats_'.$vn_Plaats_id, null);

	$vn_Place_id = $vm_Place_ids[0];

	print "Plaats: ".$vn_Plaats_id." - ".$vn_Place_id."\n";

// vervaardiger Zilvermerk -> open container -> kunstenaar_has_zilvermerk-bestand

//----------------		
// Create object
//----------------

	$t_zilver = new ca_objects();
	$t_zilver->setMode(ACCESS_WRITE);
	$t_zilver->set('type_id', $pn_object_type_id);
	$t_zilver->set('idno', "ZM_".$vn_Zilvermerken_id);	
	$t_zilver->set('access', 1);	//1:Accessible to public, 0:Not accessible to public
	$t_zilver->set('status', 4);	//4:Completed (zie ca_xxx.php

//-------------------------
// Waarden mappen      
//-------------------------
// name -> open container - geen if
	$t_zilver->addAttribute(array(
		'locale_id'	=>	$pn_locale_id,
		'name_singular'	=>	$vs_Naam,
		'name_plural'	=>	$vs_Naam
	), 'name');

// zilvermerkType -> gesloten container -> dus if
	if (isset($vn_ZM_Type_id)) {
		$t_zilver->addAttribute(array(
			'locale_id'			=>	$pn_locale_id,
			'zilvermerkType'		=>	$vn_ZM_Type_id
			), 'zilvermerkType');
	}
	//print "zilvermerkenType :".$vn_ZM_Type." - ".$vn_ZM_Type_id."\n";

// zilvermerkVorm -> gesloten contaner -> dus if
	if (isset($vn_ZM_Vorm_id)) {
		$t_zilver->addAttribute(array(
			'locale_id'			=>	$pn_locale_id,
			'zilvermerkVorm'		=>	$vn_ZM_Vorm_id
			), 'ZilvermerkVorm');
}
	//print "zilvermerkenVorm :".$vn_ZM_Vorm." - ".$vn_ZM_Vorm_id."\n";

//objectZilvermerkPlaats -> open container (dient verder uitgewerkt)
	if (trim($vs_Plaats) <> '0') {	
		$t_zilver->addAttribute(array(
			'locale_id'			=>	$pn_locale_id,
			'objectZilvermerkPlaats'	=>	"plaats_".$vn_Plaats_id
		), 'objectZilvermerkPlaats');
	}

	
	// insert the object
	$t_zilver->insert();

	if ($t_zilver->numErrors()) {
		print "ERROR INSERTING ".$vn_Zilvermerken_id.".".$vs_Naam." - ".$vs_Omschrijving.join('; ', $t_zilver->getErrors())."\n";
		continue;
	}else {
		print "insert succesvol \n";
	}
	
	$t_zilver->GetPrimaryKey();
	
	// --------------------------------------------------------------
	// Set a preferred label for the object
	// --------------------------------------------------------------
		
	$t_zilver->addLabel(
		array('name' => substr($vs_Naam,0,255)),
		$pn_locale_id, null, true
	);
		
	if ($t_zilver->numErrors()) {
		print "ERROR ADD LABEL TO ".$vn_Zilvermerken_id.".".$vs_Naam." - ".$vs_Omschrijving.join('; ', $t_zilver->getErrors())."\n";
		continue;
	} else {
		print "AddLabel succesvol\n";
	}
	
// zilvermerkDatum -> gesloten container -> dus if
	if (isset($vs_Datum)) {
		$t_zilver->addAttribute(array(
			'locale_id'			=>	$pn_locale_id,
			'zilvermerkDatum'		=>	$vs_Datum
		), 'zilvermerkDatum');
	}
	//print "Datum :".$vs_Datum."\n";

	$t_zilver->update();
	
	if ($t_zilver->numErrors()) {
		print "ERROR ADDING DATUM TO ".$vn_Zilvermerken_id.".".$vs_Naam." - ".$vs_Omschrijving.join('; ', $t_zilver->getErrors())."\n";
		$vs_Opmerking = $vs_Opmerking." - ".$vs_Datum;
		continue;
	} else {
		print "Geldige datum: update succesvol\n";
	}
	
// ZilvermerkOpmerking -> gesloten container - > dus if
	if (trim($vs_Opmerking) <> '') {	
		$t_zilver->addAttribute(array(
			'locale_id'		=>	$pn_locale_id,
			'zilvermerkOpmerking'	=>	$vs_Opmerking
			), 'zilvermerkOpmerking');
	}
	
	$t_zilver->update();
	
	if ($t_zilver->numErrors()) {
		print "ERROR ADDING OPMERKING TO ".$vn_Zilvermerken_id.".".$vs_Naam." - ".$vs_Omschrijving.join('; ', $t_zilver->getErrors())."\n";
		continue;
	} else {
		print "Geldige opmerking: update succesvol\n";
	}

// mogelijk moeten we hier nu nog een aantal relaties leggen

if (trim($vs_Plaats) <> '0' and  trim($vs_Plaats) <> null) {
	
	if ($vs_Plaats == '1259' or $vs_Plaats == '1172') {
		$vs_Plaats = '1169';
	}
	
	$vn_left_id = $t_zilver->getPrimarykey();
	//print "left_id:".$vn_left_id."\n";
	$t_zilver->set('object_id',$vn_left_id);
	
	$va_right_id = $t_plaats->getPlaceIDsByidno('plaats_'.($vn_Plaats_id), null);
	print "right_id_1:".$va_right_id[0]."\n";
	if ($va_right_id == NULL) {
		print " ERROR: PLAATS {$vn_Plaats_id} toevoegen \n";
	}else{
		$t_zilver -> addRelationship('ca_places', $va_right_id[0], $vn_objects_x_places_id);
	
		if ($t_zilver->numErrors()) {
			print "ERROR LINKING object and place : ".join('; ', $t_zilver->getErrors())."\n";
			continue;
		}
	}
}	

	unset($vn_ZM_Type_id);
	unset($vn_ZM_Vorm_id);
	unset($va_right_id);
	unset($vn_left_id);
	

	$vn_c++;
}


print "ENDED IMPORTING new_zilvermerken.csv\n";

?>
