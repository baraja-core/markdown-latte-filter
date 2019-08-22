<?php

declare(strict_types=1);

namespace Baraja\Markdown;


interface Renderer
{

	/**
	 * @param string $content
	 * @return string
	 */
	public function render(string $content): string;

}