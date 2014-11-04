<?php /* Smarty version Smarty-3.1.12, created on 2014-11-04 10:49:27
         compiled from "/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_plugins/mailbox/views/components/toolbar.html" */ ?>
<?php /*%%SmartyHeaderCode:177235920854583eb7204822-36021549%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '4d3055cdad86d866e228db56e96e34d43ebe5b81' => 
    array (
      0 => '/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_plugins/mailbox/views/components/toolbar.html',
      1 => 1414912615,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '177235920854583eb7204822-36021549',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'chatModeEnabled' => 0,
    'mailModeEnabled' => 0,
    'isAuthorizedSendMessage' => 0,
    'im_sound_url' => 0,
    'defaultAvatarUrl' => 0,
    'isAuthorizedReplyToMessage' => 0,
    'replyToMessageActionPromotedText' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_54583eb7236a59_01774594',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54583eb7236a59_01774594')) {function content_54583eb7236a59_01774594($_smarty_tpl) {?><?php if (!is_callable('smarty_function_component')) include '/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_smarty/plugin/function.component.php';
if (!is_callable('smarty_function_text')) include '/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_smarty/plugin/function.text.php';
?><?php if ($_smarty_tpl->tpl_vars['chatModeEnabled']->value){?>
<?php echo smarty_function_component(array('class'=>"MAILBOX_CMP_ChatUserList"),$_smarty_tpl);?>

<?php }?>

<div class="ow_chat_dialog_wrap" id="dialogsContainer">

    <div class="ow_chat_selector ow_hidden">
        <div class="ow_chat_block">
            <div class="ow_selector_panel">
                <a class="ow_btn_dialogs" href="javascript://"><span></span></a>
                <span class="ow_dialog_count">0</span>
                <span class="ow_count_wrap" style="display: none;">
                    <span class="ow_count_bg ow_count_active">
                        <span class="ow_count">0</span>
                    </span>
                </span>
            </div>
            <div class="ow_chat_selector_list ow_tooltip ow_tooltip_bottom_left ow_hidden">
                <div class="ow_tooltip_body">
                    <ul class="ow_chat_selector_items"></ul>
                </div>
                <div class="ow_tooltip_tail"><span></span></div>
            </div>
        </div>
    </div>

    <?php if ($_smarty_tpl->tpl_vars['mailModeEnabled']->value&&$_smarty_tpl->tpl_vars['isAuthorizedSendMessage']->value){?>
    <?php echo smarty_function_component(array('class'=>"MAILBOX_CMP_NewMessage"),$_smarty_tpl);?>

    <?php }?>
</div>

<div id="im_sound_player_audio_container" style="position: fixed; left: -1000px; top: -1000px;">
    <audio id="im_sound_player_audio" src="<?php echo $_smarty_tpl->tpl_vars['im_sound_url']->value;?>
" ></audio>
</div>

<div id="mailbox_prototypes" style="display: none;">

    <ul id="ow_chat_selector_items_proto">
        <li class="ow_chat_selector_item ow_sets_button ow_hidden">
            <a href="javascript://" id="chatSelectorContactItemDisplayName"></a>
            <span class="ow_count_wrap">
                <span class="ow_count_bg ow_count_active" style="display: none;" id="chatSelectorContactItemCounterBlock">
                    <span class="ow_count" id="chatSelectorContactItemCounter">0</span>
                </span>
            </span>
        </li>
    </ul>

    <div class="ow_chat_dialog" id="dialogPrototypeBlock">
        <div class="ow_chat_block">
            <div class="ow_author_block ow_chat_hover clearfix">
                <div class="ow_vertical_puller"></div>
                <div class="ow_diagonal_puller"></div>
                <div class="ow_horizontal_puller"></div>
                <a href="javascript://" target="_blank" class="ow_chat_in_item_author_href ow_chat_in_item_photo_wrap" id="dialogProfileUrl">
                    <span class="ow_chat_in_item_photo"><img title="" alt="" src="<?php echo $_smarty_tpl->tpl_vars['defaultAvatarUrl']->value;?>
" height="32px" width="32px" id="dialogProfileAvatarUrl"></span>
                </a>
                <a href="javascript://" class="ow_chat_item_author_wrap" id="dialogMinimizeMaximizeBtn">
                    <span class="ow_chat_item_author">
                        <span class="ow_chat_in_item_author clearfix" id="dialogProfileDisplayName"></span>
                        <div class="ow_chat_status" id="dialogProfileStatus"></div>
                    </span>
                </a>
                <a class="ow_btn_close" href="javascript://" id="dialogCloseBtn"><span></span></a>
            </div>
            <!--<div class="ow_chat_disabled_subject_block" id="dialogSubjectBlock">-->
                <!--<span id="dialogSubject"></span>-->
            <!--</div>-->
            <div class="ow_chat_in_dialog_wrap">
                <div style="display: none;" class="ow_dialog_date_cap sticky" id="dialogStickyDateCapBlock">
                    <span id="dialogStickyDateCap"></span>
                </div>
                <div class="ow_chat_in_dialog">
                    <div class="ow_dialog_items_wrap" id="dialogLog"></div>
                    <div class="ow_chat_preloader" id="dialogPreloader"></div>
                </div>
            </div>
            <?php if ($_smarty_tpl->tpl_vars['isAuthorizedReplyToMessage']->value){?>
            <div class="ow_chat_message_block">
                <div class="ow_chat_message" id="dialogMessageFormBlock">
                    <span class="ow_attachment_icons">
                        <div class="ow_attachments clearfix">
                            <a class="attach" href="javascript://" id="dialogAttachmentsBtn"></a>
                        </div>
                    </span>
                    <textarea id="dialogTextarea"></textarea>
                    <a href="javascript://" class="ow_chat_attach_btn"></a>
                </div>
            </div>
            <div class="ow_chat_message_block" style="display: none;" id="dialogUserIsUnreachable">
                <span style="padding: 8px; text-align: center; display:block;"  id="dialogUserIsUnreachableText"><?php echo smarty_function_text(array('key'=>'mailbox+user_is_unreachable'),$_smarty_tpl);?>
</span>
            </div>
            <?php }else{ ?>
                <?php if ($_smarty_tpl->tpl_vars['replyToMessageActionPromotedText']->value!=''){?>
                <div class="ow_smallmargin ow_anno ow_nocontent"><?php echo $_smarty_tpl->tpl_vars['replyToMessageActionPromotedText']->value;?>
</div>
                <?php }?>
            <?php }?>
        </div>
    </div>


    <div class="clearfix" id="dialogChatMessagePrototypeBlock">
        <div class="ow_dialog_item">
            <div class="ow_dialog_in_item " id="dialogMessageWrapper">
                <p id="dialogMessageText">...</p>
            </div><i></i>
        </div>
    </div>

    <div class="clearfix" id="dialogSysMessagePrototypeBlock"></div>

    <div class="clearfix ow_dialog_item_mailchat" id="dialogMailMessagePrototypeBlock">
        <span class="ow_chat_item_photo_wrap">
            <span class="ow_chat_item_photo">
                <span class="ow_chat_in_item_photo"><img width="32px" height="32px" alt="" src="<?php echo $_smarty_tpl->tpl_vars['defaultAvatarUrl']->value;?>
" id="dialogMessageAvatarUrl"></span>
            </span>
        </span>
        <div class="ow_dialog_item_mailchat_text" id="dialogMessageText"></div>
    </div>

    <div class="clearfix" id="dialogTimeBlockPrototypeBlock">
        <div class="ow_time_block">
            <p><span class="ow_time_text"></span><span></span></p>
        </div>
    </div>

    <div class="ow_dialog_date_cap dialogMessageGroup" id="dialogDateCapBlock">
        <span id="dialogDateCap"></span>
    </div>

    <div class="ow_mailbox_message_content_attach clearfix" id="conversationFileAttachmentContentBlockPrototype"></div>

    <div id="dialogEmbedLinkBlockPrototype">
        <div class="ow_dialog_item even">
            <div class="ow_dialog_in_item" id="dialogMessageWrapper">
                <p id="dialogMessageText"></p>
                <div class="ow_video_attachment_preview loading_content"></div>
            </div>
        </div>
    </div>

    <div id="conversationFileAttachmentBlockPrototype">
        <div class="ow_file_attachment_info">
            <a class="ow_file_attachment_name" target="_blank" href="javascript://" id="conversationFileAttachmentFileName"></a><span class="ow_file_attachment_size ow_small" style="display: inline-block;" id="conversationFileAttachmentFileSize"></span>
        </div>
    </div>
</div><?php }} ?>