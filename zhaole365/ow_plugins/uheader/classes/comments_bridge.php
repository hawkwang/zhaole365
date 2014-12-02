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
class UHEADER_CLASS_CommentsBridge
{
    const ENTITY_TYPE = 'profile-cover';

    /**
     * Singleton instance.
     *
     * @var UHEADER_CLASS_CommentsBridge
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return UHEADER_CLASS_CommentsBridge
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    /**
     *
     * @var BOL_CommentService
     */
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
        OW::getEventManager()->bind(UHEADER_BOL_Service::EVENT_REMOVE, array($this, 'onCoverRemove'));
    }
}