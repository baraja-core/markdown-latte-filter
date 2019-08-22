<?php

declare(strict_types=1);

namespace Baraja\Markdown;


class NoRenderer implements Renderer
{

	/**
	 * @param string $content
	 * @return string
	 */
	public function render(string $content): string
	{
		return $content;
	}

}