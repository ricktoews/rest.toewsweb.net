<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';
require './shared/cors.php';

$app = new \Slim\App;


//-----------------------------------------------------------------------------
// CORS
//-----------------------------------------------------------------------------
$app->add($cors);

$app->get('/', 'getShelves');
$app->get('/bookshelves', 'getShelves');
$app->get('/bookshelf/{shelf}', 'getShelf');
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


