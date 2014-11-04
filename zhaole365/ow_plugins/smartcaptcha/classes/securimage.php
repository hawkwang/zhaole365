<?php

/**
 * Copyright (c) 2014, Kairat Bakytow
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

if ( !defined('SI_IMAGE_JPEG') )
{
    define('SI_IMAGE_JPEG', 1);
}

if( !defined('SI_IMAGE_PNG') )
{
    define('SI_IMAGE_PNG',  2);
}

if ( !defined('SI_IMAGE_GIF') )
{
    define('SI_IMAGE_GIF',  3);
}

/**
 *
 * @author Kairat Bakytow <kainisoft@gmail.com>
 * @package ow_plugins.smartcaptcha.classes
 * @since 1.0
 */
class SMARTCAPTCHA_CLASS_Securimage 
{
    CONST SESSION_NAME = 'smartcaptcha_session_name';
    
    private static $classInstance;
    
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }
    
    private $image_width;
    
    public function getImageWidth()
    {
        return (int)$this->image_width;
    }
    
    public function setImageWidth( $value )
    {
        $this->image_width = (int)$value;
        
        return $this;
    }

    private $image_height;
    
    public function getImageHeight()
    {
        return (int)$this->image_height;
    }
    
    public function setImageHeight( $value )
    {
        $this->image_height = (int)$value;
        
        return $this;
    }

    private $perturbation;
    
    public function getPerturbation()
    {
        return (int)$this->perturbation;
    }
    
    public function setPerturbation( $value )
    {
        $this->perturbation = (float)$value;
        
        return $this;
    }
    
    private $image_bg_color;
    
    public function getImageBgColor()
    {
        return $this->image_bg_color;
    }
    
    public function setImageBgColor( SmartCaptcha_Securimage_Color $value )
    {
        $this->image_bg_color = $value;
        
        return $this;
    }
    
    private $text_angle_minimum;
    
    public function getTextAngleMinimum()
    {
        return (int)$this->text_angle_minimum;
    }
    
    public function setTextAngleMinimum( $value )
    {
        $this->text_angle_minimum = (int)$value;
        
        return $this;
    }
    
    private $text_angle_maximum;
    
    public function getTextAngleMaximum()
    {
        return (int)$this->text_angle_maximum;
    }
    
    public function setTextAngleMaximum( $value )
    {
        $this->text_angle_maximum = (int)$value;
        
        return $this;
    }
    
    private $use_transparent_text;
    
    public function getUseTransparentText()
    {
        return (bool)  $this->use_transparent_text;
    }
    
    public function setUseTransparentText( $value )
    {
        $this->use_transparent_text = (bool)$value;
        
        return $this;
    }
    
    private $text_transparency_percentage;
    
    public function getTextTransparencyPercentage()
    {
        return (int)$this->text_transparency_percentage;
    }
    
    public function setTextTransparencyPercentage( $value )
    {
        $this->text_transparency_percentage = (int)$value;
        
        return $this;
    }
    
    private $num_lines;
    
    public function getNumLines()
    {
        return (int)$this->num_lines;
    }
    
    public function setNumLines( $value )
    {
        $this->num_lines = (int)$value;
        
        return $this;
    }
    
    private $line_color;
    
    public function getLineColor()
    {
        return $this->line_color;
    }
    
    public function setLineColor( SmartCaptcha_Securimage_Color $value )
    {
        $this->line_color = $value;
        
        return $this;
    }

    private $signature_color;
    
    public function getSignatureColor()
    {
        return $this->signature_color;
    }
    
    public function setSignatureColor( SmartCaptcha_Securimage_Color $value )
    {
        $this->signature_color = $value;
        
        return $this;
    }

    private $text_color;
    
    public function getTextColor()
    {
        return $this->text_color;
    }
    
    public function setTextColor( SmartCaptcha_Securimage_Color $value )
    {
        $this->text_color = $value;
        
        return $this;
    }

    private $use_wordlist = false;
    
    public function getUseWordlist()
    {
        return (bool)$this->use_wordlist;
    }
    
    public function setUseWordlist( $value )
    {
        $this->use_wordlist = (bool)$value;
        
        return $this;
    }
    
    private $draw_lines_over_text;
    
    public function getDrawLinesOverText()
    {
        return (bool)$this->draw_lines_over_text;
    }
    
    public function setDrawLinesOverText( $value )
    {
        $this->draw_lines_over_text = (bool)$value;
        
        return $this;
    }
    
    private $code;
    
    public function getCode()
    {
        if ( isset($_SESSION['securimage_code_value']) && !empty($_SESSION['securimage_code_value']) )
        {
            return strtolower( $_SESSION['securimage_code_value'] );
        }
        else
        {
            return '';
        }
    }
    
    public function setCode( SMARTCAPTCHA_BOL_QuestionsDto $value )
    {
        $this->code = $value->getQuestion();
        $this->saveData( $value->getId() );
        
        return $this;
    }
    
    public function saveData( $id )
    {
        $_SESSION[self::SESSION_NAME] = (int)$id;
    }
    
    private $image_signature;
    
    public function getImageSignature()
    {
        return $this->image_signature;
    }
    
    public function setImageSignature( $value )
    {
        $this->image_signature = $value;
        
        return $this;
    }
    
    private $text_x_start;
    
    public function getTextXStart()
    {
        return (int)$this->text_x_start;
    }
    
    public function setTextXStart( $value )
    {
        $this->text_x_start = (int)$value;
        
        return $this;
    }
    
    private $use_multi_text;

    public function getUseMultiText()
    {
        return (bool)$this->use_multi_text;
    }
    
    public function setUseMultiText( $value )
    {
        $this->use_multi_text = (bool)$value;
        
        return $this;
    }

    private $multi_text_color;
    
    public function getMultiTextColor()
    {
        return $this->multi_text_color;
    }
    
    public function setMultiTextColor( $value )
    {
        $this->multi_text_color = $value;
        
        return $this;
    }

    private $image_type;
    private $code_length;
    private $charset;
    private $wordlist_file;    
    private $gd_font_file;
    private $gd_font_size;
    private $use_gd_font;
    private $ttf_file;
    private $background_directory = null;
    private $audio_path;
    private $audio_format;
    private $session_name = '';
    private $im;
    private $tmpimg;
    private $iscale;
    private $bgimg;
    private $code_entered;
    private $correct_code;
    
    private function __construct()
    {
        if ( session_id() == '' )
        {
            if ( trim($this->session_name) != '' )
            {
                session_name( $this->session_name );
            }
            
            session_start();
	}

        $this->image_width = 230;
        $this->image_height = 80;
        $this->image_type = SI_IMAGE_PNG;

        $this->code_length = 6;
        $this->charset = 'ABCDEFGHKLMNPRSTUVWYZabcdefghklmnprstuvwyz23456789';
        $this->wordlist_file = './words/words.txt';
        $this->use_wordlist = false;

        $this->gd_font_file = '';
        $this->use_gd_font = false;
        $this->gd_font_size = 24;
        $this->text_x_start = 15;

        $this->ttf_file = OW_DIR_LIB . 'securimage/AHGBold.ttf';

        $this->perturbation = 0.75;
        $this->iscale = 5;
        $this->text_angle_minimum = 0;
        $this->text_angle_maximum = 0;

        $this->image_bg_color = new SmartCaptcha_Securimage_Color( 0xff, 0xff, 0xff );
        $this->text_color = new SmartCaptcha_Securimage_Color( 0x3d, 0x3d, 0x3d );
        $this->multi_text_color = array( new SmartCaptcha_Securimage_Color(0x0, 0x20, 0xCC),
            new SmartCaptcha_Securimage_Color(0x0, 0x30, 0xEE),
            new SmartCaptcha_Securimage_Color(0x0, 0x40, 0xCC),
            new SmartCaptcha_Securimage_Color(0x0, 0x50, 0xEE),
            new SmartCaptcha_Securimage_Color(0x0, 0x60, 0xCC) );
        $this->use_multi_text = false;
        $this->use_transparent_text = false;
        $this->text_transparency_percentage = 30;

        $this->num_lines = 10;
        $this->line_color = new SmartCaptcha_Securimage_Color( 0x3d, 0x3d, 0x3d );
        $this->draw_lines_over_text = true;

        $this->image_signature = '';
        $this->signature_color = new SmartCaptcha_Securimage_Color( 0x20, 0x50, 0xCC );
        $this->signature_font = OW_DIR_LIB . 'securimage/AHGBold.ttf';

        $this->audio_path = './audio/';
        $this->audio_format = 'mp3';
        $this->session_name = '';
        $this->correct_code = false;
    }

    public function show( $background_image = "" )
    {
        if( $background_image != "" && is_readable($background_image) )
        {
            $this->bgimg = $background_image;
        }

        $this->doImage();
    }

    public function doImage()
    {
        if ( $this->use_gd_font == true )
        {
            $this->iscale = 1;
        }
        
        if ( $this->use_transparent_text == true || $this->bgimg != "")
        {
            $this->im = imagecreatetruecolor( $this->image_width, $this->image_height );
            $bgcolor = imagecolorallocate( $this->im, $this->image_bg_color->r, $this->image_bg_color->g, $this->image_bg_color->b );
            imagefilledrectangle( $this->im, 0, 0, $this->image_width * $this->iscale, $this->image_height * $this->iscale, $bgcolor );

            $this->tmpimg = imagecreatetruecolor( $this->image_width * $this->iscale, $this->image_height * $this->iscale );
            imagepalettecopy( $this->tmpimg, $this->im );
            imagefilledrectangle( $this->tmpimg, 0, 0, $this->image_width * $this->iscale, $this->image_height * $this->iscale, $bgcolor );
        }
        else
        {
            $this->im = imagecreate( $this->image_width, $this->image_height );
            $bgcolor = imagecolorallocate( $this->im, $this->image_bg_color->r, $this->image_bg_color->g, $this->image_bg_color->b );

            $this->tmpimg = imagecreate( $this->image_width * $this->iscale, $this->image_height * $this->iscale );
            imagepalettecopy( $this->tmpimg, $this->im );
        }

        $this->setBackground();
        $this->createCode();

        if ( !$this->draw_lines_over_text && $this->num_lines > 0 )
        {
            $this->drawLines();
        }

        $this->drawWord();
        
        if ( $this->use_gd_font == false )
        {
            $this->distortedCopy();
        }

        if ( $this->draw_lines_over_text && $this->num_lines > 0 )
        {
            $this->drawLines();
        }

        if ( trim($this->image_signature) != '' )
        {
            $this->addSignature();
        }

        $this->output();
    }
    
    public function setBackground()
    {
        if ( $this->bgimg == '' )
        {
            if ( $this->background_directory != null && is_dir($this->background_directory) && is_readable($this->background_directory) )
            {
                $img = $this->getBackgroundFromDirectory();

                if ( $img != false )
                {
                    $this->bgimg = $img;
                }
            }
        }

        $dat = false;

        if ( !empty($this->bgimg) )
        {
            $dat = @getimagesize( $this->bgimg );
        }

        if ( $dat == false )
        {
            return;
        }

        switch ( $dat[2] )
        {
            case 1: $newim = @imagecreatefromgif( $this->bgimg ); break;
            case 2: $newim = @imagecreatefromjpeg( $this->bgimg ); break;
            case 3: $newim = @imagecreatefrompng( $this->bgimg ); break;
            case 15: $newim = @imagecreatefromwbmp( $this->bgimg ); break;
            case 16: $newim = @imagecreatefromxbm( $this->bgimg ); break;
            default: return;
        }

        if ( !$newim )
        {
            return;
        }

        imagecopyresized( $this->im, $newim, 0, 0, 0, 0, $this->image_width, $this->image_height, imagesx($newim), imagesy($newim) );
    }
    
    public function getBackgroundFromDirectory()
    {
        $images = array();

        if ( $dh = opendir($this->background_directory) )
        {
            while ( ($file = readdir($dh)) !== false )
            {
                if ( preg_match('/(jpg|gif|png)$/i', $file) )
                {
                    $images[] = $file;
                }
            }

            closedir( $dh );

            if ( sizeof($images) > 0 )
            {
                return rtrim( $this->background_directory, '/' ) . '/' . $images[rand(0, sizeof($images)-1)];
            }
        }

        return false;
    }
    
    public function createCode()
    {
        if ( $this->use_wordlist && is_readable($this->wordlist_file) )
        {
            $this->code = $this->readCodeFromFile();
        }

        if ( $this->code == false )
        {
            $this->code = $this->generateCode( $this->code_length );
        }        
    }

    public function check( $code )
    {
        $this->code = $code;
        $this->validate();
        return $this->correct_code;
    }
    
    private function validate()
    {
        if ( !empty($_SESSION[self::SESSION_NAME]) )
        {
            $answers = SMARTCAPTCHA_BOL_Service::getInstance()->findAnswersByQuestionId( $_SESSION[self::SESSION_NAME] );
            
            foreach ( $answers as $answer )
            {
                if ( strcasecmp($answer->getAnswer(), $this->code) === 0 )
                {
                    $this->correct_code = true;
                    return;
                }
            }
            
            $this->correct_code = false; 
        }
    }

    function outputAudioFile()
    {
        if ( strtolower($this->audio_format) == 'wav' )
        {
            header( 'Content-type: audio/x-wav' );
            $ext = 'wav';
        }
        else
        {
            header( 'Content-type: audio/mpeg' );
            $ext = 'mp3';
        }

        header( "Content-Disposition: attachment; name=\"securimage_audio.{$ext}\"" );
        header( 'Cache-Control: no-store, no-cache, must-revalidate' );
        header( 'Expires: Sun, 1 Jan 2000 12:00:00 GMT' );
        header( 'Last-Modified: ' . gmdate('D, d M Y H:i:s') . 'GMT' );

        $audio = $this->getAudibleCode( $ext );

        header( 'Content-Length: ' . strlen($audio) );

        echo $audio;
        exit;
    }

    function drawLines()
    {
        $linecolor = imagecolorallocate( $this->im, $this->line_color->r, $this->line_color->g, $this->line_color->b );

        for ( $line = 0; $line < $this->num_lines; ++$line )
        {
            $x = $this->image_width * ( 1 + $line ) / ( $this->num_lines + 1 );
            $x += ( 0.5 - $this->frand() ) * $this->image_width / $this->num_lines;
            $y = rand( $this->image_height * 0.1, $this->image_height * 0.9 );

            $theta = ( $this->frand()-0.5 ) * M_PI * 0.7;
            $w = $this->image_width;
            $len = rand( $w * 0.4, $w * 0.7 );
            $lwid = rand( 0, 2 );

            $k = $this->frand() * 0.6 + 0.2;
            $k = $k * $k * 0.5;
            $phi = $this->frand() * 6.28;
            $step = 0.5;
            $dx = $step * cos( $theta );
            $dy = $step * sin( $theta );
            $n = $len / $step;
            $amp = 1.5 * $this->frand() / ( $k + 5.0 / $len );
            $x0 = $x - 0.5 * $len * cos( $theta );
            $y0 = $y - 0.5 * $len * sin( $theta );

            $ldx = round( -$dy * $lwid );
            $ldy = round( $dx * $lwid );

            for ( $i = 0; $i < $n; ++$i )
            {
                $x = $x0 + $i * $dx + $amp * $dy * sin( $k * $i * $step + $phi );
                $y = $y0 + $i * $dy - $amp * $dx * sin( $k * $i * $step + $phi );
                imagefilledrectangle( $this->im, $x, $y, $x + $lwid, $y + $lwid, $linecolor );
            }
        }
    }

    function drawWord()
    {
        $width2 = $this->image_width * $this->iscale;
        $height2 = $this->image_height * $this->iscale;
		 
        if ( $this->use_gd_font == true )
        {
            if ( !is_int($this->gd_font_file) )
            {
                $font = @imageloadfont( $this->gd_font_file );
                
                if ( $font == false )
                {
                    trigger_error( "Failed to load GD Font file {$this->gd_font_file} ", E_USER_WARNING );
                    return;
                }
            }
            else
            {
                $font = $this->gd_font_file;
            }

            $color = imagecolorallocate( $this->im, hexdec(substr($this->text_color, 1, 2)), hexdec(substr($this->text_color, 3, 2)), hexdec(substr($this->text_color, 5, 2)) );
            imagestring( $this->im, $font, $this->text_x_start, ($this->image_height / 2) - ($this->gd_font_size / 2), $this->code, $color );
        } 
        else
        {
            $font_size = $height2 * .35;
            $bb = imagettfbbox( $font_size, 0, $this->ttf_file, $this->code );
            $tx = $bb[4] - $bb[0];
            $ty = $bb[5] - $bb[1];
            $x = floor( $width2 / 2 - $tx / 2 - $bb[0] );
            $y = round( $height2 / 2 - $ty / 2 - $bb[1] );

            if ( $this->use_transparent_text == true )
            {
                $alpha = intval( $this->text_transparency_percentage / 100 * 127 );
                $font_color = imagecolorallocatealpha( $this->tmpimg, $this->text_color->r, $this->text_color->g, $this->text_color->b, $alpha );
            }
            else
            {
                $font_color = imagecolorallocate( $this->tmpimg, $this->text_color->r, $this->text_color->g, $this->text_color->b );
            }

            $strlen = strlen( $this->code );
            
            if ( !is_array($this->multi_text_color) )
            {
                $this->use_multi_text = false;
            }

            if ( $this->use_multi_text == false && $this->text_angle_minimum == 0 && $this->text_angle_maximum == 0 )
            {
                imagettftext($this->tmpimg, $font_size, 0, $x, $y, $font_color, $this->ttf_file, $this->code);
            }
            else 
            {
                for ( $i = 0; $i < $strlen; ++$i )
                {
                    $angle = rand( $this->text_angle_minimum, $this->text_angle_maximum );
                    $y = rand( $y - 5, $y + 5 );
                    if ( $this->use_multi_text == true )
                    {
                        $idx = rand( 0, sizeof($this->multi_text_color) - 1 );
                        
                        if ( !is_object($this->multi_text_color[$idx]) )
                        {
                            $this->multi_text_color[$idx] = $this->text_color;
                        }

                        if ( $this->use_transparent_text == true ) 
                        {
                            $font_color = imagecolorallocatealpha( $this->tmpimg, $this->multi_text_color[$idx]->r, $this->multi_text_color[$idx]->g, $this->multi_text_color[$idx]->b, $alpha );
                        } 
                        else
                        {
                            $font_color = imagecolorallocate( $this->tmpimg, $this->multi_text_color[$idx]->r, $this->multi_text_color[$idx]->g, $this->multi_text_color[$idx]->b );
                        }
                    }
					 
                    $ch = $this->code{$i};

                    imagettftext( $this->tmpimg, $font_size, $angle, $x, $y, $font_color, $this->ttf_file, $ch );

                    if ( strpos('abcdeghknopqsuvxyz', $ch) !== false )
                    {
                        $min_x = $font_size - ( $this->iscale * 6 );
                        $max_x = $font_size - ( $this->iscale * 6 );
                    }
                    else if ( strpos('ilI1', $ch) !== false )
                    {
                        $min_x = $font_size / 5;
                        $max_x = $font_size / 3;
                    } 
                    else if ( strpos('fjrt', $ch) !== false )
                    {
                        $min_x = $font_size - ( $this->iscale * 12 );
                        $max_x = $font_size - ( $this->iscale * 12 );
                    } 
                    else if ( $ch == 'wm' )
                    {
                        $min_x = $font_size;
                        $max_x = $font_size + ( $this->iscale * 3 );
                    }
                    else 
                    {
                        $min_x = $font_size + ( $this->iscale * 2 );
                        $max_x = $font_size + ( $this->iscale * 5 );
                    }

                    $x += rand( $min_x, $max_x );
                }
            }
        }
    }

    function distortedCopy()
    {
        $numpoles = 3;

        for ( $i = 0; $i < $numpoles; ++$i )
        {
            $px[$i] = rand( $this->image_width * 0.3, $this->image_width * 0.7 );
            $py[$i] = rand( $this->image_height * 0.3, $this->image_height * 0.7 );
            $rad[$i] = rand( $this->image_width * 0.4, $this->image_width * 0.7 );
            $tmp = -$this->frand() * 0.15 - 0.15;
            $amp[$i] = $this->perturbation * $tmp;
        }
		 
        $bgCol   = imagecolorat( $this->tmpimg, 0, 0 );
        $width2  = $this->iscale * $this->image_width;
        $height2 = $this->iscale * $this->image_height;
		 
        imagepalettecopy( $this->im, $this->tmpimg );
		 
        for ( $ix = 0; $ix < $this->image_width; ++$ix )
        {
            for ( $iy = 0; $iy < $this->image_height; ++$iy )
            {
                $x = $ix;
                $y = $iy;
					
                for ( $i = 0; $i < $numpoles; ++$i )
                {
                    $dx = $ix - $px[$i];
                    $dy = $iy - $py[$i];
                    if ( $dx == 0 && $dy == 0 )
                    {
                        continue;
                    }

                    $r = sqrt( $dx * $dx + $dy * $dy );
                    if ( $r > $rad[$i] )
                    {
                        continue;
                    }
                    
                    $rscale = $amp[$i] * sin( 3.14 * $r / $rad[$i] );
                    $x += $dx * $rscale;
                    $y += $dy * $rscale;
                }
					
                $c = $bgCol;
                $x *= $this->iscale;
                $y *= $this->iscale;

                if ( $x >= 0 && $x < $width2 && $y >= 0 && $y < $height2 )
                {
                    $c = imagecolorat( $this->tmpimg, $x, $y );
                }

                if ( $c != $bgCol )
                { 
                    imagesetpixel($this->im, $ix, $iy, $c);
                }
            }
        }
    }

    function generateCode( $len )
    {
        $code = '';

        for( $i = 1, $cslen = strlen($this->charset); $i <= $len; ++$i )
        {
            $code .= $this->charset{rand(0, $cslen - 1)};
        }
        
        return $code;
    }

    function readCodeFromFile()
    {
        $fp = @fopen( $this->wordlist_file, 'rb' );
        
        if ( !$fp )
        {
            return false;
        }
         
        $fsize = filesize( $this->wordlist_file );
            
        if ( $fsize < 32 )
        {
            return false;
        }

        if ( $fsize < 128 )
        {
            $max = $fsize;
        }
        else
        {
            $max = 128;
        }

        fseek( $fp, rand(0, $fsize - $max), SEEK_SET );
        $data = fread( $fp, 128 );
        fclose( $fp );
        $data = preg_replace( "/\r?\n/", "\n", $data );

        $start = strpos( $data, "\n", rand(0, 100) ) + 1;
        $end   = strpos( $data, "\n", $start );

        return strtolower( substr($data, $start, $end - $start) );
    }

    function output()
    {
        header( "Expires: Mon, 26 Jul 1997 05:00:00 GMT" );
        header( "Last-Modified: " . gmdate("D, d M Y H:i:s") . "GMT" );
        header( "Cache-Control: no-store, no-cache, must-revalidate" );
        header( "Cache-Control: post-check=0, pre-check=0", false );
        header( "Pragma: no-cache" );

        switch ( $this->image_type )
        {
            case SI_IMAGE_JPEG:
                header( "Content-Type: image/jpeg" );
                imagejpeg( $this->im, null, 90 );
                break;

            case SI_IMAGE_GIF:
                header( "Content-Type: image/gif" );
                imagegif( $this->im );
                break;

            default:
                header( "Content-Type: image/png" );
                imagepng( $this->im );
                break;
        }

        imagedestroy( $this->im );
        exit;
    }

    function getAudibleCode( $format = 'wav' )
    {
        $letters = array();
        $code    = $this->getCode();

        if ( $code == '' ) 
        {
            $this->createCode();
            $code = $this->getCode();
        }

        for ( $i = 0; $i < strlen($code); ++$i )
        {
            $letters[] = $code{$i};
        }

        if ( $format == 'mp3' )
        {
            return $this->generateMP3( $letters );
        }
        else
        {
            return $this->generateWAV( $letters );
        }
    }

    function setAudioPath( $audio_directory )
    {
        if ( is_dir($audio_directory) && is_readable($audio_directory) )
        {
            $this->audio_path = $audio_directory;
            return true;
        }
        else
        {
            return false;
        }
    }

    function checkCode()
    {
        return $this->correct_code;
    }

    function generateWAV( $letters )
    {
        $data_len = 0;
        $files = array();
        $out_data = '';

        foreach ( $letters as $letter )
        {
            $filename = $this->audio_path . strtoupper( $letter ) . '.wav';

            $fp = fopen( $filename, 'rb' );

            $file = array();

            $data = fread( $fp, filesize($filename) );

            $header = substr( $data, 0, 36 );
            $body   = substr( $data, 44 );

            $data = unpack('NChunkID/VChunkSize/NFormat/NSubChunk1ID/VSubChunk1Size/vAudioFormat/vNumChannels/VSampleRate/VByteRate/vBlockAlign/vBitsPerSample', $header);

            $file['sub_chunk1_id']   = $data['SubChunk1ID'];
            $file['bits_per_sample'] = $data['BitsPerSample'];
            $file['channels']        = $data['NumChannels'];
            $file['format']          = $data['AudioFormat'];
            $file['sample_rate']     = $data['SampleRate'];
            $file['size']            = $data['ChunkSize'] + 8;
            $file['data']            = $body;

            if ( ($p = strpos($file['data'], 'LIST')) !== false )
            {
                $info         = substr($file['data'], $p + 4, 8);
                $data         = unpack('Vlength/Vjunk', $info);
                $file['data'] = substr($file['data'], 0, $p);
                $file['size'] = $file['size'] - (strlen($file['data']) - $p);
            }

            $files[] = $file;
            $data    = null;
            $header  = null;
            $body    = null;

            $data_len += strlen( $file['data'] );

            fclose($fp);
        }

        $out_data = '';
	
        for ( $i = 0; $i < sizeof($files); ++$i )
        {
            if ( $i == 0 )
            {
                $out_data .= pack('C4VC8', ord('R'), ord('I'), ord('F'), ord('F'), $data_len + 36, ord('W'), ord('A'), ord('V'), ord('E'), ord('f'), ord('m'), ord('t'), ord(' '));

                $out_data .= pack('VvvVVvv',
                16,
                $files[$i]['format'],
                $files[$i]['channels'],
                $files[$i]['sample_rate'],
                $files[$i]['sample_rate'] * (($files[$i]['bits_per_sample'] * $files[$i]['channels']) / 8),
                ($files[$i]['bits_per_sample'] * $files[$i]['channels']) / 8,
                $files[$i]['bits_per_sample'] );

                $out_data .= pack('C4', ord('d'), ord('a'), ord('t'), ord('a'));

                $out_data .= pack('V', $data_len);
            }

            $out_data .= $files[$i]['data'];
        }

        $this->scrambleAudioData($out_data, 'wav');
        return $out_data;
    }

    function scrambleAudioData( &$data, $format )
    {
        if ( $format == 'wav' )
        {
            $start = strpos( $data, 'data' ) + 4;
            
            if ( $start === false )
            {
                $start = 44;
            }
        } 
        else
        {
            $start = 4;
        }

        $start  += rand( 1, 64 );
        $datalen = strlen( $data ) - $start - 256;

        for ( $i = $start; $i < $datalen; $i += 64 )
        {
            $ch = ord( $data{$i} );
            
            if ( $ch < 9 || $ch > 119 )
            {
                continue;
            }
            
            $data{$i} = chr( $ch + rand(-8, 8) );
        }
    }

    function generateMP3($letters)
    {
        $data_len    = 0;
        $files       = array();
        $out_data    = '';

        foreach ($letters as $letter)
        {
            $filename = $this->audio_path . strtoupper($letter) . '.mp3';

            $fp   = fopen($filename, 'rb');
            $data = fread($fp, filesize($filename)); // read file in

            $this->scrambleAudioData($data, 'mp3');
            $out_data .= $data;

            fclose($fp);
        }

        return $out_data;
    }

    function frand()
    {
        return 0.0001*rand( 0,9999 );
    }

    function addSignature()
    {
        $cmtcol = imagecolorallocate($this->im, $this->signature_color->r, $this->signature_color->g, $this->signature_color->b);

        if ( $this->use_gd_font )
        {
            imagestring($this->im, 5, $this->image_width - (strlen($this->image_signature) * 10), $this->image_height - 20, $this->image_signature, $cmtcol);
        }
        else
        {
            $bbox = imagettfbbox(10, 0, $this->signature_font, $this->image_signature);
            $textlen = $bbox[2] - $bbox[0];
            $x = $this->image_width - $textlen - 5;
            $y = $this->image_height - 3;

            imagettftext($this->im, 10, 0, $x, $y, $cmtcol, $this->signature_font, $this->image_signature);
        }
    }
}

