<?php
class tpQuery
{
    /**
     * Represents a database interaction class providing a fluent interface for building and executing queries.
     *
     * @property string $table The target database table for the query.
     * @property string $queryType The type of query being constructed (e.g., update, insert, find, foreach, count, delete).
     * @property array $updateData The data to be updated in an update query.
     * @property array $whereData The conditions to be applied in a query.
     * @property array $insertData The data to be inserted in an insert query.
     * @property array $selectColumns The columns to be selected in a find or count query.
     * @property string $countColumn The column to be counted in a count query.
     * @property mixed $databaseConnection The PDO database connection.
     * @property array $encryption An associative array containing encryption settings.
     * @property array $decryption An associative array containing decryption settings.
     * @property string $fetchType The fetch type to use for result sets (default is "obj").
     * @property array $errors An array containing error messages encountered during query execution.
     * @property array $FETCH_TYPES An associative array mapping fetch types for result sets.
     *
     */
    private $table;
    private $queryType;
    private $updateData = [];
    private $whereData = [];
    private $insertData = [];
    private $selectColumns = [];
    private $countColumn;
    private $databaseConnection;
    const FETCH_TYPES = [
        "assoc" => PDO::FETCH_ASSOC,
        "both" => PDO::FETCH_BOTH,
        "bound" => PDO::FETCH_BOUND,
        "class" => PDO::FETCH_CLASS,
        "into" => PDO::FETCH_INTO,
        "lazy" => PDO::FETCH_LAZY,
        "named" => PDO::FETCH_NAMED,
        "num" => PDO::FETCH_NUM,
        "obj" => PDO::FETCH_OBJ,
    ];
    private static $pdoTypes = [
        "boolean" => PDO::PARAM_BOOL,
        "integer" => PDO::PARAM_INT,
        "double" => PDO::PARAM_INT,
        "string" => PDO::PARAM_STR,
        "NULL" => PDO::PARAM_NULL,
    ];
    private $encryption = [];
    private $decryption = [];
    private $fetchType;
    private $errors = [];

    /**
     * Constructor method accepting a PDO database connection.
     *
     * @param mixed $pdo The PDO database connection.
     */
    public function __construct($pdo)
    {
        $this->databaseConnection = $pdo;
        $this->fetchType = "obj";
    }

    /**
     * Adds an error message to the list of errors.
     *
     * @param string $errorMessage The error message to be added.
     *
     * @return $this Returns the current instance for method chaining.
     */
    public function addError($errorMessage)
    {
        $this->errors[] = $errorMessage;
        return $this;
    }

    /**
     * Retrieves the list of errors.
     *
     * @return array The array containing error messages encountered during query execution.
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Sets the target database table for the query.
     *
     * @param string $table The name of the target database table.
     *
     * @return $this Returns the current instance for method chaining.
     */
    public function in($table)
    {
        $this->table = $table;
        return $this;
    }

    /**
     * Sets the query type to update and provides update data.
     *
     * @param array $updateData The data to be updated in the query.
     *
     * @return $this Returns the current instance for method chaining.
     */
    public function update(array $updateData)
    {
        $this->queryType = "update";
        $this->updateData = $updateData;
        return $this;
    }

    /**
     * Sets the query type to insert and provides insert data.
     *
     * @param array $insertData The data to be inserted in the query.
     *
     * @return $this Returns the current instance for method chaining.
     */
    public function insert(array $insertData)
    {
        $this->queryType = "insert";
        $this->insertData = $insertData;
        return $this;
    }

    /**
     * Sets the query type to find and provides select columns.
     *
     * @param array $selectColumns The columns to be selected in the query.
     *
     * @return $this Returns the current instance for method chaining.
     */
    public function select(array $selectColumns)
    {
        $this->queryType = "find";
        $this->selectColumns = $selectColumns;
        return $this;
    }

    /**
     * Sets the query type to foreach and provides select columns.
     *
     * @param array $selectColumns The columns to be selected in the query.
     *
     * @return $this Returns the current instance for method chaining.
     */
    public function selectAll(array $selectColumns)
    {
        $this->queryType = "foreach";
        $this->selectColumns = $selectColumns;
        return $this;
    }

    /**
     * Sets the query type to count and provides count columns.
     *
     * @param array $selectColumns The columns to be counted in the query.
     *
     * @return $this Returns the current instance for method chaining.
     */
    public function count(array $selectColumns)
    {
        $this->queryType = "count";
        $this->selectColumns = $selectColumns;
        return $this;
    }

