<?php

require_once __DIR__ . '/../stdlib.php';

$testCount = 0;
$failures = array();

$assertSame = function ($expected, $actual, $testName) use (&$testCount, &$failures) {
    $testCount++;

    if ($expected !== $actual) {
        $failures[] = $testName . ': expected ' . var_export($expected, true)
            . ', got ' . var_export($actual, true);
        return;
    }

    echo "PASS: " . $testName . PHP_EOL;
};

$assertSame(
    array("Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"),
    $months,
    'months contains all calendar months in order'
);

$assertSame('$0.00', sumTransactions(array()), 'empty transaction sum is zero');
$assertSame(
    '$125.50',
    sumTransactions(array(array('amount' => 100), array('amount' => 25.5))),
    'transaction sum formats positive amounts'
);
$assertSame(
    '($12.50)',
    sumTransactions(array(array('amount' => -5), array('amount' => -7.5))),
    'transaction sum formats negative amounts as accounting currency'
);

$assertSame(
    '$60.00',
    totalTransactions(
        array(array('amount' => 100)),
        array(array('amount' => 40))
    ),
    'total subtracts expenses from income'
);
$assertSame(
    '($25.00)',
    totalTransactions(
        array(array('amount' => 25)),
        array(array('amount' => 50))
    ),
    'total formats a net loss as accounting currency'
);
$assertSame(
    '$0.00',
    totalTransactions(array(array('amount' => 50)), array(array('amount' => 50))),
    'total formats break-even as zero'
);

$assertSame(
    '75.00%',
    calculateMargin(
        array(array('amount' => 100)),
        array(array('amount' => 25))
    ),
    'margin is calculated from income and expenses'
);
$assertSame(
    '-25.00%',
    calculateMargin(
        array(array('amount' => 100)),
        array(array('amount' => 125))
    ),
    'margin can be negative when expenses exceed income'
);

if (!empty($failures)) {
    foreach ($failures as $failure) {
        fwrite(STDERR, "FAIL: " . $failure . PHP_EOL);
    }

    fwrite(STDERR, count($failures) . " of " . $testCount . " tests failed." . PHP_EOL);
    exit(1);
}

echo "All " . $testCount . " tests passed." . PHP_EOL;
