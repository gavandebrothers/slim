<?php
header('Content-Type: application/json');
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
// define("PREFIX", "sl_");

require '../vendor/autoload.php';

$app = new \Slim\App;
//$app = new \Slim\App(['settings' => ['displayErrorDetails' => true]]);
$c = $app->getContainer();
$c['errorHandler'] = function ($c) {
    return function ($request, $response, $exception) use ($c) {
        return $response->withStatus(500)
            ->withHeader('Content-Type', 'text/html')
            ->write('Something went wrong!');
    };
};

//Override the default Not Found Handler before creating App
$c['notFoundHandler'] = function ($c) {
    return function ($request, $response) use ($c) {
        return $response->withStatus(404)
            ->withHeader('Content-Type', 'text/html')
            ->write('Page not found');
    };
};

//set India time zone
date_default_timezone_set("Asia/Calcutta");
//echo date("Y-m-d h:i:s"); exit;

require_once('Common_model.php');
require_once('Common_helper.php');

require_once ('../app/api.php');


$app->run();