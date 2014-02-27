<?php
/* Dit script wordt enkel gebruikt om users te maken */
/* TODO: de gisHelper.php moet aangepast worden om de juiste selectie lijsten te voorzien bij user profile
 * TODO: aanpassen van user_pref_def.conf
 * Step 1: Initialisation
 */
set_time_limit(36000);
require_once("/www/libis/html/ca_crkc/setup.php");

require_once(__CA_LIB_DIR__.'/core/Db.php');
require_once(__CA_MODELS_DIR__.'/ca_locales.php');
require_once(__CA_MODELS_DIR__.'/ca_users.php');
require_once(__CA_MODELS_DIR__.'/ca_user_groups.php');
require_once(__CA_MODELS_DIR__.'/ca_user_roles.php');
require_once(__CA_MODELS_DIR__.'/ca_groups_x_roles.php');

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
	$paswoord		=	$o_tab_parser->getRowValue(2);
	$voornaam		=	$o_tab_parser->getRowvalue(3);
	$achternaam		=	$o_tab_parser->getRowvalue(4);
	$email			=	$o_tab_parser->getRowvalue(5);
	$instelling		=	$o_tab_parser->getRowvalue(6);
	$adres			=	$o_tab_parser->getRowvalue(7);
	$postcode		=	$o_tab_parser->getRowvalue(8);
	$deelGemeente		=	$o_tab_parser->getRowvalue(9);
	$gemeente		=	$o_tab_parser->getRowvalue(10);
	$provincie		=	$o_tab_parser->getRowvalue(11);
	$bisdom			= 	$o_tab_parser->getRowvalue(12);
	$land			= 	$o_tab_parser->getRowvalue(13);
	$telefoon		= 	$o_tab_parser->getRowvalue(14);
	
	
print "\n Creating  ".$login." and adding labels for login. \n"; 
 
	
//----------------		
// Create object
//----------------

	$t_user = new ca_users();
	$t_user->setMode(ACCESS_WRITE);
	$t_user->set('user_name',$login);
	$t_user->set('fname', $voornaam);
	$t_user->set('lname', $achternaam);
	$t_user->set('active', 1);
	$t_user->set('email', $email);
	$t_user->set('password', $paswoord);
	$t_user->setPreference('user_profile_organization', $instelling);
	$t_user->setPreference('user_profile_address1', $adres);
	$t_user->setPreference('user_profile_postalcode', $postcode);
	$t_user->setPreference('user_profile_deelgemeente', $deelGemeente);
	$t_user->setPreference('user_profile_city', $gemeente);
	$t_user->setPreference('user_profile_state', $provincie);
	$t_user->setPreference('user_profile_bisdom', $bisdom);
	$t_user->setPreference('user_profile_country', $land);
	$t_user->setPreference('user_profile_phone', $telefoon);
	
	// insert the object
	$t_user->insert();

	if ($t_user->numErrors()) {
		print "\tERROR INSERTING {$voornaam}/{$achternaam}: ".join('; ', $t_user->getErrors())."\n";
		continue;
	} else {
		print "toevoegen user {$voornaam}/{$achternaam} gelukt \n";
	}

	// -------------------------------------------
	// Maak link naar entities
	//--------------------------------------------
	
/*	$user_id = $t_user->getPrimaryKey();*/
	// waarschijnlijk is het voldoende om een user aan een groep te hangen en niet aan een rol (groep bevat rol)
	// $groups mag de code zijn
/*	  VR = vaste redacteur
	  ER = eindredacteur
	  O = onderzoeker
	  CB = collectiebeheerder
	  BR = basisregistrator
	  VRO = vaste redacteur ontsluiting (geef raadpleger rechten)
	  E = eigenaar
	  NIETS of ALLES negeren
*/
	
	$vn_c++;
}

print "IMPORT Hoofdgroepen \n";

$t_role = new ca_user_roles();
$t_role->setMode(ACCESS_WRITE);

// de rollen
$roles = array();

