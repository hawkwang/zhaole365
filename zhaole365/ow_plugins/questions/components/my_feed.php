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
class QUESTIONS_CMP_MyFeed extends QUESTIONS_CMP_Feed
{
    public function findFeed( $startStamp, $count, $questionIds, $order )
    {
        if ( $order == QUESTIONS_CMP_Feed::ORDER_LATEST )
    {
        return $this->service->findMyFeed($startStamp, $this->userId, $count, $questionIds);
    }

        return $this->service->findOrderedMyFeed($startStamp, $this->userId, $count, $questionIds, array(
            QUESTIONS_BOL_FeedService::ACTIVITY_ANSWER,
            QUESTIONS_BOL_FeedService::ACTIVITY_POST
        ));
    }

    public function findActivity( $startStamp, $questionIds )
    {
        return $this->service->findMyActivity($startStamp, $this->userId, $questionIds);
    }

    public function findFeedCount( $startStamp )
    {
        return $this->service->findMyFeedCount($startStamp, $this->userId);
    }

    public function getBubbleActivityList( $activityList )
    {
        $out = array();
        foreach ( $activityList as $questionId => $activity )
        {
            foreach ( $activity as $item )
            {
                if ( $item->userId == $this->userId )
                {
                    $out[$questionId] = $item;

                    break;
                }
            }
        }

        return $out;
    }

    /*public function getBubbleActivityList( $activityList )
    {
        $prior = array(
            QUESTIONS_BOL_FeedService::ACTIVITY_CREATE => 0,
            QUESTIONS_BOL_FeedService::ACTIVITY_FOLLOW => 1,
            QUESTIONS_BOL_FeedService::ACTIVITY_ANSWER => 2,
            QUESTIONS_BOL_FeedService::ACTIVITY_POST => 2
        );

        $tmp = array();
        $out = array();
        foreach ( $activityList as $questionId => $activity )
        {
            foreach ( $activity as $item )
            {
                if ( $item->userId == $this->userId )
                {
                    $tmp[$questionId][$prior[$item->activityType]][] = $item;
                }
            }
        }

        foreach ( $tmp as $questionId => $item )
        {
            ksort($item);
            $priorActivities = reset($item);
            $out[$questionId] = reset($priorActivities);
        }

        return $out;
    }*/
}

