<?php

OW::getNavigation()->deleteMenuItem('zlgroups', 'main_menu_list');

BOL_ComponentAdminService::getInstance()->deleteWidget('ZLGROUPS_CMP_UserGroupsWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('ZLGROUPS_CMP_GroupsWidget');