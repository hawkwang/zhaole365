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

if ( !window.QUESTIONS_Loaded )
{

QUTILS = {};

QUTILS.addInvitation = function( nodes )
{
    var $nodes, start, complete;

    $nodes = $(nodes);

    start = function()
    {
        var $self = $(this);
        if ( $self.val() == $self.attr('inv') )
        {
            $self.val('');
        }

        $self.removeClass('invitation');
    };

    complete = function()
    {
        var $self = $(this);
        if ( !$self.val() )
        {
            $self.val($self.attr('inv'));
            $self.addClass('invitation');
        }
    };

    $nodes.unbind('focus.invitation').on('focus.invitation', start);
    $nodes.unbind('blur.invitation').on('blur.invitation', complete);
};

QUESTIONS_QuestionColletction = {};
QUESTIONS_AnswerListCollection= {};
QUESTIONS_RelationCollection = {};

QUESTIONS_QuestionList = function( uniqId, data )
{
    window.QUESTIONS_ListObject = this;

    var self = this;

    this.node = document.getElementById(uniqId);
    this.$list = null;
    this.data = data;
    this.rsp = null;

    this.initQuestionList = function( questions )
    {
        $(questions).each(function()
        {
            var $q = $(this);
            self.initQuestion($q.attr("rel"), $q);
        });
    };

    this.initQuestion = function( question )
    {

    };

    this.setResponder = function(rsp)
    {
        this.rsp = rsp;
    };

    this.ajax = function(query, callback)
    {
    	var cf = function()
        {
            this.ajaxSuccess.apply(this, arguments);
            if (callback)
            {
                callback.apply(this, arguments);
            }
    	};

        this.startAjax();

        $.ajax({
            type: 'POST',
            url: this.rsp,
            data: {
                "data": JSON.stringify(this.data),
                "query": JSON.stringify(query)
            },
            context: this,
            success: cf,
            dataType: 'json',
            complete: this.endAjax
        });
    };

    this.startAjax = function() {};
    this.endAjax = function() {};

    this.ajaxSuccess = function(r)
    {
        if ( r.data )
        {
            this.data = r.data;
        }

        if ( r.error )
        {
            OW.error(r.error);
        }

        if ( r.warning )
        {
            OW.warning(r.warning);
        }

        if ( r.info )
        {
            OW.info(r.info);
        }

        if ( r.markup )
        {
            if ( r.markup.html )
            {
                var newQuestions = $(r.markup.html);

                if ( r.markup.position === 'append' )
                {
                    newQuestions.appendTo(this.$list);
                }

                if ( r.markup.position === 'prepend' )
                {
                    newQuestions.prependTo(this.$list);
                }

                if ( r.markup.position === 'replace' )
                {
                    this.$list.empty().append(newQuestions);
                }

                this.initQuestionList(newQuestions);
            }

            if ( r.markup.script )
            {
                OW.addScript(r.markup.script);
            }

            this.$('.ql_delimiter').show();
            this.$('.ql_delimiter:last').hide();
        }

        if ( this.data.viewMore === false )
        {
            this.$viewMoreC.hide();
        }

        if ( this.data.viewMore === true )
        {
            this.$viewMoreC.show();
        }

        if ( r.loadMore && this.data.viewMore )
        {
            this.loadMore(r.loadMore);
        }
    };

    this.$ = function (sel)
    {
        return $(sel, this.node);
    };

    this.loadMore = function( count )
    {
        if ( this.data.displayedCount >= this.data.totalCount )
        {
            return;
        }

        var btn = this.$viewMoreC.find('input.ql_view_more');

        OW.inProgressNode(btn);
        self.ajax({
            "command": "more",
            "count": count
        }, function()
        {
            OW.activateNode(btn);
        });
    };

    this.reload = function( order )
    {
        order = order || 'latest';
        var activeMenuItem = $('.ql-menu .active span');
        activeMenuItem.addClass('q_ic_preloader');

        self.ajax({
            "command": "order",
            "order": order
        }, function()
        {
            activeMenuItem.removeClass('q_ic_preloader');
        });
    };

    this.$list = this.$(".ql-items");
    this.$orderSelect = $('.ql-sort-wrap');
    this.$viewMoreC = this.$(".ql_view_more_c");

    this.initQuestionList(this.$list);

    this.$viewMoreC.find('input.ql_view_more').click(function()
    {
        self.loadMore();
    });

    /*this.$order.click(function()
    {
        self.$orderSelect.show();
        self.$orderWrap.addClass('ql-sort-wrap-active');

        return false;
    });*/

    this.$orderSelect.find('.ql-sort-item').click(function()
    {
        var $self = $(this);

        if ( $self.hasClass('ql-sort-item-checked') )
        {
            return false;
        }

        self.$orderSelect.find('.ql-sort-item').removeClass('ql-sort-item-checked');
        self.reload($self.attr('qorder'));
        $self.addClass('ql-sort-item-checked');

        $('.ql-sort-btn').text($self.find('span').text());

        return false;
    });

    /*$(document).click(function( e )
    {
        self.$orderSelect.hide();
        self.$orderWrap.removeClass('ql-sort-wrap-active');
    });*/
};


/*QUESTIONS_QuestionStatus = function( node, pc, vc )
{
    this.init(node, pc, vc);
};

QUESTIONS_QuestionStatus.prototype = new (function(){

    var self = this, node;

    this.init = function(node)
    {

    }

    this.$ = function (sel)
    {
        return $(sel, this.node);
    };
})();*/

QUESTIONS_Question = function(uniqId, questionId)
{
    this.answerList = null;
    this.questionId = questionId;
    this.node = document.getElementById(uniqId);
    OW.bindAutoClicks(this.node);
    var self = this;

    OW.bind('base.comments_list_init', function(p)
    {
        if ( p.entityType === 'question' && p.entityId == self.questionId )
        {
            self.answerList.refreshStatus(this.totalCount, false, false);
            if ( self.answerList.relation )
            {
                self.answerList.relation.refreshStatus(this.totalCount, false, false);
            }
        }
    });
};

QUESTIONS_Question.prototype = new (function(){

    this.$ = function (sel)
    {
        return $(sel, this.node);
    };

    this.setAnswerList = function(answerList)
    {
        this.answerList = answerList;

        this.answerList.setBehavior(QUESTIONS_InQuestionBehavior);
    };

    this.focusOnPostInput = function()
    {
        this.$('.q-comments textarea').focus();
    };

})();


QUESTIONS_InQuestionBehavior =
{
    initViewMore: function()
    {
        var self = this;
        this.$('.qa-view-more').click(function()
        {
            self.loadMore();

            return false;
        });
    }
};

QUESTIONS_UserList = function(node, users, voteCount, userId, optionId)
{
    this.users = users;

    this.userId = userId;
    this.maxCount = 3;
    this.node = node;
    this.voteCount = voteCount;
    this.optionId = optionId;

    this.init();
};

QUESTIONS_UserList.prototype =
{
    $: function (sel)
    {
        return $(sel, this.node);
    },

    init: function()
    {
        var self = this, usersFBOpened = false;

        this.initTooltip();

        this.$('.qaa-view-more-btn').click(function()
        {
            if ( usersFBOpened )
            {
                return false;
            }

            if ( QUESTIONS_UserList.floatBox )
            {
                QUESTIONS_UserList.floatBox.close();
            }

            QUESTIONS_UserList.floatBox = OW.ajaxFloatBox('QUESTIONS_CMP_UserList', [self.optionId, self.getUsers()],
            {
                width: 450,
                iconClass: "ow_ic_user",
                title: OW.getLanguageText('questions', 'users_fb_title')
            });

            if ( QUESTIONS_UserList.floatBox.$preloader )
            {
                QUESTIONS_UserList.floatBox.$preloader.addClass('q-floatbox-preloader');
            }

            usersFBOpened = true;

            QUESTIONS_UserList.floatBox.bind('close', function(){
                QUESTIONS_UserList.floatBox = false;
                usersFBOpened = false;
            });
        });
    },

    getUsers: function()
    {
        var out = [], items;
        items = this.$('.qa-users-c .qa-user');

        for ( var i = 0; i < this.maxCount; i++ )
        {
            out.push($(items[i]).attr("rel"));
        }

        return out;
    },

    add: function()
    {
        this.$('.user-' + this.userId).prependTo(this.$('.qa-users-c'));
        this.voteCount++;

        this.changed();
    },

    remove: function()
    {
        this.$('.user-' + this.userId).appendTo(this.$('.qa-hidden-users-c'));
        this.voteCount--;

        this.changed();
    },

    changed: function()
    {
        if ( this.hasViewMore() )
        {
            this.$('.qaa-view-more-btn').show();
        }
        else
        {
            this.$('.qaa-view-more-btn').hide();
        }
    },

    hasViewMore: function()
    {
        return this.getOtherVoteCount() > 0;
    },

    getUserCount: function()
    {
        var length = this.$('.qa-users-c .qa-user').length;

        return length > this.maxCount ? this.maxCount : length;
    },

    getOtherVoteCount: function()
    {
        return this.voteCount - this.getUserCount();
    },

    initTooltip: function()
    {
        var self = this, $t = this.$('*[title], *[q-title]');

        $t.unbind();

        $t.hover(function()
        {
            var hasVM = self.hasViewMore();

            var params = {
                side: 'top'
            };

            if ( hasVM && $(this).is('.qaa-view-more-btn img') )
            {
                params.side = 'right';

                if ( !$(this).data('owTip') )
                {
                    var t = $(this).attr('q-title');
                    params.show = t.replace('[count]', self.getOtherVoteCount());
                }
            }

            if ( !hasVM && self.$('.qa-users-c .qa-user:last .qa-user_avatar img').is(this) )
            {
                params.side = 'right';
            }

            $(this).data('owTipStatus', false);
            OW.showTip($(this), params);
        },
        function()
        {
            OW.hideTip($(this));
        });
    }
};



QUESTIONS_AnswersProto = function()
{
    this.init = function(uniqId, options, data, disabled)
    {
        var self = this;

        this.uniqId = uniqId;

        if ( !data.inPopupMode )
        {
            QUESTIONS_RelationCollection[data.questionId] = this.uniqId;
        }

        this.userId = data.userId;
        this.data = data;
        this.options = {};
        this.activeCommands = 0;

        this.questionFloatBox = false;

        this.totalAnswers = data.totalAnswers;
        this.node = document.getElementById(uniqId);

        this.inProgress = false;
        this.fbMode = OW_FloatBox.version && OW_FloatBox.version > 1;

        this.refreshStatus(false, false, false);

        this.$('.questions-answer').each(function(){
            var opt = $(this).attr('rel');
            self.options['opt_' + opt] = self.bindOption(this, disabled);
        });

        $.each(options, function(i, o)
        {
            var opt = self.getOption(o.id);
            opt.users = new QUESTIONS_UserList(opt.node.find('.qa-users .qa-avatar'), o.users, o.voteCount, self.userId, o.id);
            opt.checked = o.checked;
            opt.newOption = false;
        });

        this.$listFollows = $('.q-' + this.uniqId + '-status-follows');
        this.$listFollowsBtn = this.$listFollows.find('.newsfeed-feature-button-control');

        if ( this.$listFollows.length )
        {
            this.$listFollowsBtn.hover(function()
            {
                var followTitle = self.$listFollowsBtn.is('.active')
                    ? OW.getLanguageText('questions', 'toolbar_unfollow_btn')
                    : OW.getLanguageText('questions', 'toolbar_follow_btn')

                self.refreshFollowLabel();

                OW.showTip(self.$listFollows, {
                    side: 'right',
                    show: followTitle
                });

            }, function()
            {
                OW.hideTip(self.$listFollows);
            });
        }
    };

    this.refreshFollowLabel = function()
    {
        var followTitle = this.$listFollowsBtn.is('.active')
            ? OW.getLanguageText('questions', 'toolbar_unfollow_btn')
            : OW.getLanguageText('questions', 'toolbar_follow_btn');

        if ( this.$listFollows.data('owTip') )
        {
            this.$listFollows.data('owTip').find('.ow_tip_box').html(followTitle);
        }
    };

    this.followQuestion = function()
    {
        this.showUnfollow();

        this.ajax({
            "command": "follow"
        });
    };

    this.unfollowQuestion = function()
    {
        this.showFollow();

        this.ajax({
            "command": "unfollow"
        });
    };

    this.showFollow = function()
    {
        var self = this;

        $('#' + this.uniqId + '-follow').show();
        $('#' + this.uniqId + '-unfollow').hide();

        if ( !this.$listFollows.length ) return;

        this.$listFollowsBtn.removeClass('active').find('.newsfeed-feature-button')
            .get(0).onclick = function() { self.followQuestion(); };

        this.refreshFollowLabel();
    };

    this.showUnfollow = function()
    {
        var self = this;

        $('#' + this.uniqId + '-unfollow').show();
        $('#' + this.uniqId + '-follow').hide();

        if ( !this.$listFollows.length ) return;

        this.$listFollowsBtn.addClass('active').find('.newsfeed-feature-button')
            .get(0).onclick = function() { self.unfollowQuestion(); };

        this.refreshFollowLabel();
    };

    this.showFollowers = function()
    {
        QUESTIONS.showQuestionFollowers(this.data.questionId, this.data.userContext, [this.data.ownerId]);
    };

    this.unvote = function()
    {
        var self = this;

        $('#' + this.uniqId + '-unvote').parent().hide();

        this.$('.qa-check input:checked').each(function(){
            this.checked = false;
            self.answer(this.value);
            self.calculate();
        });
    };

    this.showUnvote = function()
    {
        $('#' + this.uniqId + '-unvote').parent().show();
    };

    this.setRelation = function( rel )
    {
        this.relation = QUESTIONS_AnswerListCollection[rel];
    };

    this.refresh = function()
    {
        this.ajax({
            "command": "reload"
        });
    };

    this.redraw = function( draw )
    {
        $('#' + this.uniqId).replaceWith(draw.markup);
        OW.addScript(draw.script);
    };

    this.deleteQuestion = function()
    {
        if ( this.relation )
        {
            this.relation.deleteQuestion();

            return;
        }

        if ( this.questionFloatBox )
        {
            this.questionFloatBox.close();
        }

        this.ajax({
            "command": "deleteQuestion"
        });

        this.removeQuestionNode();
    };

    this.removeQuestionNode = function()
    {
        $(this.node).parents('li:eq(0)').remove();
        /*$(this.node).parents('li:eq(0)').animate({opacity: 'hide', height: 'hide'}, 'normal', function()
        {
            $(this).remove();
        });*/
    };

    this.refreshStatus = function(pc, vc, fc)
    {
        var self = this;

        var newsfeedStatusChange = function()
        {
            var $comments = $('.q-' + self.uniqId + '-status-comments .newsfeed-feature-label'),
                $votes = $('.q-' + self.uniqId + '-status-votes .newsfeed-feature-label'),
                $follows = $('.q-' + self.uniqId + '-status-follows .newsfeed-feature-label');

            vc = vc === false ? parseInt($votes.text()) : parseInt(vc);
            pc = pc === false ? parseInt($comments.text()) : parseInt(pc);
            fc = fc === false ? parseInt($follows.text()) : parseInt(fc);

            $votes.text(vc);
            $comments.text(pc);
            $follows.text(fc);
        };

        var questionStatusChange = function()
        {
            var status = $('#' + self.uniqId + '-status'),
                $vc = status.find('.q-status-votes'),
                $pc = status.find('.q-status-posts'),
                $fc = status.find('.q-status-follows'),
                $d1 = status.find('.qsd-1'),
                $d2 = status.find('.qsd-2');

            pc = pc === false ? parseInt($pc.find('.qs-number').text()) : parseInt(pc);
            vc = vc === false ? parseInt($vc.find('.qs-number').text()) : parseInt(vc);
            fc = fc === false ? parseInt($fc.find('.qs-number').text()) : parseInt(fc);

            $d1[ pc && vc ? "show" : "hide" ]();
            $d2[ pc && fc || vc && fc  ? "show" : "hide" ]();

            status.parents('.ql_control:eq(0)')[ pc || vc || fc ? "show" : "hide" ]();
            status[ pc || vc || fc ? "show" : "hide" ]();


            $vc[ vc ? "show" : "hide" ]();
            $pc[ pc ? "show" : "hide" ]();
            $fc[ fc ? "show" : "hide" ]();

            $pc.find('.qs-number').text(pc);
            $vc.find('.qs-number').text(vc);
            $fc.find('.qs-number').text(fc);
        };

        if ( this.data.expandedView )
        {
            questionStatusChange();
        }
        else
        {
            newsfeedStatusChange();
        }
    };

    this.beforeRemove = function() {};

    this.afterRemove = function(optionId, newOption)
    {
        if (!newOption)
        {
            this.data.offset--;
        }

        this.data.optionTotal--;
        this.data.displayedCount--;

        this.controlRemoveBtns();
    };

    this.startCommand = function()
    {
        this.activeCommands++;
    };

    this.endCommand = function()
    {
        this.activeCommands--;
    };

    this.isBusy = function()
    {
        return this.activeCommands > 3;
    };

    this.controlRemoveBtns = function() {};

    this.removeOption = function(optionId)
    {
        var self = this, optionCount, warning, opt = this.getOption(optionId);

        if ( this.beforeRemove(optionId) === false )
        {
            return;
        }

        warning = (opt.users.voteCount == 1 && !opt.checked) || opt.users.voteCount > 1;
        if ( warning && !confirm(OW.getLanguageText('questions', 'option_not_empty_delete_warning') ))
        {
            return false;
        }

        optionCount = this.data.displayedCount;
        opt.node.fadeTo(100, 0, function(){
            opt.node.slideUp('fast', function(){
                var lmc, vm = (self.data.optionTotal - self.data.displayedCount) > 0;
                opt.node.remove();
                optionCount--;

                lmc = Math.round((self.data.st.displayedCount * 50) / 100);

                if ( vm && ( optionCount <= (lmc < 2 ? 2 : lmc) ) )
                {
                    self.loadMore(self.data.st.displayedCount - lmc);
                }
            });
        });

        this.ajax({
            "command": "removeOption",
            "opt": optionId,
            "newOption": opt.newOption
        });

        delete this.options['opt_' + optionId];
        this.afterRemove(optionId, opt.newOption);
    };

    this.initViewMore = function()
    {
        var self = this;

        this.$('.qa-view-more').click(function()
        {
            var moreOffset = self.data.optionTotal - self.data.displayedCount;

            if ( moreOffset > 3 )
            {
                if ( self.fbMode )
                {
                    self.openQuestion();
                }
                else
                {
                    return true;
                }
            }
            else
            {
                self.loadMore();
            }

            return false;
        });
    };

    this.loadMore = function(inc, fnc)
    {
        var self = this,
            $vm = this.$('.qa-view-more'),
            offset = this.data.offset,
            $text;

        inc = inc || false;

        $vm.addClass('ow_preloader');
        $text = $vm.find('.qa-vm-content').hide();

        this.ajax({
            "command": 'more',
            'offset': offset,
            'inc': inc
    	}, function() {
            $vm.removeClass('ow_preloader');
            $text.show();

            self.updateViewMore(true);

            if (fnc) fnc.apply(this);
        });
    };

    this.openQuestion = function( focusToPost )
    {
        focusToPost = focusToPost || false;
        var self = this;

        this.questionFloatBox = QUESTIONS.openQuestion({
            userContext: this.data.userContext,
            questionId: this.data.questionId,
            relationUniqId: this.uniqId,
            focusToPost: focusToPost,
            url: this.data.url
        });

        this.questionFloatBox.bind('close', function()
        {
            self.questionFloatBox = false;
        });
    };

    this.openQuestionDelegate = function( focusToPost )
    {
        if ( this.fbMode )
        {
            this.openQuestion(focusToPost);

            return false;
        }

        return true;
    };

    this.initAddNew = function()
    {
        var $qadd, $input, $form, $button, inv, self = this;

        $form = this.$('.qaa-form');
        $input = this.$('.qaa-input');
        $button = this.$('.qaa-button');
        $qadd = this.$('.questions-add-answer');


        $button.unbind('mouseover.owtip');
        $button.unbind('mouseout.owtip');

        $button.bind('mouseover.owtip', function()
        {
            var params = {
                hideEvent: 'mouseout',
                side: 'right',
                show: $(this).attr('q-title')
            };

            OW.showTip($(this), params);
        });

        $button.bind('mouseout.owtip', function()
        {
            $(this).data('owTipHide', true);
        });


        inv = $input.val();

        $button.click(function()
        {
            $form.submit();
        });

        $input.keyup(function()
        {
            if ( !this.value )
            {
                $(this).data('upperCased', false);
            }

            if ( !$(this).data('upperCased') && this.value )
            {
                this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1);
                $(this).data('upperCased', true);
            }
        });

        $input.focus(function()
        {
            if ( this.value == inv )
            {
                this.value = '';
                $(this).removeClass('invitation');
            }
        });

        $input.blur(function()
        {
            if ( !this.value )
            {
                this.value = inv;
                $(this).addClass('invitation');
            }
        });

        $form.submit(function()
        {
            var v = $.trim($input.val()), _return;

            self.$('.questions-answer').each(function()
            {
                if ( $.trim($('.qa-text', this).text()) == v )
                {
                    self.hlOption($(this).attr('rel'));
                    _return = true;

                    return false;
                }
            });

            if ( _return )
            {
                $input.val('');
                return false;
            }

            if (v && v != inv)
            {
                self.ajax({
                    "command": "addAnswer",
                    "text": v
                }, function(){
                	$qadd.removeClass('ow_preloader').addClass('ow_ic_add');
                	$input.val('');
                	$input.attr("disabled", false);
                });

                $input.attr("disabled", "disabled");
                $qadd.removeClass('ow_ic_add').addClass('ow_preloader');
            }

            return false;
        });
    };

    this.hlOption = function( opt )
    {
        var qal = this.$('.qa-list');
        qal.css('min-height', qal.height());
        qal.append(this.getOption(opt).node);
        qal.css('min-height', 'auto');
    };

    this.bindOption = function(option, disabled)
    {
        var self = this, $opt, $check, $result, out = {};

        disabled = disabled || false;

        $opt = $(option);

        $check = $opt.find('.qa-check input');
        $result = $('.qa-result', $opt);
        if ( !$result.width() )
        {
            $result.hide();
        }

        out.id = $opt.attr('rel');
        out.node = $opt;
        out.result = $result;
        out.check = $check;
        out.content = $opt.find('.qa-content');

        var hoverC = out.content.find('.qa-hover-c'),
            cliper = out.content.find('.qa-content-clip'),
            wrapper = out.content.find('.qa-content-wrap'),
            wrapperWidth = 0;

        out.content.hover(function()
        {
            if ( !wrapperWidth )
            {
                wrapperWidth = wrapper.width();
                wrapper.width(wrapperWidth);
            }

            cliper.width(wrapperWidth - hoverC.width());
        },
        function()
        {
            cliper.width('100%');
        });

        out.remove = $opt.find('.qa-delete-option');
        out.remove.click(function()
        {
            self.removeOption(out.id);
            return false;
        });

        if ( !disabled )
        {
            var chageDelegate = function()
            {
                self.answer(this.value);
                self.calculate();
            };

            $check.change(chageDelegate);

            out.content.click(function()
            {
                var checkNode = $check.get(0), newChecked;
                newChecked = $check.is(':radio') ? true : ! checkNode.checked;
                if ( newChecked != checkNode.checked )
                {
                    checkNode.checked = newChecked;
                    chageDelegate.apply(checkNode);
                }
            });
        }

        return out;
    };

    this.getOption = function(opt)
    {
        return this.options['opt_' + opt];
    };

    this.$ = function (sel)
    {
        return $(sel, this.node);
    };

    this.setResponder = function(rsp)
    {
        this.rsp = rsp;
    };

    this.ajax = function(query, callback)
    {
        var relation = null;

        if ( this.isBusy() )
        {
            return false;
        }

    	var cf = function()
        {
            this.ajaxSuccess.apply(this, arguments);
            if (callback)
            {
                callback.apply(this, arguments);
            }
    	};

        this.beforeAjax();

        if ( this.relation )
        {
            relation = {
                uniqId: this.relation.uniqId,
                data: this.relation.data
            };
        }

        this.startCommand();
        $.ajax({
            type: 'POST',
            url: this.rsp,
            data: {
                "data": JSON.stringify(this.data),
                "query": JSON.stringify(query),
                "relation": JSON.stringify(relation)
            },
            context: this,
            success: cf,
            dataType: 'json',
            complete: function()
            {
                this.endCommand();
            }
        });
    };

    this.ajaxSuccess = function(r)
    {
        var self = this;

        this.inProgress = false;

        if ( r.listing && window.QUESTIONS_ListObject )
        {
            window.QUESTIONS_ListObject.ajaxSuccess(r.listing);
        }

        if ( r.relation && this.relation )
        {
            this.relation.ajaxSuccess(r.relation);
        }

        if ( r.status )
        {
            this.refreshStatus(r.status.posts, r.status.votes, r.status.follows);
        }

        if ( r.message )
        {
            OW.info(r.message);
        }

        if ( r.error )
        {
            OW.error(r.error);
        }

        if ( r.warning )
        {
            OW.warning(r.warning);
        }

        if ( r.reload )
        {
            this.redraw(r.reload);

            return;
        }

        if ( r.data )
        {
            this.data = r.data;
            this.updateViewMore(true);
        }

        if(r.options)
        {
            this.addOptionList(r.options);
            /*$.each(r.options, function(i, opt)
            {
                self.addOption($(opt.markup)[0], opt.data);
            });
            this.actionComplete();*/
        }

        if ( r.unvote )
        {
            this.showUnvote();
        }

        if ( r.call )
        {
            if ( typeof r.call === 'string' && $.isFunction(self[r.call]) )
            {
                self[r.call].apply(self);
            }

            if ( $.isPlainObject(r.call) && $.isFunction(self[r.call.name]) )
            {
                self[r.call.name].apply(self, r.call.args || []);
            }

            if ( $.isArray(r.call) )
            {
                $.each(r.call, function(i, fnc)
                {
                    if ( typeof self[fnc.name] === "function" )
                    {
                        self[fnc.name].apply(self, fnc.args || []);
                    }
                });
            }
        }
    };

    this.updateViewMore = function( onlyText )
    {
        var more = this.data.optionTotal - this.data.displayedCount,
            vm = this.$('.qa-view-more');

        onlyText = onlyText || false;
        vm.find('.qa-vm-count').text(more);

        if ( more > 0 )
        {
            if (!onlyText) vm.show();
            vm.removeClass('qvm-empty');
        }

        if ( more <= 0 )
        {
            if (!onlyText) vm.hide();
            vm.addClass('qvm-empty');
        }
    };

    this.beforeAjax = function()
    {

    };

    this.answer = function(opt)
    {
        var answers = {"yes": [], "no": []};

        $.each(this.options, function(i, o)
        {
            var checked = o.check.get(0).checked;
            if ( checked != o.checked )
            {
                answers[checked ? "yes" : "no"].push(o.id);
                o.users[checked ? "add" : "remove"]();
                o.checked = checked;
            }
        });

    	this.ajax({
            "command": 'answer',
            "answers": answers,
            "optionId": opt
    	});
    };

    this.getVoteCount = function(opt)
    {
        var $opt, $c, $r, vn;

        $opt = this.getOption(opt).node;
        $c = this.getOption(opt).check;
        $r = this.getOption(opt).result;

        vn = parseInt($r.attr('rel')) + ($c[0].checked ? 1 : 0);
        vn -= parseInt($c.attr('rel'));

        return vn;
    };

    this.addOption = function(option, data, addTo)
    {
        var o, dublicateOpt;

        dublicateOpt = this.$('.questions-answer[rel=' + data.id + ']');

        if (dublicateOpt.length)
        {
            dublicateOpt.replaceWith(option);
        }
        else
        {
            addTo = addTo || this.$('.qa-list');
            addTo.append(option);
        }
        o = this.bindOption(option);
        o.users = new QUESTIONS_UserList(o.node.find('.qa-users .qa-avatar'), data.users, data.voteCount, this.userId, data.id);
        o.checked = data.checked;
        o.newOption = data.newOption;

        this.options['opt_' + o.id] = o;

        this.afterOptionAdd(o);
    };

    this.afterOptionAdd = function()
    {
        this.controlRemoveBtns();
    };

    this.addOptionList = function(options)
    {
        var self = this, c;

        if (options.length === 1)
        {
            self.addOption($(options[0].markup)[0], options[0].data);
            self.actionComplete();

            return;
        }

        c = $('<div class="qa-option-animate-c"></div>').hide();
        this.$('.qa-list').append(c);

        $.each(options, function(i, opt)
        {
            self.addOption($(opt.markup)[0], opt.data, c);
        });

        if ( options.length > 5 )
        {
            c.after(c.children()).remove();
        }
        else
        {
            c.slideDown(100, function(){
                c.after(c.children()).remove();
                self.actionComplete();
            });
        }
    };

    this.actionComplete = function()
    {
        if ( this.data.optionTotal - this.data.displayedCount <= 0 )
        {
            this.$('.questions-add-answer').show();
        }

        this.updateViewMore();
    };

    this.deleteOption = function(opt)
    {
        var option = this.options['opt_' + opt];
        option.node.remove();

        delete this.options['opt_' + opt];
    };

    this.animate = function(option, vn, p)
    {
        var result;
        result = $('.qa-result', option);
        $('.qa-vote-n', option).text(vn);


        if ( p > 0 )
        {
            result.show();
        }
        else
        {
            result.hide();
        }

        if ( p >= 100 )
        {
            result.addClass('q-result-full');
        }
        else
        {
            result.removeClass('q-result-full');
        }

        $('.qa-result', option).css({width: p + '%'});

        /*$('.qa-result', option).animate({width: p + '%'},
        {
            duration: 'fast',
            queue: "global",
            complete: function(){
                if ( p <= 0 )
                {
                    $(this).hide();
                }
            }
        });*/

    };

    this.setBehavior = function( beh )
    {
        $.extend(this, beh);
    };
};


