<?php

class ZLGROUPS_Export extends DATAEXPORTER_CLASS_Export
{
    public function excludeTableList()
    {
        return array();
    }

    public function includeTableList()
    {
        return array();
    }

    public function export( $params )
    {
        /* @var $za ZipArchives */
        $za = $params['zipArchive'];
        $archiveDir = $params['archiveDir'];
        $plugin = OW::getPluginManager()->getPlugin('zlgroups');

        $string = json_encode(array(
            'dirUrl' => OW::getStorage()->getFileUrl($plugin->getUserFilesDir())
        ));
        
        $za->addFromString($archiveDir . '/' . 'config.txt', $string);
    }
}