<?php
 
require_once __DIR__ . '/workerman_linux/Autoloader.php';

use Workerman\Worker;
use Workerman\Connection\AsyncTcpConnection;


/*
*订阅数据函数
$sub_str type: string e.g market.btcusdt.depth.step0 具体请查看api
$callback type: function 回调函数，当获得数据时会调用
*/
function subscribe($callback, $sub_str="market.btcusdt.depth.step0") {
    $GLOBALS['sub_str'] = $sub_str;
    $GLOBALS['callback'] = $callback;
    $worker = new Worker();
    $worker->onWorkerStart = function($worker) {
        // ssl需要访问443端口
        $con = new AsyncTcpConnection('ws://api.huobi.pro:443/ws');

        // 设置以ssl加密方式访问，使之成为wss
        $con->transport = 'ssl';

        $con->onConnect = function($con) {
            $data = json_encode([
                'sub' => $GLOBALS['sub_str'],
                'id' => 'depth' . time()
            ]);
            $con->send($data);
        };

        $con->onMessage = function($con, $data) {
            $data = gzdecode($data);
            $data = json_decode($data, true);
            if(isset($data['ping'])) {
                $con->send(json_encode([
                    "pong" => $data['ping']
                ]));
            }else{
                call_user_func_array($GLOBALS['callback'], array($data));           
            }
        };

        $con->connect();
    };

    Worker::runAll();
}



/*
*订阅数据函数   'btcusdt@depth'
$sub_str type: string e.g market.btcusdt.depth.step0 具体请查看api
$callback type: function 回调函数，当获得数据时会调用
*/
function subscribeForBinance($callback, $sub_str="btcusdt.depth") {
    $GLOBALS['sub_str'] = $sub_str;
    $GLOBALS['callback'] = $callback;
    $worker = new Worker();
    $worker->onWorkerStart = function($worker) {
        // ssl需要访问443端口
        $con = new AsyncTcpConnection('ws://stream.binance.com:9443/ws/' . $sub_str);

        // 设置以ssl加密方式访问，使之成为wss
        $con->transport = 'ssl';

        $con->onMessage = function($con, $data) {
            var_dump($data);
        };
        $con->connect();
    };

    Worker::runAll();
}