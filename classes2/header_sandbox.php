<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

error_reporting(E_ALL);
set_time_limit(0);
$_SERVER['SCRIPT_FILENAME'] =  "c:/xampp/htdocs/crkcsandbox_test/index.php";

define("__MY_DIR__", $_SERVER['DOCUMENT_ROOT']);
define("__MY_DATA__","../data");

require_once(dirname($_SERVER['SCRIPT_FILENAME'])."/setup.php");
require_once(__CA_LIB_DIR__."/core/Db.php");
require_once(__CA_MODELS_DIR__."/ca_locales.php");
require_once(__CA_MODELS_DIR__."/ca_lists.php");
require_once(__CA_MODELS_DIR__."/ca_objects.php");

#require_once("c:/xampp/htdocs/crkc_tools-staging/shared/log/KLogger.php");
#define("__LOG_DIR__", "c:/xampp/htdocs/crkc_tools-staging/shared/log/");
define("__INI_FILE__", "tools.ini");
