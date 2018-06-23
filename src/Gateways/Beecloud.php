<?php

namespace Draguo\Pay\Gateways;

use Draguo\Pay\Contracts\PayInterface;
use Draguo\Pay\Exceptions\GatewayException;
use Draguo\Pay\Supports\Config;
use beecloud\rest\api as PayService;

class Beecloud implements PayInterface
{
    protected $config;
    protected $params;

    public function __construct(Config $config)
    {
        $this->config = $config;
        list($appId, $app_secret, $master_secret, $test_secret) =
            array_values($this->config->only(['id', 'secret', 'master_secret', 'test_secret']));
        PayService::registerApp($appId, $app_secret, $master_secret, $test_secret);
        if ($this->config->get('sandbox')) {
            PayService::setSandbox(true);
        }
        $this->params = [
            'timestamp' => time() * 1000,
            'channel' => $config->get('pay_channel'),
            'title' => '订单支付',   //订单标题
            'qr_pay_mode' => "3", // 说明文档 https://beecloud.cn/doc/?sdk=php#1-2-2
            'bill_timeout' => 60, //京东(JD*)不支持该参数
        ];
    }

    public function pay($order)
    {
        // TODO: Implement pay() method.
    }

    public function refund($order)
    {
        // TODO: Implement refund() method.
    }

    public function scan($order)
    {
        $order['bill_no'] = $order['trade_no'];
        unset($order['trade_no']);
        $order['total_fee'] = (int)$order['total_fee'];
        // 可通过传入覆盖默认
        $params = array_merge($this->params, $order);
        try{
            $result = PayService::bill($params);
            if ($result->result_code != 0) {
                throw new GatewayException($result->err_detail);
            }
            return $result;
        } catch (\Exception $exception) {
            throw new GatewayException($exception->getMessage());
        }
    }
}