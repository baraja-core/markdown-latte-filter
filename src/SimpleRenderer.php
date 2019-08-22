<?php

declare(strict_types=1);

namespace Baraja\Markdown;


class SimpleRenderer implements Renderer
{

	/**
	 * @param string $content
	 * @return string
	 */
	public function render(string $content): string
	{
		return htmlspecialchars($content, ENT_NOQUOTES | ENT_IGNORE, 'UTF-8');
	}

}