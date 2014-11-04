<?php /* Smarty version Smarty-3.1.12, created on 2014-11-04 10:49:27
         compiled from "/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_system_plugins/base/decorators/tooltip.html" */ ?>
<?php /*%%SmartyHeaderCode:196336472354583eb738aa18-77746892%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    '6a93958fab795b9664fc79357738896b99d061d4' => 
    array (
      0 => '/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_system_plugins/base/decorators/tooltip.html',
      1 => 1409085892,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '196336472354583eb738aa18-77746892',
  'function' => 
  array (
  ),
  'variables' => 
  array (
    'data' => 0,
  ),
  'has_nocache_code' => false,
  'version' => 'Smarty-3.1.12',
  'unifunc' => 'content_54583eb73a4041_09709426',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54583eb73a4041_09709426')) {function content_54583eb73a4041_09709426($_smarty_tpl) {?>
<div class="ow_tooltip <?php if (!empty($_smarty_tpl->tpl_vars['data']->value['addClass'])){?> <?php echo $_smarty_tpl->tpl_vars['data']->value['addClass'];?>
<?php }?>">
    <div class="ow_tooltip_tail">
        <span></span>
    </div>
    <div class="ow_tooltip_body">
        <?php echo $_smarty_tpl->tpl_vars['data']->value['content'];?>

    </div>
</div><?php }} ?>