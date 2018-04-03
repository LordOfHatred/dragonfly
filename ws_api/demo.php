<?php

require_once __DIR__ . '/subscribe.php';

//  火币
subscribeForHuobi(function($data) {
    $ch = isset($data['ch']) ? $data['ch'] : '';
    echo 'huobi:', $ch , "\n";
    if(isset($data['tick']) && isset($data['tick']['bids']) && isset($data['tick']['asks'])) {
        analyzeForHuobi($data['tick']['bids'], $data['tick']['asks']);
    }
}, 'market.btcusdt.depth.step0');

/*
    Top <levels> bids and asks, pushed every second. Valid <levels> are 5, 10, or 20.

    Stream Name: <symbol>@depth<levels>
*/
subscribeForBinance(function($data) {
    echo 'binance';
    print_r($data);
    if(isset($data['bids']) && isset($data['asks'])) {
        analyzeForBinance($data['bids'], $data['asks']);
    }
}, 'btcusdt@depth5');

runAll();


//这里是我的代码，$bid是行情里面的买入价表，$ask是卖出价表
function analyzeForHuobi($bids, $asks)
{
    //var_dump($bids);
    // var_dump($asks);
    echo "Huobi got new data\n";
}

//这里是我的代码，$bid是行情里面的买入价表，$ask是卖出价表
function analyzeForBinance($bids, $asks)
{
    //var_dump($bids);
    // var_dump($asks);
    echo "Binance got new data\n";
}
