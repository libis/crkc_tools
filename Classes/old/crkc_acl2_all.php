<?php
/* 
   Dit script wordt gebruikt de ca_acl tabel te vullen voor ALLES (collectie en objecten) en vraag en aanbod
   
*/
/*
 * Step 1: Initialisation
 */
ini_set('memory_limit', '1000m'); 
set_time_limit(36000);
require_once("/www/libis/html/ca_crkc/setup.php");

require_once(__CA_LIB_DIR__.'/core/Db.php');
require_once(__CA_MODELS_DIR__.'/ca_locales.php');
require_once(__CA_MODELS_DIR__.'/ca_acl.php');
require_once(__CA_MODELS_DIR__.'/ca_entities.php');
require_once(__CA_MODELS_DIR__.'/ca_user_groups.php');
require_once(__CA_MODELS_DIR__.'/ca_occurrences.php');
require_once(__CA_MODELS_DIR__.'/ca_places.php');
require_once(__CA_MODELS_DIR__.'/ca_storage_locations.php');
require_once(__CA_MODELS_DIR__.'/ca_loans.php');

require_once(__CA_LIB_DIR__.'/core/Parsers/DelimitedDataParser.php');

$_ = new Zend_Translate('gettext', __CA_APP_DIR__.'/locale/en_US/messages.mo', 'nl_NL');

$t_locale = new ca_locales();
$pn_locale_id = $t_locale->loadLocaleByCode('nl_NL');

//************************************
//****       ACL		 *****
//************************************

