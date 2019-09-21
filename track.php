<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

require 'vendor/autoload.php';
require './cgi-bin/connect.php';
require './shared/cors.php';

$app = new \Slim\App;


//-----------------------------------------------------------------------------
// CORS
//-----------------------------------------------------------------------------
$app->add($cors);

$app->post('/track', 'track');
$app->post('/gettrack', 'getTrack');
$app->run();

function track(Request $request, Response $response, array $args) {
  $req_data = json_decode($request->getBody());
  $user_id = $req_data->user_id;
  $tracking_data = $req_data->data;
  $api = new Track();
  $result = $api->set($user_id, $tracking_data);
  $data = array('result' => $result);

  $payload = $response
    ->withStatus(200)
    ->withJson($data);

  return $payload;
}

function getTrack(Request $request, Response $response, array $args) {
  $api = new Track();
  $req_data = json_decode($request->getBody());
  $user_id = $req_data->user_id;
  $tracking_data = $api->getTrack($user_id);
  $output = array('status' => 200, 'data' => $tracking_data);
  $payload = $response
    ->withStatus(200)
    ->withJson($output);
  return $payload;
}
