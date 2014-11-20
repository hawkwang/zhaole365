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
    	$groupId = (int) $params['groupId'];
    	

    	
    	OW::getFeedback()->info('add group index -' . $groupId . " to " . $this->searchengine_service->getServiceUrl());
    }
    
    public function onUpdateZLGroup( OW_Event $e )
    {
    	$params = $e->getParams();
    	$groupId = (int) $params['groupId'];
    	 
    	OW::getFeedback()->info('update group index -' . $groupId . " to " . $this->searchengine_service->getServiceUrl());
    }
    
    public function onDeleteZLGroup( OW_Event $e )
    {
    	$params = $e->getParams();
    	$groupId = (int) $params['groupId'];
    	 
    	OW::getFeedback()->info('delete group index -' . $groupId . " to " . $this->searchengine_service->getServiceUrl());
    }
    
    public function onCreateZLEvent( OW_Event $e )
    {
    	$params = $e->getParams();
    	$eventId = (int) $params['eventId'];
    	
    	OW::getFeedback()->info('add group event -' . $eventId . " to " . $this->searchengine_service->getServiceUrl());
    }

    public function onUpdateZLEvent( OW_Event $e )
    {
    	$params = $e->getParams();
    	$eventId = (int) $params['eventId'];
    
    	OW::getFeedback()->info('update group event -' . $eventId . " to " . $this->searchengine_service->getServiceUrl());
    }
    
    public function onDeleteZLEvent( OW_Event $e )
    {
    	$params = $e->getParams();
    	$eventId = (int) $params['eventId'];
    	
    	OW::getFeedback()->info('delete group event -' . $eventId . " to " . $this->searchengine_service->getServiceUrl());
    	 
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
        OW::getEventManager()->bind(EVENT_BOL_EventService::EVENT_AFTER_CREATE_EVENT, array($this, 'onCreateEvent'));
        // 更新
        OW::getEventManager()->bind(EVENT_BOL_EventService::EVENT_AFTER_EVENT_EDIT, array($this, 'onUpdateEvent'));
        // 删除
        OW::getEventManager()->bind(EVENT_BOL_EventService::EVENT_AFTER_DELETE_EVENT, array($this, 'onDeleteEvent'));
                
        
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