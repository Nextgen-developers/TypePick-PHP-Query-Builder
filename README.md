# TypePick Query Builder
 Basic class for query management.
 
<pre>
$queryBuilder = new QueryBuilder();
$queryBuilder->in("typepick_users")->select(["user_id","username","email"])
 ->where("user_id", "=", $uid)->or("username", "=", $username)
 ->execute('obj');
</pre>

In this example, the $queryBuilder instance is used to build a SELECT query for the "typepick_users" table, selecting the "user_id" column. It further adds a WHERE clause to filter by "user_id" with the value of $insertedUserId. Finally, the 'execute' method executes the query and returns the result as an object ('obj'). Adjust the table name, selected columns, and conditions as needed for your specific use case.

<b>Insert New Record:</b>
<pre>
$insertedUserId = $queryBuilder
    ->in("typepick_users")
    ->insert([
        "username" => "test1",
        "email" => "email@test.com",
        "created_time" => time(),
    ])
</pre>
This part initiates an INSERT operation into the "typepick_users" table, adding a new record with specified values for the "username," "email," and "created_time" columns.

Encryption of Inserted Data:
<pre>
->encrypt([
    "username" => ["method" => "AES", "key" => $AES_KEY, "use" => "BASE64"],
    "created_time" => ["method" => "BASE64"],
])
</pre>
This section specifies that encryption should be applied to certain columns during the insertion. In this example:
The "username" column is encrypted using the AES method with a specified key and encoded in BASE64.
The "created_time" column is encrypted using BASE64.

Execute the Query:
<pre>
->execute();
</pre>

Finally, the execute() method is called to execute the constructed query. The result is stored in the $insertedUserId variable, which presumably holds the identifier of the newly inserted record.
