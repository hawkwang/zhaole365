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

class LOCATIONTAG_BOL_LocationDao extends OW_BaseDao
{
    const ENTITY_ID = 'entityId';
    const ENTITY_TYPE = 'entityType';
    const ADDRESS_STRING = 'address';
    
    const ENTITY_TYPE_STATUS_UPDATE = 'user-status';

    /**
     * Class constructor
     *
     */
    protected function __construct()
    {
        parent::__construct();
    }
    /**
     * Class instance
     *
     * @var GOOGLELOCATION_BOL_LocationDao
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return GOOGLELOCATION_BOL_LocationDao
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
        return 'LOCATIONTAG_BOL_Location';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'locationtag_data';
    }
    
    /**
     * @param integer $userId
     * @return GOOGLELOCATION_BOL_Location
     */
    public function findByActionId( $actionId )
    {
        if ( empty($userId) )
        {
            return null;
        }
        
        $example = new OW_Example();
        $example->andFieldEqual(self::ENTITY_ID, (int)$actionId);
        $example->andFieldEqual(self::ENTITY_TYPE, self::ENTITY_TYPE_STATUS_UPDATE);
        return $this->findObjectByExample($example);
    }
    
    public function getAllLocationsForEntityType($entityType)
    {
        $query = "SELECT l.* FROM " . $this->getTableName() . " l
                WHERE l.entityType = :entityType ";
                
        return $this->dbo->queryForList($query, array('entityType' => $entityType));
    }
    
    public function findEntityIdAndEntityType( $entityId, $entityType )
    {
        if ( empty($entityId) || empty($entityType) )
        {
            return null;
        }
        
        $example = new OW_Example();
        $example->andFieldEqual(self::ENTITY_ID, $entityId);
        $example->andFieldEqual(self::ENTITY_TYPE, $entityType);
        return $this->findObjectByExample($example);
    }
    
    public function deleteByEntityIdAndEntityType( $entityId, $entityType )
    {
        if ( empty($entityId) || empty($entityType) )
        {
            return false;
        }
        
        $example = new OW_Example();
        $example->andFieldEqual(self::ENTITY_ID, $entityId);
        $example->andFieldEqual(self::ENTITY_TYPE, $entityType);
        return $this->deleteByExample($example);
    }
}
