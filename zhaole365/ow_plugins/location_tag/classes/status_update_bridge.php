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
 * @package ow_plugins.location_tag.classes
 * @since 1.0
 */
class LOCATIONTAG_CLASS_StatusUpdateBridge {

    public function __construct() {
        
    }

    public function addButton() {
        
        OW::getEventManager()->trigger(new OW_Event('locationtag.add_js_lib'));
        OW::getDocument()->addOnloadScript(" 
             $(document).ready(function(){
                    if( $(\"form[name='newsfeed_update_status']\").length )
                    {
                        OW.loadComponent('LOCATIONTAG_CMP_Tag', {},
                        {
                          onLoad: function(){
                            var change = function() {
                                var data = $(\"input[name='location_tag_data']\").val();
                                var status = $(\"textarea[name='status']\").val();
                                if ( data )
                                {
                                    
                                    $(\"textarea[name='status']\").val(status + ' ');
                                }
                                else
                                {
                                    //$(\"textarea[name='status']\").val($.trim(status));
                                }
                             };

                             $(\"textarea[name='status']\").change( change );
                             $(\"input[name='location_tag_data']\").change( change );
                          },
                          onReady: function( html ){
                             $('.ow_status_update_btn_block').append(html);
                          }
                        });
                    }

                     });
                     ");
    }

    public function addMobileButton() {
        
        OW::getEventManager()->trigger(new OW_Event('locationtag.add_js_lib'));
        OW::getDocument()->addOnloadScript(" 
             $(document).ready(function(){
                    if( $(\"form[name='newsfeed_update_status']\").length )
                    {
                        OW.loadComponent('LOCATIONTAG_CMP_Tag', {},
                        {
                          onLoad: function(){
                            var change = function() {
                                var data = $(\"input[name='location_tag_data']\").val();
                                var status = $(\"textarea[name='status']\").val();
                                
                                if ( data )
                                {
                                    
                                    $(\"textarea[name='status']\").val(status + ' ');
                                }
                                else
                                {
                                    //$(\"textarea[name='status']\").val($.trim(status));
                                }
                             };

                             $(\"textarea[name='status']\").change( change );
                             $(\"input[name='location_tag_data']\").change( change );
                          },
                          onReady: function( html ){
                             $('.owm_newsfeed_status_update_btns').append(html);
                          }
                        });
                    }

                     });
                     ");
    }

    public function init() {
        $this->addButton();
    }

}
