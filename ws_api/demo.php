<?php

require_once __DIR__ . '/subscribe.php';

subscribe(function($data) {
    var_dump($data);
});