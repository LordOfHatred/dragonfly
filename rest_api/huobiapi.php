<?php

class Huobiapi 
{
    private $api = 'api.huobi.pro';

    public $api_method = '';

    public $req_method = '';
    
    public function __construct() 
    {
        date_default_timezone_set("Etc/GMT+0");
    }

    /**
    * 公共类API
    */
    // 查询系统支持的所有交易对及精度
   public  function get_common_symbols() 
    {
        $this->api_method = '/v1/common/symbols';
        $this->req_method = 'GET';
        $url = $this->create_sign_url([]);

        return json_decode($this->curl($url));
    }

    // 查询系统支持的所有币种
    public function get_common_currencys() 
    {
        $this->api_method = "/v1/common/currencys";
        $this->req_method = 'GET';
        $url = $this->create_sign_url([]);

        return json_decode($this->curl($url));
    }

    // 查询系统当前时间
    public function get_common_timestamp() 
    {
        $this->api_method = "/v1/common/timestamp";
        $this->req_method = 'GET';
        $url = $this->create_sign_url([]);

        return json_decode($this->curl($url));
    }

    // 查询当前用户的所有账户(即account-id)
    public function get_account_accounts() 
    {
        $this->api_method = "/v1/account/accounts";
        $this->req_method = 'GET';
        $url = $this->create_sign_url([]);

        return json_decode($this->curl($url));
    }

    // 查询指定账户的余额
    public function get_account_balance() 
    {
        $this->api_method = "/v1/account/accounts/".ACCOUNT_ID."/balance";
        $this->req_method = 'GET';
        $url = $this->create_sign_url([]);

        return json_decode($this->curl($url));
    }

    // buy-market：市价买
    public function marketBuy($symbol, $qty)
    {
        return $this->place_order(ACCOUNT_ID, $qty, 0, $symbol, 'buy-market');
    }

    // sell-market：市价卖
    public function marketSell($symbol, $qty)
    {
        return $this->place_order(ACCOUNT_ID, $qty, 0, $symbol, 'sell-market');
    }

    //  buy-limit：限价买
    public function limitBuy($symbol, $qty,$price)
    {
        return $this->place_order(ACCOUNT_ID, $qty, $price, $symbol, 'buy-limit');
    }

    // sell-limit：限价卖
    public function limitSell($symbol, $qty, $price)
    {
        return $this->place_order(ACCOUNT_ID,$qty, $price, $symbol, 'sell-limit');
    }

    /**
    * 交易类API
    */
    // 下单
    public function place_order($account_id=0,$amount=0,$price=0,$symbol='',$type='') 
    {
        $source = 'api';
        $this->api_method = "/v1/order/orders/place";
        $this->req_method = 'POST';
        // 数据参数
        $postdata = [
            'account-id' => $account_id,
            'amount' => $amount,
            'source' => $source,
            'symbol' => $symbol,
            'type' => $type
        ];
        if ($price) {
            $postdata['price'] = $price;
        }
        $url = $this->create_sign_url();
        $return = $this->curl($url,$postdata);

        return json_decode($return);
    }

    // 查询某个订单详情
    public function get_order($order_id) 
    {
        $this->api_method = '/v1/order/orders/'.$order_id;
        $this->req_method = 'GET';
        $url = $this->create_sign_url();
        $return = $this->curl($url);

        return json_decode($return);
    }

    // 查询某个订单的成交明细
    public function get_order_matchresults($order_id = 0) 
    {
        $this->api_method = '/v1/order/orders/'.$order_id.'/matchresults';
        $this->req_method = 'GET';
        $url = $this->create_sign_url();
        $return = $this->curl($url,$postdata);

        return json_decode($return);
    }

    // 获取账户余额
   public function get_balance($account_id=ACCOUNT_ID) 
   {
        $this->api_method = "/v1/account/accounts/{$account_id}/balance";
        $this->req_method = 'GET';
        $url = $this->create_sign_url();
        $return = $this->curl($url);
        $result = json_decode($return);

        return $result;
    }

    // 提现
    public function withdraw($symbol, $qty, $wallet)
    {
        return $this->withdraw_create($wallet, $qty, $symbol);
    }

    /**
    * 虚拟币提现API
    */
    // 申请提现虚拟币
    public function withdraw_create($address='', $amount='',$currency='',$fee='',$addr_tag='') 
    {
        $this->api_method = "/v1/dw/withdraw/api/create";
        $this->req_method = 'POST';
        $postdata = [
            'address' => $address,
            'amount' => $amount,
            'currency' => $currency
        ];
        if ($fee) $postdata['fee'] = $fee;
        if ($addr_tag) $postdata['addr-tag'] = $addr_tag;
        $url = $this->create_sign_url($postdata);
        $return = $this->curl($url);
        $result = json_decode($return);

        return $result;
    }

    /**
    * 类库方法
    */
    // 生成验签URL
    public function create_sign_url($append_param = []) 
    {
        // 验签参数
        $param = [
            'AccessKeyId' => ACCESS_KEY,
            'SignatureMethod' => 'HmacSHA256',
            'SignatureVersion' => 2,
            'Timestamp' => date('Y-m-d\TH:i:s', time())
        ];
        if ($append_param) {
            foreach($append_param as $k=>$ap) {
                $param[$k] = $ap; 
            }
        }
        return 'https://'.$this->api.$this->api_method.'?'.$this->bind_param($param);
    }


    // 组合参数
    public function bind_param($param) 
    {
        $u = [];
        $sort_rank = [];
        foreach($param as $k=>$v) {
            $u[] = $k."=".urlencode($v);
            $sort_rank[] = ord($k);
        }
        asort($u);
        $u[] = "Signature=".urlencode($this->create_sig($u));
        return implode('&', $u);
    }

    // 生成签名
    public function create_sig($param) 
    {
        $sign_param_1 = $this->req_method."\n".$this->api."\n".$this->api_method."\n".implode('&', $param);
        $signature = hash_hmac('sha256', $sign_param_1, SECRET_KEY, true);

        return base64_encode($signature);
    }

    public function curl($url,$postdata=[]) 
    {
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        if ($this->req_method == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postdata));
        }
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch,CURLOPT_HEADER,0);
        curl_setopt($ch, CURLOPT_TIMEOUT,60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        
        curl_setopt($ch, CURLOPT_PROXY, "127.0.0.1:7001");
        curl_setopt($ch, CURLOPT_PROXYTYPE, constant('CURLPROXY_SOCKS5'));

        curl_setopt ($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
        ]);
        $output = curl_exec($ch);
        $info = curl_getinfo($ch);
        curl_close($ch);

        return $output;
    }
}

?>
