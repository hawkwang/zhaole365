<?php

class ZLGROUPS_CMP_TagsWidget extends BASE_CLASS_Widget
{

    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        $params = $paramObj->customParamList;
        $tagParams = new ZLTAGS_CLASS_Params('zlgroups', ZLGROUPS_BOL_Service::ENTITY_TYPE_TAG);
        
        $groupId = (int) $paramObj->additionalParamList['entityId'];
        $tagParams->setEntityId($groupId);
        
        if ( isset($params['display_mode']) )
        {
        	$tagParams->setDisplayType($params['display_mode']);
        }
        
        $isMember = ZLGROUPS_BOL_Service::getInstance()->findUser($groupId, OW::getUser()->getId()) !== null;
        $tagParams->setAddTag($isMember);
        
        $this->addComponent('tags', new ZLTAGS_CMP_Tags($tagParams));

    }

    private function assignList( $groupId )
    {

    	 
        $this->assign("isEditable", true);

        
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_TITLE => OW::getLanguage()->text('zlgroups', 'widget_tags_label'),
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_ICON => "ow_ic_tag" //self::ICON_INFO
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}