<?php

class ZLBASE_CTRL_Base extends OW_ActionController
{

    public function about()
    {
    	$value = ZLBASE_BOL_Service::getInstance()->findProperty('zlbase', 0, 'aboutus');
    	
    	$this->assign('about', $value);

    }
    
    public function duty()
    {
    	$value = ZLBASE_BOL_Service::getInstance()->findProperty('zlbase', 0, 'duty');
    	 
    	$this->assign('duty', $value);
    
    }
    
    public function agreement()
    {
    	$value = ZLBASE_BOL_Service::getInstance()->findProperty('zlbase', 0, 'agreement');
    
    	$this->assign('agreeement', $value);
    
    }

    private function text( $prefix, $key, array $vars = null )
    {
        return OW::getLanguage()->text($prefix, $key, $vars);
    }

    
}