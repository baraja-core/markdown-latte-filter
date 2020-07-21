<?php

declare(strict_types=1);

namespace Baraja\Markdown;


interface Renderer
{
	public function render(string $content): string;
}
