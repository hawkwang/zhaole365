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
 * @package uheader.mobile.classes
 */
class UHEADER_MCLASS_NewsfeedBridge extends UHEADER_CLASS_NewsfeedBridge
{

    /**
     * Returns class instance
     *
     * @return UHEADER_MCLASS_NewsfeedBridge
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    public function onCollectFormats( BASE_CLASS_EventCollector $event )
    {
        $event->add(array(
            "name" => "profile_cover",
            "class" => "UHEADER_MCLASS_CoverFormat"
        ));
    }
    
    public function getCoverUrl( $coverId )
    {
        return OW::getEventManager()->call("feed.get_item_permalink", array(
            "entityType" => UHEADER_CLASS_CommentsBridge::ENTITY_TYPE,
            "entityId" => $coverId
        ));
    }

    public function init()
    {
        $this->genericInit();
        
        OW::getEventManager()->bind('feed.collect_formats', array($this, 'onCollectFormats'));
    }
}