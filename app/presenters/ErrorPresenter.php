<?php

declare(strict_types=1);

namespace App\Presenters;

use Nette;
use Nette\Application\Responses;
use Nette\Http;
use Tracy\ILogger;

final class ErrorPresenter implements Nette\Application\IPresenter
{
	use Nette\SmartObject;

	/** @var ILogger */
	private ILogger $logger;

	public function __construct(ILogger $logger)
	{
		$this->logger = $logger;
	}

	public function run(Nette\Application\Request $request): Nette\Application\IResponse
	{
		$exception = $request->getParameter('exception');

		if ($exception instanceof \Nette\Application\BadRequestException) {
			[$module, , $sep] = Nette\Application\Helpers::splitName($request->getPresenterName());

			return new Responses\ForwardResponse($request->setPresenterName($module . $sep . 'Error4xx'));
		}

		$this->logger->log($exception, ILogger::EXCEPTION);

		/**
		 * @return Nette\Application\IResponse
		 */
		return new Responses\CallbackResponse(
			static function (Http\IRequest $httpRequest, Http\IResponse $httpResponse): void {
				$header = $httpResponse->getHeader('Content-Type');
				if ($header !== null && preg_match('#^text/html(?:;|$)#', $header)) {
					require __DIR__ . '/templates/Error/500.phtml';
				}
			},
		);
	}
}
