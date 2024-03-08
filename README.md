# TypePick PHP Query Builder
 Basic and simple class for query management.
 
<pre>
$queryBuilder = new tpQuery(initSetup::getDatabaseConnection());
$queryBuilder
    ->in("typepick_users")
    ->select(["user_id", "username", "email"])
    ->where("user_id", "=", $uid)
    ->or("username", "=", $username)
    ->execute("obj");
</pre>

In this example, the $queryBuilder instance is used to build a SELECT query for the "typepick_users" table, selecting the "user_id" column. It further adds a WHERE clause to filter by "user_id" with the value of $uid. Finally, the 'execute' method executes the query and returns the result as an object ('obj'). Adjust the table name, selected columns, and conditions as needed for your specific use case.

<h2>Examples:</h2>
 <h4>'COUNT' method:</h4>
<pre>
$queryBuilder
    ->in("typepick_users")
    ->count(["user_id"])
    ->where("user_id", ">", 0)
    ->execute();
</pre>
This example demonstrates a count query on the "typepick_users" table for specific conditions.

<h4>'DELETE' method:</h4>
<pre>
$queryBuilder
    ->in("typepick_users")
    ->delete()
    ->where("username", "=", $userId)
    ->and("email", "=", $userEmail)
    ->execute();
</pre>
Here, a delete query is constructed for the "typepick_users" table with conditions on "username" and "email," including encryption for the "email" column.

<h4>'UPDATE' method:</h4>
<pre>
$queryBuilder
    ->in("typepick_users")
    ->update(["username" => "new_username"])
    ->where("user_id", "=", $userId)
    ->encrypt([
        "username" => ["method" => "BASE64"],
    ])
    ->execute();
</pre>
This code updates the "typepick_users" table, setting the "username" to 'new_username' for a specific user ID, including encryption for the "username" column.

<h4>'SELECT' method:</h4>
<pre>
$queryBuilder
    ->in("typepick_users")
    ->select(["user_id", "username", "email"])
    ->where("user_id", "=", $userId)
    ->or("username", "=", $userName)
    ->decrypt([
        "email" => ["method" => "AES", "key" => $AES_KEY, "use" => "BASE64"],
        "username" => ["method" => "BASE64"],
    ])
    ->encrypt([
        "username" => ["method" => "BASE64"],
    ])
    ->execute();
</pre>
This example constructs a select query for the "typepick_users" table with specific conditions, including decryption for "email" and "username" columns and encryption for the "username" column. Method for return array of objects selectAll.

<h4>'INSERT' method:</h4>
<pre>
$queryBuilder
    ->in("typepick_users")
    ->insert([
        "username" => $userName,
        "email" => $userEmail,
        "createdTime" => time(),
    ])
    ->encrypt([
        "username" => ["method" => "BASE64"],
        "email" => ["method" => "AES", "key" => $AES_KEY, "use" => "BASE64"],
        "createdTime" => ["method" => "HEX"],
    ])
    ->execute();
</pre>
This snippet demonstrates an insert query for the "typepick_users" table with specified values, including encryption for the "username" and "createdTime" columns.

<h2>'query' method: designed to handle complex scenarios and secure parameter binding</h2>
<pre>
$buildQuery = $queryBuilder->query("find", 
"SELECT id, uid, article_id, created_time FROM typepick_users WHERE uid = :uid",
[":uid" => $userId])
</pre>
This method is particularly useful when dealing with specific scenarios where the standard CRUD operations provided by the queryBuilder class may not cover all use cases. It allows developers to create and execute custom SQL queries while benefiting from the security features provided by the underlying query building infrastructure.








