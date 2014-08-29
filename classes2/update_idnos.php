<?php
/**
 * Created by PhpStorm.
 * User: AnitaR
 * Date: 25/04/14
 * Time: 11:16
 */

define("__PROG__", "idnos_prod_xx");

include('header.php');

$log = new Klogger(__LOG_DIR__,KLogger::DEBUG);
//$AUTH_CURRENT_USER_ID = 'administrator';

require_once('GuzzleRest.php');

$t_guzzle = new GuzzleRest(__INI_FILE__);

$query = "ca_objects.idno:'*KV_*'";
$result = $t_guzzle->findObject($query, 'ca_objects');

#$log->logInfo('de objecten', $result);
$teller = 0;

foreach($result['results'] as $object) {

    $teller++;
    $update = array();
    $object_id = $object['object_id'];

    $idno = $object['idno'];
    echo $teller." | ".$idno."\n\r";

    $a_idno = explode(' - ', $idno);

    $new_idno = $a_idno[0];

    $old_idno = str_replace(array('(KV_', ')'), array('', ''), $a_idno[1]);

    $update['intrinsic_fields'] =
        array(
            'idno'  => $new_idno
        );

    $update['attributes'] ['kunstvoorwerpId'] [] =
        array(
            'locale'    =>  'nl_NL',
            'kunstvoorwerpId'   =>  $old_idno
        );

    #$log->logInfo('de update-gegevens', $update);

    $data = $t_guzzle->updateObject($update, $object_id, 'ca_objects');

    #$log->logInfo('het eindresultaat', $data);

    if (isset($data['ok']) && ($data['ok'] != 1)) {

        echo "ERROR ERROR \n";
        $log->logError("ERROR ERROR : Er is iets misgelopen!!!!!", $data);

    }

    # Digitool
    $update_new = array();
    $temp = array();

    $data_new = $t_guzzle->getFullObject($object_id, 'ca_objects');

    if (isset($data_new['attributes']['digitoolUrl'])) {

        $digitoolUrl = $data_new['attributes']['digitoolUrl'];

        #$log->logInfo('het benodigde deel ?', $digitoolUrl);

        foreach($digitoolUrl as $key => $value) {

            if(strpos($value['digitoolUrl'], '_') > 0) {

                $value['digitoolUrl'] = substr($value['digitoolUrl'], 0, strpos($value['digitoolUrl'], '_'));

            }

            $temp['digitoolUrl'][] = $value;

        }

        $update_new = array(
            "remove_attributes" => array("digitoolUrl"),
            "attributes" => ($temp)
        );

        $data2 = $t_guzzle->updateObject($update_new, $object_id, 'ca_objects');

        $log->logInfo('het eindresultaat',$data2);

        if (isset($data2['ok']) && ($data2['ok'] != 1)) {

            echo "ERROR ERROR \n";
            $log->logError("ERROR ERROR : Er is iets misgelopen!!!!!", $data);

        }

    }

}

$log->logInfo('EINDE');