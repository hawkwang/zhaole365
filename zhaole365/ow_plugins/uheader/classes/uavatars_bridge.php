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
 * @package uheader.classes
 */
class UHEADER_CLASS_UavatarsBridge
{

    /**
     * Class instance
     *
     * @var UHEADER_CLASS_UavatarsBridge
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return UHEADER_CLASS_UavatarsBridge
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private $isPluginActive = false;

    /**
     *
     * @var OW_Plugin
     */
    private $plugin;

    public function __construct()
    {
        $this->isPluginActive = OW::getPluginManager()->isPluginActive('uavatars');
        $this->plugin = OW::getPluginManager()->getPlugin('uheader');
    }

    public function isActive()
    {
        return $this->isPluginActive;
    }
    
    public function hasHistory( $userId )
    {
        if ( !$this->isActive() )
        {
            return null;
        }
        
        $avatar = UAVATARS_BOL_Service::getInstance()->findLastByUserId($userId);

        return $avatar !== null;
    }

    public function init()
    {
        
    }
}