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
 * @package gheader.classes
 */
class GHEADER_CLASS_ForumBridge
{
    /**
     * Singleton instance.
     *
     * @var GHEADER_CLASS_ForumBridge
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return GHEADER_CLASS_ForumBridge
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
        
    }
    
    public function isActive()
    {
        return OW::getPluginManager()->isPluginActive("forum") && OW::getConfig()->getValue('groups', 'is_forum_connected');
    }
    
    public function getUrl( $groupId )
    {
        if ( !$this->isActive() )
        {
            return null;
        }
        
        $forumGroup = FORUM_BOL_ForumService::getInstance()->findGroupByEntityId("groups", $groupId);
        
        if ( empty($forumGroup) )
        {
            return null;
        }
        
        return OW::getRouter()->urlForRoute('group-default', array(
            'groupId' => $forumGroup->getId()
        ));
    }
    
    public function onCollectTabs( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();
        
        $url = $this->getUrl($params["groupId"]);
        
        if ( empty($url) )
        {
            return;
        }
        
        $tab = new BASE_MenuItem();
        $tab->setKey('forum');
        $tab->setLabel(OW::getLanguage()->text('gheader', 'tab_forum'));
        $tab->setUrl($url);
        $tab->setIconClass('ow_ic_files');
        $tab->setOrder(3);
        
        $event->add($tab);
    }

    public function init()
    {
        if ( !$this->isActive() )
        {
            return;
        }
        
        OW::getEventManager()->bind("gheader.collect_tabs", array($this, "onCollectTabs"));
    }
}