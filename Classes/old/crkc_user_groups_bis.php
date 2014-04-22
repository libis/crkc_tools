<?php
/*	Dit script wordt gebruikt om groepen per collectie te maken en de gebruikers te linken aan deze groepen
/*
 * Step 1: Initialisation
 */
 // aangepaste versie: om fouten op te lossen
set_time_limit(36000);
require_once("/www/libis/html/ca_crkc/setup.php");

require_once(__CA_LIB_DIR__.'/core/Db.php');
require_once(__CA_MODELS_DIR__.'/ca_locales.php');
require_once(__CA_MODELS_DIR__.'/ca_user_groups.php');
require_once(__CA_MODELS_DIR__.'/ca_groups_x_roles.php');
require_once(__CA_MODELS_DIR__.'/ca_users_x_roles.php');
require_once(__CA_MODELS_DIR__.'/ca_users_x_groups.php');
require_once(__CA_MODELS_DIR__.'/ca_users.php');

require_once(__CA_LIB_DIR__.'/core/Parsers/DelimitedDataParser.php');


$_ = new Zend_Translate('gettext', __CA_APP_DIR__.'/locale/en_US/messages.mo', 'nl_NL');

$t_locale = new ca_locales();
$pn_locale_id = $t_locale->loadLocaleByCode('nl_NL');


//************************************
//****       USERS		 *****
//************************************

print "IMPORTING users\n";

/*
// * Step 2: Import
*/

 // want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t"); 

// Read csv; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/logins 29-05-2012_inladen_aanpassingen_liezelotte.csv')) {
	die("Couldn't parse logins 29-05-2012.csv data\n");	
}
	
print "READING logins 29-05-2012.csv...\n";

$vn_c = 1;
	
