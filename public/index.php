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
$year = date('Y');

$data = array();
for ($i = $year - 1; $i >= 2017; $i--) {
    $data[] = $i;
}


?>
    <div class="text-center">
        <a class='btn btn-outline-dark btn-lg' href='yearOverview.php?year=<?php echo $year;?>'>Current Year Summary</a>
        <a class='btn btn-outline-dark btn-lg ' href='yearForecast.php?year=<?php echo $year;?>'>Current Year Forecast</a>
    </div>
    <table class="table table-hover mt-5">
        <thead>
        <tr>
            <th>View Prior Year Summaries</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($data as $datum) {
            echo "<tr>";
            echo "<td><a class='btn btn-info' href='yearOverview.php?year=" . $datum . "'>" . $datum . "</a></td>";
            echo "</tr>";
        }
        ?>
        </tbody>
    </table>

<?php
$content = ob_get_clean();
$page->setContent($content);

$site->render();
