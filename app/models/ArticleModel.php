<?php

namespace cms;

class ArticleModel extends BaseModel {

	protected $setting = array();

	public function getArticles($menuId, $offset = 0) {



		$sql = "SELECT a.id,text,url FROM article a
			JOIN name_has_text nht ON nht.name_id=a.name_id AND language_id=%i
			JOIN text t ON t.id=nht.text_id
			WHERE menu_id=%i
			ORDER BY a.sort ASC,a.id DESC
			LIMIT %i,%i";

		$setting = $this->getSetting($menuId);

		$rows = $this->db->query($sql, $this->languageId, $menuId, $offset, $setting->count)->fetchAll();
		$length = $setting->length;
		if(!empty($length)) {
			foreach ($rows as &$row) {
				$row->text = Html::trim($row->text, $length);
			}
		}
		return $rows;
	}

	public function getSetting($menuId) {
		if(empty($this->setting[$menuId])) {
			$this->setting[$menuId] = $this->db->query("SELECT * FROM article_setting WHERE menu_id=%i", $menuId)->fetch();
			if(empty($this->setting[$menuId])) $this->setting[$menuId] = (object)array('length' => null, 'count' => 1000);
		}
		return $this->setting[$menuId];
	}

	public function getArticleByPath($url) {
		$sql = "SELECT t.text FROM article a
			JOIN name_has_text nht ON nht.name_id=a.name_id AND nht.language_id=%i
			JOIN text t ON t.id=nht.text_id AND t.url=%s";

		return $this->db->query($sql, $this->languageId, $url)->fetchSingle();
	}

	public function getArticleById($id) {
		$sql = "SELECT t.text FROM article a
			JOIN name_has_text nht ON nht.name_id=a.name_id AND nht.language_id=%i
			JOIN text t ON t.id=nht.text_id AND a.id=%i";

		return $this->db->query($sql, $this->languageId, $id)->fetchSingle();
	}
}
