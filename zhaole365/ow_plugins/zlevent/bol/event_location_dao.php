<?php

class ZLEVENT_BOL_EventLocationDao extends OW_BaseDao
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

    public function getDtoClassName()
    {
        return 'ZLEVENT_BOL_EventLocation';
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'zlevent_event_location';
    }

    //获得所有指定活动的地址信息
    public function findByEventId( $eventId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('eventId', $eventId);

        return $this->findObjectByExample($example);
    }
    
    //获得所有指定地址的信息
    public function findByLocationId( $locationId )
    {
    	$example = new OW_Example();
    	$example->andFieldEqual('locationId', $locationId);
    
    	return $this->findListByExample($example);
    }
    
    //获得所有指定地址的信息
    public function findByEventIdAndLocation( $eventId, $location )
    {
    	$example = new OW_Example();
    	$example->andFieldEqual('eventId', $eventId);
    	$example->andFieldEqual('location', $location);
    
    	return $this->findListByExample($example);
    }

    //获得指定活动ID和地址ID的EventLocation对象
    public function findEventLocation( $eventId, $locationId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('eventId', $eventId);
        $example->andFieldEqual('locationId', $locationId);

        return $this->findObjectByExample($example);
    }

    //删除活动地址信息
    public function deleteByEventId( $eventId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('eventId', $eventId);

        return $this->deleteByExample($example);
    }

}