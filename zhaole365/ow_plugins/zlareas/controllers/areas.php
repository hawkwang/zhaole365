<?php

/**
 * Main page
 *
 * @author Hawk Wang <zhaole365@yahoo.com>
 * @package ow_plugins.zlareas.controllers
 * @since 1.0
 */
class ZLAREAS_CTRL_Areas extends OW_ActionController
{

    public function index()
    {
        $this->setPageTitle(OW::getLanguage()->text('zlareas', 'index_page_title'));
        $this->setPageHeading(OW::getLanguage()->text('zlareas', 'index_page_heading'));
    	
    	$areainfos = array();
    	$areas = ZLAREAS_BOL_Service::getInstance()->getAreaList();
    	foreach ( $areas as $area )
    	{
    		$areainfos[$area->id]['areacode'] = $area->areacode;
    		$areainfos[$area->id]['province'] = $area->province;
    		$areainfos[$area->id]['city'] = $area->city;
    		$areainfos[$area->id]['area'] = $area->area;
    	}
    	
    	$this->assign('areas', $areainfos);

    }

    private function text( $prefix, $key, array $vars = null )
    {
        return OW::getLanguage()->text($prefix, $key, $vars);
    }
}