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
    <script>
        window.onunload = refreshParent;
        function refreshParent() {
            window.opener.location.reload();
        }
    </script>
<h1>Create New Transaction</h1>
<script>
    function transactionType() {
        if (document.getElementById('expense').checked) {
            document.getElementById('ifExpense').style.display = 'block';
            document.getElementById('ifIncome').style.display = 'none';
        } else {
            document.getElementById('ifExpense').style.display = 'none';
            document.getElementById('ifIncome').style.display = 'block';
        }
    }
</script>

<form class="ml-2 mr-2" method="post">
    <div class="form-group">
        <label for="title">Title</label>
        <input type="text" class="form-control" id="title" name="title" placeholder="Hookers" required>
    </div>
    <div class="form-group">
        <label for="date">Date</label>
        <input type="date" class="form-control" id="date" name="date" placeholder="01/02/2020" required>
    </div>
    <div class="form-group">
        <label for="amount">Amount</label>
        <input type="number" class="form-control" id="amount" name="amount" placeholder="0.00" required step="0.01">
    </div>
    <div class="form-group">
        <label for="spread">Spread Over</label>
        <select class="form-control" id="spread" name="spread">
            <option value="1">Month</option>
            <option value="3">Quarter</option>
            <option value="12">Year</option>
        </select>
    </div>
    <div class="mb-2">
        <div class="custom-control custom-radio custom-control-inline">
            <input type="radio" id="expense" name="type" class="custom-control-input" onchange="transactionType()" value="expense">
            <label class="custom-control-label" for="expense">Expense</label>
        </div>
        <div class="custom-control custom-radio custom-control-inline">
            <input type="radio" id="income" name="type" class="custom-control-input" onchange="transactionType()" value="income">
            <label class="custom-control-label" for="income">Income</label>
        </div>
    </div>
    <div id="ifExpense" style="display:none">
        <div class="form-group">
            <label for="category">Category</label>
            <select class="form-control" id="category" name="category">
                <option disabled selected>Select Category...</option>
                <?php
                $categories = $catHelper->getCategories();
                foreach ($categories as $category) {
                    echo "<option value='" . $category['categoryID'] . "'>" . $category['title'] . "</option>";
                }
                ?>
            </select>
        </div>
    </div>
    <div id="ifIncome" style="display:none">
        <div class="form-group">
            <label for="client">Client</label>
            <select class="form-control" id="client" name="client">
                <option disabled selected>Select Client...</option>
                <?php
                $clients = $clientHelper->getActiveClients();
                foreach ($clients as $client) {
                    echo "<option value='" . $client['clientID'] . "'>" . $client['title'] . "</option>";
                }
                ?>
            </select>
        </div>
        <div class="form-group">
            <label for="creditCardFee">Credit Card Fees</label>
            <input type="number" class="form-control" id="creditCardFee" name="creditCardFee" value="0.00" step="0.01">
        </div>
    </div>
    <button type="submit" class="btn btn-primary mt-2">Submit</button>
</form>

<?php
$content = ob_get_clean();
$page->setContent($content);

$site->render();
