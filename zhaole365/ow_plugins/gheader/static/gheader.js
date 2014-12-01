/**
 * Copyright (c) 2012, Sergey Kambalin
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * @author Sergey Kambalin <greyexpert@gmail.com>
 * @package gheader.static
 */
GHEADER = {};

GHEADER.CORE = {};

GHEADER.CORE.ObjectRegistry = {};

GHEADER.CORE.uniqId = function( prefix )
{
    prefix = prefix || '';

    return prefix + (Math.ceil(Math.random() * 1000000000)).toString();
};

/**
* Model
*/
GHEADER.CORE.AjaxModel = function( rsp, delegate )
{
   this.rsp = rsp;
   this.delegate = delegate;

   this.delegate.ajaxEnd = this.delegate.ajaxEnd || function(){};
   this.delegate.ajaxSuccess = this.delegate.ajaxSuccess || function(){};
   this.delegate.ajaxStart = this.delegate.ajaxStart || function(){};
};

GHEADER.CORE.AjaxModel.PROTO = function()
{
   this.query = function( command, params )
   {
       params = params || {};

       this.delegate.ajaxStart(command, params);

       $.ajax({
           type: 'POST',
           url: this.rsp,
           data: {
               "params": JSON.stringify(params),
               "command": command
           },
           context: this.delegate,
           success: function(r)
           {
               this.ajaxSuccess(command, r);
           },
           complete: function(r)
           {
               this.ajaxEnd(command, r);
           },
           dataType: 'json'
       });
   };
};

GHEADER.CORE.AjaxModel.prototype = new GHEADER.CORE.AjaxModel.PROTO();

GHEADER.CORE.UploadModel = function( rsp, delegate )
{
    this.rsp = rsp;
    this.delegate = delegate;
    this.fakeIframe = null;
    this.uniqId = GHEADER.CORE.uniqId('uploadModel');

    GHEADER.CORE.ObjectRegistry[this.uniqId] = this;
};

GHEADER.CORE.UploadModel.PROTO = function()
{
    this.upload = function( file, query )
    {
        query = query || {};

        var form, parent;
        parent = $(file).parent();

        this.fakeIframe = $('<iframe id="iframe-' + this.uniqId + '" name="attachmentHandler" style="display: none"></iframe>');
        form = $('<form enctype="multipart/form-data" method="POST" target="attachmentHandler" style="display: none"></form>');

        form.attr('action', this.rsp);
        form.append('<input type="hidden" name="uniqId" value="' + this.uniqId + '"/>');
        form.append('<input type="hidden" name="query" value=\'' + JSON.stringify(query) + '\' />');

        form.append(file);

        $('body').prepend(form).prepend(this.fakeIframe);
        this.delegate.uploadStart(query);

        form.get(0).submit();

        parent.append(file);
        form.remove();
    };

    this.uploadComplete = function(r)
    {
        this.fakeIframe.remove();

        if ( r.type !== 'upload' )
        {
            try
            {
                this.delegate.uploadSuccess(r);
            }
            catch ( e )
            {
                alert(e);
            }
        }

        this.delegate.uploadEnd(r);
    };
};

GHEADER.CORE.UploadModel.prototype = new GHEADER.CORE.UploadModel.PROTO();

GHEADER.bindDrag = function($node, delegate)
{
    $node = $($node);

    if ($node.data.uhDrag)
    {
        $node.data.uhDrag.setDelegate(delegate);

        return $node.data.uhDrag;
    }

    var uhDelegate = delegate;

    var uhDrag = {};

    var notify = function( method, position )
    {
        var result = true, preventDefault = false;

        var event = {
            target: $node,
            position: position,

            preventDefault: function()
            {
                preventDefault = true;
            }
        };

        if ( uhDelegate[method] )
        {
            result = uhDelegate[method].call(uhDelegate, event);
        }

        return result === false ? false : !preventDefault;
    };

    uhDrag.setDelegate = function( d )
    {
        uhDelegate = d;
    };

    var dragging = false,
        pressed = false;

    var position = {
        x: 0,
        y: 0
    };

    var start = {
        x: 0,
        y: 0
    };

    var delegates =
    {
        mouseDown: function( e )
        {
            pressed = true;

            position.y = 0;
            position.x = 0;

            start.y = e.pageY;
            start.x = e.pageX;

            if ( !notify('start', position) )
            {
                pressed = false;
            }
        },

        mouseMove: function( e )
        {
            if ( !pressed ) return;

            dragging = true;

            var p = {
                x: e.pageX - start.x,
                y: e.pageY - start.y
            };

            if ( notify('drag', p) )
            {
                position = p;
            }
        },

        mouseUp: function()
        {
            if ( !pressed ) return;

            pressed = false;
            dragging = false;

            notify('end', position);
        }
    };

    $node.mousedown(delegates.mouseDown);
    $(document.body).mousemove(delegates.mouseMove);
    $(document.body).mouseup(delegates.mouseUp);

    $(document.body).on('selectstart', function()
    {
        return !dragging;
    });

    $node.attr('unselectable', 'on')
        .css('user-select', 'none')
        .on('selectstart', false);
};

