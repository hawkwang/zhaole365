<?php

class ZLGROUPS_CMP_BriefInfoContent extends OW_Component
{

    /**
     * @return Constructor.
     */
    public function __construct( $groupId )
    {
        parent::__construct();

        $service = ZLGROUPS_BOL_Service::getInstance();
        $groupDto = $service->findGroupById($groupId);

        $group = array(
            'title' => htmlspecialchars($groupDto->title),
            'description' => $groupDto->description,
            'time' => $groupDto->timeStamp,
            'imgUrl' => empty($groupDto->imageHash) ? false : $service->getGroupImageUrl($groupDto),
            'url' => OW::getRouter()->urlForRoute('zlgroups-view', array('groupId' => $groupDto->id)),
            "id" => $groupDto->id
        );

        $imageUrl = empty($groupDto->imageHash) ? '' : $service->getGroupImageUrl($groupDto);
        OW::getDocument()->addMetaInfo('image', $imageUrl, 'itemprop');
        OW::getDocument()->addMetaInfo('og:image', $imageUrl, 'property');

        $createDate = UTIL_DateTime::formatDate($groupDto->timeStamp);
        $adminName = BOL_UserService::getInstance()->getDisplayName($groupDto->userId);
        $adminUrl = BOL_UserService::getInstance()->getUserUrl($groupDto->userId);

        $js = UTIL_JsGenerator::newInstance()
                ->jQueryEvent('#groups_toolbar_flag', 'click', UTIL_JsGenerator::composeJsString('OW.flagContent({$entity}, {$id}, {$title}, {$href}, "zlgroups+flags", {$ownerId});',
                        array(
                            'entity' => ZLGROUPS_BOL_Service::WIDGET_PANEL_NAME,
                            'id' => $groupDto->id,
                            'title' => $group['title'],
                            'href' => $group['url'],
                            'ownerId' => $groupDto->userId
                        )));

        OW::getDocument()->addOnloadScript($js, 1001);

        $toolbar = array(
            array(
                'label' => OW::getLanguage()->text('zlgroups', 'widget_brief_info_create_date', array('date' => $createDate))
            ),
            array(
                'label' => OW::getLanguage()->text('zlgroups', 'widget_brief_info_admin', array('name' => $adminName, 'url' => $adminUrl))
            ));

        if ( $service->isCurrentUserCanEdit($groupDto) )
        {
            $toolbar[] = array(
                'label' => OW::getLanguage()->text('zlgroups', 'edit_btn_label'),
                'href' => OW::getRouter()->urlForRoute('zlgroups-edit', array('groupId' => $groupId))
            );
        }

        if ( OW::getUser()->isAuthenticated() && OW::getUser()->getId() != $groupDto->userId )
        {
            $toolbar[] = array(
                'label' => OW::getLanguage()->text('base', 'flag'),
                'href' => 'javascript://',
                'id' => 'groups_toolbar_flag'
            );
        }

        $event = new BASE_CLASS_EventCollector('zlgroups.on_toolbar_collect', array('groupId' => $groupId));
        OW::getEventManager()->trigger($event);

        foreach ( $event->getData() as $item )
        {
            $toolbar[] = $item;
        }

        $this->assign('toolbar', $toolbar);

        $this->assign('group', $group);
    }
}