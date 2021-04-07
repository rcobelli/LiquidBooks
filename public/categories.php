<?php

include '../init.php';

$helper = new CategoryHelper($config);

// Application logic
if ($_REQUEST['submit'] == 'create') {
    if ($helper->createCategory($_POST['title'])) {
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
$page->setContent($content);

$site->render();
?>