QUESTIONS_PollAnswers = function()
{
    var self = this;

    this.calculate = function()
    {
        var ta = this.totalAnswers;

        this.$('.qa-check input').each(function()
        {
            var opt = self.getOption(this.value);
            ta += this.checked ? 1 : 0;
            ta -= parseInt($(this).attr('rel'));
        });

        this.$('.questions-answer').each(function()
        {
            var vn, p;

            vn = self.getVoteCount($(this).attr('rel'));
            p = ta ? (vn * 100 / ta) : 0;

            self.animate(this, vn, p);
        });
    };

    this.controlRemoveBtns = function()
    {
        var options = this.$('.questions-answer');

        if ( this.data.optionTotal <= 3 )
        {
            options.find('.qa-delete-option').hide();
        }
        else
        {
            options.find('.qa-delete-option').show();
        }
    };

};
QUESTIONS_PollAnswers.prototype = new QUESTIONS_AnswersProto();


QUESTIONS_QuestionAnswers = function()
{
    var self = this;

    this.calculate = function()
    {
        var self = this, ta = 0;

        this.$('.questions-answer').each(function()
        {
            var vn = self.getVoteCount($(this).attr('rel'));
            ta = ta > vn ? ta : vn;
        });

        this.$('.questions-answer').each(function()
        {
            var vn, p;
            vn = self.getVoteCount($(this).attr('rel'));

            p = ta ? (vn * 100 / ta) : 0;

            self.animate(this, vn, p);
        });
    };
};
QUESTIONS_QuestionAnswers.prototype = new QUESTIONS_AnswersProto();




