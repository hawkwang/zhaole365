<?php

class ZLGROUPS_CMP_JoinButtonWidget extends BASE_CLASS_Widget
{

    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $groupId = $params->additionalParamList['entityId'];
        $userId = OW::getUser()->getId();

        if ( !$params->customizeMode && ZLGROUPS_BOL_Service::getInstance()->findUser($groupId, $userId) !== null )
        {
            $this->setVisible(false);

            return;
        }

        $actionUrl = OW::getRouter()->urlForRoute('zlgroups-join', array('groupId' => $groupId));
        $this->assign('actionUrl', $actionUrl);
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_TITLE => OW_Language::getInstance()->text('zlgroups', 'widget_join_button_title'),
            self::SETTING_ICON => self::ICON_ADD
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_MEMBER;
    }
}