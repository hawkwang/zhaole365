<?php

class ZLGROUPS_BOL_GroupLocationDao extends OW_BaseDao
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
        return 'ZLGROUPS_BOL_GroupLocation';
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'zlgroups_group_location';
    }

    //获得所有指定乐群的地址信息
    public function findByGroupId( $groupId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);

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
    public function findByGroupIdAndLocation( $groupId, $location )
    {
    	$example = new OW_Example();
    	$example->andFieldEqual('groupId', $groupId);
    	$example->andFieldEqual('location', $location);
    
    	return $this->findListByExample($example);
    }

    //获得指定乐群ID和地址ID的GroupLocation对象
    public function findGroupLocation( $groupId, $locationId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);
        $example->andFieldEqual('locationId', $locationId);

        return $this->findObjectByExample($example);
    }

    //删除乐群地址信息
    public function deleteByGroupId( $groupId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);

        return $this->deleteByExample($example);
    }

}