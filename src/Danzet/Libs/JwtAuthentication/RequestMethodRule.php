<?php
/**
 * Created by PhpStorm.
 * User: arek50
 * Date: 12.02.2018
 * Time: 18:18
 */

namespace Danzet\Libs\JwtAuthentication;

use \Psr\Http\Message\RequestInterface;

class RequestMethodRule implements RuleInterface
{
    protected $options = [
        "passthrough" => ["OPTIONS"]
    ];

    public function __construct(array $options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    public function __invoke(RequestInterface $request)
    {
        return !in_array($request->getMethod(), $this->options["passthrough"]);
    }
}