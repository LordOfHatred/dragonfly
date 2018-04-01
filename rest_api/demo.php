<?php

// 定义参数
define('ACCOUNT_ID', ''); // 你的账户ID 
define('ACCESS_KEY','5d945ba9-1639e024-52ed4d01-7d575'); // 你的ACCESS_KEY
define('SECRET_KEY', '2c8692de-6b63b059-bb212a5f-0d2f1'); // 你的SECRET_KEY


include "huobiapi.php";

$huobiapi = new Huobiapi();

$accountList = $huobiapi->get_account_accounts();
print_r($accountList);die;

echo $my_acc -> get_balance();  //查询账户余额

$trade_id = $my_acc -> buy($symbol_pair, $qty); //购买qty个symbol_pair交易（symbol_pair交易指的就是类似“用美元token买入比特币”），并且返回唯一的交易标示trade_id
$trade_id = $my_acc -> sell($symbol_pair, $qty);    //卖出qty个symbol_pair交易（symbol_pair交易指的就是类似“用美元token买入比特币”），并且返回唯一的交易标示trade_id
$withdraw_id = $my_acc -> withdraw($symbol, $qty, $wallet);     //把qty个symbol币提现到wallet账号，并且返回withdraw_id作为唯一标识

$res = $my_acc -> get_trade_result($trade_id);  //获取某次交易的结果
$res = $my_acc -> get_withdraw_result($withdraw_id);    //获取某次提现的结果