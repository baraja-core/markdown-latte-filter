<?php

declare(strict_types=1);

namespace Baraja\Markdown;


use League\CommonMark\Converter;
use League\CommonMark\DocParser;
use League\CommonMark\Environment;
use League\CommonMark\HtmlRenderer;
use Webuni\CommonMark\TableExtension\TableExtension;

final class ConverterAccessor
{
	public function get(): Converter
	{
		static $cache;
		if ($cache === null) {
			$cache = $this->createInstance();
		}

		return $cache;
	}


	private function createInstance(): Converter
	{
		$environment = Environment::createCommonMarkEnvironment()->addExtension(new TableExtension);

		return new Converter(
			new DocParser($environment),
			new HtmlRenderer($environment)
		);
	}
}
