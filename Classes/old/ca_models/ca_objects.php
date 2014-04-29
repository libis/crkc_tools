	// toegevoegd door Sam
	public function getAllObjects($objectIdno=null) {
		$o_db = $this->getDb();
		if($objectIdno ==  null)
		{
			$qr_res = $o_db->query("
				SELECT DISTINCT cae.object_id
				FROM ca_objects cae
			");
		} else {
			$qr_res = $o_db->query("
				SELECT DISTINCT cae.object_id
				FROM ca_objects cae
				WHERE
					cae.idno like ?
			", (string)$objectIdno);
		}

		$va_object_ids = array();
		if(!empty($qr_res))
		{
			while($qr_res->nextRow()) {
			$va_object_ids[] = $qr_res->get('object_id');
			}
		} else {
			print "empty ca_objects";
		}
		return $va_object_ids;
	}