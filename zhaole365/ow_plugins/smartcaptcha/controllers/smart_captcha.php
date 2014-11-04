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
 * @package ow_plugins.smartcaptcha.controllers
 * @since 1.0
 */
class SMARTCAPTCHA_CTRL_SmartCaptcha extends OW_ActionController
{
    const CAPTCHA_WIDTH = 200;
    const CAPTCHA_HEIGHT = 68;

    public function __construct()
    {
        parent::__construct();

        require_once OW_DIR_LIB . 'securimage/securimage.php';
    }

    public function index( array $params )
    {
        $configs = OW::getConfig()->getValues( 'smartcaptcha' );
        
        $img = SMARTCAPTCHA_CLASS_Securimage::getInstance();
        $img->setImageWidth( $configs['imgWidth'] );
        $img->setImageHeight( $configs['imgHeight'] );
        $img->setPerturbation( $configs['imgPerturbation'] );
        $img->setImageBgColor( new SmartCaptcha_Securimage_Color($configs['imgImageBgColor']) );
        $img->setTextAngleMinimum( $configs['imgTextAngleMinimum'] );
        $img->setTextAngleMaximum( $configs['imgTextAngleMaximum'] );
        $img->setUseTransparentText( $configs['imgUseTransparentText'] );
        $img->setTextTransparencyPercentage( $configs['imgTextTransparencyPercentage'] );
        $img->setNumLines( $configs['imgNumLines'] );
        $img->setLineColor( new SmartCaptcha_Securimage_Color($configs['imgLineColor']) );
        $img->setTextColor( new SmartCaptcha_Securimage_Color($configs['imgTextColor']) );
        $img->setCode( SMARTCAPTCHA_BOL_Service::getInstance()->getRandomQuestion() );
        $img->show();
        
        exit;
    }
    
    public static function getSmartCaptchaField()
    {
        $field = new SmartCaptchaField( 'smartcaptcha' );
        $field->setRequired();
        
        $validator = new SmartCaptchaValidator();
        $validator->setJsObjectName( SmartCaptchaField::CAPTCHA_PREFIX );
        $field->addValidator( $validator );
        
        return $field;
    }
    
    public static function getPreviewSmartCaptchaField()
    {
        return new SmartCaptchaField( 'smartcaptcha' );
    }

    public function ajaxResponder()
    {
        if ( empty($_POST["command"]) || !OW::getRequest()->isAjax() )
        {
            throw new Redirect404Exception();
        }

        $command = (string) $_POST["command"];

        switch ( $command )
        {
            case 'checkCaptcha':
                $value = $_POST["value"];
                
                $result = SMARTCAPTCHA_CLASS_Securimage::getInstance()->check( $value );

                $result === FALSE ? OW::getEventManager()->trigger( new OW_Event('antibruteforce.authenticate_fail') ) : NULL;
                
                echo json_encode(array('result' => $result));

                break;
        }
        
        exit();
    }
}

class SmartCaptchaField extends FormElement
{
    const CAPTCHA_PREFIX = 'smart_captcha_';

    public $jsObjectName = null;

    public function __construct( $name )
    {
        parent::__construct($name);

        $this->addAttribute('type', 'text');
        $this->jsObjectName = self::CAPTCHA_PREFIX . preg_replace('/[^\d^\w]/', '_', $this->getId());
    }

    public function addValidator( $validator )
    {
        if ( $validator instanceof CaptchaValidator )
        {
            $validator->setJsObjectName($this->jsObjectName);
        }

        return parent::addValidator($validator);
    }

    public function renderInput( $params = null )
    {
        parent::renderInput($params);

        if ( $this->value !== null )
        {
            $this->addAttribute('value', str_replace('"', '&quot;', $this->value));
        }

        $captchaUrl = OW::getRouter()->urlFor('SMARTCAPTCHA_CTRL_SmartCaptcha', 'index');
        $captchaResponderUrl = OW::getRouter()->urlFor('SMARTCAPTCHA_CTRL_SmartCaptcha', 'ajaxResponder');
        $captchaClass = $this->getName() . '_' . $this->getId();

        $jsDir = OW::getPluginManager()->getPlugin('smartcaptcha')->getStaticJsUrl();
        OW::getDocument()->addScript($jsDir . "captcha.js");

        $string = ' window.' . $this->jsObjectName . ' = new OW_SmartCaptcha( ' . json_encode(array('captchaUrl' => $captchaUrl,
                'captchaClass' => $captchaClass,
                'captchaId' => $this->getId(),
                'responderUrl' => $captchaResponderUrl
            )) . ');';

        OW::getDocument()->addOnloadScript($string);

        return '<div class="' . $captchaClass . '">
                    <div class="ow_automargin clearfix"">
                            <div><img src="' . $captchaUrl . '" id="siimage"></div>
                            <div style="padding-top: 21px;"><span class="ic_refresh ow_automargin" id="siimage_refresh" style="cursor:pointer;"></span></div>
                    </div>
                    <div style="padding-top: 10px;">' . UTIL_HtmlTag::generateTag('input', $this->attributes) . '</div>
               </div>';
    }
}

class SmartCaptchaValidator extends CaptchaValidator
{
    public function isValid( $value )
    {
        return self::checkValue( $value );
    }

    public function checkValue( $value )
    {
        return SMARTCAPTCHA_CLASS_Securimage::getInstance()->check( $value );
    }
}
