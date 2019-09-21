<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';

$app = new \Slim\App;

require './cgi-bin/connect.php';

//-----------------------------------------------------------------------------
// CORS
//-----------------------------------------------------------------------------
$app->add(function ($request, $response, $next) {
	$newResponse = $response
		->withHeader('Access-Control-Allow-Origin', '*')
		->withHeader('Access-Control-Allow-Headers', array('Content-Type', 'X-Requested-With', 'Authorization'))
		->withHeader('Access-Control-Allow-Methods', array('GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'));

	if ($request->isOptions()) {
		return $newResponse;
	}

	return $next($request, $newResponse);
});

$app->get('/', function() { echo "Hello, world"; });
$app->get('/bookshelves', 'getShelves');
$app->get('/bookshelf/{shelf}', 'getShelf');
$app->get('/content', 'getHomeContent');
$app->get('/html-content', 'getHomeHtmlContent');
$app->get('/oauth2client', 'oauth2client');
$app->get('/hello/{name}', 'hello');

function hello(Request $request, Response $response, array $args) {
    $name = $args['name'];

    $response
        ->getBody()
        ->write("Hello, $name");

    return $response;
}
$app->run();

function getShelf(Request $request, Response $response, array $args) {
	$shelf = $args['shelf'];
	$shelf = new Bookshelf($shelf);
	$data = $shelf->get_books();
	$payload = [];
	if ($data) {
		$payload = $response
			->withStatus(200)
			->withJson(array("data" => $data));
	}
	return $payload;

}

function getShelves(Request $request, Response $response) {
	$gr = new Bookshelf();
	$data = $gr->get_shelves();
	$payload = [];
	if ($data) {
		$payload = $response
			->withStatus(200)
			->withJson(array("data" => $data));
	}
	return $payload;

}

function getHomeContent(Request $request, Response $response) {
	$content = new HomeContent();
	$payload = [];
	$data = $content->get();
	if ($data) {
		$payload = $response
			->withStatus(200)
			->withJson(array("data" => $data));
	}
	return $payload;

}


function getHomeHtmlContent(Request $request, Response $response) {
	$content = new HomeContent();
	$payload = [];
	$data = $content->get();
	if ($data) {
		$payload = $response
			->withStatus(200)
			->withJson(array("data" => $data));
	}
	return $payload;

}


function oauth2client(Request $request, Response $response) {
    $clientId = '273935039837-46ff17pla5ndkvnaag1ccsqa52rpkqop.apps.googleusercontent.com';
    $clientSecret = 'brcbuOS28Rgl7ihZ8C1RiNY3';
}
