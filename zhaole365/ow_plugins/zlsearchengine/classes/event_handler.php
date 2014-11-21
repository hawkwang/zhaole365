<?php


class ZLSEARCHENGINE_CLASS_EventHandler
{
	private $searchengine_service;
	
    public function __construct()
    {
    	$this->searchengine_service = ZLSEARCHENGINE_BOL_Service::getInstance();
    }

    public function onCreateZLGroup( OW_Event $e )
    {
    	$params = $e->getParams();
    	$entityId = (int) $params['groupId'];
    	
    	ZLSEARCHENGINE_BOL_Service::getInstance()->addToGroupIndex($entityId);
    }
    
    public function onUpdateZLGroup( OW_Event $e )
    {
    	$params = $e->getParams();
    	$groupId = (int) $params['groupId'];
    	 
    	ZLSEARCHENGINE_BOL_Service::getInstance()->addToGroupIndex($groupId);
    }
    
    public function onDeleteZLGroup( OW_Event $e )
    {
    	$params = $e->getParams();
    	$groupId = (int) $params['groupId'];
    	 
    	ZLSEARCHENGINE_BOL_Service::getInstance()->deleteGroupIndex($groupId);
    }
    
    public function onCreateZLEvent( OW_Event $e )
    {
    	$params = $e->getParams();
    	$eventId = (int) $params['eventId'];
    	
    	ZLSEARCHENGINE_BOL_Service::getInstance()->addToEventIndex($eventId);
    }

    public function onUpdateZLEvent( OW_Event $e )
    {
    	$params = $e->getParams();
    	$eventId = (int) $params['eventId'];
    
    	ZLSEARCHENGINE_BOL_Service::getInstance()->addToEventIndex($eventId);
    }
    
    public function onDeleteZLEvent( OW_Event $e )
    {
    	$params = $e->getParams();
    	$eventId = (int) $params['eventId'];
    	
    	ZLSEARCHENGINE_BOL_Service::getInstance()->deleteEventIndex($eventId);
    }
    
    public function onCreateEvent( OW_Event $e )
    {
    	$params = $e->getParams();
    	$eventId = (int) $params['eventId'];
    	 
    	OW::getFeedback()->info('add event -' . $eventId . " to " . $this->searchengine_service->getServiceUrl());
    	 
    }
    
    public function onUpdateEvent( OW_Event $e )
    {
    	$params = $e->getParams();
    	$eventId = (int) $params['eventId'];
    	 
    	OW::getFeedback()->info('update event -' . $eventId . " to " . $this->searchengine_service->getServiceUrl());
    	 
    }
    
    public function onDeleteEvent( OW_Event $e )
    {
    	$params = $e->getParams();
    	$eventId = (int) $params['eventId'];
    	 
    	OW::getFeedback()->info('delete event -' . $eventId . " to " . $this->searchengine_service->getServiceUrl());
    	 
    }
    
    public function onCreateTag( OW_Event $e )
    {
    	$params = $e->getParams();
    	$entityType = (int) $params['entityType'];
    	$entityId = (int) $params['entityId'];
    	switch ( $entityType )
    	{
    		case 'zlgroups':
    			ZLSEARCHENGINE_BOL_Service::getInstance()->addToGroupIndex($entityId);
    			break;
    			
    		case 'zlevent':
    			ZLSEARCHENGINE_BOL_Service::getInstance()->addToEventIndex($entityId);
    			break;
    			
    		default:
    			break;
    	}
    }
    
    public function onDeleteTag( OW_Event $e )
    {
    	$params = $e->getParams();
    	$entityType = (int) $params['entityType'];
    	$entityId = (int) $params['entityId'];
    	switch ( $entityType )
    	{
    		case 'zlgroups':
    			ZLSEARCHENGINE_BOL_Service::getInstance()->deleteGroupIndex($entityId);
    			break;
    			 
    		case 'zlevent':
    			ZLSEARCHENGINE_BOL_Service::getInstance()->deleteEventIndex($entityId);
    			break;
    			 
    		default:
    			break;
    	}
    }
    
    public function init()
    {
    	// 乐群相关
    	// 创建
        OW::getEventManager()->bind('zlgroups_group_create_complete', array($this, 'onCreateZLGroup'));
        // 更新
        OW::getEventManager()->bind('zlgroups_group_edit_complete', array($this, 'onUpdateZLGroup'));
        // 删除
        OW::getEventManager()->bind('zlgroups_group_delete_complete', array($this, 'onDeleteZLGroup'));
        
        // 群乐相关
        // 创建
        OW::getEventManager()->bind('zlevent_after_create_event', array($this, 'onCreateZLEvent'));
        // 更新
        OW::getEventManager()->bind('zlevent_after_event_edit', array($this, 'onUpdateZLEvent'));
        // 删除
        OW::getEventManager()->bind('zlevent_after_delete_event', array($this, 'onDeleteZLEvent'));
                
        // 众乐相关
        // 创建
        OW::getEventManager()->bind('event_after_create_event', array($this, 'onCreateEvent'));
        // 更新
        OW::getEventManager()->bind('event_after_event_edit', array($this, 'onUpdateEvent'));
        // 删除
        OW::getEventManager()->bind('event_after_delete_event', array($this, 'onDeleteEvent'));

        // 标签相关
        OW::getEventManager()->bind('zltags_after_add_tag', array($this, 'onCreateTag'));
        OW::getEventManager()->bind('zltags_after_delete_tag', array($this, 'onDeleteTag'));
        
        // 乐友相关
        // 创建
        // 更新
        // 删除
        
        
        // 相关
        // 创建
        // 更新
        // 删除
        
        
        // 乐活公告板
        // 创建
        // 更新
        // 删除
        
        
        // 找乐一条龙
        // 创建
        // 更新
        // 删除
        
        
        // 其他
        // 创建
        // 更新
        // 删除
        
    	    
    }
}