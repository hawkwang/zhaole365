<?php


$navigation = OW::getNavigation();
$navigation->addMenuItem(
		OW_Navigation::MAIN,
		'zlareas.index',
		'zlareas',
		'main_menu_list',
		OW_Navigation::VISIBLE_FOR_ALL);

// hacking - 删除下面菜单的广告项 ：－）
OW::getNavigation()->deleteMenuItem('base', 'openwack');
OW::getNavigation()->deleteMenuItem('base', 'wackwall');