<?php

require_once __DIR__ . '/../vendor/autoload.php';
require_once __DIR__ . '/../classes/CategoryHelper.php';
require_once __DIR__ . '/../classes/ClientHelper.php';
require_once __DIR__ . '/../classes/TransactionHelper.php';

use Rybel\backbone\LogStream;

$host = getenv('LIQUIDBOOKS_DB_HOST') ?: 'liquidbooks-db';
$user = getenv('LIQUIDBOOKS_DB_USER') ?: 'root';
$password = getenv('LIQUIDBOOKS_DB_PASSWORD') ?: 'root';
$sourceDatabase = getenv('LIQUIDBOOKS_DB_NAME') ?: 'liquid_books';
$testDatabase = 'liquid_books_test_' . bin2hex(random_bytes(6));
$testCount = 0;
$failures = array();
$databaseCreated = false;

$assertSame = function ($expected, $actual, $testName) use (&$testCount, &$failures) {
    $testCount++;

    if ($expected !== $actual) {
        $failures[] = $testName . ': expected ' . var_export($expected, true)
            . ', got ' . var_export($actual, true);
        return;
    }

    echo "PASS: " . $testName . PHP_EOL;
};

$assertTrue = function ($actual, $testName) use (&$assertSame) {
    $assertSame(true, $actual, $testName);
};