$roles['anoniem'] = "Anonieme gebruiker";
$roles['basreg'] = "Basisregistrator";
$roles['hfdred'] = "Hoofdredacteur";
$roles['libis'] = "LIBIS";
$roles['ond'] = "Onderzoeker";
$roles['raad'] = "Raadpleger";
$roles['red'] = "Redacteur";
$roles['regbeh'] = "Registrator beheersgegevens";
$roles['vena'] = "Vraag en aanbod gebruiker";

foreach($roles as $role_code => $role_name)
{
	$t_role->set('name',$role_name);
	$t_role->set('code',$role_code);
	$t_role->insert();

	if ($t_role->numErrors()) {
		print "\tERROR INSERTING {$role_code}: ".join('; ', $t_role->getErrors())."\n";
		continue;
	} else {
		print "toevoegen role gelukt: " . $role_code . "\n"; 
	}
}

$t_group = new ca_user_groups();
$t_group->setMode(ACCESS_WRITE);

// de groepen
$groups = array();
$groups['anongr'] = "anonieme groep";
$groups['lbisgrp'] = "LIBIS groep";
$groups['venagr'] = "Vraag en aanbod groep";
$groups['crkc_basreg'] = "CRKC basisregistator";
$groups['crkc_hfdred'] = "CRKC hoofdredacteur";
$groups['crkc_ond'] = "CRKC onderzoeker";
$groups['crkc_raad'] = "CRKC raadpleger";
$groups['crkc_red'] = "CRKC redacteur";
$groups['crkc_regbeh'] = "CRKC registrator beheergegevens";
$groups['pov_basreg'] = "POV basisregistator";
$groups['pov_hfdred'] = "POV hoofdredacteur";
$groups['pov_ond'] = "POV onderzoeker";
$groups['pov_raad'] = "POV raadpleger";
$groups['pov_red'] = "POV redacteur";
$groups['pov_regbeh'] = "POV registrator beheergegevens";
$groups['pa_basreg'] = "PA basisregistrator";
$groups['pa_hfdred'] = "PA hoofdredacteur";
$groups['pa_ond'] = "PA onderzoeker";
$groups['pa_raad'] = "PA raadpleger";
$groups['pa_red'] = "PA redacteur";
$groups['pa_regbeh'] = "PA registrator beheergegevens";
$groups['pwv_basreg'] = "PWV basisregistrator";
$groups['pwv_hfdred'] = "PWV hoofdredacteur";
$groups['pwv_ond'] = "PWV onderzoeker";
$groups['pwv_raad'] = "PWV raadpleger";
$groups['pwv_red'] = "PWV redacteur";
$groups['pwv_regbeh'] = "PWV registrator beheergegevens";

foreach($groups as $group_code => $group_name)
{
	$t_group->set('name',$group_name);
	$t_group->set('code',$group_code);
	
	// groep saven
	$t_group->insert();

	if ($t_group->numErrors()) {
		print "\tERROR INSERTING {$group_code}: ".join('; ', $t_group->getErrors())."\n";
		continue;
	} else {
		print "toevoegen group gelukt: " . $group_code  ."\n" ; 
	}
}

// gezien de objecten $t_group en $t_role al eerder bestaan,
// vraag ik mij af of dit wel nodig is??
//$t_group = new ca_user_groups();
//$t_group->setMode(ACCESS_WRITE);

$group_role = new ca_groups_x_roles();
$group_role->setMode(ACCESS_WRITE);

//$t_role = new ca_user_roles();

