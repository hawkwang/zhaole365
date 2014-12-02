<?php

class ZLGHEADER_CMP_InfoWidget extends BASE_CLASS_Widget
{
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();
        
        $groupId = (int) $paramObj->additionalParamList['entityId'];
        $group = ZLGROUPS_BOL_Service::getInstance()->findGroupById($groupId);
        
        $this->assign("description", $group->description);
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_TITLE => OW::getLanguage()->text('zlgheader', 'widget_info_title'),
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_ICON => self::ICON_INFO,
            self::SETTING_FREEZE => false
        );
    }

    public static function getSettingList()
    {
        return array();
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}