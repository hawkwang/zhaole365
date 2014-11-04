<?php /* Smarty version Smarty-3.1.12, created on 2014-11-04 10:49:27
         compiled from "/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_system_plugins/base/views/components/menu.html" */ ?>
<?php /*%%SmartyHeaderCode:68258830054583eb7131562-26003404%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'cbf25491aecda22ebfb0ee03aa3e6aa4b3fdab2f' => 
    array (
      0 => '/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_system_plugins/base/views/components/menu.html',
      1 => 1409085892,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '68258830054583eb7131562-26003404',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'class' => 0,
    'data' => 0,
    'item' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_54583eb715c251_89065209',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54583eb715c251_89065209')) {function content_54583eb715c251_89065209($_smarty_tpl) {?><ul class="<?php echo $_smarty_tpl->tpl_vars['class']->value;?>
 clearfix">
<?php  $_smarty_tpl->tpl_vars['item'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['item']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['data']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['item']->key => $_smarty_tpl->tpl_vars['item']->value){
$_smarty_tpl->tpl_vars['item']->_loop = true;
?><li class="<?php echo $_smarty_tpl->tpl_vars['item']->value['class'];?>
<?php if (!empty($_smarty_tpl->tpl_vars['item']->value['active'])){?> active<?php }?>"><a href="<?php echo $_smarty_tpl->tpl_vars['item']->value['url'];?>
"<?php if ($_smarty_tpl->tpl_vars['item']->value['new_window']){?> target="_blank"<?php }?>><span><?php echo $_smarty_tpl->tpl_vars['item']->value['label'];?>
</span></a></li><?php } ?>
</ul><?php }} ?>