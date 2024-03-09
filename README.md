# TypePick PHP Query Builder

Welcome to TypePick PHP Query Builder, a lightweight and user-friendly class for efficient query management in PHP applications.

## Getting Started

To get started, create an instance of `tpQuery` by initializing it with your database connection:

```php
$queryBuilder = new tpQuery(initSetup::getDatabaseConnection());
```

## Building a SELECT Query
In this example, we're selecting specific columns from the "typepick_users" table based on certain conditions:

```php
$queryBuilder
    ->in("typepick_users")
    ->select(["user_id", "username", "email"])
    ->where("user_id", "=", $uid)
    ->or("username", "=", $username)
    ->execute("obj");
```

Here's a breakdown of the example:
- `in("typepick_users")`: Specifies the target table as "typepick_users."
- `select(["user_id", "username", "email"])`: Specifies the columns to be selected in the query.
- `where("user_id", "=", $uid)`: Adds a WHERE clause to filter results where "user_id" equals a specific value.
- `or("username", "=", $username)`: Adds an OR condition to the WHERE clause where "username" equals a specific value.
- `execute("obj")`: Executes the constructed query and returns the result as an object.

## Encryption in SQL Format
TypePick PHP Query Builder supports encryption for enhanced security in your queries. 
Simply specify the columns and the encryption method:

```php
$queryBuilder
    ->encrypt([
        "email" => ["method" => "AES", "key" => $AES_KEY],
        "username" => ["method" => "AES", "key" => $AES_KEY]
    ]);
```

In this example, "email" and "username" columns will be encrypted using the AES method with the provided key.

## Secure Parameter Binding
To guard against SQL injection, TypePick PHP Query Builder utilizes secure parameter binding:

```php
$queryBuilder
    ->where("user_id", "=", $uid)
    ->or("username", "=", $username);
```

The values of $uid and $username are securely bound to the query, ensuring the safety of your application.

## Execution and Result Handling
Executing queries is straightforward with the execute method:

```php
$queryBuilder->execute("obj");
```

You can choose the desired result format, such as "obj" for objects or "assoc" for associative arrays.
```php
    [
        "assoc" => PDO::FETCH_ASSOC,
        "both" => PDO::FETCH_BOTH,
        "bound" => PDO::FETCH_BOUND,
        "class" => PDO::FETCH_CLASS,
        "into" => PDO::FETCH_INTO,
        "lazy" => PDO::FETCH_LAZY,
        "named" => PDO::FETCH_NAMED,
        "num" => PDO::FETCH_NUM,
        "obj" => PDO::FETCH_OBJ,
    ]
```
    
## Examples:

<h4>'selectall' method:</h4>

```php
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
    ])
    ->execute();
```
This example constructs a SELECT query for the "typepick_users" table with specific conditions, including decryption for "email" and "username" columns and encryption for the "username" column. The method returns an array of objects.

- `in("typepick_users")`: Specifies the target table as "typepick_users."
- `selectAll(["user_id", "username", "email", "account_type"])`: Specifies the columns to be selected in the query.
- `where("user_id", "=", $userId)`: Adds a WHERE clause to filter results where "user_id" equals a specific value.
- `or("username", "=", $userName)`: Adds an OR condition to the WHERE clause where "username" equals a specific value.
- `orderby(["user_id" => "DESC"])`: Orders the results by the "user_id" column in descending order.
- `limit(5)`: Limits the number of results to 5.
- `offset(1)`: Specifies the offset for the results.
- `decrypt([...])`: Specifies columns for decryption with encryption method and key.
- `encrypt([...])`: Specifies columns for encryption with encryption method and key.
- `execute()`: Executes the constructed query and returns an array of objects.

<h4>'select' method:</h4>

```php
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
```
This example constructs a SELECT query for the "typepick_users" table with specific conditions, including decryption for "email" and "username" columns and encryption for the "username" column. The method returns an array of objects.

- `in("typepick_users")`: Specifies the target table as "typepick_users."
- `select(["user_id", "username", "email"])`: Specifies the columns to be selected in the query.
- `where("user_id", "=", $userId)`: Adds a WHERE clause to filter results where "user_id" equals a specific value.
- `or("username", "=", $userName)`: Adds an OR condition to the WHERE clause where "username" equals a specific value.
- `decrypt([...])`: Specifies columns for decryption with encryption method and key.
- `encrypt([...])`: Specifies columns for encryption with encryption method and key.
- `execute()`: Executes the constructed query and returns an array of objects.

<h4>'update' method:</h4>

```php
$queryBuilder
    ->in("typepick_users")
    ->update(["username" => "new_username"])
    ->where("user_id", "=", $userId)
    ->encrypt([
        "username" => ["method" => "BASE64"],
    ])
    ->execute();
```

This code updates the "typepick_users" table, setting the "username" to 'new_username' for a specific user ID, including encryption for the "username" column.

- `in("typepick_users")`: Specifies the target table as "typepick_users."
- `update(["username" => "new_username"])`: Specifies the column to be updated and its new value.
- `where("user_id", "=", $userId)`: Adds a WHERE clause to filter results where "user_id" equals a specific value.
- `encrypt([...])`: Specifies columns for encryption with encryption method and key.
- `execute()`: Executes the constructed query.

<h4>'insert' method:</h4>

```php
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
```
This snippet demonstrates an INSERT query for the "typepick_users" table with specified values, including encryption for the "username" and "createdTime" columns.

- `in("typepick_users")`: Specifies the target table as "typepick_users."
- `insert([...])`: Specifies the columns and their values to be inserted.
- `encrypt([...])`: Specifies columns for encryption with encryption method and key.
- `execute()`: Executes the constructed query.

<h4>'delete' method:</h4>

```php
$queryBuilder
    ->in("typepick_users")
    ->delete()
    ->where("username", "=", $userId)
    ->and("email", "=", $userEmail)
    ->execute();
```
Here, a DELETE query is constructed for the "typepick_users" table with conditions on "username" and "email," including encryption for the "email" column.

- `in("typepick_users")`: Specifies the target table as "typepick_users."
- `delete()`: Constructs a DELETE query.
- `where("username", "=", $userId)`: Adds a WHERE clause to filter results where "username" equals a specific value.
- `and("email", "=", $userEmail)`: Adds an AND condition to the WHERE clause where "email" equals a specific value.
- `execute()`: Executes the constructed query.

<h4>'query' method: designed to handle complex scenarios and secure parameter binding</h4>

```php
$buildQuery = $queryBuilder->query(
    "find",
    "SELECT id, uid, article_id, created_time FROM typepick_users WHERE uid = :uid",
    [":uid" => $userId]
);
```
This method is particularly useful when dealing with specific scenarios where the standard CRUD operations provided by the queryBuilder class may not cover all use cases. It allows developers to create and execute custom SQL queries while benefiting from the security features provided by the underlying query building infrastructure.

- `query("find", "SELECT ...", [":uid" => $userId])`: Executes a custom SQL query with secure parameter binding.
  - `"find"`: A label or identifier for the custom query.
  - `"SELECT ..."`: The custom SQL query to be executed.
  - `[":uid" => $userId]`: An associative array for secure parameter binding, protecting against SQL injection.
