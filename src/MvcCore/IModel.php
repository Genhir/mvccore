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

namespace MvcCore;

/**
 * Responsibility - static members for connections and by configuration,
 *					instances members for active record pattern.
 * - Reading `db` section from system `config.ini` file.
 * - Database `\PDO` connecting by config settings and index.
 * - Instance loaded variables initializing.
 * - Instance initialized values reading.
 * - Virtual calls/sets and gets handling.
 */
interface IModel
{
	/**
	 * Pass throught values with array keys conversion from underscored case
	 * into pascal case.
	 * @var int
	 */
	const KEYS_CONVERSION_UNDERSCORES_TO_PASCALCASE	= 0b00000001;
	
	/**
	 * Pass throught values with array keys conversion from underscored case
	 * into camel case.
	 * @var int
	 */
	const KEYS_CONVERSION_UNDERSCORES_TO_CAMELCASE	= 0b00000010;
	
	/**
	 * Pass throught values with array keys conversion from pascal case
	 * into underscored case.
	 * @var int
	 */
	const KEYS_CONVERSION_PASCALCASE_TO_UNDERSCORES	= 0b00000100;
	
	/**
	 * Pass throught values with array keys conversion from pascal case
	 * into camel case.
	 * @var int
	 */
	const KEYS_CONVERSION_PASCALCASE_TO_CAMELCASE	= 0b00001000;

	/**
	 * Pass throught values with array keys conversion from camel case
	 * into underscored case.
	 * @var int
	 */
	const KEYS_CONVERSION_CAMELCASE_TO_UNDERSCORES	= 0b00010000;

	/**
	 * Pass throught values with array keys conversion from camel case
	 * into pascal case.
	 * @var int
	 */
	const KEYS_CONVERSION_CAMELCASE_TO_PASCALCASE	= 0b00100000;
	
	/**
	 * Pass throught values with array keys case sensitive.
	 * @var int
	 */
	const KEYS_CONVERSION_CASE_SENSITIVE			= 0b01000000;
	
	/**
	 * Pass throught values with array keys case insensitive.
	 * @var int
	 */
	const KEYS_CONVERSION_CASE_INSENSITIVE			= 0b10000000;
	
	

	/**
	 * Collect all model class public and inherit field values into array.
	 * @param boolean $getNullValues			If `TRUE`, include also values with `NULL`s, by default - `FALSE`.
	 * @param boolean $includeInheritProperties If `TRUE`, include only fields from current model class and from parent classes.
	 * @param boolean $publicOnly			   If `TRUE`, include only public model fields.
	 * @return array
	 */
	public function GetValues (
		$getNullValues = FALSE,
		$includeInheritProperties = TRUE,
		$publicOnly = TRUE
	);

	/**
	 * Set up given `$data` items into `$this` instance context
	 * as typed properties by PHP doc comments, as properties
	 * with the same names as `$data` array keys. Case sensitively by default.
	 * Do not set any `$data` items, which are not declared in `$this` context.
	 * @param array   $data						Collection with data to set up
	 * @param int	  $keysConversionFlags		`\MvcCore\IModel::KEYS_CONVERSION_*` flags to process array keys conversion before set up into properties.
	 * @param bool    $completeInitialValues    Complete protected array `initialValues` to be able to compare them by calling method `GetTouched()` anytime later.
	 * @return \MvcCore\Model|\MvcCore\IModel
	 */
	public function & SetUp ($data = [], $keysConversionFlags = NULL);

	/**
	 * Get touched properties from initial moment called by `SetUp()` method.
	 * Get everything, what is different to `$this->initialValues` array.
	 * @param bool $includeInheritProperties 
	 * @param bool $publicOnly 
	 * @return array Keys are class properties names, values are changed values.
	 */
	public function GetTouched ($includeInheritProperties = TRUE, $publicOnly = TRUE);

	/**
	 * Returns (or creates and holds) instance from local store.
	 * @param mixed $args,... unlimited OPTIONAL variables to pass into model `__construct()` method.
	 * @return \MvcCore\IModel
	 */
	public static function & GetInstance ();

	/**
	 * Returns (or creates if necessary) model resource instance.
 	 * @param array|NULL	$args				Values array with variables to pass into resource `__construct()` method.
	 * @param string		$resourceClassPath	Automatically initialized with string replaced with `%SELF%` by `static::class` (or by `get_called_class()`).
	 * @return \MvcCore\Model|\MvcCore\IModel
	 */
	public static function GetResource (
		$args = [], $resourceClassPath = '%SELF%s\Resource'
	);

	/**
	 * Initialize `$this->config`, `$this->db` and `$this->resource` properties.
	 * If no `$connectionName` specified by first argument, return connection
	 * config by connection name defined first in `static::$connectionName`
	 * and if there is nothing, return connection config by connection name
	 * defined in `\MvcCore\Model::$connectionName`.
	 * @param string|int|NULL $connectionName Optional. If not set, there is used value from `static::$connectionName`.
	 * @return void
	 */
	public function Init ($connectionName = NULL);

	/**
	 * Returns `\PDO` database connection by connection name/index,
	 * usually by system config values (cached by local store)
	 * or create new connection of no connection cached.
	 * @param string|int|array|\stdClass|NULL $connectionNameOrConfig
	 * @param bool $strict	If `TRUE` and no connection under given name or given 
	 *						index found, exception is thrown. `FALSE` by default.
	 * @throws \InvalidArgumentException
	 * @return \PDO
	 */
	public static function GetDb ($connectionNameOrConfig = NULL);

