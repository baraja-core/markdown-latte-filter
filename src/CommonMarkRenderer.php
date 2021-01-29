<?php

declare(strict_types=1);

namespace Baraja\Markdown;


use Nette\Application\LinkGenerator;

final class CommonMarkRenderer extends BaseRenderer
{

	/** @var string[] */
	private static array $helpers = [
		'\(' => 'LATEX-L',
		'\)' => 'LATEX-R',
	];

	private ConverterAccessor $commonMarkConverter;


	public function __construct(ConverterAccessor $converterAccessor, LinkGenerator $linkGenerator)
	{
		parent::__construct($linkGenerator);
		$this->commonMarkConverter = $converterAccessor;
	}


	public function render(string $content): string
	{
		static $cache = [];
		if (isset($cache[$content]) === false) {
			$html = $this->process(
				$this->commonMarkConverter->get()->convertToHtml(
					$this->beforeProcess($content),
				),
			);

			$baseUrl = $this->resolveBaseUrl();
			$html = preg_replace_callback(
				'/src="\/?((?:img|static)\/([^"]+))"/',
				static fn (array $match): string => 'src="' . $baseUrl . '/' . $match[1] . '"',
				$this->afterProcess($html),
			);

			$cache[$content] = $html;
		}

		return $cache[$content];
	}


	private function beforeProcess(string $haystack): string
	{
		foreach (self::$helpers as $key => $value) {
			$haystack = str_replace($key, $value, $haystack);
		}

		return $haystack;
	}


	private function afterProcess(string $haystack): string
	{
		foreach (self::$helpers as $key => $value) {
			$haystack = str_replace($value, $key, $haystack);
		}

		return $haystack;
	}
}
