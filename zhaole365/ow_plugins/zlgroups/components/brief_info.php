<?php


class ZLGROUPS_CMP_BriefInfo extends OW_Component
{
    /**
     * @return Constructor.
     */
    public function __construct($groupId)
    {
        parent::__construct();
        
        $this->addComponent('content', new ZLGROUPS_CMP_BriefInfoContent($groupId));
        
        $this->assign('box', $this->getBoxParmList($groupId));
    }
    
    private function getBoxParmList($groupId)
    {
        $settings = ZLGROUPS_CMP_BriefInfoWidget::getStandardSettingValueList();
        $defaultSettings = BOL_ComponentAdminService::getInstance()->findSettingList('zlgroup-ZLGROUPS_CMP_BriefInfoWidget');
        $customSettings = BOL_ComponentEntityService::getInstance()->findSettingList('zlgroup-ZLGROUPS_CMP_BriefInfoWidget', $groupId);
        
        $out = array_merge($settings, $defaultSettings, $customSettings);
        $out['type'] = $out['wrap_in_box'] ? '' : 'empty';
        
        return $out;
    }
}