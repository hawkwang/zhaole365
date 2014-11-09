<?php

class ZLGROUPS_CMP_UserListWidget extends BASE_CLASS_Widget
{

    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $groupId = $params->additionalParamList['entityId'];
        $count = ( empty($params->customParamList['count']) ) ? 9 : (int) $params->customParamList['count'];

        if ( $this->assignList($groupId, $count) )
        {
            $this->setSettingValue(self::SETTING_TOOLBAR, array(array(
                    'label' => OW::getLanguage()->text('zlgroups', 'widget_users_view_all'),
                    'href' => OW::getRouter()->urlForRoute('zlgroups-user-list', array('groupId' => $groupId))
                )));
        }
    }

    private function assignList( $groupId, $count )
    {
        $list = ZLGROUPS_BOL_Service::getInstance()->findUserList($groupId, 0, $count);

        $idlist = array();
        foreach ( $list as $item )
        {
            $idlist[] = $item->id;
        }

        $data = array();

        if ( !empty($idlist) )
        {
            $data = BOL_AvatarService::getInstance()->getDataForUserAvatars($idlist);
        }

        $this->assign("userIdList", $idlist);
        $this->assign("data", $data);

        return !empty($idlist);
    }

    public static function getSettingList()
    {
        $settingList = array();
        $settingList['count'] = array(
            'presentation' => self::PRESENTATION_NUMBER,
            'label' => OW_Language::getInstance()->text('zlgroups', 'widget_users_settings_count'),
            'value' => 9
        );

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_TITLE => OW_Language::getInstance()->text('zlgroups', 'widget_users_title'),
            self::SETTING_ICON => self::ICON_USER
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}