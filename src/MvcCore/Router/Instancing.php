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

namespace MvcCore\Router;

trait Instancing
{
	/**
	 * Get singleton instance of `\MvcCore\Router` stored always here.
	 * Optionally set routes as first argument.
	 * Create proper router instance type at first time by
	 * configured class name in `\MvcCore\Application` singleton.
	 *
	 * Routes could be defined in various forms:
	 * Example:
	 *	`\MvcCore\Router::GetInstance([
	 *		"Products:List"	=> "/products-list/<name>/<color>",
	 *	]);`
	 * or:
	 *	`\MvcCore\Router::GetInstance([
	 *		'products_list'	=> [
	 *			"pattern"			=> "/products-list/<name>/<color>",
	 *			"controllerAction"	=> "Products:List",
	 *			"defaults"			=> array("name" => "default-name",	"color" => "red"),
	 *			"constraints"		=> array("name" => "[^/]*",			"color" => "[a-z]*")
	 *		]
	 *	]);`
	 * or:
	 *	`\MvcCore\Router::GetInstance([
	 *		new Route(
	 *			"/products-list/<name>/<color>",
	 *			"Products:List",
	 *			array("name" => "default-name",	"color" => "red"),
	 *			array("name" => "[^/]*",		"color" => "[a-z]*")
	 *		)
	 *	]);`
	 * or:
	 *	`\MvcCore\Router::GetInstance([
	 *		new Route(
	 *			"name"			=> "products_list",
	 *			"pattern"		=> "#^/products\-list/(?<name>[^/]*)/(?<color>[a-z]*)(?=/$|$)#",
	 *			"reverse"		=> "/products-list/<name>/<color>",
	 *			"controller"	=> "Products",
	 *			"action"		=> "List",
	 *			"defaults"		=> array("name" => "default-name",	"color" => "red"),
	 *		)
	 *	]);`
	 * @param \MvcCore\Route[]|array $routes Keyed array with routes,
	 *										 keys are route names or route
	 *										`Controller::Action` definitions.
	 * @param bool $autoInitialize If `TRUE`, locale routes array is cleaned and 
	 *							   then all routes (or configuration arrays) are 
	 *							   sent into method `$router->AddRoutes();`, 
	 *							   where are routes auto initialized for missing 
	 *							   route names or route controller or route action
	 *							   record, completed always from array keys.
	 *							   You can you `FALSE` to set routes without any 
	 *							   change or auto-initialization, it could be useful 
	 *							   to restore cached routes etc.
	 * @return \MvcCore\Router
	 */
	public static function & GetInstance (array $routes = [], $autoInitialize = TRUE) {
		if (!self::$instance) {
			/** @var $app \MvcCore\Application */
			$app = & \MvcCore\Application::GetInstance();
			self::$routeClass = $app->GetRouteClass();
			self::$toolClass = $app->GetToolClass();
			$routerClass = $app->GetRouterClass();
			$instance = new $routerClass($routes, $autoInitialize);
			$instance->application = & $app;
			self::$instance = & $instance;
		}
		return self::$instance;
	}

	/**
	 * Create router as every time new instance,
	 * no singleton instance management here.
	 * optionally set routes as first argument.
	 * If there is no name configured in route array configuration,
	 * set route name by given `$routes` array key, if key is not numeric.
	 *
	 * Routes could be defined in various forms:
	 * Example:
	 *	`new \MvcCore\Router([
	 *		"Products:List"	=> "/products-list/<name>/<color>",
	 *	]);`
	 * or:
	 *	`new \MvcCore\Router([
	 *		'products_list'	=> [
	 *			"pattern"			=> "/products-list/<name>/<color>",
	 *			"controllerAction"	=> "Products:List",
	 *			"defaults"			=> ["name" => "default-name",	"color" => "red"],
	 *			"constraints"		=> ["name" => "[^/]*",			"color" => "[a-z]*"]
	 *		]
	 *	]);`
	 * or:
	 *	`new \MvcCore\Router(array(
	 *		new Route(
	 *			"/products-list/<name>/<color>",
	 *			"Products:List",
	 *			["name" => "default-name",	"color" => "red"],
	 *			["name" => "[^/]*",		"color" => "[a-z]*"]
	 *		)
	 *	);`
	 * or:
	 *	`new \MvcCore\Router([
	 *		new Route(
	 *			"name"			=> "products_list",
	 *			"pattern"		=> "#^/products\-list/(?<name>[^/]*)/(?<color>[a-z]*)(?=/$|$)#",
	 *			"reverse"		=> "/products-list/<name>/<color>",
	 *			"controller"	=> "Products",
	 *			"action"		=> "List",
	 *			"defaults"		=> ["name" => "default-name",	"color" => "red"],
	 *		)
	 *	]);`
	 * @param \MvcCore\Route[]|array $routes Keyed array with routes,
	 *										 keys are route names or route
	 *										`Controller::Action` definitions.
	 * @param bool $autoInitialize If `TRUE`, locale routes array is cleaned and 
	 *							   then all routes (or configuration arrays) are 
	 *							   sent into method `$router->AddRoutes();`, 
	 *							   where are routes auto initialized for missing 
	 *							   route names or route controller or route action
	 *							   record, completed always from array keys.
	 *							   You can you `FALSE` to set routes without any 
	 *							   change or auto-initialization, it could be useful 
	 *							   to restore cached routes etc.
	 * @return void
	 */
	public function __construct (array $routes = [], $autoInitialize = TRUE) {
		if ($routes) $this->SetRoutes($routes, NULL, $autoInitialize);
	}
}
