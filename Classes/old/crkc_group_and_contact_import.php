<?php

/*
 * Step 1: Initialisation
 */

set_time_limit(36000);
require_once("/www/libis/html/ca_crkc/setup.php");

require_once(__CA_LIB_DIR__.'/core/Db.php');
require_once(__CA_MODELS_DIR__.'/ca_locales.php');
require_once(__CA_MODELS_DIR__.'/ca_entities.php');

require_once(__CA_LIB_DIR__.'/core/Parsers/DelimitedDataParser.php');


$_ = new Zend_Translate('gettext', __CA_APP_DIR__.'/locale/en_US/messages.mo', 'nl_NL');

$t_locale = new ca_locales();
$pn_locale_id = $t_locale->loadLocaleByCode('nl_NL');

$t_list = new ca_lists();
$pn_entity_type_id =	$t_list->getItemIDFromList('entity_types', 'organization');	

//***********************************
//****   GROUP_AND_CONTACT      *****
//***********************************

print "IMPORTING group and contact \n";

/****************
 * Step 2: Import
 ****************/

 // want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t"); 

// Read csv; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/Query_group_and_contact.csv')) {
	die("Couldn't parse Query_group_and_address.csv data\n");	
}
	
print "READING Query_group_and_contact.csv...\n";

$vn_c = 1;
	
$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------
while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_Org_id		=	$o_tab_parser->getRowValue(1);
	$vs_Org_Naam		=	$o_tab_parser->getRowValue(2);
	$vs_Contact_id		=	$o_tab_parser->getRowValue(3);
	$vn_Type_Contact_id	=	$o_tab_parser->getRowvalue(4);
	$vs_Contact_Info	=	$o_tab_parser->getRowvalue(5);
	$vs_Opmerking_1		=	$o_tab_parser->getRowvalue(6);
	$vs_Opmerking_2		=	$o_tab_parser->getRowvalue(7);

print "creating contact info for organization ".$vn_Org_id.".".$vs_Org_Naam."  \n";

// ------------------------------------
// Waarden omvormen naar gepaste output
// ------------------------------------
	
	if (($vn_Type_Contact_id) == 1) {
		$vn_Type_Contact_type	=	$t_list->getItemIDFromList('contact_type', 'telefoon');
	} elseif (($vn_Type_Contact_id) == 2) {
		$vn_Type_Contact_type  	=       $t_list->getItemIDFromList('contact_type', 'fax');
	} elseif (($vn_Type_Contact_id) == 3) {
		$vn_Type_Contact_type  	=       $t_list->getItemIDFromList('contact_type', 'emailZakelijk');
	} elseif (($vn_Type_Contact_id) == 4) {
		$vn_Type_Contact_type  	=       $t_list->getItemIDFromList('contact_type', 'emailPrive');
	} elseif (($vn_Type_Contact_id) == 5) {
		$vn_Type_Contact_type  	=       $t_list->getItemIDFromList('contact_type', 'telefoonMobiel');
	} elseif (($vn_Type_Contact_id) == 6) {
		$vn_Type_Contact_type  	=       $t_list->getItemIDFromList('contact_type', 'telefoonPrive');
	} else {
		$vn_Type_Contact_type = $t_list->getItemIDFromList('contact_type', 'blank');
		print "   - WARNING: geen type_contact voor organisatie {$vs_Org_Naam}, verbeter indien nodig \n";	
	} 

//	print "Type_Contact :".$vn_Type_Contact_type."\n";
	
	if ($vs_Opmerking_1 and $vs_Opmerking_2) {
		$vs_Opmerking = $vs_Opmerking_1." - ".$vs_Opmerking_2;
	} elseif ($vs_Opmerking_1 and !$vs_Opmerking_2) {
		$vs_Opmerking = $vs_Opmerking_1;
	} elseif (!$vs_Opmerking_1 and $vs_Opmerking_2) {
		$vs_Opmerking = $vs_Opmerking_2;
	} else {
		$vs_Opmerking = "";
	}
//----------------		
// Create object
//----------------

	$t_org = new ca_entities();
	$t_org->setMode(ACCESS_WRITE);
               
	$va_Org_keys = ($t_org->getEntityIDsByName(null,$vs_Org_Naam) );
	
	$dimension = sizeof($va_Org_keys);
	
	$vn_Org_key = array_shift($va_Org_keys);
	
//	print "aantal: ".$dimension."\n";
//	print "Primary key: ".$vn_Org_key." \n";
	
	$t_org->load($vn_Org_key);
	
	if ($t_org->numErrors()) {
		print "\tERROR LOADING {$vn_Org_id}/{$vs_Org_Naam}: ".join('; ', $t_org->getErrors())."\n";
		continue;
	}

	$t_org->getPrimaryKey();

	if ($t_org->numErrors()) {
		print "\tERROR P_KEY {$vn_Org_id}/{$vs_Org_Naam}: ".join('; ', $t_org->getErrors())."\n";
		continue;
	}

//-------------------------
// Waarden mappen      
//-------------------------

	$t_org->addAttribute(array(
		'locale_id'		=>	$pn_locale_id,
		'contacttype'		=>	$vn_Type_Contact_type,
		'contactgeven'		=>	$vs_Contact_Info,
		'contactOpmerkingen'	=>	$vs_Opmerking
	), 'contactInfo');

	$t_org->update();

	if ($t_org->numErrors()) {
		print "\tERROR INSERTING {$vn_Org_id}/{$vs_Org_Naam}: ".join('; ', $t_org->getErrors())."\n";
		continue;
	}
	
	$vn_c++;

}

print "ENDED IMPORTING group_and_contact \n";

?>