	/**
	 * Get all known database connection config records as indexed/named array with `\stdClass` objects.
	 * Keys in array are connection config names/indexes and `\stdClass` values are config values.
	 * @return \stdClass[]
	 */
	public static function & GetConfigs ();

	/**
	 * Set all known configuration at once, optionally set default connection name/index.
	 * Example:
	 *	`\MvcCore\Model::SetConfigs(array(
	 *		// connection name: 'mysql-cdcol':
	 *		'mysql-cdcol'	=> array(
	 *			'driver'	=> 'mysql',
	 *			'host'		=> 'localhost',
	 *			'user'		=> 'root',
	 *			'password'	=> '1234',
	 *			'database'	=> 'cdcol',
	 *		),
	 *		// connection name: 'mssql-tests':
	 *		'mssql-tests' => array(
	 *			'driver'	=> 'sqlsrv',
	 *			'host'		=> '.\SQLEXPRESS',
	 *			'user'		=> 'sa',
	 *			'password'	=> '1234',
	 *			'database'	=> 'tests',
	 *		)
	 *	);`
	 * or:
	 *	`\MvcCore\Model::SetConfigs(array(
	 *		// connection index: 0:
	 *		array(
	 *			'driver'	=> 'mysql',
	 *			'host'		=> 'localhost',
	 *			'user'		=> 'root',
	 *			'password'	=> '1234',
	 *			'database'	=> 'cdcol',
	 *		),
	 *		// connection index: 1:
	 *		array(
	 *			'driver'	=> 'sqlsrv',
	 *			'host'		=> '.\SQLEXPRESS',
	 *			'user'		=> 'sa',
	 *			'password'	=> '1234',
	 *			'database'	=> 'tests',
	 *		)
	 *	);`
	 * @param \stdClass[]|array[] $configs Configuration array with `\stdClass` objects or arrays with configuration data.
	 * @return bool
	 */
	public static function SetConfigs (array $configs = []);

	/**
	 * Returns database connection config by connection index (integer)
	 * or by connection name (string) as `\stdClass` (cached by local store).
	 * @param int|string|NULL $connectionName
	 * @return \stdClass
	 */
	public static function & GetConfig ($connectionName = NULL);

	/**
	 * Set configuration array with optional connection name/index.
	 * If there is array key `name` or `index` inside config `array` or `\stdClass`,
	 * it's value is used for connection name or index or there is no param `$connectionName` defined.
	 * Example:
	 *	`\MvcCore\Model::SetConfig(array(
	 *		'name'		=> 'mysql-cdcol',
	 *		'driver'	=> 'mysql',		'host'		=> 'localhost',
	 *		'user'		=> 'root',		'password'	=> '1234',		'database' => 'cdcol',
	 *	));`
	 * or:
	 *	`\MvcCore\Model::SetConfig(array(
	 *		'index'		=> 0,
	 *		'driver'	=> 'mysql',	'host'		=> 'localhost',
	 *		'user'		=> 'root',	'password'	=> '1234',		'database' => 'cdcol',
	 *	));`
	 * or:
	 *	`\MvcCore\Model::SetConfig(array(
	 *		'driver'	=> 'mysql',	'host'		=> 'localhost',
	 *		'user'		=> 'root',	'password'	=> '1234',		'database' => 'cdcol',
	 *	), 'mysql-cdcol');`
	 * or:
	 *	`\MvcCore\Model::SetConfig(array(
	 *		'driver'	=> 'mysql',	'host'		=> 'localhost',
	 *		'user'		=> 'root',	'password'	=> '1234',		'database' => 'cdcol',
	 *	), 0);`
	 * @param \stdClass[]|array[] $config
	 * @param string|int|NULL $connectionName
	 * @return string|int
	 */
	public static function SetConfig (array $config = [], $connectionName = NULL);

	/**
	 * Sets any custom property `"PropertyName"` by `\MvcCore\IModel::SetPropertyName("value")`,
	 * which is not necessary to define previously or gets previously defined
	 * property `"PropertyName"` by `\MvcCore\IModel::GetPropertyName();`.
	 * Throws exception if no property defined by get call
	 * or if virtual call begins with anything different from `Set` or `Get`.
	 * This method returns custom value for get and `\MvcCore\IModel` instance for set.
	 * @param string $rawName
	 * @param array  $arguments
	 * @throws \Exception
	 * @return mixed|\MvcCore\IModel
	 */
	public function __call ($rawName, $arguments = []);

	/**
	 * Set any custom property, not necessary to previously defined.
	 * @param string $name
	 * @param mixed  $value
	 * @throws \InvalidArgumentException If name is `"autoInit" || "db" || "config" || "resource"`
	 * @return bool
	 */
	public function __set ($name, $value);

	/**
	 * Get any custom property, not necessary to previously defined,
	 * if property is not defined, NULL is returned.
	 * @param string $name
	 * @throws \InvalidArgumentException If name is `"autoInit" || "db" || "config" || "resource"`
	 * @return mixed
	 */
	public function __get ($name);

	/**
	 * Collect all properties names to serialize them by `serialize()` method.
	 * Collect all instance properties declared as private, protected and public 
	 * and if there is configured in `static::$protectedProperties` anything as 
	 * `TRUE` (under key by property name), also return those properties in 
	 * result array.
	 * @return \string[]
	 */
	public function __sleep ();

	/**
	 * Run `$this->Init()` method if there is `$this->autoInit` property defined 
	 * and if the property is `TRUE`.
	 * @return void
	 */
	public function __wakeup ();
}
