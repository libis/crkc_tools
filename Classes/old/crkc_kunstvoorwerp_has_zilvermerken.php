<?php
require_once("/www/libis/html/ca_crkc/setup.php");

require_once(__CA_LIB_DIR__.'/core/Db.php');
require_once(__CA_MODELS_DIR__.'/ca_locales.php');
require_once(__CA_MODELS_DIR__.'/ca_objects.php');
require_once(__CA_MODELS_DIR__.'/ca_objects_x_objects.php');
require_once(__CA_LIB_DIR__.'/core/Parsers/DelimitedDataParser.php');

$_ = new Zend_Translate('gettext', __CA_APP_DIR__.'/locale/en_US/messages.mo', 'nl_NL');

$t_locale = new ca_locales();
$pn_locale_id = $t_locale->loadLocaleByCode('nl_NL');		// default locale_id

$t_list = new ca_lists();

// relaties
$t_rel_types = new ca_relationship_types();

// relatie object-collection
$vn_objects_x_objects_id = $t_rel_types->getRelationshipTypeID('ca_objects_x_objects', 'related');
print "relatie kunstvoorwerp-zilvermerk: ".$vn_objects_x_objects_id."\n";
	
$t_object = new ca_objects();
$t_object->setMode(ACCESS_WRITE);

$t_zilver = new ca_objects();
$t_zilver->setMode(ACCESS_WRITE);

// want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t"); 

print "IMPORTING new_kunstvoorwerp_has_zilvermerken into an array \n";

$va_KVhasZM = array();

// Read csv; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_kunstvoorwerp_has_zilvermerken.csv')) {
	die("Couldn't parse new_kunstvoorwerp_has_zilvermerken.csv data\n");	
}
	
$vn_c = 1;
	
$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------

while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_Kunstvoorwerp_id	=	$o_tab_parser->getRowValue(1); 
	$vn_Zilvermerk_id	=	$o_tab_parser->getRowValue(2); 
	$vs_Plaats		=	$o_tab_parser->getRowvalue(3);
	$vs_Opmerking		=	$o_tab_parser->getRowvalue(4);

	$vn_Kunstvoorwerp_id = "KV_".$vn_Kunstvoorwerp_id;
	
	$va_KVhasZM[$vn_Kunstvoorwerp_id] = array($vs_Plaats, $vs_Opmerking);

// ------------------------------------
// Waarden omvormen naar gepaste output
// ------------------------------------
	$vb_Update = false;

//----------------		
// Search object
//----------------

	$va_Kunstvoorwerp_keys = ($t_object->getObjectIDsByidnoPart(("%".$vn_Kunstvoorwerp_id.")"),null) );
	
	$dimensions = sizeof($va_Kunstvoorwerp_keys);
		
	print "{$dimensions}\n";
		
	if ($dimensions > 0) {
		
		$vn_Kunstvoorwerp_key = $va_Kunstvoorwerp_keys[0];
		
		print "Primary Kunstvoorwerp-key: ".$vn_Kunstvoorwerp_key." \n";
		
		$t_object->load($vn_Kunstvoorwerp_key);
		
		// relaties 
		$vn_left_id = $t_object->getPrimarykey();
		
		print "left_id:".$vn_left_id."\n";
		
		$t_object->set('object_id', $vn_left_id);
		
		$va_right_id = $t_zilver->getObjectIDsByidno("ZM_".$vn_Zilvermerk_id);
		
		print "right_id_1:".$va_right_id[0]."\n";
		
		$aantal = sizeof($va_right_id);
		
		if ($aantal > 0) {
			
			$t_object->addRelationship('ca_objects', $va_right_id[0], $vn_objects_x_objects_id);
			
			print "done\n";
			
			if ($t_object->numErrors()) {
				print "ERROR LINKING object and object : ".join('; ', $t_object->getErrors())."\n";
				continue;
			} else {
				print "link object-object succesvol\n";
						
				if (strlen(trim($vs_Plaats)) > 0 ) {
					$t_object->addAttribute(array(
						'locale_id'			=>	$pn_locale_id,
						'objectZilvermerkPlaats'	=>	$vs_Plaats
					), 'objectZilvermerkPlaats');
					$vb_Update = true;
				}
				
				if (strlen(trim($vs_Opmerking)) > 0 ) {
					$t_object->addAttribute(array(
						'locale_id'			=>	$pn_locale_id,
						'objectZilvermerkOpmerking'	=>	$vn_opmerking
					), 'objectZilvermerkOpmerking');
					$vb_Update = true;
				}
				
				if ($vb_Update) {
				
					$t_object->update();
				
					if ($t_object->numErrors()) {
						print "\tERROR UPDATING OBJECT {$vn_Kunstvoorwerp_key}: ".join('; ', $t_object->getErrors())."\n";
						continue;
					} else {
						print "update succesvol\n";
					}
				} else {
					print "geen update nodig \n";
				}
			}
		} else {
			print " Zilvermerk bestaat (nog) NIET \n";
		}
	} else {
		print "Kunstvoorwerp bestaat (nog) NIET \n";
	}

	print "===={$vn_c}=========volgend record============== \n\n";

	$vn_c++;
}

print "gedaan ermee";
?>
