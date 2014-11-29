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
 * @package snippets.static
 */

SNIPPETS = (function() {
    
    function Snippets(uniqId)
    {
        this.uniqId = uniqId;
        this.wrap = $("#" + uniqId);
        this.list = $("[data-outlet=list]", this.wrap);
        this.clip = $("[data-outlet=clip]", this.wrap);
        this.items = $("[data-outlet=item]", this.list);
        
        this.clipWidth = 0;
        
        $(document).on("click", "#" + uniqId + " [data-outlet=more]", $.proxy(this.showMore, this));
    }
    
    Snippets.prototype = {
        init: function() {
            this.update();
            
            window.setInterval($.proxy(this.update, this), 1000);
        },
        
        showMore: function() {
            this.wrap.addClass("s-snippets-expanded");
        },
        
        update: function() {
            var clipWidth = this.clip.width();
            
            if ( clipWidth === this.clipWidth ) {
                return;
            }
            
            this.clipWidth = clipWidth;
            
            if (this.list.width() > clipWidth) {
                this.wrap.addClass("s-snippets-more");
            }
            
            this.items.each(function() {
                var item = $(this);
                var pos = item.position();
                var width = item.width();
                
                if (pos.left < clipWidth && (pos.left + width) > clipWidth) {
                    item.addClass("s-snippet-last");
                    item.find("[data-outlet=arrows-wrap]").width(clipWidth - pos.left);
                } 
                else 
                {
                    item.removeClass("s-snippet-last");
                }
            });
        }
    };
    
    
    
    function Settings(uniqId) 
    {
        var hidden = $( "[data-section=hidden]", "#" + uniqId);
        var active = $( "[data-section=active]", "#" + uniqId);
        var field = $( "[data-outlet=field]", "#" + uniqId);
        
        function saveSettings() {
            var attrs = {
               "attribute": "data-name" 
            };
            
            var value = {
                "hidden": hidden.sortable( "toArray", attrs),
                "active": active.sortable( "toArray", attrs)
            };
            
            field.val(JSON.stringify(value));
        };
        
        function checkEmpty()
        {
            hidden[hidden.find("[data-name]").length ? "removeClass" : "addClass"]("s-ss-section-empty");
            active[active.find("[data-name]").length ? "removeClass" : "addClass"]("s-ss-section-empty");
        }
        
        $( "[data-section]", "#" + uniqId).sortable({
            items: "[data-name]",
            connectWith: "#" + uniqId + " [data-section]",
            helper: "clone",
            start: function(e, ui) {
                $(ui.item).addClass("s-ss-dragging");
            },

            stop: function(e, ui) {
                $(ui.item).removeClass("s-ss-dragging");
                checkEmpty();
            },
            
            update: saveSettings,
            placeholder: "s-ss-placeholder"
        }).disableSelection();
    }
    
    return {
        snippets: function( uniqId ) {
            var snippets = new Snippets(uniqId);
            snippets.init();
        },
        
        settings: function( uniqId ) {
            var settings = new Settings(uniqId);
        }
    };
})();
