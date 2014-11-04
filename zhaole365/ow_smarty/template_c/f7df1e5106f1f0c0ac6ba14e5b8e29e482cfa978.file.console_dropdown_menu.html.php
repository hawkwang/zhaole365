<?php /* Smarty version Smarty-3.1.12, created on 2014-11-04 14:46:12
         compiled from "/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_system_plugins/base/views/components/console_dropdown_menu.html" */ ?>
<?php /*%%SmartyHeaderCode:30035625754587634767496-00753867%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'f7df1e5106f1f0c0ac6ba14e5b8e29e482cfa978' => 
    array (
      0 => '/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_system_plugins/base/views/components/console_dropdown_menu.html',
      1 => 1409085892,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '30035625754587634767496-00753867',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'items' => 0,
    'sitems' => 0,
    'item' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_545876347b3936_49806234',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_545876347b3936_49806234')) {function content_545876347b3936_49806234($_smarty_tpl) {?><ul class="ow_console_dropdown">
    <?php  $_smarty_tpl->tpl_vars["sitems"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["sitems"]->_loop = false;
 $_smarty_tpl->tpl_vars["section"] = new Smarty_Variable;
 $_from = $_smarty_tpl->tpl_vars['items']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars["sitems"]->total= $_smarty_tpl->_count($_from);
 $_smarty_tpl->tpl_vars["sitems"]->iteration=0;
foreach ($_from as $_smarty_tpl->tpl_vars["sitems"]->key => $_smarty_tpl->tpl_vars["sitems"]->value){
$_smarty_tpl->tpl_vars["sitems"]->_loop = true;
 $_smarty_tpl->tpl_vars["section"]->value = $_smarty_tpl->tpl_vars["sitems"]->key;
 $_smarty_tpl->tpl_vars["sitems"]->iteration++;
 $_smarty_tpl->tpl_vars["sitems"]->last = $_smarty_tpl->tpl_vars["sitems"]->iteration === $_smarty_tpl->tpl_vars["sitems"]->total;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']["cddm"]['last'] = $_smarty_tpl->tpl_vars["sitems"]->last;
?>
        <?php  $_smarty_tpl->tpl_vars["item"] = new Smarty_Variable; $_smarty_tpl->tpl_vars["item"]->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['sitems']->value; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
foreach ($_from as $_smarty_tpl->tpl_vars["item"]->key => $_smarty_tpl->tpl_vars["item"]->value){
$_smarty_tpl->tpl_vars["item"]->_loop = true;
?>
            <li class="<?php if (!empty($_smarty_tpl->tpl_vars['item']->value['class'])){?><?php echo $_smarty_tpl->tpl_vars['item']->value['class'];?>
<?php }?> ow_dropdown_menu_item ow_cursor_pointer" >
                <div class="ow_console_dropdown_cont">
                    <a href="<?php echo $_smarty_tpl->tpl_vars['item']->value['url'];?>
"><?php echo $_smarty_tpl->tpl_vars['item']->value['label'];?>
</a>
                </div>
            </li>
        <?php } ?>

        <?php if (!$_smarty_tpl->getVariable('smarty')->value['foreach']['cddm']['last']){?>
            <li><div class="ow_console_divider"></div></li>
        <?php }?>
    <?php } ?>
</ul><?php }} ?>