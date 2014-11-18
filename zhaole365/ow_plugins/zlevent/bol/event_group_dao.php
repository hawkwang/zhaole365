<?php

class ZLEVENT_BOL_EventGroupDao extends OW_BaseDao
{
	const EVENT_ID = 'eventId';
	const GROUP_ID = 'groupId';
	
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
        return 'ZLEVENT_BOL_EventGroup';
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'zlevent_event_group';
    }

    //获得所有指定活动的乐群信息
    public function findByEventId( $eventId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('eventId', $eventId);

        return $this->findObjectByExample($example);
    }
    
    //获得所有指定乐群的信息
    public function findByGroupId( $groupId )
    {
    	$example = new OW_Example();
    	$example->andFieldEqual('groupId', $groupId);
    
    	return $this->findListByExample($example);
    }
    
    //获得指定乐群的信息
    public function findByEventIdAndGroupId( $eventId, $groupId )
    {
    	$example = new OW_Example();
    	$example->andFieldEqual('eventId', $eventId);
    	$example->andFieldEqual('groupId', $groupId);
    
    	return $this->findObjectByExample($example);
    }

    //删除活动乐群信息
    public function deleteByEventId( $eventId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('eventId', $eventId);

        return $this->deleteByExample($example);
    }
    
    //删除活动乐群信息
    public function deleteByGroupId( $groupId )
    {
    	$example = new OW_Example();
    	$example->andFieldEqual('groupId', $groupId);
    
    	return $this->deleteByExample($example);
    }

}