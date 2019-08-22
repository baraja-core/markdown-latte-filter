<?php

declare(strict_types=1);

namespace Baraja\Markdown;


class InvalidRouteException extends \Exception
{

	/**
	 * @param string $pattern
	 * @throws InvalidRouteException
	 */
	public static function pattern(string $pattern): void
	{
		throw new self(
			'Invalid link. [' . htmlspecialchars($pattern) . '], '
			. 'use format [Presenter:action] or [Module:Presenter:action].'
		);
	}

}