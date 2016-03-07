<?php
/*
 *  Doel: de vergeten informatie linken aan de databank mbv REST API
 *
 *
 */
define("__PROG__", "verwerving_stats");

include('header.php');

$log = new Klogger(__LOG_DIR__,KLogger::DEBUG);

if (!$tools = parse_ini_file("../data/toolsOrig.ini")) {
    die("Couldn't parse ini-file\n");
}

$userid = $tools['userid'];
$paswoord = $tools['paswoord'];
$host = $tools['host'];
$base = $tools['base'];
$uri = 'http://'.$userid.':'.$paswoord.'@'.$host ;

$this->uri = 'http://'.$this->userid.':'.$this->paswoord.'@'.$this->host ;

require_once(__CA_MODELS_DIR__ . "/ca_attributes.php");
require_once('../Classes/ca_objects_bis.php');
require_once(__CA_LIB_DIR__ . '/core/Parsers/DelimitedDataParser.php');

// want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t");

$t_object = new ca_objects_bis();

print "IMPORTING kunstvoorwerpen \n";
echo __MY_DATA__;

if (!$o_tab_parser->parse(__MY_DATA__ . "/old/org_kunstvoorwerp.csv")) {
    die("Couldn't parse Kunstvoorwerpen data\n");
}

$vn_c = 1;
$geldig = 1;
$bestaatniet = 0;
$een = 0;
$een_met_opm = 0;
$een_zonder_opm = 0;
$meer = 0;
$exception = 0;
$error = 0;

$o_tab_parser->nextRow(); // skip first row

