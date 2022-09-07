<?php

declare(strict_types=1);

namespace Baraja\MarkdownLatteFilter;


use Baraja\Markdown\Markdown;
use Nette\Utils\Html;

final class MarkdownFilter
{
	public function __construct(
		private Markdown $markdown,
	) {
	}


	public function __invoke(string|object $haystack): Html
	{
		if (is_object($haystack)) {
			if ($haystack instanceof \Stringable) {
				$haystack = (string) $haystack;
			} else {
				throw new \InvalidArgumentException(
					sprintf(
						'Value can not be converted to string, because object "%s" is not stringable.',
						$haystack::class,
					),
				);
			}
		}

		return Html::el('div', ['class' => 'markdown'])->setHtml(
			$this->markdown->render($haystack),
		);
	}
}
