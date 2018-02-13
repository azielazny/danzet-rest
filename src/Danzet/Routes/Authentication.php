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

$app->add(new JwtAuthentication([
    "secret" => SECRET,
    "rules" => [
        new \Danzet\Libs\JwtAuthentication\RequestPathRule([
            // security places
            "path" => ["/cars", "/car"],
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