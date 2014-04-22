<?php

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

$t_attrib = new ca_attributes();
$t_attrib->setMode(ACCESS_WRITE);

//************************************************
//**** KUNSTVOORWERP_HAS_TYPESVANAFMETINGEN  *****
//************************************************

//print "IMPORTING kunstvoorwerp has typesvanafmetingen \n";

/****************
 * Step 2: Import
 ****************/

 // want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t"); 

$vn_c = 1;

$va_Types_Afmetingen = array();

// Read csv; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_typesvanafmetingen.csv')) {
	die("Couldn't parse typesvanafmetingen.csv data\n");
}
	
print "READING new_typesvanafmetingen.csv...\n";

$vn_c = 1;

$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------
while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_Id			=	$o_tab_parser->getRowValue(1);
	$vs_Identificatie	=	$o_tab_parser->getRowValue(2);
	$vs_Omschrijving	=	$o_tab_parser->getRowValue(3);
	$vs_Eenheid		=	$o_tab_parser->getRowValue(4);
	$vs_poid		=	$o_tab_parser->getRowValue(5);

	$va_Types_Afmetingen[$vn_Id] = array($vs_Identificatie, $vs_Omschrijving, $vs_Eenheid, $vs_poid);
	
	$vn_c++;
}

// Read csv; line by line till end of file.
if (!$o_tab_parser->parse('/www/libis/html/ca_crkc/app/lib/ca/data/new_kunstvoorwerp_has_typesvanafmetingen.csv')) {
	die("Couldn't parse kunstvoorwerp_has_typesvanafmetingen.csv data\n");	
}
	
print "READING new_kunstvoorwerp_has_typesvanafmetingen.csv...\n";

$vn_c = 1;

