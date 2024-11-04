<?php 

    /*
     *    LOGGER CLASS TO LOG EVENTS
     *    THIS LOGGER CLASS IS SUPPOSED WRITE TO A UNIVERSAL LOG PATH, BUT FOR DEMO PURPOSES, IT IS USED IN THIS NOTE TAKING DEMO
     *    
     *    AUTHOR: @ACOWO
     * */

    class log{
    	private $log_file;
	private $log_name;

	/*
	 *    This can also be modified for specific paths as its intended use
	 * */
	public function __construct($logName){
	    $this->log_file = "./logs/data-logs-" . date("d-m-Y") . ".log";
 	    $this->log_name = $logName;
	}

	/*
	 *    The below functions can be extended functionality (In addition to logging, this can also be used to send emails/SMS' during critical errors)
	 *
	 * */
	public function activity($msg){
	    error_log("\n[". date("d-m-Y H;i:s") . "]" . $this->log_name . " (ACTIVITY) - " . $msg, 3, $this->log_file);
	}

	public function error($msg){
	    error_log("\n[". date("d-m-Y H;i:s") . "]" . $this->log_name . " (ERROR) - " . $msg, 3, $this->log_file);
	}

	public function warning($msg){
	    error_log("\n[". date("d-m-Y H;i:s") . "]" . $this->log_name . " (WARNING) - " . $msg, 3, $this->log_file);
	}

    }

?>