GHEADER.CoverMenu = function(delegate)
{
   //$('.ow_context_action').off();

   var $uploadInput = $('#uh-upload-cover');

    $uploadInput.change(function()
    {
        delegate.upload($uploadInput);
    });

    $('#uhco-reposition').click(function()
    {
        delegate.reposition();
    });

    $('#uhco-remove').click(function()
    {
        delegate.remove();
    });

    $('#uhco-choose').click(function()
    {
        delegate.choose();
    });


};

GHEADER.Cover = function( options, delegate )
{
    var userId = options.userId;
    var groupId = options.groupId;

    var launcher = new GHEADER.CoverImage.nativeLauncher(options.cover.data);
    GHEADER.CoverImage.setLauncher(launcher);

    $(document).on('click', '#uh-cover-image', function()
    {
        GHEADER.CoverImage.show();
    });

    if ( options.viewOnlyMode )
    {
        return;
    }

    var imageLoaded = true;
    var $image = $('#uh-cover-image');
    var $cover = $('#uh-cover');
    var $overlay = $('#uh-cover-overlay');
    var $conextMenu = $('.uh-cover-add-btn-wrap .ow_context_action_block', '#uhc-controls');
    var coverData = {};

    coverData.position = {
        top: 0,
        left: 0
    };

    coverData.canvas = {
        height: $cover.height(),
        width: $cover.width()
    };

    coverData.userId = userId;
    coverData.groupId = groupId;

    var setImage = function( src )
    {
        if ( $image.attr('src') == src )
        {
            return;
        }

        var tmpImage = $image.clone();
        tmpImage.attr('src', src);
        $image.replaceWith(tmpImage);

        $image = tmpImage;
        $image.show();

        imageLoaded = true;

        $cover.removeClass('uh-cover-no-cover');
        $cover.addClass('uh-cover-has-cover');

        $conextMenu.addClass('ow_photo_context_action'); // Design hack
    };

    var unsetImage = function( src )
    {
        $image.hide();

        imageLoaded = false;

        $cover.removeClass('uh-cover-has-cover');
        $cover.addClass('uh-cover-no-cover');

        $conextMenu.removeClass('ow_photo_context_action'); // Design hack
    };

    var coverMode = 'view';
    var switchMode = function( mode )
    {
       $cover.removeClass('uh-cover-mode-' + coverMode);
       coverMode = mode;
       $cover.addClass('uh-cover-mode-' + coverMode);

       delegate.switchMode(mode);
    };
    
    var setRatio = function( ratio ) {
        $cover.find(".uh-scaler-img").css("width", ratio + "%");
    };

    // Upload Model

    var uploadModel, uploadDelegate = {};

    uploadDelegate.uploadStart = function( query )
    {
        switchMode('loading');
    };

    uploadDelegate.uploadSuccess = function( response )
    {
        if ( response.message )
        {
            OW.info(response.message);
        }

        if (response.src)
        {
            setImage(response.src);
        }

        if ( response.data )
        {
            coverData.position.top = response.data.position.top;
            coverData.position.left = response.data.position.left;

            $image.css(coverData.position);

            if ( response.data.css )
            {
                coverData.css = response.data.css;
                $image.css(coverData.css);
            }
        }
        
        if ( response.ratio ) {
            setRatio(response.ratio);
        }

        switchMode('reposition');
    };

    uploadDelegate.uploadEnd = function( response )
    {
        if ( response.type === 'error' )
        {
            switchMode('view');
            OW.error(response.error);
        }
    };

    uploadModel = new GHEADER.CORE.UploadModel(options.uploader, uploadDelegate);

    // Ajax Model

    var ajaxModel, ajaxDelegate = {};

    ajaxDelegate.$controls = $('#uhc-controls');

    ajaxDelegate.ajaxStart = function( command )
    {
        if ( command === 'removeCover' )
        {
            return;
        }

        if ( command === 'addFromPhotos' )
        {
            switchMode('loading');

            return;
        }

        this.$controls.addClass('ow_preloader').addClass('uh-cover-controls-loading');
    };

    ajaxDelegate.ajaxSuccess = function( command, response )
    {
        if ( response.type == 'error' )
        {
            OW.error(response.error);
            switchMode('view');

            return;
        }

        if ( response.message )
        {
            OW.info(response.message);
        }

        if ( !response.src )
        {
            unsetImage();
        }
        else
        {
            setImage(response.src);
        }

        if ( response.data )
        {
            coverData.position.top = response.data.position.top;
            coverData.position.left = response.data.position.left;

            $image.css(coverData.position);

            if ( response.data.css )
            {
                coverData.css = response.data.css;
                $image.css(coverData.css);
            }
        }

        if ( command === 'addFromPhotos' )
        {
            switchMode('reposition');
        }
        else
        {
            switchMode('view');
        }
        
        if ( response.ratio ) {
            setRatio(response.ratio);
        }
    };

    ajaxDelegate.ajaxEnd = function( command )
    {
        if ( command == 'removeCover' )
        {
            return;
        }

        this.$controls.removeClass('ow_preloader').removeClass('uh-cover-controls-loading');
    };

    ajaxModel = new GHEADER.CORE.AjaxModel(options.responder, ajaxDelegate);


    // Toolbar

    var toolbarDelegate = {};

    toolbarDelegate.upload = function( input )
    {
        coverData.canvas.height = $cover.height();
        coverData.canvas.width = $cover.width();

        coverData.userId = userId;
    coverData.groupId = groupId;

        uploadModel.upload(input, {
            height: $cover.height(),
            width: $cover.width(),
            userId: coverData.userId,
            groupId: coverData.groupId
        });
    };

    toolbarDelegate.reposition = function()
    {
        switchMode('reposition');
    };

    toolbarDelegate.remove = function()
    {
        if ( !confirm(OW.getLanguageText('gheader', 'delete_cover_confirmation')) )
        {
            return false;
        }

        unsetImage();
        switchMode('view');

        ajaxModel.query('removeCover', coverData);
    };

    toolbarDelegate.choose = function()
    {
        var winHeight = $(window).height();

        var fb;
        fb = OW.ajaxFloatBox('GHEADER_CMP_MyPhotos', [{windowHeight: winHeight}], {
            addClass: 'uh-photo-floatbox',
            title: OW.getLanguageText('gheader', 'my_photos_title'),
            scope: {
                setPhoto: function( id ) {

                    ajaxModel.query('addFromPhotos', {
                        "photoId": id,
                        "userId": userId,
                        "groupId": groupId,
                        "height": $cover.height(),
                        "width": $cover.width()
                    });

                    fb.close();
                },

                close: function() {
                    fb.close();
                }
            }
        });
    };

    GHEADER.CoverMenu(toolbarDelegate, coverData);


    var dragDelegate = {};

    dragDelegate.startPosition = {},
    dragDelegate.dimension = {},
    dragDelegate.direction = 'all',

    dragDelegate.image =

    dragDelegate.css = {
        top: parseInt($image.css('top')),
        left: parseInt($image.css('left'))
    };

    dragDelegate.setPosition = function( p )
    {
        if ( typeof p.y !== 'undefined' )
        {
            this.css.top = p.y;
        }

        if ( typeof p.x !== 'undefined' )
        {
           this.css.left = p.x;
        }

        $image.css(this.css);
    };

    dragDelegate.start = function()
    {
        if ( !imageLoaded )
        {
            return false;
        }

        var pos = $image.position();
        this.startPosition.y = pos.top;
        this.startPosition.x = pos.left;

        this.css = {
            top: this.startPosition.y,
            left: this.startPosition.x
        };

        this.dimension.parentHeight = $cover.height();
        this.dimension.parentWidth = $cover.width();
        this.dimension.imageHeight = $image.height();
        this.dimension.imageWidth = $image.width();
    };

    dragDelegate.drag = function( e )
    {
        var top = this.startPosition.y + e.position.y;
        var left = this.startPosition.x + e.position.x;
        var bottom = -(this.dimension.imageHeight - (this.dimension.parentHeight + (-top)));
        var right = -(this.dimension.imageWidth - (this.dimension.parentWidth + (-left)));

        top = top >= 0 ? 0 : top;
        left = left >= 0 ? 0 : left;

        var p = {};

        if ( bottom < 0 )
        {
            p.y = top;
        }

        if ( right < 0 )
        {
            p.x = left;
        }

        this.setPosition(p);
    };

    dragDelegate.end = function( e )
    {
        coverData.position.top = this.css.top;
        coverData.position.left = this.css.left;
    }

    GHEADER.bindDrag($overlay, dragDelegate);

    // Simple DOM binds

    $('#uh-reposition-cancel').click(function()
    {
        ajaxModel.query('cancelChanges', coverData);
    });


    $('#uh-reposition-save').click(function()
    {
        coverData.canvas.height = $cover.height();
        coverData.canvas.width = $cover.width();

        ajaxModel.query('saveCover', coverData);
    });
};


