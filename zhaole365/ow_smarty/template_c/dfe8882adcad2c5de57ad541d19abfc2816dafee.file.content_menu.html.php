<?php /* Smarty version Smarty-3.1.12, created on 2014-11-04 10:49:26
         compiled from "/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_system_plugins/base/views/components/content_menu.html" */ ?>
<?php /*%%SmartyHeaderCode:131383708154583eb6e6c267-24076441%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'dfe8882adcad2c5de57ad541d19abfc2816dafee' => 
    array (
      0 => '/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_system_plugins/base/views/components/content_menu.html',
      1 => 1409085892,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '131383708154583eb6e6c267-24076441',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'data' => 0,
    'item' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_54583eb6e9dcf4_01349679',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54583eb6e9dcf4_01349679')) {function content_54583eb6e9dcf4_01349679($_smarty_tpl) {?><div class="ow_content_menu_wrap">
<ul class="ow_content_menu clearfix">
 	<?php  $_smarty_tpl->tpl_vars['item'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['item']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['data']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars['item']->key => $_smarty_tpl->tpl_vars['item']->value){
$_smarty_tpl->tpl_vars['item']->_loop = true;
?>
    <li class="<?php echo $_smarty_tpl->tpl_vars['item']->value['class'];?>
 <?php if ($_smarty_tpl->tpl_vars['item']->value['active']){?> active<?php }?>"><a href="<?php echo $_smarty_tpl->tpl_vars['item']->value['url'];?>
"><span<?php if ($_smarty_tpl->tpl_vars['item']->value['iconClass']){?> class="<?php echo $_smarty_tpl->tpl_vars['item']->value['iconClass'];?>
"<?php }?>><?php echo $_smarty_tpl->tpl_vars['item']->value['label'];?>
</span></a></li>
	<?php } ?>
</ul>
</div><?php }} ?>