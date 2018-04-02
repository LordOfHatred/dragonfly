<?php


require 'binance_api.php';

define('ACCESS_KEY','aWqwam7rRzJSLjAfotNP1KGO2S7Bcp6Fc0OcpNdoGngVxNHaYgO1bsDMAb4IavWD'); // 你的ACCESS_KEY
define('SECRET_KEY', 'oCgevuF6sag1ppFxOnoDwElYcdFocY26cYfIWIIdgLXdO6GexPjtcLHCfc8KyxH4'); // 你的SECRET_KEY

$api = new Binance(ACCESS_KEY, SECRET_KEY);

//查询账户余额
//$balances = $api->balances();
//print_r($balances);

// 市价买
$symbol = 'BTCUSDT';
$qty = '0.001';
//$trade_id = $api->marketBuy($symbol, $qty); 
//var_dump($trade_id);
//die;
// 市价卖
$qty = '0.01';
//$trade_id = $api->marketSell($symbol, $qty);
//var_dump($trade_id);
//die;

// 限价买
$price = '6653.17';
//$trade_id = $api->limitBuy($symbol, $qty,$price); 
//var_dump($trade_id);
//die;

// 限价卖
//$trade_id = $api->limitSell($symbol, $qty,$price);  
//var_dump($trade_id);
//die;


$wallet = '17ccfC1RR2SLyrZiBgGVEsUfP5T17iZ9s5';
// 提现
$symbol='btc';
$withdraw_id = $api->withdraw($symbol, $qty, $wallet);     //把qty个symbol币提现到wallet账号，并且返回withdraw_id作为唯一标识

var_dump($withdraw_id);die;


$trade_id = 'XwTmfSFpvH02tn2fU2UqGv';
// 交易详情
//$orderDetail = $api->orderStatus( $symbol, $trade_id); 
//var_dump($orderDetail);

