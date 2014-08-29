	public function getLocationIDsByidno($ps_idno, $pn_parent_id=null) {
		$o_db = $this->getDb();
		
		if ($pn_parent_id) {
			$qr_res = $o_db->query("
				SELECT DISTINCT casl.location_id
				FROM ca_storage_locations casl
				INNER JOIN ca_storage_location_labels AS casll ON casll.location_id = casl.location_id
				WHERE
					casll.idno = ? AND casl.parent_id = ?
			", (string)$ps_idno, (int)$pn_parent_id);
		} else {
			$qr_res = $o_db->query("
				SELECT DISTINCT casl.location_id
				FROM ca_storage_locations casl
				INNER JOIN ca_storage_location_labels AS casll ON casll.location_id = casl.location_id
				WHERE
					casll.idno = ?
			", (string)$ps_idno);

		}
		$va_location_ids = array();
		while($qr_res->nextRow()) {
			$va_location_ids[] = $qr_res->get('location_id');
		}
		return $va_location_ids;
	}
	# ------------------------------------------------------
	public function getStorageLocations() {
		$o_db = $this->getDb();
		
		$qr_res = $o_db->query("
			SELECT DISTINCT casl.location_id
			FROM ca_storage_locations casl
		");
		$va_location_ids = array();
		while($qr_res->nextRow()) {
			$va_location_ids[] = $qr_res->get('location_id');
		}
		if(!empty($va_location_ids))
		{
			return $va_location_ids;
		} else {
			print "geen storage locations";
		}
	}