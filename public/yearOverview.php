<?php

include '../init.php';

if (!$samlHelper->isLoggedIn()) {
    header("Location: index.php");
    die();
}

$config['type'] = Rybel\backbone\LogStream::console;

$catHelper = new CategoryHelper($config);
$clientHelper = new ClientHelper($config);
$transHeper = new TransactionHelper($config);

// Boilerplate
$page = new Rybel\backbone\page();
$page->addHeader("../includes/header.php");
$page->addFooter("../includes/footer.php");
$page->addHeader("../includes/navbar.php");

$year = $_GET['year'];

$currentYear = $year == date('Y');

// Start rendering the content
ob_start();

?>
    <button class="btn btn-success float-right" onclick='window.open("newTransaction.php", "New Transaction", "width=500,height=700")'>Add Transaction</button>
<?php
if ($currentYear) {
    echo '<button class="btn btn-dark float-right mr-3" onclick=\'window.location.href="yearForecast.php"\'>View Forecast</button>';
}
?>
    <h1>Fiscal Year <?php echo $year; ?></h1>

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
        foreach ($categories as $category) {
            ?>
            <tr>
                <th><?php echo $category['title']; ?></th>
                <?php
                for ($i = 1; $i <= 12; $i++) {
                    $transactions = $transHeper->getExpensesByCategory($category['categoryID'], $year . '-' . $i . '-01', $year . '-' . $i . '-t');
                    if ($currentYear) {
                        if ($i > date('n') && empty($transactions)) {
                            echo "<td>&nbsp;</td>";
                        } else {
                            echo "<td onclick='window.open(\"viewDetail.php?cat=" . $category['categoryID'] . "&month=" . $i . "&year=" . $year . "\", \"View Transactions\", \"width=500,height=700\")'>" . sumTransactions($transactions) . "</td>";
                        }
                    } else {
                        echo "<td onclick='window.open(\"viewDetail.php?cat=" . $category['categoryID'] . "&month=" . $i . "&year=" . $year . "\", \"View Transactions\", \"width=500,height=700\")'>" . sumTransactions($transactions) . "</td>";
                    }
                }
                echo "<th onclick='window.open(\"viewDetail.php?cat=" . $category['categoryID'] . "&year=" . $year . "\", \"View Transactions\", \"width=500,height=700\")'>" . sumTransactions($transHeper->getExpensesByCategory($category['categoryID'], $year . '-01-01', $year . '-12-31')) . "</th>";
                ?>
            </tr>
            <?php
        }
        ?>
        </tbody>
        <thead class="thead-dark">
        <tr>
            <th>&nbsp;</th>
            <?php
            for ($i = 1; $i <= 12; $i++) {
                echo "<th onclick='window.open(\"viewDetail.php?type=expenses&month=" . $i . "&year=" . $year . "\", \"View Transactions\", \"width=500,height=700\")'>" . sumTransactions($transHeper->getExpenses($year . '-' . $i . '-01', $year . '-' . $i . '-t')) . "</th>";
            }
            echo "<th onclick='window.open(\"viewDetail.php?type=expenses&year=" . $year . "\", \"View Transactions\", \"width=500,height=700\")'>" . sumTransactions($transHeper->getExpenses($year . '-01-01', $year . '-12-31')) . "</th>";
            ?>
        </tr>
        </thead>
        <tbody>
        <?php
        $clients = $clientHelper->getClientsWithIncome($year);
        foreach ($clients as $client) {
            ?>
            <tr>
                <th><?php echo $client['title']; ?></th>
                <?php
                for ($i = 1; $i <= 12; $i++) {
                    $transactions = $transHeper->getIncomeByClient($client['clientID'], $year . '-' . $i . '-01', $year . '-' . $i . '-t');
                    if ($currentYear) {
                        if ($i > date('n') && empty($transactions)) {
                            echo "<td>&nbsp;</td>";
                        } else {
                            echo "<td onclick='window.open(\"viewDetail.php?client=" . $client['clientID'] . "&month=" . $i . "&year=" . $year . "\", \"View Transactions\", \"width=500,height=700\")'>" . sumTransactions($transactions) . "</td>";
                        }
                    } else {
                        echo "<td onclick='window.open(\"viewDetail.php?client=" . $client['clientID'] . "&month=" . $i . "&year=" . $year . "\", \"View Transactions\", \"width=500,height=700\")'>" . sumTransactions($transactions) . "</td>";
                    }
                }
                echo "<th onclick='window.open(\"viewDetail.php?client=" . $client['clientID'] . "&year=" . $year . "\", \"View Transactions\", \"width=500,height=700\")'>" . sumTransactions($transHeper->getIncomeByClient($client['clientID'], $year . '-01-01', $year . '-12-31')) . "</th>";
                ?>
            </tr>
            <?php
        }
        ?>
        </tbody>
        <thead class="thead-dark">
        <tr>
            <th>&nbsp;</th>
            <?php
            for ($i = 1; $i <= 12; $i++) {
                echo "<th onclick='window.open(\"viewDetail.php?type=income&month=" . $i . "&year=" . $year . "\", \"View Transactions\", \"width=500,height=700\")'>" . sumTransactions($transHeper->getIncome($year . '-' . $i . '-01', $year . '-' . $i . '-t')) . "</th>";
            }
            echo "<th onclick='window.open(\"viewDetail.php?type=income&year=" . $year . "\", \"View Transactions\", \"width=500,height=700\")'>" . sumTransactions($transHeper->getIncome($year . '-01-01', $year . '-12-31')) . "</th>";
            ?>
        </tr>
        </thead>
        <thead class="thead">
        <tr>
            <th>Profit</th>
            <?php
            for ($i = 1; $i <= 12; $i++) {
                echo "<th onclick='window.open(\"viewDetail.php?type=both&month=" . $i . "&year=" . $year . "\", \"View Transactions\", \"width=500,height=700\")'>" . totalTransactions($transHeper->getIncome($year . '-' . $i . '-01', $year . '-' . $i . '-t'), $transHeper->getExpenses($year . '-' . $i . '-01', $year . '-' . $i . '-t')) . "</th>";
            }
            echo "<th onclick='window.open(\"viewDetail.php?type=both&year=" . $year . "\", \"View Transactions\", \"width=500,height=700\")'>" . totalTransactions($transHeper->getIncome($year . '-01-01', $year . '-12-31'), $transHeper->getExpenses($year . '-01-01', $year . '-12-31')) . "</th>";
            ?>
        </tr>
        </thead>
    </table>

<?php
$content = ob_get_clean();
$page->render($content);
