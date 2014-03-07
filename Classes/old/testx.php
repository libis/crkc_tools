<pre>
<?php
echo "hallo";
error_reporting(E_ALL);
set_time_limit(0);
define("__MY_DIR__", $_SERVER['DOCUMENT_ROOT']);
$_SERVER['HTTP_HOST'] = "import";

require_once(__MY_DIR__."/ca_crkc/setup.php");
echo $_CA_CONFIG_PATH;
require_once(__CA_LIB_DIR__."/core/Db.php");
require_once(__CA_MODELS_DIR__."/ca_locales.php");
require_once(__CA_MODELS_DIR__."/ca_objects.php");

$o_db = new Db();

$qr_res = $o_db->query("SELECT object_id FROM ca_objects where deleted = 0 AND UPPER(idno) like 'POV%' AND object_id = 82682 ");

while($qr_res->nextRow()) {
	$entity_id = $qr_res->get('object_id');
        echo $entity_id;
        $t_entity = new ca_objects();
        $t_entity->load($entity_id);
        //$va_test1 = $t_entity->getFieldValuesArray();
        //print_r($va_test1);

        $va_test2 = $t_entity->getRelatedItems('ca_entities');
        print_r($va_test2);

}
?>
</pre>