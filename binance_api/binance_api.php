<?php 

// https://github.com/jaggedsoft/php-binance-api

class Binance {

    public $btc_value = 0.00;

    protected $base = "https://api.binance.com/api/";
   protected $wapi = 'https://api.binance.com/wapi/'; // /< REST endpoint for the withdrawals    

    public function __construct($api_key, $api_secret) {
        $this->api_key = $api_key;
        $this->api_secret = $api_secret;
    }
    public function ping() {
        return $this->request("v1/ping");
    }
    public function time() {
        return $this->request("v1/time");
    }
    public function exchangeInfo() {
        return $this->request("v1/exchangeInfo");
    }

    public function buy($symbol, $quantity, $price, $type = "LIMIT", $flags = []) {
        return $this->order("BUY", $symbol, $quantity, $price, $type, $flags);
    }
    public function sell($symbol, $quantity, $price, $type = "LIMIT", $flags = []) {
        return $this->order("SELL", $symbol, $quantity, $price, $type, $flags);
    }

    // MARKET ：市价买
    public function marketBuy($symbol, $quantity)
    {
        return $this->order("BUY", $symbol, $quantity, 0 , 'MARKET');
    }

    // MARKET ：市价卖
    public function marketSell($symbol, $quantity)
    {
        return $this->order("SELL", $symbol, $quantity, 0, 'MARKET');
    }

    //  buy-limit：限价买
    public function limitBuy($symbol, $quantity, $price)
    {
        return $this->order("BUY", $symbol, $quantity, $price, 'LIMIT');
    }

    // sell-limit：限价卖
    public function limitSell($symbol, $quantity, $price)
    {
        return $this->order("SELL", $symbol, $quantity, $price, 'LIMIT');
    }

    public function cancel($symbol, $orderid) {
        return $this->signedRequest("v3/order",["symbol"=>$symbol, "orderId"=>$orderid], "DELETE");
    }
    public function orderStatus($symbol, $orderid) {
        return $this->signedRequest("v3/order",["symbol"=>$symbol, "orderId"=>$orderid]);
    }
    
