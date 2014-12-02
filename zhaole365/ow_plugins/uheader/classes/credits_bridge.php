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
class UHEADER_CLASS_CreditsBridge
{
    /**
     * Singleton instance.
     *
     * @var UHEADER_CLASS_CreditsBridge
     */
    private static $classInstance;

    /**
     * Returns an instance of class (singleton pattern implementation).
     *
     * @return UHEADER_CLASS_CreditsBridge
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
     * @var UHEADER_CLASS_Credits
     */
    public $credits;

    /**
     *
     * @var OW_Plugin
     */
    private $plugin;

    private function __construct()
    {
        $this->credits = new UHEADER_CLASS_Credits();
        $this->plugin = OW::getPluginManager()->getPlugin('uheader');
    }

    public function onCoverAdd( OW_Event $e )
    {
        $this->credits->trackUse(UHEADER_CLASS_Credits::ACTION_ADD);
    }

    public function init()
    {
        $this->credits->triggerCreditActionsAdd();
        OW::getEventManager()->bind('usercredits.on_action_collect', array($this->credits, 'bindCreditActionsCollect'));

        OW::getEventManager()->bind(UHEADER_BOL_Service::EVENT_ADD, array($this, 'onCoverAdd'));
        OW::getEventManager()->bind(UHEADER_BOL_Service::EVENT_CHANGE, array($this, 'onCoverAdd'));
    }
}