$o_tab_parser->nextRow(); // skip first row
//-------------------------
// waarden inlezen
//-------------------------
while($o_tab_parser->nextRow()) {
	// Get columns from tab file and put them into named variables - makes code easier to read
	$vn_Volgnr		=	$o_tab_parser->getRowValue(1);
	$vn_Kunstvoorwerp_id	=	$o_tab_parser->getRowValue(2);
	$vn_Term_id		=	$o_tab_parser->getRowValue(3);
	$vs_Waarde		=	$o_tab_parser->getRowValue(4);
	$vs_Opmerking		=	$o_tab_parser->getRowValue(5);
	
print "creating materiaal voor kunstvoorwerp ".$vn_Kunstvoorwerp_id.".".$vn_Term_id."  \n";

// ------------------------------------
// Waarden omvormen naar gepaste output
// ------------------------------------

$va_Afmetingen = $va_Types_Afmetingen[$vn_Term_id];
// De vier waarden in de array
$vs_Term_Identificatie = $va_Afmetingen[0];
//$vs_Term_Omschrijving = $va_Afmetingen[1];
$vs_Term_Eenheid = $va_Afmetingen[2];
//$vs_Term_poid = $va_Afmetingen[3];

print "{$vn_Term_id} / {$vs_Term_Identificatie} / {$vs_Waarde} / {$vs_Term_Eenheid} \n ";

if (!trim($vs_Waarde) == '') {
		
	switch ($vn_Term_id) {
	case 1: // hoogte
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'afmeting');
		$vs_Height = $vs_Waarde." ".$vs_Term_Eenheid;
		break;
	case 2: // Breedte
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'afmeting');
		$vs_Width = $vs_Waarde." ".$vs_Term_Eenheid;
		break;
	case 3: // Diepte
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'afmeting');
		$vs_Depth = $vs_Waarde." ".$vs_Term_Eenheid;
		break;
	case 4: // diameter
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'afmeting');
		$vs_Diameter = $vs_Waarde." ".$vs_Term_Eenheid;
		break;
	case 5: // gewicht
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'afmeting');
		$vs_Gewicht = $vs_Waarde." ".$vs_Term_Eenheid;
		break;
	case 6: // breedte (met lijst)
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'afmetingMetLijst');
		$vs_Width = $vs_Waarde." ".$vs_Term_Eenheid;
		break;
	case 7: // hoogte (met lijst)
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'afmetingMetLijst');
		$vs_Height = $vs_Waarde." ".$vs_Term_Eenheid;
		break;
	case 8: // hoogte structuur
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'afmetingStructuur');
		$vs_Height = $vs_Waarde." ".$vs_Term_Eenheid;
		break;
	case 9: // diameter cuppa
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'afmetingCuppa');
		$vs_Diameter = $vs_Waarde." ".$vs_Term_Eenheid;
		break;
	case 10: // hoogte zonder deksel
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'afmetingZonderDeksel');
		$vs_Height = $vs_Waarde." ".$vs_Term_Eenheid;
		break;
	case 11: // diameter voet
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'afmetingVoet');
		$vs_Diameter = $vs_Waarde." ".$vs_Term_Eenheid;
		break;
	case 12: // diameter basis
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'afmetingBasis');
		$vs_Diameter = $vs_Waarde." ".$vs_Term_Eenheid;
		break;
	case 13: // diameter deksel
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'afmetingMetDeksel');
		$vs_Diameter = $vs_Waarde." ".$vs_Term_Eenheid;
		break;
	case 14: // diepte (met lijst)
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'afmetingMetLijst');
		$vs_Depth = $vs_Waarde." ".$vs_Term_Eenheid;
		break;
	case 15: // lengte
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'afmeting');
		$vs_Length = $vs_Waarde." ".$vs_Term_Eenheid;
		break;
	case 16: // diameter bovenzijde
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'afmetingBovenzijde');
		$vs_Diameter = $vs_Waarde." ".$vs_Term_Eenheid;
		break;
	case 18: // gehalte zilver
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'blank');
		$vs_ZilverGehalte = $vs_Waarde." ".$vs_Term_Eenheid;
		break;
	case 20: // totale hoogte
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'blank');
		$vs_Height = $vs_Waarde." ".$vs_Term_Eenheid;
		break;
	case 21: // dikte
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'afmeting');
		break;
	case 22: // diameter bovenaan
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'afmetingBovenzijde');
		$vs_Diameter = $vs_Waarde." ".$vs_Term_Eenheid;
		break;
	case 23: // totale lengte
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'blank');
		$vs_Length = $vs_Waarde." ".$vs_Term_Eenheid; 
		break;
	case 24: // diameter pateen
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'blank');
		$vs_Diameter = $vs_Waarde." ".$vs_Term_Eenheid;
		break;
	case 25: // breedte voetstuk
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'afmetingVoet');
		$vs_Width = $vs_Waarde." ".$vs_Term_Eenheid; 
		break;
	case 26: // diameter bak
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'blank');
		$vs_Diameter = $vs_Waarde." ".$vs_Term_Eenheid;
		break;
	case 27: // diameter onderaan
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'afmetingOnderzijde');
		$vs_Diameter = $vs_Waarde." ".$vs_Term_Eenheid;
		break;
	case 28: // totale breedte
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'blank');
		$vs_Width = $vs_Waarde." ".$vs_Term_Eenheid; 
		break;
	case 29: // hoogte voetstuk
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'afmetingVoet');
		$vs_Height = $vs_Waarde." ".$vs_Term_Eenheid;
		break;
	case 30: // hoogte voet
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'afmetingVoet');
		$vs_Height = $vs_Waarde." ".$vs_Term_Eenheid;
		break;
	case 31: // hoogte schaal
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'blank');
		$vs_Height = $vs_Waarde." ".$vs_Term_Eenheid;
		break;
	case 32: // breedte basis
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'afmeting');
		$vs_Width = $vs_Waarde." ".$vs_Term_Eenheid; 
		break;
	case 33: // hoogte cuppa
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'afmetingCuppa');
		$vs_Diameter = $vs_Waarde." ".$vs_Term_Eenheid;
		break;
	case 34: // lengte voetstuk
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'afmetingVoet');
		$vs_Lengte = $vs_Waarde." ".$vs_Term_Eenheid; 
		break;
	case 35: // lengte basis
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'afmeting');
		$vs_Length = $vs_Waarde." ".$vs_Term_Eenheid; 
		break;
	case 36: // diameter beker
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'blank');
		$vs_Diameter = $vs_Waarde." ".$vs_Term_Eenheid;
		break;
	case 37: // breedte rapport
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'afmetingRapport');
		$vs_Width = $vs_Waarde." ".$vs_Term_Eenheid;
		break;
	default:
		$vn_Type = $t_list->getItemIDFromList('dimension_types', 'blank'); 
		break;
	}
	
	$vs_Text = $vs_Term_Identificatie.": ".$vs_Waarde." ".$vs_Term_Eenheid;
	print "Text : {$vs_Text} \n";	
	
	if (!trim($vs_Opmerking) == '') {
		$vs_Opmerking = $vs_Text." - ".$vs_Opmerking;	
	} else {
		$vs_Opmerking =  $vs_Text;
	}
	print "Opmerking: {$vs_Opmerking} \n";
	
	if (!is_numeric($vs_Waarde)) {
		unset($vs_Width);
		unset($vs_Length);
		unset($vs_Depth);
		unset($vs_Diameter);
		unset($vs_ZilverGehalte);
		unset($vs_Height);
		unset($vs_Gewicht);
	}
	
	
