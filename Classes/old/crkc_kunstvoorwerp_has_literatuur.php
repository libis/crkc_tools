<?php
	require_once("/www/libis/html/ca_crkc/setup.php");

	require_once(__CA_LIB_DIR__.'/core/Db.php');
	require_once(__CA_MODELS_DIR__.'/ca_locales.php');
	require_once(__CA_MODELS_DIR__.'/ca_objects.php');
	require_once(__CA_MODELS_DIR__.'/ca_occurrences.php');
	require_once(__CA_MODELS_DIR__.'/ca_objects_x_occurrences.php');
	require_once(__CA_LIB_DIR__.'/core/Parsers/DelimitedDataParser.php');
	
	$_ = new Zend_Translate('gettext', __CA_APP_DIR__.'/locale/en_US/messages.mo', 'nl_NL');
	
	$t_locale = new ca_locales();
	$pn_locale_id = $t_locale->loadLocaleByCode('nl_NL');		// default locale_id
	
	$t_list = new ca_lists();
	
	// relaties
	$t_rel_types = new ca_relationship_types();

	// relatie object-collection
	$vn_objects_x_occurrences_id = $t_rel_types->getRelationshipTypeID('ca_objects_x_occurrences', 'related');
	print "relatie object-literatuur: ".$vn_objects_x_occurrences_id."\n";
	$t_occurrences = new ca_occurrences();

	 
//***************************
//****   Kunstvoorwerp  *****
//***************************

// want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t"); 

print "IMPORTING new_litterateur into an array \n";

$va_literatuur = array();

// Read csv; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_litterateur.csv')) {
	die("Couldn't parse new_litterateur.csv data\n");	
}
	
print "READING new_litterateur.csv...\n";

$vn_c = 1;
	
$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------

while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_Literatuur_id	=	$o_tab_parser->getRowValue(1); //id kunstvoorwerp
	$vs_kortereferentie	 	=	$o_tab_parser->getRowValue(2); //bevat nummer (nummer + "-" + titel wordt idno
	$vs_BibliographischeReferentie	=	$o_tab_parser->getRowvalue(3); //titel
	
	$va_literatuur[$vn_Literatuur_id] = $vs_kortereferentie;

	$vn_c++;
}

print "IMPORTING kunstvoorwerpen \n";

//
// Step 2: Import
//
	// ----------------------------------------------------------------------
	// process main data (a tab delimited file)
	// ----------------------------------------------------------------------
	
	if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_kunstvoorwerp_has_litterateur.csv')) {
		die("Couldn't parse new_kunstvoorwerp_has_litterateur.csv data\n");	
	}
	
	$vn_c = 1;
	
	$o_tab_parser->nextRow(); // skip first row

//-------------------------
// waarden inlezen
//-------------------------
	
while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
		$vn_Kunstvoorwerp_id		=	$o_tab_parser->getRowValue(1);
		$vn_Literatuur_id		=	$o_tab_parser->getRowValue(2);
		
		$vn_pagina = $o_tab_parser->getRowValue(3);
		$vn_opmerking = $o_tab_parser->getRowValue(4);
		
		print "PROCESSING {$vn_Kunstvoorwerp_id}\n";

// ------------------------------------
// Waarden omvormen naar gepaste output
// ------------------------------------


		
//----------------		
// Search object
//----------------

	$t_object = new ca_objects();
	$t_object->setMode(ACCESS_WRITE);
               
	$va_Kunstvoorwerp_keys = ($t_object->getObjectIDsByidnoPart(("%KV_".$vn_Kunstvoorwerp_id.")"),null) );
	
	$dimensions = sizeof($va_Kunstvoorwerp_keys[0]);
		
	print "{$dimensions}\n";
		
if ($dimensions > 0) {	
	$vn_Kunstvoorwerp_key = $va_Kunstvoorwerp_keys[0];
	
	print "Primary Kunstvoorwerp-key: ".$vn_Kunstvoorwerp_key." \n";
	
	$t_object->load($vn_Kunstvoorwerp_key);
	
	if ($t_object->numErrors()) {
		print "\tERROR LOADING OBJECT {$vn_Kunstvoorwerp_id}: ".join('; ', $t_object->getErrors())."\n";
		continue;
	} else {
		print "object {$vn_Kunstvoorwerp_id} geladen \n";
	}


	

	// relaties 
	$vn_left_id = $t_object->getPrimarykey();
	print "left_id:".$vn_left_id."\n";
	$t_object->set('object_id', $vn_left_id);
	
	$va_right_id = $t_occurrences->getOccurrenceIDsByidno($va_literatuur[$vn_Literatuur_id]);
	print "right_id_1:".$va_right_id[0]."\n";
	if ($va_right_id == NULL) {
		print " ERROR: LITERATUUR {$vn_Literatuur_id} toevoegen \n";
	}else{
		$t_object -> addRelationship('ca_occurrences', $va_right_id[0], $vn_objects_x_occurrences_id);
		print "done\n";
		if ($t_object->numErrors()) {
			print "ERROR LINKING object and literatuur : ".join('; ', $t_object->getErrors())."\n";
			continue;
		} else {
			print "link object-literatuur succesvol\n";
		}
		
		$t_object->addAttribute(array(
			'locale_id'			=>	$pn_locale_id,
			'literatuurPagina'		=>	$vn_pagina,
			'literatuurOpmerking'		=>	$vn_opmerking
		), 'literatuurInfo');
		
		$t_object->update();
	
		if ($t_object->numErrors()) {
			print "\tERROR UPDATING OBJECT {$vn_Kunstvoorwerp_key}: ".join('; ', $t_object->getErrors())."\n";
			continue;
		} else {
			print "update succesvol\n";
		}
	}

} else {
	print "Kunstvoorwerp bestaat (nog) NIET \n";
}

		print "=============volgend record============== \n\n";
	
		$vn_c++;
		
		print "=================Einde verwerking kunstwerk_has_collection ============================= \n \n";
}

print "gedaan";
?>
