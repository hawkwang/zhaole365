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

class LOCATIONTAG_CMP_TagButton extends OW_Component
{
    protected $buttonId = null;
    protected $uniqueId = null;
    protected $fieldId = null;
    protected $themeImagesUrl = null;


    public function __construct( $params = array() )
    {
        parent::__construct();
        $this->uniqueId = uniqid(rand(0, 999999999));
        $this->buttonId = 'location_tag_button_' . $this->uniqueId;
        $this->themeImagesUrl = OW::getThemeManager()->getCurrentTheme()->getStaticImagesUrl();
        $this->fieldId = 'location_tag_field_' . $this->uniqueId;
        $this->hiddenFieldId = 'location_tag_data_' . $this->uniqueId;
        //OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('locationtag')->getStaticJsUrl().'map.js');
    }
    
    public function onBeforeRender() {
        parent::onBeforeRender();
        
        OW::getDocument()->addOnloadScript(' window.locationTag = new OW_StatusUpdateLocationTag();');
        
        $attribtes = array(
            "name" => "location_tag",
            "class" => "location_tag_input ow_locationtag_location_input",
            "type" => "text",
            "id" => $this->fieldId
        );
        
        $input = '<div class="location_tag_input_div clearfix" style="display:none;">
            <div class="locationtag_address_icon_div">
                <span class="ic_locationtag_pin locationtag_address_icon"></span>
            </div>'.
            UTIL_HtmlTag::generateTag('input', $attribtes).
        '</div>';
        
        OW::getDocument()->addOnloadScript(' 
                var value = '.  json_encode(OW::getLanguage()->text('locationtag', 'where_are_you')).';
                var $form = $(\'form[name="newsfeed_update_status"]\');
                var input = $( '.  json_encode($input).' );
                var status = "hide";

                $form.append(\'<input id=' . json_encode($this->hiddenFieldId) . ' type=\"hidden\" name=\"location_tag_data\" value=\"\" >\');
                 
                $( "textarea[name=\'status\']", $form ).after(input);   
                window.locationTag.initLocationAutocomplite('.json_encode($this->fieldId).', '.json_encode($this->hiddenFieldId).');

                window.owForms.newsfeed_update_status.elements["location_tag_data"] = {
                    id:' . json_encode($this->hiddenFieldId) . ',
                    input:input,
                    name:"location_tag_data",
                    validators:[],
                    addValidator:function(){},
                    getValue:function(){ return $("#' . $this->hiddenFieldId . '").val(); },
                    removeErrors:function(){},
                    resetValue:function(){},
                    setValue:function(){},
                    showError:function(){},
                    validate:function(){}
                };
                
                $("#'.$this->buttonId.'").click(function() {
                    if ( status == "hide" )
                    {
                        $( ".location_tag_input_div", $form ).show();
                        $( \'input[id='.json_encode($this->fieldId).']\', $form ).focus()
                        status = "show";
                    }
                    else
                    {
                        $( ".location_tag_input_div", $form ).hide();
                        status = "hide";
                    }
                }); ');
        $this->assign('buttonId', $this->buttonId);
        $this->assign('themeImagesUrl', $this->themeImagesUrl);
    }
}