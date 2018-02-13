<?php
/**
 * Created by PhpStorm.
 * User: arek50
 * Date: 12.02.2018
 * Time: 18:17
 */

namespace Danzet\Libs\JwtAuthentication;

use Psr\Http\Message\RequestInterface;

interface RuleInterface {
    public function __invoke(RequestInterface $request);
}