    /**
     * Sets the query type to delete.
     *
     * @return $this Returns the current instance for method chaining.
     */
    public function delete()
    {
        $this->queryType = "delete";
        return $this;
    }

    /**
     * Binds a single value to a named placeholder in a prepared statement.
     *
     * @param PDOStatement $statement The prepared statement
     * @param string $paramName The parameter name (with or without a leading colon)
     * @param mixed $value The value to bind
     * @param int|null $pdoType The PDO type (auto-detected if not provided)
     *
     * @return bool True on success, false on failure
     */
    private static function bindValue(
        PDOStatement $statement,
        string $paramName,
        $value,
        ?int $pdoType = null
    ): bool {
        // Ensure the parameter name has a leading colon
        if ($paramName[0] !== ":") {
            $paramName = ":" . $paramName;
        }

        // Auto-determine PDO type if not provided
        if ($pdoType === null) {
            $pdoType = self::determinePdoType($value);
        }

        return $statement->bindValue($paramName, $value, $pdoType);
    }

    /**
     * Determines the PDO type for a given PHP value.
     *
     * @param mixed $value The PHP value
     *
     * @return int The corresponding PDO type
     */
    private static function determinePdoType($value): int
    {
        $phpType = gettype($value);

        return self::$pdoTypes[$phpType] ?? PDO::PARAM_STR;
    }

    /**
     * Binds multiple values to named placeholders in a prepared statement.
     *
     * @param PDOStatement $statement The prepared statement
     * @param array $paramST Associative array of parameter names and values
     * @param int|null $pdoType The PDO type for all parameters (optional)
     *
     * @return bool True on success, false on failure
     */
    public static function bindValues(
        PDOStatement $statement,
        array $paramST,
        ?int $pdoType = null
    ): bool {
        $success = true;

        // Bind each value in the associative array
        foreach ($paramST as $paramName => $value) {
            $success =
                self::bindValue($statement, $paramName, $value, $pdoType) &&
                $success;
        }

        return $success;
    }

    /**
     * Executes the constructed query with an optional fetch type (default is "obj").
     *
     * @param string $type The fetch type for result sets (default is "obj").
     *
     * @return mixed The result of the executed query based on the specified action and fetch type.
     */
    public function execute($type = "obj")
    {
        $this->fetchType = $type;
        // Build and execute the query
        return $this->query(
            $this->getAction(),
            $this->getQuery(),
            $this->getBindings(),
            $this->fetchType
        );
    }

    /**
     * Executes the prepared query based on the specified action and returns the result.
     *
     * @param string|null $action The type of query action (e.g., find, foreach, count, delete, insert, update).
     * @param string|null $statement The prepared SQL query statement.
     * @param array $parameters An associative array of parameters to bind to the query.
     * @param string|null $fetchType The fetch type for result sets (default is "obj").
     *
     * @return mixed The result of the executed query based on the specified action and fetch type.
     *
     * @throws Exception If an invalid fetch type or query action is provided, or if a database error occurs.
     */

    public function query(
        $action = null,
        $statement = null,
        $parameters = [],
        $fetchType = null
    ) {
        try {
            if ($fetchType === null) {
                $fetchType = $this->fetchType;
            }

            if (!empty($this->errors)) {
                return $this->errors;
            }

            if (!array_key_exists($fetchType, self::FETCH_TYPES)) {
                throw new Exception("Invalid fetch type: $fetchType");
            }

            $resultActions = [
                "count" => function ($stmt) {
                    $stmt->execute();
                    return $stmt->rowCount();
                },
                "find" => function ($stmt) use ($fetchType) {
                    $stmt->execute();
                    return $stmt->fetch(self::FETCH_TYPES[$fetchType]);
                },
                "foreach" => function ($stmt) use ($fetchType) {
                    $stmt->execute();
                    return $stmt->fetchAll(self::FETCH_TYPES[$fetchType]);
                },
                "delete" => function ($stmt) {
                    $stmt->execute();
                    return $stmt->rowCount(); // Return the number of affected rows for delete
                },
                "insert" => function ($stmt) {
                    $stmt->execute();
                    return $this->databaseConnection->lastInsertId(); // Return the last inserted ID for insert
                },
                "update" => function ($stmt) {
                    $stmt->execute();
                    return $stmt->rowCount(); // Return the number of affected rows for update
                },
            ];
            if (!array_key_exists($action, $resultActions)) {
                throw new Exception("Invalid query action: $action");
            }

            $this->databaseConnection->beginTransaction();
            $preparedQuery = $this->databaseConnection->prepare($statement);
            self::bindValues($preparedQuery, $parameters);

            $data = $resultActions[$action]($preparedQuery);

            $this->databaseConnection->commit();
        } catch (PDOException $e) {
            $this->databaseConnection->rollback();
            throw new Exception(
                "Database error: " . $e->getMessage(),
                (int) $e->getCode(),
                $eaes
            );
        } catch (Exception $e) {
            $this->databaseConnection->rollback();
            throw $e; // Rethrow the exception after rollback
        }
        $this->clear();
        return $data;
    }

