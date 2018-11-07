<?php

/**
 * MvcCore
 *
 * This source file is subject to the BSD 3 License
 * For the full copyright and license information, please view
 * the LICENSE.md file that are distributed with this source code.
 *
 * @copyright	Copyright (c) 2016 Tom Flídr (https://github.com/mvccore/mvccore)
 * @license  https://mvccore.github.io/docs/mvccore/5.0.0/LICENCE.md
 */

namespace MvcCore\Tool;

trait Helpers
{
	/**
	 * Platform specific temporary directory.
	 * @var string|NULL
	 */
	protected static $tmpDir = NULL;

	/**
	 * Safely encode json string from php value.
	 * @param mixed $data
	 * @throws \Exception
	 * @return string
	 */
	public static function EncodeJson (& $data) {
		$flags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP |
			(defined('JSON_UNESCAPED_SLASHES') ? JSON_UNESCAPED_SLASHES : 0) |
			(defined('JSON_UNESCAPED_UNICODE') ? JSON_UNESCAPED_UNICODE : 0) |
			(defined('JSON_PRESERVE_ZERO_FRACTION') ? JSON_PRESERVE_ZERO_FRACTION : 0);
		$json = json_encode($data, $flags);
		if ($errorCode = json_last_error()) {
			throw new \RuntimeException("[".__CLASS__."] ".json_last_error_msg(), $errorCode);
		}
		if (PHP_VERSION_ID < 70100) {
			$json = strtr($json, [
				"\xe2\x80\xa8" => '\u2028',
				"\xe2\x80\xa9" => '\u2029',
			]);
		}
		return $json;
	}

	/**
	 * Safely decode json string into php `stdClass/array`.
	 * Result has always keys:
	 * - `"success"`	- decoding boolean success
	 * - `"data"`		- decoded json data as stdClass/array
	 * - `"errorData"`	- array with possible decoding error message and error code
	 * @param string $jsonStr
	 * @return object
	 */
	public static function DecodeJson (& $jsonStr) {
		$result = (object) [
			'success'	=> TRUE,
			'data'		=> null,
			'errorData'	=> [],
		];
		$jsonData = @json_decode($jsonStr);
		$errorCode = json_last_error();
		if ($errorCode == JSON_ERROR_NONE) {
			$result->data = $jsonData;
		} else {
			$result->success = FALSE;
			$result->errorData = [json_last_error_msg(), $errorCode];
		}
		return $result;
	}

	/**
	 * Returns the OS-specific directory for temporary files.
	 * @return string
	 */
	public static function GetTmpDir () {
		if (self::$tmpDir === NULL) {
			if (strtolower(substr(PHP_OS, 0, 3)) == 'win') {
				// Windows:
				$tmpDir = sys_get_temp_dir();
				$sysRoot = getenv('SystemRoot');
				// do not store anything directly in C:\Windows, use C\windows\Temp instead
				if (!$tmpDir || $tmpDir === $sysRoot) {
					$tmpDir = !empty($_SERVER['TEMP']) 
						? $_SERVER['TEMP']
						: (!empty($_SERVER['TMP'])
							? $_SERVER['TMP']
							: (!empty($_SERVER['WINDIR'])
								? $_SERVER['WINDIR'] . '/Temp'
								: $sysRoot . '/Temp'
							)
						);
				}
				$tmpDir = str_replace('\\', '/', $tmpDir);
			} else {
				// Other systems
				$tmpDir = !empty($_SERVER['TMPDIR']) 
					? $_SERVER['TMPDIR']
					: (!empty($_SERVER['TMP'])
						? $_SERVER['TMP']
						: '/tmp'
					);
			}
			self::$tmpDir = $tmpDir;
		}
		return self::$tmpDir;
	}
}