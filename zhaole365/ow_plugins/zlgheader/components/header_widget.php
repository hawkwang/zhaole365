<?php

class ZLGHEADER_CMP_HeaderWidget extends BASE_CLASS_Widget
{
    const MAX_HEIGHT = 500;
    const MIN_HEIGHT = 100;

    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        $this->addComponent('header', new ZLGHEADER_CMP_Header($paramObj->additionalParamList['entityId']));
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_TITLE => OW::getLanguage()->text('zlgheader', 'widget_title'),
            self::SETTING_WRAP_IN_BOX => false,
            self::SETTING_ICON => self::ICON_USER,
            self::SETTING_FREEZE => false
        );
    }

    public static function getSettingList()
    {
        $settingList = array();
        $settingList['coverHeight'] = array(
            'presentation' => self::PRESENTATION_NUMBER,
            'label' => OW_Language::getInstance()->text('zlgheader', 'widget_setting_cover_height'),
            'value' => 250
        );

        if ( ZLGHEADER_CLASS_PhotoBridge::getInstance()->isActive() )
        {
            $settingList['saveToPhoto'] = array(
                'presentation' => self::PRESENTATION_CHECKBOX,
                'label' => OW_Language::getInstance()->text('zlgheader', 'widget_setting_save_to_photo'),
                'value' => true
            );

            $settingList['albumName'] = array(
                'presentation' => self::PRESENTATION_TEXT,
                'label' => OW_Language::getInstance()->text('zlgheader', 'widget_setting_photo_album'),
                'value' => ZLGHEADER_CLASS_PhotoBridge::getInstance()->getAlbumName()
            );
        }

        return $settingList;
    }

    public static function validateSettingList( $settingList )
    {
        parent::validateSettingList($settingList);

        $validationMessage = OW::getLanguage()->text('zlgheader', 'widget_height_validation_error', array(
            'min' => self::MIN_HEIGHT,
            'max' => self::MAX_HEIGHT
        ));

        $settingList['coverHeight'] = intval($settingList['coverHeight']);

        if ( $settingList['coverHeight'] < self::MIN_HEIGHT )
        {
            throw new WidgetSettingValidateException($validationMessage, 'coverHeight');
        }

        if ( $settingList['coverHeight'] > self::MAX_HEIGHT )
        {
            throw new WidgetSettingValidateException($validationMessage, 'coverHeight');
        }

        if ( !ZLGHEADER_CLASS_PhotoBridge::getInstance()->isActive() )
        {
            return;
        }

        $albumName = trim($settingList['albumName']);

        if ( $settingList['saveToPhoto'] && empty($albumName) )
        {
            $errorMessage = OW::getLanguage()->text('zlgheader', 'widget_album_name_validation_error');

            throw new WidgetSettingValidateException($errorMessage, 'albumName');
        }
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}