    /**
     * Gets the current query type.
     *
     * @return string The current query type (e.g., update, insert, find, foreach, count, delete).
     */
    public function getAction()
    {
        return $this->queryType;
    }

    /**
     * Builds and retrieves the SQL query based on the current query type.
     *
     * @return string|null The constructed SQL query or null for unsupported query types.
     */
    public function getQuery()
    {
        switch ($this->queryType) {
            case "update":
                return $this->buildUpdate();
            case "insert":
                return $this->buildInsert();
            case "find":
                return $this->buildSelect();
            case "foreach":
                return $this->buildSelect();
            case "count":
                return $this->buildSelect();
            case "delete":
                return $this->buildDelete();
            default:
                return null;
        }
    }

    /**
     * Retrieves the bindings for the current query, including update and insert data.
     *
     * @return array An associative array containing the query bindings.
     */
    public function getBindings()
    {
        $bindings = [];

        // Merge update, where, and insert bindings
        $bindings = array_merge($bindings, $this->getUpdateBindings());

        if (!empty($this->whereData)) {
            $bindings = array_merge($bindings, $this->getWhereBindings());
        }

        $bindings = array_merge($bindings, $this->getInsertBindings());

        return $bindings;
    }

    /**
     * Retrieves the bindings for the update query.
     *
     * @return array An associative array containing the update query bindings.
     */
    private function getUpdateBindings()
    {
        $bindings = [];
        foreach ($this->updateData as $column => $value) {
            $bindings[":$column"] = $value;
        }
        return $bindings;
    }

    /**
     * Retrieves the bindings for the insert query.
     *
     * @return array An associative array containing the insert query bindings.
     */
    private function getInsertBindings()
    {
        $bindings = [];
        foreach ($this->insertData as $column => $value) {
            $bindings[":$column"] = $value;
        }
        return $bindings;
    }
    /**
     * Applies encryption to the specified column value based on the provided encryption information.
     *
     * @param string $column The name of the column.
     * @param mixed $value The value to be encrypted.
     * @param array $encryptionInfo An associative array containing encryption details.
     *
     * @return string The encrypted value.
     * @throws Exception If an unknown encryption method is encountered.
     */
    private function applyEncryptionToColumn($column, $value, $encryptionInfo)
    {
        $method = $encryptionInfo["method"];
        $key = $encryptionInfo["key"] ?? "";
        $use =
            isset($encryptionInfo["use"]) &&
            in_array(strtoupper($encryptionInfo["use"]), ["HEX", "BASE64"])
                ? strtoupper($encryptionInfo["use"])
                : null;

        switch ($method) {
            case "BASE64":
                return $this->applyBase64Encryption($value, $use);
            case "HEX":
                return $this->applyHexEncryption($value, $use);
            case "AES":
                return $this->applyAESEncryption(
                    $value,
                    self::validateKey($key),
                    $use
                );
            case "MD5":
                return "MD5($value)";
            case "SHA256":
                return "SHA2($value, 256)";
            default:
                throw new Exception("Unknown encryption method: $method");
        }
    }

    /**
     * Applies Base64 encryption to the specified value.
     *
     * @param string $value The value to be encrypted.
     * @param string|null $use The optional format to be used (HEX or BASE64).
     *
     * @return string The Base64 encrypted value.
     */
    private function applyBase64Encryption($value, $use)
    {
        return $use === "HEX" ? "TO_BASE64(HEX($value))" : "TO_BASE64($value)";
    }

