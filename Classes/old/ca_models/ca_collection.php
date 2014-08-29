	// toegevoegd door Sam
	public function getCollectionIDsByidno($ps_idno) {
		$o_db = $this->getDb();
		// aangepast door Sam substr(cae.idno,1 ,4 ) = ?
		$qr_res = $o_db->query("
			SELECT DISTINCT cae.collection_id
			FROM ca_collections cae
			WHERE
				cae.idno = ?
		", (string)$ps_idno);

		$va_collection_ids = array();
		while($qr_res->nextRow()) {
			$va_collection_ids[] = $qr_res->get('collection_id');
		}
		return $va_collection_ids;
	}
	// toegevoegd door Sam
	public function getObjectsByCollectionIdno($ps_idno) {
		$o_db = $this->getDb();
		// aangepast door Sam substr(cae.idno,1 ,4 ) = ?
		$qr_res = $o_db->query("
			select oxc.object_id
			from ca_objects_x_collections oxc, ca_collections c
			where c.collection_id = oxc.collection_id and c.idno = ?
		", (string)$ps_idno);

		$va_object_ids = array();
		while($qr_res->nextRow()) {
			$va_object_ids[] = $qr_res->get('object_id');
		}
		return $va_object_ids;
	}
	// toegevoegd door Sam
	public function getAllCollections($collectionIdno=null) {
		$o_db = $this->getDb();
		if($collectionIdno ==  null)
		{
			$qr_res = $o_db->query("
				SELECT DISTINCT cae.collection_id
				FROM ca_collections cae
			");
		} else {
			$qr_res = $o_db->query("
				SELECT DISTINCT cae.collection_id
				FROM ca_collections cae
				WHERE
					cae.idno like ?
			", (string)$collectionIdno);
		}
		if(!empty($qr_res))
		{
			$va_collection_ids = array();
			while($qr_res->nextRow()) {
				$va_collection_ids[] = $qr_res->get('collection_id');
			}
		} else {
			print "empty ca_collections";
		}
		return $va_collection_ids;
	}
}
?>