foreach($groups as $group_code => $group_name)
{
	$groupids = $t_group->getGroupIdByCode($group_code);
	
	if(strstr($group_code, 'basreg'))
	{
		print "group_code:" . $group_code  ."\n";
		$roleIds = $t_role->getRoleIdByCode('basreg');
		$group_role->set('group_id',$groupids[0]);
		$group_role->set('role_id',$roleIds[0]);
		$success = true;
		
		print "group_id: ". $group_code  ."\n";
		print "role_id: " . $roleIds[0]  ."\n";
	} elseif(strstr($group_code, 'regbeh'))
	{
		print "group_code:" . $group_code  ."\n";
		$roleIds = $t_role->getRoleIdByCode('regbeh');
		$group_role->set('group_id',$groupids[0]);
		$group_role->set('role_id',$roleIds[0]);
		$success = true;
		
		print "group_id: ". $group_code  ."\n";
		print "role_id: " . $roleIds[0]  ."\n";
	} elseif(strstr($group_code, 'hfdred'))
	{
		print "group_code:" . $group_code  ."\n";
		$roleIds = $t_role->getRoleIdByCode('hfdred');
		$group_role->set('group_id',$groupids[0]);
		$group_role->set('role_id',$roleIds[0]);
		$success = true;
		
		print "group_id: ". $group_code  ."\n";
		print "role_id: " . $roleIds[0]  ."\n";
	} elseif(strstr($group_code, '_red'))
	{
		print "group_code:" . $group_code  ."\n";
		$roleIds = $t_role->getRoleIdByCode('red');
		$group_role->set('group_id',$groupids[0]);
		$group_role->set('role_id',$roleIds[0]);
		$success = true;
		
		print "group_id: ". $group_code  ."\n";
		print "role_id: " . $roleIds[0]  ."\n";
	} elseif(strstr($group_code, 'raad'))
	{
		print "group_code:" . $group_code  ."\n";
		$roleIds = $t_role->getRoleIdByCode('raad');
		$group_role->set('group_id',$groupids[0]);
		$group_role->set('role_id',$roleIds[0]);
		$success = true;
		
		print "group_id: ". $group_code  ."\n";
		print "role_id: " . $roleIds[0]  ."\n";
	} elseif(strstr($group_code, 'ond'))
	{
		print "group_code:" . $group_code  ."\n";
		$roleIds = $t_role->getRoleIdByCode('ond');
		$group_role->set('group_id',$groupids[0]);
		$group_role->set('role_id',$roleIds[0]);
		$success = true;
		
		print "group_id: ". $group_code  ."\n";
		print "role_id: " . $roleIds[0]  ."\n";
	} elseif(strstr($group_code, 'lbisgrp'))
	{
		print "group_code:" . $group_code  ."\n";
		$roleIds = $t_role->getRoleIdByCode('libis');
		$group_role->set('group_id',$groupids[0]);
		$group_role->set('role_id',$roleIds[0]);
		$success = true;
		
		print "group_id: ". $group_code  ."\n";
		print "role_id: " . $roleIds[0]  ."\n";
	} elseif(strstr($group_code, 'venagr'))
	{
		print "group_code:" . $group_code  ."\n";
		$roleIds = $t_role->getRoleIdByCode('vena');
		$group_role->set('group_id',$groupids[0]);
		$group_role->set('role_id',$roleIds[0]);
		$success = true;
		
		print "group_id: ". $group_code  ."\n";
		print "role_id: " . $roleIds[0]  ."\n";
	} elseif(strstr($group_code, 'anongr'))
	{
		print "group_code:" . $group_code  ."\n";
		$roleIds = $t_role->getRoleIdByCode('anoniem');
		$group_role->set('group_id',$groupids[0]);
		$group_role->set('role_id',$roleIds[0]);
		$success = true;
		
		print "group_id: ". $group_code  ."\n";
		print "role_id: " . $roleIds[0]  ."\n";
	}
	
	if($success)
	{
	//role saven
		$group_role->insert();
		
		if ($group_role->numErrors()) {
			print "\tERROR INSERTING link groep: {$group_code}:" .join('; ', $group_role->getErrors())."\n";
			// of er nu een fout is of niet. $success moet op false gezet worden zodat het de volgende iteratie werkt
			$success = false;
			continue;
		} else {
			print "linking gelukt groep: {$group_code}:  \n";
			// of er nu een fout is of niet. $success moet op false gezet worden zodat het de volgende iteratie werkt
			$success = false;
		}
		
	} else {
		print "\n WARNING: success was false voor {$group_code}  \n";	
	}
}

print "ENDED IMPORTING users.csv\n";

?>
