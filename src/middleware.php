<?php

use Slim\App;

return function (App $app) {
    // e.g: $app->add(new \Slim\Csrf\Guard);

	//Headers para habilitar o CORS
	$app->add(function ($req, $res, $next) {

		$res = $next($req, $res);
		$res = $res
		->withHeader('Access-Control-Allow-Origin', '*')
		->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization, X-Access-Token')
		->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, PATCH, OPTIONS')->withHeader('Content-Type','application/json');
		return $res;
	});
};
