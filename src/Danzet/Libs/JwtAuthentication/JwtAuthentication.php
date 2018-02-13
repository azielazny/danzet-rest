<?php

namespace Danzet\Libs\JwtAuthentication;

use \Danzet\Config\MonologLogger;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Firebase\JWT\JWT;

class JwtAuthentication
{

    protected $logger;
    protected $message;

    private $options = [
        "secure" => true,
        "relaxed" => ["localhost", "127.0.0.1"],
        "environment" => ["HTTP_AUTHORIZATION", "REDIRECT_HTTP_AUTHORIZATION"],
        "algorithm" => ["HS256", "HS512", "HS384"],
        "header" => "Authorization",
        "regexp" => "/Bearer\s+(.*)$/i",
        "cookie" => "token",
        "attribute" => "token",
        "path" => null,
        "passthrough" => null,
        "callback" => null,
        "error" => null
    ];

    public function __construct(array $options = [])
    {
        $monologLogger = new MonologLogger();
        $this->logger = $monologLogger->log('JWTLogger');

        $this->rules = new \SplStack;
        $this->hydrate($options);
        if (!isset($options["rules"])) {
            $this->addRule(new RequestMethodRule([
                "passthrough" => ["OPTIONS"]
            ]));
        }
        if (null !== ($this->options["path"])) {
            $this->addRule(new RequestPathRule([
                "path" => $this->options["path"],
                "passthrough" => $this->options["passthrough"]
            ]));
        }
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, callable $next)
    {
        $scheme = $request->getUri()->getScheme();
        $host = $request->getUri()->getHost();
        if (false === $this->shouldAuthenticate($request)) {
            return $next($request, $response);
        }
        /* HTTP allowed only if secure is false or server is in relaxed array. */
        if ("https" !== $scheme && true === $this->options["secure"]) {
            if (!in_array($host, $this->options["relaxed"])) {
                $message = sprintf(
                    "Insecure use of middleware over %s denied by configuration.",
                    strtoupper($scheme)
                );
                throw new \RuntimeException($message);
            }
        }
        /* If token cannot be found return with 401 Unauthorized. */
        if (false === $token = $this->fetchToken($request)) {
            return $this->error($request, $response, [
                "message" => $this->message
            ])->withStatus(401);
        }
        /* If token cannot be decoded return with 401 Unauthorized. */
        if (false === $decoded = $this->decodeToken($token)) {
            return $this->error($request, $response, [
                "message" => $this->message,
                "token" => $token
            ])->withStatus(401);
        }
        /* If callback returns false return with 401 Unauthorized. */
        if (is_callable($this->options["callback"])) {
            $params = ["decoded" => $decoded];
            if (false === $this->options["callback"]($request, $response, $params)) {
                return $this->error($request, $response, [
                    "message" => $this->message ? $this->message : "Callback returned false"
                ])->withStatus(401);
            }
        }
        /* Add decoded token to request as attribute when requested. */
        if ($this->options["attribute"]) {
            $request = $request->withAttribute($this->options["attribute"], $decoded);
        }
        return $next($request, $response);
    }

    public function shouldAuthenticate(RequestInterface $request)
    {
        foreach ($this->rules as $callable) {
            if (false === $callable($request)) {
                return false;
            }
        }
        return true;
    }

    public function error(RequestInterface $request, ResponseInterface $response, $arguments)
    {
        if (is_callable($this->options["error"])) {
            $handler_response = $this->options["error"]($request, $response, $arguments);
            if (is_a($handler_response, "\Psr\Http\Message\ResponseInterface")) {
                return $handler_response;
            }
        }
        return $response;
    }

