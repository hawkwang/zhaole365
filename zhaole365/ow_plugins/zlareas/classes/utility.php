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
    
    public function getAnotherAddressInfo($formated_address, $province, $city, $district, $longitude, $latitude)
    {
    	$details = array();
    	$details["formated_address"] = $formated_address;
    	$details["province"] = $province;
    	$details["city"] = $city;
    	$details["district"] = $district;
    	$details["longitude"] = $longitude;
    	$details["latitude"] = $latitude;
    
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
    
    // fixme, should move to zlbase.utility class
    public function get_happen_time_string($timestamp, $format = 'Y.m.d H:i') {
    	$datetime = new DateTime ();
    	$datetime->setTimestamp ( $timestamp );
    	$strDateTime = $datetime->format ( $format );
    	return $strDateTime;
    }
    
    public function getTimeInfo($timestamp)
    {
    	$weekday = date('N', $timestamp);
    	switch ($weekday) {
    		case 1:
    			$weekday = "周一";
    			break;
    		case 2:
    			$weekday = "周二";
    			break;
    		case 3:
    			$weekday = "周三";
    			break;
    		case 4:
    			$weekday = "周四";
    			break;
    		case 5:
    			$weekday = "周五";
    			break;
    		case 6:
    			$weekday = "周六";
    			break;
    		case 7:
    			$weekday = "周日";
    			break;
    	}
    	
    	$timestring = $this->get_happen_time_string($timestamp);
    	$time_details = explode(' ', $timestring);
    	
    	$date = $time_details[0];
    	$time = $time_details[1];
    	
    	return array(
    			'date' => $date,
    			'time' => $time,
    			'weekday' => $weekday
    	);
    	 
    }

    function url_exists($url) {
    	try{
    		$size = getimagesize($url);
    		$value = $size[0]*$size[1];
    		if($value > 0)
    			return true;
    	}
    	catch(Exception $ex)
    	{
    		return false;
    	}
    	
    	return false;
    }

}