    /**
     * Applies Hex encryption to the specified value.
     *
     * @param string $value The value to be encrypted.
     * @param string|null $use The optional format to be used (BASE64 or HEX).
     *
     * @return string The Hex encrypted value.
     */
    private function applyHexEncryption($value, $use)
    {
        return $use === "BASE64"
            ? "TO_BASE64(FROM_BASE64($value))"
            : "HEX($value)";
    }

    /**
     * Applies AES encryption to the specified value using the provided key.
     *
     * @param string $value The value to be encrypted.
     * @param string $key The encryption key.
     * @param string|null $use The optional format to be used (BASE64 or HEX).
     *
     * @return string The AES encrypted value.
     */
    private function applyAESEncryption($value, $key, $use)
    {
        return $use === "BASE64"
            ? "TO_BASE64(AES_ENCRYPT($value, '$key'))"
            : "AES_ENCRYPT($value, '$key')";
    }

    /**
     * Applies decryption to the specified column value based on the provided encryption information.
     *
     * @param string $column The name of the column.
     * @param mixed $value The value to be decrypted.
     * @param array $encryptionInfo An associative array containing encryption details.
     *
     * @return string The decrypted value.
     * @throws Exception If an unknown decryption method is encountered or decryption is not possible.
     */
    private function applyDecryptionToColumn($column, $value, $encryptionInfo)
    {
        $method = $encryptionInfo["method"];
        $key = $encryptionInfo["key"] ?? "";
        $use =
            isset($encryptionInfo["use"]) &&
            in_array(strtoupper($encryptionInfo["use"]), ["HEX", "BASE64"])
                ? strtoupper($encryptionInfo["use"])
                : null;

        switch ($method) {
            case "BASE64":
                return $this->applyBase64Decryption($value);
            case "HEX":
                return $this->applyHexDecryption($value);
            case "AES":
                return $this->applyAESDecryption(
                    $value,
                    self::validateKey($key),
                    $use
                );
            case "MD5":
                throw new Exception(
                    "MD5 is a one-way hash and cannot be decrypted"
                );
            case "SHA256":
                throw new Exception(
                    "SHA256 is a one-way hash and cannot be decrypted"
                );
            default:
                throw new Exception("Unknown decryption method: $method");
        }
    }

    /**
     * Applies Base64 decryption to the specified value.
     *
     * @param string $value The value to be decrypted.
     *
     * @return string The Base64 decrypted value.
     */
    private function applyBase64Decryption($value)
    {
        return "FROM_BASE64($value)";
    }

    /**
     * Applies Hex decryption to the specified value.
     *
     * @param string $value The value to be decrypted.
     *
     * @return string The Hex decrypted value.
     */
    private function applyHexDecryption($value)
    {
        return "UNHEX($value)";
    }

    /**
     * Applies AES decryption to the specified value using the provided key.
     *
     * @param string $value The value to be decrypted.
     * @param string $key The decryption key.
     * @param string|null $use The optional format to be used (BASE64 or HEX).
     *
     * @return string The AES decrypted value.
     */
    private function applyAESDecryption($value, $key, $use)
    {
        if (!empty($use)) {
            $value =
                $use === "BASE64" ? "FROM_BASE64($value)" : "UNHEX($value)";
        }

        return "AES_DECRYPT($value, '$key')";
    }

    /**
     * Configures encryption for the specified columns.
     *
     * @param array $encryptionData An associative array where keys are column names and values are encryption details.
     *
     * @return $this The current instance for method chaining.
     */
    public function encrypt(array $encryptionData)
    {
        foreach ($encryptionData as $column => $encryptionInfo) {
            if (is_array($encryptionInfo)) {
                $this->encryption[$column] = $encryptionInfo;
            }
        }
        return $this;
    }

    /**
     * Decrypts specified columns with corresponding decryption information.
     *
     * @param array $decryptionData An associative array where keys are column names and values are decryption information.
     *
     * @return $this The current instance for method chaining.
     */
    public function decrypt(array $decryptionData)
    {
        foreach ($decryptionData as $column => $decryptionInfo) {
            if (is_array($decryptionInfo)) {
                $this->decryption[$column] = $decryptionInfo;
            }
        }
        return $this;
    }

