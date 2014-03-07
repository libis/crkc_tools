<?php
/*
 * Step 1: Initialisation
 */
require_once("/www/libis/vol03/lias_html/ca_crkc/setup.php");

require_once(__CA_LIB_DIR__.'/core/Db.php');
require_once(__CA_MODELS_DIR__.'/ca_locales.php');
//require_once(__CA_MODELS_DIR__.'/ca_attributes.php');
require_once(__CA_MODELS_DIR__.'/ca_entities.php');
require_once("/www/libis/vol03/lias_html/ca_crkc/crkc_tools/classes/Klogger.php");

$t_locale = new ca_locales();
$pn_locale_id = $t_locale->loadLocaleByCode('nl_NL');

$t_entity = new ca_entities();
$t_entity->setMode(ACCESS_WRITE);

$o_db = new Db();
$o_config = Configuration::load();

define("__PROG__","opkuis_vervaardigers_01");
$logDir = "/www/libis/vol03/lias_html/ca_crkc/crkc_tools/log/";
$log = new KLogger($logDir, KLogger::DEBUG);

$vn_c = 1;

for ($vn_c = 1; $vn_c <= 150; $vn_c++) {
    $idno = 'verv_'.$vn_c;
    $log->logInfo("vervaardiger:", $idno);

    $qr1 = "SELECT entity_id FROM ca_entities WHERE idno like '$idno'";
    $qr_entity_ids = $o_db->query($qr1);
    $dimension = $o_db->affectedRows();
    $log->logInfo("gevonden aantal? ", $dimension);

    if ($dimension > 1) {

        while($qr_entity_ids->nextRow()) {

		$entity_id = $qr_entity_ids->get('entity_id');

                $qr2 = "SELECT relation_id FROM ca_objects_x_entities WHERE entity_id = $entity_id ";
                $qr_relation_ids = $o_db->query($qr2);
                $dim = $o_db->affectedRows();


                $log->logInfo("aan entity ".$entity_id."-".$idno." gerelateerde objecten? ", $dim);

	}
    }
}
$log->logInfo("EINDE");
