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
class SMARTCAPTCHA_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    private $service;
    
    public function __construct()
    {
        parent::__construct();

        $this->setPageHeadingIconClass('ow_ic_gear_wheel');
        $this->addComponent('menu', $this->getMenu());
        
        OW::getDocument()->addScript( OW_PluginManager::getInstance()->getPlugin('smartcaptcha')->getStaticJsUrl() . 'smartcaptcha.js' );
        OW::getDocument()->addScriptDeclaration( 'OW.smartcaptcha_rsp = "' . OW::getRouter()->urlForRoute('smartcaptcha.admin-ajax_responder') .'";');
        
        $this->service = SMARTCAPTCHA_BOL_Service::getInstance();
    }
    
    public function viewQuestionAnswerList( $params = NULL )
    {
        OW::getLanguage()->addKeyForJs( 'smartcaptcha', 'floatbox_caption' );
        $page = !empty( $_GET['page'] ) && (int)$_GET['page'] ? abs( (int)$_GET['page'] ) : 1;
        $this->assign( 'items', $this->service->getQuestionAndAnswersCountList($page, 20) );
        $this->setTemplate( OW::getPluginManager()->getPlugin('smartcaptcha')->getViewDir() . 'controllers' . DS . 'view_list.html' );
        
        $form = new SMARTCAPTCHA_FORM_Edit();
        $this->addForm( $form );
        
        $pages = (int) ceil( $this->service->questionCountAll() / 20 );
        $paging = new BASE_CMP_Paging($page, $pages, 20);
        $this->addComponent( 'paging', $paging );
    }
    
    public function settings( $params = NULL )
    {
        $plugin = OW::getPluginManager()->getPlugin( 'smartcaptcha' );
        $document = OW::getDocument();
        
        $document->addStyleSheet( $plugin->getStaticCssUrl() . 'colorpicker.css' );
        $document->addStyleSheet( $plugin->getStaticCssUrl() . 'layout.css', 'screen' );
        $document->addScript( $plugin->getStaticJsUrl() . 'colorpicker.js' );
        
        $settingsForm = new SMARTCAPTCHA_FORM_Settings();
        
        $fields = array();
        
        foreach ( $settingsForm->getElements() as $element )
        {
            if ( !($element instanceof HiddenField) )
            {
                $fields[$element->getName()] = $element->getName();
            }
        }

        $settingsForm->addElement( SMARTCAPTCHA_CTRL_SmartCaptcha::getPreviewSmartCaptchaField() );
        
        $this->addForm( $settingsForm );
        $this->assign( 'formData', $fields );
    }

    public function save()
    {
        if ( OW::getRequest()->isPost() )
        {
            $form = new SMARTCAPTCHA_FORM_Edit();
            
            if ( $form->isValid($_POST) )
            {
                $entity = new SMARTCAPTCHA_BOL_QuestionsDto();
                
                if ( !empty($_POST['questionId']) )
                {
                    $entity->setId( $_POST['questionId'] );
                }
                
                $entity->setQuestion( $_POST['question'] );
                $this->service->saveQuestion( $entity );
                
                $questionId = !empty( $_POST['questionId'] ) ? $_POST['questionId'] : OW::getDbo()->getInsertId();
                $this->service->deleteAnswerByQuestionIdList( (array)$questionId );

                foreach ( $_POST['answers'] as $answer )
                {
                    if ( !empty($answer) && strlen(trim($answer)) > 0 )
                    {
                        $entity = new SMARTCAPTCHA_BOL_AnswerDto();
                        $entity->setIdQuestion( $questionId );
                        $entity->setAnswer( $answer );
                        $this->service->saveAnswer( $entity );
                    }
                }
            }
            
            $this->redirect( $_SERVER['HTTP_REFERER'] );
        }
    }

    public function ajaxResponder()
    {
        if ( OW::getRequest()->isAjax() && OW::getRequest()->isPost() )
        {
            switch ($_POST['command'] )
            {
                case 'getQuestionAnswers':
                    exit( json_encode($this->service->findQuestionAndQnswersByQuestionId($_POST['questionId'])) );
                
                case 'deleteQuestion':
                    if ( $this->service->deleteQuestionByIdList($_POST['data']) )
                    {
                        exit( json_encode(array('result'=> true)) );
                    }
                case 'saveSettings':
                    $settingsForm = new SMARTCAPTCHA_FORM_Settings();
                    
                    if ( $settingsForm->isValid($_POST) )
                    {
                        $configs = OW::getConfig();
                        
                        foreach ( $settingsForm->getElements() as $element )
                        {
                            if ( $element instanceof HiddenField )
                            {
                                continue;
                            }
                            
                            $configs->saveConfig( 'smartcaptcha', $element->getName(), $element->getValue() );
                        }
                        
                        exit( json_encode(array('result' => true)) );
                    }
            }
        }
    }
    
    private function getMenu()
    {
        $language = OW::getLanguage();

        $menuItems = array();

        $keys = array( 'questions_menu_item', 'settings_menu_item' );
        $action = array( 'smartcaptcha.admin', 'smartcaptcha.admin-settings' ); 
        $icons = array( 'files', 'gear_wheel' );
        
        foreach ( $keys as $ord => $key )
        {
            $item = new BASE_MenuItem();
            $item->setLabel( $language->text('smartcaptcha', $keys[$ord]) );
            $item->setUrl( OW::getRouter()->urlForRoute($action[$ord]) );
            $item->setKey( $key );
            $item->setPrefix( 'smartcaptcha' );
            $item->setIconClass( 'ow_ic_' . $icons[$ord] );
            $item->setOrder( $ord );

            array_push( $menuItems, $item );
        }

        return new BASE_CMP_ContentMenu( $menuItems );
    }
}
