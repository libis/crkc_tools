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

$t_list->load(array('list_code' => 'trefwoord_list')); 

/****************
 * Step 2: Import
 ****************/

 // want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t"); 

// Read csv; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_trefwoord.csv')) {
	die("Couldn't parse new_trefwoord.csv data\n");	
}
	
print "READING new_trefwoord.csv...\n";

$vn_c = 1;
	
$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------
while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_stijlen_id			=	$o_tab_parser->getRowValue(1);
	$vs_stijlen_naam		=	$o_tab_parser->getRowValue(2);			
	$vs_stijlen_description		=	$o_tab_parser->getRowvalue(3);
	$vs_stijlen_AATtermnummer	=	$o_tab_parser->getRowvalue(4);
	$vn_stijlen_poid		=	$o_tab_parser->getRowValue(9);

	print "{$vn_stijlen_id} / {$vs_stijlen_naam} / {$vs_stijlen_description} / {$vs_stijlen_AATtermnummer} / {$vs_stijlen_poid} \n";
//-------------------------
// Waarden mappen      
//-------------------------
// create the term, and add the labels for this term
print "creating term ".$vs_stijlen_naam." and adding labels for term \n\n";

if ($vs_stijlen_AATtermnummer == "/" or $vs_stijlen_AATtermnummer == "") {
	$vs_AATtermnummer = "";
} else {
	$vs_AATtermnummer = "aat_".$vs_stijlen_AATtermnummer;
}

print "AATtermnummer : {$vs_AATtermnummer}\n";

// Bepalen parent_id
/*
if ($vn_stijlen_poid == 1) {
	$vn_Parent = $t_parent->getItemIDFromList('stijl_lijst', 'WesterseStijl');
} elseif ($vn_stijlen_poid == 2) {
	$vn_Parent = $t_parent->getItemIDFromList('stijl_lijst', 'EtnografischeStijl');
} elseif ($vn_stijlen_poid == 3) {
	$vn_Parent = $t_parent->getItemIDFromList('stijl_lijst', 'OrientaalseStijl');
} else {
	print "ERROR Geen parent-id voor stijl gedefinieerd";
	$vn_Parent = null;
}

print " Parent_id {$vn_Parent} \n";
*/

// public function addItem($ps_value, $pb_is_enabled=true, $pb_is_default=false, $pn_parent_id=null, $pn_type_id=null, $ps_idno=null, $ps_validation_format='', $pn_status=0, $pn_access=0, $pn_rank=null)

if ($t_item = $t_list->addItem($vs_stijlen_naam, true, false, null, null, $vs_AATtermnummer,'', 4, 1, null)) {
	// add preferred labels
	if (!($t_item->addLabel(
		array('name_singular' => $vs_stijlen_naam, 
			'name_plural' => $vs_stijlen_naam, 
			'description' => $vs_stijlen_description),
		$pn_locale_id, null, true
	))) {
		print "ERROR: Could not add preferred label to trefwoord_lijst ".$vs_stijlen_naam.": ".join("; ", $t_item->getErrors())."\n";
	}
}					
$vn_c++;
}
print "IMPORT COMPLETE.\n";

?>
