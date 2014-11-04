<?php /* Smarty version Smarty-3.1.12, created on 2014-11-04 14:46:12
         compiled from "/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_system_plugins/base/views/components/console_dropdown_list.html" */ ?>
<?php /*%%SmartyHeaderCode:138789842554587634861808-35259180%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'ed7c6655cc4cce7de09da9a524164fca9a8177e6' => 
    array (
      0 => '/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_system_plugins/base/views/components/console_dropdown_list.html',
      1 => 1409085892,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '138789842554587634861808-35259180',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'label' => 0,
    'counter' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_54587634892f63_20432536',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54587634892f63_20432536')) {function content_54587634892f63_20432536($_smarty_tpl) {?><a href="javascript://" class="ow_console_item_link"><?php echo $_smarty_tpl->tpl_vars['label']->value;?>
</a>

<span <?php if (empty($_smarty_tpl->tpl_vars['counter']->value['number'])){?>style="display: none;"<?php }?> class="ow_count_wrap OW_ConsoleItemCounter" >
    <span class="<?php if ($_smarty_tpl->tpl_vars['counter']->value['active']){?>ow_count_active<?php }?> ow_count_bg OW_ConsoleItemCounterPlace">
        <span class="ow_count OW_ConsoleItemCounterNumber" <?php if (empty($_smarty_tpl->tpl_vars['counter']->value['number'])){?>style="visibility: hidden;"<?php }?>><?php echo $_smarty_tpl->tpl_vars['counter']->value['number'];?>
</span>
    </span>
</span>
<?php }} ?>