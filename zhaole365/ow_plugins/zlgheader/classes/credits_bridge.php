<?php

class ZLGHEADER_CLASS_CreditsBridge
{

    private static $classInstance;

    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    public $credits;

    private $plugin;

    private function __construct()
    {
        $this->credits = new ZLGHEADER_CLASS_Credits();
        $this->plugin = OW::getPluginManager()->getPlugin('zlgheader');
    }

    public function onCoverAdd( OW_Event $e )
    {
        $this->credits->trackUse(ZLGHEADER_CLASS_Credits::ACTION_ADD);
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

        OW::getEventManager()->bind(ZLGHEADER_BOL_Service::EVENT_ADD, array($this, 'onCoverAdd'));
        OW::getEventManager()->bind(ZLGHEADER_BOL_Service::EVENT_CHANGE, array($this, 'onCoverAdd'));
    }
}