QUESTIONS_Tabs = function( uniqId )
{
    this.node = document.getElementById(uniqId);

    var $tabs = this.$('.gtabs-tab'),
        $contents = this.$('.gtabs-contents'),
        self = this;

    $tabs.click(function(){
        var $s = $(this), key, $current;
        $tabs.removeClass('gtabs-active');
        $s.addClass('gtabs-active');
        key = $s.data("key");
        $contents.hide();
        $current = $contents.filter('[data-key=' + key + ']').show();
        OW.trigger('questions.tabs_changed', [{
            newTab: $current
        }], self);
    });
};

QUESTIONS_Tabs.prototype =
{
    $: function (sel)
    {
        return $(sel, this.node);
    }
};

QUESTIONS_QuestionAdd = function(uniqId, formName, params)
{
    var form = owForms[formName],
        questionField = form.elements["question"],
        answersField = form.elements["answers"],
        allowAddNewField = form.elements["allowAddOprions"],
        node = document.getElementById(uniqId);

    form.bind("submit", function() {
        $(".Q_StatusPreloader", node).show(); 
    });

    form.bind("success", function() {
        $(".Q_StatusPreloader", node).hide();
    });

    function initQuestionInput( input )
    {
        $('.questions-add .questions-input', node).keyup(function()
        {
            if ( !this.value )
            {
                $(this).data('upperCased', false);
            }

            if ( !$(this).data('upperCased') && this.value )
            {
                this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1);
                $(this).data('upperCased', true);
            }
        });
    };


    $(questionField.input).focus(function()
    {
        var input = $(this).unbind().autoResize({
            extraSpace: 0
        });

        initQuestionInput(input);
    });


    questionField.validate = function()
    {
        var val = questionField.getValue();
        val = $.trim(val);

        if ( !val )
        {
            QUESTIONS_Feedback.error(OW.getLanguageText('questions', 'feedback_question_empty'), questionField.input);
            throw 'SubmitError';
        }

        if ( val.length < params.minQuestionLength )
        {
            QUESTIONS_Feedback.error(OW.getLanguageText('questions', 'feedback_question_min_length'), questionField.input);
            throw 'SubmitError';
        }

        if ( val.length > params.maxQuestionLength )
        {
            QUESTIONS_Feedback.error(OW.getLanguageText('questions', 'feedback_question_max_length'), questionField.input);
            throw 'SubmitError';
        }
    };

    answersField.form = form;
    answersField.maxAnswerLength = params.maxAnswerLength;

    answersField.onValidate = function()
    {
        if ( !allowAddNewField.input.checked && answersField.getValue().length < 2 )
        {
            this.showError(OW.getLanguageText('questions', 'feedback_question_two_apt_required'), $(allowAddNewField.input));
            throw 'SubmitError';
        }
    };

    $('.questions-add-answers-btn', node).click(function(){
        $('.questions-add-answers', node).show();
        $(this).hide();
        $('.questions-add-answers-options', node).show();
    });

    OW.bind("questions.after_question_add", function( r )
    {
    	$('.questions-add-answers', node).slideUp('fast');
    	$('.questions-add-answers-options', node).hide();
    	$('.questions-add-answers-btn', node).show();
    });
};

