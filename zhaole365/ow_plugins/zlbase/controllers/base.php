<?php

class ZLBASE_CTRL_Base extends OW_ActionController
{

    public function about()
    {
    	$document = OW::getDocument();
    	$document->addOnloadScript(';window.aboutUs.init();');
    	 
    	OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('zlbase')->getStaticCssUrl() . 'bootstrap.min.css');
    	OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('zlbase')->getStaticCssUrl() . 'timeline.css');
    	OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('zlbase')->getStaticJsUrl() . 'jquery-1.11.1.min.js', 'text/javascript', ZLAREAS_BOL_Service::JQUERY_LOAD_PRIORITY+10);
    	OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('zlbase')->getStaticJsUrl() . 'bootstrap.min.js', 'text/javascript', ZLAREAS_BOL_Service::JQUERY_LOAD_PRIORITY);
    	OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('zlbase')->getStaticJsUrl() . 'aboutus.js', 'text/javascript', ZLAREAS_BOL_Service::JQUERY_LOAD_PRIORITY);
    	 
    	$this->setPageTitle(OW::getLanguage()->text('zlbase', 'about_title'));
    	$this->setPageHeading(OW::getLanguage()->text('zlbase', 'about_heading'));
    	
    	$value = ZLBASE_BOL_Service::getInstance()->findProperty('zlbase', 0, 'aboutus');
    	
    	
    	$this->assign('about', $value);
    	

    }
    
    public function duty()
    {
    	$this->setPageTitle(OW::getLanguage()->text('zlbase', 'duty_title'));
    	$this->setPageHeading(OW::getLanguage()->text('zlbase', 'duty_heading'));
    	
    	$value = ZLBASE_BOL_Service::getInstance()->findProperty('zlbase', 0, 'duty');
    	 
    	$this->assign('duty', $value);
    
    }
    
    public function agreement()
    {
    	$this->setPageTitle(OW::getLanguage()->text('zlbase', 'agreement_title'));
    	$this->setPageHeading(OW::getLanguage()->text('zlbase', 'agreement_heading'));
    	 
    	$value = ZLBASE_BOL_Service::getInstance()->findProperty('zlbase', 0, 'agreement');
    
    	$this->assign('agreeement', $value);
    
    }

    private function text( $prefix, $key, array $vars = null )
    {
        return OW::getLanguage()->text($prefix, $key, $vars);
    }

    
}