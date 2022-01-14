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
} else if ($_GET['action'] == 'delete' && !empty($_GET['item'])) {
    if ($transHelper->deleteTransaction($_GET['item'])) {
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

if (isset($_GET['month'])) {
    echo "<h3>" . $_GET['month'] . "/" . $_GET['year'];
} else {
    echo "<h3>" . $_GET['year'];
}
if (isset($_GET['cat'])) {
    echo " - " . $catHelper->getCategoryById($_GET['cat'])['title'];
} else if (isset($_GET['client'])) {
    echo " - " . $clientHelper->getClientByID($_GET['client'])['title'];
}
echo "</h3>"

?>
    <table class="table">
        <thead>
        <tr>
            <th style="width: 10%">Date</th>
            <th>Title</th>
            <th style="width: 10%">Amount</th>
            <th style="width: 10%">&nbsp;</th>
        </tr>
        </thead>
        <tbody>
        <?php
        if (isset($_GET['month'])) {
            $startDate = $_GET['year'] . '-' . $_GET['month'] . '-01';
            $endDate = $_GET['year'] . '-' . $_GET['month'] . '-t';
        } else {
            $startDate = $_GET['year'] . '-01-01';
            $endDate = $_GET['year'] . '-12-t';
        }

        if (isset($_GET['cat'])) {
            $records = $transHelper->getExpensesByCategory($_GET['cat'], $startDate, $endDate);
        } else if (isset($_GET['client'])) {
            $records = $transHelper->getIncomeByClient($_GET['client'], $startDate, $endDate);
        } else if ($_GET['type'] == "expenses") {
            $records = $transHelper->getExpenses($startDate, $endDate);
        } else if ($_GET['type'] == "income") {
            $records = $transHelper->getIncome($startDate, $endDate);
        } else if ($_GET['type'] == "both") {
            $records = array_merge($transHelper->getIncome($startDate, $endDate), $transHelper->getExpenses($startDate, $endDate));
            $dates = array_column($records, 'date');
            array_multisort($dates, SORT_ASC, $records);

        }

        foreach ($records as $record) {
            if (!is_null($record['irrelevant']) && $record['irrelevant'] == 1) {
                echo "<tr style='color: red'>";
            } else {
                echo '<tr>';
            }
            ?>
                <td><?php echo date('m/d/Y', strtotime($record['date']));?></td>
                <td><?php echo $record['title'];?></td>
                <td>
                    <?php
                    if ($_GET['type'] == 'both' && !is_null($record['categoryID'])) {
                        echo "($" . number_format($record['amount'], 2) . ")";
                    } else {
                        echo "$" . number_format($record['amount'], 2);
                    }
                    ?>
                </td>
                <td><a href="?action=delete&item=<?php echo $record['transactionID']; ?>"><img src="resources/trash.png" height="20px" alt="Delete Transaction"/></a></td>
            <?php
            echo "</tr>";
        }
        ?>
        </tbody>
    </table>

<?php
$content = ob_get_clean();
$page->setContent($content);

$site->render();
