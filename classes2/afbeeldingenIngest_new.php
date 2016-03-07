<?php
/* Dit script wordt gebruikt om afbeeldingen van Digitool in te laden in CA na ingest.
*/
define("__PROG__","ingest_afbeeldingen");

include('header_sandbox.php');

$log = new Klogger(__LOG_DIR__,KLogger::DEBUG);

require_once(__CA_LIB_DIR__ . '/core/Parsers/DelimitedDataParser.php');

$locale_id = 'nl_NL';

require_once("GuzzleRestCookie.php");
$t_guzzle = new GuzzleRestCookie(__INI_FILE__);

###############################################################################
# PART I : read input-file into associative array (tab-delimited - no headers
###############################################################################

# want to parse comma delimited data? Pass a comma here instead of a tab.
$o_tab_parserAfbeeldingen = new DelimitedDataParser("\t");

# Read csv; line by line till end of file.
if (!$o_tab_parserAfbeeldingen->parse(__MY_DATA__ . '/122ObjectenPid.txt')) {
    die("Couldn't parse afbeeldingen.csv data\n");
}

$log->logInfo("READING afbeeldingen.csv");

$afbeeldingen = array();

// Er wordt vanuit gegaan dat er geen headers zijn
while($o_tab_parserAfbeeldingen->nextRow()) {
    // Get columns from tab file and put them into named variables - makes code easier to read
    $pid			=	$o_tab_parserAfbeeldingen->getRowValue(1);
    $label			=	$o_tab_parserAfbeeldingen->getRowValue(2);
    $actie          =   $o_tab_parserAfbeeldingen->getRowValue(3);

    if(!empty($pid)) {
        $afbeeldingen[] = array('label' => $label, 'pid' => $pid, 'action' => strtoupper($actie));
    } else {
        $log->logError("Problem adding " .$label . " and Pid: " . $pid);
    }
}

$log->logInfo("Done reading afbeeldingen.csv");

###############################################################################
# PART II: process associative-array
###############################################################################

$log->logInfo("processing afbeeldingen.csv");

$aantal = sizeof($afbeeldingen) - 1;

for($i=0; $i<= $aantal ; $i++) {

    $log->logInfo("Creating afbeelding voor", $afbeeldingen[$i] );

    // label en idno moeten nog gematcht worden
    // kunstvoorwerp_idno loop vervangen door opzoeken van label

    $label_key = $afbeeldingen[$i]['label'];
    $pid_value = $afbeeldingen[$i]['pid'];
    $action = $afbeeldingen[$i]['action'];

    #specifiek voor crkc

    $pattern = '/([a-zA-Z]+\.)(.*)(_)(.*)(_)(.*)(_)(.*)(_.*)/';
    //$pattern = '/([a-zA-Z]+\.)(.*)(_)(.*)(_.*)/';
    if (preg_match($pattern, $label_key, $matches)) {

        $lookup = $matches[1] . $matches[2] . "." . $matches[4] . "." . $matches[6] . "/" . $matches[8];
        //$lookup = $matches[1] . $matches[2] . "." . $matches[4];

    } else {

        $pattern = '/([a-zA-Z]+\.)(.*)(_)(.*)(_.*)/';
        //$pattern = '/([a-zA-Z]+\.)(.*)(_)(.*)(_)(.*)(_)(.*)(_.*)/';

        if (preg_match($pattern, $label_key, $matches)) {

            $lookup = $matches[1] . $matches[2] . "." . $matches[4];
            //$lookup = $matches[1] . $matches[2] . "." . $matches[4] . "." . $matches[6] . "/" . $matches[8];

        }

    echo $lookup ."\n";

#object_id opzoeken

    $query = "ca_objects.idno:'".trim($lookup)."'";
    $data = $t_guzzle->findObject($query, 'ca_objects');

    if (isset($data['ok']) && ($data['ok'] == 1) && is_array($data['results'])) {
        if (sizeof($data['results']) > 1) {
            echo "Meer dan 1 kandidaat gevonden \n";
            $log->logError('Meer dan één kandidaat gevonden voor ', $afbeeldingen[$i]);
            $log->logError('zie output', $data);
            $objectId = '';
            //exit;
        } else {
            $objectId = $data['results'][0]['object_id'];
            $log->logInfo('object_id gevonden', $objectId);
            echo $objectId."\n";
        }
    } else {
        echo "projectnr niet gevonden - object bestaat (nog) niet\n";
        $log->logError('projectnr niet gevonden - object bestaat nog niet', $afbeeldingen[$i]);
        $objectId = '';
    }

    if (isset($objectId) && $objectId !== '') {

        $temp = array();
        $update = array();

        if ($action === 'ADD' || $action === '') {

            $temp['digitoolUrl'][] = array(
                'locale'        => $locale_id,
                'digitoolUrl'   =>  $pid_value
            );

            $update = array(
                "attributes" => ($temp)
            );

        } elseif ($action === 'DEL') {

            $result = $t_guzzle->getFullObject($objectId, 'ca_objects');

            $digitool = $result['attributes']['digitoolUrl'];

            foreach($digitool as $value) {

                if ($value['digitoolUrl'] !== $pid_value) {

                    $temp['digitoolUrl'][] = $value;
                }
            }

            $update = array(
                "remove_attributes" => array("digitoolUrl"),
                "attributes" => ($temp)
            );

        }


        $data2 = $t_guzzle->updateObject($update, $objectId, 'ca_objects');

        $log->logInfo('het eindresultaat',$data2);

        if (isset($data2['ok']) && ($data2['ok'] != 1)) {

            echo "ERROR ERROR \n";
            $log->logError("ERROR ERROR : Er is iets misgelopen!!!!!", $data);

        }


    }
    unset($label_key);
    unset($pid_value);
    unset($action);
    unset($lookup);
    unset($query);
    unset($data);
    unset($data2);
    unset($objectId);
    unset($result);
    unset($digitool);

}
echo "END IMPORTING afbeeldingen.csv\n";
$log->logInfo('EINDE');