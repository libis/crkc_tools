<?php
/* 
	dit script wordt gebruikt om users aan groepen te hangen, maar enkel voor ALLES (= alle collecties), en Vraag en aanbod
	Andere (collectie specifieke rechten) worden met een ander script ingesteld

*/
/*
 * Step 1: Initialisation
 */
set_time_limit(36000);
require_once("/www/libis/html/ca_crkc/setup.php");

require_once(__CA_LIB_DIR__.'/core/Db.php');
require_once(__CA_MODELS_DIR__.'/ca_locales.php');
require_once(__CA_MODELS_DIR__.'/ca_users.php');

require_once(__CA_LIB_DIR__.'/core/Parsers/DelimitedDataParser.php');

$_ = new Zend_Translate('gettext', __CA_APP_DIR__.'/locale/en_US/messages.mo', 'nl_NL');

$t_locale = new ca_locales();
$pn_locale_id = $t_locale->loadLocaleByCode('nl_NL');

//************************************
//****       USERS		 *****
//************************************

print "IMPORTING users \n";

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
	
print "\n linking  ".$login." for users. \n"; 
 
	
//----------------		
// Create object
//----------------

	$t_user = new ca_users();
	$t_user->setMode(ACCESS_WRITE);
	
// dit werkt -> zie functie load() in ca_users.php

	$t_user->load($login);
	
