<?php
//-----------------------------------------------------------------------------
// CORS
//-----------------------------------------------------------------------------
$cors = function($request, $response, $next) {
	$newResponse = $response
		->withHeader('Access-Control-Allow-Origin', '*')
		->withHeader('Access-Control-Allow-Headers', array('Content-Type', 'X-Requested-With', 'Authorization'))
		->withHeader('Access-Control-Allow-Methods', array('GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'));

	if ($request->isOptions()) {
		return $newResponse;
	}

	return $next($request, $newResponse);
};
