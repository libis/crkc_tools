<?php

class MyFunctions {
    //put your code here

    const ELEMENT_ID = 'element_id';
    const LIST_ID = 'list_id';
    const ELEMENT_CODE = 'element_code';

    function loadMetadata() {

        $o_db = new Db();
        //$o_config = Configuration::load();

        $metadata = array();

        $qr1 = "SELECT element_id, list_id, element_code FROM ca_metadata_elements";

        $qr_metadata = $o_db->query($qr1);

        while($qr_metadata->nextRow()) {

                $id = $qr_metadata->get(self::ELEMENT_ID);
                $list = $qr_metadata->get(self::LIST_ID);
                $code = $qr_metadata->get(self::ELEMENT_CODE);
                $metadata[$id] = array('list' => $list, 'code' => $code);
        }
        return $metadata;
    }

    public function getListItemLabelById($pn_list_id, $pn_list_item_id) {
		$o_db = new Db();

                $qr_res = $o_db->query("
                        SELECT capl.name_singular
                        FROM ca_list_items cap
                        INNER JOIN ca_list_item_labels AS capl ON capl.item_id = cap.item_id
                        WHERE
                                (cap.item_id = ?) AND cap.list_id = ?
                ", (int) $pn_list_item_id, (int)$pn_list_id);

		$va_item_labels = array();
		while($qr_res->nextRow()) {
			$va_item_labels[] = $qr_res->get('name_singular');
		}
		return $va_item_labels;
	}

}

?>