while ($o_tab_parser->nextRow()) {
    # Get columns from tab file and put them into named variables - makes code easier to read
    $vn_Kunstvoorwerp_id        = $o_tab_parser->getRowValue(1);
    $vs_crkcObjectnr            = $o_tab_parser->getRowValue(9);
    # $vn_EigenaarInstelling_id   =	$o_tab_parser->getRowvalue(31);
    # $vn_EigenaarPersoon_id      =	$o_tab_parser->getRowvalue(32);
    $vs_Verwerving              = $o_tab_parser->getRowvalue(33);

    if ((trim($vs_Verwerving) !== '') && (trim($vs_Verwerving) !== '0')) {

        # opzoeken record in de databank

        $va_Kunstvoorwerp_keys = ($t_object->getObjectIDsByidnoPart(("%KV_" . $vn_Kunstvoorwerp_id . ")"), null) );
        $dimensions = sizeof($va_Kunstvoorwerp_keys);

        $log->logInfo('=====' . $vn_c . '===== PROCESSING ' . $vn_Kunstvoorwerp_id );
        $log->logInfo('string to process: '. $vs_Verwerving);

        $log->logInfo('aantal gevonden records: ', $dimensions);

        if ($dimensions > 0) {

            if ($dimensions > 1) {

                $log->logError('meerdere objecten gevonden -> STOP: zou niet mogen !!!!!');
            }

            $vn_Kunstvoorwerp_key = $va_Kunstvoorwerp_keys[0];
            $log->logInfo("Primary Kunstvoorwerp-key: ", $vn_Kunstvoorwerp_key);

            $t_object->load($vn_Kunstvoorwerp_key);

            #opvragen record met REST API - antwoord is json-formaat

            $url = $uri ."/". $base ."/service.php/item/ca_objects/id/".$vn_Kunstvoorwerp_key."?pretty=1&format=edit";

            $unparsed_json = file_get_contents($url);

            $json_array = json_decode($unparsed_json, TRUE);

            $places = $json_array['related']['ca_places'];
            $result = $json_array['attributes']['statuutStatusEigenaarInfo'];
            $log->logInfo('het benodigde deel ?', $result);

            if (is_null($result)) {
                $log->logInfo('attribute statuutStatusEigenaarInfo bestaat niet');
                $bestaatniet++;
                $update = array(
                    "remove_attributes" => array("statuutStatusEigenaarInfo"),
                    "attributes" =>
                        array('statuutStatusEigenaarInfo' =>
                            array (
                                0 =>
                                    array (
                                        'locale' => '1',
                                        'eigenaarInstelling' => '',
                                        'statuutStatusEigenaar' => '168',
                                        'huidigeStatusEigenaar' => '160',
                                        'statusEigenaarDatum' => '',
                                        'statuutEigenaarOpmerking' => $vs_Verwerving
                                    )
                                )
                            )
                        );

            } elseif (count($result) === 1) {
                $log->logInfo('één attribuut statuutStatusEigenaarInfo bestaat al');
                $een++;
                if (isset($result[0]['statuutEigenaarOpmerking'])) {
                    $een_met_opm++;
                    $log->logInfo('En er is een opmerking');
                    #Voegen opmerking toe aan $result
                    if (trim($result[0]['statuutEigenaarOpmerking']) === trim($vs_Verwerving)) {
                        $log->logInfo('En er is een opmerking en ze is dezelfde -> STOP: niks doen');
                        $result[0]['statusEigenaarDatum'] = NULL;
                    } else {
                        $result[0]['statuutEigenaarOpmerking'] = $result[0]['statuutEigenaarOpmerking']. "\n" . $vs_Verwerving;
                        $log->loginfo('En er is een ANDERE opmerking - voegen onze toe', $result);
                    }
                } else {
                    $een_zonder_opm++;
                    $log->logInfo('En er is noch geen opmerking -> voegen onze toe');
                    $result[0]['statuutEigenaarOpmerking'] = $vs_Verwerving;
                }
                $update =
                    array('remove_attributes' => array('statuutStatusEigenaarInfo'),
                        'attributes' => array('statuutStatusEigenaarInfo' => $result)
                    );

            } elseif (sizeof($result) > 1) {
                $meer++;
                $log->logAlert('meerdere containors beschikbaar: -> STOP: Verwerving manueel toevoegen');

            } else {
                #in principe zouden we hier nooit mogen komen
                $log->logError('hier zouden we nooit mogen komen');
                $exception++;
            }
            /*
            if (isset($update) && $update !== '' ){
                $string = json_encode($update);

                if(isJson($string)) {
                    $url2 = $uri ."/". $base ."/service.php/item/ca_objects/id/".$vn_Kunstvoorwerp_key;
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
                    curl_setopt($ch, CURLOPT_URL, $url2);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
                    curl_setopt($ch, CURLOPT_POST, 1);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, ($string));
                    $result2 = curl_exec($ch);
                    #en we vragen record terug op, ter controle:
                    $log->logInfo('het eindresultaat',$result2);
                    curl_close($ch);
                }

            }
             *
             */
            unset($update);
            unset($string);
            unset($result);

        } else {

            $log->logInfo("ERROR: object ".$vn_Kunstvoorwerp_id." / ".$vs_crkcObjectnr." niet gevonden");
            $error++;
        }

        $log->logInfo("=================Einde verwerking kunstwerk " . $vs_crkcObjectnr . " =============================");
        $geldig++;
        /*
        if ($geldig >= 500) {
            print "DONE 50+";
            $log->logInfo("==============================================");
            $log->logInfo('Geen container aanwezig: ', $bestaatniet);
            $log->logInfo('één container aanwezig: ', $een);
            $log->logInfo('met opm-attribuut: ', $een_met_opm);
            $log->logInfo('zonder opm-attribuut: ', $een_zonder_opm);
            $log->logInfo('Meerdere containers aanwezig: ', $meer);
            $log->logInfo('Uitzonderingen: ', $exception);
            $log->logInfo('Errors: ', $error);
            $log->logInfo("==============================================");
            exit;
        }
        *
         *
         */
    }

    $vn_c++;
}

$log->logInfo('Einde verwerking');
$log->logInfo("==============================================");
$log->logInfo('Geen container aanwezig: ', $bestaatniet);
$log->logInfo('één container aanwezig: ', $een);
$log->logInfo('met opm-attribuut: ', $een_met_opm);
$log->logInfo('zonder opm-attribuut: ', $een_zonder_opm);
$log->logInfo('Meerdere containers aanwezig: ', $meer);
$log->logInfo('Uitzonderingen: ', $exception);
$log->logInfo('Errors: ', $error);
$log->logInfo("==============================================");
print "Einde";


function isJson($string) {
    return ((is_string($string) &&
        (is_object(json_decode($string)) ||
            is_array(json_decode($string))))) ? true : false;
}

/**
 * Checks whether a string is valid json.
 *
 * @param string $string
 * @return boolean
 */
function json_is($string)
{
    try
    {
        // try to decode string
        json_decode($string);
    }
    catch (ErrorException $e)
    {
        // exception has been caught which means argument wasn't a string and thus is definitely no json.
        return FALSE;
    }

    // check if error occured
    return (json_last_error() == JSON_ERROR_NONE);
}
?>
