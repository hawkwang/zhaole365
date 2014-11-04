<?php

/**
 * Copyright (c) 2011, Oxwall CandyStore
 * All rights reserved.

 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is
 * licensed under The BSD license.
 */

/**
 * Rate total score component
 * 
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_topusers.components
 * @since 1.2.6
 */
class OCSTOPUSERS_CMP_TotalScore extends OW_Component
{
    public function __construct( $entityId, $entityType, $maxRate = 5 )
    {
        parent::__construct();

        $service = BOL_RateService::getInstance();

        $info = $service->findRateInfoForEntityItem($entityId, $entityType);

        $info['width'] = !isset($info['avg_score']) ? null : (int) floor((float) $info['avg_score'] / $maxRate * 100);
        $info['avgScore'] = !isset($info['avg_score']) ? 0 : round($info['avg_score'], 2);
        $info['ratesCount'] = !isset($info['rates_count']) ? 0 : (int) $info['rates_count'];

        $this->assign('info', $info);
    }
}