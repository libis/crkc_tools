<?php
/**
 * Created by PhpStorm.
 * User: AnitaR
 * Date: 1/09/14
 * Time: 22:36
 * select * from ca_attributes att
 * left join ca_attribute_values as val
 * on att.attribute_id = val.attribute_id
 * where att.element_id = 202 order by att.row_id
 */
ini_set('memomy_limit', '1024M');
define("__PROG__", "verwerving_stats");

include('header.php');

require_once(__CA_LIB_DIR__ . '/core/Parsers/DelimitedDataParser.php');

$o_tab_parser = new DelimitedDataParser("\t");

if (!$o_tab_parser->parse(__MY_DATA__ . "/new/202_new.txt")) {
    die("Couldn't parse Kunstvoorwerpen data\n");
}

$teller = 0;
$digitool = array();
$digitoolEnd = array();

$file1 = fopen(__MY_DATA__.'/new/digitool_deels_missing_new.txt', 'w');
$file2 = fopen(__MY_DATA__.'/new/ObjectIds_deels_missing_new.txt', 'w');
$file3 = fopen(__MY_DATA__.'/new/digitool_volledig_missing_new.txt', 'w');
$file4 = fopen(__MY_DATA__.'/new/ObjectIds_volledig_missing_new.txt', 'w');
$file5 = fopen(__MY_DATA__.'/new/digitool_dubbels_new.txt', 'w');
$file6 = fopen(__MY_DATA__.'/new/ObjectIds_dubbels_new.txt', 'w');

$new = 0;
$old = 0;

$o_tab_parser->nextRow(); // skip first row

while ($o_tab_parser->nextRow()) {
    # Get columns from tab file and put them into named variables - makes code easier to read
    $vn_attr_id     = $o_tab_parser->getRowValue(1);
    $vn_object_id   = $o_tab_parser->getRowValue(5);
    $vn_value_id    = $o_tab_parser->getRowValue(6);
    //$vn_attr_id2    = $o_tab_parser->getRowValue(8);
    $vn_value       = $o_tab_parser->getRowValue(10);

    if ($old === 0) {
        $old = $vn_object_id;
    }
    $new = $vn_object_id;

    if ($vn_value === 'NULL') {
        $digitool[$vn_object_id]['null'][] = array('attr_id' => $vn_attr_id, 'object_id' => $vn_object_id, 'value_id' => $vn_value_id, 'value' => $vn_value);
    } else {
        $digitool[$vn_object_id]['notnull'][] = array('attr_id' => $vn_attr_id, 'object_id' => $vn_object_id, 'value_id' => $vn_value_id, 'value' => $vn_value);
    }

    if ($new !== $old) {

        if(sizeof($digitool[$old]['null']) !== sizeof($digitool[$old]['notnull']) && sizeof($digitool[$old]['null']) !==  0) {

            foreach($digitool[$old] as $key1 => $value1) {

                foreach($value1 as $value2) {

                    if (sizeof($digitool[$old]['null']) > sizeof($digitool[$old]['notnull']) && sizeof($digitool[$old]['notnull']) !==  0 ) {

                        fwrite($file1, $value2['attr_id']."\t".$value2['object_id']."\t".$value2['value_id']."\t".$value2['value']."\n");

                    } elseif ((sizeof($digitool[$old]['null']) < sizeof($digitool[$old]['notnull']) && sizeof($digitool[$old]['notnull']) !==  0))  {

                        fwrite($file5, $value2['attr_id']."\t".$value2['object_id']."\t".$value2['value_id']."\t".$value2['value']."\n");

                    } elseif (sizeof($digitool[$old]['notnull']) ===  0) {

                        fwrite($file3, $value2['attr_id']."\t".$value2['object_id']."\t".$value2['value_id']."\t".$value2['value']."\n");

                    }
                }

            }

            if (sizeof($digitool[$old]['null']) > sizeof($digitool[$old]['notnull']) && sizeof($digitool[$old]['notnull']) !==  0) {

                fwrite($file1, "\n");
                fwrite($file2, $old."\n");


            } elseif ((sizeof($digitool[$old]['null']) < sizeof($digitool[$old]['notnull']) && sizeof($digitool[$old]['notnull']) !==  0))  {

                fwrite($file5, "\n");
                fwrite($file6, $old."\n");


            } elseif (sizeof($digitool[$old]['notnull']) ===  0) {

                fwrite($file3, "\n");
                fwrite($file4, $old."\n");

            }

        }

        $digitool[$old] = 0;

        $old = $new;

    }

}
print "Einde";


/*
foreach($digitool as $key => $value) {

    if(sizeof($value['null']) !== sizeof($value['notnull'])) {

        $digitoolEnd = $digitool[$key];

    }
}

$file = fopen(__MY_DATA__.'/digitool.txt', 'w');

foreach($digitoolEnd as $key1 => $value1) {

    foreach($value1 as $value2) {

        foreach($value2 as $value3) {

            echo fwrite($file, $value3['attr_id']."\t".$value3['object_id']."\t".$value3['value_id']."\t".$value3['value']);

        }

    }

}
*
 *
 */

fclose($file1);
fclose($file2);
fclose($file3);
fclose($file4);
fclose($file5);
fclose($file6);



