<?php

include __DIR__ . '/TradingSimulator.php';

// Example usage:
$simulator = new TradingSimulator();
$results = $simulator->initialBalance(1000)
    ->totalTradesCount(10)
    ->winRate(50)
    ->riskRewardRatio(2)
    ->platformFeeRate(0.1)
    ->enableCompounding(true)
    ->simulate()
    ->getResults();
// ->printResults();

var_dump($results);
