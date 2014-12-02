<?php

class ZLGHEADER_CMP_Tabs extends BASE_CMP_ContentMenu
{
    public function __construct( $menuItems = null )
    {
        parent::__construct($menuItems);
        
        $this->setTemplate(OW::getPluginManager()->getPlugin('zlgheader')->getCmpViewDir().'tabs.html');
    }
}