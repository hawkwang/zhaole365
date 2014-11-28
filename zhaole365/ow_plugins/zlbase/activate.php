<?php


OW::getNavigation()->addMenuItem(
	OW_Navigation::BOTTOM,
	'zlbase.about',
	'zlbase',
	'bottom_menu_item_about',
	OW_Navigation::VISIBLE_FOR_ALL
);

OW::getNavigation()->addMenuItem(
	OW_Navigation::BOTTOM,
	'zlbase.duty',
	'zlbase',
	'bottom_menu_item_duty',
	OW_Navigation::VISIBLE_FOR_ALL
);

OW::getNavigation()->addMenuItem(
	OW_Navigation::BOTTOM,
	'zlbase.agreement',
	'zlbase',
	'bottom_menu_item_agreement',
	OW_Navigation::VISIBLE_FOR_ALL
);

// hacking - 删除下面菜单的广告项 ：－）
//OW::getNavigation()->deleteMenuItem('base', 'openwack');
//OW::getNavigation()->deleteMenuItem('base', 'wackwall');