	// toegevoegd door Sam
	function getGroupIdByCode($groupCode) {
	$o_db = $this->getDb();
	$qr_res = $o_db->query("
		SELECT DISTINCT group_id
		FROM ca_user_groups
		WHERE code = ?
	", (string)$groupCode);
	
	$va_user_groups = array();
	while($qr_res->nextRow()) {
		$va_user_groups[] = $qr_res->get('group_id');
	}
	
	return $va_user_groups;
	}
	# ----------------------------------------
	// toegevoegd door Sam
	function getGroupCodeById($groupId) {
	$o_db = $this->getDb();
	$qr_res = $o_db->query("
		SELECT DISTINCT code
		FROM ca_user_groups
		WHERE group_id = ?
	", (string)$groupId);
	
	$va_user_groups = array();
	while($qr_res->nextRow()) {
		$va_user_groups[] = $qr_res->get('code');
	}
	
	return $va_user_groups;
	}