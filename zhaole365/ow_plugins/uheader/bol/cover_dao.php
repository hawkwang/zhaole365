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
 * @package uheader.bol
 */
class UHEADER_BOL_CoverDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var UHEADER_BOL_CoverDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return UHEADER_BOL_CoverDao
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
        return 'UHEADER_BOL_Cover';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'uheader_cover';
    }

    public function findByUserId( $userId, $status = UHEADER_BOL_Cover::STATUS_ACTIVE )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        
        if ( $status !== null )
        {
            $example->andFieldEqual('status', $status);
        }

        return $this->findObjectByExample($example);
    }
    
    public function findCountByTemplateId( $templateId, $status = UHEADER_BOL_Cover::STATUS_ACTIVE )
    {
        $example = new OW_Example();
        $example->andFieldEqual('templateId', $templateId);
        
        if ( $status !== null )
        {
            $example->andFieldEqual('status', $status);
        }

        return $this->countByExample($example);
    }
    
    public function findListByUserId( $userId, $status = null )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        
        if ( $status !== null )
        {
            $example->andFieldEqual('status', $status);
        }

        return $this->findListByExample($example);
    }

    public function deleteByUserId( $userId, $status = UHEADER_BOL_Cover::STATUS_ACTIVE )
    {
        $example = new OW_Example();
        $example->andFieldEqual('userId', $userId);
        $example->andFieldEqual('status', $status);

        return $this->deleteByExample($example);
    }
    
    public function deleteByTemplateId( $templateId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('templateId', $templateId);

        return $this->deleteByExample($example);
    }
    
    public function findByTemplateId( $templateId )
    {
        $example = new OW_Example();
        $example->andFieldEqual('templateId', $templateId);

        return $this->findListByExample($example);
    }
}