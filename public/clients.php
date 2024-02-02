<?php

include '../init.php';

if (!$samlHelper->isLoggedIn()) {
    header("Location: index.php");
    die();
}

$config['type'] = Rybel\backbone\LogStream::console;

$helper = new ClientHelper($config);

// Boilerplate
$page = new Rybel\backbone\page();
$page->addHeader("../includes/header.php");
$page->addFooter("../includes/footer.php");
$page->addHeader("../includes/navbar.php");

// Application logic
if ($_REQUEST['action'] == 'archive') {
    if ($helper->archiveClient($_REQUEST['item'])) {
        $page->setSuccess(true);
        unset($_REQUEST);
    } else {
        $page->addError($helper->getErrorMessage());
    }
} elseif ($_REQUEST['submit'] == 'create') {
    if ($helper->createClient($_POST['title'])) {
        $page->setSuccess(true);
        unset($_REQUEST);
    } else {
        $page->addError($helper->getErrorMessage());
    }
} elseif ($_REQUEST['submit'] == 'update') {
    if ($helper->updateClient($_POST['item'], $_POST['title'])) {
        $page->setSuccess(true);
        unset($_REQUEST);
    } else {
        $page->addError($helper->getErrorMessage());
    }
}

// Start rendering the content
ob_start();

if ($_REQUEST['action'] != 'create') {
    ?>
    <button class="btn btn-success float-right" onclick="window.location = '?action=create'">New Client</button>
    <?php
}
?>
<h1>Manage Clients</h1>

<?php

if ($_REQUEST['action'] == 'create') {
    $helper->render_newClientForm();
} elseif ($_REQUEST['action'] == 'update') {
    $helper->render_editClientForm($_REQUEST['item']);
} else {
    $helper->render_clients();
}


// End rendering the content
$content = ob_get_clean();
$page->render($content);
