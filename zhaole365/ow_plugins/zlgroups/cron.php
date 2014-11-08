<?php
// 处理与"乐群"相关的定时器功能
class ZLGROUPS_Cron extends OW_Cron
{
    const ZLGROUPS_DELETE_LIMIT = 50;

    public function getRunInterval()
    {
        return 1;
    }

    public function run()
    {
        $config = OW::getConfig();

        // check if uninstall is in progress
        if ( !$config->getValue('zlgroups', 'uninstall_inprogress') )
        {
            return;
        }

        if ( !$config->configExists('zlgroups', 'uninstall_cron_busy') )
        {
            $config->addConfig('zlgroups', 'uninstall_cron_busy', 0);
        }

        // check if cron queue is not busy
        if ( $config->getValue('zlgroups', 'uninstall_cron_busy') )
        {
            return;
        }

        $config->saveConfig('zlgroups', 'uninstall_cron_busy', 1);
        $service = ZLGROUPS_BOL_Service::getInstance();

        try
        {
            $groups = $service->findLimitedList(self::ZLGROUPS_DELETE_LIMIT);

            if ( empty($groups) )
            {
                BOL_PluginService::getInstance()->uninstall('zlgroups');
                OW::getApplication()->setMaintenanceMode(false);

                return;
            }

            foreach ( $groups as $group )
            {
                $service->deleteGroup($group->id);
            }

            $config->saveConfig('zlgroups', 'uninstall_cron_busy', 0);
        }
        catch ( Exception $e )
        {
            $config->saveConfig('zlgroups', 'uninstall_cron_busy', 0);

            throw $e;
        }
    }
}