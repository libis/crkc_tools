<?php
/* Dit script wordt gebruikt om afbeeldingen van Digitool in te laden in CA 
	Opgelet de pid waarde moet hetzelfde zijn zoals het attribuut digitoolUrl verwacht
	Thumbnail en view in één lijn

*/
/*
 * Step 1: Initialisation
 */
 
set_time_limit(36000);
require_once("/www/libis/html/ca_crkc/setup.php");

require_once(__CA_LIB_DIR__.'/core/Db.php');
require_once(__CA_MODELS_DIR__.'/ca_locales.php');
require_once(__CA_MODELS_DIR__.'/ca_objects.php');

require_once(__CA_LIB_DIR__.'/core/Parsers/DelimitedDataParser.php');


$_ = new Zend_Translate('gettext', __CA_APP_DIR__.'/locale/en_US/messages.mo', 'nl_NL');

$t_locale = new ca_locales();
$pn_locale_id = $t_locale->loadLocaleByCode('nl_NL');

$t_object = new ca_objects();
$t_object->setMode(ACCESS_WRITE);

print "IMPORTING kunstvoorwerpen into an array \n";

$kunstvoorwerpen = array();
$o_tab_parser = new DelimitedDataParser("\t"); 
// Read csv; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/org_kunstvoorwerp.csv')) {
	die("Couldn't parse new_kunstvoorwerpen.csv data\n");	
}
	
print "READING new_kunstvoorwerpen.csv...\n";

$vn_c = 1;
	
$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------

while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$kunstvoorwerp_id	=	$o_tab_parser->getRowValue(1); //id (niet gebruiken)
	$vs_crkcObjectnr	=	$o_tab_parser->getRowValue(9);
	
	if (substr(trim($vs_crkcObjectnr),0,3) == "PWV" )
	{
		
		$kunstvoorwerpIdnos[$vs_crkcObjectnr] = " - (KV_".$kunstvoorwerp_id.")";
	}
	
 $vn_c++;
}
//************************************
//****       Afbeeldingen	 *****
//************************************

print "IMPORTING afbeeldingen\n";

/*
// * Step 2: Import
*/

 // want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parserAfbeeldingen = new DelimitedDataParser("\t"); 

// Read csv; line by line till end of file.
if (!$o_tab_parserAfbeeldingen->parse('/www/libis/html/ca_crkc/app/lib/ca/data/afbeeldingenPwv.csv')) {
	die("Couldn't parse afbeeldingenPwv.csv data\n");	
}
	
print "READING afbeeldingenPwv.csv...\n";

$vn_c2 = 1;
$afbeeldingen = array();	
//$o_tab_parserAfbeeldingen->nextRow(); // skip first row -> er is hier geen titelrij
//-------------------------
// waarden inlezen
//-------------------------

$success = 0;

while($o_tab_parserAfbeeldingen->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$label			=	$o_tab_parserAfbeeldingen->getRowValue(1);
	$pid			=	$o_tab_parserAfbeeldingen->getRowValue(2);
	
	// label en idno moeten nog gematcht worden
	// kunstvoorwerp_idno loop vervangen door opzoeken van label 
	
	$pattern = '/([a-zA-Z]+\.)(.*)(_)(.*)(_.*)/';
	preg_match($pattern, $label, $matches);
	$lookup = $matches[1] . "" . $matches[2] . "." . $matches[4];
	print "lookup: {$lookup} \n";
	
	if(array_key_exists($lookup,$kunstvoorwerpIdnos))
	{
		print "array_key exists /idno: {$lookup} \n";
		print "we zoeken ".$lookup . $kunstvoorwerpIdnos[$lookup];
		$object_ids = $t_object->getObjectIDsByidno($lookup . $kunstvoorwerpIdnos[$lookup]); // voeg label + kv nummer samen en zoek objectID
		print "object_id: " . $object_ids[0] ."\n" ;
		
		if(!empty($object_ids))
		{	
		
			$t_object->load($object_ids[0]);
		
			if (trim($pid)) {
				
				$t_object->addAttribute(array(
					'locale_id'	=>	$pn_locale_id,
					'digitoolUrl'	=>	trim($pid)
				), 'digitoolUrl');
				
				$success = $success + 1;
			}
			
			$t_object->update();
	
			if ($t_object->numErrors()) {
				print "\tERROR INSERTING {$object_ids} / {$label} : ".join('; ', $t_object->getErrors())."\n";
				continue;
			} else {
				print "toevoegen van object: " . $label . " gelukt \n";
			}				
		} else {
			print "Geen object gevonden voor " . $lookup . $kunstvoorwerpIdnos[$lookup] ."\n" ; 
		}
		
	} else {
		print "niks gevonden voor " . $lookup . " \n" ;	
	}
	
print "===={$vn_c2} / {$success} =====================Volgende afbeelding======================\n";

$vn_c2++;

}
print "END IMPORTING afbeeldingen.csv\n";

?>