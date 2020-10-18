<?php

declare(strict_types=1);

namespace Baraja\Markdown;


use Baraja\Markdown\Filter\Markdown;
use Nette\Bridges\ApplicationLatte\ILatteFactory;
use Nette\DI\CompilerExtension;
use Nette\DI\Definitions\FactoryDefinition;

final class MarkdownLatteFilterExtension extends CompilerExtension
{
	public function beforeCompile(): void
	{
		$builder = $this->getContainerBuilder();

		$builder->addDefinition($this->prefix('renderer'))
			->setFactory(CommonMarkRenderer::class);

		$builder->addDefinition($this->prefix('markdown'))
			->setFactory(Markdown::class);

		$builder->addDefinition($this->prefix('converterAccessor'))
			->setFactory(ConverterAccessor::class);

		/** @var FactoryDefinition $latteFactory */
		$latteFactory = $builder->getDefinitionByType(ILatteFactory::class);
		$latteFactory->getResultDefinition()
			->addSetup('?->addFilter(?, ?)', ['@self', 'markdown', '@' . Markdown::class]);
	}
}
