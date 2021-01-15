<?php

declare(strict_types=1);

namespace Baraja\Markdown;


use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use League\CommonMark\Extension\ExtensionInterface;
use League\CommonMark\Extension\Table\TableExtension;

final class ConverterAccessor
{
	/** @var mixed[] */
	private array $config;

	/** @var ExtensionInterface[] */
	private array $extensions;


	/**
	 * @param mixed[] $config
	 * @param ExtensionInterface[] $extensions
	 */
	public function __construct(array $config = [], array $extensions = [])
	{
		$this->config = $config;
		$this->extensions = array_merge($extensions, [
			new TableExtension,
		]);
	}


	public function get(): CommonMarkConverter
	{
		static $cache;
		if ($cache === null) {
			$cache = $this->createInstance();
		}

		return $cache;
	}


	private function createInstance(): CommonMarkConverter
	{
		$environment = Environment::createCommonMarkEnvironment();
		foreach ($this->extensions as $extension) {
			$environment->addExtension($extension);
		}

		return new CommonMarkConverter($this->config, $environment);
	}
}
