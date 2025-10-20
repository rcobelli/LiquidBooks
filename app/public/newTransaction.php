<?php

include '../init.php';

$config['type'] = Rybel\backbone\LogStream::console;

// Boilerplate
$page = new Rybel\backbone\page();
$page->addHeader("../includes/header.php");
$page->addFooter("../includes/footer.php");

$catHelper = new CategoryHelper($config);
$clientHelper = new ClientHelper($config);
$transHelper = new TransactionHelper($config);

if (!empty($_POST)) {
    if ($transHelper->createTransaction($_POST)) {
        echo "<script>window.close();</script>";
    } else {
        $page->addError($helper->getErrorMessage());
    }
}


// Start rendering the content
ob_start();
?>
<h1>Create New Transaction</h1>
<script>
    window.onunload = refreshParent;

    function refreshParent() {
        window.opener.location.reload();
    }

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
        <input type="number" class="form-control" id="amount" name="amount" placeholder="0.00" required step="0.01" autocomplete="off">
    </div>
    <div class="form-group">
        <label for="spread">Spread Over</label>
        <select class="form-control" id="spread" name="spread">
            <option value="1">Month</option>
            <option value="3">Quarter</option>
            <option value="12">Year</option>
        </select>
    </div>
    <div class="custom-control custom-switch mb-2">
        <input type="checkbox" class="custom-control-input" id="customSwitch1" name="backdate">
        <label class="custom-control-label" for="customSwitch1">Back Date Spread</label>
    </div>
    <div class="custom-control custom-switch mb-2">
        <input type="checkbox" class="custom-control-input" id="customSwitch2" name="irrelevant">
        <label class="custom-control-label" for="customSwitch2">Irrelevant</label>
    </div>
    <div class="mb-2">
        <div class="custom-control custom-radio custom-control-inline">
            <input type="radio" id="expense" name="type" class="custom-control-input" onchange="transactionType()" value="expense" required>
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
<script>
    document.getElementById('date').addEventListener('input', function() {
        const input = this;
        const selectedDate = new Date(input.value);
        const currentYear = new Date().getFullYear();

        if (selectedDate.getFullYear() !== currentYear && input.value) {
            // Add red border if year is not current
            input.style.border = '2px solid red';
        } else {
            // Reset border if year is current or input is empty
            input.style.border = '';
        }
    });
</script>
<?php
$content = ob_get_clean();
$page->render($content);
