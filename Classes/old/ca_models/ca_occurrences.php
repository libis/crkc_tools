        // toegevoegd door Sam
        public function getAllOccurrences() {

                //global $ps_idno, $pn_parent_id;

                $o_db = $this->getDb();

		$qr_res = $o_db->query("
			SELECT DISTINCT cap.occurrence_id
			FROM ca_occurrences cap
		");
                $va_occurrence_ids = array();
                while($qr_res->nextRow()) {
                        $va_occurrence_ids[] = $qr_res->get('occurrence_id');
                }
                if(!empty($va_occurrence_ids))
                {
                	return $va_occurrence_ids;
                } else {
                	print "geen occurrence gevonden";
                }
                
        }