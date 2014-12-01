<?php

/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package gheader.bol
 */
class GHEADER_BOL_CoverDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var GHEADER_BOL_CoverDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return GHEADER_BOL_CoverDao
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     * @see OW_BaseDao::getDtoClassName()
     *
     */
    public function getDtoClassName()
    {
        return 'GHEADER_BOL_Cover';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'gheader_cover';
    }

    public function findByGroupId( $groupId, $status = GHEADER_BOL_Cover::STATUS_ACTIVE )
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);
        $example->andFieldEqual('status', $status);

        return $this->findObjectByExample($example);
    }

    public function deleteByGroupId( $groupId, $status = GHEADER_BOL_Cover::STATUS_ACTIVE )
    {
        $example = new OW_Example();
        $example->andFieldEqual('groupId', $groupId);
        $example->andFieldEqual('status', $status);

        return $this->deleteByExample($example);
    }
}