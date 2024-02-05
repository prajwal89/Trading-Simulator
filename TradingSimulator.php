<?php

// todo configurable position size of each trade
// todo validate inputs
// todo average time taken for trade

class TradingSimulator
{

    /**
     * @var int Initial balance for the trading simulation.
     */
    private int $initialBalance;

    /**
     * @var int Win rate percentage for the trading simulation.
     */
    private int $winRate;

    /**
     * @var float Risk to reward ratio for the trading simulation.
     */
    private float $riskRewardRatio;

    /**
     * @var int Total number of trades in the simulation.
     */
    private int $totalTradesCount;

    /**
     * @var bool Flag indicating whether compounding is enabled or not.
     */
    private bool $enableCompounding = true;

    /**
     * @var float Platform fee rate for each trade in the simulation.
     */
    private float $platformFeeRate = 0.0;

    /**
     * @var array
     */
    private array $finalResults;


    /**
     * Set the initial balance for the simulation.
     *
     * @param int $amount The initial balance amount.
     * @return $this
     */
    public function initialBalance(int $amount): self
    {
        $this->initialBalance = $amount;
        return $this;
    }

    /**
     * Set the win rate percentage for the simulation.
     *
     * @param int $percentage The win rate percentage.
     * @return $this
     */
    public function winRate(int $percentage): self
    {
        $this->winRate = $percentage;
        return $this;
    }

    /**
     * Set the risk to reward ratio for the simulation.
     *
     * @param float $riskRewardRatio The risk to reward ratio.
     * @return $this
     */
    public function riskRewardRatio(float $riskRewardRatio): self
    {
        $this->riskRewardRatio = $riskRewardRatio;
        return $this;
    }

    /**
     * Set the total number of trades for the simulation.
     *
     * @param int $totalTradesCount The total number of trades.
     * @return $this
     */
    public function totalTradesCount(int $totalTradesCount): self
    {
        $this->totalTradesCount = $totalTradesCount;
        return $this;
    }

    /**
     * Enable or disable compounding for the simulation.
     *
     * @param bool $shouldCompound Whether compounding should be enabled or not.
     * @return $this
     */
    public function enableCompounding(bool $shouldCompound): self
    {
        $this->enableCompounding = $shouldCompound;
        return $this;
    }

    /**
     * Set the platform fee rate for each trade in the simulation.
     *
     * @param float $percentage The platform fee rate as a percentage.
     * @return $this
     */
    public function platformFeeRate(float $percentage): self
    {
        $this->platformFeeRate = $percentage;
        return $this;
    }

    /**
     * Simulate the trading scenario and return the results.
     *
     * @return $this
     */
    public function simulate(): self
    {
        $this->validateInputs();

        $tradeResults = [];

        $tradeResults['balance'] = $this->initialBalance;

        $arrayOfWinsAndLoses = $this->generateTradeResults();

        $totalFeePaid = 0;

        foreach ($arrayOfWinsAndLoses as $isWin) {
            // * size of each trade will be $principle amount

            $pnl = 0;
            $positionSize = 0;

            // calculate PNL
            if ($this->enableCompounding) {
                $positionSize =  $tradeResults['balance'];
            } else {
                $positionSize =  $this->initialBalance;
            }

            if ($isWin) {
                $pnl = ($positionSize / 100) * $this->riskRewardRatio;
            } else {
                // 1 b.c  1/$this->riskRewardRatio
                $pnl = - ($positionSize / 100) * 1;
            }

            // deduct platform fee 
            if ($this->platformFeeRate !== 0.0) {
                $fee = ($this->platformFeeRate / 100) * $positionSize;
                $pnl -= $fee;
                $totalFeePaid += $fee;
            }

            $pnl = round($pnl, 2);

            $tradeResults['balance'] += $pnl;

            $tradeResults['trades'][] = [
                'pnl' => $pnl,
                'balance' => $tradeResults['balance'],
                'fee' => round($fee, 2),
            ];
        }

        $this->finalResults = array_merge(
            ['parameters' => $this->toArray()],
            ['result' => $this->getStats($tradeResults['trades'])],
            $tradeResults
        );

        return $this;
    }

