<?php
include_once "Config/config.php";
include_once "Class/tpQueryBuilder.php";
Config::initialize();

// Initialize a TypePickQueryBuilder
$queryBuilder = new tpQuery(Config::getDatabaseConnection());

// Some inputs
$AES_KEY = substr(hash('sha256', 'ffgdfgh5fgh4fg86h4f8g4hjf89g', true), 0, 32);
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

// --- Count Query Example ---
$queryBuilder
    ->in("typepick_users")
    ->count(["user_id"])
    ->where("user_id", "=", $userId)->or("username", "=", $userName)
    ->encrypt([
        "username" => ["method" => "AES", "key" => $AES_KEY]
    ]);

executeQuery($queryBuilder, 'Count');

// --- Delete Query Example ---
$queryBuilder
    ->in("typepick_users")
    ->delete()
    ->where("username", "=", $userId)->and("email", "=", $userEmail)
    ->encrypt([
        "email" => ["method" => "AES", "key" => $AES_KEY]
    ]);

executeQuery($queryBuilder, 'Delete');

// --- Update Query Example ---
$queryBuilder
    ->in("typepick_users")
    ->update(["username" => 'new_username'])
    ->where("user_id", "=", $userId)
    ->encrypt([
        "username" => ["method" => "BASE64"]
    ]);

executeQuery($queryBuilder, 'Update');

// --- Select Query Example ---
$queryBuilder
    ->in("typepick_users")
    ->select(["user_id", "username", "email"])
    ->where("user_id", "=", $userId)->or("username", "=", $userName)
    ->decrypt([
        "email" => ["method" => "AES", "key" => $AES_KEY],
        "username" => ["method" => "AES", "key" => $AES_KEY],
    ])
    ->encrypt([
        "username" => ["method" => "AES", "key" => $AES_KEY]
    ]);

executeQuery($queryBuilder, 'Select');

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

?>