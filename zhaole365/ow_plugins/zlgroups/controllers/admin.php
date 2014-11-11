<?php

class ZLGROUPS_CTRL_Admin extends ADMIN_CTRL_Abstract
{

    public function getMenu()
    {
        $item[0] = new BASE_MenuItem(array());

        $item[0]->setLabel(OW::getLanguage()->text('zlgroups', 'general_settings'));
        $item[0]->setIconClass('ow_ic_dashboard');
        $item[0]->setKey('1');

        $item[0]->setUrl(
            OW::getRouter()->urlForRoute('zlgroups-admin-widget-panel')
        );

        $item[0]->setOrder(1);

        $item[1] = new BASE_MenuItem(array());

        $item[1]->setLabel(OW::getLanguage()->text('zlgroups', 'additional_features'));
        $item[1]->setIconClass('ow_ic_files');
        $item[1]->setKey('2');
        $item[1]->setUrl(
            OW::getRouter()->urlForRoute('zlgroups-admin-additional-features')
        );

        $item[1]->setOrder(2);

        return new BASE_CMP_ContentMenu($item);
    }

    public function panel()
    {

        $componentService = BOL_ComponentAdminService::getInstance();

        $this->setPageHeading(OW::getLanguage()->text('zlgroups', 'widgets_panel_heading'));
        $this->setPageHeadingIconClass('ow_ic_dashboard');

        $place = ZLGROUPS_BOL_Service::WIDGET_PANEL_NAME;

        $dbSettings = $componentService->findAllSettingList();

        $dbPositions = $componentService->findAllPositionList($place);

        $dbComponents = $componentService->findPlaceComponentList($place);
        $activeScheme = $componentService->findSchemeByPlace($place);
        $schemeList = $componentService->findSchemeList();

        if ( empty($activeScheme) && !empty($schemeList) )
        {
            $activeScheme = reset($schemeList);
        }

        $componentPanel = new ADMIN_CMP_DragAndDropAdminPanel($place, $dbComponents);
        $componentPanel->setPositionList($dbPositions);
        $componentPanel->setSettingList($dbSettings);
        $componentPanel->setSchemeList($schemeList);


        if ( !empty($activeScheme) )
        {
            $componentPanel->setScheme($activeScheme);
        }

        $menu = $this->getMenu();

        $this->addComponent('menu', $menu);

        $this->assign('componentPanel', $componentPanel->render());
    }

    // 关联论坛，在additional view脚本中被调用
    public function connect_forum()
    {
        $config = OW::getConfig();
        $language = OW::getLanguage();

        if ( $_GET['isForumConnected'] === 'yes' && !OW::getConfig()->getValue('zlgroups', 'is_forum_connected') )
        {
            try
            {
                OW::getAuthorization()->addAction('zlgroups', 'add_topic');
            }
            catch ( Exception $e ){}

            // Add forum section
            $event = new OW_Event('forum.create_section', array('name' => 'Zlgroups', 'entity' => 'zlgroups', 'isHidden' => true));
            OW::getEventManager()->trigger($event);

            // Add widget
            $event = new OW_Event('forum.add_widget', array('place' => ZLGROUPS_BOL_Service::WIDGET_PANEL_NAME, 'section' => BOL_ComponentAdminService::SECTION_RIGHT));
            OW::getEventManager()->trigger($event);

            $groupsService = ZLGROUPS_BOL_Service::getInstance();

            $groupList = $groupsService->findGroupList(ZLGROUPS_BOL_Service::LIST_ALL);
            if ( !empty($groupList) )
            {
                foreach ( $groupList as $group )
                {
                    // Add forum group
                    $event = new OW_Event('forum.create_group', array('entity' => 'zlgroups', 'name' => $group->title, 'description' => $group->description, 'entityId' => $group->getId()));
                    OW::getEventManager()->trigger($event);
                }
            }

            $config->saveConfig('zlgroups', 'is_forum_connected', 1);
            OW::getFeedback()->info($language->text('zlgroups', 'forum_connected'));
        }

        $redirectURL = OW::getRouter()->urlForRoute('zlgroups-admin-widget-panel');
        $this->redirect($redirectURL);
    }

    // 其他配置信息
    public function additional()
    {
        $this->setPageHeading(OW::getLanguage()->text('zlgroups', 'widgets_panel_heading'));
        $this->setPageHeadingIconClass('ow_ic_dashboard');

        $is_forum_connected = OW::getConfig()->getValue('zlgroups', 'is_forum_connected');

        if ( OW::getPluginManager()->isPluginActive('forum') || $is_forum_connected )
        {
            $this->assign('isForumConnected', $is_forum_connected);
            $this->assign('isForumAvailable', true);
        }
        else
        {
            $this->assign('isForumAvailable', false);
        }

        $menu = $this->getMenu();
        $this->addComponent('menu', $menu);

        if ( OW::getConfig()->getValue('zlgroups', 'restore_groups_forum') )
        {
            // Add forum section
            $event = new OW_Event('forum.create_section', array('name' => 'Zlgroups', 'entity' => 'zlgroups', 'isHidden' => true));
            OW::getEventManager()->trigger($event);

            $groupsService = ZLGROUPS_BOL_Service::getInstance();

            $groupList = $groupsService->findGroupList(ZLGROUPS_BOL_Service::LIST_ALL);
            if ( !empty($groupList) )
            {
                foreach ( $groupList as $group )
                {
                    // Add forum group
                    $event = new OW_Event('forum.create_group', array('entity' => 'zlgroups', 'name' => $group->title, 'description' => $group->description, 'entityId' => $group->getId()));
                    OW::getEventManager()->trigger($event);
                }
            }

            OW::getConfig()->saveConfig('zlgroups', 'restore_groups_forum', 0);
        }
    }

    public function uninstall()
    {
        $config = OW::getConfig();

        if ( !$config->configExists('zlgroups', 'uninstall_inprogress') )
        {
            $config->addConfig('zlgroups', 'uninstall_inprogress', 0);
        }

        if ( isset($_POST['action']) && $_POST['action'] == 'delete_content' )
        {
            $config->saveConfig('zlgroups', 'uninstall_inprogress', 1);
            OW::getFeedback()->info(OW::getLanguage()->text('zlgroups', 'plugin_set_for_uninstall'));

            OW::getApplication()->setMaintenanceMode(true);

            $this->redirect();
        }

        $this->setPageHeading(OW::getLanguage()->text('zlgroups', 'page_title_uninstall'));
        $this->setPageHeadingIconClass('ow_ic_delete');

        $inprogress = $config->getValue('zlgroups', 'uninstall_inprogress');
        $this->assign('inprogress', $inprogress);

        $js = new UTIL_JsGenerator();
        $js->jQueryEvent('#btn-delete-content', 'click', 'if ( !confirm("' . OW::getLanguage()->text('zlgroups', 'confirm_delete_groups') . '") ) return false;');

        OW::getDocument()->addOnloadScript($js);
    }
}