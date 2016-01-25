<?php

namespace cms;

class MenuModel extends BaseModel{

	protected $menu;

	protected $siteId;

	/** @var  Library */
	protected $library;

	public function __construct(\Dibi\Connection $db, Library $library, $siteId) {
		parent::__construct($db);
		$this->siteId = $siteId;
		$this->library = $library;
	}

	public function getMenu($languageId = null) {
		if(empty($languageId)) $languageId = $this->languageId;
		if(!isset($this->menu[$languageId])) {
			$sql = "SELECT m.id,m.menu_id,m.type_id,t.text,t.url,visibility,IF(url IS NULL AND sort = 0, 255, sort) AS sort
 			FROM [menu] m
			LEFT JOIN [name_has_text] nht ON m.name_id=nht.name_id AND language_id=%i
			LEFT JOIN [text] t ON t.id=nht.text_id
			WHERE site_id=%i
			ORDER BY m.visibility,[sort],[id]";

			$rows = $this->db->query($sql, $languageId, $this->siteId)->fetchAll();
			foreach($rows as &$row) $row['menu_id'] = (int)$row['menu_id'];
			array_unshift($rows, new \Dibi\Row(array('id' => 0, 'menu_id' => null)));
			$tree = $this->library->convertToTree($rows, 'id', 'menu_id', 'items');
			$tree[0]['items'] = $this->library->removeKeys($tree[0]['items'], 'items');
			$this->menu[$languageId] = $tree;
		}
		return $this->menu[$languageId];
	}

	public function getMenuByUrl($url, $languageIds) {
		foreach($languageIds as $languageId) {
			$menu = $this->getMenu($languageId);
			$result = $this->search($menu[0], explode('/', $url));
			if(!empty($result)) {
				$this->languageId = $languageId;
				return $result;
			}
		}
	}

	private function search($menu, $urlParts) {
		$result = array();
		if(isset($menu['url'])) $urlPart = array_shift($urlParts);
		if(!isset($menu['url']) || $menu['url'] == $urlPart) {
			if(empty($urlParts)) {
				$result = $menu;
			} else {
				foreach ($menu['items'] as $item) {
					$result = $this->search($item, $urlParts);
					if (!empty($result)) break;
				}
				if(empty($result) && isset($menu['type_id']) && in_array($menu['type_id'], array(2,3,4))) {
					$result = $menu;
					$result['path'] = $urlParts;
				}
			}
		}

		return $result;
	}

}