print "ADD ACL\n";
	// Eerst alle personen en instellingen en vervaardigers linken
	$t_entities = new ca_entities();
	$entity_ids = array();
	
	// Dan alle gebeurtenissen, tentoonstellingen, referenties linken
	$t_occurrences = new ca_occurrences();
	$occurrence_ids = array();
	
	// Dan alle plaatsen linken
	$t_places = new ca_places();
	$place_ids = array();
	
	// bewaarplaatsen
	$t_storage_locations = new ca_storage_locations();
	$storage_location_ids = array();
	
	//bruiklenen
	$t_loans = new ca_loans();
	$loan_ids = array();
	
	// voorlopig worden de rechten op ca_lists niet gezet
	
	// Group id moet opgezocht worden
	$t_group = new ca_user_groups();
	$group_id = array();
	
	$t_acl = new ca_acl();
	$t_acl->setMode(ACCESS_WRITE);
	
	$crkc_group_ids = array();
	// Ophalen van de group_ids;
	$crkc_hdf_red = "crkc_hfdred";
	$group_id = $t_group->getGroupIdByCode($crkc_hdf_red);
	array_push($crkc_group_ids,$group_id[0]);
	
	$crkc_red = "crkc_red";
	$group_id = $t_group->getGroupIdByCode($crkc_red);
	array_push($crkc_group_ids,$group_id[0]);
	
	$crkc_regbeh = "crkc_regbeh";
	$group_id = $t_group->getGroupIdByCode($crkc_regbeh);
	array_push($crkc_group_ids,$group_id[0]);
	
	$crkc_raad = "crkc_raad";
	$group_id = $t_group->getGroupIdByCode($crkc_raad);
	array_push($crkc_group_ids,$group_id[0]);
	
	$crkc_ond = "crkc_ond";
	$group_id = $t_group->getGroupIdByCode($crkc_ond);
	array_push($crkc_group_ids,$group_id[0]);
	
	$crkc_basreg = "crkc_basreg";
	$group_id = $t_group->getGroupIdByCode($crkc_basreg);
	array_push($crkc_group_ids,$group_id[0]);
	
	// POV wordt mee in de crkc group gestoken
	$pov_hdf_red = "pov_hfdred";
	$group_id = $t_group->getGroupIdByCode($pov_hdf_red);
	array_push($crkc_group_ids,$group_id[0]);
	
	$pov_red = "pov_red";
	$group_id = $t_group->getGroupIdByCode($pov_red);
	array_push($crkc_group_ids,$group_id[0]);
	
	$pov_regbeh = "pov_regbeh";
	$group_id = $t_group->getGroupIdByCode($pov_regbeh);
	array_push($crkc_group_ids,$group_id[0]);
	
	$pov_raad = "pov_raad";
	$group_id = $t_group->getGroupIdByCode($pov_raad);
	array_push($crkc_group_ids,$group_id[0]);
	
	$pov_ond = "pov_ond";
	$group_id = $t_group->getGroupIdByCode($pov_ond);
	array_push($crkc_group_ids,$group_id[0]);
	
	$pov_basreg = "pov_basreg";
	$group_id = $t_group->getGroupIdByCode($pov_basreg);
	array_push($crkc_group_ids,$group_id[0]);
	
	// PA wordt in crkc_group_ids gestoken
	$pa_hdf_red = "pa_hfdred";
	$group_id = $t_group->getGroupIdByCode($pa_hdf_red);
	array_push($crkc_group_ids,$group_id[0]);
	
	$pa_red = "pa_red";
	$group_id = $t_group->getGroupIdByCode($pa_red);
	array_push($crkc_group_ids,$group_id[0]);
	
	$pa_regbeh = "pa_regbeh";
	$group_id = $t_group->getGroupIdByCode($pa_regbeh);
	array_push($crkc_group_ids,$group_id[0]);
	
	$pa_raad = "pa_raad";
	$group_id = $t_group->getGroupIdByCode($pa_raad);
	array_push($crkc_group_ids,$group_id[0]);
	
	$pa_ond = "pa_ond";
	$group_id = $t_group->getGroupIdByCode($pa_ond);
	array_push($crkc_group_ids,$group_id[0]);
	
	$pa_basreg = "pa_basreg";
	$group_id = $t_group->getGroupIdByCode($pa_basreg);
	array_push($crkc_group_ids,$group_id[0]);
	
	// PWV steken we ook in crkc_group_ids
	$pwv_hdf_red = "pwv_hfdred";
	$group_id = $t_group->getGroupIdByCode($pwv_hdf_red);
	array_push($crkc_group_ids,$group_id[0]);
	
	$pwv_red = "pwv_red";
	$group_id = $t_group->getGroupIdByCode($pwv_red);
	array_push($crkc_group_ids,$group_id[0]);
	
	$pwv_regbeh = "pwv_regbeh";
	$group_id = $t_group->getGroupIdByCode($pwv_regbeh);
	array_push($crkc_group_ids,$group_id[0]);
	
	$pwv_raad = "pwv_raad";
	$group_id = $t_group->getGroupIdByCode($pwv_raad);
	array_push($crkc_group_ids,$group_id[0]);
	
	$pwv_ond = "pwv_ond";
	$group_id = $t_group->getGroupIdByCode($pwv_ond);
	array_push($crkc_group_ids,$group_id[0]);
	
	$pwv_basreg = "pwv_basreg";
	$group_id = $t_group->getGroupIdByCode($pwv_basreg);
	array_push($crkc_group_ids,$group_id[0]);
	
	// entity
	$entity_ids = $t_entities->getAllEntities();
	if(!empty($crkc_group_ids) && !empty($entity_ids))
	{
		foreach($entity_ids as $entity_id)
		{
			foreach($crkc_group_ids as $crkc_group_id)
			{
				$t_acl->set('group_id',$crkc_group_id);
				// ca_entities is 20 zie datamodel.conf
				$t_acl->set('table_num',20);
				$t_acl->set('row_id',$entity_id);
				// 3= edit + delete (hfdred, red)
				// 2 = edit (crkc_regbeh, basreg)
				// 1 = read (crkc_raad, crkc_ond)
				// 0 = none (via app.conf)
				$groupCode = $t_group->getGroupCodeById($crkc_group_id);
				if(strcmp($groupCode[0],'crkc_hfdred') == 0
				|| strcmp($groupCode[0],'crkc_red') == 0
				|| strcmp($groupCode[0],'pov_hfdred') == 0
				|| strcmp($groupCode[0],'pov_red') == 0
				|| strcmp($groupCode[0],'pa_hfdred') == 0
				|| strcmp($groupCode[0],'pa_red') == 0
				|| strcmp($groupCode[0],'pwv_hfdred') == 0
				|| strcmp($groupCode[0],'pwv_red') == 0)
				{
					$t_acl->set('access',3);
				} elseif(strcmp($groupCode[0],'crkc_regbeh') == 0
				|| strcmp($groupCode[0],'crkc_basreg') == 0
				|| strcmp($groupCode[0],'pov_regbeh') == 0
				|| strcmp($groupCode[0],'pov_basreg') == 0
				|| strcmp($groupCode[0],'pa_regbeh') == 0
				|| strcmp($groupCode[0],'pa_basreg') == 0
				|| strcmp($groupCode[0],'pwv_regbeh') == 0
				|| strcmp($groupCode[0],'pwv_basreg') == 0) {
					$t_acl->set('access',2);
				} elseif(strcmp($groupCode[0],'crkc_raad') == 0
					|| strcmp($groupCode[0],'crkc_ond') == 0
					|| strcmp($groupCode[0],'pov_raad') == 0
					|| strcmp($groupCode[0],'pov_ond') == 0
					|| strcmp($groupCode[0],'pa_raad') == 0
					|| strcmp($groupCode[0],'pa_ond') == 0
					|| strcmp($groupCode[0],'pwv_raad') == 0
					|| strcmp($groupCode[0],'pwv_ond') == 0) {
					$t_acl->set('access',1);
				} else {
					print "Ongekend recht: " + $groupCode[0]; 
				}
				$t_acl->insert();
			
				if ($t_acl->numErrors()) {
					print "\tERROR INSERT Group: " . $groupCode[0] . "/". $entity_id. ": ".join('; ', $t_acl->getErrors())."\n";
					continue;
				}
			}
		}
		//geheugen besparen
		$entity_ids = array();
	}
	else {
		print "Geen Entity gelinkt";
	}
	$occurrence_ids = $t_occurrences->getAllOccurrences();
	if(!empty($crkc_group_ids) && !empty($occurrence_ids))
	{
		foreach($occurrence_ids as $occurrence_id)
		{
			foreach($crkc_group_ids as $crkc_group_id)
			{
				$t_acl->set('group_id',$crkc_group_id);
				// ca_occurences is 67
				$t_acl->set('table_num',67);
				$t_acl->set('row_id',$occurrence_id);
				// 3= edit + delete (hfdred, red)
				// 2 = edit (crkc_regbeh, basreg)
				// 1 = read (crkc_raad, crkc_ond)
				// 0 = none (via app.conf)
				$groupCode = $t_group->getGroupCodeById($crkc_group_id);
				if(strcmp($groupCode[0],'crkc_hfdred') == 0
				|| strcmp($groupCode[0],'crkc_red') == 0
				|| strcmp($groupCode[0],'pov_hfdred') == 0
				|| strcmp($groupCode[0],'pov_red') == 0
				|| strcmp($groupCode[0],'pa_hfdred') == 0
				|| strcmp($groupCode[0],'pa_red') == 0
				|| strcmp($groupCode[0],'pwv_hfdred') == 0
				|| strcmp($groupCode[0],'pwv_red') == 0)
				{
					$t_acl->set('access',3);
				} elseif(strcmp($groupCode[0],'crkc_regbeh') == 0
				|| strcmp($groupCode[0],'crkc_basreg') == 0
				|| strcmp($groupCode[0],'pov_regbeh') == 0
				|| strcmp($groupCode[0],'pov_basreg') == 0
				|| strcmp($groupCode[0],'pa_regbeh') == 0
				|| strcmp($groupCode[0],'pa_basreg') == 0
				|| strcmp($groupCode[0],'pwv_regbeh') == 0
				|| strcmp($groupCode[0],'pwv_basreg') == 0) {
					$t_acl->set('access',2);
				} elseif(strcmp($groupCode[0],'crkc_raad') == 0
					|| strcmp($groupCode[0],'crkc_ond') == 0
					|| strcmp($groupCode[0],'pov_raad') == 0
					|| strcmp($groupCode[0],'pov_ond') == 0
					|| strcmp($groupCode[0],'pa_raad') == 0
					|| strcmp($groupCode[0],'pa_ond') == 0
					|| strcmp($groupCode[0],'pwv_raad') == 0
					|| strcmp($groupCode[0],'pwv_ond') == 0) {
					$t_acl->set('access',1);
				} else {
					print "Ongekend recht: " + $groupCode[0]; 
				}
				$t_acl->insert();
			
				if ($t_acl->numErrors()) {
					print "\tERROR INSERT Group: " . $groupCode[0] . "/". $occurrence_id. ": ".join('; ', $t_acl->getErrors())."\n";
					continue;
				}
			}
		}
		//geheugen besparen
		$occurrence_ids = array();
	}
	else {
		print "Geen occurrences gelinkt";
	}
	
	$place_ids = $t_places->getAllPlaces();
	if(!empty($crkc_group_ids) && !empty($place_ids))
	{
		foreach($place_ids as $place_id)
		{
			foreach($crkc_group_ids as $crkc_group_id)
			{
				$t_acl->set('group_id',$crkc_group_id);
				// ca_places is 72
				$t_acl->set('table_num',72);
				$t_acl->set('row_id',$place_id);
				// 3= edit + delete (hfdred, red)
				// 2 = edit (crkc_regbeh, basreg)
				// 1 = read (crkc_raad, crkc_ond)
				// 0 = none (via app.conf)
				$groupCode = $t_group->getGroupCodeById($crkc_group_id);
				if(strcmp($groupCode[0],'crkc_hfdred') == 0
				|| strcmp($groupCode[0],'crkc_red') == 0
				|| strcmp($groupCode[0],'pov_hfdred') == 0
				|| strcmp($groupCode[0],'pov_red') == 0
				|| strcmp($groupCode[0],'pa_hfdred') == 0
				|| strcmp($groupCode[0],'pa_red') == 0
				|| strcmp($groupCode[0],'pwv_hfdred') == 0
				|| strcmp($groupCode[0],'pwv_red') == 0)
				{
					$t_acl->set('access',3);
				} elseif(strcmp($groupCode[0],'crkc_regbeh') == 0
				|| strcmp($groupCode[0],'crkc_basreg') == 0
				|| strcmp($groupCode[0],'pov_regbeh') == 0
				|| strcmp($groupCode[0],'pov_basreg') == 0
				|| strcmp($groupCode[0],'pa_regbeh') == 0
				|| strcmp($groupCode[0],'pa_basreg') == 0
				|| strcmp($groupCode[0],'pwv_regbeh') == 0
				|| strcmp($groupCode[0],'pwv_basreg') == 0) {
					$t_acl->set('access',2);
				} elseif(strcmp($groupCode[0],'crkc_raad') == 0
					|| strcmp($groupCode[0],'crkc_ond') == 0
					|| strcmp($groupCode[0],'pov_raad') == 0
					|| strcmp($groupCode[0],'pov_ond') == 0
					|| strcmp($groupCode[0],'pa_raad') == 0
					|| strcmp($groupCode[0],'pa_ond') == 0
					|| strcmp($groupCode[0],'pwv_raad') == 0
					|| strcmp($groupCode[0],'pwv_ond') == 0) {
					$t_acl->set('access',1);
				} else {
					print "Ongekend recht: " + $groupCode[0]; 
				}
				$t_acl->insert();
			
				if ($t_acl->numErrors()) {
					print "\tERROR INSERT Group: " . $groupCode[0] . "/". $place_id. ": ".join('; ', $t_acl->getErrors())."\n";
					continue;
				}
			}
		}
		//geheugen besparen
		$place_ids = array();
	}
	else {
		print "Geen places gelinkt";
	}
	
	$storage_location_ids = $t_storage_locations->getStorageLocations();
	if(!empty($crkc_group_ids) && !empty($storage_location_ids))
	{
		foreach($storage_location_ids as $storage_location_id)
		{
			foreach($crkc_group_ids as $crkc_group_id)
			{
				$t_acl->set('group_id',$crkc_group_id);
				// ca_storage_locations is 89
				$t_acl->set('table_num',89);
				$t_acl->set('row_id',$storage_location_id);
				// 3= edit + delete (hfdred, red)
				// 2 = edit (crkc_regbeh, basreg)
				// 1 = read (crkc_raad, crkc_ond)
				// 0 = none (via app.conf)
				$groupCode = $t_group->getGroupCodeById($crkc_group_id);
				if(strcmp($groupCode[0],'crkc_hfdred') == 0
				|| strcmp($groupCode[0],'crkc_red') == 0
				|| strcmp($groupCode[0],'pov_hfdred') == 0
				|| strcmp($groupCode[0],'pov_red') == 0
				|| strcmp($groupCode[0],'pa_hfdred') == 0
				|| strcmp($groupCode[0],'pa_red') == 0
				|| strcmp($groupCode[0],'pwv_hfdred') == 0
				|| strcmp($groupCode[0],'pwv_red') == 0)
				{
					$t_acl->set('access',3);
				} elseif(strcmp($groupCode[0],'crkc_regbeh') == 0
				|| strcmp($groupCode[0],'crkc_basreg') == 0
				|| strcmp($groupCode[0],'pov_regbeh') == 0
				|| strcmp($groupCode[0],'pov_basreg') == 0
				|| strcmp($groupCode[0],'pa_regbeh') == 0
				|| strcmp($groupCode[0],'pa_basreg') == 0
				|| strcmp($groupCode[0],'pwv_regbeh') == 0
				|| strcmp($groupCode[0],'pwv_basreg') == 0) {
					$t_acl->set('access',2);
				} elseif(strcmp($groupCode[0],'crkc_raad') == 0
					|| strcmp($groupCode[0],'crkc_ond') == 0
					|| strcmp($groupCode[0],'pov_raad') == 0
					|| strcmp($groupCode[0],'pov_ond') == 0
					|| strcmp($groupCode[0],'pa_raad') == 0
					|| strcmp($groupCode[0],'pa_ond') == 0
					|| strcmp($groupCode[0],'pwv_raad') == 0
					|| strcmp($groupCode[0],'pwv_ond') == 0) {
					$t_acl->set('access',1);
				} else {
					print "Ongekend recht: " + $groupCode[0]; 
				}
				$t_acl->insert();
			
				if ($t_acl->numErrors()) {
					print "\tERROR INSERT Group: " . $groupCode[0] . "/". $storage_location_id. ": ".join('; ', $t_acl->getErrors())."\n";
					continue;
				}
			}
		}
		//geheugen besparen
		$storage_location_ids = array();
	}
	else {
		print "Geen storage locations gelinkt";
	}
	
	$loan_ids = $t_loans->getLoans();
	if(!empty($crkc_group_ids) && !empty($loan_ids))
	{
		foreach($loan_ids as $loan_id)
		{
			foreach($crkc_group_ids as $crkc_group_id)
			{
				$t_acl->set('group_id',$crkc_group_id);
				// ca_loans is 133
				$t_acl->set('table_num',133);
				$t_acl->set('row_id',$loan_id);
				// 3= edit + delete (hfdred, red)
				// 2 = edit (crkc_regbeh, basreg)
				// 1 = read (crkc_raad, crkc_ond)
				// 0 = none (via app.conf)
				$groupCode = $t_group->getGroupCodeById($crkc_group_id);
				if(strcmp($groupCode[0],'crkc_hfdred') == 0
				|| strcmp($groupCode[0],'crkc_red') == 0
				|| strcmp($groupCode[0],'pov_hfdred') == 0
				|| strcmp($groupCode[0],'pov_red') == 0
				|| strcmp($groupCode[0],'pa_hfdred') == 0
				|| strcmp($groupCode[0],'pa_red') == 0
				|| strcmp($groupCode[0],'pwv_hfdred') == 0
				|| strcmp($groupCode[0],'pwv_red') == 0)
				{
					$t_acl->set('access',3);
				} elseif(strcmp($groupCode[0],'crkc_regbeh') == 0
				|| strcmp($groupCode[0],'crkc_basreg') == 0
				|| strcmp($groupCode[0],'pov_regbeh') == 0
				|| strcmp($groupCode[0],'pov_basreg') == 0
				|| strcmp($groupCode[0],'pa_regbeh') == 0
				|| strcmp($groupCode[0],'pa_basreg') == 0
				|| strcmp($groupCode[0],'pwv_regbeh') == 0
				|| strcmp($groupCode[0],'pwv_basreg') == 0) {
					$t_acl->set('access',2);
				} elseif(strcmp($groupCode[0],'crkc_raad') == 0
					|| strcmp($groupCode[0],'crkc_ond') == 0
					|| strcmp($groupCode[0],'pov_raad') == 0
					|| strcmp($groupCode[0],'pov_ond') == 0
					|| strcmp($groupCode[0],'pa_raad') == 0
					|| strcmp($groupCode[0],'pa_ond') == 0
					|| strcmp($groupCode[0],'pwv_raad') == 0
					|| strcmp($groupCode[0],'pwv_ond') == 0) {
					$t_acl->set('access',1);
				} else {
					print "Ongekend recht: " + $groupCode[0]; 
				}
				$t_acl->insert();
			
				if ($t_acl->numErrors()) {
					print "\tERROR INSERT Group: " . $groupCode[0] . "/". $loan_id. ": ".join('; ', $t_acl->getErrors())."\n";
					continue;
				}
			}
		}
		//geheugen besparen
		$loan_ids = array();
	}
	else {
		print "Geen loans gelinkt";
	}
print "ENDED IMPORTING acl ALLES\n";
?>
