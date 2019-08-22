<?php

declare(strict_types=1);

namespace Baraja\Markdown;


use Nette\Application\LinkGenerator;
use Nette\Application\UI\InvalidLinkException;
use Nette\Localization\ITranslator;

abstract class BaseRenderer implements Renderer
{

	/**
	 * @var LinkGenerator
	 */
	private $linkGenerator;

	/**
	 * @var ITranslator|null
	 */
	private $translator;

	/**
	 * @param LinkGenerator $linkGenerator
	 */
	public function __construct(LinkGenerator $linkGenerator)
	{
		$this->linkGenerator = $linkGenerator;
	}

	/**
	 * @param ITranslator $translator
	 */
	public function setTranslator(ITranslator $translator): void
	{
		$this->translator = $translator;
	}

	/**
	 * @param string $content
	 * @return string
	 */
	protected function process(string $content): string
	{
		$content = (string) preg_replace(
			'/\{\$(?:basePath|baseUrl)\}\/?/',
			rtrim(Helpers::getBaseUrl(), '/') . '/',
			$content
		);

		$content = (string) preg_replace_callback(
			'/ href="\/(?<link>[^"]*)"/',
			function (array $match): string {
				return ' href="' . Helpers::getBaseUrl() . '/' . $match['link'] . '"';
			},
			$content
		);

		$content = (string) preg_replace_callback(
			'/n:href="(?<link>[^"]*)"/',
			function (array $match): string {
				try {
					$route = Route::createByPattern($match['link']);

					return 'href="' . $this->linkGenerator->link(
							$route->getPresenterName(true) . ':' . $route->getActionName(),
							$route->getParams()
						) . '"';
				} catch (InvalidLinkException|InvalidRouteException $e) {
					trigger_error($e->getMessage());

					return 'href="#INVALID_LINK"';
				}
			},
			$content
		);

		$content = (string) preg_replace_callback(
			'/(\{_\})(?<haystack>.+?)(\{\/_\})/', // {_}hello{/_}
			function (array $match): string {
				if ($this->translator === null) {
					MarkdownException::translatorDoesNotSet();
				}

				return $this->translator->translate($match['haystack']);
			},
			$content
		);

		$content = (string) preg_replace_callback(
			'/\{_(?:(?<haystack>.*?))\}/', // {_hello}, {_'hello'}, {_"hello"}
			function (array $match): string {
				if ($this->translator === null) {
					MarkdownException::translatorDoesNotSet();
				}

				return $this->translator->translate(trim($match['haystack'], '\'"'));
			},
			$content
		);

		return $content;
	}

}