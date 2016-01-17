<?php

namespace cms;

class ArticleModel extends BaseModel {

	public function getArticles($menuId, $offset = 0) {
		$sql = "SELECT a.id,text FROM article a
			JOIN name_has_text nht ON nht.name_id=a.name_id AND language_id=%i
			JOIN text t ON t.id=nht.text_id
			WHERE menu_id=%i
			ORDER BY a.id DESC
			LIMIT %i,4";

		return $this->db->query($sql, $this->languageId, $menuId, $offset)->fetchPairs();
	}
}