<?php

namespace cms;

use Nette\Utils\Strings;

class ArticleModel extends BaseModel {

	protected $lengths = array();

	public function getArticles($menuId, $offset = 0) {

		$sql = "SELECT a.id,text,url FROM article a
			JOIN name_has_text nht ON nht.name_id=a.name_id AND language_id=%i
			JOIN text t ON t.id=nht.text_id
			WHERE menu_id=%i
			ORDER BY a.sort ASC,a.id DESC
			LIMIT %i,8";

		$rows = $this->db->query($sql, $this->languageId, $menuId, $offset)->fetchAll();
		$length = $this->getLength($menuId);
		if(!empty($length)) {
			foreach ($rows as &$row) {
				$row->text = Html::trim($row->text, $length);
			}
		}
		return $rows;
	}

	public function getLength($menuId) {
		if(!isset($this->lengths[$menuId])) $this->lengths[$menuId] = $this->db->query("SELECT [length] FROM article_setting WHERE menu_id=%i", $menuId)->fetchSingle();
		return $this->lengths[$menuId];
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
