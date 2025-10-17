<?php
namespace hitwpupdateserver\app\core;

use PDO;
use PDOException;

class database
{
    private ?PDO $link = null;
    private static ?database $inst = null;
    public static int $counter = 0;
    private string $_dbprefix = '';

    /**
     * Log database errors
     */
    public function log_db_errors(string $error, string $query): void
    {
        $message = '<p>Error at ' . date('Y-m-d H:i:s') . ':</p>';
        $message .= '<p>Query: ' . htmlentities($query) . '<br />';
        $message .= 'Error: ' . $error;
        $message .= '</p>';

        if (defined('SEND_ERRORS_TO')) {
            $headers = 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
            $headers .= 'To: Admin <' . SEND_ERRORS_TO . '>' . "\r\n";
            $headers .= 'From: Yoursite <system@' . $_SERVER['SERVER_NAME'] . '.com>' . "\r\n";
            mail(SEND_ERRORS_TO, 'Database Error', $message, $headers);
        } else {
            trigger_error($message);
        }

        if (!defined('DISPLAY_DEBUG') || (defined('DISPLAY_DEBUG') && DISPLAY_DEBUG)) {
            echo $message;
        }
    }

    /**
     * Constructor - establish database connection
     */
    public function __construct(string $DB_HOST, string $DB_USER, string $DB_PASS, string $DB_NAME)
    {
        try {
            $dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::ATTR_PERSISTENT => false
            ];

            $this->link = new PDO($dsn, $DB_USER, $DB_PASS, $options);
        } catch (PDOException $e) {
            die('Unable to connect to database: ' . $e->getMessage());
        }
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        $this->disconnect();
    }

    /**
     * Sanitize user data for display (not needed for PDO queries with prepared statements)
     * Kept for backward compatibility with existing code
     */
    public function filter($data): mixed
    {
        if (!is_array($data)) {
            $data = trim(htmlentities($data, ENT_QUOTES, 'UTF-8', false));
        } else {
            $data = array_map(array($this, 'filter'), $data);
        }
        return $data;
    }

    /**
     * Note: With PDO prepared statements, escape is not needed for queries
     * This is kept for backward compatibility only
     */
    public function escape($data): mixed
    {
        if (!is_array($data)) {
            $data = $data; // PDO handles escaping via prepared statements
        } else {
            $data = array_map(array($this, 'escape'), $data);
        }
        return $data;
    }

    /**
     * Normalize sanitized data for display
     */
    public function clean(string $data): string
    {
        $data = stripslashes($data);
        $data = html_entity_decode($data, ENT_QUOTES, 'UTF-8');
        $data = nl2br($data);
        $data = urldecode($data);
        return $data;
    }

    /**
     * Check for common MySQL functions
     */
    public function db_common($value = ''): bool
    {
        if (is_array($value)) {
            foreach ($value as $v) {
                if (preg_match('/AES_DECRYPT|AES_ENCRYPT|NOW\(\)/i', $v)) {
                    return true;
                }
            }
            return false;
        }
        return (bool)preg_match('/AES_DECRYPT|AES_ENCRYPT|NOW\(\)/i', $value);
    }

    /**
     * Perform raw query (use with caution - NO prepared statements)
     * WARNING: Only use with trusted/sanitized input!
     */
    public function query(string $query): bool
    {
        self::$counter++;
        try {
            $this->link->exec($query);
            return true;
        } catch (PDOException $e) {
            $this->log_db_errors($e->getMessage(), $query);
            return false;
        }
    }

    /**
     * Execute prepared statement - SECURE version
     *
     * Example:
     * $db->prepare_execute("SELECT * FROM users WHERE id = ? AND status = ?", [123, 'active']);
     */
    public function prepare_execute(string $query, array $params = []): mixed
    {
        self::$counter++;
        try {
            $stmt = $this->link->prepare($query);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            $this->log_db_errors($e->getMessage(), $query);
            return false;
        }
    }

    /**
     * Check if table exists
     */
    public function table_exists(string $name): bool
    {
        self::$counter++;
        try {
            $result = $this->link->query("SELECT 1 FROM {$name} LIMIT 1");
            return $result !== false;
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * Count number of rows - RAW QUERY (not secure with user input!)
     * Use prepare_num_rows() for user input
     */
    public function num_rows(string $query): int
    {
        self::$counter++;
        try {
            $stmt = $this->link->query($query);
            return $stmt->rowCount();
        } catch (PDOException $e) {
            $this->log_db_errors($e->getMessage(), $query);
            return 0;
        }
    }

    /**
     * Count rows with prepared statement - SECURE
     *
     * Example:
     * $count = $db->prepare_num_rows("SELECT id FROM users WHERE status = ?", ['active']);
     */
    public function prepare_num_rows(string $query, array $params = []): int
    {
        $stmt = $this->prepare_execute($query, $params);
        return $stmt ? $stmt->rowCount() : 0;
    }

    /**
     * Check if value exists in table - SECURE (uses prepared statements)
     */
    public function exists(string $table = '', string $check_val = '', array $params = []): bool
    {
        self::$counter++;

        if (empty($table) || empty($check_val) || empty($params)) {
            return false;
        }

        $check = [];
        $values = [];

        foreach ($params as $field => $value) {
            if (!empty($field) && !empty($value)) {
                if ($this->db_common($value)) {
                    $check[] = "`{$field}` = {$value}";
                } else {
                    $check[] = "`{$field}` = ?";
                    $values[] = $value;
                }
            }
        }

        $check_str = implode(' AND ', $check);
        $sql = "SELECT {$check_val} FROM `{$table}` WHERE {$check_str}";

        try {
            $stmt = $this->link->prepare($sql);
            $stmt->execute($values);
            return $stmt->rowCount() > 0;
        } catch (PDOException $e) {
            $this->log_db_errors($e->getMessage(), $sql);
            return false;
        }
    }

    /**
     * Get single row - RAW QUERY (not secure with user input!)
     * Use prepare_get_row() for user input
     */
    public function get_row(string $query, bool $object = false): mixed
    {
        self::$counter++;
        try {
            $stmt = $this->link->query($query);
            return $object ? $stmt->fetch(PDO::FETCH_OBJ) : $stmt->fetch(PDO::FETCH_NUM);
        } catch (PDOException $e) {
            $this->log_db_errors($e->getMessage(), $query);
            return false;
        }
    }

    /**
     * Get single row with prepared statement - SECURE
     *
     * Example:
     * list($name, $email) = $db->prepare_get_row("SELECT name, email FROM users WHERE id = ?", [123]);
     */
    public function prepare_get_row(string $query, array $params = [], bool $object = false): mixed
    {
        $stmt = $this->prepare_execute($query, $params);
        if (!$stmt) return false;

        return $object ? $stmt->fetch(PDO::FETCH_OBJ) : $stmt->fetch(PDO::FETCH_NUM);
    }

    /**
     * Get multiple rows - RAW QUERY (not secure with user input!)
     * Use prepare_get_results() for user input
     */
    public function get_results(string $query, bool $object = false): mixed
    {
        self::$counter++;
        try {
            $stmt = $this->link->query($query);
            $fetchMode = $object ? PDO::FETCH_OBJ : PDO::FETCH_ASSOC;
            $results = $stmt->fetchAll($fetchMode);
            return empty($results) ? null : $results;
        } catch (PDOException $e) {
            $this->log_db_errors($e->getMessage(), $query);
            return false;
        }
    }

    /**
     * Get multiple rows with prepared statement - SECURE
     *
     * Example:
     * $users = $db->prepare_get_results("SELECT * FROM users WHERE status = ?", ['active']);
     * foreach($users as $user) {
     *     echo $user['name'];
     * }
     */
    public function prepare_get_results(string $query, array $params = [], bool $object = false): mixed
    {
        $stmt = $this->prepare_execute($query, $params);
        if (!$stmt) return false;

        $fetchMode = $object ? PDO::FETCH_OBJ : PDO::FETCH_ASSOC;
        $results = $stmt->fetchAll($fetchMode);
        return empty($results) ? null : $results;
    }

    /**
     * Insert data into table - SECURE (uses prepared statements)
     */
    public function insert(string $table, array $variables = []): bool
    {
        self::$counter++;

        if (empty($variables)) {
            return false;
        }

        $fields = array_keys($variables);
        $values = array_values($variables);

        $fieldList = '`' . implode('`, `', $fields) . '`';
        $placeholders = implode(', ', array_fill(0, count($fields), '?'));

        $sql = "INSERT INTO `{$table}` ({$fieldList}) VALUES ({$placeholders})";

        try {
            $stmt = $this->link->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            $this->log_db_errors($e->getMessage(), $sql);
            return false;
        }
    }

    /**
     * Insert safe - same as insert with PDO (prepared statements handle safety)
     */
    public function insert_safe(string $table, array $variables = []): bool
    {
        return $this->insert($table, $variables);
    }

    /**
     * Insert multiple records - SECURE (uses prepared statements)
     */
    public function insert_multi(string $table, array $columns = [], array $records = []): int|bool
    {
        self::$counter++;

        if (empty($columns) || empty($records)) {
            return false;
        }

        $number_columns = count($columns);
        $added = 0;

        $fieldList = '`' . implode('`, `', $columns) . '`';
        $placeholder = '(' . implode(', ', array_fill(0, $number_columns, '?')) . ')';

        $allValues = [];
        $placeholders = [];

        foreach ($records as $record) {
            if (count($record) == $number_columns) {
                $placeholders[] = $placeholder;
                $allValues = array_merge($allValues, array_values($record));
                $added++;
            }
        }

        if ($added === 0) {
            return false;
        }

        $sql = "INSERT INTO `{$table}` ({$fieldList}) VALUES " . implode(', ', $placeholders);

        try {
            $stmt = $this->link->prepare($sql);
            $stmt->execute($allValues);
            return $added;
        } catch (PDOException $e) {
            $this->log_db_errors($e->getMessage(), $sql);
            return false;
        }
    }

    /**
     * Update data in table - SECURE (uses prepared statements)
     */
    public function update(string $table, array $variables = [], array $where = [], string $freewhere = '', string $limit = ''): bool
    {
        self::$counter++;

        if (empty($variables)) {
            return false;
        }

        $updates = [];
        $values = [];

        foreach ($variables as $field => $value) {
            $updates[] = "`{$field}` = ?";
            $values[] = $value;
        }

        $sql = "UPDATE `{$table}` SET " . implode(', ', $updates);

        if (!empty($where)) {
            $clauses = [];
            foreach ($where as $field => $value) {
                $clauses[] = "`{$field}` = ?";
                $values[] = $value;
            }
            $sql .= ' WHERE ' . implode(' AND ', $clauses);
        }

        if (!empty($freewhere)) {
            $sql .= ' AND ' . $freewhere;
        }

        if (!empty($limit)) {
            $sql .= ' LIMIT ' . $limit;
        }

        try {
            $stmt = $this->link->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            $this->log_db_errors($e->getMessage(), $sql);
            return false;
        }
    }

    /**
     * Delete data from table - SECURE (uses prepared statements)
     */
    public function delete(string $table, array $where = [], string $limit = ''): bool
    {
        self::$counter++;

        if (empty($where)) {
            return false;
        }

        $clauses = [];
        $values = [];

        foreach ($where as $field => $value) {
            $clauses[] = "`{$field}` = ?";
            $values[] = $value;
        }

        $sql = "DELETE FROM `{$table}` WHERE " . implode(' AND ', $clauses);

        if (!empty($limit)) {
            $sql .= " LIMIT {$limit}";
        }

        try {
            $stmt = $this->link->prepare($sql);
            return $stmt->execute($values);
        } catch (PDOException $e) {
            $this->log_db_errors($e->getMessage(), $sql);
            return false;
        }
    }

    /**
     * Get last insert ID
     */
    public function lastid(): string
    {
        self::$counter++;
        return $this->link->lastInsertId();
    }

    /**
     * Get number of affected rows from last statement
     */
    public function affected(): int
    {
        return $this->link->query("SELECT ROW_COUNT()")->fetchColumn();
    }

    /**
     * Get number of fields in query
     */
    public function num_fields(string $query): int
    {
        self::$counter++;
        try {
            $stmt = $this->link->query($query);
            return $stmt->columnCount();
        } catch (PDOException $e) {
            return 0;
        }
    }

    /**
     * List fields from query
     */
    public function list_fields(string $query): array
    {
        self::$counter++;
        try {
            $stmt = $this->link->query($query);
            $fields = [];
            $columnCount = $stmt->columnCount();

            for ($i = 0; $i < $columnCount; $i++) {
                $fields[] = $stmt->getColumnMeta($i);
            }

            return $fields;
        } catch (PDOException $e) {
            return [];
        }
    }

    /**
     * Truncate tables
     */
    public function truncate(array $tables = []): int
    {
        if (empty($tables)) {
            return 0;
        }

        $truncated = 0;

        foreach ($tables as $table) {
            try {
                $this->link->exec("TRUNCATE TABLE `" . trim($table) . "`");
                $truncated++;
                self::$counter++;
            } catch (PDOException $e) {
                // Continue with next table
            }
        }

        return $truncated;
    }

    /**
     * Display variable for debugging
     */
    public function display($variable, bool $echo = true): ?string
    {
        $out = '';

        if (!is_array($variable)) {
            $out .= $variable;
        } else {
            $out .= '<pre>';
            $out .= print_r($variable, true);
            $out .= '</pre>';
        }

        if ($echo === true) {
            echo $out;
            return null;
        }

        return $out;
    }

    /**
     * Get total number of queries executed
     */
    public function total_queries(): int
    {
        return self::$counter;
    }

    /**
     * Singleton pattern
     */
    public static function getInstance(): database
    {
        if (self::$inst === null) {
            // Note: You'll need to pass DB credentials here or load from config
            global $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME;
            self::$inst = new self($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
        }
        return self::$inst;
    }

    /**
     * Disconnect from database
     */
    public function disconnect(): void
    {
        $this->link = null;
    }

    /**
     * Get PDO connection for advanced usage
     */
    public function getConnection(): ?PDO
    {
        return $this->link;
    }
}