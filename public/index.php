<?php
include '../init.php';

$transHeper = new TransactionHelper($config);

// Site/page boilerplate
$site = new site('Liquid Books', $errors);
$site->addHeader("../includes/navbar.php");
init_site($site);

$page = new page();
$site->setPage($page);

// Start rendering the content
ob_start();

$data = array();
for ($i = 2017; $i <= date('Y'); $i++) {
    $data[$i] = "";
}

?>
    <table class="table">
        <thead>
        <tr>
            <th>&nbsp;</th>
            <th>&nbsp;</th>
            <?php
            foreach ($data as $year => $datum) {
                echo "<th><a href='yearOverview.php?year=" . explode(" ", $year)[0] . "'>" . $year . "</a></th>";
            }
            ?>
        </tr>
        </thead>
        <tbody>
        <tr>
            <th rowspan="4">Expenses</th>
            <th>Q1</th>
            <?php
            foreach ($data as $year => $datum) {
                echo "<td>" . sumTransactions($transHeper->getExpenses($year . '-01-01', $year . '-03-31')) . "</td>";
            }
            ?>
        </tr>
        <tr>
            <th>Q2</th>
            <?php
            foreach ($data as $year => $datum) {
                echo "<td>" . sumTransactions($transHeper->getExpenses($year . '-04-01', $year . '-06-30')) . "</td>";
            }
            ?>
        </tr>
        <tr>
            <th>Q3</th>
            <?php
            foreach ($data as $year => $datum) {
                echo "<td>" . sumTransactions($transHeper->getExpenses($year . '-07-01', $year . '-09-30')) . "</td>";
            }
            ?>
        </tr>
        <tr>
            <th>Q4</th>
            <?php
            foreach ($data as $year => $datum) {
                echo "<td>" . sumTransactions($transHeper->getExpenses($year . '-10-01', $year . '-12-31')) . "</td>";
            }
            ?>
        </tr>
        <tr>
            <th colspan="2">Total</th>
            <?php
            foreach ($data as $year => $datum) {
                echo "<th>" . sumTransactions($transHeper->getExpenses($year . '-01-01', $year . '-12-31')) . "</th>";
            }
            ?>
        </tr>
        <tr>
            <th rowspan="4">Income</th>
            <th>Q1</th>
            <?php
            foreach ($data as $year => $datum) {
                echo "<td>" . sumTransactions($transHeper->getIncome($year . '-01-01', $year . '-03-31')) . "</td>";
            }
            ?>
        </tr>
        <tr>
            <th>Q2</th>
            <?php
            foreach ($data as $year => $datum) {
                echo "<td>" . sumTransactions($transHeper->getIncome($year . '-04-01', $year . '-06-30')) . "</td>";
            }
            ?>
        </tr>
        <tr>
            <th>Q3</th>
            <?php
            foreach ($data as $year => $datum) {
                echo "<td>" . sumTransactions($transHeper->getIncome($year . '-07-01', $year . '-09-30')) . "</td>";
            }
            ?>
        </tr>
        <tr>
            <th>Q4</th>
            <?php
            foreach ($data as $year => $datum) {
                echo "<td>" . sumTransactions($transHeper->getIncome($year . '-10-01', $year . '-12-31')) . "</td>";
            }
            ?>
        </tr>
        <tr>
            <th colspan="2">Total</th>
            <?php
            foreach ($data as $year => $datum) {
                echo "<th>" . sumTransactions($transHeper->getIncome($year . '-01-01', $year . '-12-31')) . "</th>";
            }
            ?>
        </tr>

        </tbody>
        <thead class="thead-dark">
        <tr>
            <th colspan="2">Total Profit</th>
            <?php
            foreach ($data as $year => $datum) {
                echo "<th>" . totalTransactions($transHeper->getIncome($year . '-01-01', $year . '-12-31'), $transHeper->getExpenses($year . '-01-01', $year . '-12-31')) . "</th>";
            }
            ?>
        </tr>
        <tr>
            <th colspan="2">Total Margin</th>
            <?php
            foreach ($data as $year => $datum) {
                echo "<th>" . calculateMargin($transHeper->getIncome($year . '-01-01', $year . '-12-31'), $transHeper->getExpenses($year . '-01-01', $year . '-12-31')) . "</th>";
            }
            ?>
        </tr>
        </thead>
    </table>

<?php
$content = ob_get_clean();
$page->setContent($content);

$site->render();
