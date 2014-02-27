<?php
/*
 * Step 1: Initialisation
 */
set_time_limit(36000);
require_once("/www/libis/html/ca_crkc/setup.php");

require_once(__CA_LIB_DIR__.'/core/Db.php');
require_once(__CA_MODELS_DIR__.'/ca_locales.php');
require_once(__CA_MODELS_DIR__.'/ca_lists.php');

require_once(__CA_LIB_DIR__.'/core/Parsers/DelimitedDataParser.php');


$_ = new Zend_Translate('gettext', __CA_APP_DIR__.'/locale/en_US/messages.mo', 'nl_NL');

$t_locale = new ca_locales();
$pn_locale_id = $t_locale->loadLocaleByCode('nl_NL');

$t_list = new ca_lists();

$t_parent = new ca_lists();

/*
*******************************
trefwoord_lijst
*******************************
*/
print "IMPORTING TREFWOORD_LIJST\n";

$t_list->load(array('list_code' => 'iconografie_lijst')); 

/****************
 * Step 2: Import
 ****************/

 // want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t"); 

// Read csv; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_trefwoordiconografie.csv')) {
	die("Couldn't parse new_trefwoordiconografie.csv data\n");	
}
	
print "READING new_trefwoordiconografie.csv...\n";

$vn_c = 1;
	
$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------
while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_Trefwoord_id			=	$o_tab_parser->getRowValue(1);
	$vs_Identificatie		=	$o_tab_parser->getRowValue(2);			
	$vs_Omschrijving		=	$o_tab_parser->getRowvalue(3);
	
	print "{$vn_Iconografie_id} / {$vs_Identificatie} / {$vs_Omschrijving} \n";
//-------------------------
// Waarden mappen      
//-------------------------
// create the term, and add the labels for this term
print "creating term ".$vs_Identificatie." and adding labels for term \n\n";

// public function addItem($ps_value, $pb_is_enabled=true, $pb_is_default=false, $pn_parent_id=null, $pn_type_id=null, $ps_idno=null, $ps_validation_format='', $pn_status=0, $pn_access=0, $pn_rank=null)

if ($t_item = $t_list->addItem($vs_Identificatie, true, false, null, null, $vn_Trefwoord_id,'', 4, 1, null)) {
	// add preferred labels
	if (!($t_item->addLabel(
		array('name_singular' => $vs_Identificatie, 
			'name_plural' => $vs_Identificatie, 
			'description' => $vs_Omschrijving),
		$pn_locale_id, null, true
	))) {
		print "ERROR: Could not add preferred label to trefwoordiconografie_lijst ".$vs_Identificatie.": ".join("; ", $t_item->getErrors())."\n";
	}
}					
$vn_c++;
}
print "IMPORT COMPLETE.\n";

?>
