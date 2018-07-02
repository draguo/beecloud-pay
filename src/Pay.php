<?php

namespace Draguo\Pay;

use Draguo\Pay\Supports\Config;

class Pay
{
    /**
     * @var Config $config
     */
    protected $config;
    protected $pay_channel;

    public function __construct(array $config)
    {
        $this->config = new Config($config);
    }

    protected function create($method)
    {
        $gateway = __NAMESPACE__ . '\\Gateways\\' . str_replace(' ', '',ucwords(str_replace(['-', '_'], ' ', $method)));
        if (class_exists($gateway)) {
            return new $gateway($this->config);
        }
        throw new \Exception("Gateway [{$method}] Not Exists");
    }

    public static function __callStatic($method, $params)
    {
        $app = new self(...$params);
        return $app->create($method);
    }
}