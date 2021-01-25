<?php

class TransactionHelper extends Helper
{

    public function getAllTransactions()
    {
        return $this->query("SELECT * FROM Categories");
    }

    public function getExpenses($startDate, $endDate) {
        return $this->query("SELECT * FROM Transactions WHERE categoryID IS NOT NULL AND date >= ? AND date <= ?", $this->normalizeDate($startDate), $this->normalizeDate($endDate));
    }

    public function getExpensesByCategory($categoryID, $startDate, $endDate)
    {
        return $this->query("SELECT * FROM Transactions WHERE categoryID = ? AND date >= ? AND date <= ?", $categoryID, $this->normalizeDate($startDate), $this->normalizeDate($endDate));
    }

    public function getIncome($startDate, $endDate) {
        return $this->query("SELECT * FROM Transactions WHERE clientID IS NOT NULL AND date >= ? AND date <= ?", $this->normalizeDate($startDate), $this->normalizeDate($endDate));
    }

    public function getIncomeByClient($clientID, $startDate, $endDate)
    {
        return $this->query("SELECT * FROM Transactions WHERE clientID = ? AND date >= ? AND date <= ?", $clientID, $this->normalizeDate($startDate), $this->normalizeDate($endDate));
    }

    public function estimateExpensesByCategory($categoryID, $year, $month) {
        $transactions = $this->getExpensesByCategory($categoryID, $year - 1 . '-' . $month . '-01', $year - 1 . '-12-t');

        $total = 0.0;
        foreach ($transactions as $datum) {
            $total += $datum['amount'];
        }

        return number_format($total / (12-$month+1), 0);
    }

    public function estimateIncomeByClient($clientID, $year, $month) {
        $transactions = $this->getIncomeByClient($clientID, $year . '-01-01', $year . '-12-t');

        $total = 0.0;
        $count = 0;
        foreach ($transactions as $transaction) {
            $total += $transaction['amount'];
            $count += 1;
        }

        if ($count == 0) {
            return "0";
        }


        return number_format($total / $count, 0);
    }

    public function createTransaction($data)
    {
        if ($data['type'] == "income") {
            if ($data['creditCardFee'] > 0) {
                $clientHelper = new ClientHelper($this->config);
                $clientName = $clientHelper->getClientByID($data['client'])['title'];

                $creditCardData = array(
                    'title' => $clientName . ' - ' . $data['title'] . " bank fee",
                    'amount' => $data['creditCardFee'],
                    'date' => $data['date'],
                    'category' => 0,
                    'type' => 'expense',
                    'spread' => 1
                );
                if ($this->createTransaction($creditCardData)) {
                    $creditCardTransId = $this->getLastInsertID();
                } else {
                    return false;
                }
            } else {
                $creditCardTransId = null;
            }

            if ($data['spread'] > 1) {

                $newAmount = $data['amount'] / $data['spread'];
                for ($i = 1; $i < $data['spread']; $i++) {
                    $transData = array(
                        'title' => $data['title'] . " <i>(Spread)</i>",
                        'amount' => $newAmount,
                        'date' => date('Y-m-01', strtotime('+ ' . $i . ' month')),
                        'client' => $data['client'],
                        'type' => 'income'
                    );
                    if (!$this->createTransaction($transData)) {
                        return false;
                    }
                }
            } else {
                $newAmount = $data['amount'];
            }

            return $this->query("INSERT INTO Transactions (title, amount, date, clientID, linkedTransactionID) VALUES (?, ?, ?, ?, ?)", $data['title'], $newAmount, $data['date'], $data['client'], $creditCardTransId);
        } else {
            if ($data['spread'] > 1) {

                $newAmount = $data['amount'] / $data['spread'];
                for ($i = 1; $i < $data['spread']; $i++) {
                    $transData = array(
                        'title' => $data['title'] . " <i>(Spread)</i>",
                        'amount' => $newAmount,
                        'date' => date('Y-m-01', strtotime('+' . $i . ' month', date("Y-m-d"))),
                        'category' => $data['category'],
                        'type' => 'expense'
                    );
                    $this->createTransaction($transData);
                }
            } else {
                $newAmount = $data['amount'];
            }

            return $this->query("INSERT INTO Transactions (title, amount, date, categoryID) VALUES (?, ?, ?, ?)", $data['title'], $newAmount, $data['date'], $data['category']);
        }
    }
    private function normalizeDate($input) {
        if (strpos($input, "-t") === false) {
            return date($input);
        } else {
            $components = explode("-", $input);
            return date($input, strtotime($components[0] . '-'. $components . '-01'));
        }
    }
}
