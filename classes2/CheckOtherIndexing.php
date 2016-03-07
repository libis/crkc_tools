<?php
/**
 * Created by PhpStorm.
 * User: AnitaR
 * Date: 25/04/14
 * Time: 11:16
 */

# define("__PROG__", "comparison");

include('header.php');

#$log = new Klogger(__LOG_DIR__,KLogger::DEBUG);

require_once('GuzzleRestCookie.php');

$t_guzzle = new GuzzleRestCookie(__INI_FILE__);

$con = mysql_connect(__CA_DB_HOST__, __CA_DB_USER__, __CA_DB_PASSWORD__, __CA_DB_DATABASE__);
mysql_select_db(__CA_DB_DATABASE__, $con);

$id = 'id';
$tabel = 'tabel';
$tabelnr = 'tabelnr';

$matrix = array('13' => array($id => 'collection_id', $tabel => 'ca_collections', $tabelnr => '13'),
                '20' => array($id => 'entity_id', $tabel => 'ca_entities', $tabelnr => '20'),
                '33' => array($id => 'item_id', $tabel => 'ca_list_items', $tabelnr => '33'),
                '67' => array($id => 'occurrence_id', $tabel => 'ca_occurrences', $tabelnr => '67'),
                '72' => array($id => 'place_id', $tabel => 'ca_places', $tabelnr => '72'),
                '89' => array($id => 'location_id', $tabel => 'ca_storage_locations', $tabelnr => '89'),
                '133' => array($id => 'loan_id', $tabel => 'ca_loans', $tabelnr => '133'));

foreach($matrix as $value) {

    $table_num = $value[$tabelnr];
    $element_id = $value[$id];

# REST API-call

    $query = $value[$tabel] .".". $value[$id]. ":*";

    $result = $t_guzzle->findObject($query, $value[$tabel]);

    $objectIds_1 = array();

    foreach($result['results'] as $object) {
        $objectIds_1[] = $object[$value[$id]];
    }

# Mysql query

    $qry1 = "SELECT $value[$id] FROM $value[$tabel] where deleted = 0";

    $rs1 = mysql_query($qry1, $con);
    $count1 = mysql_num_rows($rs1);

    $objectIds_2 = array();

    while($row1 = mysql_fetch_assoc($rs1)) {

        $objectIds_2[] = $row1[$value[$id]];

    }

# vergelijken

    $compare = array_diff($objectIds_2, $objectIds_1);

    printf("[%20s] \t %d \t %d \t %d \t",$value[$tabel], sizeof($objectIds_1), sizeof($objectIds_2), sizeof($compare));

    $bestand = '../data/indexing/' . $value[$tabel]. 'ToIndex.txt';

    file_put_contents($bestand, print_r($compare, true));

    echo "RESULTAAT weggeschreven naar bestand " . $bestand . "\n";

}

echo 'THE END';