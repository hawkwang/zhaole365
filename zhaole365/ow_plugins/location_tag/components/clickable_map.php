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

class LOCATIONTAG_CMP_ClickableMap extends LOCATIONTAG_CMP_Map
{
    public function __construct( $params = array() )
    {
        $this->setTemplate(OW::getPluginManager()->getPlugin('locationtag')->getCmpViewDir()."map.html");
        parent::__construct( $params );
    }
    
    public function initialize()
    {
        parent::initialize();

        /*$script = "$( document ).ready(function(){
            var map = window.map[".(json_encode($this->name))."].getMap();
            var markerList = [];
                
            google.maps.event.addListener(map, 'click', function(e) {
                placeMarker(e.latLng, map);
            });

            function placeMarker(position, map) {
              marker = new google.maps.Marker({
                position: position,
                map: map
              });

                for (var i = 0; i < markerList.length; i++ ) {
                  markerList[i].setMap(null);
                }

              markerList = [];
              markerList.push(marker);
              map.panTo(position);
            }

           }); "; */
        
        $script = "$( document ).ready(function(){
            var map = window.map[".(json_encode($this->name))."].getMap();
            var markerList = [];
            var infoWindowList = [];
            
            var node = $('<div class=\'status_update771\'>test</div>');
                
            google.maps.event.addListener(map, 'click', function(e) {
                placeMarker(e.latLng, map);
            });

            function placeMarker(position, map) {
              marker = new google.maps.Marker({
                position: position,
                map: map
              });

              for (var i = 0; i < markerList.length; i++ ) {
                  markerList[i].setMap(null);
              }

              markerList = [];
              markerList.push(marker); 
              
              for (var i = 0; i < infoWindowList.length; i++ ) {
                  infoWindowList[i].close();
                  google.maps.event.clearListeners(infoWindowList[i], 'closeclick');
              }

              OW.loadComponent('NEWSFEED_CMP_UpdateStatus', ['map_status_update_1', 'user', 1] , node);

              var infoWindow = window.map[".(json_encode($this->name))."].createInfoWindow(node.html());
              infoWindow.open(map, marker)
              
              infoWindowList = []
              infoWindowList.push(infoWindow);

              google.maps.event.addListener(infoWindow, 'closeclick', function() {
                    infoWindow.close();
                    marker.setMap(null);
              });
              
              

              map.panTo(position);
            }

           }); ";
        
               /* $script = "$( document ).ready(function(){
            var map = window.map[".(json_encode($this->name))."].getMap();
                
            google.maps.event.addListener(map, 'click', function(e) {
                var marker = new google.maps.Marker({
                  position: position,
                  map: map
                });
                map.panTo(position);

                var infoWindow = window.map[".(json_encode($this->name))."].createInfoWindow('<div class='marker'>test</div>');
                infoWindow.open(map, marker)
              });
           }); "; */
        
        OW::getDocument()->addOnloadScript($script);
    }
}