	public function getPlaceIDsByidno($ps_idno, $pn_parent_id=null) {
		$o_db = $this->getDb();
		
		if ($pn_parent_id) {
			$qr_res = $o_db->query("
				SELECT DISTINCT cap.place_id
				FROM ca_places cap
				WHERE
					cap.idno = ? AND cap.parent_id = ?
			", (string)$ps_idno, (int)$pn_parent_id);
		} else {
			$qr_res = $o_db->query("
				SELECT DISTINCT cap.place_id
				FROM ca_places cap
				WHERE
					cap.idno = ?
			", (string)$ps_idno);

		}
		$va_idno_ids = array();
		while($qr_res->nextRow()) {
			$va_idno_ids[] = $qr_res->get('place_id');
		}
		return $va_idno_ids;
	}
	// toegevoegd door Sam
	public function getAllPlaces() {
		$o_db = $this->getDb();
		
		$qr_res = $o_db->query("
			SELECT DISTINCT cap.place_id
			FROM ca_places cap
		");
		$va_idno_ids = array();
		while($qr_res->nextRow()) {
			$va_idno_ids[] = $qr_res->get('place_id');
		}
		if(!empty($va_idno_ids))
		{
			return $va_idno_ids;
		} else {
			print "geen place gevonden";	
		}
	}