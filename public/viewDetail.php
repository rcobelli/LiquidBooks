<?php

include '../init.php';

$catHelper = new CategoryHelper($config);
$clientHelper = new ClientHelper($config);
$transHelper = new TransactionHelper($config);

if (!empty($_POST)) {
    if ($transHelper->createTransaction($_POST)) {
        echo "<script>window.close();</script>";
    } else {
        $errors[] = $transHelper->getErrorMessage();
    }
}

// Site/page boilerplate
$site = new site('Liquid Books', $errors);
init_site($site);

$page = new page();
$site->setPage($page);


// Start rendering the content
ob_start();



?>
    <h1>View Transactions</h1>
<?php
if (isset($_GET['cat'])) {
    echo "<h3>" . $_GET['month'] . "/" . $_GET['year'] . " - " . $catHelper->getCategoryById($_GET['cat'])['title'] . "</h3>";
} else {
    echo "<h3>" . $_GET['month'] . "/" . $_GET['year'] . " - " . $clientHelper->getClientByID($_GET['client'])['title'] . "</h3>";
}

?>
    <table class="table">
        <thead>
        <tr>
            <th style="width: 10%">Date</th>
            <th>Title</th>
            <th style="width: 10%">Amount</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (isset($_GET['cat'])) {
            $records = $transHelper->getExpensesByCategory($_GET['cat'], $_GET['year'] . '-' . $_GET['month'] . '-01', $_GET['year'] . '-' . $_GET['month'] . '-t');
        } else {
            $records = $transHelper->getIncomeByClient($_GET['client'], $_GET['year'] . '-' . $_GET['month'] . '-01', $_GET['year'] . '-' . $_GET['month'] . '-t');
        }

        foreach ($records as $record) {
            ?>
            <tr>
                <td><?php echo date('m/d/Y', strtotime($record['date']));?></td>
                <td><?php echo $record['title'];?></td>
                <td>$<?php echo number_format($record['amount'], 2);?></td>
            </tr>
            <?php
        }
        ?>
        </tbody>
    </table>

<?php
$content = ob_get_clean();
$page->setContent($content);

$site->render();
