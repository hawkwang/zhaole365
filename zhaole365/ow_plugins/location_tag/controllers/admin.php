<?php

/**
 * Copyright (c) 2013, Podyachev Evgeny <joker.OW2@gmail.com>
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */
/**
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_plugins.location_tag.controllers
 * @since 1.0
 */

class LOCATIONTAG_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    /**
     * Default action
     */
    public function index()
    {
        $language = OW::getLanguage();

        $this->setPageHeading($language->text('locationtag', 'admin_page_heading'));
        //$this->setPageTitle($language->text('googlelocation', 'admin_page_title'));
        $this->setPageHeadingIconClass('ow_ic_comment');

        $this->assign('googlemaplocation_enable', false);
        
        if (OW::getPluginManager()->isPluginActive('googlelocation') )
        {
            $this->assign('googlemaplocation_enable', true);
            $this->assign('googlemaplocation_settings_url', OW::getRouter()->urlForRoute('googlelocation_admin'));
        }
        
        $configSaveForm = new ConfigForm();
        $this->addForm($configSaveForm);

        if ( OW::getRequest()->isPost() && $configSaveForm->isValid($_POST) )
        {
            $configSaveForm->process();
            OW::getFeedback()->info($language->text('locationtag', 'settings_updated'));
            $this->redirect();
        }
    }
}

/**
 * Save Configurations form class
 */
class ConfigForm extends Form
{

    /**
     * Class constructor
     *
     */
    public function __construct()
    {
        parent::__construct('configSaveForm');

        $language = OW::getLanguage();

        $configs = OW::getConfig()->getValues('locationtag');

        $element = new TextField('api_key');
        $element->setValue($configs['api_key']);

        $validator = new StringValidator(0, 40);
        $validator->setErrorMessage($language->text('locationtag', 'api_key_too_long'));

        $element->addValidator($validator);
        $this->addElement($element);

        // submit
        $submit = new Submit('save');
        $submit->setValue($language->text('base', 'edit_button'));
        $this->addElement($submit);
    }

    /**
     * Updates forum plugin configuration
     *
     * @return boolean
     */
    public function process()
    {
        $values = $this->getValues();

        $apiKey = empty($values['api_key']) ? '' : $values['api_key'];

        $config = OW::getConfig();

        $config->saveConfig('locationtag', 'api_key', $apiKey);

        return array('result' => true);
    }
}