try {
    $server = new PDO(
        'mysql:host=' . $host . ';charset=utf8mb4',
        $user,
        $password,
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );
    $server->exec("SET SESSION sql_mode = CONCAT(@@SESSION.sql_mode, ',NO_AUTO_VALUE_ON_ZERO')");

    $server->exec('CREATE DATABASE `' . $testDatabase . '` CHARACTER SET utf8mb4');
    $databaseCreated = true;

    $database = new PDO(
        'mysql:host=' . $host . ';dbname=' . $testDatabase . ';charset=utf8mb4',
        $user,
        $password,
        array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
    );

    foreach (array('Categories', 'Clients', 'Transactions') as $table) {
        $quotedTable = '`' . $table . '`';
        $server->exec(
            'CREATE TABLE `' . $testDatabase . '`.' . $quotedTable
            . ' LIKE `' . $sourceDatabase . '`.' . $quotedTable
        );
        $server->exec(
            'INSERT INTO `' . $testDatabase . '`.' . $quotedTable
            . ' SELECT * FROM `' . $sourceDatabase . '`.' . $quotedTable
        );
        $database->exec('DELETE FROM ' . $quotedTable);
    }

    $database->exec(
        "INSERT INTO Categories (categoryID, title) VALUES (201, 'Services'), (202, 'Travel')"
    );
    $database->exec(
        "INSERT INTO Clients (clientID, title, active) "
        . "VALUES (101, 'Active client', 1), (102, 'Archived client', 0)"
    );
    $database->exec(
        "INSERT INTO Transactions "
        . "(transactionID, title, amount, date, clientID, categoryID, linkedTransactionID, irrelevant, spreadID) "
        . "VALUES "
        . "(1, 'Consulting', 100, '2025-01-05', 101, NULL, NULL, 0, NULL), "
        . "(2, 'Annual license <i>(Spread)</i>', 60, '2025-01-10', 101, NULL, NULL, 0, 9001), "
        . "(3, 'Annual license <i>(Spread)</i>', 60, '2025-02-10', 101, NULL, NULL, 0, 9001), "
        . "(4, 'Other client income', 80, '2025-03-05', 102, NULL, NULL, 0, NULL), "
        . "(5, 'Taxi', 30, '2025-01-06', NULL, 201, NULL, 0, NULL), "
        . "(6, 'Annual hosting <i>(Spread)</i>', 20, '2025-01-08', NULL, 201, NULL, 0, 9002), "
        . "(7, 'Annual hosting <i>(Spread)</i>', 20, '2025-02-08', NULL, 201, NULL, 0, 9002), "
        . "(8, 'Personal expense', 5, '2025-03-05', NULL, 202, NULL, 1, NULL)"
    );

    $config = array(
        'dbo' => $database,
        'appName' => 'Liquid Books test',
        'logLocal' => true,
        'type' => LogStream::console
    );
    $categories = new CategoryHelper($config);
    $clients = new ClientHelper($config);
    $transactions = new TransactionHelper($config);
    $startDate = '2025-01-01';
    $endDate = '2025-12-31';

    $assertSame(
        'Services',
        $categories->getCategoryById(201)['title'],
        'category lookup selects existing category columns'
    );
    $assertTrue($categories->createCategory('Meals'), 'category insert uses valid table columns');
    $assertSame(3, count($categories->getCategories()), 'category listing returns inserted row');

    $assertSame(
        'Active client',
        $clients->getClientByID(101)['title'],
        'client lookup selects existing client columns'
    );
    $assertSame(
        1,
        count($clients->getActiveClients()),
        'active client query filters archived rows'
    );
    $assertSame(
        array(101, 102),
        array_map('intval', array_column($clients->getClientsWithIncome(2025), 'clientID')),
        'income client query joins transactions and filters by year'
    );
    $assertTrue($clients->updateClient(101, 'Updated client'), 'client update uses valid columns');
    $assertSame(
        'Updated client',
        $clients->getClientByID(101)['title'],
        'updated client can be read'
    );
    $assertTrue($clients->archiveClient(101), 'client archive updates active status');
    $assertSame(
        0,
        (int) $clients->getClientByID(101)['active'],
        'archived client status is persisted'
    );

    $assertSame(
        array('Consulting', 'Annual license <i>(Spread)</i>', 'Annual license <i>(Spread)</i>', 'Other client income'),
        array_column($transactions->getIncome($startDate, $endDate), 'title'),
        'income query selects valid columns and applies date bounds'
    );
    $assertSame(
        array('Taxi', 'Annual hosting <i>(Spread)</i>', 'Annual hosting <i>(Spread)</i>', 'Personal expense'),
        array_column($transactions->getExpenses($startDate, $endDate), 'title'),
        'expense query selects valid columns and applies date bounds'
    );
    $assertSame(
        array('Taxi', 'Annual hosting <i>(Spread)</i>', 'Annual hosting <i>(Spread)</i>'),
        array_column($transactions->getRelevantExpenses($startDate, $endDate), 'title'),
        'relevant expense query excludes irrelevant rows'
    );
    $assertSame(
        array('Taxi', 'Annual hosting <i>(Spread)</i>', 'Annual hosting <i>(Spread)</i>'),
        array_column($transactions->getExpensesByCategory(201, $startDate, $endDate), 'title'),
        'category expense query filters by category'
    );
    $assertSame(
        array('Consulting', 'Annual license <i>(Spread)</i>', 'Annual license <i>(Spread)</i>'),
        array_column($transactions->getIncomeByClient(101, $startDate, $endDate), 'title'),
        'client income query filters by client'
    );

    $unspreadIncome = $transactions->getUnspreadIncome($startDate, $endDate);
    $assertSame(
        3,
        count($unspreadIncome),
        'income spread GROUP BY collapses repeated spread rows'
    );
    $assertSame(
        120.0,
        (float) $unspreadIncome[1]['amount'],
        'income spread GROUP BY sums amounts'
    );
    $assertSame(
        'Annual license ',
        $unspreadIncome[1]['title'],
        'income spread query trims generated spread suffix'
    );
    $assertSame(
        2,
        count($transactions->getUnspreadIncomeByClient(101, $startDate, $endDate)),
        'client income spread GROUP BY filters correctly'
    );

    $unspreadExpenses = $transactions->getUnspreadExpenses($startDate, $endDate);
    $assertSame(
        3,
        count($unspreadExpenses),
        'expense spread GROUP BY collapses repeated spread rows'
    );
    $assertSame(
        40.0,
        (float) $unspreadExpenses[1]['amount'],
        'expense spread GROUP BY sums amounts'
    );
    $assertSame(
        'Annual hosting ',
        $unspreadExpenses[1]['title'],
        'expense spread query trims generated spread suffix'
    );
    $assertSame(
        2,
        count($transactions->getUnspreadExpensesByCategory(201, $startDate, $endDate)),
        'category expense spread GROUP BY filters correctly'
    );

    $byCategory = $transactions->getExpensesByCategory(201, '2025-01-01', '2025-01-31');
    $assertSame(
        array('Taxi', 'Annual hosting <i>(Spread)</i>'),
        array_column($byCategory, 'title'),
        'date range queries include both boundary dates'
    );
} catch (Throwable $error) {
    fwrite(
        STDERR,
        'Database test setup or query failed at ' . $error->getFile() . ':' . $error->getLine()
        . ': ' . $error->getMessage() . PHP_EOL
    );
    $failures[] = 'Database test raised an exception';
} finally {
    if ($databaseCreated) {
        $server->exec('DROP DATABASE `' . $testDatabase . '`');
    }
}

if (!empty($failures)) {
    foreach ($failures as $failure) {
        fwrite(STDERR, "FAIL: " . $failure . PHP_EOL);
    }

    fwrite(STDERR, count($failures) . " of " . $testCount . " tests failed." . PHP_EOL);
    exit(1);
}

echo "All " . $testCount . " database tests passed." . PHP_EOL;
