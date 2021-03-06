<?php

class ZLGROUPS_CMP_LocationWidget extends BASE_CLASS_Widget
{

    /**
     * @return Constructor.
     */
    public function __construct( BASE_CLASS_WidgetParameter $params )
    {
        parent::__construct();

        $groupId = $params->additionalParamList['entityId'];
        
        OW::getDocument()->addScript('http://api.map.baidu.com/api?v=2.0&ak=HL2OtpqEFglWT1j2RoS62eRD');
        
        $this->assignList( $groupId );

    }

    private function assignList( $groupId )
    {

    	$detailedLocationInfo = ZLGROUPS_BOL_Service::getInstance()->findLocationDetailedInfoByGroupId($groupId);
    	 
        $this->assign("location", $detailedLocationInfo['location']);
        $this->assign("formated_address", $detailedLocationInfo['formated_address']);
        $this->assign("longitude", $detailedLocationInfo['longitude']);
        $this->assign("latitude", $detailedLocationInfo['latitude']);
        $zlmarkerurl = OW::getPluginManager()->getPlugin('zlbase')->getStaticUrl() . 'img/zlmarker.png';
        $this->assign("zlmarkerurl", $zlmarkerurl);
        
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_TITLE => OW::getLanguage()->text('zlgroups', 'widget_location_label'),
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_ICON => "ow_ic_places" //self::ICON_INFO
        );
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}