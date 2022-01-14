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
    <table class="table table-hover">
        <thead>
        <tr>
            <th>View Summary</th>
            <th>View Forecast</th>
        </tr>
        </thead>
        <tbody>
        <?php
        foreach ($data as $year => $datum) {
            echo "<tr>";
            echo "<td><a class='btn btn-info' href='yearOverview.php?year=" . explode(" ", $year)[0] . "'>" . $year . "</a></td>";
            if ($year == date('Y')) {
                echo "<td><a class='btn btn-info' href='yearForecast.php?year=" . explode(" ", $year)[0] . "'>" . $year . "</a></td>";
            } else {
                echo "<td>&nbsp;</td>";
            }
            echo "</tr>";
        }
        ?>
        </tbody>
    </table>

<?php
$content = ob_get_clean();
$page->setContent($content);

$site->render();
