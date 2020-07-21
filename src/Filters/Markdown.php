<?php

declare(strict_types=1);

namespace Baraja\Markdown\Filter;


use Baraja\Markdown\CommonMarkRenderer;
use Nette\Utils\Html;

final class Markdown
{

	/** @var CommonMarkRenderer */
	private $commonMarkRenderer;


	public function __construct(CommonMarkRenderer $commonMarkRenderer)
	{
		$this->commonMarkRenderer = $commonMarkRenderer;
	}


	/**
	 * @param string|object $haystack
	 * @return Html
	 */
	public function __invoke($haystack): Html
	{
		if (is_object($haystack) && method_exists($haystack, '__toString')) {
			$haystack = (string) $haystack;
		}

		return Html::el('div', ['class' => 'markdown'])->setHtml(
			$this->commonMarkRenderer->render($haystack)
		);
	}
}
