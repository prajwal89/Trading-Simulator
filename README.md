# Trading Simulator

The Trading Simulator is a PHP class designed to simulate a trading scenario based on specified parameters such as initial balance, win rate, risk-to-reward ratio, total number of trades, platform fee rate, and compounding settings.

## Features

- Simulate trading scenarios with configurable parameters.
- Calculate and display various trading statistics.
- Option to enable or disable compounding for the simulation.
- Adjustable risk-to-reward ratio for each trade.
- Platform fee rate consideration for accurate results.

## Getting Started

1. Include the `TradingSimulator` class in your PHP project.
2. Create an instance of the `TradingSimulator` class.
3. Set the desired parameters using the provided methods.
4. Run the simulation using the `simulate()` method.
5. Retrieve and analyze the results using the `getResults()` method or display them using `printResults()`.

## Example Usage

```php
// Create a new instance of the TradingSimulator class
$simulator = new TradingSimulator();

// Set simulation parameters
$results = $simulator->initialBalance(1000)
    ->totalTradesCount(10)
    ->winRate(50)
    ->riskRewardRatio(2)
    ->platformFeeRate(0.1)
    ->enableCompounding(true)
    ->simulate()
    ->getResults();

// Display the results
var_dump($results);
```

## Live Demo

We've developed an interactive **Trading Simulator**.
Check it out [here](https://onlineminitools.com/trading-simulator).
