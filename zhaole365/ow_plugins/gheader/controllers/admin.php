<?php

/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package gheader.controllers
 */
class GHEADER_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    private $activePlugins = array();

    public function __construct()
    {
        parent::__construct();

        $this->activePlugins = array(
            'photo' => OW::getPluginManager()->isPluginActive('photo'),
            'groups' => OW::getPluginManager()->isPluginActive('groups'),
        );
    }

    public function index()
    {
        OW::getDocument()->setHeading(OW::getLanguage()->text('gheader', 'heading_configuration'));
        OW::getDocument()->setHeadingIconClass('ow_ic_gear_wheel');

        $this->assign('activePlugins', $this->activePlugins);

        if ( !$this->activePlugins['groups'] )
        {
            return;
        }

        $groupSettingUrl = OW::getRouter()->urlForRoute('groups-admin-widget-panel');
        $this->assign('groupsSettings', $groupSettingUrl);

        $screensUrl = OW::getPluginManager()->getPlugin('gheader')->getStaticUrl() . 'admin/';
        $this->assign('screensUrl', $screensUrl);
    }
}
