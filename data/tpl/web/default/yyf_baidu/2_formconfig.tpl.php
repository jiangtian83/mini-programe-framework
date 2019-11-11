<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<style type="text/css">
	.col-sm-3 input,.col-sm-2 input{ float: left; width: 20px; height: 20px;} 
	.info{ margin:5px 0 0 5px; }
	TEXTAREA{ width: 200px; height: 170px; }
</style>
<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">
            表单自定义  
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="post" action="" name="submit">
        	<div class="alert alert-info" role="alert">
        		如果有需求想在公众号自定义菜单里跳转到表单页面，跳转地址为：yyf_baidu/pages/message/message
        	</div>
        	<!--<div class="form-group">-->
				<!--<label  class="col-sm-2 control-label">模板样式选择</label>-->
				<!--<div class="col-sm-5">-->
					<!--<div class="col-sm-12" style="text-align: center;">-->
					<!--<img src="<?php  echo $imgurl;?>form_templet1.jpg">-->
					<!--</div>-->
					<!--<div class="col-sm-12" style="text-align: center;">-->
						<!--<input type="radio" name="templet" value="0"  style="width: 20px; height: 20px" <?php  if($v['templet']=='0') { ?> checked <?php  } ?>>-->
					<!--</div>-->
				<!--</div>-->
				<!--<div class="col-sm-5">-->
					<!--<div class="col-sm-12" style="text-align: center;">-->
					<!--<img src="<?php  echo $imgurl;?>form_templet2.jpg">-->
					<!--</div>-->
					<!--<div class="col-sm-12" style="text-align: center;">-->
						<!--<input type="radio" name="templet" value="1" style="width: 20px; height: 20px" <?php  if($v['templet']=='1') { ?> checked <?php  } ?>>-->
					<!--</div>-->
				<!--</div>-->
			<!--</div>-->

			<div class="form-group" id="myisshow">
				<label for="lastname" class="col-sm-2 control-label">是否显示在首页下方</label>
				<div class="col-sm-7">
					 <div class="checkbox"> 
        				<label><input type="checkbox"   name="isshow" <?php  if($v['isshow']) { ?> checked="checked"<?php  } ?> /></label>
        			 </div>	
				</div>
			</div>

        	<div class="form-group">
				<label  class="col-sm-2 control-label">表单上方标题</label>
				<div class="col-sm-6">
					<input type="text" class="form-control"  name="catname" value="<?php  echo $v['catname'];?>" >
				</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-2 control-label">表单上方图片</label>
				<div class="col-sm-6">
					<?php  echo tpl_form_field_image('thumb',$v['thumb']);?>
				</div>
				<div class="col-sm-4">
					<a href="<?php  echo $imgurl;?>form_top.jpg" target="_blank" style="color:red; font-size: 18px;"> 下载素材图片</a>
				</div>
			</div>
        	<div class="form-group">
				<label  class="col-sm-2 control-label">表单上方的说明文字</label>
				<div class="col-sm-6">
					
					<input type="text" class="form-control"  name="desc" value="<?php  echo $v['desc'];?>" >
				</div>
			</div>

			<div class="form-group">
				<label  class="col-sm-2 control-label">表单提交时间间隔</label>
				<div class="col-sm-4">
					<input type="text" class="form-control"  name="interval" value="<?php  echo $v['interval'];?>">
				</div>
				<div class="col-sm-6">
					为防止恶意重复提交，这里设置同一个客户两次提交数据的间隔时间，单位是分钟。如不需要此功能，填写0即可
				</div>
			</div>

			<div class="form-group">
				<label  class="col-sm-2 control-label">提交成功后的提示</label>
				<div class="col-sm-4">
					<input type="text" class="form-control"  name="successtext" value="<?php  echo $v['successtext'];?>">
				</div>
				<div class="col-sm-6">
					客户提交表单成功后，弹出的提示。例如：感谢提交的信息，我们会及时与您联系！
				</div>
			</div>

			<div class="form-group">
				<label  class="col-sm-2 control-label">一行的文本框</label>
				<div class="col-sm-4">
					<input type="text" class="form-control"  name="t1name" value="<?php  echo $v['t1name'];?>" placeholder="这里填写文本框标题，如联系人、姓名">
					<input type="hidden" name="token" value="<?php  echo $_W['token'];?>">
				</div>
				<div class="col-sm-2">
					<input type="checkbox" class="form-control"  name="t1show" <?php  if($v['t1show']) { ?> checked="checked" <?php  } ?>> 
					<label class="info">启用</label>
				</div>
				<div class="col-sm-2">
					<input type="checkbox" class="form-control"  name="t1full" <?php  if($v['t1full']) { ?> checked="checked" <?php  } ?>> 
					<label class="info">必填</label>
				</div>
				<div class="col-sm-2">
					<input type="checkbox" class="form-control"  name="t1phone" <?php  if($v['t1phone']) { ?> checked="checked" <?php  } ?>>
					<label class="info">验证手机号</label>
				</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-2 control-label">一行的文本框</label>
				<div class="col-sm-4">
					<input type="text" class="form-control"  name="t2name" value="<?php  echo $v['t2name'];?>" placeholder="这里填写文本框标题，如联系人、姓名">
				</div>
				<div class="col-sm-2">
					<input type="checkbox" class="form-control"  name="t2show" <?php  if($v['t2show']) { ?> checked="checked" <?php  } ?> > 
					<label class="info">启用</label>
				</div>
				<div class="col-sm-2">
					<input type="checkbox" class="form-control"  name="t2full" <?php  if($v['t1full']) { ?> checked="checked" <?php  } ?> > 
					<label class="info">必填</label>
				</div>
				<div class="col-sm-2">
					<input type="checkbox" class="form-control"  name="t2phone" <?php  if($v['t2phone']) { ?> checked="checked" <?php  } ?>>
					<label class="info">验证手机号</label>
				</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-2 control-label">一行的文本框</label>
				<div class="col-sm-4">
					<input type="text" class="form-control"  name="t3name" value="<?php  echo $v['t3name'];?>" placeholder="这里填写文本框标题，如联系人、姓名">
				</div>
				<div class="col-sm-2">
					<input type="checkbox" class="form-control"  name="t3show" <?php  if($v['t3show']) { ?> checked="checked" <?php  } ?> > 
					<label class="info">启用</label>
				</div>
				<div class="col-sm-2">
					<input type="checkbox" class="form-control"  name="t3full" <?php  if($v['t3full']) { ?> checked="checked" <?php  } ?> > 
					<label class="info">必填</label>
				</div>
				<div class="col-sm-2">
					<input type="checkbox" class="form-control"  name="t3phone" <?php  if($v['t3phone']) { ?> checked="checked" <?php  } ?>>
					<label class="info">验证手机号</label>
				</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-2 control-label">一行的文本框</label>
				<div class="col-sm-4">
					<input type="text" class="form-control"  name="t4name" value="<?php  echo $v['t4name'];?>" placeholder="这里填写文本框标题，如联系人、姓名">
				</div>
				<div class="col-sm-2">
					<input type="checkbox" class="form-control"  name="t4show" <?php  if($v['t4show']) { ?> checked="checked" <?php  } ?> > 
					<label class="info">启用</label>
				</div>
				<div class="col-sm-2">
					<input type="checkbox" class="form-control"  name="t4full" <?php  if($v['t3full']) { ?> checked="checked" <?php  } ?> > 
					<label class="info">必填</label>
				</div>
				<div class="col-sm-2">
					<input type="checkbox" class="form-control"  name="t4phone" <?php  if($v['t4phone']) { ?> checked="checked" <?php  } ?>>
					<label class="info">验证手机号</label>
				</div>
			</div>

			<div class="form-group">
				<label  class="col-sm-2 control-label">多行的文本框</label>
				<div class="col-sm-4">
					<TEXTAREA class="form-control" placeholder="这里填写多行文本框标题" name="aname"><?php  echo $v['aname'];?></TEXTAREA>
				</div>
				<div class="col-sm-2">
					<input type="checkbox" class="form-control"  name="ashow" <?php  if($v['ashow']) { ?> checked="checked" <?php  } ?> > 
					<label class="info">启用</label>
				</div>
				<div class="col-sm-2">
					<input type="checkbox" class="form-control"  name="afull" <?php  if($v['afull']) { ?> checked="checked" <?php  } ?> > 
					<label class="info">必填</label>
				</div>
			</div>

			<div class="form-group">
				<label  class="col-sm-2 control-label">单选框</label>
				<div class="col-sm-4">
					<input type="text" class="form-control"  name="rname" value="<?php  echo $v['rname'];?>" placeholder="这里填写单选框标题"> 
				</div>
				<div class="col-sm-2">
					<input type="checkbox" class="form-control"  name="rshow" <?php  if($v['rshow']) { ?> checked="checked" <?php  } ?>> 
					<label class="info">启用</label>
				</div>
				<div class="col-sm-2">
					<input type="checkbox" class="form-control"  name="rfull" <?php  if($v['rfull']) { ?> checked="checked" <?php  } ?> > 
					<label class="info">必填</label>
				</div>
			</div>

			<div class="form-group">
				<label  class="col-sm-2 control-label">单选框的值</label>
				<div class="col-sm-10">
					<input type="text" class="form-control"  name="rvalue" value="<?php  echo $v['rvalue'];?>" placeholder="每个值用英文逗号隔开，例如：高中,本科,硕士"> 
				</div>
			</div>

			<div class="form-group">
				<label  class="col-sm-2 control-label">复选框</label>
				<div class="col-sm-4">
					<input type="text" class="form-control"  name="cname" value="<?php  echo $v['cname'];?>" placeholder="这里填写复选框标题"> 
				</div>
				<div class="col-sm-2">
					<input type="checkbox" class="form-control"  name="cshow" <?php  if($v['cshow']) { ?> checked="checked" <?php  } ?>> 
					<label class="info">启用</label>
				</div>
				<div class="col-sm-2">
					<input type="checkbox" class="form-control"  name="cfull"  <?php  if($v['cfull']) { ?> checked="checked" <?php  } ?>> 
					<label class="info">必填</label>
				</div>
			</div>

			<div class="form-group">
				<label  class="col-sm-2 control-label">复选框的值</label>
				<div class="col-sm-10">
					<input type="text" class="form-control"  name="cvalue" value="<?php  echo $v['cvalue'];?>" placeholder="每个值用英文逗号隔开，例如：篮球,游泳,羽毛球"> 
				</div>
			</div>

			<div class="form-group">
				
				<div class="col-sm-offset-2 col-sm-10">
					<input type="submit" name="submit" class="btn btn-default">
					
				</div>
			</div>


			
	</form>
	<?php  echo $page;?>
    </div>
</div>


<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>