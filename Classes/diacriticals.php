<PRE>
<?php
/**
 * Created by PhpStorm.
 * User: AnitaR
 * Date: 14/04/14
 * Time: 21:10
 */

define("__PROG__", "diacriticals");

include('header.php');

require_once(__CA_MODELS_DIR__ . "/ca_attributes.php");
require_once(__CA_MODELS_DIR__ . '/ca_collections.php');

$diacr = array('Ã ', 'Ã¡', 'Ã¢', 'Ã¤', 'Ã§', 'Ã©', 'Ã¨', 'Ãª', 'Ã«', 'Ã³', 'Ã²', 'Ã´', 'Ã¶', 'Ãº', 'Ã¹', 'Ã»', 'Ã¼');
$gewenst = "ATT";

$connection = mysql_connect(__CA_DB_HOST__, __CA_DB_USER__, __CA_DB_PASSWORD__);
mysql_select_db(__CA_DB_DATABASE__, $connection);

if ($gewenst === "OBJ" || $gewenst === "ALL"){

    foreach($diacr as $value) {
        echo $value."\n\r";
        //$qr_table = "SELECT label_id,object_id,  name FROM ca_object_labels where name like CONCAT('%',cast(_latin1'$value' AS CHAR CHARACTER SET utf8), '%')";
        $qr_table = "SELECT label_id, object_id, name FROM ca_object_labels where name like '%$value%'";

        $table = mysql_query($qr_table, $connection);

        if (!$table) {
            echo "no records found\n\p";
        } else {

            $count = mysql_num_rows($table);
            echo $count."\n\r";
            while($table_array = mysql_fetch_assoc($table)) {

                $label_id = $table_array['label_id'];
                $object_id = $table_array['object_id'];
                echo $object_id."\n\r";

                $qr_update = "UPDATE ca_object_labels SET name = convert(binary convert(name using latin1) using utf8) where label_id = $label_id";

                $update = mysql_query($qr_update, $connection);
                echo $update."\n\r";
            }
        }
    }
    echo "DONE";
    echo "Do not forget to rebuild the sort values using caUtils in support/bin";
}

if ($gewenst === "ATT" || $gewenst === "ALL"){

    foreach($diacr as $value) {
        echo $value."\n\r";
        //$qr_table = "SELECT label_id,object_id,  name FROM ca_object_labels where name like CONCAT('%',cast(_latin1'$value' AS CHAR CHARACTER SET utf8), '%')";
        $qr_table = "SELECT value_id, value_longtext1 FROM ca_attribute_values where value_longtext1 like '%$value%'";

        $table = mysql_query($qr_table, $connection);

        if (!$table) {
            echo "no records found\n\p";
        } else {

            $count = mysql_num_rows($table);
            echo $count."\n\r";

            while($table_array = mysql_fetch_assoc($table)) {

                $label_id = $table_array['value_id'];

                $qr_update = "UPDATE ca_attribute_values SET value_longtext1 = convert(binary convert(value_longtext1 using latin1) using utf8) where value_id = $label_id";

                $update = mysql_query($qr_update, $connection);
                echo $update."\n\r";
            }
        }
    }
    echo "DONE";
}

mysql_close($connection);

?>
</PRE>
