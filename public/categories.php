<?php

include '../init.php';

if (!$samlHelper->isLoggedIn()) {
    header("Location: index.php");
    die();
}

$config['type'] = Rybel\backbone\LogStream::console;

$helper = new CategoryHelper($config);

// Boilerplate
$page = new Rybel\backbone\page();
$page->addHeader("../includes/header.php");
$page->addFooter("../includes/footer.php");
$page->addHeader("../includes/navbar.php");

// Application logic
if ($_REQUEST['submit'] == 'create') {
    if ($helper->createCategory($_POST['title'])) {
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
    <button class="btn btn-success float-right" onclick="window.location = '?action=create'">New Category</button>
    <?php
}
?>
<h1>Manage Categories</h1>

<?php

if ($_REQUEST['action'] == 'create') {
    $helper->render_newCategoryForm();
} else {
    $helper->render_categories();
}


// End rendering the content
$content = ob_get_clean();
$page->render($content);
