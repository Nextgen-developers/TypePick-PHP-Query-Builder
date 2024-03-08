<?php
include_once "Config/config.php";
include_once "Class/tpQueryBuilder.php";
initSetup::initialize();

//$simplified = $queryBuilder->in("typepick_users")->select(["user_id"])->where("user_id", "=", $insertedUserId)->run('obj');

// Start the timer
$startTime = microtime(true);

$queryBuilder = new tpQuery(initSetup::getDatabaseConnection());
$AES_KEY = substr(hash('sha256','ffgdfgh5fgh4fg86h4f8g4hjf89g', true), 0, 32);

echo "<h1>TypePick Query Builder Tests:</h1>";

// Insert operation
$insertedUserId = $queryBuilder
    ->in("typepick_users")
    ->insert([
        "username" => "test1",
        "email" => "email@test.com",
        "created_time" => time(),
    ])
    ->encrypt([
        "username" => ["method" => "AES", "key" => $AES_KEY, "use" => "BASE64"],
        "created_time" => ["method" => "BASE64"],
    ])
    ->execute();
echo "Added new user with ID: " . $insertedUserId;

// Measure and display the time taken for the insert operation
$insertTime = microtime(true) - $startTime;
echo "<br>Insert Time: " . round($insertTime * 1000, 2) . " ms<br>";

// Count operation
$countUsers = $queryBuilder
    ->in("typepick_users")
    ->count(["user_id"])
    ->execute();
echo "<br>All users: " . $countUsers . "<br>";

if ($insertedUserId) {
    // Update operation
    $startTime = microtime(true);
    $updatedUsername = $queryBuilder
        ->in("typepick_users")
        ->update(["username" => "updatedUsername"])
        ->where("user_id", "=", $insertedUserId)
        ->encrypt([
            "username" => [
                "method" => "AES",
                "key" => $AES_KEY,
                "use" => "BASE64",
            ],
        ])
        ->execute();
    echo "Updated user name: " . $updatedUsername . "<br>";

    // Measure and display the time taken for the update operation
    $updateTime = microtime(true) - $startTime;
    echo "Update Time: " . round($updateTime * 1000, 2) . " ms<br><br>";

    // Select and Decrypt operation
    $startTime = microtime(true);
    $userData = $queryBuilder
        ->in("typepick_users")
        ->select(["user_id", "email", "created_time", "username"])
        ->where("user_id", "=", $insertedUserId)
        ->decrypt([
            "username" => [
                "method" => "AES",
                "key" => $AES_KEY,
                "use" => "BASE64",
            ],
            "created_time" => ["method" => "BASE64"],
        ])
        ->execute('obj');
    echo "Created User Data: " . json_encode($userData, JSON_PRETTY_PRINT) . "<br>";

    // Measure and display the time taken for the select and decrypt operation
    $selectDecryptTime = microtime(true) - $startTime;
    echo "Select and Decrypt Time: " . round($selectDecryptTime * 1000, 2) . " ms<br><br>";

    // Delete operation
    $startTime = microtime(true);
    $deleteUser = $queryBuilder
        ->in("typepick_users")
        ->delete()
        ->where("user_id", "=", $insertedUserId)
        ->execute();
    echo "Delete created user: " . $deleteUser . "<br>";

    // Measure and display the time taken for the delete operation
    $deleteTime = microtime(true) - $startTime;
    echo "Delete Time: " . round($deleteTime * 1000, 2) . " ms<br><br>";

    // Count operation
    $startTime = microtime(true);
    $countUsers = $queryBuilder
        ->in("typepick_users")
        ->count(["user_id"])
        ->execute();
    echo "All users: " . $countUsers . "<br>";

    // Measure and display the time taken for the count operation
    $countTime = microtime(true) - $startTime;
    echo "Count Time: " . round($countTime * 1000, 2) . " ms<br>";
} else {
    echo "Failed to insert new user.";
}

?>
