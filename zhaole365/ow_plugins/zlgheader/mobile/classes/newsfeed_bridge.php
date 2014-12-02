<?php

class ZLGHEADER_MCLASS_NewsfeedBridge extends ZLGHEADER_CLASS_NewsfeedBridge
{

    private static $classInstance;

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
            "name" => "zlgroup_cover",
            "class" => "ZLGHEADER_MCLASS_CoverFormat"
        ));
    }
    
    public function init()
    {
        OW::getEventManager()->bind('feed.collect_formats', array($this, 'onCollectFormats'));
    }
}