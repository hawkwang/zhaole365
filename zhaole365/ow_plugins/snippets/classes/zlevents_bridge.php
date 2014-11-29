<?php

class SNIPPETS_CLASS_ZleventsBridge
{
    
    const SNIPPET_NAME = "zlevents";
    
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
        return OW::getPluginManager()->isPluginActive("zlevent");
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
        
        $service = ZLEVENT_BOL_EventService::getInstance();
                
        $snippet = new SNIPPETS_CMP_Snippet(self::SNIPPET_NAME, $userId);
        
        if ( $preview )
        {
            $snippet->setLabel($language->text("snippets", "snippet_events_preview"));
            $snippet->setIconClass("ow_ic_calendar");
            $event->add($snippet);
            
            return;
        }
        
        // Privacy check
        $eventParams =  array(
            'action' => 'zlevent_view_attend_events',
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
        
        $list = $service->findUserParticipatedPublicEvents($userId, 1, 3);
        $total = $service->findUserParticipatedPublicEventsCount($userId);

        if ( empty($list) )
        {
            return;
        }
        
        $images = array();
        foreach ( $list as $eventItem )
        {
            $images[] = $eventItem->getImage() ? $service->generateImageUrl($eventItem->getImage(), true) : $service->generateDefaultImageUrl();
        }
        
        $url = OW::getRouter()->urlForRoute("zlevent.view_event_list", array(
            "list" => "user-participated-events"
        ));
        
        $url = OW::getRequest()->buildUrlQueryString($url, array(
            "userId" => $userId
        ));
        
        $snippet->setImages($images);
        $snippet->setLabel($language->text("snippets", "snippet_events", array(
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