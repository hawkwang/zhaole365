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
 * @package gheader.classes
 */
class GHEADER_CLASS_CreditsBridge
{
    /**
     * Singleton instance.
     *
     * @var GHEADER_CLASS_CreditsBridge
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return GHEADER_CLASS_CreditsBridge
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
     * @var GHEADER_CLASS_Credits
     */
    public $credits;

    /**
     *
     * @var OW_Plugin
     */
    private $plugin;

    private function __construct()
    {
        $this->credits = new GHEADER_CLASS_Credits();
        $this->plugin = OW::getPluginManager()->getPlugin('gheader');
    }

    public function onCoverAdd( OW_Event $e )
    {
        $this->credits->trackUse(GHEADER_CLASS_Credits::ACTION_ADD);
    }

    public function getAllPermissions()
    {
        $out = array();

        foreach ( $this->credits->allActions as $action )
        {
            $out[$action] = $this->credits->isAvaliable($action);
        }

        return $out;
    }

    public function getAllPermissionMessages()
    {
        $out = array();

        foreach ( $this->credits->allActions as $action )
        {
            $out[$action] = $this->credits->getErrorMessage($action);
        }

        return $out;
    }

    public function init()
    {
        $this->credits->triggerCreditActionsAdd();
        OW::getEventManager()->bind('usercredits.on_action_collect', array($this->credits, 'bindCreditActionsCollect'));

        OW::getEventManager()->bind(GHEADER_BOL_Service::EVENT_ADD, array($this, 'onCoverAdd'));
        OW::getEventManager()->bind(GHEADER_BOL_Service::EVENT_CHANGE, array($this, 'onCoverAdd'));
    }
}