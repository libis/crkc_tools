	public function getEntityIDsByidno($ps_idno, $pn_parent_id=null) {

                $o_db = $this->getDb();

                if ($pn_parent_id) {
                        $qr_res = $o_db->query("
                                SELECT DISTINCT cap.entity_id
                                FROM ca_entities cap
                                WHERE
                                        cap.idno = ? AND cap.parent_id = ?
                        ", (string)$ps_idno, (int)$pn_parent_id);
                } else {
                        $qr_res = $o_db->query("
                        	SELECT DISTINCT cap.entity_id
                                FROM ca_entities cap
                                WHERE
                                        cap.idno = ?
                        ", (string)$ps_idno);

                }
                $va_entity_ids = array();
                while($qr_res->nextRow()) {
                        $va_entity_ids[] = $qr_res->get('entity_id');
                }
                return $va_entity_ids;
        }
        // toegevoegd door Sam
	public function getAllEntities() {

                $o_db = $this->getDb();

                        $qr_res = $o_db->query("
                                SELECT DISTINCT cap.entity_id
                                FROM ca_entities cap
                                ");

                $va_entity_ids = array();
                while($qr_res->nextRow()) {
                        $va_entity_ids[] = $qr_res->get('entity_id');
                }
                if(!empty($va_entity_ids))
                {
                return $va_entity_ids;
                } else {
                	print "Geen entity gevonden";
                }
        }