    /**
     * Calculate and return statistics based on the provided trades.
     *
     * @param array $trades Array containing trade information.
     * @return array Trading statistics.
     */
    private function getStats(array $trades): array
    {
        $totalFeePaid = round(array_sum(array_map(function ($trade) {
            return $trade['fee'];
        }, $trades)), 2);

        $finalBalance = end($trades)['balance'];

        $grossProfit = $finalBalance - $this->initialBalance;

        $grossProfitPercentage = round($grossProfit / ($this->initialBalance / 100), 2);

        $netProfit = $grossProfit - $totalFeePaid;

        $netProfitPercentage = round($netProfit / ($this->initialBalance / 100), 2);

        $mdd = $this->calculateMaxDrawdown($trades);

        $totalFeePaid = round($totalFeePaid, 2);

        $stats = [
            'balance' => $finalBalance,
            'fee' => $totalFeePaid,
            'mdd' => $mdd,
            'gross' => [
                'pnl' => $grossProfit,
                'percentage' => $grossProfitPercentage
            ],
            'net' => [
                'pnl' => $netProfit,
                'percentage' => $netProfitPercentage
            ]
        ];

        return $stats;
    }


    public function getResults(): array
    {
        return $this->finalResults;
    }

    /**
     * Display the formatted results to the console.
     *
     * @param array $results Array containing simulation results.
     * @return void
     */
    public function printResults(): void
    {
        $formattedResults = [
            "Final Balance: " . $this->finalResults['result']['balance'] . "$",
            "Gross Profit: " . $this->finalResults['result']['gross']['pnl'] . "$ (" . $this->finalResults['result']['gross']['percentage'] . "%)",
            "Net Profit: " . $this->finalResults['result']['net']['pnl'] . "$ (" . $this->finalResults['result']['net']['percentage'] . "%)",
            "Total Fee Paid: " . $this->finalResults['result']['fee'] . "$",
            "MDD: " . $this->finalResults['result']['mdd'] . "%"
        ];

        foreach ($formattedResults as $line) {
            $this->print($line, 'success');
        }
    }

    private function validateInputs()
    {
        // fee,principle amount, 
    }

    /**
     * Generate an array representing the results of individual trades.
     *
     * @return array Array indicating whether each trade is a win or loss.
     */
    private function generateTradeResults()
    {
        $winCount = $this->totalTradesCount * $this->winRate / 100;
        $lossCount = $this->totalTradesCount - $winCount;

        $tradeResults = array_fill(0, $this->totalTradesCount, false); // Initialize the array with all losses

        // Set random positions in the array to true for wins
        for ($i = 0; $i < $winCount; $i++) {
            $randomIndex = mt_rand(0, $this->totalTradesCount - 1);

            // Ensure the selected index is not already a win
            while ($tradeResults[$randomIndex]) {
                $randomIndex = mt_rand(0, $this->totalTradesCount - 1);
            }

            $tradeResults[$randomIndex] = true;
        }

        shuffle($tradeResults); // Shuffle the array to randomize the order

        return $tradeResults;
    }

    /**
     * Calculate the maximum drawdown based on the provided trades.
     *
     * @param array $trades Array containing trade information.
     * @return float Maximum drawdown percentage.
     */
    private function calculateMaxDrawdown(array $trades)
    {
        $maxDrawdown = 0;
        $drawdown = 0;
        $peakBalance = $this->initialBalance;

        foreach ($trades as $trade) {
            $peakBalance = max($peakBalance, $trade['balance']);
            $drawdown = min($drawdown, ($trade['balance'] - $peakBalance) / $peakBalance);
        }

        $maxDrawdown = min($maxDrawdown, $drawdown);

        return round($maxDrawdown * 100, 2);
    }

    /**
     * Print a message to the console with optional color.
     *
     * @param string $message The message to be printed.
     * @param string $color The color code for the console output.
     * @return void
     */
    private function print(string $message, string $color = 'default')
    {
        $colors = [
            'default' => "\033[0m",   // Reset to default color
            'success' => "\033[0;32m", // Green
            'error' => "\033[0;31m",   // Red
            'info' => "\033[0;36m"     // Cyan
        ];

        if (!isset($colors[$color])) {
            $color = 'default';
        }

        echo $colors[$color] . $message . $colors['default'] . PHP_EOL;
    }

    /**
     * Convert the class object to an associative array.
     *
     * @return array
     */
    public function toArray()
    {
        $properties = get_object_vars($this);

        $array = [];
        foreach ($properties as $property => $value) {
            // You can customize the transformation if needed
            $array[$property] = $value;
        }

        return $array;
    }
}

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
