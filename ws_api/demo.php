<?php

require_once __DIR__ . '/subscribe.php';

/*
subscribe(function($data) {
    if(isset($data['tick']) && isset($data['tick']['bids']) && isset($data['tick']['asks'])) {
        analyze($data['tick']['bids'], $data['tick']['asks']);
    }
}, 'market.btcusdt.depth.step0');*/

//这里是我的代码，$bid是行情里面的买入价表，$ask是卖出价表
function analyze($bids, $asks)
{
    var_dump($bids);
    var_dump($asks);
}

subscribeForBinance(function($data) {
    if(isset($data['bids']) && isset($data['asks'])) {
        analyze($data['bids'], $data['asks']);
    }
}, 'btcusdt@depth');
