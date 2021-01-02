<?php

$months = array(
    "Jan",
    "Feb",
    "Mar",
    "Apr",
    "May",
    "Jun",
    "Jul",
    "Aug",
    "Sep",
    "Oct",
    "Nov",
    "Dec"
);

function init_site(site $site)
{
    $site->addHeader("../includes/header.php");
    $site->addFooter("../includes/footer.php");
}

function logMessage($message)
{
    // echo $message;
}

function sumTransactions($data) {
    $total = 0.0;
    foreach ($data as $datum) {
        $total += $datum['amount'];
    }
    if ($total < 0) {
        return "($" . number_format($total,2) . ")";
    }
    return "$" . number_format($total,2);
}

function totalTransactions($income, $expenses) {
    $total = 0.0;
    foreach ($income as $datum) {
        $total += $datum['amount'];
    }
    foreach ($expenses as $datum) {
        $total -= $datum['amount'];
    }

    if ($total < 0) {
        return "($" . number_format($total,2) . ")";
    }
    return "$" . number_format($total,2);
}

function calculateMargin($income, $expenses) {
    $revenue = 0.0;
    foreach ($income as $datum) {
        $revenue += $datum['amount'];
    }

    $cost = 0.0;
    foreach ($expenses as $datum) {
        $cost += $datum['amount'];
    }
    return number_format((1 - ($cost/$revenue)) * 100, 2) . "%";
}