//----------------		
// Create object
//----------------

//	$t_object->setMode(ACCESS_WRITE);
               
	$va_Kunstvoorwerp_keys = ($t_object->getObjectIDsByidnoPart(("%KV_".$vn_Kunstvoorwerp_id.")"),null) );
	
	$dimensions = sizeof($va_Kunstvoorwerp_keys[0]);
		
	print "{$dimensions}\n";
		
	if ($dimensions > 0) {
	
		$vn_Kunstvoorwerp_key = $va_Kunstvoorwerp_keys[0];
	
		print "Primary Kunstvoorwerp-key: ".$vn_Kunstvoorwerp_key." \n";
/*	
		$t_object->load($vn_Kunstvoorwerp_key);
	
		if ($t_object->numErrors()) {
			print "\tERROR LOADING OBJECT {$vn_Kunstvoorwerp_id}: ".join('; ', $t_object->getErrors())."\n";
			continue;
		}

		$t_object->getPrimaryKey();
*/	
//-------------------------
// Waarden mappen      
//-------------------------

		$t_attrib->addAttribute(57, $vn_Kunstvoorwerp_key, 'work_dimensions', array(
			'locale_id'		=>	$pn_locale_id,
			'dimensions_height'	=>	$vs_Height,
			'dimensions_width'	=>	$vs_Width,
			'dimensions_depth'	=>	$vs_Depth,
			'dimensions_diameter'	=>	$vs_Diameter,
			'dimensions_lengte'	=>	$vs_Length,
			'dimensions_gewicht'	=>	$vs_Gewicht,
			'dimensions_zilverGehalte'	=>	$vs_ZilverGehalte,
			'dimensions_type'	=>	$vn_Type,
			'dimensions_Opmerking'	=>	$vs_Opmerking
		));

//		$t_object->update();

		if ($t_attrib->numErrors()) {
			print "\tERROR UPDATING OBJECT {$vn_Kunstvoorwerp_key}: ".join('; ', $t_attrib->getErrors())."\n";
			continue;
		} else {
			print "update succesvol\n";
		}
	
	
	} else {
		print "Kunstvoorwerp bestaat (nog) NIET \n";
	}

} else {
	
	print " blanco waarden  -  skipping record \n";
	
}

print "==={$vn_c}==========volgend record============== \n\n";

unset($vs_Width);
unset($vs_Length);
unset($vs_Depth);
unset($vs_Diameter);
unset($vs_ZilverGehalte);
unset($vs_Height);
unset($vs_Gewicht);
	
$vn_c++;

}

print "END IMPORTING kunstvoorwerp_has_typesvanafmetingen \n";

?>
