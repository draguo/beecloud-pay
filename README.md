<div data-type="alignment" data-value="center" style="text-align:center">
  <h2 id="bn8zcv" data-type="h">
    <a class="anchor" id="支付" href="#bn8zcv"></a>支付</h2>
  <div data-type="p"></div>
</div>

支持功能


### 配置
```php
$config = [
    'beecloud' => [
        'id' => '',
        'secret' => '',
        'master_secret' => '',
        'test_secret' => ''
    ],
    'unionpay' => [
        'mch_id' => '', // 商户号
        'return_url' => '', // 前台支付完成返回地址，可单独覆盖
        'notify_url' => '', // 后台回调, 可单独覆盖
        'cert_path' => '', // 签名证书位置，服务器绝对路径
        'cert_pwd' => '000000', // 签名密码
    ]
]
```

## 
### 支付

```php
$order = [
    'trade_no' => '20180627181607', // 订单号
    'total_fee' => '2000', // 订单金额,单位分
    'bill_timeout' => 15, // 订单有效时间，单位：分钟
    'txn_time' => '20180628170154', // 下单时间，银联在后续查询的时候需要，格式 YYYYMMDDhhmmss，北京时间
    'return_url' => 'https://', // 支付完成跳转地址
];


// 微信二维码
Pay::wechat($config)->scan($order);
// 微信小程序
Pay::wechat($config)->miniapp($order);
// 支付宝收银台 pc
Pay::alipay($config)->web($order);
// 银联 B2B pc
Pay::b2b($config)->web($order);
```

### 查询

```php
// $trade_no 系统订单号，$txn_time 银联必填，下单时间，格式 YYYYMMDDhhmmss，北京时间
// 微信
$order = Pay::wechat($config)->find($trade_no);
// 支付宝
$order = Pay::alipay($config)->find($trade_no);
// b2b
$order = Pay::alipay($config)->find($trade_no, $txn_time);
```

### 退款
```php
$pay_channel = 'WX_NATIVE'; // 渠道名，可选项参看下表

$order = [
    'trade_no' => '20180627181607', // 商家自定义的交易单号
    'refund_fee' => '2000', // 退款金额
    'refund_no' => '201806271816051', // 退款单号
];
// 银联额外参数
[
    'txn_time' => '20180628170154', // 下单时间 , 银联需要
    'transaction_id' => '', // 银联渠道必填, 由银联返回
]
$result = Pay::alipay($config)->refund($order);
$result = Pay::wechat($config)->refund($order);
$result = Pay::b2b($config)->refund($order);
```

### 验证签名
```php
// wechat && alipay 
Pay::beecloud($config)->verify();
// B2B todo
Pay::b2b($config)->verify();
```

