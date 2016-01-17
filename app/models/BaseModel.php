<?php

namespace cms;

use Nette;

class BaseModel extends Nette\Object{

	/** @var DibiConnection */
	protected $db;

	protected $languageId = 1;

	public function __construct(\Dibi\Connection $db)
	{
		$this->db = $db;
	}

	public function setLanguage($language)
	{
		$this->languageId = $language;
		return $this;
	}
}