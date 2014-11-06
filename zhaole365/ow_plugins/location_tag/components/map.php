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

class LOCATIONTAG_CMP_Map extends OW_Component
{
    protected  $name;
    protected  $points = array();
    protected  $centerLatitude;
    protected  $centerLonditude;
    protected  $attributes = array();
    protected  $options = array(
        'zoom' => 1
    );

    protected  $northEastLat;
    protected  $northEastLng;
    protected  $southWestLat;
    protected  $southWestLng;
    protected  $isSetBounds = false;
    protected  $setAutoBounds = false;
    
    protected  $boxLabel = null;
    protected  $boxIcon = "";
    protected  $boxClass = "";
    
    public function __construct( $params = array() )
    {
        $this->attributes['id'] = uniqid('map_'. rand(0,999999));
        $this->name = $this->attributes['id'];
        $this->setWidth('100%');
        $this->setheight('200px');
        
        if ( !empty($params['mapName']) )
        {
            $this->name = $params['mapName'];
        }

        if ( !empty($params['id']) )
        {
            $this->attributes['id'] = $params['id'];
        }

        OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('locationtag')->getStaticJsUrl().'map.js', "text/javascript", LOCATIONTAG_BOL_LocationService::JQUERY_LOAD_PRIORITY+1);
    }

    public function setWidth( $width )
    {
        $this->width = $width;
    }

    public function setHeight( $height )
    {
        $this->height = $height;
    }

    public function getWidth()
    {
        return $this->width;
    }

    public function getHeight()
    {
        return $this->height;
    }

    public function getMapName()
    {
        return $this->name;
    }

    public function getZoom()
    {
        return isset($this->options['zoom']) ? $this->options['zoom'] : 0;
    }

    public function getCenter()
    {
        return array( 'lat' => $this->centerLatitude,
                      'lon' => $this->centerLonditude );
    }

    public function setMapName( $name )
    {
        $this->name = $name;
    }

    public function setZoom( $zoom )
    {
        $this->options['zoom'] = $zoom;
    }

    public function setCenter( $lat, $lon )
    {
        $this->centerLatitude = (float)$lat;
        $this->centerLonditude = (float)$lon;
    }

    public function getBounds( $lat, $lon )
    {
        return array(
            'northEastLat' => $this->northEastLat,
            'northEastLng' => $this->northEastLng,
            'southWestLat' => $this->southWestLat,
            'southWestLng' => $this->southWestLng
        );
    }

    public function setBounds( $swlat, $swlng, $nelat, $nelng )
    {
        $this->northEastLat = (float)$nelat;
        $this->northEastLng = (float)$nelng;
        $this->southWestLat = (float)$swlat;
        $this->southWestLng = (float)$swlng;
        $this->isSetBounds = true;
    }

    public function setMapOption( $key, $value )
    {
        $this->options[$key] = (string)$value;
    }

    public function getMapOption( $key )
    {
        if (  isset($this->options[$key]) )
        {
            return $this->options[$key];
        }

        return null;
    }

    public function setMapOptions( array $options )
    {
        if ( !empty($options) && is_array($options) )
        {
            $this->options = array_merge( $this->options, $options );
        }
    }

    public function getMapOptions()
    {
        return $this->options;
    }

    public function addPoint( $location, $title = '', $windowContent = '', $isOpen = false )
    {
        if ( !empty($location) )
        {
            $this->points[] = array(
                'location' => $location,
                'title' => UTIL_HtmlTag::stripJs($title),
                'content' => UTIL_HtmlTag::stripJs($windowContent),
                'isOpen' => (boolean)$isOpen);
        }
    }

    public function addAttribute( $name, $value )
    {
        if ( !empty($name) )
        {
            $this->attributes[$name] = $value;
        }
    }

    public function getAttribute( $name )
    {
        return !empty($this->attributes[$name]) ? $this->attributes[$name] : null;
    }

    public function initialize()
    {
        $points = "";
        $bounds = " var bounds; ";
        $count = 0;
        
        foreach ( $this->points as $point )
        {
            $points .= " window.map[".(json_encode($this->name))."].addPoint(".((float)$point['location']['lat']).", ".((float)$point['location']['lng']).", ".  json_encode($point['title']).", ".  json_encode($point['content']).", ".  json_encode($point['isOpen'])."); \n";

            if ( $this->setAutoBounds || !$this->isSetBounds )
            {
                $sw = " new google.maps.LatLng(".(float)$point['location']['southWestLat'].",".(float)$point['location']['southWestLng'].") ";
                $ne = " new google.maps.LatLng(".(float)$point['location']['northEastLat'].",".(float)$point['location']['northEastLng'].") ";

                $bound = " new google.maps.LatLngBounds( $sw , $ne ) ";

                if ( $count == 0 )
                {
                    $bounds .= "
                        bounds = new google.maps.LatLngBounds( $sw , $ne );
                     "; 
                }
                else
                {
                    $bounds .= "
                        bounds.union( new google.maps.LatLngBounds( $sw , $ne ) );
                     ";
                }
                
                $count++;
            }
            
        }

        if( $count > 0 )
        {
            $bounds .= "
                        window.map[".(json_encode($this->name))."].fitBounds(bounds);
                     "; 
        }
        
        if( $this->isSetBounds )
        {
            $bounds = "
                var sw = new google.maps.LatLng(".(float)$this->southWestLat.",".(float)$this->southWestLng.");
                var ne = new google.maps.LatLng(".(float)$this->northEastLat.",".(float)$this->northEastLng.");

                var bounds = new google.maps.LatLngBounds(sw, ne);
                window.map[".(json_encode($this->name))."].fitBounds(bounds); ";
        }

        $mapOptions = $this->options;

        $mapOptionsString = " {
                zoom: ".(int)$mapOptions['zoom'].",
                center: latlng,
                mapTypeId: google.maps.MapTypeId.ROADMAP, ";

        unset($mapOptions['zoom']);

        if ( isset($mapOptions['center']) )
        {
            unset($mapOptions['center']);
        }

        if ( isset($mapOptions['mapTypeId']) )
        {
            unset($mapOptions['mapTypeId']);
        }

        foreach( $this->options as $key => $value )
        {
            if ( !empty($value) )
            {
                $mapOptionsString .=  " $key: $value, \n";
            }
        }

        $mapOptionsString .= "}";

        $script = "$( document ).ready(function(){
            var latlng = new google.maps.LatLng(".((float)$this->centerLatitude).", ".((float)$this->centerLonditude).");

            var options = $mapOptionsString;

            window.map[".(json_encode($this->name))."] = new OW_GoogleMap(".json_encode($this->attributes['id']).");
            window.map[".(json_encode($this->name))."].initialize(options);
            {$bounds}
            
            {$points} 
           }); ";
        
        OW::getDocument()->addOnloadScript($script);

        $this->attributes['style'] = (!empty($this->attributes['style']) ? $this->attributes['style'] : "") . "width:".$this->width.";height:".$this->height.";";
        $tag = UTIL_HtmlTag::generateTag('div', $this->attributes, true);

        $this->assign('map', $tag);
    }

    public function onBeforeRender()
    {
        parent::onBeforeRender();
        $this->initialize();
        $this->assign('boxLabel', $this->boxLabel);
        $this->assign('boxIcon', $this->boxIcon);
        $this->assign('boxClass', $this->boxClass);
    }
    
    public function  setBox( $label, $icon = null, $class = null )
    {
        $this->boxLabel = $label;
        $this->boxIcon = $icon;
        $this->boxClass = $class;
    }

    public function setAutoBounds( $value )
    {
        $this->setAutoBounds = (boolean) $value;
    }
}