<?php

/**
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package questions.components
 */
class QUESTIONS_CMP_FollowList extends BASE_CMP_FloatboxUserList
{
    public function __construct($questionId, $context, $hidden)
    {
        $service = QUESTIONS_BOL_Service::getInstance();

        $context = empty($context) ? array() : $context;

        $follows = $service->findFollows($questionId, $context, $hidden);

        $userIds = array();
        foreach ( $follows as $follow )
        {
            $userIds[] = (int) $follow->userId;
        }

        parent::__construct($userIds);

        $this->setTemplate(OW::getPluginManager()->getPlugin('base')->getCmpViewDir() . 'floatbox_user_list.html');
    }
}