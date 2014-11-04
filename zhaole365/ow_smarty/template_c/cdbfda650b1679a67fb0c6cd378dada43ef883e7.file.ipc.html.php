<?php /* Smarty version Smarty-3.1.12, created on 2014-11-04 10:49:27
         compiled from "/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_system_plugins/base/decorators/ipc.html" */ ?>
<?php /*%%SmartyHeaderCode:88819163854583eb707da93-60111164%%*/if(!defined('SMARTY_DIR')) exit('no direct access allowed');
$_valid = $_smarty_tpl->decodeProperties(array (
  'file_dependency' => 
  array (
    'cdbfda650b1679a67fb0c6cd378dada43ef883e7' => 
    array (
      0 => '/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_system_plugins/base/decorators/ipc.html',
      1 => 1409085892,
      2 => 'file',
    ),
  ),
  'nocache_hash' => '88819163854583eb707da93-60111164',
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
  'unifunc' => 'content_54583eb711f801_47959360',
),false); /*/%%SmartyHeaderCode%%*/?>
<?php if ($_valid && !is_callable('content_54583eb711f801_47959360')) {function content_54583eb711f801_47959360($_smarty_tpl) {?><?php if (!is_callable('smarty_block_style')) include '/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_smarty/plugin/block.style.php';
if (!is_callable('smarty_function_decorator')) include '/Users/hawkwang/BUPT/teaching/class_1/zhaole365/ow_smarty/plugin/function.decorator.php';
?>
<?php $_smarty_tpl->smarty->_tag_stack[] = array('style', array()); $_block_repeat=true; echo smarty_block_style(array(), null, $_smarty_tpl, $_block_repeat);while ($_block_repeat) { ob_start();?>


.ow_ipc_toolbar span{
    display:inline-block;
}

<?php $_block_content = ob_get_clean(); $_block_repeat=false; echo smarty_block_style(array(), $_block_content, $_smarty_tpl, $_block_repeat);  } array_pop($_smarty_tpl->smarty->_tag_stack);?>

<div class="ow_ipc <?php if (!empty($_smarty_tpl->tpl_vars['data']->value['addClass'])){?><?php echo $_smarty_tpl->tpl_vars['data']->value['addClass'];?>
<?php }?> clearfix">
	<div class="ow_ipc_picture">
        <?php if (!empty($_smarty_tpl->tpl_vars['data']->value['avatar'])){?>
            <?php echo smarty_function_decorator(array('name'=>'avatar_item','data'=>$_smarty_tpl->tpl_vars['data']->value['avatar']),$_smarty_tpl);?>

        <?php }else{ ?>
            <img src="<?php echo $_smarty_tpl->tpl_vars['data']->value['imageSrc'];?>
" alt="<?php echo $_smarty_tpl->tpl_vars['data']->value['imageTitle'];?>
" title="<?php echo $_smarty_tpl->tpl_vars['data']->value['imageTitle'];?>
" />
    	<?php }?>
    </div>
	<div class="ow_ipc_info">
		<?php if (isset($_smarty_tpl->tpl_vars['data']->value['infoString'])){?><div class="ow_ipc_header"><?php echo $_smarty_tpl->tpl_vars['data']->value['infoString'];?>
</div><?php }?>
		<div class="ow_ipc_content"><?php echo $_smarty_tpl->tpl_vars['data']->value['content'];?>
</div>
		<?php if (isset($_smarty_tpl->tpl_vars['data']->value['toolbar'])&&$_smarty_tpl->tpl_vars['data']->value['toolbar']){?>
      		<div class="clearfix">
      		<div class="ow_ipc_toolbar ow_remark">
      		<?php  $_smarty_tpl->tpl_vars['item'] = new Smarty_Variable; $_smarty_tpl->tpl_vars['item']->_loop = false;
 $_from = $_smarty_tpl->tpl_vars['data']->value['toolbar']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array');}
 $_smarty_tpl->tpl_vars['item']->index=-1;
foreach ($_from as $_smarty_tpl->tpl_vars['item']->key => $_smarty_tpl->tpl_vars['item']->value){
$_smarty_tpl->tpl_vars['item']->_loop = true;
 $_smarty_tpl->tpl_vars['item']->index++;
 $_smarty_tpl->tpl_vars['item']->first = $_smarty_tpl->tpl_vars['item']->index === 0;
 $_smarty_tpl->tpl_vars['smarty']->value['foreach']['toolbar']['first'] = $_smarty_tpl->tpl_vars['item']->first;
?>
                <?php if (!$_smarty_tpl->getVariable('smarty')->value['foreach']['toolbar']['first']&&(empty($_smarty_tpl->tpl_vars['item']->value['class'])||(!strstr($_smarty_tpl->tpl_vars['item']->value['class'],'ow_ipc_date')))){?> <span class="ow_delimiter">&middot;</span> <?php }?>
      		    <span class="ow_nowrap<?php if (!empty($_smarty_tpl->tpl_vars['item']->value['class'])){?> <?php echo $_smarty_tpl->tpl_vars['item']->value['class'];?>
<?php }?>">
      		    <?php if (isset($_smarty_tpl->tpl_vars['item']->value['href'])){?><a <?php if (isset($_smarty_tpl->tpl_vars['item']->value['id'])){?> id="<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
"<?php }?> href="<?php echo $_smarty_tpl->tpl_vars['item']->value['href'];?>
"><?php }?>
      		    <?php echo $_smarty_tpl->tpl_vars['item']->value['label'];?>

      		    <?php if (isset($_smarty_tpl->tpl_vars['item']->value['href'])){?></a><?php }?>
      		    </span>
      		<?php } ?>
      		</div>
      		</div>
        <?php }?>
   </div>
</div><?php }} ?>