<?php


class ZLTAGS_CLASS_EventHandler
{

    public function __construct()
    {
        
    }
    
    public function onDeleteEntityTags( OW_Event $event )
    {
    	$params = $event->getParams();
    
    	if ( !empty($params['entityType']) && !empty($params['entityId']) )
    	{
    		ZLTAGS_BOL_TagService::getInstance()->deleteEntityTags($params['entityType'], $params['entityId']);
    	}
    }
    
    public function onPluginUninstallDeleteTags( OW_Event $event )
    {
    	$params = $event->getParams();
    
    	if ( !empty($params['pluginKey']) )
    	{
    		ZLTAGS_BOL_TagService::getInstance()->deletePluginTags($params['pluginKey']);
    	}
    }

    public function genericInit()
    {
    	$eventManager = OW::getEventManager();
    	
        $eventManager->bind("zltags.delete_item", array($this, 'onDeleteEntityTags'));    
    	$eventManager->bind(OW_EventManager::ON_BEFORE_PLUGIN_UNINSTALL, array($this, 'onPluginUninstallDeleteComments'));    
    }

}