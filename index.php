<?php
/**
 * TypePick PHP Query Builder
 * https://github.com/Nextgen-developers/TypePick-PHP-Query-Builder
 *
 * @copyright 2024 Nextgen-developers
 * @license   MIT, https://opensource.org/licenses/MIT
 */
include_once "Config/config.php";
include_once "Build/builderClass.php";

initSetup::initialize();
$AES_KEY = substr(hash('sha256', initSetup::TYPEPICK_AES_KEY, true), 0, 32);

// Initialize a TypePickQueryBuilder
$queryBuilder = new tpQuery(initSetup::getDatabaseConnection());

// Some inputs
$userId = 455;
$userEmail = "test@email.com";
$userName = "testUsername";

echo "<h1>TypePick Query Builder:</h1>";

// Function to execute a query and measure performance time
function executeQuery($queryBuilder, $queryType)
{
    $startTime = microtime(true);
    $queryData = $queryBuilder->getQuery();
    $bindingsData = $queryBuilder->getBindings();
    $actionData = ucfirst($queryBuilder->getAction());

    // Execute the query
    $result = $queryBuilder->clear();

    // Measure and display the time taken for the query
    echo "<br>{$actionData} Query: " .
    $queryData.
    PHP_EOL;
     echo "<br>{$actionData} Bindings: " .
    json_encode($bindingsData, JSON_PRETTY_PRINT) .
    PHP_EOL;
    $executionTime = microtime(true) - $startTime;
    echo "<br>{$actionData} Template Execution Time: " .
        round($executionTime * 1000, 2) . " ms<br>";
}

// --- SelectAll Query Example ---
$queryBuilder
    ->in("typepick_users")
    ->selectAll(["user_id", "username", "email", "account_type"])
    ->where("user_id", "=", $userId)
    ->or("username", "=", $userName)
    ->orderby(["user_id" => "DESC"])
    ->limit(5)
    ->offset(1)
    ->decrypt([
        "email" => ["method" => "AES", "key" => $AES_KEY],
        "username" => ["method" => "AES", "key" => $AES_KEY],
    ])
    ->encrypt([
        "username" => ["method" => "AES", "key" => $AES_KEY]
    ]);

executeQuery($queryBuilder, 'Select');

// --- Select Query Example ---
$queryBuilder
    ->in("typepick_users")
    ->select(["user_id", "username", "email"])
    ->where("user_id", "=", $userId)
    ->and("username", "=", $userName)
    ->orderby(["user_id" => "DESC"])
    ->decrypt([
        "email" => ["method" => "AES", "key" => $AES_KEY],
        "username" => ["method" => "AES", "key" => $AES_KEY],
    ])
    ->encrypt([
        "username" => ["method" => "AES", "key" => $AES_KEY]
    ]);

executeQuery($queryBuilder, 'Select');

// --- Update Query Example ---
$queryBuilder
    ->in("typepick_users")
    ->update(["username" => 'new_username'])
    ->where("user_id", "=", $userId)
    ->encrypt([
        "username" => ["method" => "BASE64"]
    ]);

executeQuery($queryBuilder, 'Update');

// --- Insert Query Example ---
$queryBuilder
    ->in("typepick_users")
    ->insert([
        "username" => $userName,
        "email" => $userEmail,
        "createdTime" => time()
    ])
    ->encrypt([
        "username" => ["method" => "AES", "key" => $AES_KEY, "use" => "BASE64"],
        "createdTime" => ["method" => "HEX"],
    ]);

executeQuery($queryBuilder, 'Insert');

// --- Count Query Example ---
$queryBuilder->in("typepick_users")->count(["user_id"])
    ->where("user_id", ">", 0);

executeQuery($queryBuilder, 'Count');

// --- Delete Query Example ---
$queryBuilder->in("typepick_users")->delete()
    ->where("username", "=", $userId)->and("email", "=", $userEmail);

executeQuery($queryBuilder, 'Delete');

?>