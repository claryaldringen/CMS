<?php

namespace cms;

class LanguageModel extends BaseModel{

	public function getUsedLanguages($siteId) {
		$sql = "SELECT iso,l.id FROM menu m
			JOIN name_has_text nht ON m.name_id=nht.name_id
			JOIN language l ON l.id=nht.language_id
			WHERE site_id=%i
			GROUP BY l.id";

		return $this->db->query($sql, $siteId)->fetchPairs();
	}

}