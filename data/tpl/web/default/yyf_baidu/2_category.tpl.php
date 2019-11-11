<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>

<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">
            分类列表   <a class="btn btn-success" href="<?php  echo url('site/entry/AddCat',array('m'=>'yyf_baidu'));?>" style="margin-left: 10px;color:white">添加顶级分类</a>
            <?php  if($isson==false) { ?><a class="btn btn-success" href="<?php  echo url('site/entry/AddCat',array('m'=>'yyf_baidu','son'=>'son'));?>" style="margin-left: 10px; color: white;">添加子类</a><?php  } ?>
            <?php  if($isson==true) { ?><a class="btn btn-success" href="<?php  echo url('site/entry/category',array('m'=>'yyf_baidu'));?>" style="margin-left: 10px; color: white;">返回</a><?php  } ?>
        </h3>
    </div>
    <div class="panel-body">
       <form class="form-horizontal" role="form" method="post" action="" name="submit">
            <div class="alert alert-info" role="alert">
               分类跳转地址，点击列表里的点击显示按钮
            </div>
            <div class="alert alert-info url_div hide" role="alert">
                
       </div>
            <div class="table-responsive panel-body">
                <table class="table table-hover">
                    <thead class="navbar-inner">
                    <tr>
                      <th>排序</th>
                      <th>ID</th>
                        <th>分类名称</th>
                        <?php  if($isson==false) { ?><th>栏目图片</th><?php  } ?> 
                        <?php  if($isson==false) { ?><th>栏目地址</th><?php  } ?> 
                       
                        <?php  if($isson==false) { ?><th>子类管理</th><?php  } ?> 
                        <th style="text-align:right;">操作</th>
                    </tr>
                    </thead>
                    <tbody id="level-list">
                    <?php  if(is_array($result)) { foreach($result as $index => $item) { ?>
                     <tr>
                     <td>
                     <input type="text" class="form-control" style="width:50px"  value="<?php  echo $item['sortrank'];?>" name="sortrank[]"/>
                     <input type="hidden" class="form-control" style="width:50px"  value="<?php  echo $item['id'];?>" name="id[]"/>
                     <input type="hidden" name="token" value="<?php  echo $_W['token'];?>">
                     </td>
                      <td><div class="type-parent"><?php  echo $item['id'];?></div></td>
                       <td><div class="type-parent"><?php  echo $item['name'];?></div></td>
                      <?php  if($isson==false) { ?> <td> <div class="type-parent"><img src="<?php  echo tomedia($item['thumb']);?>" width="50" height="50" /></div></td><?php  } ?>
                       <?php  if($isson==false) { ?><td><div class="type-parent url_btn" ><span class="label label-success">点击显示</span><span class="hide urlvalue"><?php  echo $item['path'];?></span></div></td><?php  } ?>
                       <?php  if($isson==false) { ?><td><a class="btn btn-default btn-sm" href="<?php  echo url('site/entry/category',array('son'=>$item['id'],'m'=>'yyf_baidu'));?>" title="编辑">管理子类</a></td><?php  } ?>

                        <td style="text-align:right;">
                        <?php  if($isson==false) { ?>    
                        <a class="btn btn-default btn-sm" href="<?php  echo url('site/entry/EditCat',array('id'=>$item['id'],'m'=>'yyf_baidu'));?>" title="编辑">改</a>
                        <?php  } else { ?>
                         <a class="btn btn-default btn-sm" href="<?php  echo url('site/entry/EditCat',array('id'=>$item['id'],'m'=>'yyf_baidu','son'=>'son'));?>" title="编辑">改</a>
                         <?php  } ?>
                        &nbsp;&nbsp;<a class="btn btn-default btn-sm" href="<?php  echo url('site/entry/DelCat',array('id'=>$item['id'],'m'=>'yyf_baidu'));?>" onclick="return confirm('确认删除此分类吗？');return false;" title="删除">删</a></td>
                    </tr>
                    <?php  } } ?>   

                    </tbody>
                </table>
                    <div class="form-group">
                
                <div class="col-sm-offset-0 col-sm-12">
                    <input type="submit" name="submit" class="btn btn-default" value="排序">
                    
                </div>
            </div>  
            </div>
        </form>
    </div>
</div>
<style type="text/css">
    .hide{display: none}
</style>
<script type="text/javascript">
    $(function(){
        $('.url_btn').click(function(){
            var str=$(this).children('.urlvalue').text();
            $('.url_div').text('跳转地址：'+str);
            $('.url_div').removeClass('hide');
        })
    })
</script>

<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>