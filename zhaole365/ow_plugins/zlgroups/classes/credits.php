<?php

class ZLGROUPS_CLASS_Credits
{
    private $actions;

    private $authActions = array();

    public function __construct()
    {
        $this->actions[] = array('pluginKey' => 'zlgroups', 'action' => 'add_group', 'amount' => 0);
        $this->actions[] = array('pluginKey' => 'zlgroups', 'action' => 'add_post', 'amount' => 0);
        $this->actions[] = array('pluginKey' => 'zlgroups', 'action' => 'add_comment', 'amount' => 0);

        $this->authActions['create'] = 'add_group';
        $this->authActions['add_topic'] = 'add_post';
        $this->authActions['add_comment'] = 'add_comment';
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

    public function getActionKey( OW_Event $e )
    {
        $params = $e->getParams();
        $authAction = $params['actionName'];

        if ( $params['groupName'] != 'zlgroups' )
        {
            return;
        }

        if ( !empty($this->authActions[$authAction]) )
        {
            $e->setData($this->authActions[$authAction]);
        }
    }
}