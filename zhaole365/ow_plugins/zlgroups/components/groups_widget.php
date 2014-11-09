<?php

class ZLGROUPS_CMP_GroupsWidget extends BASE_CLASS_Widget
{

    private $service;
    
    private $showCreate = true;

    // 构造函数
    // BASE_CLASS_WidgetParameter 为widget参数类
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        $this->service = ZLGROUPS_BOL_Service::getInstance();

        // 判断当前用户是否具有创建乐群权限，如果有显示创建乐群按钮
        if ( !$this->service->isCurrentUserCanCreate() )
        {
            $authStatus = BOL_AuthorizationService::getInstance()->getActionStatus('zlgroups', 'create');
            $this->showCreate = $authStatus['status'] == BOL_AuthorizationService::STATUS_PROMOTED;

            if ( $this->showCreate ) // 显示创建乐群按钮
            {
                $script = UTIL_JsGenerator::composeJsString('$("#groups-create-btn-c a").click(function(){
                    OW.authorizationLimitedFloatbox({$msg});
                    return false;
                });', array(
                    "msg" => $authStatus["msg"]
                ));
                OW::getDocument()->addOnloadScript($script);
            }
        }

        // 获取widget设置的显示个数
        $num = isset($paramObj->customParamList['count']) ? (int) $paramObj->customParamList['count'] : 8;

        // 设置widget的显示标题
        $this->assign('showTitles', !empty($paramObj->customParamList['showTitles']));

        // 获得设置个数的最新和最流行乐群列表
        $latest = $this->service->findGroupList(ZLGROUPS_BOL_Service::LIST_LATEST, 0, $num);
        $popular = $this->service->findGroupList(ZLGROUPS_BOL_Service::LIST_MOST_POPULAR, 0, $num);

        // 构建latest 和 popular的工具栏项
        $toolbars = self::getToolbar();

        $lang = OW::getLanguage();
        $menuItems = array();

        // 构造$latest乐群列表信息，如果不为空，则创建菜单项
        if ( $this->assignList('latest', $latest) )
        {
            $this->setSettingValue(self::SETTING_TOOLBAR, $toolbars['latest']);
            $menuItems[] = array(
                'label' => $lang->text('zlgroups', 'group_list_menu_item_latest'),
                'id' => 'groups-widget-menu-latest',
                'contId' => 'groups-widget-latest',
                'active' => true
            );
        }

        // 构造$popular乐群列表信息，如果不为空，则创建菜单项
        if ( $this->assignList('popular', $popular) )
        {
            $menuItems[] = array(
                'label' => $lang->text('zlgroups', 'group_list_menu_item_popular'),
                'id' => 'groups-widget-menu-popular',
                'contId' => 'groups-widget-popular',
                'active' => empty($menuItems)
            );
        }

        if ( empty($menuItems) && !$this->showCreate )
        {
            $this->setVisible(false);

            return;
        }

        // 设置菜单变量menuItems，以供显示部分使用
        $this->assign('menuItems', $menuItems);

        // 如果乐群列表页面是用户定制的，则设置菜单变量menu为空；
        // 否则，使用BASE_CMP_WidgetMenu组件
        if ( $paramObj->customizeMode )
        {
            $this->assign('menu', '');
        }
        else
        {
            $this->addComponent('menu', new BASE_CMP_WidgetMenu($menuItems));
        }

        // 设置toolbars变量，以供显示部分使用
        $this->assign('toolbars', $toolbars);
        
        // 设置createUrl变量，以供显示部分使用
        $this->assign('createUrl', OW::getRouter()->urlForRoute('zlgroups-create'));
    }

    // 构造需要显示列表所需的信息
    private function assignList( $listName, $list )
    {
        $groupIdList = array();

        foreach ( $list as $item )
        {
            $groupIdList[] = $item->id;
        }

        $userCountList = $this->service->findUserCountForList($groupIdList);

        $tplList = array();
        foreach ( $list as $item )
        {
        	// TBD － 该部分信息可以扩充，
        	// - 显示模板 ： 指定列表显示形式 （该部分不应该在这里，应该根据用户喜好设置获得，在widget构造中获得）
        	// - 流行度
        	// - 
            $tplList[] = array(
                'image' => $this->service->getGroupImageUrl($item),
                'title' => htmlspecialchars($item->title),
                'url' => OW::getRouter()->urlForRoute('zlgroups-view', array('groupId' => $item->id)),
                'users' => $userCountList[$item->id]
            );
        }

        $this->assign($listName, $tplList);

        return!empty($tplList);
    }

    // 构建latest 和 popular的工具栏项
    private static function getToolbar()
    {
        $lang = OW::getLanguage();

        // 构建 latest 工具栏项目信息
        $toolbars['latest'] = array();
        $showCreate = true;
        if ( !ZLGROUPS_BOL_Service::getInstance()->isCurrentUserCanViewList() )
        {
            $authStatus = BOL_AuthorizationService::getInstance()->getActionStatus('zlgroups', 'create');
            $showCreate = $authStatus['status'] == BOL_AuthorizationService::STATUS_PROMOTED;
        }
        
        if ( $showCreate )
        {
            $toolbars['latest'][] = array(
                'href' => OW::getRouter()->urlForRoute('zlgroups-create'),
                'label' => $lang->text('zlgroups', 'add_new'),
                "id" => "groups-create-btn-c"
            );
        }

        $toolbars['latest'][] = array(
            'href' => OW::getRouter()->urlForRoute('zlgroups-latest'),
            'label' => $lang->text('base', 'view_all')
        );

        // 构建 popular 工具栏项目信息
        $toolbars['popular'] = array();

        if ( $showCreate )
        {
            $toolbars['popular'][] = array(
                'href' => OW::getRouter()->urlForRoute('zlgroups-create'),
                'label' => $lang->text('zlgroups', 'add_new'),
                "id" => "groups-create-btn-c"
            );
        }

        $toolbars['popular'][] = array(
            'href' => OW::getRouter()->urlForRoute('zlgroups-most-popular'),
            'label' => $lang->text('base', 'view_all')
        );

        return $toolbars;
    }

    // 用于设置信息
    public static function getSettingList()
    {
        $settingList = array();

        $settingList['count'] = array(
            'presentation' => self::PRESENTATION_NUMBER,
            'label' => OW::getLanguage()->text('zlgroups', 'widget_groups_count_setting'),
            'value' => 3
        );

        $settingList['showTitles'] = array(
            'presentation' => self::PRESENTATION_CHECKBOX,
            'label' => OW::getLanguage()->text('zlgroups', 'widget_groups_show_titles_setting'),
            'value' => true
        );

        return $settingList;
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_TITLE => OW::getLanguage()->text('zlgroups', 'widget_groups_title'),
            self::SETTING_ICON => self::ICON_COMMENT,
            self::SETTING_SHOW_TITLE => true
        );
    }
}