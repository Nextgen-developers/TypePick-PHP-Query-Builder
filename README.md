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

<h4>'query' method: designed to handle complex scenarios and secure parameter binding</h4>
<pre>
$buildQuery = $queryBuilder->query(
    "find",
    "SELECT id, uid, article_id, created_time FROM typepick_users WHERE uid = :uid",
    [":uid" => $userId]
);
</pre>
This method is particularly useful when dealing with specific scenarios where the standard CRUD operations provided by the queryBuilder class may not cover all use cases. It allows developers to create and execute custom SQL queries while benefiting from the security features provided by the underlying query building infrastructure.

<h2>Methods:</h2>

> ```
> Method in($table)
> Inserts the target database table for the query.
> This table will be used in future query building.
> 
> Method update($updateData)
> Sets the query type to update and provides update data.
> This data will be used in updating records in the specified table.
> 
> Method insert($insertData)
> Sets the query type to insert and provides insert data.
> This data will be used in inserting records into the specified table.
> 
> Method select($selectColumns)
> Sets the query type to find and provides select columns.
> These columns will be retrieved in the query.
> 
> Method selectAll($selectColumns)
> Sets the query type to foreach and provides select columns.
> These columns will be retrieved in the query.
> 
> Method count($selectColumns)
> Sets the query type to count and provides count columns.
> Counts the number of rows based on the specified columns.
> 
> Method delete()
> Sets the query type to delete.
> Specifies a delete operation in the query.
>
> Method where($conditions)
> Adds a WHERE clause to the query based on the specified conditions.
>
> Method and($conditions)
> Adds an AND condition to the WHERE clause in the query.
>
> Method or($conditions)
> Adds an OR condition to the WHERE clause in the query.
> 
> Method execute($type)
> Prepares query for execution and calls query method for exact execution.
> 
> ```
