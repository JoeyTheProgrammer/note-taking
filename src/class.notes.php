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
            if(!hash_equals($_SESSION["csrf_token"], $token)){
                $this->response["response_code"] = -1; 
                $this->response["message"] = "Unable to connect to database";
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
            $this->response["message"] = "Clear";
            $this->log->activity("<" . __FUNCTION__ . "> " . $_SESSION["user_name"] . " is attempting to add new note");

            $validateToken = $this->validateCsrfToken($params['csrf_token']);
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

            $noteTitle = isset($params['note_title']) ? trim($params['note_title']) : null;
            $description = isset($params['description']) ? trim($params['description']) : null;

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

            $this->log->activity("<" . __FUNCTION__ . "> " . $_SESSION["user_name"] . " has successfully added note");
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
            
            $sql = "SELECT * FROM notes WHERE created_by = " . $this->userId . " AND status = 1";
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
        
        public function updateNote($params){
            $this->response["response_code"] = 0;
            $this->response["message"] = "Clear";
            $this->log->activity("<" . __FUNCTION__ . "> " . $_SESSION["user_name"] . " is attempting to update a note");

            $validateToken = $this->validateCsrfToken($params['csrf_token']);
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

            $noteId = isset($params['id']) ? intval($params['id']) : null;
            $noteTitle = isset($params['note_title']) ? trim($params['note_title']) : null;
            $description = isset($params['description']) ? trim($params['description']) : null;

            if(!$noteId || !$noteTitle || !$description){
                $this->response["response_code"] = -1;
                $this->response["message"] = "Note ID, title, and description cannot be empty.";
                return $this->response;
            }

            $sql = "SELECT * FROM notes WHERE id = " . $noteId . " AND user_id = " . $this->userId . " AND status = 1";
            $sqlResponse = $this->db->execute($sql);
            if($this->db->getResponseCode() != 0){
                $this->response["response_code"] = -1;
                $this->response["message"] = $this->defaultErrorMessage;
                $this->log->error("<" . __FUNCTION__ . "> in line: " . __LINE__ . " something went wrong while executing the query: \n" . $sql . "\n response: " . json_encode($this->db->getFullResponse));
                return $this->response;
            }

            $rows = $this->db->fetchAll($sql);

            if(!$noteExists){
                $this->response["response_code"] = -1;
                $this->response["message"] = "Note not found or you don't have permission to edit this note.";
                return $this->response;
            }

            $sql = "UPDATE notes SET note_title = " . $noteTitle . ", description = " . $description . ", updated_by = " . $this->userId . " WHERE id = " . $noteId . " AND user_id = " . $this->userId . "";

            $sql = $this->db->execute($sql);
            if($this->db->getResponseCode() != 0){
                $this->response["response_code"] = -1;
                $this->response["message"] = $this->defaultErrorMessage;
                $this->log->error("<" . __FUNCTION__ . "> Failed to update note, error: " . json_encode($this->db->getFullResponse()));
                return $this->response;
            }

            $this->log->activity("<" . __FUNCTION__ . "> " . $_SESSION["user_name"] . " has successfully updated note with id: $noteId");
            return $this->response;
        }
 
        // Delete
        public function deleteNote($params){
            $this->response["response_code"] = 0;
            $this->response["message"] = "Clear";
            $this->log->activity("<" . __FUNCTION__ . "> " . $_SESSION["user_name"] . " is attempting to delete note");

            $validateToken = $this->validateCsrfToken($params['csrf_token']);
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

            $noteId = isset($params['id']) ? intval($params['id']) : null;

            if(!$noteId){
                $this->response["response_code"] = -1;
                $this->response["message"] = "Note ID cannot be empty.";
                return $this->response;
            }

            $sql = "SELECT * FROM notes WHERE id = " . $noteId . " AND user_id = " . $this->userId . " AND status = 1";
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

            $sql = "UPDATE notes SET status = 0, updated_by = " . $this->userId . " WHERE id = " . $noteId . " AND user_id = " . $this->user_id;

            $stmt = $this->db->execute($sql);
            if($this->db->getResponseCode() != 0){
                $this->response["response_code"] = -1;
                $this->response["message"] = $this->defaultErrorMessage;
                $this->log->error("<" . __FUNCTION__ . "> Failed to delete note, error: " . json_encode($this->db->getFullResponse()));
                return $this->response;
            }

            $this->log->activity("<" . __FUNCTION__ . "> " . $_SESSION["user_name"] . " has successfully deleted note with id: $noteId");
            return $this->response;
        }
        

        //encrypt notes
        private function encryptNoteData($plaintext, $key) {
            $cipher = "aes-256-cbc";
            $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length($cipher));

            // Encrypt the plaintext
            $ciphertext = openssl_encrypt($plaintext, $cipher, $key, 0, $iv);

            // Encode the encrypted data and IV together for storage
            return base64_encode($iv . $ciphertext);
        }
        
        //Decrypt Notes
        private function decryptNoteData($encryptedData, $key) {
            $cipher = "aes-256-cbc";

            // Decode from base64 and extract the IV and ciphertext
            $decodedData = base64_decode($encryptedData);
            $ivLength = openssl_cipher_iv_length($cipher);
            $iv = substr($decodedData, 0, $ivLength);
            $ciphertext = substr($decodedData, $ivLength);

            // Decrypt the ciphertext
            return openssl_decrypt($ciphertext, $cipher, $key, 0, $iv);
        }


        
    }



?>
