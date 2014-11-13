<?php

class ZLAREAS_CLASS_Utility
{

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

    }
    
    // 分析百度map得到的以||分割的地址详细信息，返回指称性数组
    public function getAddressInfo($addressinfo)
    {
    	$infos = explode("||", $addressinfo);
    	
    	$details = array();
    	$details["formated_address"] = $infos[0];
    	$details["province"] = $infos[1];
    	$details["city"] = $infos[2];
    	$details["district"] = $infos[3];
    	$details["longitude"] = $infos[4];
    	$details["latitude"] = $infos[5];
    	 
    	return $details;
    }
    
    // 判断$alladdressdescription中是否含有$description
    public function containOriginAddress($alladdressdescription, $description)
    {
    	$addresses = explode("||", $alladdressdescription);
    	if(in_array($description, $addresses))
    		return true;
    	else
    		return false;
    }


}