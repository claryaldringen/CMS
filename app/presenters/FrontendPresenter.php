<?php

namespace cms;

use Nette\Application\BadRequestException;
use Nette\Application\Responses\FileResponse;
use Nette\Application\UI;

class FrontendPresenter extends UI\Presenter{

	protected $languageId;

	protected $languages = array();

	protected $data = array();

	public function startup()
	{
		parent::startup();
		$this->languages = $this->context->getService('languageModel')->getUsedLanguages($this->context->parameters['siteId']);
		if(empty($this->getSession('cms')->languageId)) {
			$language = $this->context->getByType('Nette\Http\Request')->detectLanguage(array_keys($this->languages));
			$this->getSession('cms')->languageId = isset($languages[$language]) ? $languages[$language] : $this->context->parameters['language'];
		}
		$this->languageId = $this->getSession('cms')->languageId;
	}

	public function  actionAdmin() {
		$host = implode('.', array_slice(explode('.', $this->context->getByType('Nette\Http\Request')->getUrl()->host), -2));
		$this->context->getByType('Nette\Http\Response')->redirect('http://admin.' . $host);
	}

	public function actionDownload($hash) {

		if(!file_exists($this->context->parameters['tempDir'] . '/' . $hash)) {
			file_put_contents($this->context->parameters['tempDir'] . '/' . $hash, file_get_contents('http://' . $this->context->parameters['adminHost'] . '/userfiles/' . $hash));
		}
		$file = $this->context->getService('galleryModel')->setLanguage($this->languageId)->getFileByHash($hash);
		$response = new FileResponse($this->context->parameters['tempDir'] . '/' . $hash, $file['name']);
		$this->sendResponse($response);
	}

	protected function baseRender($url) {

		$menuModel =  $this->context->getService('menuModel');

		$menu = $menuModel->getMenu($this->languageId);

		if(!empty($url)) {
			$item = $menuModel->getMenuByUrl($url, array_values($this->languages));
		} elseif(isset($menu[0]['items'][0])) {
			$menuModel->setLanguage($this->languageId);
			$item = $menu[0]['items'][0];
		} else {
			$item = array('id' => 0,'type_id' => NULL, 'text' => '');
		}

		$menu = $menuModel->getMenu();
		$this->template->items = reset($menu);
		$this->template->typeId = $item['type_id'];
		$this->template->title = $item['text'];
		$this->template->menuId = $item['id'];
		$this->template->item = $item;
		$this->template->adminHost = $this->context->parameters['adminHost'];
		return $item;
	}

	public function singlePageRender() {
		$menuModel =  $this->context->getService('menuModel');
		$menu = $menuModel->getMenu($this->languageId);
		foreach($menu[0]['items'] as $item) {
			if($item['visibility'] == 'visible') {
				$this->renderContainers($item, NULL);
			}
		}

		$this->template->isSinglePage = true;
		$this->template->items = reset($menu);
		$this->template->title = '';
		$this->template->adminHost = $this->context->parameters['adminHost'];
	}

	public function renderDefault($url) {
		$params = $this->context->getParameters();
		if(!empty($params['isSinglePage'])) {
			$this->singlePageRender();
		} else {
			$this->renderContainers($this->baseRender($url), $url);
		}
		$this->template->data = $this->data;
	}

	protected function renderContainers($item, $url) {
		if($item['type_id'] == 1) {
			$this->renderText($item, $url);
		} elseif($item['type_id'] == 2) {
			$this->renderGallery($item, $url);
		} elseif($item['type_id'] == 3) {
			$this->renderArticles($item, $url);
		} elseif($item['type_id'] == 4) {
			$this->renderDiscography($item, $url);
		} elseif($item['type_id'] == 5) {
			foreach($item['items'] as $itm) {
				$this->renderContainers($itm, $url);
			}
		} elseif($item['type_id'] == 6) {
			$this->renderDiscussion($item, $url);
		}
	}

	protected function renderText($item, $url) {
		$this->data[$item['id']]['text'] = $this->context->getService('pageModel')->setLanguage($this->languageId)->getPage($item['id']);
	}

	protected function renderGallery($item, $url) {
		if(empty($item['path'])) $item['path'] = array();
		$data['folder'] = $this->context->getService('galleryModel')->setLanguage($this->languageId)->getFoldersByPath($item['id'], $item['path']);
		if(empty($data['folder'])) throw new BadRequestException('Gallery not found.');
		$data['url'] = $url;
		$this->data[$item['id']] = $data;
	}

	protected function renderArticles($item, $url) {
		$model = $this->context->getService('articleModel')->setLanguage($this->languageId);
		if(empty($item['path'])) {
			$this->data[$item['id']]['articles'] = $model->getArticles($item['id']);
			$this->data[$item['id']]['length'] = $model->getSetting($item['id'])->length;
		} else {
			$this->data[$item['id']]['article'] = $model->getArticleByPath($item['path']);
		}
	}

	protected function renderDiscography($item, $url) {
		if(empty($item['path'])) {
			$this->data[$item['id']]['albums'] =  $this->context->getService('albumModel')->setLanguage($this->languageId)->getAlbums($item['id']);
		} else {
			$this->data[$item['id']]['album'] =  $this->context->getService('albumModel')->setLanguage($this->languageId)->getAlbumByPath($item['path']);
		}
	}

	protected function renderDiscussion($item, $url) {
		$this->renderText($item, $url);
	}

	public function renderSitemap() {
		$this->template->host = $this->context->getByType('Nette\Http\Request')->getUrl()->hostUrl;
		$params = $this->context->getParameters();
		if(empty($params['isSinglePage'])) {
			$this->template->sitemap = $this->context->getService('menuModel')->getSitemap();
		} else {
			$this->template->sitemap = ['' => 0];
		}
	}

}
