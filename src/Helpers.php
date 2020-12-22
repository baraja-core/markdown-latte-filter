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
	 * Return current absolute URL.
	 * Return null, if current URL does not exist (for example in CLI mode).
	 */
	public static function getCurrentUrl(): ?string
	{
		if (!isset($_SERVER['REQUEST_URI'], $_SERVER['HTTP_HOST'])) {
			return null;
		}

		return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http')
			. '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
	}


	public static function getBaseUrl(): ?string
	{
		static $return;

		if ($return !== null) {
			return $return;
		}
		if (($currentUrl = self::getCurrentUrl()) !== null) {
			if (preg_match('/^(https?:\/\/.+)\/www\//', $currentUrl, $localUrlParser)) {
				$return = $localUrlParser[0];
			} elseif (preg_match('/^(https?:\/\/[^\/]+)/', $currentUrl, $publicUrlParser)) {
				$return = $publicUrlParser[1];
			}
		}
		if ($return !== null) {
			$return = rtrim($return, '/');
		}

		return $return;
	}


	/**
	 * Sanitizes string for use inside href attribute.
	 */
	public static function safeUrl(string $s): string
	{
		return preg_match('~^(?:(?:https?|ftp)://[^@]+(?:/.*)?|(?:mailto|tel|sms):.+|[/?#].*|[^:]+)$~Di', $s) ? $s : '';
	}


	public static function parseDomain(string $url): string
	{
		$domainPattern = '/^https?:\/\/(?<subdomain>[^\/]*?)(?<domain>localhost|(?:\d{1,3}\.?){4}|(?:(?:[a-z0-9-]+)\.(?:[a-z0-9-]+)))(?:\/|$)/';
		if (preg_match($domainPattern, $url, $parser)) {
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
