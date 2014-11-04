<?php /* Smarty version Smarty-3.1.12, created on 2014-11-04 14:29:48
         compiled from "/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_themes/origin/master_pages/html_document.html" */ ?>
<?php /*%%SmartyHeaderCode:4579291075458725c043fe9-48248300%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '01e5ca9d136992c8150b90d02cbeadf634272669' => 
    array (
      0 => '/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_themes/origin/master_pages/html_document.html',
      1 => 1409085898,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '4579291075458725c043fe9-48248300',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'language' => 0,
    'direction' => 0,
    'title' => 0,
    'headData' => 0,
    'bodyClass' => 0,
    'pageBody' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_5458725c0760e5_35696371',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_5458725c0760e5_35696371')) {function content_5458725c0760e5_35696371($_smarty_tpl) {?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="<?php echo $_smarty_tpl->tpl_vars['language']->value;?>
" dir="<?php echo $_smarty_tpl->tpl_vars['direction']->value;?>
">
<head>
<title><?php echo $_smarty_tpl->tpl_vars['title']->value;?>
</title>
<?php echo $_smarty_tpl->tpl_vars['headData']->value;?>

</head>
<!--[if IE 8]><body class="ow ie8<?php echo $_smarty_tpl->tpl_vars['bodyClass']->value;?>
"><![endif]-->
<!--[if !IE 8]><!--><body class="ow<?php echo $_smarty_tpl->tpl_vars['bodyClass']->value;?>
"><!--<![endif]-->
<?php echo $_smarty_tpl->tpl_vars['pageBody']->value;?>

</body>
</html>
<?php }} ?>