QUESTIONS_AnswersField = function(id, name, inv)
{
    this.form = null;
    this.invMsg = inv;
    this.maxAnswerLength = 150;

    OwFormElement.call(this, id, name);

    var self = this;

    this.delegates =
    {
            onLastFocus: function()
            {
                self.addItem();
            },

            onFocus: function()
            {
                if ( this.value == inv )
                {
                    this.value = '';
                    $(this).removeClass('invitation');
                }
            },

            onKeyUp: function()
            {
                if ( this.value != inv )
                {
                    if ( !this.value )
                    {
                        $(this).data('upperCased', false);
                    }

                    if ( !$(this).data('upperCased') && this.value )
                    {
                        this.value = this.value.charAt(0).toUpperCase() + this.value.slice(1);
                        $(this).data('upperCased', true);
                    }
                }
            },

            onBlur: function(){
                    if ( !this.value )
                    {
                            this.value = inv;
                            $(this).addClass('invitation');
                    }
            }
    };

    function bindInput(input)
    {
        input.unbind('focus').bind('focus', self.delegates.onFocus);
        input.unbind('blur').bind('blur', self.delegates.onBlur);
        input.unbind('keyup').bind('keyup', self.delegates.onKeyUp);
    }

    function bindLastInput(input)
    {
        input.unbind('focus.add_new').bind('focus.add_new', self.delegates.onLastFocus);
    }

    bindInput($('.mt-item-input', this.input));
    bindLastInput($('.mt-item-input:last', this.input));

    this.getValue = function()
    {
        var self = this, out = [];

        this.removeErrors();

        $('.mt-item-input', this.input).each(function(i, o)
        {
            var val = $(o).val();
            if ( val && self.invMsg != val )
            {
                out.push(val);
            }
        });

        return out;
    };

    this.setValue = function( value )
    {
        $('.mt-item-input', this.input).each(function(i, o)
        {
            $(o).val(value[i-1] || '');
        });
    };

    this.resetValue = function()
    {
        var self = this;

        this.removeErrors();

        $('.mt-added-item', this.input).remove();
        $('.mt-item-input', this.input).each(function(i, o)
        {
            o.value = '';
            self.delegates.onBlur.call(o);
        });

        bindLastInput($('.mt-item-input:last', this.input));
    };

    this.addItem = function()
    {
    	$('.mt-item-input').unbind('focus.add_new');
    	var input = $('.mt-item:eq(0)', this.input).clone().show();
        input.addClass('mt-added-item');
    	input.appendTo(this.input);
        bindInput(input.find('.mt-item-input'));
    	bindLastInput(input.find('.mt-item-input'));
    };

    this.validate = function()
    {
        var dub = {};

        $('.mt-item', this.input).removeClass('mt-incorrect-option').each(function(i, o)
        {
            var $input = $('.mt-item-input', o);
            var val = $input.val();
            if ( val && self.invMsg != val )
            {
                if ( val.length > self.maxAnswerLength )
                {
                    self.showError(OW.getLanguageText('questions', 'feedback_option_max_length'), $input);
                    throw 'SubmitError';
                }

                if (dub[val])
                {
                    $(o).addClass('mt-incorrect-option');
                    $input.one('keydown.incorrect', function()
                    {
                        $(o).removeClass('mt-incorrect-option');
                    });

                    $input.get(0).focus();
                    self.showError( OW.getLanguageText('questions', 'feedback_question_dublicate_option'), $input);
                    throw 'SubmitError';
                }
                else
                {
                    dub[val] = true;
                }
            }
        });

        this.onValidate(this);
    };

    this.onValidate = function(){};

    this.removeErrors = function()
    {
        QUESTIONS_Feedback.removeErrors();
    };

    this.showError = function( msg, node )
    {
        QUESTIONS_Feedback.error(msg, node);
    };
};

