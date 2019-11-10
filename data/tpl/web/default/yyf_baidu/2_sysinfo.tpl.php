<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<style type="text/css">
	.info{
		height: 35px; line-height: 35px; padding-top: 0px;
	}
</style>
<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">
            网站设置
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="post" action="" name="submit">
			<div class="alert alert-info" role="alert">
        		页面地址：yyf_baidu/pages/index/index
        	</div>
        	
        	
       		<div class="form-group">
				<label  class="col-sm-2 control-label">网站标题</label>
				<div class="col-sm-10">
					<input type="text" class="form-control" id="notice" name="title" value="<?php  echo $result['title'];?>">
					
				</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-2 control-label">网站公告</label>
				<div class="col-sm-7">
					<input type="text" class="form-control" id="notice" name="notice" value="<?php  echo $result['notice'];?>">
					<input type="hidden" name="token" value="<?php  echo $_W['token'];?>">
				</div>
				<div class="col-sm-3">
					公告内容不要写太多
				</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-2 control-label">网站seo标题</label>
				<div class="col-sm-6">
					<input type="text" class="form-control"  name="seo_title" value="<?php  echo $result['seo_title'];?>">
				</div>
				<div class="col-sm-4">
					如果开启了百度h5站点（也就是web化）功能，必须完善seo信息
				</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-2 control-label">网站seo关键词：</label>
				<div class="col-sm-6">
					<input type="text" class="form-control"  name="seo_keywords" value="<?php  echo $result['seo_keywords'];?>">
				</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-2 control-label">网站seo描述：</label>
				<div class="col-sm-10">
					<input type="text" class="form-control"  name="seo_desc" value="<?php  echo $result['seo_desc'];?>">
				</div>
			</div>
			
			<div class="form-group">
				<label  class="col-sm-2 control-label">首页转发标题</label>
				<div class="col-sm-7">
					<input type="text" class="form-control"  name="name" value="<?php  echo $result['name'];?>">
				</div>

			</div>




			<div class="form-group">
				<label  class="col-sm-2 control-label">表单提醒邮箱</label>
				<div class="col-sm-6">
					<input type="text" class="form-control"  name="message_email" value="<?php  echo $result['message_email'];?>">
				</div>
				<div class="col-sm-4">
					多个邮箱用英文逗号隔开。微信提醒设置方法：微信设置->通用->功能->开启QQ邮箱提醒
				</div>
			</div>
			

			<div class="form-group">
				<label  class="col-sm-2 control-label">发送邮箱：</label>
				<div class="col-sm-6">
					<input type="text" class="form-control"  name="smtp_email" value="<?php  echo $result['smtp_email'];?>">
				</div>
				<div class="col-sm-4">
					发送邮箱最好和提醒邮箱是同一个邮箱账号
					
				</div>
			</div>

			<div class="form-group">
				<label  class="col-sm-2 control-label">发送邮箱授权码</label>
				<div class="col-sm-6">
					<input type="text" class="form-control"  name="smtp_key" value="<?php  echo $result['smtp_key'];?>">
				</div>
				<div class="col-sm-4">
					<a href="<?php  echo url('site/entry/Smtp',array('m'=>'yyf_baidu'));?>" style="color: red; font-size: 16px" target="_blank">邮箱配置说明 </a>
				</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-2 control-label">邮件标题设置</label>
				<div class="col-sm-6">
					<input type="text" class="form-control"  name="message_title" value="<?php  echo $result['message_title'];?>">
				</div>
				<div class="col-sm-4">
					客户提交表单后，管理员邮箱收到邮件标题。比如:您有新的业务咨询，折C翼V天C使V资C源V社C区V提C供。
				</div>
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