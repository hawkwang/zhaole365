<?php /* Smarty version Smarty-3.1.12, created on 2014-11-04 10:49:27
         compiled from "/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_system_plugins/base/views/components/bottom_menu.html" */ ?>
<?php /*%%SmartyHeaderCode:141708734454583eb7162b22-56915504%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '29f96e1482b40a3b405400420311190feaab1f78' => 
    array (
      0 => '/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_system_plugins/base/views/components/bottom_menu.html',
      1 => 1409085892,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '141708734454583eb7162b22-56915504',
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
  'unifunc' => 'content_54583eb7184a12_73781244',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54583eb7184a12_73781244')) {function content_54583eb7184a12_73781244($_smarty_tpl) {?><div class="ow_footer_menu">
<?php  $_smarty_tpl->tpl_vars['item'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['item']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['data']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['item']->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars['item']->iteration=0;
foreach ($_from as $_smarty_tpl->tpl_vars['item']->key => $_smarty_tpl->tpl_vars['item']->value){
$_smarty_tpl->tpl_vars['item']->_loop = true;
 $_smarty_tpl->tpl_vars['item']->iteration++;
 $_smarty_tpl->tpl_vars['item']->last = $_smarty_tpl->tpl_vars['item']->iteration === $_smarty_tpl->tpl_vars['item']->total;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['bottom_menu']['last'] = $_smarty_tpl->tpl_vars['item']->last;
?>
	<a href="<?php echo $_smarty_tpl->tpl_vars['item']->value['url'];?>
"<?php if ($_smarty_tpl->tpl_vars['item']->value['active']){?> class="active"<?php }?><?php if ($_smarty_tpl->tpl_vars['item']->value['new_window']){?> target="_blank"<?php }?>><?php echo $_smarty_tpl->tpl_vars['item']->value['label'];?>
</a><?php if (!$_smarty_tpl->getVariable('smarty')->value['foreach']['bottom_menu']['last']){?> | <?php }?>
<?php } ?>
</div><?php }} ?>