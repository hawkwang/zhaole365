<?php /* Smarty version Smarty-3.1.12, created on 2014-11-04 14:46:12
         compiled from "/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_system_plugins/base/views/components/console_dropdown_hover.html" */ ?>
<?php /*%%SmartyHeaderCode:1804217168545876347bbe55-92454652%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '112a58b41858bd719e5405204397977ff718b7d9' => 
    array (
      0 => '/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_system_plugins/base/views/components/console_dropdown_hover.html',
      1 => 1409085892,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '1804217168545876347bbe55-92454652',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'url' => 0,
    'label' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_545876347c99a1_05310048',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_545876347c99a1_05310048')) {function content_545876347c99a1_05310048($_smarty_tpl) {?><a href="<?php echo $_smarty_tpl->tpl_vars['url']->value;?>
" class="ow_console_item_link"><?php echo $_smarty_tpl->tpl_vars['label']->value;?>
</a>
<span class="ow_console_more"></span><?php }} ?>