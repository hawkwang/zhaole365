<?php

class ZLGROUPS_CMP_WallWidget extends BASE_CLASS_Widget
{

    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        $params = $paramObj->customParamList;

        $commentParams = new BASE_CommentsParams('zlgroups', ZLGROUPS_BOL_Service::ENTITY_TYPE_WAL);

        $groupId = (int) $paramObj->additionalParamList['entityId'];
        $commentParams->setEntityId($groupId);

        if ( isset($params['comments_count']) )
        {
            $commentParams->setCommentCountOnPage($params['comments_count']);
        }

        if ( isset($params['display_mode']) )
        {
            $commentParams->setDisplayType($params['display_mode']);
        }

        $isMember = ZLGROUPS_BOL_Service::getInstance()->findUser($groupId, OW::getUser()->getId()) !== null;
        $commentParams->setAddComment($isMember);

        $this->addComponent('comments', new BASE_CMP_Comments($commentParams));
    }

    public static function getSettingList()
    {
        $settingList = array();
        $settingList['comments_count'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => OW::getLanguage()->text('base', 'cmp_widget_wall_comments_count'),
            'optionList' => array('3' => 3, '5' => 5, '10' => 10, '20' => 20, '50' => 50, '100' => 100),
            'value' => 10
        );

        $settingList['display_mode'] = array(
            'presentation' => self::PRESENTATION_SELECT,
            'label' => OW::getLanguage()->text('base', 'cmp_widget_wall_comments_mode'),
            'optionList' => array(
                '1' => OW::getLanguage()->text('base', 'cmp_widget_wall_comments_mode_option_1'),
                '2' => OW::getLanguage()->text('base', 'cmp_widget_wall_comments_mode_option_2')
            ),
            'value' => 2
        );

        return $settingList;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_ICON => self::ICON_COMMENT,
            self::SETTING_TITLE => OW::getLanguage()->text('zlgroups', 'wall_widget_label'),
            self::SETTING_WRAP_IN_BOX => false
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}