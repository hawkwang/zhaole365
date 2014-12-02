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
class UHEADER_BOL_TemplateDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var UHEADER_BOL_TemplateDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return UHEADER_BOL_TemplateDao
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
        return 'UHEADER_BOL_Template';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'uheader_template';
    }
    
    public function findListForRoleIds( $roleIds )
    {
        if ( empty($roleIds) )
        {
            return array();
        }
        
        $templateRoleDao = UHEADER_BOL_TemplateRoleDao::getInstance();
        
        $query = "SELECT DISTINCT t.* FROM " . $this->getTableName() . " t 
            LEFT JOIN " . $templateRoleDao->getTableName() . " tr ON t.id = tr.templateId
                WHERE tr.roleId IS NULL OR tr.roleId IN (" . implode(", ", $roleIds) . ")";
        
        return $this->dbo->queryForObjectList($query, $this->getDtoClassName());
    }
    
    public function findDefaultForRoleIds( $roleIds )
    {
        if ( empty($roleIds) )
        {
            return null;
        }
        
        $templateRoleDao = UHEADER_BOL_TemplateRoleDao::getInstance();
        
        $query = "SELECT t.* FROM " . $this->getTableName() . " t 
            LEFT JOIN " . $templateRoleDao->getTableName() . " tr ON t.id = tr.templateId
                WHERE t.default = 1 AND (tr.roleId IS NULL OR tr.roleId IN (" . implode(", ", $roleIds) . ")) ORDER BY RAND() LIMIT 1";
        
        return $this->dbo->queryForObject($query, $this->getDtoClassName());
    }
    
    public function findCountForRoleIds( $roleIds, $default = false )
    {
        if ( empty($roleIds) )
        {
            return null;
        }
        
        $defaultSql = "1";
        
        if ( $default )
        {
            $defaultSql = "t.default = 1";
        }
        
        $templateRoleDao = UHEADER_BOL_TemplateRoleDao::getInstance();
        
        $query = "SELECT COUNT(t.id) FROM " . $this->getTableName() . " t 
            LEFT JOIN " . $templateRoleDao->getTableName() . " tr ON t.id = tr.templateId
                WHERE $defaultSql AND (tr.roleId IS NULL OR tr.roleId IN (" . implode(", ", $roleIds) . "))";
        
        return $this->dbo->queryForColumn($query);
    }
    
    public function findListForAllUsers()
    {
        $templateRoleDao = UHEADER_BOL_TemplateRoleDao::getInstance();
        
        $query = "SELECT DISTINCT t.* FROM " . $this->getTableName() . " t 
            LEFT JOIN " . $templateRoleDao->getTableName() . " tr ON t.id = tr.templateId
                WHERE tr.id IS NULL ORDER BY t.timeStamp DESC";
        
        return $this->dbo->queryForObjectList($query, $this->getDtoClassName());
    }
}