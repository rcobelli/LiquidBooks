<?php

include '../init.php';

$catHelper = new CategoryHelper($config);
$clientHelper = new ClientHelper($config);
$transHeper = new TransactionHelper($config);

// Site/page boilerplate
$site = new site('Liquid Books', $errors);
$site->addHeader("../includes/navbar.php");
init_site($site);

$page = new page();
$site->setPage($page);

$year = date('Y');

$currentYear = $year == date('Y');

// Start rendering the content
ob_start();

$data = array();
for ($i = 2017; $i <= date('Y'); $i++) {
    $data[$i] = "";
}

?>
    <button class="btn btn-dark float-right" onclick='window.location.href="yearOverview.php?year=<?php echo date('Y'); ?>"'>View Reality</button>
    <h1>Fiscal Year <?php echo $year;?> - <span style='color: red'>Forecast</span></h1>

    <table class="table accounting">
        <thead>
        <tr>
            <th>&nbsp;</th>
            <?php
            foreach ($months as $month) {
                echo "<th>" . $month . "</th>";
            }
            ?>
            <th>Total</th>
        </tr>
        </thead>
        <tbody>
        <?php
        $categories = $catHelper->getCategories();
        $expensesTotal = 0.0;
        foreach ($categories as $category) {
            ?>
            <tr>
                <th><?php echo $category['title']; ?></th>
                <?php
                $total = 0.0;
                for ($i = 1; $i <= 12; $i++) {
                    $transactions = $transHeper->getExpensesByCategory($category['categoryID'], $year . '-' . $i . '-01', $year . '-' . $i . '-t');
                    if ($i >= date('n') && empty($transactions)) {
                        $estimate = $transHeper->estimateExpensesByCategory($category['categoryID'], $year, $i);
                        $total += $estimate;

                        echo "<td><span style='color: red'>$" . $estimate . "</span></td>";
                    } else {
                        $value = sumTransactions($transactions);
                        $total += preg_replace('/[^0-9,.]+/', '', $value);

                        echo "<td onclick='window.open(\"viewDetail.php?cat=" . $category['categoryID'] . "&month=" . $i . "&year=" . $year . "\", \"View Transactions\", \"width=500,height=700\")'>" . $value . "</td>";
                    }
                }
                echo "<th><span style='color: red'>$" . number_format($total,2) . "</span></th>";
                $expensesTotal += $total;
                ?>
            </tr>
            <?php
        }
        ?>
        </tbody>
        <thead class="thead-dark">
        <tr>
            <?php
            for ($i = 0; $i <= 12; $i++) {
                echo "<th>&nbsp;</th>";
            }
            echo "<th><span style='color: red'>$" . number_format($expensesTotal,2) . "</span></th>";
            ?>
        </tr>
        </thead>
        <tbody>
        <?php
        $clients = $clientHelper->getClientsWithIncome($year - 1);
        $incomeTotal = 0.0;
        foreach ($clients as $client) {
            ?>
            <tr>
                <th><?php echo $client['title']; ?></th>
                <?php
                $total = 0.0;
                for ($i = 1; $i <= 12; $i++) {
                    $transactions = $transHeper->getIncomeByClient($client['clientID'], $year . '-' . $i . '-01', $year . '-' . $i . '-t');
                    if ($i >= date('n') && empty($transactions)) {
                        $estimate = $transHeper->estimateIncomeByClient($client['clientID'], $year, $i);
                        $total += $estimate;

                        echo "<td><span style='color: red'>$" . $estimate . "</span></td>";
                    } else {
                        $value = sumTransactions($transactions);
                        $total += preg_replace('/[^0-9,.]+/', '', $value);

                        echo "<td onclick='window.open(\"viewDetail.php?client=" . $client['clientID'] . "&month=" . $i . "&year=" . $year . "\", \"View Transactions\", \"width=500,height=700\")'>" . $value . "</td>";
                    }
                }
                echo "<th><span style='color: red'>$" . number_format($total,2) . "</span></th>";
                $incomeTotal += $total;
                ?>
            </tr>
            <?php
        }
        ?>
        </tbody>
        <thead class="thead-dark">
        <tr>
            <?php
            for ($i = 0; $i <= 12; $i++) {
                echo "<th>&nbsp;</th>";
            }
            echo "<th><span style='color: red'>$" . number_format($incomeTotal,2) . "</span></th>";
            ?>
        </tr>
        </thead>
        <thead class="thead">
        <tr>
            <th colspan="13">Profit</th>
            <?php
            echo "<th>$" . number_format($incomeTotal - $expensesTotal, 2) . "</th>";
            ?>
        </tr>
        </thead>
    </table>

    <h2>Year Over Year Comparison</h2>

    <table class="table">
        <thead>
        <tr>
            <th>&nbsp;</th>
            <?php
            foreach ($data as $year => $datum) {
                echo "<th><a href='yearOverview.php?year=" . explode(" ", $year)[0] . "'>" . $year . "</a></th>";
            }
            ?>
            <th><?php echo date('Y'); ?> Forecast</th>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th>Expenses</th>
            <?php
            foreach ($data as $year => $datum) {
                echo "<td>" . sumTransactions($transHeper->getExpenses($year . '-01-01', $year . '-12-31')) . "</td>";
            }
            ?>
            <td>$<?php echo number_format($expensesTotal, 2); ?></td>
        </tr>

        <tr>
            <th>Income</th>
            <?php
            foreach ($data as $year => $datum) {
                echo "<td>" . sumTransactions($transHeper->getIncome($year . '-01-01', $year . '-12-31')) . "</td>";
            }
            ?>
            <td>$<?php echo number_format($incomeTotal, 2); ?></td>
        </tr>
        <tr>
            <th>Total Profit</th>
            <?php
            foreach ($data as $year => $datum) {
                echo "<td>" . totalTransactions($transHeper->getIncome($year . '-01-01', $year . '-12-31'), $transHeper->getExpenses($year . '-01-01', $year . '-12-31')) . "</td>";
            }
            ?>
            <td>$<?php echo number_format($incomeTotal - $expensesTotal, 2); ?></td>
        </tr>
        <tr>
            <th>Total Margin</th>
            <?php
            foreach ($data as $year => $datum) {
                echo "<td>" . calculateMargin($transHeper->getIncome($year . '-01-01', $year . '-12-31'), $transHeper->getExpenses($year . '-01-01', $year . '-12-31')) . "</td>";
            }
            ?>
            <td><?php echo number_format((1 - ($expensesTotal / $incomeTotal)) * 100, 2) ?>%</td>
        </tr>
        </thead>
    </table>

<?php
$content = ob_get_clean();
$page->setContent($content);

$site->render();