GHEADER.Header = function( options )
{
    var userId = options.userId;
    var groupId = options.groupId;

    var $header = $('#uh-header');
    var headerMode = 'view';

    var switchMode = function( mode )
    {
        $header.removeClass('uh-mode-' + headerMode);
        headerMode = mode;
        $header.addClass('uh-mode-' + headerMode);
    };

    this.cover = new GHEADER.Cover(options.cover,
    {
        switchMode: function( mode )
        {
            if ( mode === 'view' )
            {
                switchMode('view');
            }
            else
            {
                switchMode('coverEdit');
            }
        }
    });
};



$(function(){

    var to = null;

    var clearTO = function()
    {
        if ( to )
        {
            window.clearTimeout(to);
        }
    };

    $('.uh-at-more-wrap').hover(function(){
        clearTO();

        $(this).find(".ow_tooltip").stop(true, true).show().animate({top: 2, opacity: 1}, "fast");
        $(this).find('.ow_context_action').addClass('active');
    },
    function(){
        clearTO();
        var self = $(this);

        to = window.setTimeout(function()
        {
            self.find(".ow_tooltip").stop(true, true).animate({top: -4, opacity: 0}, "fast", function()
            {
                $(this).hide();
            });

            self.find('.ow_context_action').removeClass('active');
        }, 200);
    });

});


