<?php

namespace cms;

use Nette;
use Tracy\ILogger;


class ErrorPresenter implements Nette\Application\IPresenter
{
	/** @var ILogger */
	private $logger;


	public function __construct(ILogger $logger)
	{
		$this->logger = $logger;
	}


	/**
	 * @return Nette\Application\IResponse
	 */
	public function run(Nette\Application\Request $request)
	{
		$e = $request->getParameter('exception');

		if ($e instanceof Nette\Application\BadRequestException) {
			return new Nette\Application\Responses\ForwardResponse($request->setPresenterName('Error4xx'));
		}

		$this->logger->log($e, ILogger::EXCEPTION);
		return new Nette\Application\Responses\CallbackResponse(function () {
			require '../app/templates/Error4xx/500.latte';
		});

	}

}