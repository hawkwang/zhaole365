<?php /* Smarty version Smarty-3.1.12, created on 2014-11-04 14:46:12
         compiled from "/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_plugins/mailbox/views/components/chat_user_list.html" */ ?>
<?php /*%%SmartyHeaderCode:137504826954587634666153-43232322%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'd04b0743d7a30bc484c5813262f4fd5da6dccb80' => 
    array (
      0 => '/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_plugins/mailbox/views/components/chat_user_list.html',
      1 => 1414912615,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '137504826954587634666153-43232322',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'useChat' => 0,
    'msg' => 0,
    'avatar_proto_data' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_545876346b1de1_26217892',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_545876346b1de1_26217892')) {function content_545876346b1de1_26217892($_smarty_tpl) {?><?php if (!is_callable('smarty_function_text')) include '/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_smarty/plugin/function.text.php';
if (!is_callable('smarty_block_form')) include '/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_smarty/plugin/block.form.php';
if (!is_callable('smarty_function_input')) include '/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_smarty/plugin/function.input.php';
?><?php if ($_smarty_tpl->tpl_vars['useChat']->value=='promoted'){?>
<div class="ow_chat_cont ow_hidden" id="chatPromotionBlock">
    <div class="ow_chat_wrap">
        <div class="ow_chat">
            <div class="ow_puller"></div>
            <div class="ow_chat_block_wrap ow_border">
                <div class="ow_chat_block ow_active">
                    <div class="ow_chat_block_main">
                        <div class="ow_top_panel">
                            <div class="ow_count_block">
                                <a href="javascript://" class="btn2_panel">
                                    <span class="ow_count_txt"><?php echo smarty_function_text(array('key'=>"mailbox+chat"),$_smarty_tpl);?>
</span>
                                </a>
                            </div>
                        </div>
                        <div class="ow_chat_in_block">
                            <div class="ow_chat_list showOnlineOnly ow_scrollable" style="width: 245px;">
                                <div style="display: table; height: 100%; width: 100%;">
                                    <div style="padding: 0 8px; text-align: center; font-size: 12px; display: table-cell; vertical-align: middle;">
                                        <?php echo $_smarty_tpl->tpl_vars['msg']->value;?>

                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="ow_bot_panel">
                        <div class="ow_count_block">
                            <a href="javascript://" class="btn2_panel"><span class="ow_count_txt"><?php echo smarty_function_text(array('key'=>"mailbox+chat"),$_smarty_tpl);?>
</span></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php }?>

<?php if ($_smarty_tpl->tpl_vars['useChat']->value=='available'){?>
<div class="ow_chat_cont">
    <div class="ow_chat_wrap">
        <div class="ow_chat">
            <div class="ow_puller"></div>
            <div class="ow_chat_block_wrap ow_border">
                <div class="ow_chat_block">
                    <?php $_smarty_tpl->smarty->_tag_stack[] = array('form', array('name'=>'im_user_settings_form')); $_block_repeat=true; echo smarty_block_form(array('name'=>'im_user_settings_form'), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>

                    <div class="ow_chat_block_main">
                        <div class="ow_top_panel">
                            <div class="ow_count_block">
                                <a href="javascript://" class="btn2_panel">
                                    <span class="ow_btn_sort_users" id="mailboxSortUsersPreference"><span class="ow_btn_sort_online"></span></span>
                                    <span class="ow_count_txt"><?php echo smarty_function_text(array('key'=>"mailbox+chat"),$_smarty_tpl);?>
</span>
                                <span class="ow_count_wrap">
									<span class="ow_count_bg totalUserOnlineCountBackground">
										<span class="ow_count totalUserOnlineCount">0</span>
									</span>
								</span>
                                </a>
                            </div>
                        </div>
                        <div class="ow_chat_in_block">
                            <div class="ow_chat_search">
                                <span><?php echo smarty_function_input(array('name'=>'im_find_contact','id'=>"im_find_contact"),$_smarty_tpl);?>
</span>
                            </div>

                            <div class="ow_chat_list" style="width: 245px;">
                                <div class="ow_chat_preloader"></div>
                                <ul></ul>
                            </div>

                        </div>
                    </div>
                    <div class="ow_bot_panel">
                        <div class="ow_count_block">
                            <a href="javascript://" class="btn2_panel"><span class="ow_count_txt"><?php echo smarty_function_text(array('key'=>"mailbox+chat"),$_smarty_tpl);?>
</span>
                                <span class="ow_count_wrap">
                                    <span class="ow_count_bg totalUserOnlineCountBackground">
                                        <span class="ow_count totalUserOnlineCount">0</span>
                                    </span>
                                </span>
                            </a>
                        </div>
                        <a href="javascript://" class="ow_btn_sound" id="mailboxSoundPreference"><span></span></a>
                    </div>
                    <?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_form(array('name'=>'im_user_settings_form'), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

                </div>
            </div>
        </div>
    </div>
</div>


<div id="ow_chat_prototypes" style="display: none;">

    <div id="ow_chat_list_proto">
        <ul>
            <li>
                <a href="javascript://" class="clearfix ow_chat_item">
                    <span class="ow_chat_item_photo_wrap">
                        <span class="ow_chat_item_photo">
                            <span class="ow_chat_in_item_photo"><img src="<?php echo $_smarty_tpl->tpl_vars['avatar_proto_data']->value['src'];?>
"  width="32px" height="32px" alt="" id="contactItemAvatarUrl" /></span>
                        </span>
                    </span>
                    <span class="ow_chat_item_author">
                        <span class="ow_chat_in_item_author" id="contactItemDisplayName"></span>
                    </span>
                    <span class="ow_count_wrap ow_count_active" style="display: none;"><span class="ow_count">0</span></span>
                    <div class="ow_chat_status" id="contactProfileStatus"></div>
                </a>
            </li>
        </ul>
    </div>
</div>
<?php }?><?php }} ?>