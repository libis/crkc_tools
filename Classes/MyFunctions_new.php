<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of MyFunctions_new
 *
 * @author AnitaR
 */
class MyFunctions_new {
    //put your code here
    
    function setLogging() {
//***
        $logDir = __MY_DIR__."/crkc_tools-staging/shared/log/";
        $log = new KLogger($logDir, KLogger::DEBUG);

        return $log;
    }

    function ReadMappingcsv($bestand) {
//***
        $file = __MY_DIR__."/crkc_tools/mapping/".$bestand;
        $data = array();
        if (($fh = fopen($file, "r")) !== FALSE) {
            $i = 0;
            while (($lineArray = fgetcsv($fh, 200, ';')) !== FALSE) {
                for ($j=0; $j<count($lineArray); $j++) {
                    $data[$i][$j] = $lineArray[$j];
                }
                $i++;
            }
            fclose($fh);
        }
        return $data;
    }

    // inlezen XML-node naar array
    function ReadXMLnode($reader) {
//***
            $dom = new DOMDocument;
            $node = simplexml_import_dom($dom->importNode($reader->expand(), true));
            $json = json_encode($node);
            $xmlarray = json_decode($json, TRUE);
            return $xmlarray;
    }

    //De XMLArray converteren naar Array met enkel benodigde gegevens
    function XMLArraytoResultArray($xmlarray,$mappingarray){
//***
        //maken array van de te weerhouden tags (op basis van Mapping-bestand
        $hooiberg = array();
        for ($j = 1; $j <= count($mappingarray) - 1 ; $j++) {
                $hooiberg[] = $mappingarray[$j]['0'];
        }
        //print_r ($hooiberg);exit;

        //herwerken gegevens in $xmlarray tot $resultarray
        //met daarin enkel de benodigde gegevens (zie $hooiberg)
        //(->reeds gemapt naar juiste CA metadata-element)
        $resultarray = array();

        foreach ($xmlarray as $key => $value) {
            if (in_array($key, $hooiberg))
            {
                for ($j = 1; $j<= count($mappingarray) - 1 ; $j++) {
                    if (($mappingarray[$j][0]) == $key)
                    {
                        $new_key = $mappingarray[$j][1];
                    }
                }
                $resultarray[$new_key] = $value;
            }
        }

        return $resultarray;
    }
    
    function Herhalen($resultarray, $fields) {
        $maximum = 0;
        foreach($fields as $value)
        {
            $aantal = count($resultarray[$value]);
            if ($aantal > $maximum)
            {
                $maximum = $aantal;
            }
        }
        return $maximum;
    }

    function makeArray(&$resultarray, $fields) {
        foreach($fields as $value)
        {
            if ( (isset($resultarray[$value])) && (!is_array($resultarray[$value])) )
            {
                $waarde = $resultarray[$value];
                $resultarray[$value] = array($waarde);
            }
        }
    }
    
}

?>
