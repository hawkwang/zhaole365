<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class ZLEvent_Cron extends OW_Cron
{
    public function __construct()
    {
        parent::__construct();

        $this->addJob('clearInvitations', 20);
    }

    public function run()
    {
        //ignore
    }

    public function clearInvitations()
    {        
        $list = ZLEVENT_BOL_EventService::getInstance()->findCronExpiredEvents(0, 1500);
        
        if ( !empty($list) )
        {
            /* @var $event ZLEVENT_BOL_Event */
            foreach ( $list as $event )
            {
                ZLEVENT_BOL_EventService::getInstance()->clearEventInvitations($event->id);
            }
        }
    }
}