<?php

class ZLTAGS_BOL_TagDao extends OW_BaseDao
{
    const USER_ID = 'userId';
    const TAG = 'tag';
    const CREATE_STAMP = 'createStamp';

    
    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    protected function __construct()
    {
        parent::__construct();
    }

    public function getDtoClassName()
    {
        return 'ZLTAGS_BOL_Tag';
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'zltags_tag';
    }
    
    public function findByTag($tag)
    {
    	if ( empty($tag) || strlen($tag)==0)
    	{
    		return null;
    	}
    	
    	$example = new OW_Example();
    	$example->andFieldEqual(self::TAG, $tag);
    	return $this->findObjectByExample($example);
    }
    
}
