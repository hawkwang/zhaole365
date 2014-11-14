<?php
class ZLEVENT_CLASS_Credits
{
    private $actions;

    private $authActions = array();

    public function __construct()
    {
        $this->actions[] = array('pluginKey' => 'zlevent', 'action' => 'add_event', 'amount' => 0);
        $this->actions[] = array('pluginKey' => 'zlevent', 'action' => 'add_comment', 'amount' => 0);
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
}