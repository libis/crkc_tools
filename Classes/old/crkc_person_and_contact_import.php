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
$pn_entity_type_id =	$t_list->getItemIDFromList('entity_types', 'individual');	

//************************************
//****   PERSON_AND_CONTACT      *****
//************************************

print "IMPORTING person and contact \n";

/****************
 * Step 2: Import
 ****************/

 // want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t"); 

// Read csv; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/Query_person_and_contact.csv')) {
	die("Couldn't parse Query_person_and_address.csv data\n");	
}
	
print "READING Query_person_and_contact.csv...\n";

$vn_c = 1;
	
$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------
while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_Personen_id			=	$o_tab_parser->getRowValue(1);
	$vs_Personen_Naam		=	$o_tab_parser->getRowValue(2);
	$vs_Personen_Voornaam		=	$o_tab_parser->getRowValue(3);
	$vs_Contact_id			=	$o_tab_parser->getRowValue(4);
	$vn_Type_Contact_id		=	$o_tab_parser->getRowvalue(5);
	$vs_Contact_Info		=	$o_tab_parser->getRowvalue(6);
	$vs_Opmerking_1			=	$o_tab_parser->getRowvalue(7);
	$vs_Opmerking_2			=	$o_tab_parser->getRowvalue(8);

print "creating contact info for person ".$vn_Personen_id.".".$vs_Personen_Naam."  \n";

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
		$vn_Type_Contact_type	=	$t_list->getItemIDFromList('contact_type', 'telefoon');
		print "   - WARNING: geen type_contact voor persoon {$vs_Personen_Naam}, ingesteld op telefoon, verbeter indien nodig \n";	
	} 

	print "Type_Contact :".$vn_Type_Contact_type."\n";
	
//----------------		
// Create object
//----------------

	$t_person = new ca_entities();
	$t_person->setMode(ACCESS_WRITE);
/*
	$o_db = new Db();
        $qr_res = $o_db->query("
        	SELECT DISTINCT cae.entity_id
                FROM ca_entities cae
                INNER JOIN ca_entity_labels AS cael ON cael.entity_id = cae.entity_id
                WHERE
                cael.forename = ? AND cael.surname = ?
        ", (string)$vs_Personen_Voornaam, (string)$vs_Personen_Naam);
        $va_Person_key = array();
        
        while($qr_res->nextRow()) {
        	$va_Person_key[] = $qr_res->get('entity_id');
        }
*/        
               
	$va_Person_keys = ($t_person->getEntityIDsByName($vs_Personen_Voornaam,$vs_Personen_Naam) );
	
	$dimension = sizeof($va_Person_keys);
	
	$vn_Person_key = array_shift($va_Person_keys);
	
	print "aantal: ".$dimension."\n";
	print "Primary key: ".$vn_Person_key." \n";
	
	$t_person->load($vn_Person_key);
	
	if ($t_person->numErrors()) {
		print "\tERROR LOADING {$vn_Personen_id}/{$vs_Personen_Naam}: ".join('; ', $t_person->getErrors())."\n";
		continue;
	}

	$t_person->getPrimaryKey();

	if ($t_person->numErrors()) {
		print "\tERROR P_KEY {$vn_Personen_id}/{$vs_Personen_Naam}: ".join('; ', $t_person->getErrors())."\n";
		continue;
	}

//-------------------------
// Waarden mappen      
//-------------------------
	if(trim($vs_Opmerking_2) <> "") { 
		$vs_Opmerking = $vs_Opmerking_1 . "\n" . $vs_Opmerking_2;
	} else {
		$vs_Opmerking = $vs_Opmerking_1;
	}
	
	$t_person->addAttribute(array(
		'locale_id'		=>	$pn_locale_id,
		'contacttype'		=>	$vn_Type_Contact_type,
		'contactgeven'		=>	$vs_Contact_Info,
		'contactOpmerkingen'	=>	$vs_Opmerking
	), 'contactInfo');


/*
	$t_person->addAttribute(array(
		'locale_id'	=>	$pn_locale_id,
		'contactgeven'	=>	$vs_Contact_Info
	), 'prefix');

if (trim($vs_Opmerking) <> "") {
		$t_person->addAttribute(array(
		'locale_id'		=>	$pn_locale_id,
		'contactOpmerkingen'	=>	$vs_Opmerking_1
	), 'contactOpmerkingen');

}

	$t_person->set('contacttype',$vn_Type_Contact_type);
	$t_person->set('contactgeven',$vs_Contact_Info);
	$t_person->set('contactOpmerkingen',$vs_Opmerking_1);
*/

	$t_person->update();

	if ($t_person->numErrors()) {
		print "\tERROR INSERTING {$vn_Personen_id}/{$vs_Personen_Naam}: ".join('; ', $t_person->getErrors())."\n";
		continue;
	}
	
	$vn_c++;

}

print "ENDED IMPORTING person_and_contact \n";

?>
