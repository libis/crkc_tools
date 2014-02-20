<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

define("__PROG__","resetBaseACLs_sandbox_table36");

include('header_sandbox.php');

$logDir = __MY_DIR__."/crkc_tools-staging/shared/log/";
$log = new KLogger($logDir, KLogger::DEBUG);

$con = mysql_connect(__CA_DB_HOST__, __CA_DB_USER__, __CA_DB_PASSWORD__, __CA_DB_DATABASE__);
mysql_select_db(__CA_DB_DATABASE__, $con);

$id = 'id';
$tabel = 'tabel';
$tabelnr = 'tabelnr';
/*
$matrix = array('20' => array($id => 'entity_id', $tabel => 'ca_entities', $tabelnr => '20'),
                '33' => array($id => 'item_id', $tabel => 'ca_list_items', $tabelnr => '33'),
                '36' => array($id => 'list_id', $tabel => 'ca_lists', $tabelnr => '36'),
                '67' => array($id => 'occurrence_id', $tabel => 'ca_occurrences', $tabelnr => '67'),
                '72' => array($id => 'place_id', $tabel => 'ca_places', $tabelnr => '72'),
                '89' => array($id => 'location_id', $tabel => 'ca_storage_locations', $tabelnr => '89'),
                '103' => array($id => 'set_id', $tabel => 'ca_sets', $tabelnr => '103'),
                '133' => array($id => 'loan_id', $tabel => 'ca_loans', $tabelnr => '133')
                );
 *
 */
//$matrix = array('20' => array($id => 'entity_id', $tabel => 'ca_entities', $tabelnr => '20') );
//$matrix = array('33' => array($id => 'item_id', $tabel => 'ca_list_items', $tabelnr => '33') );
//$matrix = array('36' => array($id => 'list_id', $tabel => 'ca_lists', $tabelnr => '36') );
//$matrix = array('67' => array($id => 'occurrence_id', $tabel => 'ca_occurrences', $tabelnr => '67') );
//$matrix = array('72' => array($id => 'place_id', $tabel => 'ca_places', $tabelnr => '72') );
//$matrix = array('89' => array($id => 'location_id', $tabel => 'ca_storage_locations', $tabelnr => '89') );
$matrix = array('103' => array($id => 'set_id', $tabel => 'ca_sets', $tabelnr => '103'));
//$matrix = array('133' => array($id => 'loan_id', $tabel => 'ca_loans', $tabelnr => '133'));

$log->logInfo("De matrix : ", $matrix);

$aangemaakt = 0;
$aangepast = 0;
$verwijderd = 0;
$d_errors = 0;
$i_errors = 0;
$u_errors = 0;

//$o_db = new Db();