$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------
while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$login			=	$o_tab_parser->getRowValue(1);
	// CRKC van 15 - 20
	$collectie_crkc1	=	$o_tab_parser->getRowValue(15);
	$collectie_crkc2	=	$o_tab_parser->getRowValue(16);
	$collectie_crkc3	=	$o_tab_parser->getRowValue(17);
	
	$recht_crkc1		=	$o_tab_parser->getRowValue(18);
	$recht_crkc2		=	$o_tab_parser->getRowValue(19);
	$recht_crkc3		=	$o_tab_parser->getRowValue(20);
	
	// Vraag en aanbod 21
	$vena			=	$o_tab_parser->getRowValue(21);
	
	//POV van 22 -25
	$collectie_pov1		=	$o_tab_parser->getRowValue(22);
	$collectie_pov2		=	$o_tab_parser->getRowValue(23);
	
	$recht_pov1		=	$o_tab_parser->getRowValue(24);
	$recht_pov2		=	$o_tab_parser->getRowValue(25);

	//PA van 26 - 31
	$collectie_pa1		=	$o_tab_parser->getRowValue(26);
	$collectie_pa2		=	$o_tab_parser->getRowValue(27);
	$collectie_pa3		=	$o_tab_parser->getRowValue(28);
	
	$recht_pa1		=	$o_tab_parser->getRowValue(29);
	$recht_pa2		=	$o_tab_parser->getRowValue(30);
	$recht_pa3		=	$o_tab_parser->getRowValue(31);

	//PWV van 32 - 33
	$collectie_pwv1		=	$o_tab_parser->getRowValue(32);
	
	$recht_pwv1		=	$o_tab_parser->getRowValue(33);
	
	// Conversie van het recht naar de juiste rolnaam om later geen problemen
	// te hebben

	// CRKC1 - ER
	if(trim($recht_crkc1) == "ER")
	{
		$recht_crkc1 = "hfdred";
	}
	// VR
	if(trim($recht_crkc1) == "VR")
	{
		$recht_crkc1 = "red";
	}
	// O
	if(trim($recht_crkc1) == "O")
	{
		$recht_crkc1 = "ond";
	}
	// BR
	if(trim($recht_crkc1) == "BR")
	{
		$recht_crkc1 = "basreg";
	}
	// E
	if(trim($recht_crkc1) == "E")
	{
		$recht_crkc1 = "raad";
	}
	// CB
	if(trim($recht_crkc1) == "CB")
	{
		$recht_crkc1 = "regbeh";
	}
	// CRKC2 - ER
	if(trim($recht_crkc2) == "ER")
	{
		$recht_crkc2 = "hfdred";
	}
	// VR
	if(trim($recht_crkc2) == "VR")
	{
		$recht_crkc2 = "red";
	}
	// O
	if(trim($recht_crkc2) == "O")
	{
		$recht_crkc2 = "ond";
	}
	// BR
	if(trim($recht_crkc2) == "BR")
	{
		$recht_crkc2 = "basreg";
	}
	// E
	if(trim($recht_crkc2) == "E")
	{
		$recht_crkc2 = "raad";
	}
	// CB
	if(trim($recht_crkc2) == "CB")
	{
		$recht_crkc2 = "regbeh";
	}
	// CRKC3 - ER
	if(trim($recht_crkc3) == "ER")
	{
		$recht_crkc3 = "hfdred";
	}
	// VR
	if(trim($recht_crkc3) == "VR")
	{
		$recht_crkc3 = "red";
	}
	// O
	if(trim($recht_crkc3) == "O")
	{
		$recht_crkc3 = "ond";
	}
	// BR
	if(trim($recht_crkc3) == "BR")
	{
		$recht_crkc3 = "basreg";
	}
	// E
	if(trim($recht_crkc3) == "E")
	{
		$recht_crkc3 = "raad";
	}
	// CB
	if(trim($recht_crkc3) == "CB")
	{
		$recht_crkc3 = "regbeh";
	}
	// POV1 - ER
	if(trim($recht_pov1) == "ER")
	{
		$recht_pov1 = "hfdred";
	}
	// VR
	if(trim($recht_pov1) == "VR")
	{
		$recht_pov1 = "red";
	}
	// O
	if(trim($recht_pov1) == "O")
	{
		$recht_pov1 = "ond";
	}
	// BR
	if(trim($recht_pov1) == "BR")
	{
		$recht_pov1 = "basreg";
	}
	// E
	if(trim($recht_pov1) == "E")
	{
		$recht_pov1 = "raad";
	}
	// CB
	if(trim($recht_pov1) == "CB")
	{
		$recht_pov1 = "regbeh";
	}
	// POV2 - ER
	if(trim($recht_pov2) == "ER")
	{
		$recht_pov2 = "hfdred";
	}
	// VR
	if(trim($recht_pov2) == "VR")
	{
		$recht_pov2 = "red";
	}
	// O
	if(trim($recht_pov2) == "O")
	{
		$recht_pov2 = "ond";
	}
	// BR
	if(trim($recht_pov2) == "BR")
	{
		$recht_pov2 = "basreg";
	}
	// E
	if(trim($recht_pov2) == "E")
	{
		$recht_pov2 = "raad";
	}
	// CB
	if(trim($recht_pov2) == "CB")
	{
		$recht_pov2 = "regbeh";
	}
	// PA1 - ER
	if(trim($recht_pa1) == "ER")
	{
		$recht_pa1 = "hfdred";
	}
	// VR
	if(trim($recht_pa1) == "VR")
	{
		$recht_pa1 = "red";
	}
	// O
	if(trim($recht_pa1) == "O")
	{
		$recht_pa1 = "ond";
	}
	// BR
	if(trim($recht_pa1) == "BR")
	{
		$recht_pa1 = "basreg";
	}
	// E
	if(trim($recht_pa1) == "E")
	{
		$recht_pa1 = "raad";
	}
	// CB
	if(trim($recht_pa1) == "CB")
	{
		$recht_pa1 = "regbeh";
	}
	// PA2 - ER
	if(trim($recht_pa2) == "ER")
	{
		$recht_pa2 = "hfdred";
	}
	// VR
	if(trim($recht_pa2) == "VR")
	{
		$recht_pa2 = "red";
	}
	// O
	if(trim($recht_pa2) == "O")
	{
		$recht_pa2 = "ond";
	}
	// BR
	if(trim($recht_pa2) == "BR")
	{
		$recht_pa2 = "basreg";
	}
	// E
	if(trim($recht_pa2) == "E")
	{
		$recht_pa2 = "raad";
	}
	// CB
	if(trim($recht_pa2) == "CB")
	{
		$recht_pa2 = "regbeh";
	}
	// PA3 - ER
	if(trim($recht_pa3) == "ER")
	{
		$recht_pa3 = "hfdred";
	}
	// VR
	if(trim($recht_pa3) == "VR")
	{
		$recht_pa3 = "red";
	}
	// O
	if(trim($recht_pa3) == "O")
	{
		$recht_pa3 = "ond";
	}
	// BR
	if(trim($recht_pa3) == "BR")
	{
		$recht_pa3 = "basreg";
	}
	// E
	if(trim($recht_pa3) == "E")
	{
		$recht_pa3 = "raad";
	}
	// CB
	if(trim($recht_pa3) == "CB")
	{
		$recht_pa3 = "regbeh";
	}
	// PWV1 - ER
	if(trim($recht_pwv1) == "ER")
	{
		$recht_pwv1 = "hfdred";
	}
	// VR
	if(trim($recht_pwv1) == "VR")
	{
		$recht_pwv1 = "red";
	}
	// O
	if(trim($recht_pwv1) == "O")
	{
		$recht_pwv1 = "ond";
	}
	// BR
	if(trim($recht_pwv1) == "BR")
	{
		$recht_pwv1 = "basreg";
	}
	// E
	if(trim($recht_pwv1) == "E")
	{
		$recht_pwv1 = "raad";
	}
	// CB
	if(trim($recht_pwv1) == "CB")
	{
		$recht_pwv1 = "regbeh";
	}