    /**
     * Retrieves the bindings for the WHERE conditions in the query.
     *
     * @return array An associative array containing the WHERE condition bindings.
     */
    private function getWhereBindings()
    {
        $bindings = [];
        $bindingCounts = []; // Track the count of each binding name

        foreach ($this->whereData as $condition) {
            $column = $condition["column"];
            $value = $condition["value"];

            // If the value is an array, convert it to a string or handle it accordingly
            $bindingName = ":$column";
            $bindingCounts[$bindingName] = isset($bindingCounts[$bindingName])
                ? $bindingCounts[$bindingName] + 1
                : 1;
            $uniqueBindingName = $bindingName . $bindingCounts[$bindingName];

            $bindings[$uniqueBindingName] = is_array($value)
                ? implode(", ", $value)
                : $value;
        }

        return $bindings;
    }

    /**
     * Adds a WHERE condition to the query.
     *
     * @param string $column The column name.
     * @param string|null $operator The comparison operator.
     * @param mixed $value The value to compare against.
     *
     * @return $this The current instance for method chaining.
     */
    public function where($column, $operator = null, $value = null)
    {
        $this->parseConditions([$column, $operator, $value, "AND"]);
        return $this;
    }

    /**
     * Adds an AND condition to the query.
     *
     * @param string $column The column name.
     * @param string $operator The comparison operator.
     * @param mixed $value The value to compare against.
     *
     * @return $this The current instance for method chaining.
     */
    public function and($column, $operator, $value)
    {
        $this->parseConditions([$column, $operator, $value, "AND"]);
        return $this;
    }

    /**
     * Adds an OR condition to the query.
     *
     * @param string $column The column name.
     * @param string $operator The comparison operator.
     * @param mixed $value The value to compare against.
     *
     * @return $this The current instance for method chaining.
     */
    public function or($column, $operator, $value)
    {
        $this->parseConditions([$column, $operator, $value, "OR"]);
        return $this;
    }

    /**
     * Parses and adds conditions to the WHERE clause of the query.
     *
     * @param array $condition An array representing a single condition.
     *
     * @return void
     */
    private function parseConditions(array $condition)
    {
        // Ensure that the condition is an array with at least three elements
        if (count($condition) >= 3) {
            $column = $condition[0];
            $operator = strtoupper($condition[1]);
            $value = $condition[2];
            $logicalOperator = isset($condition[3])
                ? strtoupper($condition[3])
                : "AND";

            // Add the condition to whereData
            $this->whereData[] = compact(
                "column",
                "operator",
                "value",
                "logicalOperator"
            );
        } elseif (count($condition) == 1) {
            // Handle logical operators for the whole condition set
            $logicalOperator = strtoupper($condition[0]);
            $this->whereData[] = compact("logicalOperator");
        } else {
            $this->addError("Invalid condition format");
        }
    }
    /**
     * Builds the SELECT query.
     *
     * @return string The generated SELECT query.
     */
    private function buildSelect()
    {
        $query = "SELECT ";
        $columns = [];
        $bindingCounts = [];

        foreach ($this->selectColumns as $column) {
            // Use the binding name generated in getWhereBindings
            $bindingName = "$column";

            // Add count for unique binding names
            $bindingCounts[$bindingName] = isset($bindingCounts[$bindingName])
                ? $bindingCounts[$bindingName] + 1
                : 1;

            $uniqueBindingName = $bindingName;

            // Check if decryption is required and apply it
            if (isset($this->decryption[$column])) {
                $decryptedColumn = $this->applyDecryptionToColumn(
                    $column,
                    $uniqueBindingName,
                    $this->decryption[$column]
                );
                $columns[] = "$decryptedColumn as $column";
            } else {
                $columns[] = "$column";
                $this->bindings[$uniqueBindingName] = $column;
            }
        }

        $query .= implode(", ", $columns);

        $query .= " FROM {$this->table}";
        $bindingCounts = [];
        // Build the WHERE part of the query using the same binding names
        if (!empty($this->whereData)) {
            $where = [];
            $clause = "";

            foreach ($this->whereData as $index => $condition) {
                $logicalOperator = isset($condition["logicalOperator"])
                    ? $condition["logicalOperator"]
                    : "AND";
                $column = $condition["column"];
                $operator = $condition["operator"];
                $value = $condition["value"];

                // Use the binding name generated in getWhereBindings
                $bindingName = ":$column";

                // Add count for unique binding names (shared with SELECT part)
                $bindingCounts[$bindingName] = isset(
                    $bindingCounts[$bindingName]
                )
                    ? $bindingCounts[$bindingName] + 1
                    : 1;

                $uniqueBindingName =
                    $bindingName . $bindingCounts[$bindingName];

                // Check if encryption or decryption is required and apply it
                if (isset($this->encryption[$column])) {
                    $clause =
                        "$column $operator " .
                        $this->applyEncryptionToColumn(
                            $column,
                            $uniqueBindingName,
                            $this->encryption[$column]
                        );
                } else {
                    $clause = "$column $operator $uniqueBindingName";
                    $this->bindings[$uniqueBindingName] = $value;
                }

                // Add logical operator only if there's a next item
                if ($index < count($this->whereData) - 1) {
                    $where[] = "$clause $logicalOperator ";
                } else {
                    $where[] = $clause;
                }
            }

            $query .= " WHERE " . implode(" ", $where);
        }

        return $query;
    }

