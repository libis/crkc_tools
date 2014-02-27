<?php

/*
 * Step 1: Initialisation
 */
 
set_time_limit(36000);
require_once("/www/libis/html/ca_crkc/setup.php");

require_once(__CA_LIB_DIR__.'/core/Db.php');
require_once(__CA_MODELS_DIR__.'/ca_locales.php');
require_once(__CA_MODELS_DIR__.'/ca_objects.php');
require_once(__CA_MODELS_DIR__.'/ca_entities.php');
require_once(__CA_MODELS_DIR__.'/ca_objects_x_entities.php');

require_once(__CA_LIB_DIR__.'/core/Parsers/DelimitedDataParser.php');


$_ = new Zend_Translate('gettext', __CA_APP_DIR__.'/locale/en_US/messages.mo', 'nl_NL');

$t_locale = new ca_locales();
$pn_locale_id = $t_locale->loadLocaleByCode('nl_NL');

// get occurrence_relationship types
$t_rel_types = new ca_relationship_types();
$vn_objects_x_entities_id =	$t_rel_types->getRelationshipTypeID('ca_objects_x_entities', 'vervaardigerRelatie');

print "object_entity_relatie: {$vn_objects_x_entities_id} \n";

//$t_entity = new ca_entities();
//$t_entity->setMode(ACCESS_WRITE);

$t_zilver = new ca_objects();
$t_zilver->setMode(ACCESS_WRITE);


//********************************************
//****  kunstenaar_has_zilvermerken      *****
//********************************************

print "IMPORTING kunstenaar_has_zilvermerken \n";

/*
// * Step 2: Import
*/

 // want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t"); 

// Read csv; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_kunstenaar_has_zilvermerken.csv')) {
	die("Couldn't parse new_kunstenaar_has_zilvermerken.csv data\n");	
}
	
print "READING new_kunstenaar_has_zilvermerken.csv...\n";

$vn_c = 1;
	
$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------
while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_Kunstenaar_id	=	$o_tab_parser->getRowValue(1);
	$vn_Zilvermerk_id	=	$o_tab_parser->getRowValue(2);
	$vs_Opmerking		=	$o_tab_parser->getRowvalue(3);
	
// ------------------------------------
// Waarden omvormen naar gepaste output
// ------------------------------------
	print "========{$vn_c}=====verv_{$vn_Kunstenaar_id} = ZM_{$vn_Zilvermerk_id}=====\n";

//	$vb_Opmerking = false;
//----------------		
// Create object
//----------------

//	if (strlen(trim($vs_Opmerking)) > 0 and (trim($vs_Opmerking) != '/' )) {
//		$vb_Opmerking = true;
//	}

// -------------------------------------------
// Maak link tussen occurrences
//--------------------------------------------
	
	$va_ZilverMerken_id = ($t_zilver->getObjectIDsByidno("ZM_".$vn_Zilvermerk_id, null));
	$dimensions = sizeof($va_ZilverMerken_id);
	
	if ($dimensions > 0) {
	
		$vn_ZilverMerken_id = $va_ZilverMerken_id[0];
	
		$t_zilver->load($vn_ZilverMerken_id);
		$t_zilver->getPrimaryKey();
		
	//	$t_zilver->set('object_id', $va_left_id[0]);
		
	//function addRelationship($pm_rel_table_name_or_num, $pn_rel_id, $pm_type_id=null, $ps_effective_date=null, $ps_source_info=null, $ps_direction=null, $pn_rank=null)	
		$va_right_id = $t_entity->getEntityIDsByidno('verv_'.$vn_Kunstenaar_id);
		$dims = sizeof($va_right_id);
		if ($dims > 0) {
			//print "right_id_1:".$va_right_id[0]."\n";
			$t_zilver->addRelationship('ca_entities', $va_right_id[0], $vn_objects_x_entities_id);
			//print "relatie succesvol toegevoegd\n";
			if ($t_zilver->numErrors()) {
				print "ERROR LINKING Object Zilvermerk {$vn_Zilvermerk_id} and Entity Vervaardiger {$vn_Kunstenaar_id} : ".join('; ', $t_zilver->getErrors())."\n";
				continue;
			} else {
				print "link object-vervaardiger succesvol toegevoegd \n";
/*
				if ($vb_Opmerking) {
					$t_entity->load($va_right_id[0]);
					$t_entity->getPrimaryKey();
					$t_entity->addAttribute(array(
						'locale_id'			=>	$pn_locale_id,
						'vervaardigerZilvermerkOpm'	=>	$vs_Opmerking
					), 'vervaardigerZilvermerkOpm');
	
					$t_entity->update();
			
					if ($t_entity->numErrors()) {
						print "ERROR UPDATING KUNSTENAAR {$vn_Kunstenaar_id}: ".join('; ', $t_entity->getErrors())."\n";
						continue;
					} else {
						print "update entity Vervaardiger {$vn_Kunstenaar_id} succesvol\n";
					}		
					
				}
 * 
 */
			}
		
		} else {
			print "ERROR: vervaardiger {$vn_Kunstenaar_id} niet gevonden \n";
		}
			
			
	} else {
		
		print "ERROR: object {$vn_Zilvermerk_id} niet gevonden \n";
		
	}
	
	$va_right_id = null;
	$va_ZilverMerken_id = null;
	
	$vn_c++;
}

print "ENDED IMPORTING new_tentoonstelling_has_literatuur_bis.csv\n";

?>
