<?php

/**
 * Copyright (c) 2014, Kairat Bakytow
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

$smartcaptcha = OW::getPluginManager()->getPlugin( 'smartcaptcha' );

$staticDir = OW_DIR_STATIC_PLUGIN . $smartcaptcha->getModuleName() . DS;
$staticJsDir = $staticDir  . 'js' . DS;

if ( !file_exists($staticDir) )
{
    mkdir( $staticDir );
    chmod( $staticDir, 0777 );
}

if ( !file_exists($staticJsDir) )
{
    mkdir( $staticJsDir );
    chmod( $staticJsDir, 0777 );
}

@copy( $smartcaptcha->getStaticJsDir() . 'captcha.js', $staticJsDir . 'captcha.js' );
