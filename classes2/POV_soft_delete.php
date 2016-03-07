<?php
/**
 * Created by PhpStorm.
 * User: AnitaR
 * Date: 25/04/14
 * Time: 11:16
 */


define("__PROG__", "pa_delete");

include('header.php');

#$log = new Klogger(__LOG_DIR__,KLogger::DEBUG);
//$AUTH_CURRENT_USER_ID = 'administrator';

require_once('GuzzleRestCookie.php');

$t_guzzle = new GuzzleRestCookie(__INI_FILE__);
$teller = 0;

$query = "ca_objects.idno:'PA*'";
$result = $t_guzzle->findObject($query, 'ca_objects');

if (isset($result['ok']) && sizeof($result) === 1) {

    echo "ERROR ERROR \n";
    #$log->logError("ERROR ERROR : Er is iets misgelopen!!!!!", $data);

} else {

    foreach($result['results'] as $object) {

        $teller++;
        $object_id = $object['object_id'];

        $idno = $object['idno'];
        echo $teller." | ".$object_id . " | ".$idno."\n\r";

        $data = $t_guzzle->softDeleteObject($object_id, 'ca_objects');

        #$log->logInfo('het eindresultaat', $data);

        if (isset($data['ok']) && ($data['ok'] != 1)) {

            echo "ERROR ERROR \n";
            $log->logError("ERROR ERROR : Er is iets misgelopen!!!!!", $data);

        } else {

            echo " | DONE \n";

        }

        if ($teller == 2000) {

            exit;
        }
    }

}

echo 'EINDE';