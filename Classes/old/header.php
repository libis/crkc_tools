<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
error_reporting(E_ALL);
set_time_limit(0);
define("__MY_DIR__", $_SERVER['DOCUMENT_ROOT']);
$_SERVER['HTTP_HOST'] = "import";

require_once(__MY_DIR__."/ca_crkc/setup.php");
require_once(__CA_LIB_DIR__."/core/Db.php");
require_once(__CA_MODELS_DIR__."/ca_locales.php");
require_once(__CA_MODELS_DIR__."/ca_lists.php");
require_once(__CA_MODELS_DIR__."/ca_collections.php");
require_once(__CA_MODELS_DIR__."/ca_entities.php");
require_once(__CA_MODELS_DIR__."/ca_objects.php");
require_once(__CA_MODELS_DIR__."/ca_occurrences.php");
require_once(__CA_MODELS_DIR__."/ca_metadata_elements.php");
//require_once(__MY_DIR__."/cag_tools/classes/UserException.php");

//require_once(__MY_DIR__."/cag_tools-staging/shared/log/KLogger.php");

//include __MY_DIR__."/cag_tools/classes/MyFunctions_new.php";
include __MY_DIR__."/ca_crkc/crkc_tools/classes/MyFunctions.php";
