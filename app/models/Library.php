<?php

namespace cms;

class Library
{
	public function convertToTree(array $flat, $idField, $parentIdField,  $childNodesField) {
		$indexed = array();

		// first pass - get the array indexed by the primary id
		foreach ($flat as $row) {
			$indexed[$row[$idField]] = $row->toArray();
			$indexed[$row[$idField]][$childNodesField] = array();
		}

		//second pass
		$root = null;
		foreach ($indexed as $id => $row) {
			$indexed[$row[$parentIdField]][$childNodesField][$id] =& $indexed[$id];
			if ($row[$parentIdField] === null) {
				$root = $id;
			}
		}

		return array($root => $indexed[$root]);
	}

	public function removeKeys($items, $childNodesField) {
		foreach($items as $id => $item) {
			$items[$id][$childNodesField] = $this->removeKeys($item[$childNodesField], $childNodesField);
		}
		return array_values($items);
	}

}
