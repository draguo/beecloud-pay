<?php

namespace Draguo\Pay\Gateways;


class Alipay extends Beecloud
{
    public function web($order)
    {
        $this->channel = 'ALI_WEB';
        return parent::pay($order);
    }

    public function find($trade_no, $txn_time = null)
    {
        $this->channel = 'ALI';
        return parent::find($trade_no, $txn_time);
    }

    public function verify()
    {
        return parent::verify();
    }
}