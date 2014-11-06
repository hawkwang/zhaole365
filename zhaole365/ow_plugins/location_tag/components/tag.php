<?php

/**
 * Copyright (c) 2013, Podyachev Evgeny <joker.OW2@gmail.com>
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */
/**
 * @author Podyachev Evgeny <joker.OW2@gmail.com>
 * @package ow_plugins.location_tag.components
 * @since 1.0
 */

class LOCATIONTAG_CMP_Tag extends OW_Component
{
    protected $buttonId = null;
    protected $uniqueId = null;
    protected $themeImagesUrl = null;


    public function __construct( $params = array() )
    {
        parent::__construct();
        $this->uniqueId = 'location_tag'. uniqid(rand(0, 999999999));
        $this->themeImagesUrl = OW::getThemeManager()->getCurrentTheme()->getStaticImagesUrl();
        $this->hiddenFieldId = 'location_tag_data_' . $this->uniqueId;
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();
        
        OW::getDocument()->addStyleSheet(OW::getPluginManager()->getPlugin('locationtag')->getStaticCssUrl() . 'smoothness/jquery-ui-1.10.3.custom.min.css');
//         OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('locationtag')->getStaticJsUrl() . 'location_tag_1.js', 'text/javascript', LOCATIONTAG_BOL_LocationService::JQUERY_LOAD_PRIORITY+1);
//        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('locationtag')->getStaticJsUrl() . 'location_tag.js', 'text/javascript', LOCATIONTAG_BOL_LocationService::JQUERY_LOAD_PRIORITY+1);
//         OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('locationtag')->getStaticJsUrl() . 'location_tag_baidu.js', 'text/javascript', LOCATIONTAG_BOL_LocationService::JQUERY_LOAD_PRIORITY+1);
        
//         OW::getDocument()->addOnloadScript('
//             var $form = $(\'form[name="newsfeed_update_status"]\');
//             var $input = $(\'<input id=' . json_encode($this->hiddenFieldId) . ' type=\"hidden\" name=\"location_tag_data\" value=\"\" >\');
//             $form.append($input);

//             window.locationTag = new OW_StatusUpdateLocationTag();
//             window.locationTag.initLocationAutocomplite('.json_encode($this->uniqueId).', '.json_encode($this->hiddenFieldId).'); 
//             window.owForms.newsfeed_update_status.elements["location_tag_data"] = {
//                 id:' . json_encode($this->hiddenFieldId) . ',
//                 input:$input,
//                 name:"location_tag_data",
//                 validators:[],
//                 addValidator:function(){},
//                 getValue:function(){
//                     return $form.find("input[name=\'location_tag_data\']").val();
//                 },
//                 removeErrors:function(){},
//                 resetValue:function(){
//                     $form.find("input[name=\'location_tag_data\']").val("");
//                 },
//                 setValue:function(value){
//                     $form.find("input[name=\'location_tag_data\']").val(value);
//                 },
//                 showError:function(){},
//                 validate:function(){}
//             };

//             window.owForms.newsfeed_update_status.bind("success", function(data)
//             {
//                 window.locationTag.refresh();
//             });
//         ', LOCATIONTAG_BOL_LocationService::JQUERY_LOAD_PRIORITY+1);

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
    	
        $this->assign('uniqueId', $this->uniqueId);
        $this->assign('themeImagesUrl', $this->themeImagesUrl);
    }
}
