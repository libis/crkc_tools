<?php
define("__PROG__","VAusers");

include('header_sandbox.php');
$log = setLogging();

require_once(__CA_MODELS_DIR__."/ca_users.php");
require_once(__CA_LIB_DIR__.'/core/Parsers/DelimitedDataParser.php');

$t_user = new ca_users();

$t_user->setMode(ACCESS_WRITE);


$o_tab_parser = new DelimitedDataParser("\t");
// Read csv; line by line till end of file.
if (!$o_tab_parser->parse(__MY_DIR__.'/crkc_tools/data/userAccounts.csv')) {
	die("Couldn't parse userAccounts.csv data\n");
}
$log->logInfo("READING userAccounts.csv...");

while($o_tab_parser->nextRow()) {
    // Get columns from tab file and put them into named variables - makes code easier to read
    $name      =	$o_tab_parser->getRowValue(1); //id (niet gebruiken)
    $new_name = $name.'VA';

    $log->logInfo('Verwerken user: ', $name);

    if ( ($t_user->exists($name)) && !($t_user->exists($new_name)) ) {
        $t_user->load($name);
        $t_user->getPrimaryKey();
        $t_user->removeFromGroups('6');

        $t_user->cloneRecord();
        $t_user->set('user_name', $new_name);
        $t_user->insert();

        $log->logInfo('VA gebruiker aangemaakt', $new_name);
        $t_user->load($new_name);
        $t_user->getPrimaryKey();

        $t_user->addToGroups('6');

        $log->logInfo('Groupsettings aangepast');

    } else {
        $log->logInfo('Gebruiker bestaat reeds', $new_name);
    }
}
$log->logInfo('Einde verwerking');


function setLogging() {
    $logDir = readlink(__MY_DIR__."/crkc_tools/log/");
    $log = new KLogger($logDir, KLogger::DEBUG);

    return $log;
}