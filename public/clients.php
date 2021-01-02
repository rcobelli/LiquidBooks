<?php

include '../init.php';

$helper = new ClientHelper($config);

// Application logic
if ($_REQUEST['action'] == 'archive') {
    if ($helper->archiveClient($_REQUEST['item'])) {
        $success = true;
        unset($_REQUEST);
    } else {
        $errors[] = $helper->getErrorMessage();
    }
} elseif ($_REQUEST['submit'] == 'create') {
    if ($helper->createClient($_POST['title'])) {
        $success = true;
        unset($_REQUEST);
    } else {
        $errors[] = $helper->getErrorMessage();
    }
} elseif ($_REQUEST['submit'] == 'update') {
    if ($helper->updateClient($_POST['item'], $_POST['title'])) {
        $success = true;
        unset($_REQUEST);
    } else {
        $errors[] = $helper->getErrorMessage();
    }
}

// Site/page boilerplate
$site = new site('Liquid Books', $errors, $success);
$site->addHeader("../includes/navbar.php");
init_site($site);

$page = new page();
$site->setPage($page);


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
$page->setContent($content);

$site->render();
?>
