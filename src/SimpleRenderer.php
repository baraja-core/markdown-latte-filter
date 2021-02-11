<?php

declare(strict_types=1);

namespace Baraja\Markdown;


final class SimpleRenderer implements Renderer
{
	public function render(string $content): string
	{
		return htmlspecialchars($content, ENT_NOQUOTES | ENT_IGNORE);
	}
}
