<?php
/* Dit script wordt gebruikt om afbeeldingen van Digitool in te laden in CA 
	Opgelet de pid waarde moet hetzelfde zijn zoals het attribuut digitoolUrl verwacht
	Thumbnail en view in één lijn
*/
define('__CA_DONT_DO_SEARCH_INDEXING__',true);
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


print "IMPORTING kunstvoorwerpen into an array \n";

$o_tab_parser = new DelimitedDataParser("\t");


print "IMPORTING afbeeldingen\n";

/*
// * Step 2: Import
*/

 // want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parserAfbeeldingen = new DelimitedDataParser("\t"); 

// Read csv; line by line till end of file.
if (!$o_tab_parserAfbeeldingen->parse('/www/libis/html/ca_crkc/app/lib/ca/data/afbeeldingen.csv')) {
	die("Couldn't parse afbeeldingenPov.csv data\n");	
}
	
print "READING afbeeldingen.csv...\n";

$vn_c2 = 1;

$afbeeldingen = array();

$o_tab_parserAfbeeldingen->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------
while($o_tab_parserAfbeeldingen->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$label			=	$o_tab_parserAfbeeldingen->getRowValue(0);
	$pid			=	$o_tab_parserAfbeeldingen->getRowValue(1);
	
	$afbeeldingen[$label] = $pid;
	
	$vn_c2++;
}

print "\n Creating afbeelding voor ".$label." \n"; 

	// label en idno moeten nog gematcht worden
	// kunstvoorwerp_idno loop vervangen door opzoeken van label
	
$t_object = new ca_objects();

foreach($afbeeldingen as $label_key => $pid_value)
{
	
	$pattern = '/([a-zA-Z]+\.)(.*)(_)(.*)(_.*)/';
	preg_match($pattern, $label_key, $matches);
	$lookup = $matches[1] . $matches[2] . "." . $matches[4];
	
	if(array_key_exists($lookup,$kunstvoorwerpIdnos))
	{
		
		$object_ids = $t_object->getObjectIDsByidno($lookup);
		
		if(!empty($object_ids))
		{	
			$t_object->setMode(ACCESS_WRITE);
			
			$t_object->load($object_ids[0]);
				if (trim($pid_value)) {
					$t_object->addAttribute(array(
						'locale_id'	=>	$pn_locale_id,
					'digitoolUrl'	=>	trim($pid_value)
				), 'digitoolUrl');
			}
			
			//$t_object->update();
	
			if ($t_object->numErrors()) {
				print "\tERROR UPDATING {$object_ids[0]}/{$pid_value}: ".join('; ', $t_user->getErrors())."\n";
				continue;
			} else {
				print "\n toevoegen van afbeelding aan object : " . $label_key ." / ". $lookup. " gelukt"; 	
			}
						
		} else {
			print "Geen object gevonden voor koppelen " . $lookup;
		}
		
	}
}
print "END IMPORTING afbeeldingen.csv\n";

?>
