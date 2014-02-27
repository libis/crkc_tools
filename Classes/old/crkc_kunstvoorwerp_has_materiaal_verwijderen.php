<?php
define('__CA_DONT_DO_SEARCH_INDEXING__',true);
/*
 * Step 1: Initialisation
 */
set_time_limit(36000);
require_once("/www/libis/html/ca_crkc/setup.php");

require_once(__CA_LIB_DIR__.'/core/Db.php');
require_once(__CA_MODELS_DIR__.'/ca_locales.php');
require_once(__CA_MODELS_DIR__.'/ca_objects.php');
require_once(__CA_MODELS_DIR__.'/ca_attributes.php');

require_once(__CA_LIB_DIR__.'/core/Parsers/DelimitedDataParser.php');


$_ = new Zend_Translate('gettext', __CA_APP_DIR__.'/locale/en_US/messages.mo', 'nl_NL');

$t_locale = new ca_locales();
$pn_locale_id = $t_locale->loadLocaleByCode('nl_NL');

$t_list = new ca_lists();

print "IMPORTING kunstvoorwerp \n";

/****************
 * Step 2: Import
 ****************/

 // want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t"); 

$vn_c = 1;
	
// Read csv; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_kunstvoorwerp_has_materiaal.csv')) {
	die("Couldn't parse kunstvoorwerp_has_materiaal.csv data\n");	
}
	
print "READING new_kunstvoorwerp_has_materiaal.csv...\n";

$vn_c = 1;

$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------
while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_Kunstvoorwerp_id	=	$o_tab_parser->getRowValue(1);
//	$vs_crkcObjectnr	=	$o_tab_parser->getRowValue(9);
print "removing attributes from ".$vn_Kunstvoorwerp_id." \n";

//----------------		
// Object opzoekn
//----------------

	$t_object = new ca_objects();
	$t_object->setMode(ACCESS_WRITE);
               
	$va_Kunstvoorwerp_keys = ($t_object->getObjectIDsByidnoPart(("%KV_".$vn_Kunstvoorwerp_id.")"),null) );
	
	$dimensions = sizeof($va_Kunstvoorwerp_keys);
	
	print "{$dimensions}\n";
	$j = 0;
		
	if ($dimensions > 0) {
	
		$vn_Kunstvoorwerp_key = $va_Kunstvoorwerp_keys[$j];	
		print "Primary Kunstvoorwerp-key: ".$vn_Kunstvoorwerp_key." \n";
	
		$t_object->load($vn_Kunstvoorwerp_key);
	
		if ($t_object->numErrors()) {
			print "\tERROR LOADING OBJECT {$vn_Kunstvoorwerp_id}: ".join('; ', $t_object->getErrors())."\n";
			continue;
		}

		$t_object->getPrimaryKey();
	
//-------------------------
// Waarden mappen      
//-------------------------
	
		$aantal = $t_object->getAttributeCountByElement('objectMateriaalInfo');
		print "aantal objecten: {$aantal}\n";
	
		$va_attrs = $t_object->getAttributesByElement('objectMateriaalInfo');
	
		$aantal_attrs = sizeof($va_attrs);
		$i = 0;
	
		while($aantal_attrs>0) 
		{
			$t_object->removeAttribute($va_attrs[$i]->getAttributeID());
		
			if ($t_object->numErrors()) {
				print "ERROR REMOVING ATTIBUTE objectMateriaalInfo ".$vn_Kunstvoorwerp_id."/".$vs_crkcObjectnr.": ".join('; ', $t_object->getErrors())."\n";
				
			} else {
				print "remove succesvol\n";
			}
		
			$aantal_attrs = $aantal_attrs - 1;
			$i++;
		}
		
		$t_object->update();

		if ($t_object->numErrors()) {
			print "\tERROR UPDATING OBJECT ".$vn_Kunstvoorwerp_id."/".$vs_crkcObjectnr.": ".join('; ', $t_object->getErrors())."\n";
	
		} else {
			print "update succesvol\n";
		}
	
		$dimensions = $dimensions - 1 ;
		$j++;
	
	} else {
		print "Kunstvoorwerp bestaat (nog) NIET \n";

	}

print "=============volgend record============== \n\n";
	
$vn_c++;

}

print "ENDED DELETING ATTRIBUTE objectMateriaalInfo \n";

?>
