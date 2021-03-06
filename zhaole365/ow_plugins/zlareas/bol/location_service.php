<?php

/**
 * Copyright (c) 2013, Podyachev Evgeny <joker.OW2@gmail.com>
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */
/**
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_plugins.location_tag.bol
 * @since 1.0
 */

final class ZLAREAS_BOL_LocationService
{
    const JQUERY_LOAD_PRIORITY = 100000002;
    
    const SESSION_VAR_ENTITY_LIST = 'googlelocation_userlist_session_var';
    const ENTITY_TYPE_STATUS_UPDATE = 'user-status';


    private $locationDao;
    private static $classInstance;

    private function __construct()
    {
        $this->locationDao = ZLAREAS_BOL_LocationDao::getInstance();
    }

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function save( ZLAREAS_BOL_Location $dto )
    {
        $this->locationDao->save($dto);
    }

    public function addLocation( $address, $longitude, $latitude, $areacode, $description )
    {
        if ( empty($latitude) || empty($longitude) || empty($areacode) || empty($address)  || empty($description) )
        {
            return;
        }

        $dto = new ZLAREAS_BOL_Location();
        $dto->address = $address;
        $dto->longitude = $longitude;
        $dto->latitude = $latitude;
        $dto->areacode = $areacode;
        $dto->address = $address;
        $dto->description = $description;
        
        $this->save($dto);
    }
    
    public function addDetailedLocation( $formated_address, $province, $city, $district, $longitude, $latitude, $address )
    {
    	$area = ZLAREAS_BOL_Service::getInstance()->getAreaByDetailedinfo($province,$city, $district);
    	$areacode = '0';
    	if($area==null)
    	{
    		OW::getFeedback()->error("找不到相应的区域代码！将自动创建新的区域代码！");
    		$areacode = uniqid();
    		ZLAREAS_BOL_Service::getInstance()->addArea($areacode, $province, $city, $district);
    	}
    	else 
    		$areacode = $area->areacode;
    	
    	$this->addLocation($formated_address, $longitude, $latitude, $areacode, $address);
    }
    
    public function getLocationList()
    {
    	return ZLAREAS_BOL_LocationDao::getInstance()->findAll();
    }
    
    public function getAllLocations( )
    {
        return $this->locationDao->getAllLocations();
    }
    
    public function findLocationByAddress( $address )
    {
    	return $this->locationDao->findLocationByAddress( $address );
    }
    
    public function findLocationByLongitudeAndLatitude($longitude,$latitude)
    {
    	return $this->locationDao->findLocationByLongitudeAndLatitude($longitude,$latitude);
    }

    public function deleteById( $id )
    {
    	//return $this->locationDao->deleteByIdList( array($id) );
        //return $this->locationDao->deleteById( $id );
    	$id = (int) $id;
    	if ( $id > 0 )
    	{
    		$this->locationDao->deleteById($id);
    	}
    	
    }
    
    public function findById( $id )
    {
        return $this->locationDao->findById($id);
    }
}