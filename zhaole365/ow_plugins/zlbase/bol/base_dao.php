<?php

class ZLBASE_BOL_BasePropertyDao extends OW_BaseDao
{
	const ID = 'id';
	const ENTITYTYPE = 'entityType';
	const ENTITYID = 'entityId';
	const KEY = 'key';		
	const VALUE = 'value';

    protected function __construct()
    {
        parent::__construct();
    }
    
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
        return 'ZLBASE_BOL_BaseProperty';
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'zlbase_base_property';
    }
    
    public function getValueByKey( $entityType, $entityId, $key )
    {
    	if ( empty($key) )
    	{
    		return null;
    	}
    
    	$example = new OW_Example();
    	$example->andFieldEqual(self::ENTITYTYPE, $entityType);
    	$example->andFieldEqual(self::ENTITYID, $entityId);
    	$example->andFieldEqual(self::KEY, $key);
    	
    	$property = $this->findObjectByExample($example);
    	if($property!=null)
    		return $property->value;
    	
    	return null;
    }
}