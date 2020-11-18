<?php 
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$url_config = '/home/admin/web/wp.test.dataservix.com/public_html/wp-config.php';
$url_api_class = 'classes/class-dmrfid-api.php';
//$url_config = '/home/admin/web/wp.test.dataservix.com/public_html/wp-config.php';
include_once $url_config;
include_once $url_api_class;


use FelipheGomez\PhpCrudApi\Api;
use FelipheGomez\PhpCrudApi\Config;
use FelipheGomez\PhpCrudApi\RequestFactory;
use FelipheGomez\PhpCrudApi\ResponseUtils;

$config = new Config([
	'debug' => true,
	"driver"    => "mysql",
	"address"      => DB_HOST,
	"username"      => DB_USER,
	"password"      => DB_PASSWORD,
	"database"  => DB_NAME,
	// "charset"   => DB_CHARSET,
	'port' => 3306,
	'openApiBase' => '{"info":{"title":"API-REST-DMRFID-'.$table_prefix.'","version":"1.0.0"}}',
	'controllers' => 'records,columns,openapi,geojson', //cache
	'middlewares' => 'cors,authorization,dbAuth,sanitation,validation,multiTenancy,customization', //  ipAddress  joinLimits pageLimits,xsrf,jwtAuth
	'cacheType' => 'NoCache',
	'dbAuth.mode' => 'req',
	'dbAuth.usersTable' => 'users',
	'dbAuth.usernameColumn' => 'username',
	'dbAuth.passwordColumn' => 'password',
	'dbAuth.returnedColumns' => '',
	'customization.beforeHandler' => function ($operation, $tableName, $request, $environment) {
		$environment->start = microtime(true);
	},
	'customization.afterHandler' => function ($operation, $tableName, $response, $environment) {
		return $response->withHeader('X-Time-Taken', microtime(true) - $environment->start);
	},
	'authorization.tableHandler' => function ($operation, $tableName) {
		$finish = (!isset($_SESSION['user']) || !$_SESSION['user']) ? $operation !== 'create' && $operation !== 'update' && $operation !== 'delete' : (!isset($_SESSION['user']) || !$_SESSION['user']) ? $tableName != 'users' : true;
		return $finish;
	},
	'sanitation.handler' => function ($operation, $tableName, $column, $value) {
		if ($column['name'] == 'password'){
			if ($operation == 'create' || $operation == 'update'){
				return is_string($value) ? password_hash($value, PASSWORD_DEFAULT) : password_hash(strip_tags($value), PASSWORD_DEFAULT);
			} else {
				return is_string($value) ? strip_tags($value) : $value;
			}
		} else {
			return is_string($value) ? ($value) : (string) $value;
		}
	},
]);
$request = RequestFactory::fromGlobals();
$api = new Api($config);
$response = $api->handle($request);
ResponseUtils::output($response);

	
	/*


namespace FelipheGomez\PhpCrudApi {
    use FelipheGomez\PhpCrudApi\Api;
    use FelipheGomez\PhpCrudApi\Config;
    use FelipheGomez\PhpCrudApi\RequestFactory;
    use FelipheGomez\PhpCrudApi\ResponseUtils;
	exit();
    $config = new Config([
		'debug' => true,
		"driver"    => "mysql",
		"address"      => DB_HOST,
		"username"      => DB_USER,
		"password"      => DB_PASSWORD,
		"database"  => DB_NAME,
		// "charset"   => DB_CHARSET,
		'port' => 3306,
		'openApiBase' => '{"info":{"title":"API-REST","version":"1.0.0"}}',
		'controllers' => 'records,columns,openapi,geojson', //cache
		'middlewares' => 'cors,authorization,dbAuth,sanitation,validation,multiTenancy,customization', //  ipAddress  joinLimits pageLimits,xsrf,jwtAuth
		'cacheType' => 'NoCache',
		'dbAuth.mode' => 'req',
		'dbAuth.usersTable' => 'users',
		'dbAuth.usernameColumn' => 'username',
		'dbAuth.passwordColumn' => 'password',
		'dbAuth.returnedColumns' => '',
		//'pageLimits.records' => 100,
		//'xsrf.cookieName' => COOKIE_NAME . '-TOKEN',
		///'xsrf.headerName' => 'X-' . COOKIE_NAME . '-TOKEN',
    ]);
    $request = RequestFactory::fromGlobals();
    $api = new Api($config);
    $response = $api->handle($request);
    ResponseUtils::output($response);
}
	*/