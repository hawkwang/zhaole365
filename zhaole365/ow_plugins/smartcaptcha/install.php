<?php

/**
 * Copyright (c) 2014, Kairat Bakytow
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

$sql = '
DROP TABLE IF EXISTS `' . OW_DB_PREFIX . 'smartcaptcha_questions`;
CREATE TABLE `' . OW_DB_PREFIX . 'smartcaptcha_questions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `question` varchar(128) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

INSERT INTO `' . OW_DB_PREFIX . 'smartcaptcha_questions` (`question`) VALUES
("How many parts in the movie The Lord of the Rings?"),
("The President of the United States"),
("Hulk, Iron Man, Thor, Captain America, etc. in one film"),
("How many days in a leap year?"),
("Year of the birth of Tom Cruise"),
("How many books about Harry Potter?"),
("Human best friend"),
("The biggest continent"),
("The longest river in the world"),
("The capital of Brazil"),
("How many hours in a day"),
("The founder of Facebook"),
("The first president of the United States") ; 

DROP TABLE IF EXISTS `' . OW_DB_PREFIX . 'smartcaptcha_answers`;
CREATE TABLE `' . OW_DB_PREFIX . 'smartcaptcha_answers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `idQuestion` int(11) NOT NULL,
  `answer` varchar(50) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idQuestion` (`idQuestion`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 ;

INSERT INTO `' . OW_DB_PREFIX . 'smartcaptcha_answers` (`idQuestion`, `answer`) VALUES
(1, "Three"),
(1, "Три"),
(1, "3"),
(2, "Барак Обама"),
(2, "Barack Obama"),
(3, "Avengers"),
(3, "Мстители"),
(4, "тристо шестьдесят шесть"),
(4, "three hundred sixty-six"),
(4, "366"),
(5, "Nineteen sixty-second"),
(5, "Тысяча девятьсот шестьдесят второй"),
(5, "1962"),
(6, "Seven"),
(6, "Семь"),
(6, "7"),
(7, "Dog"),
(7, "Собака"),
(8, "Евразия"),
(8, "Eurasia"),
(9, "Нил"),
(9, "Nile"),
(10, "Бразилиа"),
(10, "Brazilia"),
(11, "24"),
(11, "Двадцать четыре"),
(11, "twenty-four"),
(12, "Марк Цукерберг"),
(12, "Mark Zuckerberg"),
(13, "George Washington"),
(13, "Джордж Вашингтон") ; ';

OW::getDbo()->query( $sql );

OW::getPluginManager()->addPluginSettingsRouteName('smartcaptcha', 'smartcaptcha.admin');

$config = OW::getConfig();

if ( !$config->configExists('smartcaptcha', 'imgWidth') )
{
    $config->addConfig( 'smartcaptcha', 'imgWidth', 600 );
}

if ( !$config->configExists('smartcaptcha', 'imgHeight') )
{
    $config->addConfig( 'smartcaptcha', 'imgHeight', 50 );
}

if ( !$config->configExists('smartcaptcha', 'imgPerturbation') )
{
    $config->addConfig( 'smartcaptcha', 'imgPerturbation', 0 );
}

if ( !$config->configExists('smartcaptcha', 'imgImageBgColor') )
{
    $config->addConfig( 'smartcaptcha', 'imgImageBgColor', '#054487' );
}

if ( !$config->configExists('smartcaptcha', 'imgTextAngleMinimum') )
{
    $config->addConfig( 'smartcaptcha', 'imgTextAngleMinimum', 0 );
}

if ( !$config->configExists('smartcaptcha', 'imgTextAngleMaximum') )
{
    $config->addConfig( 'smartcaptcha', 'imgTextAngleMaximum', 0 );
}

if ( !$config->configExists('smartcaptcha', 'imgUseTransparentText') )
{
    $config->addConfig( 'smartcaptcha', 'imgUseTransparentText', true );
}

if ( !$config->configExists('smartcaptcha', 'imgTextTransparencyPercentage') )
{
    $config->addConfig( 'smartcaptcha', 'imgTextTransparencyPercentage', 0 );
}

if ( !$config->configExists('smartcaptcha', 'imgNumLines') )
{
    $config->addConfig( 'smartcaptcha', 'imgNumLines', 0 );
}

if ( !$config->configExists('smartcaptcha', 'imgLineColor') )
{
    $config->addConfig( 'smartcaptcha', 'imgLineColor', '#7B92AA' );
}

if ( !$config->configExists('smartcaptcha', 'imgTextColor') )
{
    $config->addConfig( 'smartcaptcha', 'imgTextColor', '#ffffff' );
}

OW::getLanguage()->importPluginLangs( OW::getPluginManager()->getPlugin('smartcaptcha')->getRootDir() . 'langs.zip', 'smartcaptcha' );
