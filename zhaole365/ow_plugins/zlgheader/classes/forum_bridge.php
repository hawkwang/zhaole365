<?php

class ZLGHEADER_CLASS_ForumBridge
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

    private function __construct()
    {
        
    }
    
    public function isActive()
    {
        return OW::getPluginManager()->isPluginActive("forum") && OW::getConfig()->getValue('zlgroups', 'is_forum_connected');
    }
    
    public function getUrl( $groupId )
    {
        if ( !$this->isActive() )
        {
            return null;
        }
        
        $forumGroup = FORUM_BOL_ForumService::getInstance()->findGroupByEntityId("zlgroups", $groupId);
        
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
        $tab->setLabel(OW::getLanguage()->text('zlgheader', 'tab_forum'));
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
        
        OW::getEventManager()->bind("zlgheader.collect_tabs", array($this, "onCollectTabs"));
    }
}