//-------------------------
// Waarden mappen      
//-------------------------
	
	$user_id = $t_user->getPrimaryKey();
	
	$groups =  array();
	$group = "";
	
	// Creeren van vraag en aanbod groep. Het linken gebeurt later
	if(trim($vena) == "1")
	{
		$group = "venagr";
		array_push($groups, $group);
		
		print "user: " .$login . "groep: " . $group ."\n";
	}
	
	// Deze groepen hebben rechten op Alle collecties en objecten
	// ALLES - NIETS mag genegeerd worden
	// ALLES - ER
	if((trim($collectie_crkc1) == "ALLES" && trim($recht_crkc1) == "ER") 
	|| (trim($collectie_crkc2) == "ALLES" && trim($recht_crkc2) == "ER")
	|| (trim($collectie_crkc3) == "ALLES" && trim($recht_crkc3) == "ER"))
	{
		$group = "crkc_hfdred";
		array_push($groups, $group);
		print "user: " .$login . "groep: " . $group ."\n";
	}
	if((trim($collectie_pov1) == "ALLES" && trim($recht_pov1) == "ER") 
	|| (trim($collectie_pov2) == "ALLES" && trim($recht_pov2) == "ER")) 
	{
		$group = "pov_hfdred";
		array_push($groups, $group);
		print "user: " .$login . "groep: " . $group ."\n";	
	}
	if((trim($collectie_pa1) == "ALLES" && trim($recht_pa1) == "ER") 
	|| (trim($collectie_pa2) == "ALLES" && trim($recht_pa2) == "ER")
	|| (trim($collectie_pa3) == "ALLES" && trim($recht_pa3) == "ER"))
	{
		$group = "pa_hfdred";
		array_push($groups, $group);
		print "user: " .$login . "groep: " . $group ."\n";
	}	
	if(trim($collectie_pwv1) == "ALLES" && trim($recht_pwv1) == "ER")
	{
		$group = "pwv_hfdred";
		array_push($groups, $group);
		print "user: " .$login . "groep: " . $group ."\n";
	}
	// ALLES - VR
	if((trim($collectie_crkc1) == "ALLES" && trim($recht_crkc1) == "VR")
	|| (trim($collectie_crkc2) == "ALLES" && trim($recht_crkc2) == "VR")
	|| (trim($collectie_crkc3) == "ALLES" && trim($recht_crkc3) == "VR"))
	{
		$group = "crkc_red";
		array_push($groups, $group);
		print "user: " .$login . "groep: " . $group ."\n";
	}
	if((trim($collectie_pov1) == "ALLES" && trim($recht_pov1) == "VR")
	|| (trim($collectie_pov2) == "ALLES" && trim($recht_pov2) == "VR"))
	{
		$group = "pov_red";
		array_push($groups, $group);
		print "user: " .$login . "groep: " . $group ."\n";
	}
	if((trim($collectie_pa1) == "ALLES" && trim($recht_pa1) == "VR")
	|| (trim($collectie_pa2) == "ALLES" && trim($recht_pa2) == "VR")
	|| (trim($collectie_pa3) == "ALLES" && trim($recht_pa3) == "VR"))
	{
		$group = "pa_red";
		array_push($groups, $group);
		print "user: " .$login . "groep: " . $group ."\n";
	}
	if(trim($collectie_pwv1) == "ALLES" && trim($recht_pwv1) == "VR")
	{
		$group = "pwv_red";
		array_push($groups, $group);
		print "user: " .$login . "groep: " . $group ."\n";
	}
	// ALLES - O
	if((trim($collectie_crkc1) == "ALLES" && trim($recht_crkc1) == "O")
	|| (trim($collectie_crkc2) == "ALLES" && trim($recht_crkc2) == "O")
	|| (trim($collectie_crkc3) == "ALLES" && trim($recht_crkc3) == "O"))
	{
		$group = "crkc_ond";
		array_push($groups, $group);
		print "user: " .$login . "groep: " . $group ."\n";
	}
	if((trim($collectie_pov1) == "ALLES" && trim($recht_pov1) == "O")
	|| (trim($collectie_pov2) == "ALLES" && trim($recht_pov2) == "O"))
	{
		$group = "pov_ond";
		array_push($groups, $group);
		print "user: " .$login . "groep: " . $group ."\n";
	}
	if((trim($collectie_pa1) == "ALLES" && trim($recht_pa1) == "O")
	|| (trim($collectie_pa2) == "ALLES" && trim($recht_pa2) == "O")
	|| (trim($collectie_pa3) == "ALLES" && trim($recht_pa3) == "O"))
	{
		$group = "pa_ond";
		array_push($groups, $group);
		print "user: " .$login . "groep: " . $group ."\n";
	}
	if(trim($collectie_pwv1) == "ALLES" && trim($recht_pwv1) == "O")
	{
		$group = "pwv_ond";
		array_push($groups, $group);
		print "user: " .$login . "groep: " . $group ."\n";
	}
	// ALLES - E
	if((trim($collectie_crkc1) == "ALLES" && trim($recht_crkc1) == "E")
	|| (trim($collectie_crkc2) == "ALLES" && trim($recht_crkc2) == "E")
	|| (trim($collectie_crkc3) == "ALLES" && trim($recht_crkc3) == "E"))
	{
		$group = "crkc_raad";
		array_push($groups, $group);
		print "user: " .$login . "groep: " . $group ."\n";
	}
	if((trim($collectie_pov1) == "ALLES" && trim($recht_pov1) == "E")
	|| (trim($collectie_pov2) == "ALLES" && trim($recht_pov2) == "E"))
	{
		$group = "pov_raad";
		array_push($groups, $group);
		print "user: " .$login . "groep: " . $group ."\n";
	}
	if((trim($collectie_pa1) == "ALLES" && trim($recht_pa1) == "E")
	|| (trim($collectie_pa2) == "ALLES" && trim($recht_pa2) == "E")
	|| (trim($collectie_pa3) == "ALLES" && trim($recht_pa3) == "E"))
	{
		$group = "pa_raad";
		array_push($groups, $group);
		print "user: " .$login . "groep: " . $group ."\n";
	}
	if(trim($collectie_pwv1) == "ALLES" && trim($recht_pwv1) == "E")
	{
		$group = "pwv_raad";
		array_push($groups, $group);
		print "user: " .$login . "groep: " . $group ."\n";
	}
	// ALLES - BR
	if((trim($collectie_crkc1) == "ALLES" && trim($recht_crkc1) == "BR")
	|| (trim($collectie_crkc2) == "ALLES" && trim($recht_crkc2) == "BR")
	|| (trim($collectie_crkc3) == "ALLES" && trim($recht_crkc3) == "BR"))
	{
		$group = "crkc_basreg";
		array_push($groups, $group);
		print "user: " .$login . "groep: " . $group ."\n";
	}
	if((trim($collectie_pov1) == "ALLES" && trim($recht_pov1) == "BR")
	|| (trim($collectie_pov2) == "ALLES" && trim($recht_pov2) == "BR"))
	{
		$group = "pov_basreg";
		array_push($groups, $group);
		print "user: " .$login . "groep: " . $group ."\n";
	}
	if((trim($collectie_pa1) == "ALLES" && trim($recht_pa1) == "BR")
	|| (trim($collectie_pa2) == "ALLES" && trim($recht_pa2) == "BR")
	|| (trim($collectie_pa3) == "ALLES" && trim($recht_pa3) == "BR"))
	{
		$group = "pa_basreg";
		array_push($groups, $group);
		print "user: " .$login . "groep: " . $group ."\n";
	}
	if(trim($collectie_pwv1) == "ALLES" && trim($recht_pwv1) == "BR")
	{
		$group = "pwv_basreg";
		array_push($groups, $group);
		print "user: " .$login . "groep: " . $group ."\n";
	}
	// ALLES - CB
	if((trim($collectie_crkc1) == "ALLES" && trim($recht_crkc1) == "CB")
	|| (trim($collectie_crkc2) == "ALLES" && trim($recht_crkc2) == "CB")
	|| (trim($collectie_crkc3) == "ALLES" && trim($recht_crkc3) == "CB"))
	{
		$group = "crkc_regbeh";
		array_push($groups, $group);
		print "user: " .$login . "groep: " . $group ."\n";
	}
	if((trim($collectie_pov1) == "ALLES" && trim($recht_pov1) == "CB")
	|| (trim($collectie_pov2) == "ALLES" && trim($recht_pov2) == "CB"))
	{
		$group = "pov_regbeh";
		array_push($groups, $group);
		print "user: " .$login . "groep: " . $group ."\n";
	}
	if((trim($collectie_pa1) == "ALLES" && trim($recht_pa1) == "CB")
	|| (trim($collectie_pa2) == "ALLES" && trim($recht_pa2) == "CB")
	|| (trim($collectie_pa3) == "ALLES" && trim($recht_pa3) == "CB"))
	{
		$group = "pa_regbeh";
		array_push($groups, $group);
		print "user: " .$login . "groep: " . $group ."\n";
	}
	if(trim($collectie_pwv1) == "ALLES" && trim($recht_pwv1) == "CB")
	{
		$group = "pwv_regbeh";
		array_push($groups, $group);
		print "user: " .$login . "groep: " . $group ."\n";
	}
	if(!empty($groups))
	{
		// Update wordt maar tijdelijk in deze if gezet aangezien er nog niks anders wordt geupdatet
		
		$aantal = $t_user->addToGroups($groups);
		// $groups = array met groups waar user in moet komen
		// returnvalue: number (= aantal groups waar user aan toegevoegd werd) of false
				
		// is deze update() hierna wel nodig -> commentaar
		// $t_user->update();
	
		if ($t_user->numErrors() or (!$aantal)) {
			print "\tERROR ADDING user to GROUPS : ".join('; ', $t_user->getErrors())."\n";
			continue;
		} else {
			print "user aan {$aantal} groups toegevoegd \n";
			
		}
	}
	// -------------------------------------------
	// Maak link naar entities
	//--------------------------------------------
	
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

print "ENDED IMPORTING usersgroups\n";

?>