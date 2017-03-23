<?php
/**
 * Created by PhpStorm.
 * User: clary
 * Date: 23.3.17
 * Time: 10:24
 */

namespace cms;

use Nette\Application\UI;

class Error4xxPresenter extends UI\Presenter{

	public function renderDefault(Nette\Application\BadRequestException $exception) {
		$this->template->code = $exception->getCode();
	}

}
