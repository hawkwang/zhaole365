<?php

class ZLBASE_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function index()
    {
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('zlbase')->getStaticCssUrl() . 'bootstrap.min.css');
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('zlbase')->getStaticJsUrl() . 'jquery-1.11.1.js', 'text/javascript', ZLAREAS_BOL_Service::JQUERY_LOAD_PRIORITY+10);
        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('zlbase')->getStaticJsUrl() . 'bootstrap.min.js', 'text/javascript', ZLAREAS_BOL_Service::JQUERY_LOAD_PRIORITY);
        
        $this->setPageTitle(OW::getLanguage()->text('zlbase', 'admin_base_title'));
        $this->setPageHeading(OW::getLanguage()->text('zlbase', 'admin_base_heading'));

    }
	
	public function delete( $params )
	{
		$this->redirect(OW::getRouter()->urlForRoute('zlbase.admin'));
	}

	
}