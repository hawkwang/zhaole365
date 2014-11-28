<?php

class ZLBASE_CLASS_Logger
{

	private $log_file, $fp;
	
    private static $classInstance;


    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
    	$this->log_file = '/tmp/mylog.txt';
    	$this->fp = fopen($this->log_file, 'a');
    	
    }
    
    public function log($message) {

    	// define script name
    	$script_name = pathinfo($_SERVER['PHP_SELF'], PATHINFO_FILENAME);
    	// define current time and suppress E_WARNING if using the system TZ settings
    	// (don't forget to set the INI setting date.timezone)
    	$time = @date('[d/M/Y:H:i:s]');
    			// write current time, script name and message to the log file
    	fwrite($this->fp, "$time ($script_name) $message" . PHP_EOL);
    }
    
    // close log file (it's always a good idea to close a file when you're done with it)
    public function lclose() {
    	fclose($this->fp);
    }

}