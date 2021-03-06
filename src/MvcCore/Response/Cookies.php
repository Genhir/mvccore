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

namespace MvcCore\Response;

trait Cookies
{
	/**
	 * Send a cookie.
	 * @param string $name			Cookie name. Assuming the name is `cookiename`, this value is retrieved through `$_COOKIE['cookiename']`.
	 * @param string $value			The value of the cookie. This value is stored on the clients computer; do not store sensitive information.
	 * @param int    $lifetime		Life time in seconds to expire. 0 means "until the browser is closed".
	 * @param string $path			The path on the server in which the cookie will be available on. If set to '/', the cookie will be available within the entire domain.
	 * @param string $domain		If not set, value is completed by `\MvcCore\Application::GetInstance()->GetRequest()->GetHostName();` .
	 * @param bool   $secure		If not set, value is completed by `\MvcCore\Application::GetInstance()->GetRequest()->IsSecure();`.
	 * @param bool   $httpOnly		HTTP only cookie, `TRUE` by default.
	 * @throws \RuntimeException	If HTTP headers have been sent.
	 * @return bool					True if cookie has been set.
	 */
	public function SetCookie (
		$name, $value,
		$lifetime = 0, $path = '/',
		$domain = NULL, $secure = NULL, $httpOnly = TRUE
	) {
		if ($this->IsSent()) {
			$selfClass = version_compare(PHP_VERSION, '5.5', '>') ? self::class : __CLASS__;
			throw new \RuntimeException(
				"[".$selfClass."] Cannot set cookie after HTTP headers have been sent."
			);
		}
		$request = \MvcCore\Application::GetInstance()->GetRequest();
		return \setcookie(
			$name, $value,
			$lifetime === 0 ? 0 : time() + $lifetime,
			$path,
			$domain === NULL ? $request->GetHostName() : $domain,
			$secure === NULL ? $request->IsSecure() : $secure,
			$httpOnly
		);
	}

	/**
	 * Delete cookie - set value to empty string and
	 * set expiration to "until the browser is closed".
	 * @param string $name			Cookie name. Assuming the name is `cookiename`, this value is retrieved through `$_COOKIE['cookiename']`.
	 * @param string $path			The path on the server in which the cookie will be available on. If set to '/', the cookie will be available within the entire domain.
	 * @param string $domain		If not set, value is completed by `\MvcCore\Application::GetInstance()->GetRequest()->GetHostName();` .
	 * @param bool   $secure		If not set, value is completed by `\MvcCore\Application::GetInstance()->GetRequest()->IsSecure();`.
	 * @throws \RuntimeException	If HTTP headers have been sent.
	 * @return bool					True if cookie has been set.
	 */
	public function DeleteCookie ($name, $path = '/', $domain = NULL, $secure = NULL) {
		return $this->SetCookie($name, '', 0, $path, $domain, $secure);
	}
}
