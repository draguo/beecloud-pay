<?php
/**
 * 银联支付过程
 * 通过参数生成自动提交的表单，输出到网页上，会自动跳转到真实的付款页面
 */

namespace Draguo\Pay\Gateways;

use Draguo\Pay\Contracts\PayInterface;
use Draguo\Pay\Supports\Config;
use Draguo\Pay\Supports\HttpRequest;
use Draguo\Pay\Supports\Rsa;
use function GuzzleHttp\Psr7\parse_query;

class Unionpay implements PayInterface
{
    use HttpRequest;

    const VERSION = '5.1.0';
    const SIGN_METHOD = '01';
    const API_PREFIX = 'https://gateway.95516.com/gateway/api/';
    const API_TEST_PREFIX = 'https://gateway.test.95516.com/gateway/api/';

    protected $config;
    protected $params;
    protected $uri_prefix;
    protected $endpoint;

    public function __construct(Config $config)
    {
        $this->config = $config;
        $this->params = [
            'version' => self::VERSION,
            'encoding' => 'utf-8',
            'signMethod' => self::SIGN_METHOD,
            'channelType' => '07',  //渠道类型，07-PC，08-手机
            'accessType' => '0',  //接入类型
            'merId' => $config->get('mch_id'), //商户代码
        ];
    }

    public function pay($order)
    {
        // 个性参数
        $add = [
            'txnType' => '01',  //交易类型, 固定
            'bizType' => '000202',  //业务类型
            'txnSubType' => '01',  //交易子类
            'currencyCode' => '156',  //交易币种，境内商户固定156
            'backUrl' => $this->config->get('notify_url'),  //后台通知地址
            'frontUrl' => $this->config->get('return_url'), // 前台返回地址
            'txnAmt' => $order['total_fee'],
            'orderId' => $order['trade_no'],
            'txnTime' => $order['txn_time'], //订单发送时间，格式为YYYYMMDDhhmmss，取北京时间
            //'issInsCode' => 'ABC',  //发卡机构代码，直接跳转到银行网银的形式
        ];

        $this->params = array_merge($this->params, $add);
        $this->params['signature'] = $this->signature($this->params);
        return $this->createAutoFormHtml($this->params, $this->getEndpoint('frontTransReq.do'));
    }

    public function find($trade_no, $txn_time = null)
    {
        if ($txn_time === null) {
            throw new \Exception('txn_time not be null');
        }
        // 个性参数
        $add = [
            'txnType' => '00',  //交易类型, 固定
            'bizType' => '000802',  //业务类型
            'txnSubType' => '00',  //交易子类
            'orderId' => $trade_no,
            'txnTime' => $txn_time,
        ];

        $this->params = array_merge($this->params, $add);
        $this->params['signature'] = $this->signature($this->params);
        $resultRaw = $this->post($this->getEndpoint('queryTrans.do'), $this->params);
        return parse_query($resultRaw);
    }

    public function refund($order)
    {
        // 个性参数
        $add = [
            'txnType' => '04',  //交易类型, 固定
            'bizType' => '000201',  //业务类型
            'txnSubType' => '00',  //交易子类
            'backUrl' => $this->config->get('notify_url'),  //后台通知地址
            'txnAmt' => $order['total_fee'],
            'orderId' => $order['refund_no'],
            'txnTime' => $order['txn_time'], //订单发送时间，格式为YYYYMMDDhhmmss，取北京时间
            'origQryId' => $order['transaction_id'], //银联返回的交易流水号
        ];

        $this->params = array_merge($this->params, $add);
        $this->params['signature'] = $this->signature($this->params);
//        dd($this->params);
        $resultRaw = $this->post($this->getEndpoint('backTransReq.do'), $this->params);
        return parse_query($resultRaw);
    }

    protected function getEndpoint($uri)
    {
        if ($this->config->get('sandbox')) {
            return self::API_TEST_PREFIX . $uri;
        }
        return self::API_PREFIX . $uri;
    }
    // rsa 签名方式
    protected function signature($params)
    {
        $cert_path = $this->config->get('cert_path');
        $cert_pwd = $this->config->get('cert_pwd');
        $params['certId'] = Rsa::getCertId($cert_path, $cert_pwd);
        $this->params['certId'] = $params['certId'];
        $private_key = Rsa::getPrivateKey($cert_path, $cert_pwd);
        // 转换成 query
        ksort($params);
        $params_str = urldecode(http_build_query($params));
        //sha256签名摘要
        $params_sha256x16 = hash('sha256', $params_str);
        // 签名
        openssl_sign($params_sha256x16, $signature, $private_key, 'sha256');
        return base64_encode($signature);
    }

    protected function createAutoFormHtml($params, $reqUrl)
    {
        $encodeType = isset ($params ['encoding']) ? $params ['encoding'] : 'UTF-8';
        $html = <<<eot
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset={$encodeType}" />
</head>
<body onload="javascript:document.pay_form.submit();">
    <form id="pay_form" name="pay_form" action="{$reqUrl}" method="post">
	
eot;
        foreach ($params as $key => $value) {
            $html .= "    <input type=\"hidden\" name=\"{$key}\" id=\"{$key}\" value=\"{$value}\" />\n";
        }
        $html .= <<<eot
    </form>
</body>
</html>
eot;
        return $html;
    }
}