QUESTIONS_AnswersField.prototype = OwFormElement.prototype;

QUESTIONS_Feedback = {};
QUESTIONS_Feedback.errors = [];

QUESTIONS_Feedback.error = function( msg, node )
{
    node = $(node);

    if ( node.data('owTip') )
    {
        node.data('owTip').find('.ow_tip_box').html(msg);
    }

    var params = {
        side: 'left',
        show: msg,
        width: 150
    };

    OW.showTip(node, params);
    QUESTIONS_Feedback.errors.push(node);

    window.setTimeout(function()
    {
        OW.hideTip(node);
    }, 8000);
};

QUESTIONS_Feedback.removeErrors = function()
{
    for ( var i = 0; i < QUESTIONS_Feedback.errors.length; i++ )
    {
        OW.hideTip(QUESTIONS_Feedback.errors.pop());
    }
};

QUESTIONS = new (function(){

    this.openQuestion = function( params )
    {
        params.focusToPost = params.focusToPost || false;

        if ( params.relationId && !params.relationUniqId && QUESTIONS_RelationCollection[params.relationId])
        {
            params.relationUniqId = QUESTIONS_RelationCollection[params.relationId];
        }

        var fb, curentUrl;

        if (params.url && window.history.replaceState) {
            curentUrl = window.location.href;
            window.history.pushState({} , '', params.url);
        }

        fb = OW.ajaxFloatBox('QUESTIONS_CMP_Question', [params.questionId, params.userContext, null,
        {
            "relation": params.relationUniqId,
            "focusToPost" : params.focusToPost,
            "loadStatic": false,
            "inPopup": true
        }],
        {width: 550, top: 50, iconClass: "ow_ic_lens"
            //, title: OW.getLanguageText('questions', 'question_fb_title')
        });
        
        fb.bind("close", function() {
            if (curentUrl) {
                window.history.pushState({} , '', curentUrl);
            }
        });

        return fb;
    };

    this.showQuestionFollowers = function( questionId, userContext, ignoreUsers )
    {
        QUESTIONS_UserList.floatBox = OW.ajaxFloatBox('QUESTIONS_CMP_FollowList', [questionId, userContext, ignoreUsers],
        {
            width: 450,
            iconClass: "ow_ic_user",
            title: OW.getLanguageText('questions', 'followers_fb_title')
        });

        if ( QUESTIONS_UserList.floatBox.$preloader )
        {
            QUESTIONS_UserList.floatBox.$preloader.addClass('q-floatbox-preloader');
        }

        QUESTIONS_UserList.floatBox.bind('close', function(){
            QUESTIONS_UserList.floatBox = false;
        });
    };

})();

