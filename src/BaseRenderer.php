<?php

declare(strict_types=1);

namespace Baraja\Markdown;


use Baraja\Url\Url;
use Nette\Application\LinkGenerator;
use Nette\Application\UI\InvalidLinkException;
use Nette\Localization\Translator;
use Nette\Utils\Strings;

abstract class BaseRenderer implements Renderer
{
	private LinkGenerator $linkGenerator;

	private ?Translator $translator;

	private ?string $baseUrl = null;

	/** @var string[] */
	private array $safeDomains = ['baraja.cz'];


	public function __construct(LinkGenerator $linkGenerator, ?Translator $translator = null)
	{
		$this->linkGenerator = $linkGenerator;
		$this->translator = $translator;
	}


	public function setTranslator(Translator $translator): void
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
			$content,
		);

		$content = (string) preg_replace_callback( // <a href="..."> HTML links
			'/ href="\/(?<link>[^"]*)"/',
			static fn (array $match): string => ' href="' . $baseUrl . '/' . $match['link'] . '"',
			$content,
		);

		$content = (string) preg_replace_callback( // n:href="..." Nette links
			'/n:href="(?<link>[^"]*)"/',
			function (array $match): string {
				try {
					$route = Route::createByPattern($match['link']);

					return 'href="' . $this->linkGenerator->link(
						$route->getPresenterName(true) . ':' . $route->getActionName(),
						$route->getParams(),
					) . '"';
				} catch (InvalidLinkException | \InvalidArgumentException $e) {
					trigger_error($e->getMessage());

					return 'href="#INVALID_LINK"';
				}
			},
			$content,
		);

		$content = (string) preg_replace_callback( // URL inside text
			'/(.|\n|^)((?i)\b(?:(?:https?:\/\/|www\d{0,3}[.]|[a-z0-9.\-]+[.][a-z]{2,4}\/)(?:[^\s()<>]+|\(([^\s()<>]+|(\([^\s()<>]+\)))*\))+(?:\(([^\s()<>]+|(\([^\s()<>]+\)))*\)|[^\s`!()\[\]{};:\'\\\\".,<>?«»“”‘’])))/u',
			function (array $match) use ($baseUrl): string {
				if ($match[1] === '"' || $match[1] === '\'') { // ignore inside <a href=" attribute
					return $match[0];
				}

				return $match[1] . $this->renderLink(htmlspecialchars_decode(trim($match[2] ?? '')), $baseUrl);
			},
			$content,
		);

		$content = (string) preg_replace_callback( // Translate macros
			'/(\{_\})(?<haystack>.+?)(\{\/_\})/', // {_}hello{/_}
			fn (array $match): string => $this->getTranslator()->translate($match['haystack']),
			$content,
		);

		$content = (string) preg_replace_callback( // Alternative translate macros
			'/\{_(?:(?<haystack>.*?))\}/', // {_hello}, {_'hello'}, {_"hello"}
			fn (array $match): string => $this->getTranslator()->translate(trim($match['haystack'], '\'"')),
			$content,
		);

		return $content;
	}


	protected function resolveBaseUrl(): string
	{
		return $this->baseUrl ?? Url::get()->getBaseUrl();
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
			. '>' . html_entity_decode(
				(string) preg_replace_callback('/^(https?:\/\/[^\/]+)(.*)$/', fn (array $part): string => $part[1] . Strings::truncate($part[2], 32), strip_tags($url)),
				ENT_QUOTES | ENT_HTML5,
				'UTF-8'
			)
			. '</a>';
	}


	private function getTranslator(): Translator
	{
		if ($this->translator === null) {
			throw new \RuntimeException('Translator service does not set. Did you call ->setTranslator()?');
		}

		return $this->translator;
	}
}
