<?php

class ZLSEARCH_CTRL_Search extends OW_ActionController
{

    public function index()
    {
    	$plugin = OW::getPluginManager()->getPlugin('zlsearch');
    	 
        $this->setPageTitle('找乐365');
        $this->setPageHeading('寻乐群，找乐子');
    	
    	//$this->assign('areas', $areainfos);
        $document = OW::getDocument();
        
        $document->addOnloadScript(';window.searchBar.init();');
        
        $document->addStyleSheet($plugin->getStaticCssUrl() . 'bootstrap.min.css');
        $document->addStyleSheet($plugin->getStaticCssUrl() . 'search_index.css');
        //$document->addScript($plugin->getStaticJsUrl() . 'jquery-1.11.1.min.js');
        $document->addScript($plugin->getStaticJsUrl() . 'bootstrap.min.js');
        $document->addScript($plugin->getStaticJsUrl() . 'search_index.js');
        
    }

    private function text( $prefix, $key, array $vars = null )
    {
        return OW::getLanguage()->text($prefix, $key, $vars);
    }
    
}