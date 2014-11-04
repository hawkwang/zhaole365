<?php

/**
 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.

 * ---
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * Redistribution and use in source and binary forms, with or without modification, are permitted provided that the
 * following conditions are met:
 *
 *  - Redistributions of source code must retain the above copyright notice, this list of conditions and
 *  the following disclaimer.
 *
 *  - Redistributions in binary form must reproduce the above copyright notice, this list of conditions and
 *  the following disclaimer in the documentation and/or other materials provided with the distribution.
 *
 *  - Neither the name of the Oxwall Foundation nor the names of its contributors may be used to endorse or promote products
 *  derived from this software without specific prior written permission.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

/**
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package questions.components
 */
class QUESTIONS_CMP_Avatars extends OW_Component
{

    /**
     * Constructor.
     *
     * @param array $idList
     */
    public function __construct( array $idList, $totalCount )
    {
        parent::__construct();

        $userId = OW::getUser()->getId();
        $hiddenUser = false;
        if ( $userId && !in_array($userId, $idList) )
        {
            $hiddenUser = $userId;
            $idList[] = $userId;
        }

        $users = BOL_AvatarService::getInstance()->getDataForUserAvatars($idList, true, true, true, false);

        if ($hiddenUser)
        {
            $users[$hiddenUser]['id'] = $hiddenUser;
            $this->assign('hiddenUser', $users[$hiddenUser]);
            unset($users[$hiddenUser]);
        }

        $count = count($users);
        $otherCount = $totalCount - ($count > 3 ? 3 : $count);
        $otherCount = $otherCount < 0 ? 0 : $otherCount;

        $this->assign('otherCount', $otherCount);

        $this->assign('users', $users);

        $staticUrl = OW::getPluginManager()->getPlugin('questions')->getStaticUrl();
        $this->assign('staticUrl', $staticUrl);

    }

}