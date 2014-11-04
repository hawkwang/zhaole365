<?php /* Smarty version Smarty-3.1.12, created on 2014-11-04 14:46:12
         compiled from "/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_system_plugins/base/views/components/file_attachment.html" */ ?>
<?php /*%%SmartyHeaderCode:798787851545876346c94f5-01925651%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '80c168c94b83530b060e0eed97b190875278444c' => 
    array (
      0 => '/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_system_plugins/base/views/components/file_attachment.html',
      1 => 1409085892,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '798787851545876346c94f5-01925651',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'data' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_545876346ded86_39803588',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_545876346ded86_39803588')) {function content_545876346ded86_39803588($_smarty_tpl) {?><div id="<?php echo $_smarty_tpl->tpl_vars['data']->value['uid'];?>
">
    <div class="ow_file_attachment_preview clearfix"<?php if (empty($_smarty_tpl->tpl_vars['data']->value['showPreview'])){?> style="display:none;"<?php }?>></div>
    <?php if (empty($_smarty_tpl->tpl_vars['data']->value['selector'])){?>
    <div class="clearfix ow_status_update_btn_block">
        <span class="ow_attachment_icons">
            <div id="nfa-feed1" class="ow_attachments">
              <span class="buttons clearfix">
                  <a title="Attach" href="javascript://" class="attach"></a>
              </span>
            </div>
        </span>
    </div>
    <?php }?>
</div><?php }} ?>