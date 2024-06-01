<?php
define("db_host", "localhost");
define("db_user", "root");
define("db_pass", "");
define("db_name", "fraud_detection");

class db_connect {
    public $host = db_host;
    public $user = db_user;
    public $pass = db_pass;
    public $name = db_name;
    public $conn;
    public $error;

    public function __construct() {
        $this->connect();
    }

    public function connect() {
        $this->conn = new mysqli($this->host, $this->user, $this->pass, $this->name);

        if ($this->conn->connect_error) {
            $this->error = "Fatal Error: Can't connect to database " . $this->conn->connect_error;
            return false;
        }
    }
}

// Create an instance of db_connect
$db = new db_connect();

// Check for connection errors
if ($db->error) {
    echo "Error: " . $db->error;
    // Handle the error as needed
} else {
    // The database connection is successful, and you can use $db->conn for queries
}
?>
