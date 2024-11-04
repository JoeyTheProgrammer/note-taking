<?php
session_start();    
    include_once(__DIR__ . "/database.php");
    class notes{
        private $db;
        private $response;
        private $log;
        private $defaultErrorMessage;
        private $userId;

        public function __construct(){
            //Since this is a demo, alot of these would change... (I wouldnt hard code this...)
            if(!isset($_SESSION['user_id'])){
                $_SESSION["user_name"] = "acowo";
                $_SESSION["user_id"] = uniqid("user_acowo");
                $_SESSION["session_start_time"] = time();
                $_SESSION["csrf_token"] = bin2hex(random_bytes(32)); // Generate a CSRF token
            }else{
                $_SESSION["session_start_time"] = time(); // Extend session
            }
     
            $this->db = new conn("NOTE_APP");
            $this->response["response_code"] = 0;
            $this->response["message"] = "Clear";
            $this->log = new log("NOTE_APP");
            $this->defaultErrorMessage = "Oops, something went wrong, try again or contact your system admin.";
        }
       
        /* 
        *   Get CSRF token 
        * */
        public function getCsrfToken(){
            return $_SESSION["csrf_token"];
        }

        /* 
        *   Validate CSRF token
        * */
        public function validateCsrfToken($token){
            if (!hash_equals($_SESSION["csrf_token"], $token)) {
                throw new Exception("CSRF token validation failed.");
            }
        } 
        
        private function validateUser(){
            $this->response["response_code"] = 0;
            $this->response["message"] = "Clear";
            $this->log->activity("<" . __FUNCTION__ . "> in line: " . __LINE__ . " Attempting to verify user: " . $_SESSION["user_name"]);
            
            if($this->db->getResponseCode() != 0){
                $this->log->error("<" . __FUNCTION__ . "> in line: " . __LINE__ . " Unable to connect to database, error: " . json_encode($this->db->getFullResponse()));
                $this->response["response_code"] = -1;
                $this->response["message"] = "Unable to connect to database";
                return $this->response;
            }
            
            $sql = "SELECT * FROM users WHERE user_name = '" . trim($_SESSION["user_name"]) . "' AND status = 1";
            
            $sqlResponse = $this->db->execute($sql);
            if($this->db->getResponseCode() != 0){
                $this->response["response_code"] = -1;
                $this->response["message"] = $this->defaultErrorMessage;
                $this->log->error("<" . __FUNCTION__ . "> in line: " . __LINE__ . " something went wrong while executing the query: \n" . $sql . "\n response: " . json_encode($this->db->getFullResponse));
                return $this->response;
            }

            $rows = $this->db->fetchAll($sql);
            if(!$rows){
                $this->response["response_code"] = -1;
                $this->response["message"] = $this->defaultErrorMessage;
                $this->log->error("<" . __FUNCTION__ . "> in line: " . __LINE__ . " User: " . $_SESSION["user_name"] . " no longer appears active in the database");
                return $this->response;
            }
            $this->response["user_data"] = isset($rows) ? $rows : NULL;
            
            $this->log->activity("<" . __FUNCTION__ . "> in line: " . __LINE__ . " Verified user: " . $_SESSION["user_name"] . " is still an active user");
            return $this->response;
        }

        /*
        *   CRUDS for note taking     
        * */

        //Create
        public function addNote($params){
            $this->response["response_code"] = 0;
            $this->response["message"] = "Clear";
            $this->log->activity("<" . __FUNCTION__ . "> in line: " . __LINE__ . " " . $_SESSION["user_name"] . " is attempting to add new note");


            $this->log->activity("<" . __FUNCTION__ . "> in line: " . __LINE__ . " " . $_SESSION["user_name"] . " has successfully added note");
            return $this->response;
        }
            
        //Read
        public function getAllNotes(){
            $this->log->activity("<" . __FUNCTION__ . "> in line: " . __LINE__ . " " . $_SESSION["user_name"] . " is attempting to get all notes");
           
            if($this->db->getResponseCode() != 0){
                $this->log->error("<" . __FUNCTION__ . "> in line: " . __LINE__ . " Unable to connect to database, error: " . json_encode($this->db->getFullResponse()));
                $this->response["response_code"] = -1;
                $this->response["message"] = $this->defaultErrorMessage;
                return $this->response;
            }
            
            $verifyUser = $this->validateUser();
            if($verifyUser["response_code"] != 0){
                $this->log->error("<" . __FUNCTION__ . "> in line: " . __LINE__ . " Something went wrong while validating user, response: " . json_encode($verifyUser));
                $this->response["response_code"] = -1;
                $this->response["message"] = $this->defaultErrorMessage;
                return $this->response;
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
           
            $this->log->activity("<" . __FUNCTION__ . "> in line: " . __LINE__ . " " . $_SESSION["user_name"]  . " has retrieved all notes"); 
            return $this->response;
        }
        
        //Update
        public function updateNote($params){
            $this->response["response_code"] = 0;
            $this->response["message"] = "Clear";
            $this->log->activity("<" . __FUNCTION__ . "> in line: " . __LINE__ . " " . $_SESSION["user_name"] . " is attempting to update a note with id:");


            $this->log->activity("<" . __FUNCTION__ . "> in line: " . __LINE__ . " " . $_SESSION["user_name"] . " has successfully updated note with id:");
            return $this->response;
        }
        
        //Delete
        public function DeleteNote($params){
            $this->response["response_code"] = 0;
            $this->response["message"] = "Clear";
            $this->log->activity("<" . __FUNCTION__ . "> in line: " . __LINE__ . " " . $_SESSION["user_name"] . " is attempting to delete note with id:");


            $this->log->activity("<" . __FUNCTION__ . "> in line: " . __LINE__ . " " . $_SESSION["user_name"] . " is attempting to delete note with id:");
            return $this->response;
        }
        
    }



?>
