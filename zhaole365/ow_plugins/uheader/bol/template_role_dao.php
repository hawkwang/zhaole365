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
class UHEADER_BOL_TemplateRoleDao extends OW_BaseDao
{
    /**
     * Singleton instance.
     *
     * @var UHEADER_BOL_TemplateRoleDao
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return UHEADER_BOL_TemplateRoleDao
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
        return 'UHEADER_BOL_TemplateRole';
    }

    /**
     * @see OW_BaseDao::getTableName()
     *
     */
    public function getTableName()
    {
        return OW_DB_PREFIX . 'uheader_template_role';
    }
    
    public function findByTemplateId( $templateId )
    {
        $example = new OW_Example();
        $example->andFieldEqual("templateId", $templateId);
        
        return $this->findListByExample($example);
    }
    
    public function deleteByTemplateId( $templateId )
    {
        $example = new OW_Example();
        $example->andFieldEqual("templateId", $templateId);
        
        return $this->deleteByExample($example);
    }
}