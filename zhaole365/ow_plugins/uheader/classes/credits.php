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
class UHEADER_CLASS_Credits
{
    const ACTION_ADD = 'add_cover';

    public $allActions = array();

    private $actions;

    public function __construct()
    {
        $this->actions[] = array('pluginKey' => 'uheader', 'action' => self::ACTION_ADD, 'amount' => 0);

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
        if ( OW::getUser()->isAuthorized('uheader', $action) )
        {
            return true;
        }
        
        return $this->isPromoted($action);
    }
    
    public function isPromoted( $action )
    {
        $status = BOL_AuthorizationService::getInstance()->getActionStatus('uheader', $action);
        
        return $status['status'] == BOL_AuthorizationService::STATUS_PROMOTED;
    }

    public function getErrorMessage( $action )
    {
        $status = BOL_AuthorizationService::getInstance()->getActionStatus('uheader', $action);
        
        if ( $status['status'] == BOL_AuthorizationService::STATUS_PROMOTED )
        {
            return $status['msg'];
        }
        
        return null;
    }

    public function trackUse( $action )
    {
        BOL_AuthorizationService::getInstance()->trackAction('uheader', $action);
    }
}