<?php
    include_once(__DIR__ . "/database.php");
    class notes{
        private $db;
        private $response;
        private $log;
        private $defaultErrorMessage;

        public function __construct(){
            $this->db = new conn("NOTE_APP");
            $this->response["response_code"] = 0;
            $this->response["message"] = "Clear";
            $this->log = new log("NOTE_APP");
            $this->defaultErrorMessage = "Oops, something went wrong, try again or contact your system admin.";
        }
        
    	public function getAllNotes(){
	        $this->log->activity("<" . __FUNCTION__ . "> in line: " . __LINE__ . " attempting to get all notes");
           
            if($this->db->getResponseCode() != 0){
                $this->log->error("<" . __FUNCTION__ . "> in line: " . __LINE__ . " Unable to connect to database, error: " . json_encode($this->db->getFullResponse()));
            }
            
            $sql = "SELECT * FROM notes WHERE status = 1";
            
            $sqlResponse = $this->db->execute($sql);
            if($this->db->getResponseCode() != 0){
                $this->response["response_code"] = -1;
                $this->response["message"] = "";
                $this->log->error("<" . __FUNCTION__ . "> in line: " . __LINE__ . " something went wrong while executing the query: \n" . $sql . "\n response: " . json_encode($db->response));
                return $this->response;
            }

            $rows = $this->db->fetchAll($sql);
            $this->response["note_data"] = isset($rows) ? $rows : NULL;
            
            return $this->response;
	    }
    
    }



?>
