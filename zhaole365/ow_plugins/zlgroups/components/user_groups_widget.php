<?php

class ZLGROUPS_CMP_UserGroupsWidget extends BASE_CLASS_Widget
{

    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        /*if ( !ZLGROUPS_BOL_Service::getInstance()->isCurrentUserCanViewList() )
        {
            $this->setVisible(false);

            return;
        }*/

        $userId = $params->additionalParamList['entityId'];
        $count = ( empty($params->customParamList['count']) ) ? 3 : (int) $params->customParamList['count'];

        // privacy check
        $viewerId = OW::getUser()->getId();
        $ownerMode = $userId == $viewerId;
        $modPermissions = OW::getUser()->isAuthorized('zlgroups');

        if ( !$ownerMode && !$modPermissions )
        {
            $privacyParams = array('action' => ZLGROUPS_BOL_Service::PRIVACY_ACTION_VIEW_MY_GROUPS, 'ownerId' => $userId, 'viewerId' => $viewerId);
            $event = new OW_Event('privacy_check_permission', $privacyParams);

            try {
                OW::getEventManager()->trigger($event);
            }
            catch ( RedirectException $e )
            {
                $this->setVisible(false);

                return;
            }
        }

        $userName = BOL_UserService::getInstance()->findUserById($userId)->getUsername();
        if ( !$this->assignList($userId, $count) )
        {
            $this->setVisible($params->customizeMode);

            return;
        }

        $this->setSettingValue(self::SETTING_TOOLBAR, array(array(
            'label' => OW::getLanguage()->text('zlgroups', 'widget_user_groups_view_all'),
            'href' => OW::getRouter()->urlForRoute('zlgroups-user-groups', array('user' => $userName))
        )));

    }

    private function assignList( $userId, $count )
    {
        $service = ZLGROUPS_BOL_Service::getInstance();
        $list = $service->findUserGroupList($userId, 0, $count);

        $tplList = array();
        foreach ( $list as $item )
        {
            /* @var $item ZLGROUPS_BOL_Group */
            $tplList[] = array(
                'image' => $service->getGroupImageUrl($item),
                'title' => htmlspecialchars($item->title),
                'url' => OW::getRouter()->urlForRoute('zlgroups-view', array('groupId' => $item->id))
            );
        }

        $this->assign("list", $tplList);

        return!empty($tplList);
    }

    public static function getSettingList()
    {
        $settingList = array();
        $settingList['count'] = array(
            'presentation' => self::PRESENTATION_NUMBER,
            'label' => OW_Language::getInstance()->text('zlgroups', 'widget_user_groups_settings_count'),
            'value' => 3
        );

        return $settingList;
    }

    public static function processSettingList( $settingList, $place, $isAdmin )
    {
        $settingList['count'] = intval($settingList['count']);

        return parent::processSettingList($settingList, $place, $isAdmin);
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW_Language::getInstance()->text('zlgroups', 'widget_user_groups_title'),
            self::SETTING_ICON => self::ICON_COMMENT,
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_WRAP_IN_BOX => true
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}