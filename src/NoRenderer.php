<?php

declare(strict_types=1);

namespace Baraja\Markdown;


final class NoRenderer implements Renderer
{
	public function render(string $content): string
	{
		return $content;
	}
}
