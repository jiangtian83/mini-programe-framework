<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>

<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">
            表单管理    
        </h3>
    </div>
    <div class="panel-body">
       <form action="" method="post" class="form-horizontal form">
            <input type="hidden" name="storeid" value="">
            <div class="table-responsive panel-body">
                <table class="table table-hover">
                    <thead class="navbar-inner">
                    <tr>
                       <th>id</th>
                        <th>状态</th>
                       
                        <th >时间</th>
                        <th style="text-align:right;">操作</th>
                    </tr>
                    </thead>
                    <tbody id="level-list">
                    <?php  if(is_array($list)) { foreach($list as $index => $item) { ?>
                     <tr>
                     <td><div class="type-parent"><?php  echo $item['id'];?></div></td>
                       <td><div class="type-parent"><?php  if($item['read']==1) { ?>已读<?php  } else { ?>未读<?php  } ?></div></td>
                      
                        <td><div class="type-parent"><?php  echo $item['addtime'];?></div></td>
                        <td style="text-align:right;"><a class="btn btn-default btn-sm" href="<?php  echo url('site/entry/formRead',array('id'=>$item['id'],'m'=>'yyf_baidu'));?>" title="查看">查看</a>&nbsp;&nbsp;<a class="btn btn-default btn-sm" href="<?php  echo url('site/entry/formDelete',array('id'=>$item['id'],'m'=>'yyf_baidu'));?>" onclick="return confirm('确认删除吗？');return false;" title="删除">删</a></td>
                    </tr>
                    <?php  } } ?>     
                    </tbody>
                </table>
                 <?php  echo $pager;?>   
            </div>
        </form>
    </div>
</div>


<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>