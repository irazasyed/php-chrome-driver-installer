<?php

namespace Irazasyed\PHP;

class OperatingSystem
{
	/**
	 * Returns the current OS identifier.
	 *
	 * @return string
	 */
	public static function id(): string {
		if ( static::onWindows() ) {
			return 'win';
		}

		if ( static::onMac() ) {
			return static::macArchitectureId();
		}

		return 'linux';
	}

	/**
	 * Determine if the operating system is Windows or Windows Subsystem for Linux.
	 *
	 * @return bool
	 */
	public static function onWindows(): bool {
		return PHP_OS === 'WINNT' || str_contains(php_uname(), 'Microsoft');
	}

	/**
	 * Determine if the operating system is macOS.
	 *
	 * @return bool
	 */
	public static function onMac(): bool {
		return PHP_OS === 'Darwin';
	}

	/**
	 * Mac platform architecture.
	 *
	 * @return string
	 */
	public static function macArchitectureId(): string {
		return match ( php_uname( 'm' ) ) {
			'arm64' => 'mac-arm',
			'x86_64' => 'mac-intel',
			default => 'mac',
		};
	}
}
