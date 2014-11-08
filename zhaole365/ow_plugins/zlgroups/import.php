<?php

class ZLGROUPS_Import extends DATAIMPORTER_CLASS_Import
{
    public function import( $params )
    {
        $importDir = $params['importDir'];
        $configFile = $importDir . 'config.txt';
        $service = ZLGROUPS_BOL_Service::getInstance();

        $string = file_get_contents($configFile);
        $configs = json_decode($string, true);
        $sourceDirUrl = $configs['dirUrl'];
        $counter = 0;
        while (true)
        {
            $list = $service->findGroupList(ZLGROUPS_BOL_Service::LIST_ALL, $counter, 100);
            if ( empty($list) )
            {
                break;
            }
            $counter += 100;

            foreach ( $list as $dto )
            {
                $fileName = $service->getGroupImageFileName($dto);
                if ( $fileName === null )
                {
                    continue;
                }

                $sourceFileUrl = $sourceDirUrl . '/' . $fileName;
                $content = file_get_contents($sourceFileUrl);
                $distFilePath = $service->getGroupImagePath($dto);

                if ( !empty($content) )
                {
                    OW::getStorage()->fileSetContent($distFilePath, $content);
                }
            }
        }
    }
}