foreach($matrix as $value) {

        $log->logInfo("=========================================================");

        $table_num = $value[$tabelnr];
        $element_id = $value[$id];

        $qry1 = "SELECT $value[$id] FROM $value[$tabel]";
        $log->logInfo("QUERY : ", $qry1);

        $rs1 = mysql_query($qry1, $con);
        $count1 = mysql_num_rows($rs1);
        $log->logInfo("Aantal records : ", $count1);

        while($row1 = mysql_fetch_assoc($rs1)) {

            $element = $row1[$element_id];
            $log->logInfo("ELEMENT ", $element);

            ####################################################################
            # STAP 1: Verwijder ALLE bestaande ACL's voor dit element
            ####################################################################

            $qry5 = "SELECT acl_id FROM ca_acl WHERE table_num = $table_num AND row_id = $element";
            $rs5 = mysql_query($qry5, $con);
            $count5 = mysql_num_rows($rs5);

            $qry6 = "DELETE FROM ca_acl WHERE table_num = $table_num AND row_id = $element";

            $rs6 = mysql_query($qry6, $con);

            if ($rs6) {
                $log->logInfo("ACL succesvol verwijderd");
                $verwijderd = $verwijderd + $count5;
            } else {
                $log->logError("ERROR bij verwijderen ACL");
                $d_errors = $d_errors + 1;
            }

            #####################################################################
            # STAP 2: voegen correcte basisACL in (met read-rechten voor iedereen
            #####################################################################
            //parameters: connectie, user_group, tabelnummer, row_id, access, log, teller, error_teller
            insertACL($con, 'NULL', $table_num, $element, 1, $log, $aangemaakt, $i_errors);

            ####################################################################
            # STAP 3 : insert ACL's voor de hoofdredacteuren
            ####################################################################
            //CRKC hoofdredacteuren
            insertACL($con,  8, $table_num, $element, 3, $log, $aangemaakt, $i_errors);
            //POV hoofdredacteuren
            insertACL($con, 14, $table_num, $element, 3, $log, $aangemaakt, $i_errors);
            //PA hoofdredacteuren
            insertACL($con, 20, $table_num, $element, 3, $log, $aangemaakt, $i_errors);
            //PWV hoofdredacteuren
            insertACL($con, 26, $table_num, $element, 3, $log, $aangemaakt, $i_errors);

        }

        unset($element);
        unset($table_num);
        unset($element_id);

        $log->logInfo("=========================================================");
        $log->logInfo("verwerking", $value);
        $log->logInfo("=========================================================");
        $log->logInfo("INSERTS : ", $aangemaakt);
        $log->logInfo("INSERT_ERRORS : ", $i_errors);
        $log->logInfo("UPDATES : ", $aangepast);
        $log->logInfo("UPDATE_ERRORS : ", $u_errors);
        $log->logInfo("DELETES : ", $verwijderd);
        $log->logInfo("DELETE_ERRORS : ", $d_errors);
        $log->logInfo("=========================================================");

        unset($aangemaakt);
        unset($aangepast);
        unset($verwijderd);
        unset($i_errors);
        unset($u_errors);
        unset($d_errors);
}

$log->logInfo("+++++++++++++++FINISHED++++++++++++++++++++++");

mysql_close($con);

function deleteACLs ($con, $query_res, $log, &$verwijderd, &$d_errors) {

    while($row = mysql_fetch_assoc($query_res)) {

        $acl = $row['acl_id'];

        $qryd = "DELETE FROM ca_acl WHERE acl_id = $acl";

        $qr_ids_d = mysql_query($qryd, $con);

        if ($qr_ids_d) {
            $log->logInfo("ACL succesvol verwijderd", $acl);
            $verwijderd = $verwijderd + 1;
        } else {
            $log->logError("ERROR bij verwijderen ACL", $acl);
            $d_errors = $d_errors + 1;
        }
        unset($acl);
        unset($qryd);
        unset($qr_ids_d);
    }
}


//parameters: connectie, user_group, array uit $matrix, row_id, access, log, teller, error_teller
function insertACL($con, $group, $table, $element, $access, $log, &$aangemaakt, &$i_errors) {

    $qryi = "INSERT into ca_acl (group_id, user_id, access, table_num, row_id) VALUES ($group, NULL, $access, $table, $element) ";

    $qr_ids_i = mysql_query($qryi, $con);

    if ($qr_ids_i) {
        $log->logInfo("ACL succesvol aangemaakt", $qryi);
        $aangemaakt = $aangemaakt + 1;
    } else {
        $log->logError("ERROR bij aanmaken ACL", $qryi);
        $i_errors = $i_errors + 1;
    }
    unset($qryi);
    unset($qr_ids_i);
}

function updateACL($con, $rs2, $log, &$aangepast, &$u_errors) {

    $row = mysql_fetch_assoc($rs2);

    $acl = $row['acl_id'];

    $qryu = "UPDATE ca_acl SET access = 1 WHERE acl_id = $acl";
    $qr_ids_u = mysql_query($qryu, $con);

    if ($qr_ids_u) {
        $log->logInfo("ACL succesvol aangepast", $acl);
        $aangepast = $aangepast + 1;
    } else {
        $log->logError("ERROR bij aanpassen ACL", $acl);
        $u_errors = $u_errors + 1;
    }
    unset($acl);
    unset($qryu);
    unset($qr_ids_u);
}