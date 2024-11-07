<?php
session_start();    
    include_once(__DIR__ . "/database.php");
    include_once(__DIR__ . "/encryption.php");
    
    class notes{
        private $db;
        private $response;
        private $log;
        private $defaultErrorMessage;
        private $userId;
        private $encryptionInstance;

        public function __construct(){
            //Since this is a demo, alot of these would change... (I wouldnt hard code this...)
            if(!isset($_SESSION["user_id"])){
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
            $this->encryptionInstance = new encryption();
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
            if(!hash_equals($_SESSION["csrf_token"], $token)){
                $this->response["response_code"] = -1; 
                $this->response["message"] = "Unable to verify Csrf token";
                return $this->response;
            }
        } 
       
        /*
        *  Validate User
        * */ 
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

            $rows = $this->db->fetch($sql);
            if(!$rows){
                $this->response["response_code"] = -1;
                $this->response["message"] = $this->defaultErrorMessage;
                $this->log->error("<" . __FUNCTION__ . "> in line: " . __LINE__ . " User: " . $_SESSION["user_name"] . " no longer appears active in the database");
                return $this->response;
            }
            
            $this->response["user_data"] = isset($rows) ? $rows : NULL;
            $this->userId = isset($rows["id"]) ? $rows["id"] : NULL ; 
            
            $this->log->activity("<" . __FUNCTION__ . "> in line: " . __LINE__ . " Verified user: " . $_SESSION["user_name"] . " is still an active user");
            return $this->response;
        }

        /*
        *   CRUDS for note taking     
        * */
        
        // Create
        public function addNote($params){
            $this->response["response_code"] = 0;
            $this->response["message"] = "Note Successfully added!";
            $this->log->activity("<" . __FUNCTION__ . "> " . $_SESSION["user_name"] . " is attempting to add new note");

            $csrfToken = isset($params["csrf_token"]) ? $params["csrf_token"] : NULL;

            $validateToken = $this->validateCsrfToken($csrfToken);
            if($validateToken["response_code"] != 0){
                $this->response["response_code"] = -1;
                $this->response["message"] = "Invalid CSRF token.";
                return $this->response;
            }

            if($this->db->getResponseCode() != 0){
                $this->log->error("<" . __FUNCTION__ . "> Unable to connect to database, error: " . json_encode($this->db->getFullResponse()));
                $this->response["response_code"] = -1;
                $this->response["message"] = $this->defaultErrorMessage;
                return $this->response;
            }

            $verifyUser = $this->validateUser();
            if($verifyUser["response_code"] != 0){
                $this->log->error("<" . __FUNCTION__ . "> User validation failed.");
                $this->response["response_code"] = -1;
                $this->response["message"] = $this->defaultErrorMessage;
                return $this->response;
            }

            $noteTitle = isset($params["note_title"]) ? trim($params["note_title"]) : null;
            $description = isset($params["description"]) ? trim($params["description"]) : null;

            if(!$noteTitle || !$description){
                $this->response["response_code"] = -1;
                $this->response["message"] = "Note title and description cannot be empty.";
                return $this->response;
            }

            $sql = "INSERT INTO notes (user_id, note_title, description, created_by) VALUES (" . $this->userId . ", '" . $noteTitle . "', '" . $description . "', " . $this->userId . ")";
            $sqlResponse = $this->db->execute($sql);
            if($this->db->getResponseCode() != 0){
                $this->response["response_code"] = -1;
                $this->response["message"] = $this->defaultErrorMessage;
                $this->log->error("<" . __FUNCTION__ . "> in line: " . __LINE__ . " something went wrong while executing the query: \n" . $sql . "\n response: " . json_encode($this->db->getFullResponse));
                return $this->response;
            }

            $this->response["notes"] = $this->getAllNotes();
            $this->log->activity("<" . __FUNCTION__ . "> " . $_SESSION["user_name"] . " has successfully added note");
            return $this->response;
        }
            
        /**
         *  Read All Notes
        */
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
            
            $sql = "SELECT * FROM notes WHERE created_by = " . $this->userId . " AND status = 1 ORDER BY ID ASC";
            $sqlResponse = $this->db->execute($sql);
            if($this->db->getResponseCode() != 0){
                $this->response["response_code"] = -1;
                $this->response["message"] = $this->defaultErrorMessage;
                $this->log->error("<" . __FUNCTION__ . "> in line: " . __LINE__ . " something went wrong while executing the query: \n" . $sql . "\n response: " . json_encode($this->db->getFullResponse));
                return $this->response;
            }

            $rows = $this->db->fetchAll($sql);
            $this->response["note_data"] = isset($rows) ? $rows : NULL;
           
            $this->log->activity("<" . __FUNCTION__ . "> in line: " . __LINE__ . " " . $_SESSION["user_name"]  . " has retrieved all notes"); 
            return $this->response;
        }

        /**
         *  Read Single Notes
        */
        public function getSingleNote($params){
            $this->log->activity("<" . __FUNCTION__ . "> in line: " . __LINE__ . " " . $_SESSION["user_name"] . " is attempting to a single notes");
           
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

            $noteId = isset($params["note_id"]) ? $this->encryptionInstance->decrypt($params["note_id"]) : NULL ;
            
            $sql = "SELECT * FROM notes WHERE created_by = " . $this->userId . " AND status = 1 AND id = " . $noteId["decrypted_data"];
            $sqlResponse = $this->db->execute($sql);
            if($this->db->getResponseCode() != 0){
                $this->response["response_code"] = -1;
                $this->response["message"] = $this->defaultErrorMessage;
                $this->log->error("<" . __FUNCTION__ . "> in line: " . __LINE__ . " something went wrong while executing the query: \n" . $sql . "\n response: " . json_encode($this->db->getFullResponse));
                return $this->response;
            }

            $rows = $this->db->fetch($sql);
            $this->response["note_data"] = isset($rows) ? $rows : NULL;
           
            $this->log->activity("<" . __FUNCTION__ . "> in line: " . __LINE__ . " " . $_SESSION["user_name"]  . " has retrieved a single note"); 
            return $this->response;
        }
        
        /**
         *  Update Notes
        */
        public function updateNote($params){
            $this->response["response_code"] = 0;
            $this->response["message"] = "Clear";
            $this->log->activity("<" . __FUNCTION__ . "> " . $_SESSION["user_name"] . " is attempting to update a note");

            $csrfToken = isset($params["csrf_token"]) ? $params["csrf_token"] : NULL;

            $validateToken = $this->validateCsrfToken($csrfToken);
            if($validateToken["response_code"] != 0){
                $this->log->error("<" . __FUNCTION__ . "> Unable to validate CSRF token");
                $this->response["response_code"] = -1;
                $this->response["message"] = "Invalid CSRF token.";
                return $this->response;
            }

            if($this->db->getResponseCode() != 0){
                $this->log->error("<" . __FUNCTION__ . "> Unable to connect to database, error: " . json_encode($this->db->getFullResponse()));
                $this->response["response_code"] = -1;
                $this->response["message"] = $this->defaultErrorMessage;
                return $this->response;
            }

            $verifyUser = $this->validateUser();
            if($verifyUser["response_code"] != 0){
                $this->log->error("<" . __FUNCTION__ . "> User validation failed.");
                $this->response["response_code"] = -1;
                $this->response["message"] = $this->defaultErrorMessage;
                return $this->response;
            }

            $noteId = isset($params["id"]) ? $this->encryptionInstance->decrypt($params["id"]) : null;
            $noteTitle = isset($params["note_title"]) ? trim($params["note_title"]) : null;
            $description = isset($params["description"]) ? trim($params["description"]) : null;
            if(!$noteId || !$noteTitle || !$description){
                $this->response["response_code"] = -1;
                $this->response["message"] = "Note ID, title, and description cannot be empty.";
                return $this->response;
            }

            $sql = "SELECT * FROM notes WHERE id = " . $noteId["decrypted_data"] . " AND user_id = " . $this->userId . " AND status = 1";
            $sqlResponse = $this->db->execute($sql);
            if($this->db->getResponseCode() != 0){
                $this->response["response_code"] = -1;
                $this->response["message"] = $this->defaultErrorMessage;
                $this->log->error("<" . __FUNCTION__ . "> in line: " . __LINE__ . " something went wrong while executing the query: \n" . $sql . "\n response: " . json_encode($this->db->getFullResponse));
                return $this->response;
            }

            $row = $this->db->fetch($sql);
            if(empty($row)){
                $this->response["response_code"] = -1;
                $this->response["message"] = "Note not found or you don't have permission to edit this note.";
                return $this->response;
            }

            $sql = "UPDATE notes SET note_title = '" . $noteTitle . "', description = '" . $description . "', updated_by = " . $this->userId . " WHERE id = " . $noteId["decrypted_data"] . " AND user_id = " . $this->userId . "";

            $sql = $this->db->execute($sql);
            if($this->db->getResponseCode() != 0){
                $this->response["response_code"] = -1;
                $this->response["message"] = $this->defaultErrorMessage;
                $this->log->error("<" . __FUNCTION__ . "> Failed to update note, error: " . json_encode($this->db->getFullResponse()));
                return $this->response;
            }

            $this->log->activity("<" . __FUNCTION__ . "> " . $_SESSION["user_name"] . " has successfully updated note with id:" . $noteId["decrypted_data"]);
            return $this->response;
        }
 
        /**
         *  Delete Note
         */
        public function deleteNote($params){
            $this->response["response_code"] = 0;
            $this->response["message"] = "Clear";
            $this->log->activity("<" . __FUNCTION__ . "> " . $_SESSION["user_name"] . " is attempting to delete note");

            $validateToken = $this->validateCsrfToken($params["csrf_token"]);
            if($validateToken["response_code"] != 0){
                $this->response["response_code"] = -1;
                $this->response["message"] = "Invalid CSRF token.";
                return $this->response;
            }

            if($this->db->getResponseCode() != 0){
                $this->log->error("<" . __FUNCTION__ . "> Unable to connect to database, error: " . json_encode($this->db->getFullResponse()));
                $this->response["response_code"] = -1;
                $this->response["message"] = $this->defaultErrorMessage;
                return $this->response;
            }

            $verifyUser = $this->validateUser();
            if($verifyUser["response_code"] != 0){
                $this->log->error("<" . __FUNCTION__ . "> User validation failed.");
                $this->response["response_code"] = -1;
                $this->response["message"] = $this->defaultErrorMessage;
                return $this->response;
            }

            $noteId = isset($params["id"]) ? $this->encryptionInstance->decrypt($params["id"]) : null;

            if(!$noteId){
                $this->response["response_code"] = -1;
                $this->response["message"] = "Note ID cannot be empty.";
                return $this->response;
            }

            $sql = "SELECT * FROM notes WHERE id = " . $noteId["decrypted_data"] . " AND user_id = " . $this->userId . " AND status = 1";
            $sqlResponse = $this->db->execute($sql);
            if($this->db->getResponseCode() != 0){
                $this->response["response_code"] = -1;
                $this->response["message"] = $this->defaultErrorMessage;
                $this->log->error("<" . __FUNCTION__ . "> in line: " . __LINE__ . " something went wrong while executing the query: \n" . $sql . "\n response: " . json_encode($this->db->getFullResponse));
                return $this->response;
            }

            $row = $this->db->fetchAll($sql);

            if(!$row){
                $this->response["response_code"] = -1;
                $this->response["message"] = "Note not found or you don't have permission to delete this note.";
                return $this->response;
            }

            $sql = "UPDATE notes SET status = 0, updated_by = " . $this->userId . " WHERE id = " . $noteId["decrypted_data"] . " AND user_id = " . $this->userId;

            $stmt = $this->db->execute($sql);
            if($this->db->getResponseCode() != 0){
                $this->response["response_code"] = -1;
                $this->response["message"] = $this->defaultErrorMessage;
                $this->log->error("<" . __FUNCTION__ . "> Failed to delete note, error: " . json_encode($this->db->getFullResponse()));
                return $this->response;
            }

            $this->log->activity("<" . __FUNCTION__ . "> " . $_SESSION["user_name"] . " has successfully deleted note with id:" . $noteId["decrypted_data"]);
            return $this->response;
        }
    }



?>
