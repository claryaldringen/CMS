<?php

namespace cms;

class PageModel extends BaseModel{

	public function  getPage($menuId) {
		$sql = "SELECT [text] FROM [page] p
			JOIN [name_has_text] nht ON p.name_id=nht.name_id
			JOIN [text] t ON t.id=nht.text_id
			WHERE menu_id=%i";

		return $this->db->query($sql, $menuId)->fetchSingle();
	}

}
