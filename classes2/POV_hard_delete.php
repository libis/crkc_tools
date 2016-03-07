<?php
/**
 * Created by PhpStorm.
 * User: AnitaR
 * Date: 25/04/14
 * Time: 11:16
 */

include('header.php');
require_once(__CA_LIB_DIR__ . '/core/Parsers/DelimitedDataParser.php');
require_once('GuzzleRestCookie.php');

# for Solr-delete
$AUTH_CURRENT_USER_ID = 'administrator';
$t_object = new ca_objects();
$t_object->setMode(ACCESS_WRITE);
# for Mysql-delete
$t_guzzle = new GuzzleRestCookie(__INI_FILE__);
# for reading pids
$o_tab_parser = new DelimitedDataParser("\t");

# Read txt; line by line till end of file.
if (!$o_tab_parser->parse(__MY_DATA__ . '/delete/pa_delete.txt')) {
    die("Couldn't parse delete.txt data\n");
}

$delete = array();
$teller = 1;
# Er wordt vanuit gegaan dat er geen headers zijn
while($o_tab_parser->nextRow() && $teller <= 2000) {
    // Get columns from tab file and put them into named variables - makes code easier to read
    $pid = $o_tab_parser->getRowValue(1);

    $delete[] = $pid;
    $teller++;
}
$g = 0;
foreach($delete as $pid) {
    $g++;
    # Access the object in CA
    $t_object->load($pid);
    $t_object->getPrimaryKey();

    echo $g .". Deleting " . $pid;

    # First we do the Rest-delete to clean up Mysql

    $data = $t_guzzle->hardDeleteObject($pid, 'ca_objects');

    if (isset($data['ok']) && ($data['ok'] != 1)) {

        echo "ERROR SQL ERROR ";

    } else {

        # Next do the Solr-delete

        $result = $t_object->delete(true, array('hard'), null, null);

        if (!$result) {

            echo "ERROR SOLR ERROR ";

        }

    }

    echo " DONE\n";

}

echo "FINISHED";