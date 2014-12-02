<?php

/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 *
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package uheader.classes
 */
class UHEADER_MCLASS_EventHandler extends UHEADER_CLASS_EventHandler
{
    /**
     * Returns class instance
     *
     * @return UHEADER_MCLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    public function onGetClassInstance( OW_Event $event )
    {
        $params = $event->getParams();

        if ( $params['className'] != 'BASE_MCMP_ProfileHeader' )
        {
            return;
        }

        $arguments = $params['arguments'];
        $cmp = new UHEADER_MCMP_ProfileHeader($arguments[0]);
        $event->setData($cmp);

        return $cmp;
    }
    
    public function init()
    {
        $this->genericInit();
        
        OW::getEventManager()->bind('class.get_instance', array($this, 'onGetClassInstance'));
    }
}