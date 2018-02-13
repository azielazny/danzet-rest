<?php
/**
 * Created by PhpStorm.
 * User: arek50
 * Date: 12.02.2018
 * Time: 18:18
 */

namespace Danzet\Libs\JwtAuthentication;

use Psr\Http\Message\RequestInterface;


class RequestPathRule implements RuleInterface
{
    protected $options = [
        "path" => ["/"],
        "passthrough" => []
    ];

    public function __construct($options = [])
    {
        $this->options = array_merge($this->options, $options);
    }

    public function __invoke(RequestInterface $request)
    {
        $uri = "/" . $request->getUri()->getPath();
        $uri = preg_replace("#/+#", "/", $uri);
        /* If request path is matches passthrough should not authenticate. */
        foreach ((array)$this->options["passthrough"] as $passthrough) {
            $passthrough = rtrim($passthrough, "/");
            if (!!preg_match("@^{$passthrough}(/.*)?$@", $uri)) {
                return false;
            }
        }
        /* Otherwise check if path matches and we should authenticate. */
        foreach ((array)$this->options["path"] as $path) {
            $path = rtrim($path, "/");
            if (!!preg_match("@^{$path}(/.*)?$@", $uri)) {
                return true;
            }
        }
        return false;
    }
}