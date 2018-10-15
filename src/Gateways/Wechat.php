<?php

namespace Draguo\Pay\Gateways;


use Draguo\Pay\Contracts\txn_time;

class Wechat extends Beecloud
{
    public function scan($order)
    {
        $this->channel = 'WX_NATIVE';
        return parent::pay($order);
    }

    public function miniapp($order)
    {
        $this->channel = 'WX_MINI';
        return parent::pay($order);
    }

    public function find($trade_no, $txn_time = null)
    {
        $this->channel = 'WX';
        return parent::find($trade_no, $txn_time);
    }

    public function refund($order)
    {
        $order['channel'] = 'WX';

        return parent::refund($order);
    }

    public function verify()
    {
        return parent::verify();
    }
}