<?php

// 定义参数
define('ACCOUNT_ID', '3141076'); // 你的账户ID  spot：3141076，otc：3141141
define('ACCESS_KEY','5d945ba9-1639e024-52ed4d01-7d575'); // 你的ACCESS_KEY
define('SECRET_KEY', '2c8692de-6b63b059-bb212a5f-0d2f1'); // 你的SECRET_KEY


include "huobiapi.php";

$huobiapi = new Huobiapi();

// $accountList = $huobiapi->get_account_accounts();

//查询账户余额
//$balance = $huobiapi->get_balance();  
//var_dump($balance);
//die;
// 市价买
$symbol = 'btcusdt';
$qty = '1';
//$trade_id = $huobiapi->marketBuy($symbol, $qty); 
// 市价卖
$qty = '0.001';
//$trade_id = $huobiapi->marketSell($symbol, $qty);    
// 限价买
$price = '6653.17';
//$trade_id = $huobiapi->limitBuy($symbol, $qty,$price); 

// 限价卖
//$trade_id = $huobiapi->limitSell($symbol, $qty,$price);  

$wallet = '17ccfC1RR2SLyrZiBgGVEsUfP5T17iZ9s5';
// 提现
$symbol='btc';
$withdraw_id = $huobiapi->withdraw($symbol, $qty, $wallet);     //把qty个symbol币提现到wallet账号，并且返回withdraw_id作为唯一标识

var_dump($withdraw_id);die;
//$trade_id = 2932505965;
// 交易详情
//$orderDetail = $huobiapi->get_order($trade_id); 
//var_dump($orderDetail);
// 交易明细
//$list = $huobiapi->get_order_matchresults($trade_id); 
//var_dump($list);
