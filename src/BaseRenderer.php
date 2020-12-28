<?php

declare(strict_types=1);

namespace Baraja\Markdown;


use Nette\Application\LinkGenerator;
use Nette\Application\UI\InvalidLinkException;
use Nette\Localization\ITranslator;

abstract class BaseRenderer implements Renderer
{
	private LinkGenerator $linkGenerator;

	private ?ITranslator $translator = null;

	private ?string $baseUrl = null;

	/** @var string[] */
	private array $safeDomains = ['baraja.cz'];


	public function __construct(LinkGenerator $linkGenerator)
	{
		$this->linkGenerator = $linkGenerator;
	}


	public function setTranslator(ITranslator $translator): void
	{
		$this->translator = $translator;
	}


	public function setBaseUrl(string $baseUrl): void
	{
		$this->baseUrl = $baseUrl;
	}


	/**
	 * @param string[] $safeDomains
	 */
	public function setSafeDomains(array $safeDomains): void
	{
		$this->safeDomains = $safeDomains;
	}


	protected function process(string $content): string
	{
		$baseUrl = $this->resolveBaseUrl();
		$content = (string) preg_replace(
			'/\{\$(?:basePath|baseUrl)\}\/?/',
			rtrim($baseUrl, '/') . '/',
			$content
		);

		$content = (string) preg_replace_callback( // <a href="..."> HTML links
			'/ href="\/(?<link>[^"]*)"/',
			static function (array $match) use ($baseUrl): string {
				return ' href="' . $baseUrl . '/' . $match['link'] . '"';
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

		$content = (string) preg_replace_callback( // URL inside text
			'/(?i)\b(?:(?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'\\\\".,<>?«»“”‘’]))/u',
			function (array $match) use ($baseUrl): string {
				return $this->renderLink($match[0] ?? '', $baseUrl);
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


	protected function resolveBaseUrl(): string
	{
		if ($this->baseUrl === null) {
			if (($baseUrl = Helpers::getBaseUrl()) === null) {
				throw new \LogicException(
					'BaseUrl can not be null.' . "\n"
					. 'To solve this issue: BaseUrl is automatically detected according to the current HTTP request. '
					. 'In CLI mode (when there is no HTTP request), you need to manually define the BaseUrl by the setBaseUrl() method.'
				);
			}
			$this->baseUrl = $baseUrl;
		}

		return $this->baseUrl;
	}


	protected function renderLink(string $url, string $baseUrl): string
	{
		$urlDomain = Helpers::parseDomain($url);
		$baseUrlDomain = Helpers::parseDomain($baseUrl);

		$external = $urlDomain !== $baseUrlDomain && !\in_array($urlDomain, $this->safeDomains, true);
		if (($safeUrl = Helpers::safeUrl($url)) === '') {
			trigger_error('Given URL is not safe, because string "' . $url . '" given.');
		}

		return '<a href="' . Helpers::escapeHtmlAttr($safeUrl) . '"'
			. ($external ? ' target="_blank" rel="nofollow"' : '')
			. '>' . html_entity_decode(strip_tags($url), ENT_QUOTES | ENT_HTML5, 'UTF-8') . '</a>';
	}


	private function getTranslator(): ITranslator
	{
		if ($this->translator === null) {
			throw new \RuntimeException('Translator service does not set. Did you call ->setTranslator()?');
		}

		return $this->translator;
	}
}
