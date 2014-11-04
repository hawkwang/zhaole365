<?php /* Smarty version Smarty-3.1.12, created on 2014-11-04 14:29:47
         compiled from "/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_system_plugins/base/views/components/console_list.html" */ ?>
<?php /*%%SmartyHeaderCode:4954686995458725be2ec65-19451665%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'eb8a88bb46bf6d04b1e59e1440d225078d1ca3b0' => 
    array (
      0 => '/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_system_plugins/base/views/components/console_list.html',
      1 => 1409085892,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '4954686995458725be2ec65-19451665',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'viewAll' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_5458725be55c31_53194369',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5458725be55c31_53194369')) {function content_5458725be55c31_53194369($_smarty_tpl) {?><?php if (!is_callable('smarty_function_text')) include '/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_smarty/plugin/function.text.php';
?><div class="ow_console_list_wrapper OW_ConsoleListContainer">
    <div class="ow_nocontent OW_ConsoleListNoContent"><?php echo smarty_function_text(array('key'=>'base+no_items'),$_smarty_tpl);?>
</div>
    <ul class="ow_console_list OW_ConsoleList">

    </ul>
    <div class="ow_preloader_content ow_console_list_preloader OW_ConsoleListPreloader" style="visibility: hidden"></div>
</div>

<?php if (!empty($_smarty_tpl->tpl_vars['viewAll']->value)){?>
    <div class="ow_console_view_all_btn_wrap"><a href="<?php echo $_smarty_tpl->tpl_vars['viewAll']->value['url'];?>
" class="ow_console_view_all_btn"><?php echo $_smarty_tpl->tpl_vars['viewAll']->value['label'];?>
</a></div>
<?php }?>
<?php }} ?>