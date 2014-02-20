<pre>
<?php

// wat hebben we voorhanden om attributen te wijzigen

echo 'We beginnen eraan';
set_time_limit(0);

define("__PROG__","test");
//echo __PROG__;

define("__MY_DIR__", $_SERVER['DOCUMENT_ROOT']);
include(__MY_DIR__."/crkc_tools/Classes/header.php");
//echo '__MY_DATA__';

require_once(__CA_LIB_DIR__.'/core/Db.php');
require_once(__CA_MODELS_DIR__.'/ca_locales.php');
//require_once(__CA_MODELS_DIR__.'/ca_attributes.php');
//require_once(__CA_MODELS_DIR__.'/ca_attribute_values.php');

$t_func = new MyFunctions_new();
//$pn_locale_id = $t_func->idLocale("nl_NL");
$log = $t_func->setLogging();

//$t_attrib = new ca_attributes();
//$t_attrib->setFieldAttribute($ps_field, $ps_attribute, $ps_value);
//$t_values = new ca_attribute_values();

//inlezen (in array) mapping-bestand
//$mappingarray = $t_func->ReadMappingcsv("move_mapping.csv");
$resultarray = array();
//++++++++++++++++++
// STAP 1
// lijst (array) maken van voorkomende waarden (waarde is de key in de array - > gevolg: geen dubbels)
//++++++++++++++++++
$reader = new XMLReader();

$reader->open(__MY_DIR__."/cag_tools/data/AmoveThes 2012-09.xml");

while ($reader->read() && $reader->name !== 'record');
//==============================================================================begin van de loop
while ($reader->name === 'record' )
{
    $xmlarray = $t_func->ReadXMLnode($reader);

    //$resultarray = $t_func->XMLArraytoResultArray($xmlarray,$mappingarray);
    
    foreach ($xmlarray as $key => $child) {
        
        if (($key) === 'term') {
            $term = $child;
        } 
        if ($key === 'term.type') {
            if (!is_array($child)) {
                $term_type[] = $child;
            } else {
                $term_type = $child;
            }
        }
    }
    print ($term);
    print_r($term_type);
    
    foreach ($term_type as $waarde) {
        if ($waarde ==='objectnaam') {
            $resultarray['objectnaam'][] = $term;
        } elseif ($waarde === 'materiaal') {
            $resultarray['materiaal'][] = $term;
        } elseif ($waarde === 'techniek') {
            $resultarray['techniek'][] = $term;
        } elseif ($waarde === 'gidsterm') {
            $resultarray['gidsterm'][] = $term;
        }
    }

    //print_r ($xmlarray);
    
    //print_r($resultarray);
    
    unset($term);
    unset($term_type);
    
    $reader->next('record');
}
$reader->close();

//$log->logInfo('Inhoud collectie-array', $resultarray);
print_r($resultarray);

?>
</pre>