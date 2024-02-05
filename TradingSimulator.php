<?php

// todo configurable position size of each trade
// todo validate inputs
// todo average time taken for trade

class TradingSimulator
{

    private int $principleAmount;
    private int $winPercentage;
    private float $riskToRewardRatio;
    private int $totalTrades;
    private bool $compounding = true;
    private float $platformFee = 0.0;

    public function principleAmount(int $amount): self
    {
        $this->principleAmount = $amount;
        return $this;
    }

    public function winPercentage(int $percentage): self
    {
        $this->winPercentage = $percentage;
        return $this;
    }

    public function riskToRewardRatio(float $riskToRewardRatio): self
    {
        $this->riskToRewardRatio = $riskToRewardRatio;
        return $this;
    }

    public function totalTrades(int $totalTrades): self
    {
        $this->totalTrades = $totalTrades;
        return $this;
    }

    public function compounding(bool $shouldCompound): self
    {
        $this->compounding = $shouldCompound;
        return $this;
    }

    public function platformFee(float $percentage): self
    {
        $this->platformFee = $percentage;
        return $this;
    }

    public function simulate(): array
    {
        $this->validateInputs();

        $tradeResults = [];

        $tradeResults['balance'] = $this->principleAmount;

        $arrayOfWinsAndLoses = $this->generateTradeResults();

        $totalFeePaid = 0;

        foreach ($arrayOfWinsAndLoses as $isWin) {
            // * size of each trade will be $principle amount

            $pnl = 0;
            $positionSize = 0;

            // calculate PNL
            if ($this->compounding) {
                $positionSize =  $tradeResults['balance'];
            } else {
                $positionSize =  $this->principleAmount;
            }

            if ($isWin) {
                $pnl = ($positionSize / 100) * $this->riskToRewardRatio;
            } else {
                // 1 b.c  1/$this->riskToRewardRatio
                $pnl = - ($positionSize / 100) * 1;
            }

            // deduct platform fee 
            if ($this->platformFee !== 0.0) {
                $fee = ($this->platformFee / 100) * $positionSize;
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

        $tradeResults = array_merge(
            $this->getStats($tradeResults['trades']),
            $tradeResults
        );

        return $tradeResults;
    }


    private function getStats(array $trades): array
    {
        $totalFeePaid = round(array_sum(array_map(function ($trade) {
            return $trade['fee'];
        }, $trades)), 2);

        $finalBalance = end($trades)['balance'];

        $grossProfit = $finalBalance - $this->principleAmount;

        $grossProfitPercentage = round($grossProfit / ($this->principleAmount / 100), 2);

        $netProfit = $grossProfit - $totalFeePaid;

        $netProfitPercentage = round($netProfit / ($this->principleAmount / 100), 2);

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

    private function printResults(array $results): void
    {
        $formattedResults = [
            "Final Balance: " . $results['balance'] . "$",
            "Gross Profit: " . $results['gross']['pnl'] . "$ (" . $results['gross']['percentage'] . "%)",
            "Net Profit: " . $results['net']['pnl'] . "$ (" . $results['net']['percentage'] . "%)",
            "Total Fee Paid: " . $results['fee'] . "$",
            "MDD: " . $results['mdd'] . "%"
        ];

        foreach ($formattedResults as $line) {
            $this->print($line, 'success');
        }
    }

    private function validateInputs()
    {
        // fee,principle amount, 
    }

    private function generateTradeResults()
    {
        $winCount = $this->totalTrades * $this->winPercentage / 100;
        $lossCount = $this->totalTrades - $winCount;

        $tradeResults = array_fill(0, $this->totalTrades, false); // Initialize the array with all losses

        // Set random positions in the array to true for wins
        for ($i = 0; $i < $winCount; $i++) {
            $randomIndex = mt_rand(0, $this->totalTrades - 1);

            // Ensure the selected index is not already a win
            while ($tradeResults[$randomIndex]) {
                $randomIndex = mt_rand(0, $this->totalTrades - 1);
            }

            $tradeResults[$randomIndex] = true;
        }

        shuffle($tradeResults); // Shuffle the array to randomize the order

        return $tradeResults;
    }

    private function calculateMaxDrawdown(array $trades)
    {
        $maxDrawdown = 0;
        $drawdown = 0;
        $peakBalance = $this->principleAmount;

        foreach ($trades as $trade) {
            $peakBalance = max($peakBalance, $trade['balance']);
            $drawdown = min($drawdown, ($trade['balance'] - $peakBalance) / $peakBalance);
        }

        $maxDrawdown = min($maxDrawdown, $drawdown);

        return round($maxDrawdown * 100, 2);
    }

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
}

// Example usage:
$simulator = new TradingSimulator();
$results = $simulator->principleAmount(1000)
    ->totalTrades(10)
    ->winPercentage(50)
    ->riskToRewardRatio(2)
    ->platformFee(0.1)
    ->compounding(true)
    ->simulate();

var_dump($results);
