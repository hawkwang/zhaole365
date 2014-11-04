<?php /* Smarty version Smarty-3.1.12, created on 2014-11-04 14:29:47
         compiled from "/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_plugins/mailbox/views/components/new_message.html" */ ?>
<?php /*%%SmartyHeaderCode:14722965155458725bb210a4-94222538%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '9bd44848da93f509b2cbcbefa7af26a63e784835' => 
    array (
      0 => '/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_plugins/mailbox/views/components/new_message.html',
      1 => 1414912615,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '14722965155458725bb210a4-94222538',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'defaultAvatarUrl' => 0,
    'enableAttachments' => 0,
    'attachments' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_5458725bba43b6_16607711',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5458725bba43b6_16607711')) {function content_5458725bba43b6_16607711($_smarty_tpl) {?><?php if (!is_callable('smarty_block_form')) include '/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_smarty/plugin/block.form.php';
if (!is_callable('smarty_function_text')) include '/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_smarty/plugin/function.text.php';
if (!is_callable('smarty_function_input')) include '/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_smarty/plugin/function.input.php';
if (!is_callable('smarty_function_submit')) include '/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_smarty/plugin/function.submit.php';
?><!-- ----------------------MAILBOX SEND NEW MESSAGE--------------------------- -->
<div class="ow_chat_dialog ow_mailchat_new_message " id="newMessageWindow">
    <div class="ow_chat_block ow_mailchat_select_user_wrap">
        <?php $_smarty_tpl->smarty->_tag_stack[] = array('form', array('name'=>"mailbox-new-message-form")); $_block_repeat=true; echo smarty_block_form(array('name'=>"mailbox-new-message-form"), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

        <!-- ---- NEW MESSAGE CAP ---->
        <!-- ----------------------- SELECTED USER CAP --------------------------- -->
        <div class="ow_mailchat_selected_user ow_author_block clearfix">
            <div aria-disabled="false" style="position: absolute;" class="ow_puller ui-draggable"></div>
            <a href="javascript://" target="_blank" class="ow_chat_in_item_author_href ow_chat_in_item_photo_wrap" id="userFieldProfileLink">
                <span class="ow_chat_in_item_photo"><img title="" alt="" src="<?php echo $_smarty_tpl->tpl_vars['defaultAvatarUrl']->value;?>
" height="32px" width="32px" id="userFieldAvatar"></span>
            </a>
            <a href="javascript://" class="ow_chat_item_author_wrap" id="newMessageWindowMinimizeBtn">
                <span class="ow_chat_item_author">
                    <span class="ow_chat_message_to"><?php echo smarty_function_text(array('key'=>'mailbox+new_message_to'),$_smarty_tpl);?>
</span>
                    <span class="ow_chat_in_item_author" id="userFieldDisplayname"></span>
                </span>
                <span class="ow_mailchat_delete_receiver" id="userFieldDeleteBtn"></span>
            </a>
            <a class="ow_btn_close" id="newMessageWindowCloseBtn" href="javascript://"><span></span></a>
        </div>
        <!-- ----------------------- UNSELECTED USER CAP --------------------------- -->
        <div class="ow_mailchat_select_user ow_author_block">
            <div aria-disabled="false" style="position: absolute;" class="ow_puller ui-draggable"></div>
            <?php echo smarty_function_input(array('name'=>'opponentId'),$_smarty_tpl);?>

            <a href="javascript://" class="ow_chat_minimize_btn" id="newMessageWindowUnselectedCapMinimizeBtn"></a>
            <a class="ow_btn_close" id="newMessageWindowUnselectedCapCloseBtn" href="javascript://"><span></span></a>
            <div class="ow_mailchat_new_message_title"><?php echo smarty_function_text(array('key'=>"mailbox+new_message_title"),$_smarty_tpl);?>
</div>
        </div>
        <!-- ----------------------- END OF USER CAP --------------------------- -->
        <!-- ---- END OF NEW MESSAGE CAP ---->
        <div class="ow_chat_subject_block">
            <?php echo smarty_function_input(array('name'=>'subject','class'=>"newMessageWindowSubjectInputControl"),$_smarty_tpl);?>

        </div>
        <div class="ow_chat_mailchat_inputarea">
            <?php echo smarty_function_input(array('name'=>'message','class'=>"newMessageWindowMessageInputControl"),$_smarty_tpl);?>

            <?php if ($_smarty_tpl->tpl_vars['enableAttachments']->value){?>
            <div class="ow_file_attachment_preview clearfix">
            <?php echo $_smarty_tpl->tpl_vars['attachments']->value;?>

            </div>
            <?php }?>
            <div class="ow_file_attachment_preview clearfix" id="newMessageWindowEmbedAttachmentsBlock"></div>
            <div class="ow_chat_mailchat_buttons clearfix">
                <span class="ow_attachment_btn">
                    <?php echo smarty_function_submit(array('name'=>"send"),$_smarty_tpl);?>

                </span>
                <?php if ($_smarty_tpl->tpl_vars['enableAttachments']->value){?>
                <span class="ow_attachment_icons">
                    <div class="ow_attachments">
                        <span class="buttons clearfix">
                            <a class="attach" href="javascript://" title="Attach" id="newMessageWindowAttachmentsBtn"></a>
                        </span>
                    </div>
                </span>
                <?php }?>
            </div>
            
        </div>

        <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_form(array('name'=>"mailbox-new-message-form"), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

    </div>
</div>
<!-- -----------------------END OF SEND NEW MESSAGE---------------------------- --><?php }} ?>