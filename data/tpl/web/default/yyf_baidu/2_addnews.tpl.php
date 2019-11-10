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
			添加内容 （添加时请注意：时间有时会跟着鼠标滚轮变化）
		</h3>
	</div>
	<div class="panel-body">
		<form class="form-horizontal" role="form" method="post" action="" name="submit">
			<div class="alert alert-info info" role="alert">
				图片尺寸: [新闻缩略图->228*150],[一行两列缩略图->346*210],[一行一列缩略图->533*300]单位为像素
			</div>
			<div class="form-group">
				<label for="firstname" class="col-sm-2 control-label">内容标题</label>
				<div class="col-sm-10">
					<input type="text" class="form-control"  name="title">
					<input type="hidden" name="token" value="<?php  echo $_W['token'];?>">
				</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-2 control-label">分类</label>
				<div class="col-sm-10">
					<select class="form-control" name="cid" style="width: 50%;" id="cate">
						<?php  if(is_array($list)) { foreach($list as $index => $item) { ?>
						<option value="<?php  echo $item['id'];?>" test="<?php  echo $item['type'];?>" readnum="<?php  echo $item['readnum'];?>" votenum="<?php  echo $item['votenum'];?>"><?php  echo $item['name'];?></option>
						<?php  if(is_array($item['son'])) { foreach($item['son'] as $index1 => $item1) { ?>
						<option value="<?php  echo $item1['id'];?>" test="<?php  echo $item['type'];?>" readnum="<?php  echo $item1['readnum'];?>" votenum="<?php  echo $item1['votenum'];?>">&nbsp;&nbsp;&nbsp;&nbsp;___<?php  echo $item1['name'];?></option>
						<?php  } } ?>
						<?php  } } ?>
					</select>
				</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-2 control-label">缩略图</label>
				<div class="col-sm-7">
					<?php  echo tpl_form_field_image('thumb');?>
				</div>
			</div>
			<div class="form-group">
				<label  class="col-sm-2 control-label">时间</label>
				<div class="col-sm-10" style="width: 50%;">
					<?php  echo tpl_form_field_date('addtime');?>
				</div>
			</div>
			<div class="form-group">
				<label for="firstname" class="col-sm-2 control-label">排序(越大越靠前)</label>
				<div class="col-sm-2">
					<input type="text" class="form-control"  name="sortrank" style="width: 100px;" value="0">
				</div>
				<label for="firstname" class="col-sm-1 control-label">阅读数</label>
				<div class="col-sm-2">
					<input type="text" class="form-control"  name="read_num" style="width: 100px;" value="<?php  echo $readnum;?>" id="read_num">
				</div>
				<label for="firstname" class="col-sm-1 control-label">点赞数</label>
				<div class="col-sm-2">
					<input type="text" class="form-control"  name="vote_num" style="width: 100px;" value="<?php  echo $votenum;?>" id="vote_num">
				</div>
			</div>

			<div class="form-group" id="appid">
				<label for="firstname" class="col-sm-2 control-label">跳转小程序的appid</label>
				<div class="col-sm-3">
					<input type="text" class="form-control"  name="appid" >
				</div>
				<div class="col-sm-7">
					两个小程序必须同时绑定在同一个公众号下才可以跳转。具体绑定步骤：
					登录公众号在左侧找到小程序管理栏目->添加->关联小程序<br/>
					一个公众号可关联10个同主体的小程序，3个不同主体的小程序。<br/>
					一个小程序可关联3个公众号。<br/>
					公众号一个月可新增关联小程序13次，小程序一个月可新增关联5次。<br/>
				</div>
			</div>

			<div class="form-group" id="pageaddress">
				<label for="firstname" class="col-sm-2 control-label">跳转小程序页面地址</label>
				<div class="col-sm-5">
					<input type="text" class="form-control"  name="pageaddress" >
				</div>
				<div class="col-sm-5">
					每个小程序的地址都可能不同，本款小程序的主页地址是：yyf_company/pages/index/index
				</div>
			</div>
			<div class="alert alert-info " role="alert" style="padding-top: 4px;padding-bottom:4px;">
				使用方法：将腾讯视频的播放地址，复制到下边框里即可(不支持VIP或者版权电影电视剧)<br/>个别视频播放出现黑屏或播放时间不对都属正常情况，还是推荐传到第三方服务器上<a target="blank" href="http://blog.csdn.net/shaisuanqin4706/article/details/79259144" style="color: red">【音频或视频使用说明】</a>
			</div>
			<div class="form-group">
				<label  class="col-sm-2 control-label">多媒体设置</label>
				<div class="col-sm-8">
					<label class="radio-inline">
						<input type="radio"  value="0" name="media_kind" checked="checked" > 视频
					</label>
					<label class="radio-inline">
						<input type="radio"  value="1" name="media_kind" > 音频
					</label>

				</div>
			</div>
			<div class="form-group" id="video_div">
				<label  class="col-sm-2 control-label">视频播放地址</label>
				<div class="col-sm-6" >
					<input type="text" class="form-control"  name="videosrc">
				</div>
			</div>
			<div id="audio_div" class="hide">
				<div class="form-group" id="audio_adress">
					<label  class="col-sm-2 control-label">音频播放地址</label>
					<div class="col-sm-6" >
						<input type="text" class="form-control"  name="audio_src">
					</div>
				</div>
				<div class="form-group" id="audio_adress">
					<label  class="col-sm-2 control-label">音频名称和作者</label>
					<div class="col-sm-2" >
						<input type="text" class="form-control"  name="audio_name">
					</div>
					<div class="col-sm-2" >
						<input type="text" class="form-control"  name="audio_author">
					</div>
					<div class="col-sm-2">名称和作者可以为空</div>
				</div>
				<div class="form-group">
					<label  class="col-sm-2 control-label">音频背景图片</label>
					<div class="col-sm-5">
						<?php  echo tpl_form_field_image('audio_img');?>
					</div>
					<div class="col-sm-5">
						<img src="<?php echo MODULE_URL;?>images/audio.jpg" />
					</div>
				</div>
			</div>

			<div class="alert alert-info " role="alert" style="padding-top: 4px;padding-bottom:4px;">
				请注意，小程序端生成海报图片，分享朋友圈功能。需要在小程序官方后台左侧分类找到设置->【开发设置】->服务器域名【downloadFile合法域名】添加跟request合法域名同样的值
			</div>
			<div class="form-group">
				<label  class="col-sm-2 control-label">文章分享设置</label>
				<div class="col-sm-8">
					<label class="radio-inline">
						<input type="radio"  value="0" name="diyshare" checked="checked" > 系统自动生成
					</label>
					<label class="radio-inline">
						<input type="radio"  value="1" name="diyshare" > 单独设置分享时的标题和图片
					</label>
				</div>
			</div>


			<div class="form-group hide" id="diysharediv">
				<label  class="col-sm-2 control-label">标题和图片</label>
				<div class="col-sm-4" >
					<input type="text" class="form-control"  name="sharetitle">
				</div>
				<div class="col-sm-3" >
					<?php  echo tpl_form_field_image('shareimg');?>
				</div>
				<div class="col-sm-3" >
					分享图片尺寸（600*500）px
				</div>
			</div>

			<div  style="text-indent: 150px; font-size: 16px;">小程序暂时不支持复杂的格式文本，如果发现前台显示空白，请把内容复制到文本文档里中转一下。</div>
			<div class="form-group" id="cotent">
				<label  class="col-sm-2 control-label">内容</label>
				<div class="col-sm-10">
					<?php  echo tpl_ueditor('content',$result['content']);?>
				</div>
			</div>

			<div class="form-group">

				<div class="col-sm-offset-2 col-sm-10">
					<input type="submit" name="submit" class="btn btn-default" value="添加">

				</div>
			</div>



		</form>
		<?php  echo $page;?>
	</div>