class SmartCaptcha_Securimage_Color
{
    public $r;
    public $g;
    public $b;

    public function __construct( $red, $green = null, $blue = null )
    {
        if ( $green == null && $blue == null && preg_match('/^#[a-f0-9]{3,6}$/i', $red) ) 
        {
            $col = substr($red, 1);
            
            if ( strlen($col) == 3 )
            {
                $red   = str_repeat(substr($col, 0, 1), 2);
                $green = str_repeat(substr($col, 1, 1), 2);
                $blue  = str_repeat(substr($col, 2, 1), 2);
            }
            else
            {
                $red   = substr($col, 0, 2);
                $green = substr($col, 2, 2);
                $blue  = substr($col, 4, 2);
            }

            $red   = hexdec($red);
            $green = hexdec($green);
            $blue  = hexdec($blue);
        } 
        else
        {
            if ( $red < 0 )
            {
                $red = 0;
            }
            
            if ( $red > 255 )
            {
                $red = 255;
            }
            
            if ( $green < 0 )
            {
                $green = 0;
            }
            
            if ( $green > 255 )
            {
                $green = 255;
            }
            
            if ( $blue < 0 )
            {
                $blue = 0;
            }
            
            if ( $blue > 255 )
            {
                $blue = 255;
            }
        }

        $this->r = $red;
        $this->g = $green;
        $this->b = $blue;
    }
}
