/**
 * Copyright (c) 2014, Kairat Bakytow
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

var smartcaptcha = (function( $ )
{
    var instance;
    
    function _construct()
    {
        var _elements = {};
        var _methods = {};
        var _questions = {};
        
        _elements.smartcaptcha = $( document.getElementById('smartcaptcha') );
        _elements.smartcaptchaForm = $( document.getElementById('smartcaptchaForm') );
        
        $( document.getElementById('selectAll') ).on( 'click', function()
        {
            $( '[name="select"]', _elements.smartcaptcha ).attr( 'checked', this.checked );
        });
        
        $( document.getElementById('newQuestion') ).on( 'click', function()
        {
            _methods.drawQuestionFloatBox();
        });
        
        $( document.getElementById('deleteQuestion') ).on( 'click', function()
        {
            if ( !confirm('Are you sure?') )
            {
                return;
            }
            
            var data = [];
            
            $( 'input:checked[name="select"]', _elements.smartcaptcha ).each( function()
            {
                data.push( this.value );
            });
            
            if ( data.length === 0 )
            {
                return;
            }
            
            $.ajax(
            {
                url: OW.smartcaptcha_rsp,
                cache: false,
                dataType: "json",
                type: "POST",
                data:
                {
                    "command": "deleteQuestion",
                    "data": data
                },
                success: _methods.deleteQuestionRow,
                error: function( XMLHttpRequest, textStatus, errorThrown )
                {
                    OW.error( textStatus );
                    throw textStatus;
                }
            });
        });
        
        _methods.addNewAnswer = function( event, val )
        {
            var content = (event.jquery) ? event : event.data.content;
            var value = val || '';
            var answerCont = $( '.smartcaptcha_answers', content );
            var className = answerCont.find( 'tr' ).length % 2 === 0 ? 'ow_alt2' : 'ow_alt1';
            var newAnswer = $( '<tr class="' + className + '"><td class="ow_value"><input type="text" name="answers[]" value="' + value + '"/></td></tr>' );
            answerCont.append( newAnswer );
        };
        
        _methods.drawQuestionFloatBox = function( data )
        {
            var content;
            
            if ( data && data.questionId && _questions[data.questionId] )
            {
                content = _questions[data.questionId];
            }
            else
            {
                content = _elements.smartcaptchaForm.clone();
                
                if ( data && data.id )
                {
                    content.find( '[name="questionId"]' ).val( data.id );
                    content.find( '[name="question"]' ).val( data.question );

                    for ( var key in data.answers )
                    {
                        _methods.addNewAnswer( content, data.answers[key].answer );
                    }
                }
                else
                {
                    _methods.addNewAnswer( content );
                }
            }
            
            $( '[name="newAnswer"]', content ).on( 'click', {content: content}, _methods.addNewAnswer );
            
            var floatbox = new OW_FloatBox(
            {
                $title: OW.getLanguageText('smartcaptcha', 'floatbox_caption'),
                $contents: content,
                width: 500
            });
            
            floatbox.bind( 'close', function()
            {
                var data = this.$container.find( '#smartcaptchaForm' );
                var questionId = data.find( '[name="questionId"]' ).val();

                _questions[questionId] = data;
            });
        };
        
        _methods.deleteQuestionRow = function()
        {
            var table = $( '.ow_table_2', _elements.smartcaptcha ).find( '.ow_alt1, .ow_alt2' ).removeClass();
            var removed = table.filter( 'tr:has(input:checked[name="select"])' ).detach();
            table = table.not( removed ); 
            table.filter( 'tr:odd' ).addClass( 'ow_alt2' );
            table.filter( 'tr:even' ).addClass( 'ow_alt1' );
        };
        
        $( '.question', _elements.smartcaptcha ).on( 'click', function()
        {
            var questionId = $( this ).find( '[name="question"]').val();
            
            if ( _questions[questionId] )
            {
                _methods.drawQuestionFloatBox({questionId: questionId});
                return;
            }
            
            $.ajax(
            {
                url: OW.smartcaptcha_rsp,
                cache: false,
                dataType: "json",
                type: "POST",
                data:
                {
                    "command": "getQuestionAnswers",
                    "questionId": questionId
                },
                success: _methods.drawQuestionFloatBox,
                error: function( XMLHttpRequest, textStatus, errorThrown )
                {
                    OW.error( textStatus );
                    throw textStatus;
                }
            });
        });
        
        return {
            
        };
    }
    
    return {
        getInstance: function()
        {
            if ( instance === undefined )
            {
                instance = _construct();
            }
            
            return instance;
        }
    };
})( jQuery ).getInstance();

OwFormElement.prototype.colorPicker = function( color )
{
    color = this._color = color || '#000000';
    
    var self = this;
    
    $( this.input ).children().css('backgroundColor', color);
    
    $( this.input ).ColorPicker(
    {        
        color: color,
        onShow: function (colpkr)
        {
            $( colpkr ).fadeIn( 500 );
            return false;
        },
        onHide: function (colpkr)
        {
            $( colpkr ).fadeOut( 500 );
            return false;
        },
        onChange: function (hsb, hex, rgb)
        {
            $( 'div', self.input ).css('backgroundColor', '#' + hex);
            self._color = '#' + hex;
        }
    });
};

var SmartcaptchaColorField = function( id, name, color )
{
    var formElement = new OwFormElement( id, name );
    
    formElement.colorPicker( color );
    
    formElement.getValue = function()
    {
        return this._color || '#000000';
    };

    return formElement;
};
