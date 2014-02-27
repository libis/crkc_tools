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

require_once(__CA_LIB_DIR__.'/core/Parsers/DelimitedDataParser.php');


$_ = new Zend_Translate('gettext', __CA_APP_DIR__.'/locale/en_US/messages.mo', 'nl_NL');

$t_locale = new ca_locales();
$pn_locale_id = $t_locale->loadLocaleByCode('nl_NL');

$t_list = new ca_lists();

$t_object = new ca_objects();
$t_object->setMode(ACCESS_WRITE);
               

//*************************************
//**** KUNSTVOORWERP_OBJECTNAAM   *****
//*************************************

print "IMPORTING kunstvoorwerp objectnaam \n";

/****************
 * Step 2: Import
 ****************/

 // want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t"); 

// Read csv; line by line till end of file
// and put information in an array
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_thesaurus.csv')) {
	die("Couldn't parse new_thesaurus.csv data\n");	
}
$va_Thesaurus = array();
	
$vn_c = 1;

$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------
while($o_tab_parser->nextRow()) 
{
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_Id		=	$o_tab_parser->getRowValue(1);
	$vs_Term	=	$o_tab_parser->getRowValue(2);
	$vs_Type	=	$o_tab_parser->getRowValue(3);
	
	$va_Thesaurus[$vn_Id] = array($vs_Term, $vs_Type);
	
	$vn_c++;
}

//print_r($va_Stijlen);

$vn_c = 1;
	
// Read csv; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_kunstvoorwerp.csv')) {
	die("Couldn't parse kunstvoorwerp.csv data\n");	
}
	
print "READING new_kunstvoorwerp.csv...\n";

$vn_c = 1;

