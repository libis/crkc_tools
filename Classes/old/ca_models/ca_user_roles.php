	// toegevoegd door Sam
	public static function getRoleIdByCode($roleCode) {
		
		$o_db = new Db();
		$qr_res = $o_db->query("
			SELECT DISTINCT role_id
			FROM ca_user_roles
			WHERE
				code = ?
		", (string) $roleCode);
		$va_roles = array();
		if(!empty($qr_res))
		{
			while($qr_res->nextRow()) {
				$va_roles[] =$qr_res->get('role_id');
			}
		} else {
			print "geen user_rol gevonden voor code: " . $roleCode;
		}
 		return $va_roles;
	}