<?php

/**
 * Copyright (c) 2012, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * Guests mobile event handler
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_guests.mobile.classes
 * @since 1.3.1
 */
class OCSGUESTS_MCLASS_EventHandler
{
    /**
     * Class instance
     *
     * @var OCSGUESTS_MCLASS_EventHandler
     */
    private static $classInstance;

    /**
     * Class constructor
     *
     */
    private function __construct()
    {
        
    }

    /**
     * Returns class instance
     *
     * @return OCSGUESTS_MCLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( null === self::$classInstance )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public function trackVisit( BASE_CLASS_EventCollector $event )
    {
        $params = $event->getParams();

        if ( empty($params['userId']) )
        {
            return;
        }

        $userId = (int) $params['userId'];
        $viewerId = OW::getUser()->getId();

        OCSGUESTS_BOL_Service::getInstance()->trackVisit($userId, $viewerId);
    }

    public function onUserUnregister( OW_Event $event )
    {
        $params = $event->getParams();

        $userId = $params['userId'];

        OCSGUESTS_BOL_Service::getInstance()->deleteUserGuests($userId);
    }

    public function init()
    {
        OCSGUESTS_CLASS_EventHandler::getInstance()->genericInit();
        $em = OW::getEventManager();

        $em->bind('mobile.content.profile_view_top', array($this, 'trackVisit'));
    }
}
