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
 * @package uheader.controllers
 */
class UHEADER_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    const PLUGIN_STORE_URL = "http://www.oxwall.org/store/item/483";
    
    private $activePlugins = array();

    public function __construct()
    {
        parent::__construct();

        $this->activePlugins = array(
            'photo' => OW::getPluginManager()->isPluginActive('photo')
        );
    }
    
    public static function getMenu()
    {
        $language = OW::getLanguage();

        $menuItems = array();

        $item = new BASE_MenuItem();
        $item->setLabel($language->text('uheader', 'setting_menu_main'));
        $item->setUrl(OW::getRouter()->urlForRoute('uheader-settings-page'));
        $item->setKey('main');
        $item->setIconClass('ow_ic_gear_wheel');
        $item->setOrder(0);
        $menuItems[] = $item;
        
        $item = new BASE_MenuItem();
        $item->setLabel($language->text('uheader', 'setting_menu_gallery'));
        $item->setUrl(OW::getRouter()->urlForRoute('uheader-settings-gallery'));
        $item->setKey('gallery');
        $item->setIconClass('ow_ic_gear_wheel');
        $item->setOrder(1);
        $menuItems[] = $item;

        return new BASE_CMP_ContentMenu($menuItems);
    }

    public function index()
    {
        UHEADER_CLASS_Plugin::getInstance()->includeStaticFile("admin.css");
        
        $form = new UHEADER_SettingForm($this->activePlugins);
        $this->addForm($form);

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $form->process($_POST, $this->activePlugins);
            OW::getFeedback()->info(OW::getLanguage()->text('uheader', 'settings_saved_message'));
            $this->redirect();
        }

        OW::getDocument()->setHeading(OW::getLanguage()->text('uheader', 'heading_configuration'));
        OW::getDocument()->setHeadingIconClass('ow_ic_gear_wheel');

        $this->assign('photoShare', OW::getConfig()->getValue('uheader', 'photo_share'));
        $this->assign('photoViewMode', OW::getConfig()->getValue('uheader', 'tpl_view_mode'));

        $this->assign('plugins', $this->activePlugins);

        $this->assign('minHeight', UHEADER_SettingForm::COVER_MIN_HEIGHT);
        $this->assign('maxHeight', UHEADER_SettingForm::COVER_MAX_HEIGHT);
        
        $this->addComponent("menu", self::getMenu());
        $this->assign("pluginUrl", self::PLUGIN_STORE_URL);
    }
}

class UHEADER_SettingForm extends Form
{
    const COVER_MIN_HEIGHT = 130;
    const COVER_MAX_HEIGHT = 600;

    /**
     * Class constructor
     *
     */
    public function __construct($plugins)
    {
        parent::__construct('configForm');

        $language = OW::getLanguage();

        $values = OW::getConfig()->getValues('uheader');

        if ( $plugins['photo'] )
        {
            $field = new CheckboxField('photo_share');
            $field->setId('photo_share_check');
            $field->setValue($values['photo_share']);
            $this->addElement($field);

            $field = new TextField('photo_album_name');
            $field->setValue(OW::getLanguage()->text('uheader', 'default_photo_album_name'));
            $field->setRequired();

            $this->addElement($field);
        }

        $field = new TextField('cover_height');
        $field->setValue($values['cover_height']);
        $field->addValidator( new IntValidator(self::COVER_MIN_HEIGHT, self::COVER_MAX_HEIGHT));
        $field->setRequired();

        $this->addElement($field);
        
        // submit
        $submit = new Submit('save');
        $submit->setValue($language->text('uheader', 'config_save_label'));
        $this->addElement($submit);
    }

    /**
     * Updates user settings configuration
     *
     * @return boolean
     */
    public function process($post, $plugins)
    {
        $values = $this->getValues();
        $config = OW::getConfig();

        $config->saveConfig('uheader', 'cover_height', intval($values['cover_height']));

        if ( $plugins['photo'] )
        {
            $config->saveConfig('uheader', 'photo_share', empty($values['photo_share']) ? 0 : 1);

            if ( !empty($values['photo_share']) )
            {
                $languageService = BOL_LanguageService::getInstance();
                $langKey = $languageService->findKey('uheader', 'default_photo_album_name');
                if ( !empty($langKey) )
                {
                    $langValue = $languageService->findValue($languageService->getCurrent()->getId(), $langKey->getId());

                    if ( $langValue === null )
                    {
                        $langValue = new BOL_LanguageValue();
                        $langValue->setKeyId($langKey->getId());
                        $langValue->setLanguageId($languageService->getCurrent()->getId());
                    }

                    $languageService->saveValue(
                        $langValue->setValue($values['photo_album_name'])
                    );
                }
            }
        }
        
        OW::getConfig()->saveConfig("uheader", "tpl_view_mode", $post["tpl_view_mode"]);

        return true;
    }
}