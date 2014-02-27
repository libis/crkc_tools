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
require_once(__CA_MODELS_DIR__.'/ca_collections.php');
require_once(__CA_MODELS_DIR__.'/ca_user_groups.php');
require_once(__CA_MODELS_DIR__.'/ca_objects.php');

require_once(__CA_LIB_DIR__.'/core/Parsers/DelimitedDataParser.php');

$_ = new Zend_Translate('gettext', __CA_APP_DIR__.'/locale/en_US/messages.mo', 'nl_NL');

$t_locale = new ca_locales();
$pn_locale_id = $t_locale->loadLocaleByCode('nl_NL');

//************************************
//****       ACL		 *****
//************************************

print "ADD ACL\n";
	// Eerst alle collecties linken
	$t_collection = new ca_collections();
	$collection_ids = array();
	
	// Dan alle objecten linken
	$t_objects = new ca_objects();
	$object_ids = array();
	
	// Group id moet opgezocht worden
	$t_group = new ca_user_groups();
	$group_id = array();
	
	$t_acl = new ca_acl();
	$t_acl->setMode(ACCESS_WRITE);
	
	// ALLES CRKC
	$crkc_collection_ids = $t_collection->getAllCollections('CRKC%');
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
	
	if(!empty($crkc_group_ids) && !empty($crkc_collection_ids))
	{
		foreach($crkc_collection_ids as $crkc_collection_id)
		{
			foreach($crkc_group_ids as $crkc_group_id)
			{
				$t_acl->set('group_id',$crkc_group_id);
				// ca_collections is 13
				$t_acl->set('table_num',13);
				$t_acl->set('row_id',$crkc_collection_id);
				// 3= edit + delete (hfdred, red)
				// 2 = edit (crkc_regbeh, basreg)
				// 1 = read (crkc_raad, crkc_ond)
				// 0 = none (via app.conf)
				$groupCode = $t_group->getGroupCodeById($crkc_group_id);
				if(strcmp($groupCode[0],'crkc_hfdred') == 0
				|| strcmp($groupCode[0],'crkc_red') == 0)
				{
					$t_acl->set('access',3);
				} elseif(strcmp($groupCode[0],'crkc_regbeh') == 0
				|| strcmp($groupCode[0],'crkc_basreg') == 0) {
					$t_acl->set('access',2);
				} elseif(strcmp($groupCode[0],'crkc_raad') == 0
					|| strcmp($groupCode[0],'crkc_ond') == 0) {
					$t_acl->set('access',1);
				} else {
					print "Ongekend recht: " + $groupCode[0]; 
				}
				$t_acl->insert();
			
				if ($t_acl->numErrors()) {
					print "\tERROR INSERT Group: " . $crkc_collection_id . "/". $crkc_collection_id. ": ".join('; ', $t_acl->getErrors())."\n";
					continue;
				}
			}
		}
		//geheugen besparen
		$crkc_collection_ids = array();
	}
	else {
		print "Geen Collecties gelinkt";
	}
	$crkc_object_ids = $t_objects->getAllObjects('CRKC%');
	if(!empty($crkc_group_ids) && !empty($crkc_object_ids))
	{
		foreach($crkc_object_ids as $crkc_object_id)
		{
			foreach($crkc_group_ids as $crkc_group_id)
			{
				$t_acl->set('group_id',$crkc_group_id);
				// ca_objects is 57
				$t_acl->set('table_num',57);
				$t_acl->set('row_id',$crkc_object_id);
				// 3= edit + delete (hfdred, red)
				// 2 = edit (crkc_regbeh, basreg)
				// 1 = read (crkc_raad, crkc_ond)
				// 0 = none (via app.conf)
				$groupCode = $t_group->getGroupCodeById($crkc_group_id);
				if(strcmp($groupCode[0],'crkc_hfdred') == 0
				|| strcmp($groupCode[0],'crkc_red') == 0)
				{
					$t_acl->set('access',3);
				} elseif(strcmp($groupCode[0],'crkc_regbeh') == 0
				|| strcmp($groupCode[0],'crkc_basreg') == 0) {
					$t_acl->set('access',2);
				} elseif(strcmp($groupCode[0],'crkc_raad') == 0
					|| strcmp($groupCode[0],'crkc_ond') == 0) {
					$t_acl->set('access',1);
				} else {
					print "Ongekend recht: " + $groupCode[0]; 
				}
				$t_acl->insert();
			
				if ($t_acl->numErrors()) {
					print "\tERROR INSERT Group: " . $crkc_object_id . "/". $crkc_object_id. ": ".join('; ', $t_acl->getErrors())."\n";
					continue;
				}
			}
		}
		//geheugen besparen
		$crkc_group_ids = array();
	}
	else {
		print "Geen Objects gelinkt";
	}
	// ALLES POV
	$pov_collection_ids = $t_collection->getAllCollections('POV%');
	
	$pov_group_ids = array();
	// Ophalen van de group_ids;
	$pov_hdf_red = "pov_hfdred";
	$group_id = $t_group->getGroupIdByCode($pov_hdf_red);
	array_push($pov_group_ids,$group_id[0]);
	
	$pov_red = "pov_red";
	$group_id = $t_group->getGroupIdByCode($pov_red);
	array_push($pov_group_ids,$group_id[0]);
	
	$pov_regbeh = "pov_regbeh";
	$group_id = $t_group->getGroupIdByCode($pov_regbeh);
	array_push($pov_group_ids,$group_id[0]);
	
	$pov_raad = "pov_raad";
	$group_id = $t_group->getGroupIdByCode($pov_raad);
	array_push($pov_group_ids,$group_id[0]);
	
	$pov_ond = "pov_ond";
	$group_id = $t_group->getGroupIdByCode($pov_ond);
	array_push($pov_group_ids,$group_id[0]);
	
	$pov_basreg = "pov_basreg";
	$group_id = $t_group->getGroupIdByCode($pov_basreg);
	array_push($pov_group_ids,$group_id[0]);
	
	if(!empty($pov_group_ids) && !empty($pov_collection_ids))
	{
		foreach($pov_collection_ids as $pov_collection_id)
		{
			foreach($pov_group_ids as $pov_group_id)
			{
				$t_acl->set('group_id',$pov_group_id);
				// ca_collections is 13
				$t_acl->set('table_num',13);
				$t_acl->set('row_id',$pov_collection_id);
				// 3= edit + delete (hfdred, red)
				// 2 = edit (pov_regbeh, basis_reg)
				// 1 = read (pov_raad, pov_ond)
				// 0 = none (via app.conf)
				$groupCode = $t_group->getGroupCodeById($pov_group_id);
				if(strcmp($groupCode[0],'pov_hfdred') == 0
				|| strcmp($groupCode[0],'pov_red') == 0)
				{
					$t_acl->set('access',3);
				} elseif(strcmp($groupCode[0],'pov_regbeh') == 0
				|| strcmp($groupCode[0],'pov_basreg') == 0){
					$t_acl->set('access',2);
				} elseif(strcmp($groupCode[0],'pov_raad') == 0
					|| strcmp($groupCode[0],'pov_ond') == 0) {
					$t_acl->set('access',1);
				} else {
					print "Ongekend recht: " + $groupCode[0]; 
				}
				$t_acl->insert();
			
				if ($t_acl->numErrors()) {
					print "\tERROR INSERT Group: " . $pov_collection_id . "/". $pov_collection_id. ": ".join('; ', $t_acl->getErrors())."\n";
					continue;
				}
			}
		}
		// Geheugen besparen
		$pov_collection_ids = array();
	}
	else {
		print "Geen Collecties gelinkt";
	}
	$pov_object_ids = $t_objects->getAllObjects('POV%');
	if(!empty($pov_group_ids) && !empty($pov_object_ids))
	{
		foreach($pov_object_ids as $pov_object_id)
		{
			foreach($pov_group_ids as $pov_group_id)
			{
				$t_acl->set('group_id',$pov_group_id);
				// ca_objects is 57
				$t_acl->set('table_num',57);
				$t_acl->set('row_id',$pov_object_id);
				// 3= edit + delete (hfdred, red)
				// 2 = edit (pov_regbeh, basis_reg)
				// 1 = read (pov_raad, pov_ond)
				// 0 = none (via app.conf)
				$groupCode = $t_group->getGroupCodeById($pov_group_id);
				if(strcmp($groupCode[0],'pov_hfdred') == 0
				|| strcmp($groupCode[0],'pov_red') == 0)
				{
					$t_acl->set('access',3);
				} elseif(strcmp($groupCode[0],'pov_regbeh') == 0
				|| strcmp($groupCode[0],'pov_basreg') == 0) {
					$t_acl->set('access',2);
				} elseif(strcmp($groupCode[0],'pov_raad') == 0
					|| strcmp($groupCode[0],'pov_ond') == 0) {
					$t_acl->set('access',1);
				} else {
					print "Ongekend recht: " + $groupCode[0]; 
				}
				$t_acl->insert();
			
				if ($t_acl->numErrors()) {
					print "\tERROR INSERT Group: " . $pov_object_id . "/". $pov_object_id. ": ".join('; ', $t_acl->getErrors())."\n";
					continue;
				}
			}
		}
		// geheugen besparen
		$pov_object_ids = array();
	}
	else {
		print "Geen Objects gelinkt";
	}
	// ALLES PA
	$pa_collection_ids = $t_collection->getAllCollections('PA%');
	$pa_group_ids = array();
	// Ophalen van de group_ids;
	$pa_hdf_red = "pa_hfdred";
	$group_id = $t_group->getGroupIdByCode($pa_hdf_red);
	array_push($pa_group_ids,$group_id[0]);
	
	$pa_red = "pa_red";
	$group_id = $t_group->getGroupIdByCode($pa_red);
	array_push($pa_group_ids,$group_id[0]);
	
	$pa_regbeh = "pa_regbeh";
	$group_id = $t_group->getGroupIdByCode($pa_regbeh);
	array_push($pa_group_ids,$group_id[0]);
	
	$pa_raad = "pa_raad";
	$group_id = $t_group->getGroupIdByCode($pa_raad);
	array_push($pa_group_ids,$group_id[0]);
	
	$pa_ond = "pa_ond";
	$group_id = $t_group->getGroupIdByCode($pa_ond);
	array_push($pa_group_ids,$group_id[0]);
	
	$pa_basreg = "pa_basreg";
	$group_id = $t_group->getGroupIdByCode($pa_basreg);
	array_push($pa_group_ids,$group_id[0]);
	
	if(!empty($pa_group_ids) && !empty($pa_collection_ids))
	{
		foreach($pa_collection_ids as $pa_collection_id)
		{
			foreach($pa_group_ids as $pa_group_id)
			{
				$t_acl->set('group_id',$pa_group_id);
				// ca_collections is 13
				$t_acl->set('table_num',13);
				$t_acl->set('row_id',$pa_collection_id);
				// 3= edit + delete (hfdred, red)
				// 2 = edit (pa_regbeh, basis_reg)
				// 1 = read (pa_raad, pa_ond)
				// 0 = none (via app.conf)
				$groupCode = $t_group->getGroupCodeById($pa_group_id);
				if(strcmp($groupCode[0],'pa_hfdred') == 0
				|| strcmp($groupCode[0],'pa_red') == 0)
				{
					$t_acl->set('access',3);
				} elseif(strcmp($groupCode[0],'pa_regbeh') == 0
				|| strcmp($groupCode[0],'pa_basreg') == 0) {
					$t_acl->set('access',2);
				} elseif(strcmp($groupCode[0],'pa_raad') == 0
					|| strcmp($groupCode[0],'pa_ond') == 0) {
					$t_acl->set('access',1);
				} else {
					print "Ongekend recht: " + $groupCode[0]; 
				}
				$t_acl->insert();
			
				if ($t_acl->numErrors()) {
					print "\tERROR INSERT Group: " . $pa_collection_id . "/". $pa_collection_id. ": ".join('; ', $t_acl->getErrors())."\n";
					continue;
				}
			}
		}
		// Geheugen besparen
		$pa_collection_ids = array();
	}
	else {
		print "Geen Collecties gelinkt";
	}
	$pa_object_ids = $t_objects->getAllObjects('PA%');
	if(!empty($pa_group_ids) && !empty($pa_object_ids))
	{
		foreach($pa_object_ids as $pa_object_id)
		{
			foreach($pa_group_ids as $pa_group_id)
			{
				$t_acl->set('group_id',$pa_group_id);
				// ca_objects is 57
				$t_acl->set('table_num',57);
				$t_acl->set('row_id',$pa_object_id);
				// 3= edit + delete (hfdred, red)
				// 2 = edit (pa_regbeh, basis_reg)
				// 1 = read (pa_raad, pa_ond)
				// 0 = none (via app.conf)
				$groupCode = $t_group->getGroupCodeById($pa_group_id);
				if(strcmp($groupCode[0],'pa_hfdred') == 0
				|| strcmp($groupCode[0],'pa_red') == 0)
				{
					$t_acl->set('access',3);
				} elseif(strcmp($groupCode[0],'pa_regbeh') == 0
				|| strcmp($groupCode[0],'pa_basreg') == 0) {
					$t_acl->set('access',2);
				} elseif(strcmp($groupCode[0],'pa_raad') == 0
					|| strcmp($groupCode[0],'pa_ond') == 0) {
					$t_acl->set('access',1);
				} else {
					print "Ongekend recht: " + $groupCode[0]; 
				}
				$t_acl->insert();
			
				if ($t_acl->numErrors()) {
					print "\tERROR INSERT Group: " . $pa_object_id . "/". $pa_object_id. ": ".join('; ', $t_acl->getErrors())."\n";
					continue;
				}
			}
		}
		// Geheugen besparen
		$pa_group_ids = array();
	}
	else {
		print "Geen Objects gelinkt";
	}
	// ALLES PWV
	$pwv_collection_ids = $t_collection->getAllCollections('PWV%');
	$pwv_object_ids = $t_objects->getAllObjects('PWV%');
	
	$pwv_group_ids = array();
	// Ophalen van de group_ids;
	$pwv_hdf_red = "pwv_hfdred";
	$group_id = $t_group->getGroupIdByCode($pwv_hdf_red);
	array_push($pwv_group_ids,$group_id[0]);
	
	$pwv_red = "pwv_red";
	$group_id = $t_group->getGroupIdByCode($pwv_red);
	array_push($pwv_group_ids,$group_id[0]);
	
	$pwv_regbeh = "pwv_regbeh";
	$group_id = $t_group->getGroupIdByCode($pwv_regbeh);
	array_push($pwv_group_ids,$group_id[0]);
	
	$pwv_raad = "pwv_raad";
	$group_id = $t_group->getGroupIdByCode($pwv_raad);
	array_push($pwv_group_ids,$group_id[0]);
	
	$pwv_ond = "pwv_ond";
	$group_id = $t_group->getGroupIdByCode($pwv_ond);
	array_push($pwv_group_ids,$group_id[0]);
	
	$pwv_basreg = "pwv_basreg";
	$group_id = $t_group->getGroupIdByCode($pwv_basreg);
	array_push($pwv_group_ids,$group_id[0]);
	
	if(!empty($pwv_group_ids) && !empty($pwv_collection_ids))
	{
		foreach($pwv_collection_ids as $pwv_collection_id)
		{
			foreach($pwv_group_ids as $pwv_group_id)
			{
				$t_acl->set('group_id',$pwv_group_id);
				// ca_collections is 13
				$t_acl->set('table_num',13);
				$t_acl->set('row_id',$pwv_collection_id);
				// 3= edit + delete (hfdred, red)
				// 2 = edit (pwv_regbeh, basis_reg)
				// 1 = read (pwv_raad, pwv_ond)
				// 0 = none (via app.conf)
				$groupCode = $t_group->getGroupCodeById($pwv_group_id);
				if(strcmp($groupCode[0],'pwv_hfdred') == 0
				|| strcmp($groupCode[0],'pwv_red') == 0)
				{
					$t_acl->set('access',3);
				} elseif(strcmp($groupCode[0],'pwv_regbeh') == 0
				|| strcmp($groupCode[0],'pwv_basreg') == 0) {
					$t_acl->set('access',2);
				} elseif(strcmp($groupCode[0],'pwv_raad') == 0
					|| strcmp($groupCode[0],'pwv_ond') == 0) {
					$t_acl->set('access',1);
				} else {
					print "Ongekend recht: " + $groupCode[0]; 
				}
				$t_acl->insert();
			
				if ($t_acl->numErrors()) {
					print "\tERROR INSERT Group: " . $pwv_collection_id . "/". $pwv_collection_id. ": ".join('; ', $t_acl->getErrors())."\n";
					continue;
				}
			}
		}
	}
	else {
		print "Geen Collecties gelinkt";
	}
	if(!empty($pwv_group_ids) && !empty($pwv_object_ids))
	{
		foreach($pwv_object_ids as $pwv_object_id)
		{
			foreach($pwv_group_ids as $pwv_group_id)
			{
				$t_acl->set('group_id',$pwv_group_id);
				// ca_objects is 57
				$t_acl->set('table_num',57);
				$t_acl->set('row_id',$pwv_object_id);
				// 3= edit + delete (hfdred, red)
				// 2 = edit (pwv_regbeh, basis_reg)
				// 1 = read (pwv_raad, pwv_ond) (venagr zie verder)
				// 0 = none (via app.conf)
				$groupCode = $t_group->getGroupCodeById($pwv_group_id);
				if(strcmp($groupCode[0],'pwv_hfdred') == 0
				|| strcmp($groupCode[0],'pwv_red') == 0)
				{
					$t_acl->set('access',3);
				} elseif(strcmp($groupCode[0],'pwv_regbeh') == 0
				|| strcmp($groupCode[0],'pwv_basreg') == 0) {
					$t_acl->set('access',2);
				} elseif(strcmp($groupCode[0],'pwv_raad') == 0
					|| strcmp($groupCode[0],'pwv_ond') == 0) {
					$t_acl->set('access',1);
				} else {
					print "Ongekend recht: " + $groupCode[0]; 
				}
				$t_acl->insert();
			
				if ($t_acl->numErrors()) {
					print "\tERROR INSERT Group: " . $pwv_object_id . "/". $pwv_object_id. ": ".join('; ', $t_acl->getErrors())."\n";
					continue;
				}
			}
		}
	}
	else {
		print "Geen Objects gelinkt";
	}
	
	print "IMPORTING kunstvoorwerpen \n";

