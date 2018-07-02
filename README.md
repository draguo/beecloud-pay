<div data-type="alignment" data-value="center" style="text-align:center">
  <h2 id="bn8zcv" data-type="h">
    <a class="anchor" id="支付" href="#bn8zcv"></a>支付</h2>
  <div data-type="p"></div>
</div>

支持 beecloud 和银联 B2B 支付

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
];

$pay_channel = 'WX_NATIVE'; // 渠道名，可选项参看下表

// pc 端
return Pay::{$pay_channel}($config)->pay($order);
// 二维码, 银联 B2B 不支持
return Pay::{$pay_channel}($config)->scan($order);
```

### 查询

```php
$pay_channel = 'WX_NATIVE'; // 渠道名，可选项参看下表

// $trade_no 系统订单号，$txn_time 银联必填，下单时间，格式 YYYYMMDDhhmmss，北京时间
$order = Pay::{$pay_channel}($config)->find($trade_no, $txn_time);
```

### 退款
```php
$pay_channel = 'WX_NATIVE'; // 渠道名，可选项参看下表

$order = [
    'trade_no' => '20180627181607',
    'refund_fee' => '2000',
    'txn_time' => '20180628170154',
    'refund_no' => '201806271816051',
    'transaction_id' => '', // 银联渠道必填
];
$result = Pay::b2b()->refund($order);
```

### 渠道支持
```
WX_NATIVE // 微信网页收款
ALI_WEB // 支付宝收银台
WX // beecloud 微信退款使用
ALI // beecloud 支付宝退款使用
b2b // 银联 b2b 支付
```

