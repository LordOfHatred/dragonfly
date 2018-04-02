<?php


require 'binance_api.php';

define('ACCESS_KEY','aWqwam7rRzJSLjAfotNP1KGO2S7Bcp6Fc0OcpNdoGngVxNHaYgO1bsDMAb4IavWD'); // 你的ACCESS_KEY
define('SECRET_KEY', 'oCgevuF6sag1ppFxOnoDwElYcdFocY26cYfIWIIdgLXdO6GexPjtcLHCfc8KyxH4'); // 你的SECRET_KEY

$api = new Binance(ACCESS_KEY, SECRET_KEY);

$ticker = $api->prices();
print_r($ticker); // List prices of all symbols 
echo "Price of BNB: {$ticker['BNBBTC']} BTC.".PHP_EOL;

// Get balances for all of your positions, including estimated BTC value
$balances = $api->balances($ticker);
print_r($balances);
echo "BTC owned: ".$balances['BTC']['available'].PHP_EOL;
echo "ETH owned: ".$balances['ETH']['available'].PHP_EOL;
echo "Estimated Value: ".$api->btc_value." BTC".PHP_EOL;

// Getting 24hr ticker price change statistics for a symbol
$prevDay = $api->prevDay("BNBBTC");
print_r($prevDay);
echo "BNB price change since yesterday: ".$prevDay['priceChangePercent']."%".PHP_EOL;
