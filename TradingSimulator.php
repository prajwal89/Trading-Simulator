<?php

// todo add support simulating multiple batches
// todo configurable position size of each trade
// todo calculate average of batches
// todo validate inputs
// todo arrayable output for simulation

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

    public function simulate(int $times = 1): void
    {
        $this->validateInputs();

        // save results of each batch
        $tradeResults = [];

        $times = 1;
        for ($batchNo = 1; $batchNo <= $times; $batchNo++) {
            // initial balance is principle amount
            $tradeResults[$batchNo]['balance'] = $this->principleAmount;

            // simulate each trade set
            // $this->print('Simulating batchNo: ' . $batchNo, 'info');

            $arrayOfWinsAndLoses = $this->generateTradeResults();

            // simulate each trade of trade set
            $totalFeePaid = 0;
            foreach ($arrayOfWinsAndLoses as $isWin) {
                // * size of each trade will be $principle amount

                $pnl = 0;
                $finalBalanceForSet = 0;
                $positionSize = 0;

                // calculate PNL
                if ($this->compounding) {
                    $positionSize =  $tradeResults[$batchNo]['balance'];
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

                $tradeResults[$batchNo]['balance'] += $pnl;

                $tradeResults[$batchNo]['trades'][] = [
                    'pnl' => $pnl,
                    'balance' => $tradeResults[$batchNo]['balance']
                ];
            }

            $finalBalanceForSet = end($tradeResults[$batchNo]['trades'])['balance'];

            $grossProfit = $finalBalanceForSet - $this->principleAmount;

            $grossProfitPercentage = round($grossProfit / ($this->principleAmount / 100), 2);

            $netProfit = $grossProfit - $totalFeePaid;

            $netProfitPercentage = round($netProfit / ($this->principleAmount / 100), 2);

            $mdd = $this->calculateMaxDrawdown($tradeResults[$batchNo]['trades']);

            $totalFeePaid = round($totalFeePaid, 2);

            $this->print("Final Balance: {$finalBalanceForSet}$", 'success');
            $this->print("Gross PNL: {$grossProfit}$ ({$grossProfitPercentage}%)", 'success');
            $this->print("Net PNL: {$netProfit}$ ({$netProfitPercentage}%)", 'success');
            $this->print("Fee: {$totalFeePaid}$", 'success');
            $this->print("MDD: {$mdd}%", 'success');
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
$simulator->principleAmount(1000)
    ->totalTrades(100)
    ->winPercentage(50)
    ->riskToRewardRatio(2.5)
    ->platformFee(0.1)
    ->compounding(true)
    ->simulate();
