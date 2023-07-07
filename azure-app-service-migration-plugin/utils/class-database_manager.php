<?php
// TO DO: remove debug echos and add comments
class AASM_Database_Manager {
    private $host;
    private $username;
    private $password;
    
    public function __construct($host, $username, $password) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
    }
    
    public function connect() {
        $conn = new mysqli($this->host, $this->username, $this->password);
        
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }
        return $conn;
    }
    
    public function create_database($databaseName) {
        $conn = $this->connect();
        $sql = "CREATE DATABASE $databaseName";
        
        if ($conn->query($sql) === TRUE) {
            echo "Database created successfully";
        } else {
            echo "Error creating database: " . $conn->error;
        }
        
        $conn->close();
    }
    
    public function drop_database($databaseName) {
        $conn = $this->connect();
        $sql = "DROP DATABASE $databaseName";
        
        if ($conn->query($sql) === TRUE) {
            echo "Database dropped successfully";
        } else {
            echo "Error dropping database: " . $conn->error;
        }
        
        $conn->close();
    }
    
    public function rename_database($oldDatabaseName, $newDatabaseName) {
        $conn = $this->connect();
        $sql = "ALTER DATABASE $oldDatabaseName RENAME TO $newDatabaseName";
        
        if ($conn->query($sql) === TRUE) {
            echo "Database renamed successfully";
        } else {
            echo "Error renaming database: " . $conn->error;
        }
        
        $conn->close();
    }

    public function database_exists($dbname) {
        $conn = $this->connect();
        $sql = "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'";
        $result = $conn->query($sql);
        $databaseExists = ($result->num_rows > 0);
        $conn->close();

        return $databaseExists;
    }

    public function import_sql_file($databaseName, $sqlFile) {
        if (!file_exists($sqlFile)) {
            echo "SQL file does not exist\n";
            return;
        }

        $conn = $this->connect();
        $sql = file_get_contents($sqlFile);
        $conn->select_db($databaseName);
        if ($conn->multi_query($sql) === TRUE) {
            echo "SQL file imported successfully\n";
        } else {
            echo "Error importing SQL file: " . $this->connection->error;
        }
    }

    public function run_custom_sql($databaseName, $sql) {
        $conn = $this->connect();
        $conn->select_db($databaseName);

        // Execute each SQL statement in the string
        $sqlStatements = explode(';', $sql);
        foreach ($sqlStatements as $statement) {
            $trimmedStatement = trim($statement);
            if (!empty($trimmedStatement)) {
                if ($conn->query($trimmedStatement) === TRUE) {
                    echo "SQL statement executed successfully: $trimmedStatement\n";
                } else {
                    echo "Error executing SQL statement: $conn->error\n";
                }
            }
        }

        $conn->close();
    }
}