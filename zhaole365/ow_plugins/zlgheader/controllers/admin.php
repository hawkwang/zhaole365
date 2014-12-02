<?php

class ZLGHEADER_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    private $activePlugins = array();

    public function __construct()
    {
        parent::__construct();

        $this->activePlugins = array(
            'photo' => OW::getPluginManager()->isPluginActive('photo'),
            'groups' => OW::getPluginManager()->isPluginActive('zlgroups'),
        );
    }

    public function index()
    {
        OW::getDocument()->setHeading(OW::getLanguage()->text('zlgheader', 'heading_configuration'));
        OW::getDocument()->setHeadingIconClass('ow_ic_gear_wheel');

        $this->assign('activePlugins', $this->activePlugins);

        if ( !$this->activePlugins['groups'] )
        {
            return;
        }

        $groupSettingUrl = OW::getRouter()->urlForRoute('zlgroups-admin-widget-panel');
        $this->assign('groupsSettings', $groupSettingUrl);

        $screensUrl = OW::getPluginManager()->getPlugin('zlgheader')->getStaticUrl() . 'admin/';
        $this->assign('screensUrl', $screensUrl);
    }
}
