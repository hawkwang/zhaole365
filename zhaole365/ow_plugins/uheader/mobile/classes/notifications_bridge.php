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
class UHEADER_MCLASS_NotificationsBridge extends UHEADER_CLASS_NotificationsBridge
{
    /**
     * Returns class instance
     *
     * @return UHEADER_MCLASS_NotificationsBridge
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    public function onItemRender( OW_Event $event )
    {
        $params = $event->getParams();
        $data = $params["data"];

        if ( !in_array($params['entityType'], array(self::TYPE_COMMENT, self::TYPE_LIKE)) )
        {
            return;
        }
        
        $coverId = null;
        
        if ( empty($params["data"]["coverId"]) )
        {
            $cover = UHEADER_BOL_Service::getInstance()->findCoverByUserId(OW::getUser()->getId());
            
            if ( !empty($cover) )
            {
                $coverId = $cover->id;
            }
        }
        else
        {
            $coverId = $params["data"]["coverId"];
        }
        
        $url = null;
        
        if ( !empty($coverId) )
        {
            $url = UHEADER_MCLASS_NewsfeedBridge::getInstance()->getCoverUrl($coverId);
        }
        
        if ( !empty($url) )
        {
            $data["url"] = $url;
        }
                
        $event->setData($data);
    }
    
    public function init()
    {
        $this->genericInit();
        
        OW::getEventManager()->bind('mobile.notifications.on_item_render', array($this, 'onItemRender'));
    }
}