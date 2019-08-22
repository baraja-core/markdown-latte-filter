<?php

declare(strict_types=1);

namespace Baraja\Markdown;


class MarkdownException extends \Exception
{

	/**
	 * @throws MarkdownException
	 */
	public static function translatorDoesNotSet(): void
	{
		throw new self(
			'Translator service does not set. Did you call ->setTranslator()?'
		);
	}

}