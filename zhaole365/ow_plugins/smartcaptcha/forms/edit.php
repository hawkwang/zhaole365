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
class SMARTCAPTCHA_FORM_Edit extends Form
{
    public function __construct()
    {
        parent::__construct( 'smartcaptchaEdit' );
        $this->setAjax( false );
        $this->setAction( OW::getRouter()->urlForRoute('smartcaptcha.admin-save') );
        
        $qustionId = new HiddenField( 'questionId' );
        $this->addElement( $qustionId );
        
        $question = new TextField( 'question' );
        $question->setRequired();
        $this->addElement( $question );
        
        $submit = new Submit( 'save' );
        $submit->setValue( OW::getLanguage()->text('smartcaptcha', 'save_settings') );
        $this->addElement( $submit );
    }
}
