<?php
/*
 * Step 1: Initialisation
 */
require_once("/www/libis/html/ca_crkc/setup.php");

require_once(__CA_LIB_DIR__.'/core/Db.php');
require_once(__CA_MODELS_DIR__.'/ca_locales.php');
//require_once(__CA_MODELS_DIR__.'/ca_attributes.php');
require_once(__CA_MODELS_DIR__.'/ca_objects.php');
require_once(__CA_LIB_DIR__.'/core/Parsers/DelimitedDataParser.php');

$_ = new Zend_Translate('gettext', __CA_APP_DIR__.'/locale/en_US/messages.mo', 'nl_NL');

$t_locale = new ca_locales();
$pn_locale_id = $t_locale->loadLocaleByCode('nl_NL');

$t_list = new ca_lists();

//$qr_attrib_ids = new ca_attributes();
//$qr_attrib_ids->setMode(ACCESS_WRITE);

$t_object = new ca_objects();
$t_object->setMode(ACCESS_WRITE);

$o_db = new Db();
$o_config = Configuration::load();

$vn_c = 1;
print "{$vn_c}\n";
$success = 0;

//$qr1 = "SELECT attribute_id, value_longtext1 FROM ca_attribute_values WHERE (element_id = 124 and value_longtext1 is not null) and attribute_id < 1974065";

$qr1 = "SELECT attribute_id, value_longtext1 FROM ca_attribute_values WHERE (element_id = 124 and value_longtext1 is not null) and attribute_id <= 1882957 ";

$qr_attr_ids = $o_db->query($qr1);

$dimension = rowcount($qr_attr_ids);

print "aantal te behandelen records {$dimension} \n";

while($qr_attr_ids->nextRow()) 
	{
		$attr_id = $qr_attr_ids->get('attribute_id');
		
		$datum_old = $qr_attr_ids->get('value_longtext1');
		
		$datum_new = $datum_old / 1;
		
		print "editing attribute {$attr_id} / {$datum_old} / {$datum_new} \n";
		
		if (is_date($datum_new)) {
	
			$qr3 = "SELECT attribute_id, value_longtext1 FROM ca_attribute_values WHERE element_id = 122 and attribute_id = $attr_id ";
			
			$qr_attr2_ids = $o_db->query($qr3);
			
			$dim = rowcount($qr_attr2_ids);
			
			print "is er een 122? {$dim} \n";
			
			if ($dim == 0 ) { 
			
				$qr2 = "select row_id from ca_attributes where attribute_id = $attr_id";
				
				$qr_object_ids = $o_db->query($qr2);
				$qr_object_ids->nextRow();
				
				$object_id = $qr_object_ids->get('row_id');
				
				print "editing object {$object_id}\n";
				
				$t_object->load($object_id);
				
				$t_object->getPrimaryKey();
				
				$t_object->replaceAttribute(array(
					'locale_id'		=>	$pn_locale_id,
					'objectDatum'		=>	$datum_new,
					'objectDatumOpmerking'	=>	""
				), 'objectDatumInfo', null);
				
				$t_object->update();
			
				if ($t_object->numErrors()) {
					print "\tERROR UPDATING OBJECT {$object_id}: ".join('; ', $t_object->getErrors())."\n";
					$vn_c++;
					
					print "=== record {$vn_c} # {$dimension} === successen: {$success} \n";
					
					continue;
				} else {
					$success++;
					print "update succesvol\n";
				}
				
			} else {
				
				print "veld 122 bestaat reeds - ga verder\n" ;
				
			}
			
		} else {
			
			print "inhoud 124-veld geen geldige datum - niks te doen \n";
			
		}
			
	print "=== record {$vn_c} # {$dimension} === successen: {$success} \n";			
		
	$vn_c++;	
	
	}
	
	
	
function is_date($date) 
    { 
        $date = str_replace(array('\'', '-', '.', ','), '/', $date); 
        $date = explode('/', $date); 
                                                               
        if(    count($date) == 1 // No tokens 
            and    is_numeric($date[0]) 
            and    $date[0] < 20991231 and 
            (    checkdate(substr($date[0], 4, 2) 
                        , substr($date[0], 6, 2) 
                        , substr($date[0], 0, 4))) 
        ) 
        { 
            return true; 
        } 
        
        if(    count($date) == 3 
            and    is_numeric($date[0]) 
            and    is_numeric($date[1]) 
            and is_numeric($date[2]) and 
            (    checkdate($date[0], $date[1], $date[2]) //mmddyyyy 
            or    checkdate($date[1], $date[0], $date[2]) //ddmmyyyy 
            or    checkdate($date[1], $date[2], $date[0])) //yyyymmdd 
        ) 
        { 
            return true; 
        } 
        
        return false; 
    } 

?>