    public function fetchToken(RequestInterface $request)
    {
        $server_params = $request->getServerParams();
        $header = "";
        $message = "";
        foreach ((array)$this->options["environment"] as $environment) {
            if (isset($server_params[$environment])) {
                $message = "Using token from environment";
                $header = $server_params[$environment];
            }
        }
        if (empty($header)) {
            $message = "Using token from request header";
            $headers = $request->getHeader($this->options["header"]);
            $header = isset($headers[0]) ? $headers[0] : "";
        }
        if (empty($header) && function_exists("apache_request_headers")) {
            $message = "Using token from apache_request_headers()";
            $headers = apache_request_headers();
            $header = isset($headers[$this->options["header"]]) ? $headers[$this->options["header"]] : "";
        }
        if (preg_match($this->options["regexp"], $header, $matches)) {
            $this->logger->debug($message);
            return $matches[1];
        }
        $cookie_params = $request->getCookieParams();
        if (isset($cookie_params[$this->options["cookie"]])) {
            $this->logger->debug("Using token from cookie");
            $this->logger->debug($cookie_params[$this->options["cookie"]]);
            return $cookie_params[$this->options["cookie"]];
        };
        $this->message = "Token not found";
        $this->logger->warning($this->message);
        return false;
    }

    public function decodeToken($token)
    {
        try {
            return JWT::decode(
                $token,
                $this->options["secret"],
                (array)$this->options["algorithm"]
            );
        } catch (\Exception $exception) {
            $this->message = $exception->getMessage();
            $this->logger->warning($exception->getMessage(),[$token]);
            return false;
        }
    }

    private function hydrate(array $data = [])
    {
        foreach ($data as $key => $value) {
            $method = "set" . ucfirst($key);
            if (method_exists($this, $method)) {
                call_user_func(array($this, $method), $value);
            }
        }
        return $this;
    }

    public function getPath()
    {
        return $this->options["path"];
    }

    public function setPath($path)
    {
        $this->options["path"] = $path;
        return $this;
    }

    public function getPassthrough()
    {
        return $this->options["passthrough"];
    }

    public function setPassthrough($passthrough)
    {
        $this->options["passthrough"] = $passthrough;
        return $this;
    }

    public function getEnvironment()
    {
        return $this->options["environment"];
    }

    public function setEnvironment($environment)
    {
        $this->options["environment"] = $environment;
        return $this;
    }

    public function getCookie()
    {
        return $this->options["cookie"];
    }

    public function setCookie($cookie)
    {
        $this->options["cookie"] = $cookie;
        return $this;
    }

    public function getSecure()
    {
        return $this->options["secure"];
    }

    public function setSecure($secure)
    {
        $this->options["secure"] = !!$secure;
        return $this;
    }

    public function getRelaxed()
    {
        return $this->options["relaxed"];
    }

    public function setRelaxed(array $relaxed)
    {
        $this->options["relaxed"] = $relaxed;
        return $this;
    }

    public function getSecret()
    {
        return $this->options["secret"];
    }

    public function setSecret($secret)
    {
        $this->options["secret"] = $secret;
        return $this;
    }

    public function getCallback()
    {
        return $this->options["callback"];
    }

    public function setCallback($callback)
    {
        $this->options["callback"] = $callback->bindTo($this);
        return $this;
    }

    public function getError()
    {
        return $this->options["error"];
    }

    public function setError($error)
    {
        $this->options["error"] = $error;
        return $this;
    }

    public function getRules()
    {
        return $this->rules;
    }

    public function setRules(array $rules)
    {
        unset($this->rules);
        $this->rules = new \SplStack;

        foreach ($rules as $callable) {
            $this->addRule($callable);
        }
        return $this;
    }

    public function addRule($callable)
    {
        $this->rules->push($callable);
        return $this;
    }

    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

    public function getAttribute()
    {
        return $this->options["attribute"];
    }

    public function setAttribute($attribute)
    {
        $this->options["attribute"] = $attribute;
        return $this;
    }

    public function getHeader()
    {
        return $this->options["header"];
    }

    public function setHeader($header)
    {
        $this->options["header"] = $header;
        return $this;
    }

    public function getRegexp()
    {
        return $this->options["regexp"];
    }

    public function setRegexp($regexp)
    {
        $this->options["regexp"] = $regexp;
        return $this;
    }

    public function getAlgorithm()
    {
        return $this->options["algorithm"];
    }

    public function setAlgorithm($algorithm)
    {
        $this->options["algorithm"] = $algorithm;
        return $this;
    }
}