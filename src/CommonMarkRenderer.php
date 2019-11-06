<?php

declare(strict_types=1);

namespace Baraja\Markdown;


use Nette\Application\LinkGenerator;

class CommonMarkRenderer extends BaseRenderer
{

	/**
	 * @var string[]
	 */
	private static $helpers = [
		'\(' => 'LATEX-L',
		'\)' => 'LATEX-R',
	];

	/**
	 * @var ConverterAccessor
	 */
	private $commonMarkConverter;

	/**
	 * @param ConverterAccessor $converterAccessor
	 * @param LinkGenerator $linkGenerator
	 */
	public function __construct(ConverterAccessor $converterAccessor, LinkGenerator $linkGenerator)
	{
		parent::__construct($linkGenerator);
		$this->commonMarkConverter = $converterAccessor;
	}

	/**
	 * @param string $content
	 * @return string
	 */
	public function render(string $content): string
	{
		static $cache = [];

		if (isset($cache[$content]) === false) {
			$html = $this->process(
				$this->commonMarkConverter->get()->convertToHtml(
					$this->beforeProcess($content)
				)
			);

			$html = preg_replace_callback(
				'/src="\/?(static\/([^"]+))"/',
				function (array $match): string {
					return 'src="' . Helpers::getBaseUrl() . '/' . $match[1] . '"';
				},
				$this->afterProcess($html)
			);

			$cache[$content] = $html;
		}

		return $cache[$content];
	}

	/**
	 * @param string $haystack
	 * @return string
	 */
	private function beforeProcess(string $haystack): string
	{
		foreach (self::$helpers as $key => $value) {
			$haystack = str_replace($key, $value, $haystack);
		}

		return $haystack;
	}

	/**
	 * @param string $haystack
	 * @return string
	 */
	private function afterProcess(string $haystack): string
	{
		foreach (self::$helpers as $key => $value) {
			$haystack = str_replace($value, $key, $haystack);
		}

		return $haystack;
	}

}
