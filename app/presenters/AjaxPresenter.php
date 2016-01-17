<?php

namespace cms;

use Nette\Application\UI;
use Tracy\Debugger;

class AjaxPresenter extends UI\Presenter{

	protected $languageId;

	public function renderDefault() {
		$languages = $this->context->getService('languageModel')->getUsedLanguages($this->context->parameters['siteId']);
		if(empty($this->getSession('cms')->languageId)) {
			$language = $this->context->getByType('Nette\Http\Request')->detectLanguage(array_keys($languages));
			$this->getSession('cms')->languageId = isset($languages[$language]) ? $languages[$language] : $this->context->parameters['language'];
		}
		$this->languageId = $this->getSession('cms')->languageId;
		$post = $this->request->post;
		try {
			$this->template->response = $this->{$post['action']}(json_decode($post['data']));
		} catch(Exception $ex) {
			$this->template->response = array('error' => $ex->getMessage() . 'on line ' . $ex->getLine() . ' at ' . $ex->getFile() . "\n\n" . $ex->getTraceAsString());
			Debugger::log($ex);
		}
	}

	protected function loadArticle($data) {
		return array_values($this->context->getService('articleModel')->setLanguage($this->languageId)->getArticles($data->menuId, $data->offset));
	}
	protected function loadComments($data) {
		return array('comments' => $this->context->getService('commentModel')->getComments($data->menuId));
	}

	protected function saveComment($data) {
		$id = $this->context->getService('commentModel')->setComment($data);
		$comments = $this->context->getService('commentModel')->getComments($data->menu_id);
		return array('comments' => $comments, 'id' => $id);
	}

	protected function removeComment($data) {
		$comments = $this->context->getService('commentModel')->removeComment($data->id)->getComments($data->menuId);
		return array('comments' => $comments);
	}

}