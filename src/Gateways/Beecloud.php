<?php

namespace Draguo\Pay\Gateways;

use Draguo\Pay\Contracts\PayInterface;
use Draguo\Pay\Exceptions\GatewayException;
use Draguo\Pay\Supports\Config;
use beecloud\rest\api as PayService;
use Symfony\Component\HttpFoundation\Request;

class Beecloud implements PayInterface
{
    protected $config;
    protected $params;
    protected $request;

    public function __construct(Config $config)
    {
        $this->config = $config->get('beecloud');
        list($appId, $app_secret, $master_secret, $test_secret) =
            array_values($this->config->only(['id', 'secret', 'master_secret', 'test_secret']));
        PayService::registerApp($appId, $app_secret, $master_secret, $test_secret);
        if ($this->config->get('sandbox')) {
            PayService::setSandbox(true);
        }
        $this->params = [
            'timestamp' => time() * 1000,
            'channel' => $config->get('pay_channel'),
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
        $order['bill_no'] = $order['trade_no'];
        unset($order['trade_no']);
        // 可通过传入覆盖默认
        $data = array_merge($this->params, $order);
        $result = PayService::refund($data);
        if ($result->result_code != 0) {
            throw new GatewayException($result->err_detail, $result->result_code);
        }
        return $result;
    }

    public function verify()
    {
        $request = Request::createFromGlobals();
        $this->request = $request;

        $data = $request->request->count() > 0 ? $request->request->all() : $request->query->all();
        if ($this->isJson()) {
            $data = ((array) json_decode($request->getContent(), true));
        }

        $checkParams[] = $this->config['id'];
        foreach (['transaction_id', 'transaction_type', 'channel_type', 'transaction_fee'] as $key) {
            array_push($checkParams, $data[$key]);
        }
        array_push($checkParams, $this->config->get('master_secret'));
        if (md5(implode('', $checkParams)) != $data['signature']) {
            throw new \Exception('签名出错');
        }
        return true;
    }

    protected function isJson()
    {
        return mb_strpos($this->request->headers->get('content-type'), 'json');
    }

    public function scan($order)
    {
        $order['bill_no'] = $order['trade_no'];
        unset($order['trade_no']);
        $order['total_fee'] = (int)$order['total_fee'];
        $order['title'] = '订单支付';
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