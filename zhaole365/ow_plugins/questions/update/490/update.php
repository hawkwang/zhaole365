<?php

if ( Updater::getConfigService()->configExists('questions', 'ev_page_visited') )
{
    Updater::getConfigService()->saveConfig('questions', 'ev_page_visited', 0);
}
