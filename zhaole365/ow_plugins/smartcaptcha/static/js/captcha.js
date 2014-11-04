/**
 * Copyright (c) 2014, Kairat Bakytow
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

var OW_SmartCaptcha = function( $params )
{
    var self = this;

    this.captchaId = $params.captchaId;
    this.captchaClass = $params.captchaClass;
    this.captchaUrl = $params.captchaUrl;
    this.responderUrl = $params.responderUrl;
    this.captcha = $( '#' + this.captchaId );

    this.refresh = function()
    {
        $( document.getElementById('siimage') ).attr( 'src', this.captchaUrl + '?sid=' + Math.random() );
    };

    this.validateCaptcha = function()
    {
        var result = {};

        $.ajax(
        {
            url: self.responderUrl,
            type: 'POST',
            data: { command: 'checkCaptcha', value: this.captcha.val() },
            dataType: 'json',
            async: false,
            success: function( data )
            {
                result = data;
            }
        });

        if ( result.reload && result.reload.length )
        {
            window.location = result.reload;
        }
        
        if( result.result === false )
        {
             self.refresh();
             return false;
        }

        return true;
    };

    $( document.getElementById('siimage_refresh') ).click( function(){self.refresh();} );
};