</div>

<script type="text/javascript">
    $(function(){
        var catetype=$('#cate').find("option:selected").attr("test");
        domhide(catetype);
        $('#cate').change(function(){
            var catetype=$(this).find("option:selected").attr("test");
            domhide(catetype);
        })
        function domhide(catetype){
            if(catetype=='6'){
                $('#video_adress').hide();
                $('#content').hide();
                $('#appid').show();
                $('#pageaddress').show();
            }else{
                $('#video_adress').show();
                $('#content').show();
                $('#appid').hide();
                $('#pageaddress').hide();
            }
        }

        //阅读数处理
        //var readnum=$('#cate').find("option:selected").attr("readnum");

        $('#cate').change(function(){
            var readnum=$(this).find("option:selected").attr("readnum");
            $('#read_num').val(readnum)
            var votenum=$(this).find("option:selected").attr("votenum");
            $('#vote_num').val(votenum)
        })

        //多媒体切换
        $('input[name="media_kind"]').click(function(){
            var copy_kind=$('input[type="radio"][name="media_kind"]:checked').val();
            if(copy_kind==0){
                $('#video_div').show();
                $('#audio_div').addClass('hide');
            }
            if(copy_kind==1){
                $('#video_div').hide();
                $('#audio_div').removeClass('hide');
            }

        })
        //分享切换
        $('input[name="diyshare"]').click(function(){
            var copy_kind=$('input[type="radio"][name="diyshare"]:checked').val();
            if(copy_kind==0){
                $('#diysharediv').addClass('hide');
            }
            if(copy_kind==1){
                $('#diysharediv').removeClass('hide');
            }

        })
    })
</script>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>