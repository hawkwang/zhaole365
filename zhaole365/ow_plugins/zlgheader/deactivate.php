<?php

require_once 'plugin.php';

$plugin = ZLGHEADER_Plugin::getInstance();

$credits = new ZLGHEADER_CLASS_Credits();
$credits->triggerCreditActionsAdd();

if ( $plugin->isAvaliable() )
{
    $plugin->fullDeactivate();
}
else
{
   $plugin->shortDeactivate();
}