    /**
     * Builds the UPDATE query.
     *
     * @return string The generated UPDATE query.
     */
    private function buildUpdate()
    {
        $query = "UPDATE {$this->table}";

        // Build the SET part of the query
        if (!empty($this->updateData)) {
            $set = [];
            foreach ($this->updateData as $column => $value) {
                // Check if decryption is required and apply it
                if (isset($this->encryption[$column])) {
                    $set[] =
                        "$column = " .
                        $this->applyEncryptionToColumn(
                            $column,
                            ":$column",
                            $this->encryption[$column]
                        );
                } else {
                    // Use the binding name generated in getWhereBindings
                    $bindingName = ":$column";
                    $set[] = "$column = $bindingName";
                    $this->bindings[$bindingName] = $value;
                }
            }
            $query .= " SET " . implode(", ", $set);
        }

        // Build the WHERE part of the query using the same binding names
        if (!empty($this->whereData)) {
            $where = [];
            $clause = "";

            foreach ($this->whereData as $index => $condition) {
                $logicalOperator = isset($condition["logicalOperator"])
                    ? $condition["logicalOperator"]
                    : "AND";
                $column = $condition["column"];
                $operator = $condition["operator"];
                $value = $condition["value"];

                // Use the binding name generated in getWhereBindings
                $bindingName = ":$column";

                // Add count for unique binding names
                $bindingCounts[$bindingName] = isset(
                    $bindingCounts[$bindingName]
                )
                    ? $bindingCounts[$bindingName] + 1
                    : 1;
                $uniqueBindingName =
                    $bindingName . $bindingCounts[$bindingName];

                // Check if encryption is required and apply it
                if (isset($this->encryption[$column])) {
                    $clause =
                        "$column $operator " .
                        $this->applyEncryptionToColumn(
                            $column,
                            $uniqueBindingName,
                            $this->encryption[$column]
                        );
                } elseif (isset($this->decryption[$column])) {
                    $clause =
                        "$column $operator " .
                        $this->applyDecryptionToColumn(
                            $column,
                            $uniqueBindingName,
                            $this->decryption[$column]
                        );
                } else {
                    $clause = "$column $operator $uniqueBindingName";
                    $this->bindings[$uniqueBindingName] = $value;
                }

                // Add logical operator only if there's a next item
                if ($index < count($this->whereData) - 1) {
                    $where[] = "$clause $logicalOperator ";
                } else {
                    $where[] = $clause;
                }
            }

            $query .= " WHERE " . implode(" ", $where);
        }

        return $query;
    }

    /**
     * Builds the DELETE query.
     *
     * @return string The generated DELETE query.
     */
    private function buildDelete()
    {
        $query = "DELETE FROM {$this->table}";

        // Build the WHERE part of the query using the same binding names
        if (!empty($this->whereData)) {
            $where = [];
            foreach ($this->whereData as $index => $condition) {
                $logicalOperator = isset($condition["logicalOperator"])
                    ? $condition["logicalOperator"]
                    : "AND";
                $column = $condition["column"];
                $operator = $condition["operator"];
                $value = $condition["value"];

                // Use the binding name generated in getWhereBindings
                $bindingName = ":$column";

                // Add count for unique binding names
                $bindingCounts[$bindingName] = isset(
                    $bindingCounts[$bindingName]
                )
                    ? $bindingCounts[$bindingName] + 1
                    : 1;
                $uniqueBindingName =
                    $bindingName . $bindingCounts[$bindingName];

                // Check if encryption or decryption is required and apply it
                if (isset($this->encryption[$column])) {
                    $clause =
                        "$column $operator " .
                        $this->applyEncryptionToColumn(
                            $column,
                            $uniqueBindingName,
                            $this->encryption[$column]
                        );
                } elseif (isset($this->decryption[$column])) {
                    $clause =
                        "$column $operator " .
                        $this->applyDecryptionToColumn(
                            $column,
                            $uniqueBindingName,
                            $this->decryption[$column]
                        );
                } else {
                    $clause = "$column $operator $uniqueBindingName";
                    $this->bindings[$uniqueBindingName] = $value;
                }

                // Add logical operator only if it's not the first condition
                if (!empty($where)) {
                    $where[] = "$logicalOperator $clause";
                } else {
                    $where[] = $clause;
                }
            }
            $query .= " WHERE " . implode(" ", $where);
        }

        return $query;
    }

