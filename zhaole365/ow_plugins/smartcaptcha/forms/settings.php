<?php

/**
 * Copyright (c) 2014, Kairat Bakytow
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 *
 * @author Kairat Bakytow
 * @package ow_plugins.smartcaptcha.forms
 * @since 1.0
 */
class SMARTCAPTCHA_FORM_Settings extends Form
{
    public function __construct()
    {
        parent::__construct( 'smartcaptchaSettings' );
        
        $this->setAjax();
        $this->setAjaxResetOnSuccess( false );
        $this->setAction( OW::getRouter()->urlForRoute('smartcaptcha.admin-ajax_responder') );
        $this->bindJsFunction( 'success' , 'function(data){OW.info(OW.getLanguageText("smartcaptcha","success_save_settings"));window["smart_captcha_" + window.owForms.smartcaptchaSettings.elements.smartcaptcha.id].refresh();}');
        OW::getLanguage()->addKeyForJs( 'smartcaptcha', 'success_save_settings' );
        
        $hidden = new HiddenField( 'command' );
        $hidden->setValue( 'saveSettings' );
        $this->addElement( $hidden );
        
        $lang = OW::getLanguage();
        $configs = OW::getConfig()->getValues( 'smartcaptcha' );
        $fields = array( 
            'imgWidth' => 'i', 
            'imgHeight' => 'i', 
            'imgPerturbation' => 'f',
            'imgTextAngleMinimum' => 'i',
            'imgTextAngleMaximum' => 'i',
            'imgTextTransparencyPercentage' => 'i',
            'imgNumLines' => 'i',
            'imgLineColor' => 'c',
            'imgImageBgColor' => 'c',
            'imgTextColor' => 'c');
        
        foreach ( $fields as $key => $field )
        {
            $imgField = strpos( $key, 'Color' ) === FALSE ? new TextField( $key ) : new SMARTCAPTCHA_FORMELEMENT_Color( $key );
            
            switch ( $field )
            {
                case 'i':
                    $validator = new IntValidator();
                    break;
                case 'f':
                    $validator = new FloatValidator();
                    break;
                default :
                    $validator = new RequiredValidator();
                    break;
            }

            $imgField->addValidator( $validator );
            $imgField->setLabel( $lang->text('smartcaptcha', 'label_' . $key) );
            $imgField->setValue( $configs[$key] );
            $imgField->setDescription( $lang->text('smartcaptcha', 'desc_' . $key) );
            $this->addElement( $imgField );
        }

        $submit = new Submit( 'save' );
        $submit->setValue( $lang->text('smartcaptcha', 'save_settings') );
        $submit->addAttribute( 'class', 'ow_ic_save' );
        $this->addElement( $submit );
    }
}
