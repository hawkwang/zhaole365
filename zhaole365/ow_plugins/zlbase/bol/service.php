<?php

class ZLBASE_BOL_Service
{
	const JQUERY_LOAD_PRIORITY = 100000002;
	
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

    public function saveProperty( $entityType, $entityId, $key, $value )
    {
        $property = new ZLBASE_BOL_BaseProperty();
        $property->entityType = $entityType;
        $property->entityId = $entityId;
        $property->key = $key;
        $property->value = $value;
        ZLBASE_BOL_BasePropertyDao::getInstance()->save($property);
    }

    public function findProperty($entityType, $entityId, $key)
    {
    	return ZLBASE_BOL_BasePropertyDao::getInstance()->findProperty($entityType, $entityId, $key);
    }

    public function findProperties($entityType, $entityId)
    {
    	return ZLBASE_BOL_BasePropertyDao::getInstance()->findProperties($entityType, $entityId );
    }
    
    public function getValue( $entityType, $entityId, $key )
    {
		return ZLBASE_BOL_BasePropertyDao::getInstance()->getValue($entityType, $entityId, $key );
    }
    
    public function deleteAllProperties($entityType, $entityId)
    {
    	ZLBASE_BOL_BasePropertyDao::getInstance()->deleteAllProperties($entityType, $entityId);
    }
    
}