<?php
///**
// * Created by PhpStorm.
// * User: arek50
// * Date: 11.02.2018
// * Time: 22:39
// */

use \Danzet\Libs\JwtAuthentication\JwtAuthentication;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

// CORS
$app->options('/{routes:.+}', function (Request $request, Response $response, $args) {
    return $response;
});

$app->add(function ($req, $res, $next) {
    $response = $next($req, $res);
    return $response
        ->withHeader('Access-Control-Allow-Origin', 'http://localhost:4200')
        ->withHeader('Access-Control-Allow-Headers', 'X-Requested-With, Content-Type, Accept, Origin, Authorization')
        ->withHeader('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
});
$app->add(new JwtAuthentication([
    "secret" => SECRET,
    "rules" => [
        new \Danzet\Libs\JwtAuthentication\RequestPathRule([
            // security places
            "path" => ["/api", "/api/1"],
            // free from security
            "passthrough" => ["/car/3"]
        ])
    ]
]));

//verify JWTtoken
$app->get('/verify', function (Request $request, Response $response) {
    $token = str_replace('Bearer ', '', $request->getServerParams()['HTTP_AUTHORIZATION']);
    $result = JwtAuth::verifyToken($token);
    $data['status'] = $result;
    return $response->withJson($data, 200);
});