GHEADER.CoverImage = (function()
{
    var launcher;

    var setLauncher = function(l)
    {
        launcher = l;
    };

    var getLauncher = function(l)
    {
        return launcher;
    };

    var show = function()
    {
        launcher.show();
    };

    return {
        setLauncher: setLauncher,
        getLauncher: getLauncher,
        show: show
    };
})();

GHEADER.CoverImage.nativeLauncher = function( cover )
{
    this.show = function()
    {
        OW.ajaxFloatBox('GHEADER_CMP_CoverView', [cover.groupId], {
            layout: 'empty'
        });
    };
};

GHEADER.CoverImage.photoLauncher = function( cover )
{
    this.show = function()
    {

    };
};


GHEADER.PhotoSelector = function( options, delegate )
{
    var isListFull = options.listFull;
    var $list = $('#uhps-list');
    var $listContent = $('#uhps-list-content');

    var $preloader = $('#uhps-preloader');
    if ( isListFull )
    {
        $preloader.hide();
    }

    var offset = function() {
        return $listContent.find('.uh-photo').length;
    };

    var ajaxModel, ajaxDelegate = {};

    ajaxDelegate.ajaxStart = function( command )
    {
        $preloader.css('visibility', 'visible');
    };

    ajaxDelegate.ajaxSuccess = function( command, response )
    {
        offset = response.offset;

        if ( response.listFull )
        {
            isListFull = true;
            $preloader.hide();
        }

        if ( response.list )
        {
            $listContent.append(response.list);
        }

        OW.addScroll($list);
    };

    ajaxDelegate.ajaxEnd = function( command )
    {
        $preloader.css('visibility', 'hidden');
    };

    ajaxModel = new GHEADER.CORE.AjaxModel(options.responder, ajaxDelegate);

    $('#uhps-cancel').click(function() {
        delegate.close();
    });

    $(document).off('click.uhps');
    $(document).on('click.uhps', '.uh-photo', function() {
        var photo = $(this);

        delegate.setPhoto(photo.data('id'));
    });

    $list.on('jsp-arrow-change', function( event, isAtTop, isAtBottom, isAtLeft, isAtRight )
    {
        if ( isListFull )
        {
            return;
        }

        if ( isAtBottom )
        {
            ajaxModel.query('loadMorePhotos', {
                offset: offset()
            });
        }
    });

    OW.addScroll($list);
};