//
// Step 2: Import
//
	
	// ----------------------------------------------------------------------
	// process main data (a tab delimited file)
	// ----------------------------------------------------------------------
	$o_tab_parser = new DelimitedDataParser("\t");
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
		$vn_VenAStatus			=	$o_tab_parser->getRowValue(2);
		$vs_Identificatie		=	$o_tab_parser->getRowValue(7);
		$vs_crkcObjectnr		=	$o_tab_parser->getRowValue(9);
		
		print "PROCESSING {$vn_Kunstvoorwerp_id}\n";
// Object linken aan Vraag en aanbod groep 
	if(strcmp($vn_VenAStatus, '1') == 0)
	{
		// Maken van kunstvoorwerpen zoals gedefinieerd in kunstvoorwerp script 			
		if(!$vs_Identificatie) {
			$vs_Identificatie = $vs_crkcObjectnr."??? TITLE UNKNOWN ???";		// if all else fails make it obvious
			print "WARNING: Identificatie/titel {$vs_crkcObjectnr} undefined \n";
		} else {
			$vs_Identificatie = $vs_crkcObjectnr." - (KV_".$vn_Kunstvoorwerp_id.")";
		}
		// Zoeken van de het object obv idno
		$t_object = new ca_objects();
		$object_ids = $t_object->getObjectIDsByidno($vs_Identificatie);
		// venagr is de group code van Vraag en aanbod
		$group_ids = $t_group->getGroupIdByCode('venagr');
		if(!empty($object_ids) && !empty($group_ids))
		{
			$t_acl->set('group_id', $group_ids[0]);
			// ca_object table_num is 57
			$t_acl->set('table_num', 57);
			$t_acl->set('row_id', $object_ids[0]);
			// access 1 is read toegang
			$t_acl->set('access', 1);
			
			$t_acl->insert();
				
			if ($t_acl->numErrors()) {
				print "\tERROR INSERT Group: " . $object_ids[0] . "/". $vs_crkcObjectnr. ": ".join('; ', $t_acl->getErrors())."\n";
				continue;
			} else {
				print "linken aan de vena groep gelukt: objectid: " . $object_ids[0] . " objectnummer: " .$vs_crkcObjectnr;
			}
		}
	}
		
