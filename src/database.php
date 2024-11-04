<?php

class conn{
    private $pdo;
    private $response;

    /**
     * Constructor to initialize the database connection using XML configuration.
     *
     */
    public function __construct($connName){
        $xmlFile = __DIR__ . '/connections.xml';

        if (!file_exists($xmlFile)){
            $this->response["response_code"] = -1;
            $this->response["response_message"] = "XML configuration not found";
            return $this->response;
        }

        $xml = simplexml_load_file($xmlFile);

        $connection = null;

        // Search for the connection with the matching name
        foreach($xml->connection as $conn){
            if($conn['name'] == $connName){
                $connection = $conn;
                break;
            }
        }

        if(!$connection){
            $this->response["response_code"] = -1;
            $this->response["response_message"] = "Connection named '$connName' not found in XML file.";
            return $this->response;
        }

        // Retrieve connection parameters
        $username = isset($connection->username) ? $connection->username : NULL;
        $password = isset($connection->password) ? $connection->password : NULL;
        $host     = isset($connection->host) ? $connection->host : NULL;
        $dbname   = isset($connection->dbname) ? $connection->dbname : NULL;
        $port     = isset($connection->port) ? $connection->port : NULL;

        if(!$username || !$password || !$host || !$dbname || !$port ){
            $this->response["response_code"] = -1;
            $this->response["response_message"] = "Ensure that all necessary parameters (username, password, host, dbname, port) exist within the xml configuration file";
            return $this->response;
        }

        $dsn = "mysql:host=$host;dbname=$dbname;port=$port;charset=utf8mb4";

        try {
            $this->pdo = new PDO($dsn, $username, $password);
            // Set PDO error mode to exception
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->response["response_code"] = 0;
        } catch (PDOException $e) {
            $this->response["response_code"] = $e->getCode();
            $this->response["response_message"] = $e->getMessage();
            return $this->response;
        }
    }

    /**
     * Executes an SQL query with optional parameters.
     *
     */
    public function execute($query, $params = []){
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);
            $this->response = 0; // Success code
            return $stmt;
        }catch(PDOException $e){
            $this->response["response_code"] = $e->getCode();
            $this->response["response_message"] = "Query execution failed " . $e->getMessage();
            return $this->response;
        }
    }

    /**
     * Fetches all rows from the executed query.
     *
     */
    public function fetchAll($query, $params = []){
        $stmt = $this->execute($query, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetches a single row from the executed query.
     *
     */
    public function fetch($query, $params = []){
        $stmt = $this->execute($query, $params);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Gets the response code from the last operation.
     *
     */
    public function getResponseCode(){
        return $this->response["response_code"];
    }

    /**
     * Gets the full response.
     *
     */
    public function getFullResponse(){
        return $this->response;
    }

    /**
     * Destructor to close the PDO connection.
     */
    public function __destruct(){
        $this->pdo = null;
    }
}

?>




