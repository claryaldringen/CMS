<?php

namespace cms;

class AlbumModel extends BaseModel{

	public function getAlbums($menuId) {
		$sql = "SELECT a.id,text AS name,url,CONCAT(i.hash,'.',i.mime) AS file,year,price  FROM album a
			JOIN name_has_text nht ON nht.name_id=a.name_id AND nht.language_id=%i
			JOIN text t ON t.id=nht.text_id
			JOIN image i ON i.id=a.image_id
			WHERE a.menu_id=%i
			ORDER BY [year] DESC";

		return $this->db->query($sql, $this->languageId, $menuId)->fetchAll();
	}

	public function getAlbumByPath($url) {
		$sql = "SELECT
				a.id,t.text AS name,year,CONCAT(i.hash, '.', i.mime) AS file,link,t2.text
			FROM album a
			JOIN name_has_text nht ON nht.name_id=a.name_id AND nht.language_id=%i
			JOIN text t ON t.id=nht.text_id
			JOIN name_has_text nht2 ON nht2.name_id=a.text_name_id AND nht2.language_id=%i
			JOIN text t2 ON t2.id=nht2.text_id
			JOIN image i ON i.id=a.image_id
			WHERE t.url = %s";

		$album = $this->db->query($sql, $this->languageId, $this->languageId, $url)->fetch();

		$sql = "SELECT t.text AS name,t2.text,i.hash AS file,link FROM song s
			JOIN name_has_text nht ON nht.name_id=s.name_id AND nht.language_id=%i
			JOIN text t ON t.id=nht.text_id
			LEFT JOIN image i ON i.id=s.image_id
			LEFT JOIN name_has_text nht2 ON nht2.name_id=s.text_name_id AND nht.language_id=%i
			LEFT JOIN text t2 ON t2.id=nht2.text_id
			WHERE s.album_id=%i
			ORDER BY s.sort_key";

		$album->songs = $this->db->query($sql, $this->languageId, $this->languageId, $album->id)->fetchAll();

		return $album;
	}

	public function getAlbum($ids) {

		if(!is_array($ids)) $ids = array($ids);

		$sql = "SELECT a.id,t.text AS name,year,CONCAT(i.hash, '.', i.mime) AS file,link,price FROM album a
			JOIN name_has_text nht ON nht.name_id=a.name_id AND nht.language_id=%i
			JOIN text t ON t.id=nht.text_id
			JOIN image i ON i.id=a.image_id
			WHERE a.id IN %in";

		return $this->db->query($sql, $this->languageId, $ids)->fetchAll();
	}

}
