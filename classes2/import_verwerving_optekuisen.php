<?php
/*
 *  Doel: de vergeten informatie linken aan de databank mbv REST API
 *
 *
 */
define("__PROG__", "final_verwerving_update");

include('header.php');

$log = new Klogger(__LOG_DIR__,KLogger::DEBUG);

require_once('GuzzleRestCookie.php');

$t_guzzle = new GuzzleRestCookie(__INI_FILE__);

#require_once(__CA_MODELS_DIR__ . "/ca_attributes.php");
#require_once('../Classes/ca_objects_bis.php');
require_once(__CA_LIB_DIR__ . '/core/Parsers/DelimitedDataParser.php');

// want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parser = new DelimitedDataParser("\t");

# $t_object = new ca_objects_bis();

print "IMPORTING kunstvoorwerpen \n";

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

    if ((trim($vs_Verwerving) !== '') && (trim($vs_Verwerving) !== '0') && (substr(trim($vs_Verwerving),0,3) !== 'POV') ) {

        # opzoeken record in de databank

        $query = "ca_objects.idno:'" . $vs_crkcObjectnr . "'";
        $result_x = $t_guzzle->findObject($query, 'ca_objects');
        $dimensions = sizeof($result_x['results']);

        echo '=====' . $vn_c . '===== PROCESSING ' . $vn_Kunstvoorwerp_id . "/" . $vs_crkcObjectnr . "\n";
        echo'string to process: '. $vs_Verwerving ."\n";

        $log->logInfo('aantal gevonden records: ', $dimensions);

        if ($dimensions === 1) {

            $object_id = $result_x['results'][0]['object_id'];
            $data_new = $t_guzzle->getFullObject($object_id, 'ca_objects');

            //$places = $json_array['related']['ca_places'];
            $result = $data_new['attributes']['statuutStatusEigenaarInfo'];

            if (is_null($result)) {
                /*
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
                                        'statusEigenaarDatum' => NULL,
                                        'statuutEigenaarOpmerking' => $vs_Verwerving
                                    )
                                )
                            )
                        );
                *
                 *
                 */

            } elseif (count($result) === 1) {
                /*
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
                *
                 *
                 */

            } elseif (sizeof($result) === 2) {
                # dezelfde eigenaar - opmerking toevoegen aan Nee-container
                if ($result[0]['eigenaarInstelling'] === $result[1]['eigenaarInstelling']) {
                    if (($result[0]['huidigeStatusEigenaar'] === '160' && $result[1]['huidigeStatusEigenaar'] === '161') ||
                        ($result[1]['huidigeStatusEigenaar'] === '160' && $result[0]['huidigeStatusEigenaar'] === '161')) {
                        if ($result[0]['huidigeStatusEigenaar'] === '160') {
                            if (trim($result[0]['statuutEigenaarOpmerking']) !== trim($vs_Verwerving)) {
                                $result[0]['statuutEigenaarOpmerking'] = trim($result[0]['statuutEigenaarOpmerking']. "\n" . $vs_Verwerving);
                                $update =
                                    array('remove_attributes' => array('statuutStatusEigenaarInfo'),
                                        'attributes' => array('statuutStatusEigenaarInfo' => $result)
                                    );
                            } else {
                                echo "dezelfde info \n";
                            }
                        } elseif ($result[1]['huidigeStatusEigenaar'] === '160') {
                            if (trim($result[1]['statuutEigenaarOpmerking']) !== trim($vs_Verwerving)) {
                                $result[1]['statuutEigenaarOpmerking'] = trim($result[1]['statuutEigenaarOpmerking']. "\n" . $vs_Verwerving);
                                $update =
                                    array('remove_attributes' => array('statuutStatusEigenaarInfo'),
                                        'attributes' => array('statuutStatusEigenaarInfo' => $result)
                                    );
                            } else {
                                echo "dezelfde info";
                            }
                        }
                    } else {
                        echo ('manueel') ;
                        # loggen
                    }
                }
                # verschillende eigenaars => opmerking toevoegen aan Ja-containor
                elseif ($result[0]['eigenaarInstelling'] !== $result[1]['eigenaarInstelling']) {
                    if (($result[0]['huidigeStatusEigenaar'] === '160' && $result[1]['huidigeStatusEigenaar'] === '161') ||
                        ($result[1]['huidigeStatusEigenaar'] === '160' && $result[0]['huidigeStatusEigenaar'] === '161')) {
                        if ($result[0]['huidigeStatusEigenaar'] === '161') {
                            if (trim($result[0]['statuutEigenaarOpmerking']) !== trim($vs_Verwerving)) {
                                $result[0]['statuutEigenaarOpmerking'] = trim($result[0]['statuutEigenaarOpmerking']. "\n" . $vs_Verwerving);
                                $update =
                                    array('remove_attributes' => array('statuutStatusEigenaarInfo'),
                                        'attributes' => array('statuutStatusEigenaarInfo' => $result)
                                    );
                            } else {
                                echo "dezelfde info";
                            }
                        } elseif ($result[1]['huidigeStatusEigenaar'] === '161') {
                            if (trim($result[1]['statuutEigenaarOpmerking']) !== trim($vs_Verwerving)) {
                                $result[1]['statuutEigenaarOpmerking'] = trim($result[1]['statuutEigenaarOpmerking']. "\n" . $vs_Verwerving);
                                $update =
                                    array('remove_attributes' => array('statuutStatusEigenaarInfo'),
                                        'attributes' => array('statuutStatusEigenaarInfo' => $result)
                                    );
                            } else {
                                echo "dezelfde info";
                            }
                        }
                    } else {
                        echo 'manueel';
                        # loggen
                    }

                } else {
                    echo 'manueel';
                    # geen pasklare oplossing -> loggen
                }

            } else {
                #in principe zouden we hier nooit mogen komen
                $log->logError('hier zouden we nooit mogen komen');
                $exception++;
            }

            if (isset($update) && $update !== '' ){

                $data = $t_guzzle->updateObject($update, $object_id, 'ca_objects');

                if (isset($data['ok']) && ($data['ok'] != 1)) {

                    echo "ERROR ERROR ".$data['errors']. "\n";
                    $log->logError("ERROR ERROR : Er is iets misgelopen!!!!!", $data);

                }
                /*
                $string = json_encode($update);

                if(isJson($string)) {
                    $url2 = $uri ."/". $base ."/service.php/item/ca_objects/id/".$object_id;
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
                *
                 *
                 */

            }
            unset($update);
            unset($string);
            unset($result);

        } else {

            $log->logInfo("ERROR: object ".$vn_Kunstvoorwerp_id." / ".$vs_crkcObjectnr." niet gevonden");
            $error++;
        }

        $log->logInfo("=================Einde verwerking kunstwerk " . $vs_crkcObjectnr . " =============================");
        $geldig++;

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