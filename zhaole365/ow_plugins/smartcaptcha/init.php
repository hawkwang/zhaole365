<?php

/**
 * Copyright (c) 2014, Kairat Bakytow
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

OW::getRouter()->addRoute( new OW_Route('smartcaptcha.ajax_responder', 'smartcaptcha/ajax-responder', 'SMARTCAPTCHA_CTRL_SmartCaptcha', 'ajaxResponder') );
OW::getRouter()->addRoute( new OW_Route('smartcaptcha.admin', 'admin/smartcaptcha', 'SMARTCAPTCHA_CTRL_Admin', 'viewQuestionAnswerList') );
OW::getRouter()->addRoute( new OW_Route('smartcaptcha.admin-save', 'admin/smartcaptcha/save', 'SMARTCAPTCHA_CTRL_Admin', 'save') );
OW::getRouter()->addRoute( new OW_Route('smartcaptcha.admin-settings', 'admin/smartcaptcha/settings', 'SMARTCAPTCHA_CTRL_Admin', 'settings') );
OW::getRouter()->addRoute( new OW_Route('smartcaptcha.admin-ajax_responder', 'admin/smartcaptcha/ajax-responder', 'SMARTCAPTCHA_CTRL_Admin', 'ajaxResponder') );

OW::getAutoloader()->addPackagePointer( 'SMARTCAPTCHA_FORM', OW::getPluginManager()->getPlugin('smartcaptcha')->getRootDir() . 'forms' . DS );
OW::getAutoloader()->addPackagePointer( 'SMARTCAPTCHA_FORMELEMENT', OW::getPluginManager()->getPlugin('smartcaptcha')->getRootDir() . 'forms' . DS . 'elements' . DS );

function smartcaptcha_get_captcha_field( OW_Event $event )
{
    $event->setData( SMARTCAPTCHA_CTRL_SmartCaptcha::getSmartCaptchaField() );
}
OW::getEventManager()->bind( 'join.get_captcha_field', 'smartcaptcha_get_captcha_field' );

function smartcaptcha_add_access_exception( BASE_CLASS_EventCollector $event )
{
    $event->add(array('controller' => 'SMARTCAPTCHA_CTRL_SmartCaptcha', 'action' => 'index'));
} 

OW::getEventManager()->bind('base.members_only_exceptions', 'smartcaptcha_add_access_exception');
OW::getEventManager()->bind('base.password_protected_exceptions', 'smartcaptcha_add_access_exception');
OW::getEventManager()->bind('base.splash_screen_exceptions', 'smartcaptcha_add_access_exception');
