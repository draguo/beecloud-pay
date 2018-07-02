<?php

namespace Draguo\Pay\Gateways;


class B2B extends Unionpay
{
    public function web($order)
    {
        return parent::pay($order);
    }
}