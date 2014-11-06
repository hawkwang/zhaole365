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
 * @package ow_plugins.location_tag.map.controllers
 * @since 1.0
 */


class LOCATIONTAG_MCTRL_Map extends OW_MobileActionController
{    
    public function map( $params )
    {
        if ( empty($params['tagId']) )
        {
            throw new Redirect404Exception();
        }

        OW::getEventManager()->trigger(new OW_Event('locationtag.add_js_lib'));
        
        $tagId = !empty($params['tagId']) ? $params['tagId'] : null;

        /* @var $location LOCATIONTAG_BOL_Location */
        $location = LOCATIONTAG_BOL_LocationService::getInstance()->findById($tagId);;

        if ( empty($location) )
        {
            throw new Redirect404Exception();
        }
        
        $this->setPageHeading($location->address);
        OW::getDocument()->setTitle($location->address);
        $this->setPageHeadingIconClass('ow_ic_bookmark');
        
        $map = new LOCATIONTAG_CMP_Map();
        $map->setHeight('300px');
        $map->setMapOption('scrollwheel', 'true');
        $map->setBounds($location->southWestLat, $location->southWestLng, $location->northEastLat, $location->northEastLng);
        
        $locationArray = get_object_vars($location);
        
        $map->addPoint($locationArray);

        $this->addComponent("map", $map);
    }
}
