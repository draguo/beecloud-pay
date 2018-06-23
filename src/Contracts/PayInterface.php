<?php

namespace Draguo\Pay\Contracts;

interface PayInterface
{
    public function pay($order);

    public function refund($order);
}