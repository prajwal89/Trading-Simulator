# Trading Simulator

A PHP class for simulating trading scenarios based on specified parameters like initial balance, win rate, risk-reward ratio, total trades, compounding, and platform fees.

## Table of Contents

- [Introduction](#introduction)
- [Usage](#usage)
- [Methods](#methods)
- [Example](#example)
- [Todo](#todo)

## Introduction

The `TradingSimulator` class allows you to simulate trading scenarios and analyze the results based on user-defined parameters. It calculates the final balance, gross and net profit, maximum drawdown, and other statistics.

## Usage

To use the `TradingSimulator` class, follow these steps:

1. Create an instance of the `TradingSimulator` class.
2. Set the simulation parameters using the provided methods.
3. Call the `simulate` method to run the simulation and get the results.

## Methods

### `initialBalance(int $amount): self`

Set the initial balance for the simulation.

### `winRate(int $percentage): self`

Set the win rate percentage for the simulation.

### `riskRewardRatio(float $riskRewardRatio): self`

Set the risk to reward ratio for the simulation.

### `totalTradesCount(int $totalTradesCount): self`

Set the total number of trades for the simulation.

### `enableCompounding(bool $shouldCompound): self`

Enable or disable compounding for the simulation.

### `platformFeeRate(float $percentage): self`

Set the platform fee rate for each trade in the simulation.

### `simulate(): array`

Run the simulation and return an array containing the results.

### `printResults(array $results): void`

Display the formatted results to the console.

### `validateInputs(): void`

Validate the inputs before starting the simulation.

### `generateTradeResults(): array`

Generate an array representing the results of individual trades.

### `calculateMaxDrawdown(array $trades): float`

Calculate the maximum drawdown based on the provided trades.

### `print(string $message, string $color = 'default'): void`

Print a message to the console with optional color.

## Example Usage

```php
$simulator = new TradingSimulator();
$results = $simulator->initialBalance(1000)
    ->totalTradesCount(10)
    ->winRate(50)
    ->riskRewardRatio(2)
    ->platformFeeRate(0.1)
    ->enableCompounding(true)
    ->simulate();

var_dump($results);
