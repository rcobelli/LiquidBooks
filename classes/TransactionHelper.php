<?php

class TransactionHelper extends Helper
{

    public function getExpenses($startDate, $endDate) {
        return $this->query("SELECT transactionID, title, amount, date, categoryID, linkedTransactionID FROM Transactions WHERE categoryID IS NOT NULL AND date >= ? AND date <= ? ORDER BY date", $this->normalizeDate($startDate), $this->normalizeDate($endDate));
    }

    public function getExpensesByCategory($categoryID, $startDate, $endDate)
    {
        return $this->query("SELECT transactionID, title, amount, date, categoryID, linkedTransactionID FROM Transactions WHERE categoryID = ? AND date >= ? AND date <= ? ORDER BY date", $categoryID, $this->normalizeDate($startDate), $this->normalizeDate($endDate));
    }

    public function getIncome($startDate, $endDate) {
        return $this->query("SELECT transactionID, title, amount, date, clientID, linkedTransactionID FROM Transactions WHERE clientID IS NOT NULL AND date >= ? AND date <= ? ORDER BY date", $this->normalizeDate($startDate), $this->normalizeDate($endDate));
    }

    public function getIncomeByClient($clientID, $startDate, $endDate)
    {
        return $this->query("SELECT transactionID, title, amount, date, clientID, linkedTransactionID FROM Transactions WHERE clientID = ? AND date >= ? AND date <= ? ORDER BY date", $clientID, $this->normalizeDate($startDate), $this->normalizeDate($endDate));
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
        $data['date'] = date('Y-m-d', strtotime($data['date']));
        $data['time'] = strtotime($data['date']);

        if ($data['type'] == "income") {
            return $this->processIncome($data);
        } else {
            return $this->processExpense($data);
        }
    }

    private function processIncome($data) {
        if ($data['creditCardFee'] > 0) {
            $clientHelper = new ClientHelper($this->config);
            $clientName = $clientHelper->getClientByID($data['client'])['title'];

            $creditCardData = array(
                'title' => $clientName . ' - ' . $data['title'] . " bank fee",
                'amount' => $data['creditCardFee'],
                'date' => $data['date'],
                'category' => 0,
                'type' => 'expense'
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
            $spreadDates = $this->generateSpreadDates($data);
            $newAmount = $data['amount'] / $data['spread'];

            foreach ($spreadDates as $newDate) {
                $transData = array(
                    'title' => $data['title'] . " <i>(Spread)</i>",
                    'amount' => $newAmount,
                    'date' => date('Y-m-d', $newDate),
                    'client' => $data['client'],
                    'type' => 'income'
                );
                if (!$this->createTransaction($transData)) {
                    return false;
                }
            }

            return true;
        }

        return $this->query("INSERT INTO Transactions (title, amount, date, clientID, linkedTransactionID) VALUES (?, ?, ?, ?, ?)", $data['title'], $data['amount'], $data['date'], $data['client'], $creditCardTransId);
    }

    private function processExpense($data) {
        if ($data['spread'] > 1) {
            $spreadDates = $this->generateSpreadDates($data);
            $newAmount = $data['amount'] / $data['spread'];

            foreach ($spreadDates as $newDate) {
                $transData = array(
                    'title' => $data['title'] . " <i>(Spread)</i>",
                    'amount' => $newAmount,
                    'date' => date('Y-m-d', $newDate),
                    'category' => $data['category'],
                    'type' => 'expense'
                );
                if (!$this->createTransaction($transData)) {
                    return false;
                }
            }

            return true;
        }

        return $this->query("INSERT INTO Transactions (title, amount, date, categoryID) VALUES (?, ?, ?, ?)", $data['title'], $data['amount'], $data['date'], $data['category']);
    }

    private function generateSpreadDates($data) {
        $spreadDates = array();

        if ($data['backdate'] == "on") {
            if ($data['spread'] == 3) {
                // Get the first day of the quarter
                $quarter = $this->getQuarterStart(date('n', $data['time']));
                $base = date('Y-' . $quarter . '-01');

                // Add a transaction for the first day of each month
                for ($i = 0; $i < 3; $i++) {
                    array_push($spreadDates, strtotime($base . ' + ' . $i . ' month'));
                }

                // Remove the transaction for the original month and add it back on the correct day
                unset($spreadDates[(date('n', $data['time']) - 1) % 3]);
                array_push($spreadDates, $data['time']);
            }
            else if ($data['spread'] == 12) {
                $base = date('Y-01-01', $data['time']);

                // Add a transaction for the first day of each month
                for ($i = 0; $i < 12; $i++) {
                    array_push($spreadDates, strtotime($base . ' + ' . $i . ' month'));
                }

                // Remove the transaction for the original month and add it back on the correct day
                unset($spreadDates[date('n', $data['time']) - 1]);
                array_push($spreadDates, $data['time']);
            }
        } else {
            array_push($spreadDates, strtotime($data['date']));

            $base = date('Y-m-01', $data['time']);
            for ($i = 1; $i < $data['spread']; $i++) {
                array_push($spreadDates, strtotime($base . ' + ' . $i . ' month'));
            }
        }

        return $spreadDates;
    }

    private function getQuarterStart($month) {
        switch ($month) {
            case 1:
            case 2:
            case 3:
                return 1;
            case 4:
            case 5:
            case 6:
                return 4;
            case 7:
            case 8:
            case 9:
                return 7;
            default:
                return 10;
        }
    }

    private function normalizeDate($input) {
        if (strpos($input, "-t") === false) {
            return date($input);
        } else {
            $components = explode("-", $input);
            return date($input, strtotime($components[0] . '-'. $components[1] . '-01'));
        }
    }
}
