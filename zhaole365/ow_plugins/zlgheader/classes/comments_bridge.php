<?php

class ZLGHEADER_CLASS_CommentsBridge
{
    const ENTITY_TYPE = 'zlgroup-cover';

    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private $service;

    private function __construct()
    {
        $this->service = BOL_CommentService::getInstance();
    }

    public function onCoverRemove( OW_Event $e )
    {
        $params = $e->getParams();

        $this->service->deleteEntityComments(self::ENTITY_TYPE, $params['id']);
    }

    public function init()
    {
        OW::getEventManager()->bind(ZLGHEADER_BOL_Service::EVENT_REMOVE, array($this, 'onCoverRemove'));
    }
}