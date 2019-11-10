<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>

<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">
            幻灯片管理  <a class="btn btn-success" href="<?php  echo url('site/entry/AddSlide',array('m'=>'yyf_baidu'));?>" style="margin-left: 10px; color: white;">添加幻灯片</a>
        </h3>
    </div>
    <div class="panel-body">
       <form action="" method="post" class="form-horizontal form">
        <input type="hidden" name="token" value="<?php  echo $_W['token'];?>">
      
            <div class="table-responsive panel-body">
                <table class="table table-hover">
                    <thead class="navbar-inner">
                    <tr>
                        <th>排序</th>
                        <th>id</th>
                        <th >缩略图</th>
                        
                        <th style="text-align:right;">操作</th>
                    </tr>
                    </thead>
                    <tbody id="level-list">
                    <?php  if(is_array($list)) { foreach($list as $index => $item) { ?>
                     <tr>
                     <td><input type="text" class="form-control" style="width:50px"  value="<?php  echo $item['sortrank'];?>" name="sortrank[]"/></td>
                     <input type="hidden" class="form-control" style="width:50px"  value="<?php  echo $item['id'];?>" name="id[]"/>
                       <td><div class="type-parent"><?php  echo $item['id'];?></div></td>
                      <td><div class="type-parent"><img src="<?php  echo tomedia($item['images']);?>" width="150" height="100" /></div></td>
                        <td style="text-align:right;"><a class="btn btn-default btn-sm" href="<?php  echo url('site/entry/EditSlide',array('id'=>$item['id'],'m'=>'yyf_baidu'));?>" title="编辑">改</a><a class="btn btn-default btn-sm" href="<?php  echo url('site/entry/DeleteSlide',array('id'=>$item['id'],'m'=>'yyf_baidu'));?>" onclick="return confirm('确认删除吗？');return false;" title="删除">删</a></td>
                    </tr>
                    <?php  } } ?>     
                    </tbody>
                </table>
            </div>
             <div class="form-group">
                
                <div class="col-sm-offset-0 col-sm-12">
                    <input type="submit" name="submit" class="btn btn-default" value="排序">
                    
                </div>
            </div>  
        </form>


    </div>
</div>


<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>