    public function openOrders($symbol) {
        return $this->signedRequest("v3/openOrders",["symbol"=>$symbol]);
    }
    public function orders($symbol, $limit = 500) {
        return $this->signedRequest("v3/allOrders",["symbol"=>$symbol, "limit"=>$limit]);
    }
    public function trades($symbol) {
        return $this->signedRequest("v3/myTrades",["symbol"=>$symbol]);
    }
    public function prices() {
        return $this->priceData($this->request("v1/ticker/allPrices"));
    }
    public function bookPrices() {
        return $this->bookPriceData($this->request("v1/ticker/allBookTickers"));
    }
    public function account() {
        return $this->signedRequest("v3/account");
    }
    public function depth($symbol) {
        return $this->request("v1/depth",["symbol"=>$symbol]);
    }
    public function balances($priceData = false) {
        return $this->balanceData($this->signedRequest("v3/account"),$priceData);
    }
    public function prevDay($symbol) {
        return $this->request("v1/ticker/24hr", ["symbol"=>$symbol]);
    }
    private function request($url, $params = [], $method = "GET") {
        $opt = [
            "http" => [
                "method" => $method,
                "header" => "User-Agent: Mozilla/4.0 (compatible; PHP Binance API)\r\n"
            ]
        ];
        $context = stream_context_create($opt);
        $query = http_build_query($params, '', '&');
        return json_decode(file_get_contents($this->base.$url.'?'.$query, false, $context), true);
    }
    private function signedRequest($url, $params = [], $method = "GET") {
        $params['timestamp'] = number_format(microtime(true)*1000,0,'.','');
        $query = http_build_query($params, '', '&');
        $signature = hash_hmac('sha256', $query, $this->api_secret);
        $opt = [
            "http" => [
                "method" => $method,
                "ignore_errors" => true,
                "header" => "User-Agent: Mozilla/4.0 (compatible; PHP Binance API)\r\nX-MBX-APIKEY: {$this->api_key}\r\nContent-type: application/x-www-form-urlencoded\r\n"
            ]
        ];
        if ( $method == 'GET' ) {
            // parameters encoded as query string in URL
            $endpoint = "{$this->base}{$url}?{$query}&signature={$signature}";
        } else {
            // parameters encoded as POST data (in $context)
            $endpoint = "{$this->base}{$url}";
            $postdata = "{$query}&signature={$signature}";
            $opt['http']['content'] = $postdata;
        }
        $context = stream_context_create($opt);
        return json_decode(file_get_contents($endpoint, false, $context), true);
    }
    private function order_test($side, $symbol, $quantity, $price, $type = "LIMIT", $flags = []) {
        $opt = [
            "symbol" => $symbol,
            "side" => $side,
            "type" => $type,
            "quantity" => $quantity,
            "recvWindow" => 60000
        ];
        if ( $type == "LIMIT" ) {
            $opt["price"] = $price;
            $opt["timeInForce"] = "GTC";
        }
        // allow additional options passed through $flags
        if ( isset($flags['recvWindow']) ) $opt['recvWindow'] = $flags['recvWindow'];
        if ( isset($flags['timeInForce']) ) $opt['timeInForce'] = $flags['timeInForce'];
        if ( isset($flags['stopPrice']) ) $opt['stopPrice'] = $flags['stopPrice'];
        if ( isset($flags['icebergQty']) ) $opt['icebergQty'] = $flags['icebergQty'];
        return $this->signedRequest("v3/order/test", $opt, "POST");
    }
    private function order($side, $symbol, $quantity, $price, $type = "LIMIT", $flags = []) {
        $opt = [
            "symbol" => $symbol,
            "side" => $side,
            "type" => $type,
            "quantity" => $quantity,
            "recvWindow" => 60000
        ];
        if ( $type == "LIMIT" ) {
            $opt["price"] = $price;
            $opt["timeInForce"] = "GTC";
        }
        // allow additional options passed through $flags
        if ( isset($flags['recvWindow']) ) $opt["recvWindow"] = $flags['recvWindow'];
        if ( isset($flags['timeInForce']) ) $opt["timeInForce"] = $flags['timeInForce'];
        if ( isset($flags['stopPrice']) ) $opt['stopPrice'] = $flags['stopPrice'];
        if ( isset($flags['icebergQty']) ) $opt['icebergQty'] = $flags['icebergQty'];
        return $this->signedRequest("v3/order", $opt, "POST");
    }
    //1m,3m,5m,15m,30m,1h,2h,4h,6h,8h,12h,1d,3d,1w,1M
    public function candlesticks($symbol, $interval = "5m") {
        return $this->request("v1/klines",["symbol"=>$symbol, "interval"=>$interval]);
    }
    private function balanceData($array, $priceData = false) {
        if ( $priceData ) $btc_value = 0.00;
        $balances = [];
        foreach ( $array['balances'] as $obj ) {
            $asset = $obj['asset'];
            $balances[$asset] = ["available"=>$obj['free'], "onOrder"=>$obj['locked'], "btcValue"=>0.00000000];
            if ( $priceData ) {
                if ( $obj['free'] < 0.00000001 ) continue;
                if ( $asset == 'BTC' ) {
                    $balances[$asset]['btcValue'] = $obj['free'];
                    $btc_value+= $obj['free'];
                    continue;
                }
                $btcValue = number_format($obj['free'] * $priceData[$asset.'BTC'],8,'.','');
                $balances[$asset]['btcValue'] = $btcValue;
                $btc_value+= $btcValue;
            }
        }
        if ( $priceData ) {
            uasort($balances, function($a, $b) { return $a['btcValue'] < $b['btcValue']; });
            $this->btc_value = $btc_value;
        }
        return $balances;
    }
    private function bookPriceData($array) {
        $bookprices = [];
        foreach ( $array as $obj ) {
            $bookprices[$obj['symbol']] = [
                "bid"=>$obj['bidPrice'],
                "bids"=>$obj['bidQty'],
                "ask"=>$obj['askPrice'],
                "asks"=>$obj['askQty']
            ];
        }
        return $bookprices;
    }
    private function priceData($array) {
        $prices = [];
        foreach ( $array as $obj ) {
            $prices[$obj['symbol']] = $obj['price'];
        }
        return $prices;
    }

   /**
    * withdraw requests a asset be withdrawn from binance to another wallet
    *
    * $asset = "BTC";
    * $address = "1C5gqLRs96Xq4V2ZZAR1347yUCpHie7sa";
    * $amount = 0.2;
    * $response = $api->withdraw($asset, $address, $amount);
    *
    * $address = "44tLjmXrQNrWJ5NBsEj2R77ZBEgDa3fEe9GLpSf2FRmhexPvfYDUAB7EXX1Hdb3aMQ9FLqdJ56yaAhiXoRsceGJCRS3Jxkn";
    * $addressTag = "0e5e38a01058dbf64e53a4333a5acf98e0d5feb8e523d32e3186c664a9c762c1";
    * $amount = 0.1;
    * $response = $api->withdraw($asset, $address, $amount, $addressTag);
    *
    * @param $asset string the currency such as BTC
    * @param $address string the addressed to whihc the asset should be deposited
    * @param $amount double the amount of the asset to transfer
    * @param $addressTag string adtional transactionid required by some assets
    * @return array with error message or array transaction
    */
   public function withdraw( $asset,  $amount,  $address, $addressTag = null ) {
      $options = [ 
            "asset" => $asset,
            "address" => $address,
            "amount" => $amount,
            "wapi" => true,
            "name" => "API Withdraw" 
      ];
      if( is_null( $addressTag ) == false && is_empty( $addressTag ) == false ) {
         $options[ 'addressTag' ] = $addressTag;
      }
      return $this->httpRequest( "v3/withdraw.html", "POST", $options, true );
   } 
   

