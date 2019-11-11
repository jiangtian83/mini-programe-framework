<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>

<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">
            内容管理 <a class="btn btn-success" href="<?php  echo url('site/entry/Addnews',array('m'=>'yyf_baidu'));?>" style="margin-left: 10px; color: white;">添加内容</a>   <div style="display: inline; margin-left: 30px;">
                <form class="form-inline" style="display: inline;" action="" method="get" id="form_search">
  <div class="form-group">
    <label for="exampleInputName2" style="color: #31708f;">按分类筛选:</label>
    <select class="form-control" name="cid" id="cate" style="width: 150px;"> 
      <option <?php  if(!$cid) { ?> selected="selected" <?php  } ?> value="<?php  echo $item['id'];?>" url="<?php  echo url('site/entry/News',array('m'=>'yyf_baidu'));?>">全部</option>
        <?php  if(is_array($cateArr)) { foreach($cateArr as $index => $item) { ?>
        <option <?php  if($item['id']==$cid) { ?> selected="selected" <?php  } ?> value="<?php  echo $item['id'];?>" url="<?php  echo url('site/entry/News',array('m'=>'yyf_baidu','cid'=>$item['id']));?>"><?php  echo $item['name'];?></option>
            <?php  if(is_array($item['son'])) { foreach($item['son'] as $index1 => $item1) { ?>
                <option  <?php  if($item1['id']==$cid) { ?> selected="selected" <?php  } ?> value="<?php  echo $item1['id'];?>" url="<?php  echo url('site/entry/News',array('m'=>'yyf_baidu','cid'=>$item1['id']));?>">&nbsp;&nbsp;&nbsp;&nbsp;___<?php  echo $item1['name'];?></option>
            <?php  } } ?>
        <?php  } } ?>
     </select>
  </div>
</form>

            </div>
        </h3>
    </div>
    <div class="panel-body">
      
        <div class="alert alert-info" role="alert">
               文章跳转地址，点击列表里的显示地址
       </div>
       <div class="alert alert-info url_div hide" role="alert">
                
       </div>
            <input type="hidden" name="storeid" value="">
            <div class="table-responsive panel-body">
                <table class="table table-hover">
                    <thead class="navbar-inner">
                    <tr>
                       <th>id</th>
                       <th>排序</th>
                        <th>标题</th>
                        <th >类别</th>
                        <th >时间</th>
                        <th >跳转地址</th>
                        <th style="text-align:right;">操作</th>
                    </tr>
                    </thead>
                    <tbody id="level-list">
                    <?php  if(is_array($list)) { foreach($list as $index => $item) { ?>
                     <tr>
                     <td><div class="type-parent"><?php  echo $item['id'];?></div></td>
                     <td><div class="type-parent" style="text-align: center;"><?php  echo $item['sortrank'];?></div></td>
                       <td><div class="type-parent"><?php  echo $item['title'];?></div></td>
                        <td><div class="type-parent"><?php  echo $item['name'];?></div></td>
                        <td><div class="type-parent"><?php  echo $item['addtime'];?></div></td>
                        <th ><div class="type-parent url_btn" ><span class="label label-success">点击显示</span><span class="hide urlvalue"><?php  echo $item['path'];?></span></div></th>
                        <td style="text-align:right;"><a class="btn btn-default btn-sm" href="<?php  echo url('site/entry/EditNews',array('id'=>$item['id'],'m'=>'yyf_baidu'));?>" title="编辑">改</a>&nbsp;&nbsp;<a class="btn btn-default btn-sm" href="<?php  echo url('site/entry/Delete',array('id'=>$item['id'],'m'=>'yyf_baidu'));?>" onclick="return confirm('确认删除此分类吗？');return false;" title="删除">删</a></td>
                    </tr>
                    <?php  } } ?>     
                    </tbody>
                </table>
                 <?php  echo $pager;?>   
            </div>
        
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

        $('#cate').change(function(){
            var id=$('#cate').find("option:selected").attr('url');
            //$('#form_search').submit();
           window.location.href=id;
        })
    })
</script>

<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>