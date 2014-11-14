<?php

OW::getNavigation()->deleteMenuItem('zlevent', 'main_menu_item');

BOL_ComponentAdminService::getInstance()->deleteWidget('ZLEVENT_CMP_UpcomingEventsWidget');
BOL_ComponentAdminService::getInstance()->deleteWidget('ZLEVENT_CMP_ProfilePageWidget');