    /**
     * Builds the INSERT query.
     *
     * @return string The generated INSERT query.
     */
    private function buildInsert()
    {
        $query = "INSERT INTO {$this->table}";

        // Build the column names part of the query
        $columns = [];
        foreach ($this->insertData as $column => $value) {
            // Check if encryption or decryption is required and apply it
            if (isset($this->encryption[$column])) {
                $columns[] = $column;
            } elseif (isset($this->decryption[$column])) {
                $columns[] = $column;
            } else {
                $columns[] = $column;
            }
        }
        $query .= " (" . implode(", ", $columns) . ")";

        // Build the VALUES part of the query
        $values = [];
        foreach ($this->insertData as $column => $value) {
            // Check if encryption or decryption is required and apply it
            if (isset($this->encryption[$column])) {
                $values[] = $this->applyEncryptionToColumn(
                    $column,
                    ":$column",
                    $this->encryption[$column]
                );
            } elseif (isset($this->decryption[$column])) {
                $values[] = $this->applyDecryptionToColumn(
                    $column,
                    ":$column",
                    $this->decryption[$column]
                );
            } else {
                $values[] = ":$column";
            }
        }
        $query .= " VALUES (" . implode(", ", $values) . ")";

        return $query;
    }

    /**
     * Validates the encryption key and ensures its length is valid (128, 192, or 256 bits).
     *
     * @param string $key The encryption key to be validated.
     * @return string The validated key with a length of 32 bytes.
     */
    public static function validateKey($key)
    {
        // Ensure the key length is valid (128, 192, or 256 bits)
        return substr(hash("sha256", $key, true), 0, 32);
    }

    /**
     * Clears all data and settings in the query builder instance.
     */
    public function clear()
    {
        $this->table = null;
        $this->updateData = [];
        $this->queryType = null;
        $this->whereData = [];
        $this->insertData = [];
        $this->selectColumns = [];
        $this->countColumn = null;
        $this->encryption = [];
        $this->decryption = [];
    }

    public static function AESencrypt(
        $value,
        $use = null,
        $key = initSetup::TYPEPICK_AES_KEY
    ) {
        if (!empty($use)) {
            if ($use === "BASE64") {
                // Encrypt the value using AES_ENCRYPT and encode the result with BASE64
                return "TO_BASE64(AES_ENCRYPT(" .
                    $value .
                    ", '" .
                    self::validateKey($key) .
                    "'))";
            } elseif ($use === "HEX") {
                // Encrypt the value using AES_ENCRYPT and encode the result with HEX
                return "HEX(AES_ENCRYPT(" .
                    $value .
                    ", '" .
                    self::validateKey($key) .
                    "'))";
            }
        } else {
            // Encrypt the value using AES_ENCRYPT
            return "AES_ENCRYPT(" .
                $value .
                ", '" .
                self::validateKey($key) .
                "')";
        }
    }

    public static function AESdecrypt(
        $encryptedValue,
        $use = null,
        $key = initSetup::TYPEPICK_AES_KEY
    ) {
        if (!empty($use)) {
            if ($use === "BASE64") {
                // Decode the BASE64 and then decrypt using AES_DECRYPT
                return "AES_DECRYPT(FROM_BASE64('" .
                    $encryptedValue .
                    "'), '" .
                    self::validateKey($key) .
                    "')";
            } elseif ($use === "HEX") {
                // Decode the HEX and then decrypt using AES_DECRYPT
                return "AES_DECRYPT(UNHEX('" .
                    $encryptedValue .
                    "'), '" .
                    self::validateKey($key) .
                    "')";
            }
        } else {
            // Decrypt using AES_DECRYPT
            return "AES_DECRYPT('" .
                $encryptedValue .
                "', '" .
                self::validateKey($key) .
                "')";
        }
    }
}

?>