   /**
    * httpRequest curl wrapper for all http api requests.
    * You can't call this function directly, use the helper functions
    *
    * @see buy()
    * @see sell()
    * @see marketBuy()
    * @see marketSell() $this->httpRequest( "https://api.binance.com/api/v1/ticker/24hr");
    *     
    * @param $url string the endpoint to query, typically includes query string
    * @param $method string this should be typically GET, POST or DELETE
    * @param $params array addtional options for the request
    * @param $signed bool true or false sign the request with api secret
    * @return array containing the response
    */
   private function httpRequest( $url, $method = "GET", $params = [],  $signed = false ) {
      if( function_exists( 'curl_init' ) == false ) {
         die( "Sorry cURL is not installed!" );
      }
      
      if( is_string( $url ) == false ) {
         echo "warning: url expected string got " . gettype( $url ) . PHP_EOL;
      }
      
      if( is_string( $method ) == false ) {
         echo "warning: method expected string got " . gettype( $method ) . PHP_EOL;
      }
      
      if( is_array( $params ) == false ) {
         echo "warning: params expected array got " . gettype( $params ) . PHP_EOL;
      }
      
      if( is_bool( $signed ) == false ) {
         echo "warning: signed expected bool got " . gettype( $signed ) . PHP_EOL;
      }
      
      $ch = curl_init();
      curl_setopt( $ch, CURLOPT_VERBOSE, $this->httpDebug );
      $query = http_build_query( $params, '', '&' );
      
      // signed with params
      if( $signed == true ) {
         if( empty( $this->api_key ) )
            die( "signedRequest error: API Key not set!" );
         if( empty( $this->api_secret ) )
            die( "signedRequest error: API Secret not set!" );
         $base = $this->base;
         $ts = ( microtime( true ) * 1000 ) + $this->info[ 'timeOffset' ];
         $params[ 'timestamp' ] = number_format( $ts, 0, '.', '' );
         if( isset( $params[ 'wapi' ] ) ) {
            unset( $params[ 'wapi' ] );
            $base = $this->wapi;
         }
         $query = http_build_query( $params, '', '&' );
         $signature = hash_hmac( 'sha256', $query, $this->api_secret );
         $endpoint = $base . $url . '?' . $query . '&signature=' . $signature;
         curl_setopt( $ch, CURLOPT_URL, $endpoint );
         curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 
               'X-MBX-APIKEY: ' . $this->api_key 
         ) );
      }
      // params so buildquery string and append to url
      else if( count( $params ) > 0 ) {
         curl_setopt( $ch, CURLOPT_URL, $this->base . $url . '?' . $query );
      }
      // no params so just the base url
      else {
         curl_setopt( $ch, CURLOPT_URL, $this->base . $url );
         curl_setopt( $ch, CURLOPT_HTTPHEADER, array( 
               'X-MBX-APIKEY: ' . $this->api_key 
         ) );
      }
      curl_setopt( $ch, CURLOPT_USERAGENT, "User-Agent: Mozilla/4.0 (compatible; PHP Binance API)" );
      // Post and postfields
      if( $method == "POST" ) {
         curl_setopt( $ch, CURLOPT_POST, true );
         // curl_setopt($ch, CURLOPT_POSTFIELDS, $query);
      }
      // Delete Method
      if( $method == "DELETE" ) {
         curl_setopt( $ch, CURLOPT_CUSTOMREQUEST, $method );
      }
      curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, true );
      // headers will proceed the output, json_decode will fail below
      curl_setopt( $ch, CURLOPT_HEADER, false );
      curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
      curl_setopt( $ch, CURLOPT_TIMEOUT, 60 );
      $output = curl_exec( $ch );
      // Check if any error occurred
      if( curl_errno( $ch ) > 0 ) {
         echo 'Curl error: ' . curl_error( $ch ) . "\n";
         return [];
      }
      curl_close( $ch );
      $json = json_decode( $output, true );
      if( isset( $json[ 'msg' ] ) ) {
         echo "signedRequest error: {$output}" . PHP_EOL;
      }

      return $json;
   }      
}

// https://www.binance.com/restapipub.html
