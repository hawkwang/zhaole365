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
 * @package gheader.components
 */
class GHEADER_CMP_Tabs extends BASE_CMP_ContentMenu
{
    public function __construct( $menuItems = null )
    {
        parent::__construct($menuItems);
        
        $this->setTemplate(OW::getPluginManager()->getPlugin('gheader')->getCmpViewDir().'tabs.html');
    }
}