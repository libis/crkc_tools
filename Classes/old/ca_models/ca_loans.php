	// Toegevoegd door Sam
	public function getLoanIDsByidno($ps_idno, $pn_parent_id=null) {

                $o_db = $this->getDb();

                if ($pn_parent_id) {
                        $qr_res = $o_db->query("
                                SELECT DISTINCT cap.loan_id
                                FROM ca_loans cap
                                WHERE
                                        cap.idno = ? AND cap.parent_id = ?
                        ", (string)$ps_idno, (int)$pn_parent_id);
                } else {
                        $qr_res = $o_db->query("
                                SELECT DISTINCT cap.loan_id
                                FROM ca_loans cap
                                WHERE
                                        cap.idno = ?
                        ", (string)$ps_idno);

                }
                $va_loan_ids = array();
                while($qr_res->nextRow()) {
                        $va_loan_ids[] = $qr_res->get('loan_id');
                }
                return $va_loan_ids;
        }
        // Toegevoegd door Sam
	public function getLoans() {

                $o_db = $this->getDb();

		$qr_res = $o_db->query("
			SELECT DISTINCT cap.loan_id
			FROM ca_loans cap
		");
                $va_loan_ids = array();
                while($qr_res->nextRow()) {
                        $va_loan_ids[] = $qr_res->get('loan_id');
                }
                if(!empty($va_loan_ids))
                {
                	return $va_loan_ids;
                } else {
                	print "Geen loans";	
                }
        }