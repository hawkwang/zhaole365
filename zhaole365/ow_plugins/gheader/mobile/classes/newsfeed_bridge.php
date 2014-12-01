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
 * @package gheader.mobile.classes
 */
class GHEADER_MCLASS_NewsfeedBridge extends GHEADER_CLASS_NewsfeedBridge
{

    /**
     * Class instance
     *
     * @var GHEADER_MCLASS_NewsfeedBridge
     */
    private static $classInstance;

    /**
     * Returns class instance
     *
     * @return GHEADER_MCLASS_NewsfeedBridge
     */
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
        parent::__construct();
    }
    
    public function onCollectFormats( BASE_CLASS_EventCollector $event )
    {
        $event->add(array(
            "name" => "group_cover",
            "class" => "GHEADER_MCLASS_CoverFormat"
        ));
    }
    
    public function init()
    {
        OW::getEventManager()->bind('feed.collect_formats', array($this, 'onCollectFormats'));
    }
}