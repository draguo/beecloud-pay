<?php

namespace Draguo\Pay\Contracts;

interface PayInterface
{
    public function pay($order);

    public function find($trade_no, $txn_time = null);

    public function refund($order);
}