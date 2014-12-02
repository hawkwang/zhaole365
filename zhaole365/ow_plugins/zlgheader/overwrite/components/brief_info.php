<?php

class ZLGROUPS_CMP_BriefInfo extends OW_Component
{

    public function __construct($groupId)
    {
        parent::__construct();
        
        $this->addComponent('content', new ZLGHEADER_CMP_Header($groupId));
        
        $this->assign('box', $this->getBoxParmList($groupId));
    }
    
    private function getBoxParmList($groupId)
    {
        $settings = ZLGHEADER_BOL_Service::getInstance()->getConfigList($groupId);
        
        $settings['type'] = $settings['wrap_in_box'] ? '' : 'empty';
        
        return $settings;
    }
}