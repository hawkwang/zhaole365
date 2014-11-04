<?php

class QUESTIONS_CTRL_Upgrade extends ADMIN_CTRL_Abstract
{
    public function getMenu()
    {
        $item[0] = new BASE_MenuItem(array());

        $item[0]->setLabel(OW::getLanguage()->text('questions', 'admin_general_settings'));
        $item[0]->setIconClass('ow_ic_lens');
        $item[0]->setKey('1');

        $item[0]->setUrl(
            OW::getRouter()->urlForRoute('questions-admin-main')
        );

        $item[0]->setOrder(1);

        $item[1] = new BASE_MenuItem(array());

        $item[1]->setLabel(OW::getLanguage()->text('questions', 'admin_extended_version'));
        $item[1]->setIconClass('ow_ic_star');
        $item[1]->setKey('2');
        $item[1]->setUrl(
            OW::getRouter()->urlForRoute('questions-upgrade')
        );

        $item[1]->setOrder(2);

        return new BASE_CMP_ContentMenu($item);
    }

    public function index()
    {
        if ( isset($_GET['skip']) )
        {
            OW::getConfig()->saveConfig('questions', 'ev_page_visited', 1);

            $this->redirect(OW::getRouter()->urlForRoute('questions-upgrade'));
        }

        OW::getDocument()->setHeading(OW::getLanguage()->text('questions', 'admin_extended_version_page_heading'));
        OW::getDocument()->setHeadingIconClass('ow_ic_lens');

        $this->addComponent('menu', $this->getMenu());

        $this->assign('uniq', uniqid());
    }
}