<?php

class ZLGHEADER_BOL_CoverDao extends OW_BaseDao
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
        return 'ZLGHEADER_BOL_Cover';
    }

    public function getTableName()
    {
        return OW_DB_PREFIX . 'zlgheader_cover';
    }

    public function findByGroupId( $groupId, $status = ZLGHEADER_BOL_Cover::STATUS_ACTIVE )
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);
        $example->andFieldEqual('status', $status);

        return $this->findObjectByExample($example);
    }

    public function deleteByGroupId( $groupId, $status = ZLGHEADER_BOL_Cover::STATUS_ACTIVE )
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);
        $example->andFieldEqual('status', $status);

        return $this->deleteByExample($example);
    }
}