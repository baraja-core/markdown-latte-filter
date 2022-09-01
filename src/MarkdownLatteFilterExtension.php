<?php

declare(strict_types=1);

namespace Baraja\MarkdownLatteFilter;


use Nette\Bridges\ApplicationLatte\LatteFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\FactoryDefinition;

final class MarkdownLatteFilterExtension extends CompilerExtension
{
	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();

		/** @var FactoryDefinition $latteFactory */
		$latteFactory = $builder->getDefinitionByType(LatteFactory::class);
		$latteFactory->getResultDefinition()
			->addSetup('?->addFilter(?, ?)', ['@self', 'markdown', '@' . MarkdownFilter::class]);
	}
}
