<?php

class GROUPS_CMP_BriefInfoWidget extends BASE_CLASS_Widget
{

    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        $service = GROUPS_BOL_Service::getInstance();
        $groupId = (int) $paramObj->additionalParamList['entityId'];

        $this->addComponent('briefInfo', new GROUPS_CMP_BriefInfoContent($groupId));
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_TITLE => OW::getLanguage()->text('groups', 'widget_brief_info_label'),
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_ICON => self::ICON_INFO
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}