QUESTIONS.friendMode = false;

/*
 * jQuery autoResize (textarea auto-resizer)
 * @copyright James Padolsey http://james.padolsey.com
 * @version 1.04
 */

(function(a){a.fn.autoResize=function(j){var b=a.extend({onResize:function(){},animate:true,animateDuration:150,animateCallback:function(){},extraSpace:20,limit:1000},j);this.filter('textarea').each(function(){var c=a(this).css({resize:'none','overflow-y':'hidden'}),k=c.height(),f=(function(){var l=['height','width','lineHeight','textDecoration','letterSpacing'],h={};a.each(l,function(d,e){h[e]=c.css(e)});return c.clone().removeAttr('id').removeAttr('name').css({position:'absolute',top:0,left:-9999}).css(h).attr('tabIndex','-1').insertBefore(c)})(),i=null,g=function(){f.height(0).val(a(this).val()).scrollTop(10000);var d=Math.max(f.scrollTop(),k)+b.extraSpace,e=a(this).add(f);if(i===d){return}i=d;if(d>=b.limit){a(this).css('overflow-y','');return}b.onResize.call(this);b.animate&&c.css('display')==='block'?e.stop().animate({height:d},b.animateDuration,b.animateCallback):e.height(d)};c.unbind('.dynSiz').bind('keyup.dynSiz',g).bind('keydown.dynSiz',g).bind('change.dynSiz',g)});return this}})(jQuery);

window.QUESTIONS_Loaded = true;
}


window.EQAjaxLoadCallbacksRun = function()
{
    if ( window.ATTPAjaxLoadCallbackQueue )
    {
        $.each(window.ATTPAjaxLoadCallbackQueue, function(i, fnc)
        {
            fnc.call();
        });
    }
};