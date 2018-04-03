<?php
 
require_once __DIR__ . '/workerman_linux/Autoloader.php';

use Workerman\Worker;
use Workerman\Connection\AsyncTcpConnection;


$GLOBALS['huobi']['sub_str'] = '';
$GLOBALS['huobi']['callback'] = '';

$GLOBALS['binance']['sub_str'] = '';
$GLOBALS['binance']['callback'] = '';

$worker = new Worker();
$worker->onWorkerStart = function($worker) {
    // 火币网
    if($GLOBALS['huobi']['sub_str'] && $GLOBALS['huobi']['callback']) {
        // ssl需要访问443端口
        $conHuobi = new AsyncTcpConnection('ws://api.huobi.pro:443/ws');
        $conHuobi->transport = 'ssl';
        $conHuobi->onConnect = function($con) {
            $data = json_encode([
                'sub' => $GLOBALS['huobi']['sub_str'],
                'id' => 'depth' . time()
            ]);
            $con->send($data);
        };

        $conHuobi->onMessage = function($con, $data) {
            $data = gzdecode($data);
            $data = json_decode($data, true);
            if(isset($data['ping'])) {
                $con->send(json_encode([
                    "pong" => $data['ping']
                ]));
            }else{
                call_user_func_array($GLOBALS['huobi']['callback'], array($data));           
            }
        };
        $conHuobi->connect();
    }

    // binance
    if($GLOBALS['binance']['sub_str'] && $GLOBALS['binance']['callback']) {
        // ssl需要访问443端口
        $con = new AsyncTcpConnection('ws://stream.binance.com:9443/ws/' . $GLOBALS['binance']['sub_str']);
        // 设置以ssl加密方式访问，使之成为wss
        $con->transport = 'ssl';
        $con->onConnect = function($con) {
        };
        $con->onMessage = function($con, $data) {
            $data = json_decode($data, true);
            if(isset($data['ping'])) {
                $con->send(json_encode([
                    "pong" => $data['ping']
                ]));
            }else{
                call_user_func_array($GLOBALS['binance']['callback'], array($data));           
            }
        };
        $con->connect();
    }

};




/*
*订阅数据函数
$sub_str type: string e.g market.btcusdt.depth.step0 具体请查看api
$callback type: function 回调函数，当获得数据时会调用
*/
function subscribeForHuobi($callback, $sub_str="market.btcusdt.depth.step0") {
    $GLOBALS['huobi']['sub_str'] = $sub_str;
    $GLOBALS['huobi']['callback'] = $callback;
}



/*
*订阅数据函数   'btcusdt@depth'
$sub_str type: string e.g market.btcusdt.depth.step0 具体请查看api
$callback type: function 回调函数，当获得数据时会调用
*/
function subscribeForBinance($callback, $sub_str="btcusdt@depth5") {
    $GLOBALS['binance']['sub_str'] = $sub_str;
    $GLOBALS['binance']['callback'] = $callback;
}

function runAll()
{
    Worker::runAll();
}