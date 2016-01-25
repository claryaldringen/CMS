<?php

namespace cms;

class CommentModel extends BaseModel {

	/** @var Library */
	protected $library;

	public function __construct(\Dibi\Connection $db, Library $library)
	{
		parent::__construct($db);
		$this->library = $library;
	}

	public function getComments($menuId) {
		$rows = $this->db->query("SELECT * FROM comment WHERE menu_id=%i", $menuId)->fetchAll();
		foreach($rows as &$row) $row['comment_id'] = (int)$row['comment_id'];
		array_unshift($rows, new \Dibi\Row(array('id' => 0, 'comment_id' => null)));
		$tree = $this->library->convertToTree($rows, 'id', 'comment_id', 'comments');
		$tree[0]['comments'] = $this->library->removeKeys($tree[0]['comments'], 'comments');
		return $tree[0]['comments'];
	}

	public function setComment($comment) {
		if(isset($comment->id)) {
			$this->db->query("UPDATE comment SET [text]=%s, [name]=%s, caption=%s WHERE id=%i", $comment->text, $comment->name, $comment->caption, $comment->id);
		} else {
			$this->db->query("INSERT INTO comment", json_decode(json_encode($comment), true));
			return $this->db->getInsertId();
		}
	}

	public function removeComment($id) {
		$this->db->query("DELETE FROM comment WHERE id=%i", $id);
		return $this;
	}
}