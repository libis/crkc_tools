<?php
/**
 * Created by PhpStorm.
 * User: AnitaR
 * Date: 25/04/14
 * Time: 11:16
 */

# define("__PROG__", "comparison");

include('header_sandbox.php');

# $log = new Klogger(__LOG_DIR__,KLogger::DEBUG);

require_once('GuzzleRestCookie.php');

$t_guzzle = new GuzzleRestCookie(__INI_FILE__);

$part = array('CRKC', 'POV', 'PA', 'PWV', 'ZM_');

foreach($part as $value) {

# REST API-call

    $query = "ca_objects.idno:". $value . "*";

    $result = $t_guzzle->findObject($query, 'ca_objects');

    $objectIds_1 = array();

    foreach($result['results'] as $object) {
        $objectIds_1[] = $object['object_id'];
    }

# Mysql query

    $con = mysql_connect(__CA_DB_HOST__, __CA_DB_USER__, __CA_DB_PASSWORD__, __CA_DB_DATABASE__);
    mysql_select_db(__CA_DB_DATABASE__, $con);

    #$qry1 = "SELECT $value[$id] FROM $value[$tabel]"; uit reset_base_ACLs
    $qry1 = "SELECT object_id FROM ca_objects where idno like '$value%' and deleted = 0";

    $rs1 = mysql_query($qry1, $con);
    $count1 = mysql_num_rows($rs1);

    $objectIds_2 = array();

    while($row1 = mysql_fetch_assoc($rs1)) {

        $objectIds_2[] = $row1['object_id'];

    }

# vergelijken

    $compare = array_diff($objectIds_2, $objectIds_1);

    printf("[%20s] \t %d \t %d \t %d \t",$value, sizeof($objectIds_1), sizeof($objectIds_2), sizeof($compare));

    $bestand = '../data/indexing/ObjectIdsToIndexSandbox_20150811_' . $value . '.txt';

    file_put_contents($bestand, print_r($compare, true));

    echo "RESULTAAT weggeschreven naar bestand " . $bestand . "\n";

}

echo 'THE END';