<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<style type="text/css">
	.info{
		height: 35px; line-height: 35px; padding-top: 0px; 
	}
	.hide{ display: none; }
</style>
<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">
            全局样式
        </h3>
    </div>
    <div class="panel-body">
        <form class="form-horizontal" role="form" method="post" action="" name="submit">
        
			<div class="form-group" style="margin-bottom: 0px;">
				<label  class="col-sm-2 control-label">网站主题颜色</label>
				<div class="col-sm-6">
					<?php  echo tpl_form_field_color(tcolor, $item['tcolor']);?>
					<input type="hidden" name="token" value="<?php  echo $_W['token'];?>">
				</div>
				<div class="col-sm-4">
					<div class="alert alert-info info" role="alert" style="width: 290px">必须设置!蓝色模板使用的是: #2d7dee</div>
				</div>
			</div>
			<div class="form-group" style="margin-bottom: 10px;">
				<label  class="col-sm-2 control-label">单独设置小程序顶部</label>
				<div class="col-sm-7">
					 <div class="checkbox">
        				<label><input type="checkbox"  id="show_diy_head" /></label>
        			 </div>	
				</div>
			</div>
			<div class="hide" id="diy_head">
				<div class="alert alert-info info" role="alert" style="height: 150px; display: flex;"><img src="<?php echo MODULE_URL;?>images/diy_head.jpg" />顶部背景颜色默认是主题颜色，字体默认是白色。一般都无须单独设置</div>
				<div class="form-group" style="margin-bottom: 10px;">
					<label  class="col-sm-2 control-label">小程序顶部背景颜色</label>
					<div class="col-sm-6">
						<?php  echo tpl_form_field_color(head_color, $item['head_color']);?>
					</div>
					<div class="col-sm-4">
					</div>
				</div>
				<div class="form-group" style="margin-bottom: 10px;">
					<label  class="col-sm-2 control-label">小程序顶部标题颜色</label>
					<div class="col-sm-6">
						<select class="form-control" name="font_color" style="width: 170px;"> 
						 	<option value="#ffffff" <?php  if($item['font_color'] == '#ffffff') { ?>selected="selected"<?php  } ?>>白色</option>
						 	<option value="#000000" <?php  if($item['font_color'] == '#000000') { ?>selected="selected"<?php  } ?>>黑色</option>
						 </select>
					</div>
					<div class="col-sm-4">
					</div>
				</div>
			</div>


			<div class="form-group">
				<label  class="col-sm-2 control-label">首页导航样式</label>
				<div class="col-sm-7">
					<select class="form-control" name="nav_style" style="width: 100%;" id="select_nav"> 
					 	<option value="0" <?php  if($item['nav_style'] == '0') { ?>selected="selected"<?php  } ?>>一行四列小图标-默认版</option>
						<option value="3" <?php  if($item['nav_style'] == '3') { ?>selected="selected"<?php  } ?>>一行四列自定义图标</option>
					 	<option value="1" <?php  if($item['nav_style'] == '1') { ?>selected="selected"<?php  } ?>>一行两列大图</option>
					 	<option value="2" <?php  if($item['nav_style'] == '2') { ?>selected="selected"<?php  } ?>>一行三列中图</option>
					 </select>
				</div>
				<div class="col-sm-3">
					<a href="<?php  echo url('site/entry/NavExplain',array('m'=>'yyf_baidu'));?>" target="_blank"  style="margin-left: 10px; color: red; font-size: 18px;"> 导航样式说明</a>
				</div>
			</div>
			<div class="form-group" style="margin-bottom: 0px;">
				<label  class="col-sm-2 control-label">导航背景颜色</label>
				<div class="col-sm-6">
					<?php  echo tpl_form_field_color(nav_bg, $item['nav_bg']);?>
				</div>
				<div class="col-sm-4">
					<div class="alert alert-info info" role="alert" style="width: 290px">一般用默认的白色即可</div>
				</div>
			</div>
			<div class="form-group" id="nav3" >
				<label  class="col-sm-2 control-label">导航图标高度</label>
				<div class="col-sm-3">
					<input type="text" class="form-control"  name="nav_height" value="<?php  echo $item['nav_height'];?>">
				</div>
				<div class="col-sm-7">
					<div class="alert alert-info info" role="alert" style="width: 360px">只支持一行二列和一行三列图标样式的高度自定义</div>
				</div>
			</div>

			<div class="form-group">
				<label  class="col-sm-2 control-label">首页分类修饰样式</label>
				<div class="col-sm-7">
					<select class="form-control" name="title_style" style="width: 100%;" id="myselect"> 
					 	<option value="0" <?php  if($item['title_style'] == '0') { ?>selected="selected"<?php  } ?>>默认版</option>
					 	<option value="1" <?php  if($item['title_style'] == '1') { ?>selected="selected"<?php  } ?>>第二版</option>
					 	<option value="2" <?php  if($item['title_style'] == '2') { ?>selected="selected"<?php  } ?>>第三版</option>
					 	<option value="3" <?php  if($item['title_style'] == '3') { ?>selected="selected"<?php  } ?>>第四版</option>
					 </select>
				</div>
				<div class="col-sm-3">
					<a href="<?php  echo url('site/entry/TitleExplain',array('m'=>'yyf_baidu'));?>" target="_blank" style="margin-left: 10px; color: red; font-size: 18px;" > 分类修饰样式说明</a>
				</div>
			</div>
			<div class="form-group" id="myisshow">
				<label  class="col-sm-2 control-label">隐藏搜索框</label>
				<div class="col-sm-3">
					 <div class="checkbox">
        				<label><input type="checkbox"  name="hide_search" <?php  if($item['hide_search']) { ?> checked="checked"<?php  } ?> /></label>
        			 </div>	
				</div>
				<label  class="col-sm-2 control-label">隐藏幻灯片</label>
				<div class="col-sm-3">
					 <div class="checkbox">
        				<label><input type="checkbox"  name="slide_close" <?php  if($item['slide_close']) { ?> checked="checked"<?php  } ?> /></label>
        			 </div>	
				</div>
			</div>
			
			<div class="form-group" id="myisshow">
				<label  class="col-sm-2 control-label">隐藏首页导航栏</label>
				<div class="col-sm-3">
					 <div class="checkbox"> 
        				<label><input type="checkbox"   name="nav_close" <?php  if($item['nav_close']) { ?> checked="checked"<?php  } ?> /></label>
        			 </div>	
				</div>
				<label  class="col-sm-2 control-label">隐藏全站底部菜单</label>
				<div class="col-sm-3">
					 <div class="checkbox"> 
        				<label><input type="checkbox"  name="hide_tabbar" <?php  if($item['hide_tabbar']) { ?> checked="checked"<?php  } ?> /></label>
        			 </div>	
				</div>
			</div>

			
			<div class="form-group" id="myisshow">
				<label  class="col-sm-2 control-label">隐藏首页公告栏</label>
				<div class="col-sm-3">
					 <div class="checkbox">
        				<label><input type="checkbox"   name="notice_close" <?php  if($item['notice_close']) { ?> checked="checked"<?php  } ?> /></label>
        			 </div>	
				</div>
				<label  class="col-sm-2 control-label">隐藏发布时间</label>
				<div class="col-sm-1">
					 <div class="checkbox">
        				<label><input type="checkbox"  name="hide_time" <?php  if($item['hide_time']) { ?> checked="checked"<?php  } ?> /></label>
        			 </div>	
				</div>
				<div class="col-sm-4" style="padding-top: 10px;">
					如勾选会隐藏全站内容的发布时间	
				</div>
			</div>

			

			

			<div class="form-group" id="myisshow">
				<label  class="col-sm-2 control-label">隐藏文章标题</label>
				<div class="col-sm-1">
					 <div class="checkbox">
        				<label><input type="checkbox"  name="hide_title" <?php  if($item['hide_title']) { ?> checked="checked"<?php  } ?> /></label>
        			 </div>	
				</div>
				<div class="col-sm-8" style="text-align: left;padding-top: 10px;">
					如勾选会隐藏文章详情页面里的标题
				</div>
			</div>

			<div class="form-group">
				<label  class="col-sm-2 control-label">首页公告栏上喇叭图标</label>
				<div class="col-sm-8">
					 <?php  echo tpl_form_field_image('horn',$item['horn']);?>
				</div>
			</div>
			
		
	
		

			<div class="form-group">
				<div class="col-sm-offset-2 col-sm-10">
					<input type="submit" name="submit" class="btn btn-default" style="background: #428bca;color: #fff">
					
				</div>
			</div>

		
			
			
	</form>
	<?php  echo $page;?>
    </div>
</div>

<script type="text/javascript">
	$(function(){
		var value=$('#select_nav').children('option:selected').val();
			if(value=='2' || value=='1'){
				$('#nav3').show();
			}else{
				$('#nav3').hide();
			}	
		$('#select_nav').change(function(){
			var value=$(this).children('option:selected').val();
			if(value=='2' || value=='1'){
				$('#nav3').show();
			}else{
				$('#nav3').hide();
			}	
		})
		$('#show_diy_head').click(function(){
			if($('#show_diy_head').is(':checked')){
				$('#diy_head').removeClass('hide');
			}else{
				$('#diy_head').addClass('hide');
			}
		})
		

	})
</script>

<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>