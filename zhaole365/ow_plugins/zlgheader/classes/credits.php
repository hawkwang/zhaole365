<?php

class ZLGHEADER_CLASS_Credits
{
    const ACTION_ADD = 'add_cover';

    public $allActions = array();

    private $actions;

    public function __construct()
    {
        $this->actions[] = array('pluginKey' => 'zlgheader', 'action' => self::ACTION_ADD, 'amount' => 0);

        $this->allActions = array(
            self::ACTION_ADD
        );

    }

    public function bindCreditActionsCollect( BASE_CLASS_EventCollector $e )
    {
        foreach ( $this->actions as $action )
        {
            $e->add($action);
        }
    }

    public function triggerCreditActionsAdd()
    {
        $e = new BASE_CLASS_EventCollector('usercredits.action_add');

        foreach ( $this->actions as $action )
        {
            $e->add($action);
        }

        OW::getEventManager()->trigger($e);
    }

    public function isAvaliable( $action )
    {
        if ( OW::getUser()->isAuthorized('zlgheader', $action) )
        {
            return true;
        }
        
        return $this->isPromoted($action);
    }
    
    public function isPromoted( $action )
    {
        $status = BOL_AuthorizationService::getInstance()->getActionStatus('zlgheader', $action);
        
        return $status['status'] == BOL_AuthorizationService::STATUS_PROMOTED;
    }

    public function getErrorMessage( $action )
    {
        $status = BOL_AuthorizationService::getInstance()->getActionStatus('zlgheader', $action);
        
        if ( $status['status'] == BOL_AuthorizationService::STATUS_PROMOTED )
        {
            return $status['msg'];
        }
        
        return null;
    }

    public function trackUse( $action )
    {
        BOL_AuthorizationService::getInstance()->trackAction('zlgheader', $action);
    }
}