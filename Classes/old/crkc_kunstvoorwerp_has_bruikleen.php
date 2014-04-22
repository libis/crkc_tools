<?php
	require_once("/www/libis/html/ca_crkc/setup.php");

	
	require_once(__CA_LIB_DIR__.'/core/Db.php');
	require_once(__CA_MODELS_DIR__.'/ca_locales.php');
	require_once(__CA_MODELS_DIR__.'/ca_objects.php');
	require_once(__CA_MODELS_DIR__.'/ca_loans.php');
	require_once(__CA_MODELS_DIR__.'/ca_loans_x_objects.php');
	require_once(__CA_LIB_DIR__.'/core/Parsers/DelimitedDataParser.php');
	
	$_ = new Zend_Translate('gettext', __CA_APP_DIR__.'/locale/en_US/messages.mo', 'nl_NL');
	
	$t_locale = new ca_locales();
	$pn_locale_id = $t_locale->loadLocaleByCode('nl_NL');		// default locale_id
	
	$t_list = new ca_lists();
	
	// relaties
	$t_rel_types = new ca_relationship_types();

	// relatie object-bruikleen
	$vn_loans_x_objects_id = $t_rel_types->getRelationshipTypeID('ca_loans_x_objects', 'loan');
	print "relatie object-bruikleen: ".$vn_loans_x_objects_id."\n";
	$t_loans = new ca_loans();
	// omgekeerde relatie dus loans moet geupdatet worden
	$t_loans->setMode(ACCESS_WRITE);

	 
//***************************
//****   Kunstvoorwerp  *****
//***************************

// want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t"); 
/* Dit kan helemaal weggelaten worden
print "IMPORTING new_bruikleen into an array \n";

$va_bruikleen = array();

// Read csv; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_bruikleen.csv')) {
	die("Couldn't parse new_bruikleen.csv data\n");	
}
	
print "READING new_bruikleen.csv...\n";

$vn_c = 1;
	
$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------

while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_Bruikleen_id	=	$o_tab_parser->getRowValue(1); //id kunstvoorwerp
	
	$va_bruikleen[$vn_Bruikleen_id] = "bruikl_" . $vn_Bruikleen_id;
	$vn_c++;
}
*/
print "IMPORTING kunstvoorwerpen_has_bruikleen \n";

//
// Step 2: Import
//
	// want to parse comma delimited data? Pass a comma here instead of a tab.
	$o_tab_parser = new DelimitedDataParser("\t"); 
	
	// ----------------------------------------------------------------------
	// process main data (a tab delimited file)
	// ----------------------------------------------------------------------
	
	if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_kunstvoorwerp_has_bruikleen.csv')) {
		die("Couldn't parse new_kunstvoorwerp_has_bruikleen.csv data\n");	
	}
	
	$vn_c = 1;
	
	$o_tab_parser->nextRow(); // skip first row

//-------------------------
// waarden inlezen
//-------------------------
	
while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_Kunstvoorwerp_id		=	$o_tab_parser->getRowValue(1);
		
	$vn_Bruikleen_id		=	$o_tab_parser->getRowValue(2);
		
	$vs_opmerkingen			=	$o_tab_parser->getRowValue(3);
		
	$vs_Bruikleen_id = "bruikl_".$vn_Bruikleen_id;
	
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
		
		//print "bruikleen ".$vs_Bruikleen_id;
		$va_right_id = $t_loans->getLoanIDsByidno($vs_Bruikleen_id);
		//print "test" . $t_loans->get('loan_id');
		print "right_id_1:".$va_right_id[0]."\n";
		if ($va_right_id == NULL) {
			print " ERROR: LOAN {$vs_Bruikleen_id} toevoegen \n";
		}else{
			// loans moet geupdate worden, niet objects
			$t_loans->set('loan_id', $va_right_id[0]);
			$t_loans -> addRelationship('ca_objects', $vn_left_id, $vn_loans_x_objects_id);
			print "done\n";
			if ($t_loans->numErrors()) {
				print "ERROR LINKING loan and kunstvoorwerp : ".join('; ', $t_loans->getErrors())."\n";
				continue;
			} else {
				print "link bruikleen-kunstvoorwerp succesvol\n";
			}
			
			$t_loans->addAttribute(array(
				'locale_id'			=>	$pn_locale_id,
				'bruikleenOpmerkingen'		=>	$vs_opmerkingen
			), 'bruikleenOpmerkingen');
			
			$t_loans->update();
		
			if ($t_loans->numErrors()) {
				print "\tERROR UPDATING LOAN {$vs_Bruikleen_id}: ".join('; ', $t_object->getErrors())."\n";
				continue;
			} else {
				print "update succesvol\n";
			}
		}
	
	} else {
		
		print "Kunstvoorwerp bestaat (nog) NIET \n";
	}

	print "==={$vn_c}==========volgend record============== \n\n";
	
	$vn_c++;

}

print "gedaan";
?>