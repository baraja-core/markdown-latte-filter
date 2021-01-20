<?php

declare(strict_types=1);

namespace Baraja\Markdown;


final class Helpers
{

	/** @throws \Error */
	public function __construct()
	{
		throw new \Error('Class ' . get_class($this) . ' is static and cannot be instantiated.');
	}


	/**
	 * Sanitizes string for use inside href attribute.
	 */
	public static function safeUrl(string $s): string
	{
		if (strncmp(strtolower($s), 'http', 4) !== 0) {
			$s = 'http://' . $s;
		}

		return preg_match('~^(?:(?:https?|ftp)://[^@]+(?:/.*)?|(?:mailto|tel|sms):.+|[/?#].*|[^:]+)$~Di', $s) ? $s : '';
	}


	public static function parseDomain(string $url): string
	{
		$domainPattern = '/^(?:https?:\/\/)?(?<subdomain>[^\/]*?)(?<domain>localhost|(?:\d{1,3}\.?){4}|(?:(?:[a-z0-9-]+)\.(?:[a-z0-9-]+)))(?:\/|$)/';
		if (preg_match($domainPattern, strtolower($url), $parser)) {
			return (string) ($parser['domain'] ?? '');
		}

		throw new \InvalidArgumentException('URL "' . $url . '" is invalid.');
	}


	public static function escapeHtmlAttr(string $s): string
	{
		if (strpos($s, '`') !== false && strpbrk($s, ' <>"\'') === false) {
			$s .= ' '; // protection against innerHTML mXSS vulnerability nette/nette#1496
		}

		return htmlspecialchars($s, ENT_QUOTES | ENT_HTML5 | ENT_SUBSTITUTE, 'UTF-8');
	}
}
