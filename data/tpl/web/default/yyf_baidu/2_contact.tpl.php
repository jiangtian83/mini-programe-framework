<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>

<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">
            联系我们设置
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="post" action="" name="submit">

        	<div class="alert alert-info" role="alert">
        		电话号码2、邮箱、QQ、微信、公司网址。如果不想在联系我们页面显示，留空即可,<font color="red">(<script>document.write(unescape('\u6298\u002f\u7ffc\u005c\u5929\u002f\u4f7f\u002f\u8d44\u002f\u6e90\u002f\u793e\u005c\u533a\u002f\u63d0\u005c\u4f9b\u000d\u000a'));</script>)</font></a>
。
        		如果有需求想在公众号自定义菜单里跳转到联系我们页面，跳转地址为：yyf_baidu/pages/contact/contact
        	</div>

        	<div class="form-group">
				<!--<label  class="col-sm-2 control-label">模板样式选择</label>-->
				<!--<div class="col-sm-5">-->
					<!--<div class="col-sm-12" style="text-align: center;">-->
					<!--<img src="<?php echo MODULE_URL;?>images/c_1.jpg">-->
					<!--</div>-->
					<!--<div class="col-sm-12" style="text-align: center;">-->
						<!--<input type="radio" name="c_templet" value="2"  style="width: 20px; height: 20px" <?php  if($result['c_templet']=='2') { ?> checked <?php  } ?>>-->
					<!--</div>-->
				<!--</div>-->
				<!--<div class="col-sm-5">-->
					<!--<div class="col-sm-12" style="text-align: center;">-->
					<!--<img src="<?php echo MODULE_URL;?>images/c_2.jpg">-->
					<!--</div>-->
					<!--<div class="col-sm-12" style="text-align: center;">-->
						<!--<input type="radio" name="c_templet" value="3" style="width: 20px; height: 20px" <?php  if($result['c_templet']=='3') { ?> checked <?php  } ?>>-->
					<!--</div>-->
				<!--</div>-->
			</div>

			<div class="form-group">
				<label  class="col-sm-2 control-label">联系人姓名</label>
				<div class="col-sm-10">
					<input type="text" class="form-control"  name="contact_username" value="<?php  echo $result['contact_username'];?>">
				</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-2 control-label">电话号码</label>
				<div class="col-sm-10">
					<input type="text" class="form-control"  name="phone" value="<?php  echo $result['phone'];?>">
					<input type="hidden" name="token" value="<?php  echo $_W['token'];?>">
				</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-2 control-label">电话号码2</label>
				<div class="col-sm-6">
					<input type="text" class="form-control"  name="phone2" value="<?php  echo $result['phone2'];?>">
				</div>
				
			</div>
			<div class="form-group">
				<label  class="col-sm-2 control-label">邮箱</label>
				<div class="col-sm-10">
					<input type="text" class="form-control"  name="email" value="<?php  echo $result['email'];?>">
				</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-2 control-label">QQ</label>
				<div class="col-sm-10">
					<input type="text" class="form-control"  name="qq" value="<?php  echo $result['qq'];?>">
				</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-2 control-label">微信</label>
				<div class="col-sm-10">
					<input type="text" class="form-control"  name="wechat" value="<?php  echo $result['wechat'];?>">
				</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-2 control-label">公司网址</label>
				<div class="col-sm-10">
					<input type="text" class="form-control"  name="website" value="<?php  echo $result['website'];?>">
				</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-2 control-label">公司地址</label>
				<div class="col-sm-10">
					<input type="text" class="form-control"  name="address" value="<?php  echo $result['address'];?>">
				</div>
			</div>
			<div class="form-group">
				<label for="firstname" class="col-sm-2 control-label">公司名称</label>
				<div class="col-sm-5">
					<input type="text" class="form-control"  name="contact_name" value="<?php  echo $item['contact_name'];?>">
				</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-2 control-label">公司地址坐标</label>
				<div class="col-sm-5">
					<input type="text" class="form-control"  name="position" value="<?php  echo $result['jing'];?>,<?php  echo $result['wei'];?>" placeholder="35.399660,119.503670">
				</div>
				<div class="col-sm-5"><a href="http://lbs.qq.com/tool/getpoint/" target="blank" style="color: red">点击进入地图选取坐标</a></div>
			</div>
		
			<!--<div class="form-group" id="myisshow">-->
				<!--<label  class="col-sm-2 control-label">隐藏在线客服</label>-->
				<!--<div class="col-sm-7">-->
					 <!--<div class="checkbox">-->
        				<!--<label><input type="checkbox"  name="custom_close" <?php  if($item['custom_close']) { ?> checked="checked"<?php  } ?> /></label>-->
        			 <!--</div>	-->
				<!--</div>-->
				<!--<div class="col-sm-3">-->
				<!--</div>-->
			<!--</div>-->

			<div class="form-group">
				<label  class="col-sm-2 control-label"> logo图片</label>
				<div class="col-sm-8">
					 <?php  echo tpl_form_field_image('contact_logo',$item['contact_logo']);?>
				</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-2 control-label">顶部的图片</label>
				<div class="col-sm-8">
					 <?php  echo tpl_form_field_image('contact_background',$item['contact_background']);?>
				</div>
			</div>


			<div>
					模板1顶部的图片大小(600*360),联系我们的logo大小(100*100) 单位像素,喇叭的小图标可以到自定义底部栏目里提供的素材网站搜索下载
					<img src="<?php  echo $contactimg;?>" />
			</div>	

			

			<div class="form-group">
				
				<div class="col-sm-offset-2 col-sm-10">
					<input type="submit" name="submit" class="btn btn-default">
					
				</div>
			</div>

			
			
			
			
	</form>
    </div>
</div>

<script type="text/javascript">
	$(function(){
		$('input[name="copy_kind"]').click(function(){
			var copy_kind=$('input[type="radio"][name="copy_kind"]:checked').val();
			if(copy_kind==0){
				$('.calling').hide();
				$('.navapp').hide();
			}
			if(copy_kind==1){
				$('.calling').show();
				$('.navapp').hide();
			}
			if(copy_kind==2){
				$('.calling').hide();
				$('.navapp').show();
			}
		})
	})

</script>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>