<?php

declare(strict_types=1);

namespace Baraja\Markdown;


use Nette\Application\LinkGenerator;
use Nette\Application\UI\InvalidLinkException;
use Nette\Localization\ITranslator;

abstract class BaseRenderer implements Renderer
{
	private LinkGenerator $linkGenerator;

	private ?ITranslator $translator;


	public function __construct(LinkGenerator $linkGenerator)
	{
		$this->linkGenerator = $linkGenerator;
	}


	public function setTranslator(ITranslator $translator): void
	{
		$this->translator = $translator;
	}


	protected function process(string $content): string
	{
		$content = (string) preg_replace(
			'/\{\$(?:basePath|baseUrl)\}\/?/',
			rtrim(Helpers::getBaseUrl(), '/') . '/',
			$content
		);

		$content = (string) preg_replace_callback( // <a href="..."> HTML links
			'/ href="\/(?<link>[^"]*)"/',
			static function (array $match): string {
				return ' href="' . Helpers::getBaseUrl() . '/' . $match['link'] . '"';
			},
			$content
		);

		$content = (string) preg_replace_callback( // n:href="..." Nette links
			'/n:href="(?<link>[^"]*)"/',
			function (array $match): string {
				try {
					$route = Route::createByPattern($match['link']);

					return 'href="' . $this->linkGenerator->link(
							$route->getPresenterName(true) . ':' . $route->getActionName(),
							$route->getParams()
						) . '"';
				} catch (InvalidLinkException | \InvalidArgumentException $e) {
					trigger_error($e->getMessage());

					return 'href="#INVALID_LINK"';
				}
			},
			$content
		);

		$content = (string) preg_replace_callback( // Translate macros
			'/(\{_\})(?<haystack>.+?)(\{\/_\})/', // {_}hello{/_}
			function (array $match): string {
				return $this->getTranslator()->translate($match['haystack']);
			},
			$content
		);

		$content = (string) preg_replace_callback( // Alternative translate macros
			'/\{_(?:(?<haystack>.*?))\}/', // {_hello}, {_'hello'}, {_"hello"}
			function (array $match): string {
				return $this->getTranslator()->translate(trim($match['haystack'], '\'"'));
			},
			$content
		);

		return $content;
	}


	private function getTranslator(): ITranslator
	{
		if ($this->translator === null) {
			throw new \RuntimeException('Translator service does not set. Did you call ->setTranslator()?');
		}

		return $this->translator;
	}
}
