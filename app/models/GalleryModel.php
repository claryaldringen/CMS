<?php

namespace cms;

use Nette\Application\BadRequestException;
use Tracy\Debugger;

class GalleryModel extends BaseModel{

	protected $gallery = array();

	protected $images = array();

	public function getFolders($menuId) {
		if(!isset($this->gallery[$menuId])) {
			$tree = $folderIds = array();

			$sql = "SELECT f.id,f.folder_id AS parent,t.text AS [name],t.url,sort_key,t2.text FROM [folder] f
			JOIN [menu_has_folder] mhf ON mhf.folder_id=f.id
			LEFT JOIN [name_has_text] nht ON f.name_id=nht.name_id AND language_id=%i
			LEFT JOIN [text] t ON t.id=nht.text_id
			LEFT JOIN [folder_has_name] fhn ON fhn.folder_id=f.id
			LEFT JOIN [name_has_text] nht2 ON nht2.name_id=fhn.name_id
			LEFT JOIN [text] t2 ON t2.id=nht2.text_id
			WHERE menu_id=%i ORDER BY f.folder_id";

			$rows = $this->db->query($sql, $this->languageId, $menuId)->fetchAll();
			foreach ($rows as $key => $row) {
				$folderIds[] = $row->id;
				if ($key == 0) {
					$tree[] = $row->toArray();
				} else {
					$this->addFolder($tree, $row->toArray());
				}
			}

			$sql = "SELECT
				i.id,
				IF(type = 'general', i.hash, CONCAT(i.hash, '.jpg')) AS file,
				folder_id AS parent,[text] AS name,
				type
			FROM [image] i
			JOIN [name_has_text] nht ON i.name_id=nht.name_id AND language_id=%i
			JOIN [text] t ON t.id=nht.text_id
			WHERE folder_id IN %in
			ORDER BY i.sort_key";

			$keys = array();
			$rows = $this->db->query($sql, $this->languageId, $folderIds)->fetchAll();
			foreach ($rows as $key => $row) {
				$keys[$row['id']] = $key;
				$this->addImage($tree, $row->toArray());
			}

			if(empty($tree[0]['folders']) && empty($tree[0]['images'])) throw new BadRequestException('Page not found', 404);
			if(!empty($tree[0]['folders'])) {
				foreach ($tree[0]['folders'] as &$folder) {
					if (empty($folder['images'])) {
						foreach ($this->images as $imageId => $image) {
							if (in_array($folder['id'], $image)) {
								$image = $rows[$keys[$imageId]];
								$image['temp'] = true;
								$folder['images'] = array($image);
								break;
							}
						}
					}
				}
			}

			$this->gallery[$menuId] = $tree;
		}
		return $this->gallery[$menuId];
	}

	private function addFolder(&$tree, $folder) {
		foreach($tree as &$branch) {
			if($branch['id'] == $folder['parent']) {
				if(!isset($branch['folders'])) {
					$branch['folders'] = array($folder);
				} else {
					$branch['folders'][] = $folder;
				}
				return true;
			} elseif(isset($branch['folders'])) {
				if($this->addFolder($branch['folders'], $folder)) return true;
			}
		}
		return false;
	}

	private function addImage(&$tree, $image) {
		foreach($tree as &$branch) {
			if($branch['id'] == $image['parent']) {
				if(!isset($branch['images'])) {
					$branch['images'] = array($image);
				} else {
					$branch['images'][] = $image;
				}
				if(!isset($this->images[$image['id']])) $this->images[$image['id']] = array();
				$this->images[$image['id']][] = $branch['id'];
				return true;
			} elseif(isset($branch['folders'])) {
				if($this->addImage($branch['folders'], $image)) {
					$this->images[$image['id']][] = $branch['id'];
					return true;
				}
			}
		}
		return false;
	}

	public function getFoldersByPath($menuId, $path) {
		$folders = $this->getFolders($menuId);
		return $this->search($folders[0], $path);
	}

	public function getFileByHash($hash) {
		$sql = "SELECT i.*,t.text AS name FROM [image] i
			JOIN name_has_text nht ON nht.name_id=i.name_id AND language_id=%i
			JOIN [text] t ON t.id=nht.text_id
			WHERE i.hash=%s";

		return $this->db->query($sql, $this->languageId, $hash)->fetch();
	}

	private function search($folder, $path) {
		$result = array();
		if(isset($folder['url'])) $part = array_shift($path);
		if(!isset($folder['url']) || $folder['url'] == $part) {
			if(empty($path)) {
				$result = $folder;
			} else {
				foreach($folder['folders'] as $item) {
					$result = $this->search($item, $path);
					if(!empty($result)) break;
				}
			}
		}
		return $result;
	}

}
