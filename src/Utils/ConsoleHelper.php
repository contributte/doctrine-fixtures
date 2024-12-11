<?php declare(strict_types = 1);

namespace Nettrine\Fixtures\Utils;

class ConsoleHelper
{

	public static function bool(mixed $value): bool
	{
		return filter_var($value, FILTER_VALIDATE_BOOLEAN);
	}

	public static function stringNull(mixed $value): ?string
	{
		if ($value === null || $value === '') {
			return null;
		}

		if (!is_scalar($value)) {
			return null;
		}

		return (string) $value;
	}

	/**
	 * @return array<string>
	 */
	public static function arrayString(mixed $value): array
	{
		if ($value === null || $value === '') {
			return [];
		}

		return is_array($value) ? $value : []; // @phpstan-ignore-line
	}

}
