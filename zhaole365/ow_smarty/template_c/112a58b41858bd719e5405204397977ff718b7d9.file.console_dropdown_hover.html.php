<?php /* Smarty version Smarty-3.1.12, created on 2014-11-04 10:49:27
         compiled from "/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_system_plugins/base/views/components/console_dropdown_hover.html" */ ?>
<?php /*%%SmartyHeaderCode:125742541454583eb733be96-21780799%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
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
  'nocache_hash' => '125742541454583eb733be96-21780799',
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
  'unifunc' => 'content_54583eb73475d0_08746110',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54583eb73475d0_08746110')) {function content_54583eb73475d0_08746110($_smarty_tpl) {?><a href="<?php echo $_smarty_tpl->tpl_vars['url']->value;?>
" class="ow_console_item_link"><?php echo $_smarty_tpl->tpl_vars['label']->value;?>
</a>
<span class="ow_console_more"></span><?php }} ?>