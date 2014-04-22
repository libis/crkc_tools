<?php
/** ---------------------------------------------------------------------
 * app/models/ca_occurrences.php : table access class for table ca_occurrences
 * ----------------------------------------------------------------------
 * CollectiveAccess
 * Open-source collections management software
 * ----------------------------------------------------------------------
 *
 * Software by Whirl-i-Gig (http://www.whirl-i-gig.com)
 * Copyright 2008-2011 Whirl-i-Gig
 *
 * For more information visit http://www.CollectiveAccess.org
 *
 * This program is free software; you may redistribute it and/or modify it under
 * the terms of the provided license as published by Whirl-i-Gig
 *
 * CollectiveAccess is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTIES whatsoever, including any implied warranty of 
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  
 *
 * This source code is free and modifiable under the terms of 
 * GNU General Public License. (http://www.gnu.org/copyleft/gpl.html). See
 * the "license.txt" file for details, or visit the CollectiveAccess web site at
 * http://www.CollectiveAccess.org
 * 
 * @package CollectiveAccess
 * @subpackage models
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License version 3
 * 
 * ----------------------------------------------------------------------
 */
 
 /**
   *
   */

require_once(__CA_LIB_DIR__."/ca/IBundleProvider.php");
require_once(__CA_LIB_DIR__."/ca/BundlableLabelableBaseModelWithAttributes.php");


	/**
	 *
	 */
	function getOccurrenceIDsByidno($ps_idno, $pn_parent_id=null) {
		
		global $ps_idno, $pn_parent_id;
		
		$o_db = $this->getDb();
		
		if ($pn_parent_id) {
			$qr_res = $o_db->query("
				SELECT DISTINCT cap.occurrence_id
				FROM ca_occurrences cap
				WHERE
					cap.idno = ? AND cap.parent_id = ?
			", (string)$ps_idno, (int)$pn_parent_id);
		} else {
			$qr_res = $o_db->query("
				SELECT DISTINCT cap.occurrence_id
				FROM ca_occurrences cap
				WHERE
					cap.idno = ?
			", (string)$ps_idno);

		}
		$va_occurrence_ids = array();
		while($qr_res->nextRow()) {
			$va_occurrence_ids[] = $qr_res->get('occurrence_id');
		}
		return $va_occurrence_ids;
	}



	# ------------------------------------------------------
         /**
         * Returns entity_id for entities with matching fore- and surnames
         *
         * @param string $ps_forename The forename to search for
         * @param string $ps_surnamae The surname to search for
         * @return array Entity_id's for matching entities
         */
        function getEntityIDsByidno($ps_idno) {
                $o_db = $this->getDb();
                $qr_res = $o_db->query("
                        SELECT DISTINCT cae.entity_id
                        FROM ca_entities cae
                        WHERE
                                cae.idno = ? 
                ", (string)$ps_idno);

                $va_entity_ids = array();
                while($qr_res->nextRow()) {
                        $va_entity_ids[] = $qr_res->get('entity_id');
                }
                return $va_entity_ids;
        }

        # ------------------------------------------------------


?>
