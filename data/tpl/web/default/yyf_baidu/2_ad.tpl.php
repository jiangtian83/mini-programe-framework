<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>

<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">
            智能广告位管理   <a class="btn btn-success" href="<?php  echo url('site/entry/ad',array('m'=>'yyf_baidu','op'=>'add'));?>" style="margin-left: 10px;color:white">添加广告位</a>

        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="post" action="" name="submit">
            <div class="alert alert-info" role="alert">
                图片为自适应。小提示：如果有多张广告图在同一位置显示，可以根据排序来决定先后位置！
            </div>
            <div class="alert alert-info url_div hide" role="alert">

            </div>
            <div class="table-responsive panel-body">
                <table class="table table-hover">
                    <thead class="navbar-inner">
                    <tr>
                        <th>排序</th>
                        <th>ID</th>
                        <th>广告位描述</th>
                        <th>显示位置</th>
                        <th>广告图样式</th>
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
                        <td><div class="type-parent"><?php  echo $item['adinfo'];?></div></td>
                        <td><div class="type-parent"><?php  echo $item['pstr'];?></div></td>
                        <td><div class="type-parent"><?php  echo $item['sstr'];?></div></td>
                        <td style="text-align:right;">

                            <a class="btn btn-default btn-sm" href="<?php  echo url('site/entry/ad',array('id'=>$item['id'],'m'=>'yyf_baidu','op'=>'edit'));?>" title="编辑">改</a>
                            &nbsp;&nbsp;<a class="btn btn-default btn-sm" href="<?php  echo url('site/entry/ad',array('id'=>$item['id'],'m'=>'yyf_baidu','op'=>'del'));?>" onclick="return confirm('确认删除吗？');return false;" title="删除">删</a></td>
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



<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>