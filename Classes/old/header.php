<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
error_reporting(E_ALL);
set_time_limit(0);
$_SERVER['SCRIPT_FILENAME'] = "/www/libis/web/lias_html/ca_crkc/index.php";
define("__MY_DATA__", "../../data/");
define("__MY_DIR__", dirname($_SERVER['SCRIPT_FILENAME']));

require_once(__MY_DIR__."/ca_crkc/setup.php");
require_once(__CA_LIB_DIR__."/core/Db.php");
require_once(__CA_MODELS_DIR__."/ca_locales.php");
require_once(__CA_MODELS_DIR__."/ca_lists.php");
require_once(__CA_MODELS_DIR__."/ca_collections.php");
require_once(__CA_MODELS_DIR__."/ca_entities.php");
require_once(__CA_MODELS_DIR__."/ca_objects.php");
require_once('ca_models/ca_objects_bis.php');
require_once(__CA_MODELS_DIR__."/ca_occurrences.php");
require_once(__CA_MODELS_DIR__."/ca_metadata_elements.php");

include "MyFunctions.php";