$vn_c++;
		
print "=================Einde verwerking kunstwerk {$vs_CrkcObjectnr} ============================= \n \n";
}

$coll_groups = array();
// Zoek alle groepen van een bepaalde collectie
$coll_group_lists = $t_group->getGroupList();
foreach($coll_group_lists as $coll_group_list)
{
	$coll_group_id = $coll_group_list['group_id'];
	array_push($coll_groups,$coll_group_id);
}
$t_collection = new ca_collections();
foreach($coll_groups as $coll_group)
{
	$group_code = $t_group->getGroupCodeById($coll_group);
	if(strstr($group_code[0],"CRKC."))
	{
		$collectie = explode("_", $group_code[0],2);
		$collectieName = $collectie[0];
		$collectieRecht = $collectie[1];
		print "\ncollectieName: " .$collectieName;
		$crkc_collection_ids = $t_collection->getAllCollections($collectieName);
		$crkc_object_ids = $t_collection->getObjectsByCollectionIdno($collectieName);
		// zowel de collectie als de objecten mogen niet leeg zijn
		if(!empty($crkc_collection_ids) && !empty($crkc_object_ids))
		{
			// We beginnen met objecten
			foreach($crkc_object_ids as $crkc_object_id)
			{
				$t_acl->set('group_id',$coll_group);
				// ca_objects is 57
				$t_acl->set('table_num',57);
				$t_acl->set('row_id',$crkc_object_id);
				// 3= edit + delete (hfdred, red)
				// 2 = edit (pwv_regbeh, basis_reg)
				// 1 = read (pwv_raad, pwv_ond) (venagr zie verder)
				// 0 = none (via app.conf)
				$groupCode = $t_group->getGroupCodeById($coll_group);
				if(strcmp($collectieRecht,'hfdred') == 0
				|| strcmp($collectieRecht,'red') == 0)
				{
					$t_acl->set('access',3);
				} elseif(strcmp($collectieRecht,'regbeh') == 0
					|| strcmp($groupCode[0],'basreg') == 0) {
					$t_acl->set('access',2);
				} elseif(strcmp($collectieRecht,'raad') == 0
					|| strcmp($collectieRecht,'ond') == 0) {
					$t_acl->set('access',1);
				} else {
					print "Ongekend recht: " + $collectieRecht; 
				}
				$t_acl->insert();
			
				if ($t_acl->numErrors()) {
					print "\tERROR INSERT Group: " . $coll_group . "/". $crkc_object_id. ": ".join('; ', $t_acl->getErrors())."\n";
					continue;
				} else {
					print "\nINSERTING gelukt, Group: ". $coll_group . " object id " .  $crkc_object_id;
				}
			}
			// daarna de collecties
			foreach($crkc_collection_ids as $crkc_collection_id)
			{
				$t_acl->set('group_id',$coll_group);
				// ca_collections is 13
				$t_acl->set('table_num',13);
				$t_acl->set('row_id',$crkc_collection_id);
				// 3= edit + delete (hfdred, red)
				// 2 = edit (pwv_regbeh, basis_reg)
				// 1 = read (pwv_raad, pwv_ond) (venagr zie verder)
				$groupCode = $t_group->getGroupCodeById($coll_group);
				if(strcmp($collectieRecht,'hfdred') == 0
				|| strcmp($collectieRecht,'red') == 0)
				{
					$t_acl->set('access',3);
				} elseif(strcmp($collectieRecht,'regbeh') == 0
					|| strcmp($groupCode[0],'basreg') == 0) {
					$t_acl->set('access',2);
				} elseif(strcmp($collectieRecht,'raad') == 0
					|| strcmp($collectieRecht,'ond') == 0) {
					$t_acl->set('access',1);
				} else {
					print "Ongekend recht: " + $collectieRecht; 
				}
				$t_acl->insert();
			
				if ($t_acl->numErrors()) {
					print "\tERROR INSERT Group: " . $coll_group . "/". $crkc_collection_id. ": ".join('; ', $t_acl->getErrors())."\n";
					continue;
				} else {
					print "\nINSERTING gelukt, Group: ". $coll_group . " collection id " .  $crkc_collection_id;
				}
			}
		}
		else {
			print "Collectie en objecten waren leeg";
		}
	
	} elseif(strpos($group_code[0],"POV."))
	{
		$collectie = explode("_", $group_code[0],2);
		$collectieName = $collectie[0];
		$collectieRecht = $collectie[1];
		print "\ncollectieName: " .$collectieName;
		$pov_collection_ids = $t_collection->getAllCollections($collectieName);
		$pov_object_ids = $t_collection->getObjectsByCollectionIdno($collectieName);
		// zowel de collectie als de objecten mogen niet leeg zijn
		if(!empty($pov_collection_ids) && !empty($pov_object_ids))
		{
			// We beginnen met objecten
			foreach($pov_object_ids as $pov_object_id)
			{
				$t_acl->set('group_id',$coll_group);
				// ca_objects is 57
				$t_acl->set('table_num',57);
				$t_acl->set('row_id',$pov_object_id);
				// 3= edit + delete (hfdred, red)
				// 2 = edit (pwv_regbeh, basis_reg)
				// 1 = read (pwv_raad, pwv_ond) (venagr zie verder)
				// 0 = none (via app.conf)
				$groupCode = $t_group->getGroupCodeById($coll_group);
				if(strcmp($collectieRecht,'hfdred') == 0
				|| strcmp($collectieRecht,'red') == 0)
				{
					$t_acl->set('access',3);
				} elseif(strcmp($collectieRecht,'regbeh') == 0
					|| strcmp($groupCode[0],'basreg') == 0) {
					$t_acl->set('access',2);
				} elseif(strcmp($collectieRecht,'raad') == 0
					|| strcmp($collectieRecht,'ond') == 0) {
					$t_acl->set('access',1);
				} else {
					print "Ongekend recht: " + $collectieRecht; 
				}
				$t_acl->insert();
			
				if ($t_acl->numErrors()) {
					print "\tERROR INSERT Group: " . $coll_group . "/". $pov_object_id. ": ".join('; ', $t_acl->getErrors())."\n";
					continue;
				} else {
					print "\nINSERTING gelukt, Group: ". $coll_group . " object id " .  $pov_object_id;
				}
			}
			// daarna de collecties
			foreach($pov_collection_ids as $pov_collection_id)
			{
				$t_acl->set('group_id',$coll_group);
				// ca_collections is 13
				$t_acl->set('table_num',13);
				$t_acl->set('row_id',$pov_collection_id);
				// 3= edit + delete (hfdred, red)
				// 2 = edit (pwv_regbeh, basis_reg)
				// 1 = read (pwv_raad, pwv_ond) (venagr zie verder)
				$groupCode = $t_group->getGroupCodeById($coll_group);
				if(strcmp($collectieRecht,'hfdred') == 0
				|| strcmp($collectieRecht,'red') == 0)
				{
					$t_acl->set('access',3);
				} elseif(strcmp($collectieRecht,'regbeh') == 0
					|| strcmp($groupCode[0],'basreg') == 0) {
					$t_acl->set('access',2);
				} elseif(strcmp($collectieRecht,'raad') == 0
					|| strcmp($collectieRecht,'ond') == 0) {
					$t_acl->set('access',1);
				} else {
					print "Ongekend recht: " + $collectieRecht; 
				}
				$t_acl->insert();
			
				if ($t_acl->numErrors()) {
					print "\tERROR INSERT Group: " . $coll_group . "/". $pov_collection_id. ": ".join('; ', $t_acl->getErrors())."\n";
					continue;
				} else {
					print "\nINSERTING gelukt, Group: ". $coll_group . " collection id " .  $pov_collection_id;
				}
			}
		}
		else {
			print "Collectie en objecten waren leeg";
		}
		
	} elseif(strpos($group_code[0],"PA.")) 
	{
		$collectie = explode("_", $group_code[0],2);
		$collectieName = $collectie[0];
		$collectieRecht = $collectie[1];
		print "\ncollectieName: " .$collectieName;
		$pa_collection_ids = $t_collection->getAllCollections($collectieName);
		$pa_object_ids = $t_collection->getObjectsByCollectionIdno($collectieName);
		// zowel de collectie als de objecten mogen niet leeg zijn
		if(!empty($pa_collection_ids) && !empty($pa_object_ids))
		{
			// We beginnen met objecten
			foreach($pa_object_ids as $pa_object_id)
			{
				$t_acl->set('group_id',$coll_group);
				// ca_objects is 57
				$t_acl->set('table_num',57);
				$t_acl->set('row_id',$pa_object_id);
				// 3= edit + delete (hfdred, red)
				// 2 = edit (pwv_regbeh, basis_reg)
				// 1 = read (pwv_raad, pwv_ond) (venagr zie verder)
				// 0 = none (via app.conf)
				$groupCode = $t_group->getGroupCodeById($coll_group);
				if(strcmp($collectieRecht,'hfdred') == 0
				|| strcmp($collectieRecht,'red') == 0)
				{
					$t_acl->set('access',3);
				} elseif(strcmp($collectieRecht,'regbeh') == 0
				|| strcmp($groupCode[0],'basreg') == 0) {
					$t_acl->set('access',2);
				} elseif(strcmp($collectieRecht,'raad') == 0
					|| strcmp($collectieRecht,'ond') == 0) {
					$t_acl->set('access',1);
				} else {
					print "Ongekend recht: " + $collectieRecht; 
				}
				$t_acl->insert();
			
				if ($t_acl->numErrors()) {
					print "\tERROR INSERT Group: " . $coll_group . "/". $pa_object_id. ": ".join('; ', $t_acl->getErrors())."\n";
					continue;
				} else {
					print "\nINSERTING gelukt, Group: ". $coll_group . " object id " .  $pa_object_id;
				}
			}
			// daarna de collecties
			foreach($pa_collection_ids as $pa_collection_id)
			{
				$t_acl->set('group_id',$coll_group);
				// ca_collections is 13
				$t_acl->set('table_num',13);
				$t_acl->set('row_id',$pa_collection_id);
				// 3= edit + delete (hfdred, red)
				// 2 = edit (pwv_regbeh, basis_reg)
				// 1 = read (pwv_raad, pwv_ond) (venagr zie verder)
				$groupCode = $t_group->getGroupCodeById($coll_group);
				if(strcmp($collectieRecht,'hfdred') == 0
				|| strcmp($collectieRecht,'red') == 0)
				{
					$t_acl->set('access',3);
				} elseif(strcmp($collectieRecht,'regbeh') == 0
					|| strcmp($groupCode[0],'basreg') == 0) {
					$t_acl->set('access',2);
				} elseif(strcmp($collectieRecht,'raad') == 0
					|| strcmp($collectieRecht,'ond') == 0) {
					$t_acl->set('access',1);
				} else {
					print "Ongekend recht: " + $collectieRecht; 
				}
				$t_acl->insert();
			
				if ($t_acl->numErrors()) {
					print "\tERROR INSERT Group: " . $coll_group . "/". $pa_collection_id. ": ".join('; ', $t_acl->getErrors())."\n";
					continue;
				} else {
					print "\nINSERTING gelukt, Group: ". $coll_group . " collection id " .  $pa_collection_id;
				}
			}
		}
		else {
			print "Collectie en objecten waren leeg";
		}
		
	} elseif(strpos($group_code[0],"PWV."))
	{
		$collectie = explode("_", $group_code[0],2);
		$collectieName = $collectie[0];
		$collectieRecht = $collectie[1];
		print "\ncollectieName: " .$collectieName;
		$pwv_collection_ids = $t_collection->getAllCollections($collectieName);
		$pwv_object_ids = $t_collection->getObjectsByCollectionIdno($collectieName);
		// zowel de collectie als de objecten mogen niet leeg zijn
		if(!empty($pwv_collection_ids) && !empty($pwv_object_ids))
		{
			// We beginnen met objecten
			foreach($pwv_object_ids as $pwv_object_id)
			{
				$t_acl->set('group_id',$coll_group);
				// ca_objects is 57
				$t_acl->set('table_num',57);
				$t_acl->set('row_id',$pwv_object_id);
				// 3= edit + delete (hfdred, red)
				// 2 = edit (pwv_regbeh, basis_reg)
				// 1 = read (pwv_raad, pwv_ond) (venagr zie verder)
				// 0 = none (via app.conf)
				$groupCode = $t_group->getGroupCodeById($coll_group);
				if(strcmp($collectieRecht,'hfdred') == 0
				|| strcmp($collectieRecht,'red') == 0)
				{
					$t_acl->set('access',3);
				} elseif(strcmp($collectieRecht,'regbeh') == 0
					|| strcmp($groupCode[0],'basreg') == 0) {
					$t_acl->set('access',2);
				} elseif(strcmp($collectieRecht,'raad') == 0
					|| strcmp($collectieRecht,'ond') == 0) {
					$t_acl->set('access',1);
				} else {
					print "Ongekend recht: " + $collectieRecht; 
				}
				$t_acl->insert();
			
				if ($t_acl->numErrors()) {
					print "\tERROR INSERT Group: " . $coll_group . "/". $pwv_object_id. ": ".join('; ', $t_acl->getErrors())."\n";
					continue;
				} else {
					print "\nINSERTING gelukt, Group: ". $coll_group . " object id " .  $pwv_object_id;
				}
			}
			// daarna de collecties
			foreach($pwv_collection_ids as $pwv_collection_id)
			{
				$t_acl->set('group_id',$coll_group);
				// ca_collections is 13
				$t_acl->set('table_num',13);
				$t_acl->set('row_id',$pwv_collection_id);
				// 3= edit + delete (hfdred, red)
				// 2 = edit (pwv_regbeh, basis_reg)
				// 1 = read (pwv_raad, pwv_ond) (venagr zie verder)
				$groupCode = $t_group->getGroupCodeById($coll_group);
				if(strcmp($collectieRecht,'hfdred') == 0
				|| strcmp($collectieRecht,'red') == 0)
				{
					$t_acl->set('access',3);
				} elseif(strcmp($collectieRecht,'regbeh') == 0
				|| strcmp($groupCode[0],'basreg') == 0) {
					$t_acl->set('access',2);
				} elseif(strcmp($collectieRecht,'raad') == 0
					|| strcmp($collectieRecht,'ond') == 0) {
					$t_acl->set('access',1);
				} else {
					print "Ongekend recht: " + $collectieRecht; 
				}
				$t_acl->insert();
			
				if ($t_acl->numErrors()) {
					print "\tERROR INSERT Group: " . $coll_group . "/". $pwv_collection_id. ": ".join('; ', $t_acl->getErrors())."\n";
					continue;
				} else {
					print "\nINSERTING gelukt, Group: ". $coll_group . " collection id " .  $pwv_collection_id;
				}
			}
		}
		else {
			print "Collectie en objecten waren leeg";
		}
	}
}
	
print "ENDED IMPORTING acl ALLES\n";
?>