print "\n Creating  ".$login." and groups for user. \n"; 
 
	
//----------------		
// Create object
//----------------

	// gebruik ca_users om de user_id op te zoeken
	$t_user = new ca_users();
	$t_user->setMode(ACCESS_WRITE);
	$t_user->load($login);
	$user_id = $t_user->getPrimaryKey();
	
	$groups =  array();
	$group = "";
	// als een collectie komma's bevat wordt het hier ingestoken
	$groups_crkc = array();
	$groups_pov = array();
	$groups_pa = array();
	$groups_pwv = array();
	
	$t_group_role = new ca_groups_x_roles();
	$t_group_role->setMode(ACCESS_WRITE);
	// haal alle individuele collecties op (negeren van ALLES). 
	// Ik gebruik geen contains, omdat de ALLES velden opgesplitst zijn en geen komma's meer bevatten
	// De velden die een komma hebben moeten eerst opgeslitst worden
	if(trim($collectie_crkc1) != "ALLES" && trim($collectie_crkc1) !== "")
	{
		
		if(strstr($collectie_crkc1, ","))
		{
			foreach(explode(",",$collectie_crkc1) as $temp)
			{
				$groups_crkc = array("CRKC.". trim($temp) => $recht_crkc1);
			}
		// Moet nog gesplitst worden, omdat er maar één collectie in een veld zit
		} else {
			$groups_crkc["CRKC." .trim($collectie_crkc1)] = $recht_crkc1;
		}
	}
	if(trim($collectie_crkc2) != "ALLES" && trim($collectie_crkc2) !== "")
	{
		if(strstr($collectie_crkc2, ","))
		{
			foreach(explode(",",$collectie_crkc2) as $temp)
			{
				$groups_crkc = array("CRKC.". trim($temp) => $recht_crkc2);
			}
		} else {
			$groups_crkc["CRKC." .trim($collectie_crkc2)] = $recht_crkc2;
		}
	}
	if(trim($collectie_crkc3) != "ALLES" && trim($collectie_crkc3) !== "")
	{
		if(strstr($collectie_crkc3, ","))
		{
			foreach(explode(",",$collectie_crkc3) as $temp)
			{
				$groups_crkc["CRKC.". trim($temp)] = $recht_crkc3;
			}
		} else {
			$groups_crkc["CRKC." .trim($collectie_crkc3)] = $recht_crkc3;
		}
	}
	
	if(trim($collectie_pov1) != "ALLES" && trim($collectie_pov1) !== "")
	{
		if(strstr($collectie_pov1, ","))
		{
			foreach(explode(",",$collectie_pov1) as $temp)
			{
				$groups_pov["POV.". trim($temp)] = $recht_pov1;
			}
		} else {
			$groups_pov["POV." .trim($collectie_pov1)] = $recht_pov1;
		}
	}
	if(trim($collectie_pov2) != "ALLES" && trim($collectie_pov2) !== "")
	{
		if(strstr($collectie_pov2, ","))
		{
			foreach(explode(",",$collectie_pov2) as $temp)
			{
				$groups_pov["POV.". trim($temp)] = $recht_pov2;
			}
		} else {
			$groups_pov["POV." .trim($collectie_pov2)] = $recht_pov2;
		}
	}
	if(trim($collectie_pa1) != "ALLES" && trim($collectie_pa1) !== "")
	{
		if(strstr($collectie_pa1, ","))
		{
			foreach(explode(",",$collectie_pa1) as $temp)
			{
				$groups_pa["PA.". trim($temp)] = $recht_pa1;
			}
		} else {
			$groups_pa["PA." .trim($collectie_pa1)] = $recht_pa1;
		}
	}
	if(trim($collectie_pa2) != "ALLES" && trim($collectie_pa2) !== "")
	{
		if(strstr($collectie_pa2, ","))
		{
			foreach(explode(",",$collectie_pa2) as $temp)
			{
				$groups_pa["PA.". trim($temp)] = $recht_pa2;
			}
		} else {
			$groups_pa["PA." .trim($collectie_pa2)] = $recht_pa2;
		}
	}
	if(trim($collectie_pa3) != "ALLES" && trim($collectie_pa3) !== "")
	{
		if(strstr($collectie_pa3, ","))
		{
			foreach(explode(",",$collectie_pa3) as $temp)
			{
				$groups_pa["PA.". trim($temp)] = $recht_pa3;
			}
		} else {
			$groups_pa["PA." .trim($collectie_pa3)] = $recht_pa3;
		}
	}
	if(trim($collectie_pwv1) != "ALLES" && trim($collectie_pwv1) !== "")
	{
		//print "Collectie" .$collectie_pwv1;
		if(strstr($collectie_pwv1, ","))
		{
			foreach(explode(",", $collectie_pwv1) as $temp)
			{
				$groups_pwv["PWV.". trim($temp)] = $recht_pwv1;
			}
		}
		// Moet nog gesplitst worden, omdat er maar één collectie in een veld zit
		else {
			$groups_pwv["PWV." .trim($collectie_pwv1)] = $recht_pwv1;
		}
	}
	
	$t_user_roles = new ca_user_roles();
	$t_user_roles->setMode(ACCESS_WRITE);
	
	// de overblijvende rollen toevoegen
	if(!empty($groups_crkc))
	{
		foreach($groups_crkc as $key_crkc => $value_crkc)
		{
			$t_group = new ca_user_groups();
			$availableGroups = $t_group->getGroupList();
			$t_group->setMode(ACCESS_WRITE);
			$bestaat = false;
			foreach($availableGroups as $temp_group)
			{
				if(strcmp($temp_group['code'],$key_crkc . "_" .$value_crkc) == 0)
				{
					$bestaat = true;
					print "bestaat is true: " . $key_crkc . " _ " .$value_crkc ."\n" ;
				}
			}
			if($bestaat == true)
			{
				$roles = array();
				// ALLES - ER
				if(trim($value_crkc) == "hfdred")
				{
					array_push($roles,"hfdred");
					print "user: " .$login . "rol: " .$value_crkc ."\n" ;
				}
				// ALLES - VR
				if(trim($value_crkc) == "red")
				{
					array_push($roles,"red");
					print "user: " .$login . "rol: "  .$value_crkc ."\n" ;
				}
				// ALLES - O
				if(trim($value_crkc) == "ond")
				{
					array_push($roles,"ond");
					print "user: " .$login . "role: "  .$value_crkc ."\n" ;
				}
				// BR
				if(trim($value_crkc) == "basreg")
				{
					array_push($roles,"basreg");
					print "user: " .$login . "role: "  .$value_crkc ."\n" ;
				}
				// E
				if(trim($value_crkc) == "raad")
				{
					array_push($roles,"raad");
					print "user: " .$login . "role: "  .$value_crkc ."\n" ;
				}
				// CB
				if(trim($value_crkc) == "regbeh")
				{
					array_push($roles,"regbeh");
					print "user: " .$login . "role:  "  .$value_crkc;
				}
				// Als een rol empty is, dan betekent dat NIETS of leeg of een fout en dan moet er geen groep gebeuren
				
				if(!empty($roles))
				{
					foreach($roles as $role)
					{
					/*	// groep aanmaken
						$t_group->set('name',$key_crkc. " " . $role);
						$t_group->set('code',$key_crkc. "_" . $role);
						$t_group->set('parent_id', 1);
						$t_group-> insert();
						if ($t_group->numErrors()) {
							print "\tERROR (01) Inserting Group: " . $key_crkc . "user" . $login. ": ".join('; ', $t_group->getErrors())."\n";
							continue;
						} else { */
							// rol toevoegen
							$group_id = $t_group->getGroupIdByCode($key_crkc . "_" .$value_crkc);
							$t_group_role->set('group_id',$group_id[0]);
							// Opgelet getRoleIdByCode is toegevoegd door Sam
							$rol_id = $t_user_roles->getRoleIdByCode($role);
							$t_group_role->set('role_id',$rol_id[0]);
							$t_group_role-> insert();
							if ($t_group_role->numErrors()) {
								print "\tERROR (02) Inserting linking groep id: " . $t_group->getPrimaryKey() . "role: " . $role. ": ".join('; ', $t_group_role->getErrors())."\n";
								continue;
							}
					/*	} */
					}
				} else {
					print "ERROR: rol is leeg of niks en groep: ". $key_crkc . " en user: " . $login . "werd niet aangemaakt";
				}
				// rollen terugleeg maken
				$roles = array();
			}
			// De groep bestaat en de user kan dus gelinkt worden
/*			$group_id = $t_group->getGroupIdByCode($key_crkc . "_" .$value_crkc);
			if(!empty($group_id) && !empty($user_id))
			{
				$t_users_x_groups = new ca_users_x_groups();
				$t_users_x_groups->setMode(ACCESS_WRITE);
				$t_users_x_groups->set('group_id', $group_id[0]);
				$t_users_x_groups->set('user_id', $user_id);
				$t_users_x_groups->insert();
				
				
				if ($t_users_x_groups->numErrors()) {
					print "\tERROR (03) INSERT Group: " . $key_crkc . "/". $value_crkc . "user" . $login. ": ".join('; ', $t_users_x_groups->getErrors())."\n";
					continue;
				}
			} else {
				print "\nGroep id: " . $group_id . " of user_id " . $user_id . " was leeg\n";	
			}
*/			
			
			$bestaat = false;
		}
	}
	if(!empty($groups_pov))
	{
		foreach($groups_pov as $key_pov => $value_pov)
		{
			$t_group = new ca_user_groups();
			$availableGroups = $t_group->getGroupList();
			$t_group->setMode(ACCESS_WRITE);
			$bestaat = false;
			foreach($availableGroups as $temp_group)
			{
				if(strcmp($temp_group['code'],$key_pov . "_" .$value_pov) == 0)
				{
					$bestaat = true;
					print "bestaat is true: " . $key_pov . "_" .$value_pov;
				}
			}
			if($bestaat == true)
			{
				
				$roles = array();
				// ALLES - ER
				if(trim($value_pov) == "hfdred")
				{
					array_push($roles,"hfdred");
					print "user: " .$login . "rol: hoofd_redac";
				}
				// ALLES - VR
				if(trim($value_pov) == "red")
				{
					array_push($roles,"red");
					print "user: " .$login . "rol: redacteur";
				}
				// ALLES - O
				if(trim($value_pov) == "ond")
				{
					array_push($roles,"ond");
					print "user: " .$login . "role: onderzoeker";
				}
				// BR
				if(trim($value_pov) == "basreg")
				{
					array_push($roles,"basreg");
					print "user: " .$login . "role: basisregistrator";
				}
				// E
				if(trim($value_pov) == "raad")
				{
					array_push($roles,"raad");
					print "user: " .$login . "role: raadpleger";
				}
				// CB
				if(trim($value_pov) == "regbeh")
				{
					array_push($roles,"regbeh");
					print "user: " .$login . "role: registr_beheer";
				}
				// Als een rol empty is, dan betekent dat NIETS of leeg of een fout en dan moet er geen groep gebeuren
				if(!empty($roles))
				{
					foreach($roles as $role)
					{
					/*	// groep aanmaken
						$t_group->set('name',$key_pov. " " . $role);
						$t_group->set('code',$key_pov. "_" . $role);
						$t_group->set('parent_id', 1);
						$t_group-> insert();
						if ($t_group->numErrors()) {
							print "\tERROR (04) Inserting Group: " . $key_pov . "user" . $login. ": ".join('; ', $t_group->getErrors())."\n";
							continue;
						} else { */
							// rol toevoegen
							$group_id = $t_group->getGroupIdByCode($key_pov . "_" .$value_pov);
							$t_group_role->set('group_id',$group_id[0]);
							$rol_id = $t_user_roles->getRoleIdByCode($role);
							$t_group_role->set('role_id',$rol_id[0]);
							$t_group_role-> insert();
							if ($t_group_role->numErrors()) {
								print "\tERROR (05) Inserting linking groep id: " . $t_group->getPrimaryKey() . "role: " . $role. ": ".join('; ', $t_group_role->getErrors())."\n";
								continue;
							}
					/*	} */
					}
				} else {
					print "ERROR: rol is leeg of niks en groep: ". $key_pov . " en user: " . $login . "werd niet aangemaakt";
				}
				// rollen terugleeg maken
				$roles = array();
			}
			// De groep bestaat en de user kan dus gelinkt worden
/*			$group_id = $t_group->getGroupIdByCode($key_pov . "_" .$value_pov);
			$t_users_x_groups = new ca_users_x_groups();
			$t_users_x_groups->setMode(ACCESS_WRITE);
			$t_users_x_groups->set('group_id', $group_id[0]);
			$t_users_x_groups->set('user_id', $user_id);
			$t_users_x_groups->insert();
			
			
			if ($t_users_x_groups->numErrors()) {
				print "\tERROR (06) INSERT Group: " . $key_pov . "/". $value_pov . "user" . $login. ": ".join('; ', $t_users_x_groups->getErrors())."\n";
				continue;
			}
*/
			$bestaat = false;
		}
	}
	if(!empty($groups_pa))
	{
		foreach($groups_pa as $key_pa => $value_pa)
		{
			$t_group = new ca_user_groups();
			$availableGroups = $t_group->getGroupList();
			$t_group->setMode(ACCESS_WRITE);
			$bestaat = false;
			foreach($availableGroups as $temp_group)
			{
				if(strcmp($temp_group['code'],$key_pa . "_" .$value_pa) == 0)
				{
					$bestaat = true;
					print "bestaat is true: " . $key_pa . "_" .$value_pa;
				}
			}
			if($bestaat == true)
			{
				
				$roles = array();
				// ALLES - ER
				if(trim($value_pa) == "hfdred")
				{
					array_push($roles,"hfdred");
					print "user: " .$login . "rol: hoofd_redac";
				}
				// ALLES - VR
				if(trim($value_pa) == "red")
				{
					array_push($roles,"red");
					print "user: " .$login . "rol: redacteur";
				}
				// ALLES - O
				if(trim($value_pa) == "ond")
				{
					array_push($roles,"ond");
					print "user: " .$login . "role: onderzoeker";
				}
				// BR
				if(trim($value_pa) == "basreg")
				{
					array_push($roles,"basreg");
					print "user: " .$login . "role: basisregistrator";
				}
				// E
				if(trim($value_pa) == "raad")
				{
					array_push($roles,"raad");
					print "user: " .$login . "role: raadpleger";
				}
				// CB
				if(trim($value_pa) == "regbeh")
				{
					array_push($roles,"regbeh");
					print "user: " .$login . "role: registr_beheer";
				}
				// Als een rol empty is, dan betekent dat NIETS of leeg of een fout en dan moet er geen groep gebeuren
				if(!empty($roles))
				{
					foreach($roles as $role)
					{
					/*	// groep aanmaken
						$t_group->set('name',$key_pa. " " . $role);
						$t_group->set('code',$key_pa. "_" . $role);
						$t_group->set('parent_id', 1);
						$t_group-> insert();
						if ($t_group->numErrors()) {
							print "\tERROR (07) Inserting Group: " . $key_pa . "user" . $login. ": ".join('; ', $t_group->getErrors())."\n";
							continue;
						} else { */
							// rol toevoegen
							$group_id = $t_group->getGroupIdByCode($key_pa . "_" .$value_pa);
							$t_group_role->set('group_id',$group_id[0]);
							$rol_id = $t_user_roles->getRoleIdByCode($role);
							$t_group_role->set('role_id',$rol_id[0]);
							$t_group_role-> insert();
							if ($t_group_role->numErrors()) {
								print "\tERROR (08) Inserting linking groep id: " . $t_group->getPrimaryKey() . "role: " . $role. ": ".join('; ', $t_group_role->getErrors())."\n";
								continue;
							}
					/*	} */
					}
				} else {
					print "ERROR: rol is leeg of niks en groep: ". $key_pa . " en user: " . $login . "werd niet aangemaakt";
				}
				// rollen terugleeg maken
				$roles = array();
			}
			// De groep bestaat en de user kan dus gelinkt worden
/*			$group_id = $t_group->getGroupIdByCode($key_pa . "_" .$value_pa);
			$t_users_x_groups = new ca_users_x_groups();
			$t_users_x_groups->setMode(ACCESS_WRITE);
			$t_users_x_groups->set('group_id', $group_id[0]);
			$t_users_x_groups->set('user_id', $user_id);
			$t_users_x_groups->insert();
			
			
			if ($t_users_x_groups->numErrors()) {
				print "\tERROR (09) INSERT Group: " . $key_pa . "/". $value_pa . "user" . $login. ": ".join('; ', $t_users_x_groups->getErrors())."\n";
				continue;
			}
*/
			$bestaat = false;
		}
	}
	if(!empty($groups_pwv))
	{
		foreach($groups_pwv as $key_pwv => $value_pwv)
		{
			$t_group = new ca_user_groups();
			$availableGroups = $t_group->getGroupList();
			$t_group->setMode(ACCESS_WRITE);
			$bestaat = false;
			foreach($availableGroups as $temp_group)
			{
				if(strcmp($temp_group['code'],$key_pwv . "_" .$value_pwv) == 0)
				{
					$bestaat = true;
					print "bestaat is true: " . $key_pwv . "_" .$value_pwv;
				}
			}
			if($bestaat == true)
			{
				
				$roles = array();
				// ALLES - ER
				if(trim($value_pwv) == "hfdred")
				{
					array_push($roles,"hfdred");
					print "user: " .$login . "rol: hoofd_redac";
				}
				// ALLES - VR
				if(trim($value_pwv) == "red")
				{
					array_push($roles,"red");
					print "user: " .$login . "rol: redacteur";
				}
				// ALLES - O
				if(trim($value_pwv) == "ond")
				{
					array_push($roles,"ond");
					print "user: " .$login . "role: onderzoeker";
				}
				// BR
				if(trim($value_pwv) == "basreg")
				{
					array_push($roles,"basreg");
					print "user: " .$login . "role: basisregistrator";
				}
				// E
				if(trim($value_pwv) == "raad")
				{
					array_push($roles,"raad");
					print "user: " .$login . "role: raadpleger";
				}
				// CB
				if(trim($value_pwv) == "regbeh")
				{
					array_push($roles,"regbeh");
					print "user: " .$login . "role: registr_beheer";
				}
				// Als een rol empty is, dan betekent dat NIETS of leeg of een fout en dan moet er geen groep gebeuren
				if(!empty($roles))
				{
					foreach($roles as $role)
					{
						// groep aanmaken
					/*	$t_group->set('name',$key_pwv. " " . $role);
						$t_group->set('code',$key_pwv. "_" . $role);
						$t_group->set('parent_id', 1);
						$t_group-> insert();
						if ($t_group->numErrors()) {
							print "\tERROR (10) Inserting Group: " . $key_pwv . "user" . $login. ": ".join('; ', $t_group->getErrors())."\n";
							continue;
						} else { */
							// rol toevoegen
							$group_id = $t_group->getGroupIdByCode($key_pwv . "_" .$value_pwv);
							$t_group_role->set('group_id',$group_id[0]);
							$rol_id = $t_user_roles->getRoleIdByCode($role);
							$t_group_role->set('role_id',$rol_id[0]);
							$t_group_role-> insert();
							if ($t_group_role->numErrors()) {
								print "\tERROR (11) Inserting linking groep id: " . $t_group->getPrimaryKey() . "role: " . $role. ": ".join('; ', $t_group_role->getErrors())."\n";
								continue;
							}
					/*	} */
					}
				} else {
					print "ERROR: rol is leeg of niks en groep: ". $key_pwv . " en user: " . $login . "werd niet aangemaakt";
				}
				// rollen terugleeg maken
				$roles = array();
			}
			// De groep bestaat en de user kan dus gelinkt worden
/*			$group_id = $t_group->getGroupIdByCode($key_pwv . "_" .$value_pwv);
			$t_users_x_groups = new ca_users_x_groups();
			$t_users_x_groups->setMode(ACCESS_WRITE);
			$t_users_x_groups->set('group_id', $group_id[0]);
			$t_users_x_groups->set('user_id', $user_id);
			$t_users_x_groups->insert();
			
			
			if ($t_users_x_groups->numErrors()) {
				print "\tERROR (12) INSERT Group: " . $key_pwv . "/". $value_pwv . "user" . $login. ": ".join('; ', $t_users_x_groups->getErrors())."\n";
				continue;
			}
*/
			$bestaat = false;
		}
	}
	
	$vn_c++;
}

print "ENDED IMPORTING new_bewaarplaats.csv\n";

?>
