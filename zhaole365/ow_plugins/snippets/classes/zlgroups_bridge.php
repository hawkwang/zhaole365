<?php

class SNIPPETS_CLASS_ZlgroupsBridge
{
    
    const WIDGET_NAME = "zlgroups";
    
    protected static $classInstance;

    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    protected function __construct()
    {
        
    }
    
    public function isActive()
    {
        return OW::getPluginManager()->isPluginActive("zlgroups");
    }
    
    public function collectSnippets( BASE_CLASS_EventCollector $event )
    {
        $language = OW::getLanguage();
        $params = $event->getParams();
        
        if ( $params["entityType"] != SNIPPETS_CLASS_EventHandler::ENTITY_TYPE_USER )
        {
            return;
        }
        
        $userId = $params["entityId"];
        $preview = $params["preview"];
        
        $snippet = new SNIPPETS_CMP_Snippet(self::WIDGET_NAME, $userId);
        
        if ( $preview )
        {
            $snippet->setLabel($language->text("snippets", "snippet_groups_preview"));
            $snippet->setIconClass("ow_ic_files");
            $event->add($snippet);
            
            return;
        }
        
        // Privacy check
        $eventParams =  array(
            'action' => ZLGROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS,
            'ownerId' => $userId,
            'viewerId' => OW::getUser()->getId()
        );
        
        try
        {
            OW::getEventManager()->getInstance()->call('privacy_check_permission', $eventParams);
        }
        catch( RedirectException $exception )
        {
            return;
        }
                
        $service = ZLGROUPS_BOL_Service::getInstance();
        $total = $service->findUserGroupListCount($userId);
        $list = $service->findUserGroupList($userId, 0, 3);

        if ( empty($list) )
        {
            return;
        }
        
        $images = array();
        foreach ( $list as $group )
        {
            $images[] = $service->getGroupImageUrl($group);
        }
        
        $url = OW::getRouter()->urlForRoute("zlgroups-user-groups", array(
            "user" => BOL_UserService::getInstance()->getUserName($userId)
        ));
        
        $snippet->setImages($images);
        $snippet->setLabel($language->text("snippets", "snippet_groups", array(
            "count" => '<span class="ow_txt_value">' . $total . '</span>'
        )));
        $snippet->setUrl($url);
        
        $event->add($snippet);
    }
    
    public function init()
    {
        if ( !$this->isActive() )
        {
            return;
        }
        
        OW::getEventManager()->bind(SNIPPETS_CLASS_EventHandler::EVENT_COLLECT_SNIPPETS, array($this, "collectSnippets"));
    }
}