$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------
while($o_tab_parser->nextRow()) 
{
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_Kunstvoorwerp_id		=	$o_tab_parser->getRowValue(1);
	$vs_crkcObjectnr		=	$o_tab_parser->getRowValue(9);
	$vs_Titel			=	$o_tab_parser->getRowValue(11);
	$vn_ObjectNaam_id		=	$o_tab_parser->getRowvalue(26);
	$vn_ThesaurusTermObjectNaam_id	=	$o_tab_parser->getRowvalue(41);

	print "creating objectnaam voor kunstvoorwerp ".$vn_Kunstvoorwerp_id.".".$vs_crkcObjectnr."  \n";
	print "{$vn_ThesaurusTermObjectNaam_id} / {$vn_ObjectNaam_id} / {$vs_Titel} \n";
// ------------------------------------
// De logica: wat invullen bij ojectnaam
// ------------------------------------
if (!(isset($vn_ThesaurusTermObjectNaam_id)) or (!($vn_ThesaurusTermObjectNaam_id == 0))) 
{
	$va_Term_waarde = $va_Thesaurus[$vn_ThesaurusTermObjectNaam_id];

	$vs_Term_Text = $va_Term_waarde[0];
	$vs_Term_Type = $va_Term_waarde[1];
	
	print "op te zoeken waarde {$vs_Term_Text} / {$vs_Term_Type} \n";
	
	$vn_Term_waarde = $t_list->getItemIDFromListByLabel('move_objectnaam', trim($vs_Term_Text));
	print " (1) objectnaam_id in amMove-objectnaam: {$vn_Term_waarde} \n ";
	if ($vn_Term_waarde) 
	{
		Print " success 1: objecnaam teruggevonden in amMove: {$vn_Term_waarde} \n";
		// lijst instellen - geen opmerking		
		$vs_Opmerking = '';
		
	} else {
		
		Print " geen success 1: {$vs_Term_Text}/{$vs_Term_Type} - opmerking invullen \n";
		$vn_Term_waarde = $t_list->getItemIDFromListByLabel('move_objectnaam', '-');
		$vs_Opmerking = "Objectnaam: ".$vs_Term_Text." (".$vs_Term_Type.")";
	}
	
} elseif (!(isset($vn_ObjectNaam_id)) or (!($vn_ObjectNaam_id == 0))) 
	{
		// over naar vs_Objectnaam_id	
		$va_Term_waarde = $va_Thesaurus[$vn_ObjectNaam_id];

		$vs_Term_Text = $va_Term_waarde[0];
		$vs_Term_Type = $va_Term_waarde[1];

		$vn_Term_waarde = $t_list->getItemIDFromListByLabel('move_objectnaam', trim($va_Term_waarde[0]));
		print " (2) objectnaam_id in amMove-objectnaam {$vn_Term_waarde} \n ";

		if ($vn_Term_waarde) {

			Print " success 2: objecnaam teruggevonden in amMove: {$vn_Term_waarde} \n";
			// lijst instellen - geen opmerking		
			$vs_Opmerking = '';
			
		} elseif (!(trim($vs_Titel) == '')) 
			{	
				print " geen success 2: plaatsen titel in opmerking \n";
		
				$vn_Term_waarde = $t_list->getItemIDFromListByLabel('move_objectnaam', '-');
				$vs_Opmerking = "Titel: ".$vs_Titel;")";
				
			} else {
			
				print " geen success 3: Er is zelfs geen titel.  Container moet dicht blijven \n";
				unset($vn_Term_waarde);
				unset($vs_Opmerking);
			}
	}
	
//--------------------------------		
// Primary key van Object opzoeken
// wat volgt is enkel nodig als we effectief een waarde hebben voor objectnaam -> if
//--------------------------------

if (($vn_Term_waarde)) 
{ 
		$va_Kunstvoorwerp_keys = ($t_object->getObjectIDsByidnoPart(("%KV_".$vn_Kunstvoorwerp_id.")"),null) );
	
		$dimensions = sizeof($va_Kunstvoorwerp_keys[0]);
		
		print "{$dimensions}\n";
		
		if ($dimensions > 0) {
	
			$vn_Kunstvoorwerp_key = $va_Kunstvoorwerp_keys[0];
	
			print "Primary Kunstvoorwerp-key: ".$vn_Kunstvoorwerp_key." \n";
	
			$t_object->load($vn_Kunstvoorwerp_key);
			
			// Object in geheugen laden

			if ($t_object->numErrors()) {
				print "\tERROR LOADING OBJECT {$vn_Kunstvoorwerp_id}: ".join('; ', $t_object->getErrors())."\n";
				print "======{$vn_c}=======volgend record============== \n\n";
				continue;
			}
			
			// Primary key opvragen

			$t_object->getPrimaryKey();
	
			// Nieuwe Waarden mappen      

			$t_object->addAttribute(array(
				'locale_id'			=>	$pn_locale_id,
				'objectNaam'			=>	$vn_Term_waarde
			), 'objectNaam' );
			
			if (trim($vs_Opmerking)) {
				$t_object->addAttribute(array(
					'locale_id'		=>	$pn_locale_id,
					'objectHistoriek'	=>	"Objectnaam: " .trim($vs_Opmerking)
				), 'objectHistoriek');
			}			

			// Update record			
			$t_object->update();

			if ($t_object->numErrors()) {
				print "\tERROR UPDATING OBJECT {$vs_crkcObjectnr}: ".join('; ', $t_object->getErrors())."\n";
				print "======{$vn_c}=======volgend record============== \n\n";
				continue;
			} else {
				print "update succesvol\n";
			}
	
	
		} else {
			print "Kunstvoorwerp bestaat (nog) NIET \n";
		}

	} else {
		print "WARNING: Geen ThesaurusTermObjectNaam, noch Objectnaam, noch Titel beschikbaar voor {$vs_crkcObjectnr}  \n";
	
	}

unset($vn_Term_waarde);
unset($vs_Opmerking);
unset($va_Term_waarde);
unset($vs_Term_Text);
unset($vs_Term_Type);
	
print "======{$vn_c}=======volgend record============== \n\n";
	
$vn_c++;

}

print "ENDED IMPORTING kunstvoorwerp_has_materiaal \n";

?>
