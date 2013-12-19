<?php
/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

define("__PROG__","dubbele_vervaardigers");

include('header.php');

$logDir = __MY_DIR__."/crkc_tools-staging/shared/log/";
$log = new KLogger($logDir, KLogger::DEBUG);

$con = mysql_connect(__CA_DB_HOST__, __CA_DB_USER__, __CA_DB_PASSWORD__, __CA_DB_DATABASE__);
mysql_select_db(__CA_DB_DATABASE__, $con);

for($i=1; $i<=142; $i++)
{
    $idno = 'verv_'.$i;

    $qr1 = "SELECT entity_id FROM ca_entities WHERE idno = '$idno'";

    $rs1 = mysql_query($qr1, $con);

    while($row1 = mysql_fetch_assoc($rs1)) {
        $log->logInfo("=========================================================");
        $log->logInfo("Idno: ",$idno);

        $entity_id = $row1['entity_id'];
        $log->logInfo("Entity_id: ",$entity_id);

        $objects = getNumberOfRelations($con, $entity_id, 'ca_objects_x_entities', 'entity_id');
        $log->logInfo("Related objects: ",$objects);

        $locations = getNumberOfRelations($con, $entity_id, 'ca_entities_x_storage_locations', 'entity_id');
        $log->logInfo("Related storage locations: ", $locations);

        $entities_left = getNumberOfRelations($con, $entity_id, 'ca_entities_x_entities', 'entity_left_id');
        $log->logInfo("Related left entities: ",$entities_left);
        $entities_right = getNumberOfRelations($con, $entity_id, 'ca_entities_x_entities', 'entity_right_id');
        $log->logInfo("Related right entities: ", $entities_right);

        $places = getNumberOfRelations($con, $entity_id, 'ca_entities_x_places', 'entity_id');
        $log->logInfo("Related places: ", $places);

        $collections = getNumberOfRelations($con, $entity_id, 'ca_entities_x_collections', 'entity_id');
        $log->logInfo("Related collections: ", $collections);

        $acls = getNumberOfEntries($con, $entity_id, 'ca_acl', 'acl_id');
        $log->logInfo("Number of ACLs: ", $acls);

        $attributes = getNumberOfEntries($con, $entity_id, 'ca_attributes', 'attribute_id');
        $log->logInfo("Number of attributes: ", $attributes);

        if ( ($objects == 0) && ($locations == 0) && ($entities_left == 0) &&
                ($entities_right == 0) && ($places == 0) && ($collections == 0) ) {
            $log->logAlert("RECORD CAN BE DELETED? ", $idno." / ".$entity_id);
        }

    }

}

mysql_close($con);


function getNumberOfRelations($con, $entity_id, $tabel, $kolom) {

    $qr = "SELECT count($kolom) FROM $tabel WHERE $kolom = $entity_id";

    $rs = mysql_query($qr, $con);

    if ($rs) {
        $row = mysql_fetch_row($rs);
        $count = $row[0];
    } else {
        $count = 0;
    }

    return $count;
}

function getNumberOfEntries($con, $entity_id, $tabel, $kolom) {

    $qr = "SELECT $kolom FROM $tabel WHERE table_num = 20 AND row_id = $entity_id";

    $rs = mysql_query($qr, $con);

    $count = mysql_num_rows($rs);

    return $count;
}
/*
$myFile = "testFile.txt";
$fh = fopen($myFile, 'a') or die("can't open file");
$stringData = "New Stuff 1\n";
fwrite($fh, $stringData);
$stringData = "New Stuff 2\n";
fwrite($fh, $stringData);
fclose($fh);
 *
 */