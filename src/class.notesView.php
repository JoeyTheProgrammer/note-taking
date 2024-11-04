<?php
    include_once(__DIR__ . "/class.notes.php");
    include_once(__DIR__ . "/logger.php");

    class notesView{
	    private $noteInstance;
        private $log;

        public function __construct(){
	        $this->noteInstance = new notes();
            $this->log = new log("NOTE_VIEW");
        }
        
    	
        /*
        *   Gets inital home page 
        *
        * */
	    public function getHomeScreen(){    
	        $response["response_code"] = 0;
            $response["message"] = "Clear";
            $defaultErrorMessage = "Oops, something went wrong, kindly try again or contact your system administrator for assistance";
            
            $getNotes = $this->noteInstance->getAllNotes();
            if($getNotes["response_code"] != 0){
                $this->log->activity("<" . __FUNCTION__ . "> in line: " . __LINE__ . " Something went wrong while attempting to get all notes, response: \n" . json_encode($getNotes));
                $response["response_code"] = -1;
                $response["message"] = $defaultErrorMessage;
                return $response;
            }
	        
	        return $response;
	    }
    	
    }



?>
