<?php

declare(strict_types=1);

namespace Baraja\Markdown\Filter;


use Baraja\Markdown\CommonMarkRenderer;
use Nette\Utils\Html;

final class Markdown
{
	public function __construct(
		private CommonMarkRenderer $commonMarkRenderer
	) {
	}


	public function __invoke(string|object $haystack): Html
	{
		if (is_object($haystack)) {
			if (method_exists($haystack, '__toString')) {
				$haystack = (string) $haystack;
			} else {
				throw new \InvalidArgumentException(
					'Value can not be converted to string, '
					. 'because object "' . $haystack::class . '" does not contain "__toString" method.',
				);
			}
		}

		return Html::el('div', ['class' => 'markdown'])->setHtml(
			$this->commonMarkRenderer->render($haystack),
		);
	}
}
