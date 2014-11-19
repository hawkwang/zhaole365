<?php

class ZLSEARCHENGINE_BOL_Service
{
    private static $classInstance;
    
    private $searchengine_url = 'http://localhost:8983/solr';

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
    	$searchengine_url = Ow::getConfig()->getValue('zlsearchengine', 'searchengine_url');
    	
    	if ( !empty($searchengine_url) )
    	{
    		$this->searchengine_url = $searchengine_url;
    	}
    }
    
    public function getServiceUrl()
    {
    	return $this->searchengine_url;
    }

}