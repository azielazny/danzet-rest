<?php
/**
 * Created by PhpStorm.
 * User: arek50
 * Date: 12.02.2018
 * Time: 18:46
 */

namespace Danzet\Libs;

use \Firebase\JWT\JWT;

class JwtAuth
{
    private function __construct()
    {
    }

    public static function getToken($username, $password)
    {
        $secret = SECRET;
        // date: now
        $now = date('Y-m-d H:i:s');
        // date: now +2 hours
        $future = date('Y-m-d H:i:s', mktime(date('H') + 2, date('i'), date('s'), date('m'), date('d'), date('Y')));
        $token = array(
            'header' => [
                'username' => $username,
                'password' => $password,
            ],
            'payload' => [
                'iat' => $now,
                'exp' => $future
            ]
        );
        return JWT::encode($token, $secret, "HS256");
    }

    public static function verifyToken($token)
    {
        $secret = SECRET;
        $obj = JWT::decode($token, $secret, array("HS256"));
        if (isset($obj->payload)) {
            $now = strtotime(date('Y-m-d H:i:s'));
            $exp = strtotime($obj->payload->exp);
            if (($exp - $now) > 0) {
                return true;
            }
        }
        return false;
    }
}
