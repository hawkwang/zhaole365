<?php

/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package gheader.components
 */
class GHEADER_CMP_HeaderWidget extends BASE_CLASS_Widget
{
    const MAX_HEIGHT = 500;
    const MIN_HEIGHT = 100;

    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        $this->addComponent('header', new GHEADER_CMP_Header($paramObj->additionalParamList['entityId']));
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => false,
            self::SETTING_TITLE => OW::getLanguage()->text('gheader', 'widget_title'),
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
            'label' => OW_Language::getInstance()->text('gheader', 'widget_setting_cover_height'),
            'value' => 250
        );

        if ( GHEADER_CLASS_PhotoBridge::getInstance()->isActive() )
        {
            $settingList['saveToPhoto'] = array(
                'presentation' => self::PRESENTATION_CHECKBOX,
                'label' => OW_Language::getInstance()->text('gheader', 'widget_setting_save_to_photo'),
                'value' => true
            );

            $settingList['albumName'] = array(
                'presentation' => self::PRESENTATION_TEXT,
                'label' => OW_Language::getInstance()->text('gheader', 'widget_setting_photo_album'),
                'value' => GHEADER_CLASS_PhotoBridge::getInstance()->getAlbumName()
            );
        }

        return $settingList;
    }

    public static function validateSettingList( $settingList )
    {
        parent::validateSettingList($settingList);

        $validationMessage = OW::getLanguage()->text('gheader', 'widget_height_validation_error', array(
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

        if ( !GHEADER_CLASS_PhotoBridge::getInstance()->isActive() )
        {
            return;
        }

        $albumName = trim($settingList['albumName']);

        if ( $settingList['saveToPhoto'] && empty($albumName) )
        {
            $errorMessage = OW::getLanguage()->text('gheader', 'widget_album_name_validation_error');

            throw new WidgetSettingValidateException($errorMessage, 'albumName');
        }
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }
}