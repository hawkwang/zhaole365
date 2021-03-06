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
 * @package uheader.components
 */
class UHEADER_CMP_ActionToolbar extends BASE_CMP_ProfileActionToolbar
{
    public function __construct($userId) 
    {
        parent::__construct($userId);
        
        $template = OW::getPluginManager()->getPlugin("base")->getCmpViewDir() 
                . "profile_action_toolbar.html";
        
        $this->setTemplate($template);
    }
}