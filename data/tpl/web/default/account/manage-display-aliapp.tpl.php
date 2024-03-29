<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<div class="we7-page-title"><?php  echo ACCOUNT_TYPE_NAME?>管理</div>
<ul class="we7-page-tab">
	<li class="active"><a href="<?php  echo url ('account/manage', array('account_type' => ACCOUNT_TYPE))?>"><?php  echo ACCOUNT_TYPE_NAME?>列表</a></li>
	<?php  if($_W['role'] == ACCOUNT_MANAGE_NAME_OWNER || $_W['role'] == ACCOUNT_MANAGE_NAME_FOUNDER || $_W['role'] == ACCOUNT_MANAGE_NAME_VICE_FOUNDER) { ?>
	<li><a href="<?php  echo url ('account/recycle', array('account_type' => ACCOUNT_TYPE))?>"><?php  echo ACCOUNT_TYPE_NAME?>回收站</a></li>
	<?php  } ?>
</ul>
<div class="clearfix we7-margin-bottom">
	<form action="" class="form-inline  pull-left" method="get">
		<input type="hidden" name="c" value="account">
		<input type="hidden" name="a" value="manage">
		<input type="hidden" name="account_type" value="<?php  echo ACCOUNT_TYPE?>">
		<div class="input-group form-group" style="width: 400px;">
			<input type="text" name="keyword" value="<?php  echo $_GPC['keyword'];?>" class="form-control" placeholder="搜索关键字"/>
			<span class="input-group-btn"><button class="btn btn-default"><i class="fa fa-search"></i></button></span>
		</div>
	</form>
	
		<?php  if(!empty($account_info['wxapp_limit']) && (!empty($account_info['founder_wxapp_limit']) && $_W['user']['owner_uid'] || empty($_W['user']['owner_uid'])) || $_W['isfounder'] && !user_is_vice_founder()) { ?>
		<div class="pull-right">
			<a href="<?php  echo url('miniapp/post', array('type' => ACCOUNT_TYPE_ALIAPP_NORMAL))?>" class="btn btn-primary we7-padding-horizontal">添加支付宝小程序</a>
		</div>
		<?php  } ?>
	
	
</div>
<table class="table we7-table table-hover vertical-middle table-manage" id="js-system-account-display" ng-controller="SystemAccountDisplay" ng-cloak>
	<col width="120px" />
	<col />
	<col width="208px" />
	<col width="245px" />
	<tr>
		<th colspan="5" class="text-left filter">
			<div class="dropdown dropdown-toggle we7-dropdown">
				<a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					时间排序
					<span class="caret"></span>
				</a>
				<ul class="dropdown-menu" aria-labelledby="dLabel">
					<li><a href="<?php  echo filter_url('order:asc');?>" class="active">创建时间正序</a></li>
					<li><a href="<?php  echo filter_url('order:desc');?>">创建时间倒序</a></li>
				</ul>
			</div>
			<div class="dropdown dropdown-toggle we7-dropdown">
				<a data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
					到期筛选
					<span class="caret"></span>
				</a>
				<ul class="dropdown-menu" aria-labelledby="dLabel">
					<li><a href="<?php  echo filter_url('type:all');?>" class="active">全部<?php  echo ACCOUNT_TYPE_NAME?></a></li>
					<li><a href="<?php  echo filter_url('type:expire');?>" class="active"><?php  echo ACCOUNT_TYPE_NAME?>已到期</a></li>
				</ul>
			</div>
		</th>
	</tr>
	<tr>
		<th colspan="2" class="text-left">帐号</th>
		<th>有效期</th>
		<th class="text-right">操作</th>
	</tr>
	<tr class="color-gray" ng-repeat="list in lists">
		<td class="text-left td-link">
			<?php  if($role_type) { ?>
			<a ng-href="{{links.post}}&acid={{list.acid}}&uniacid={{list.uniacid}}"></a>
			<?php  } else { ?>
			<a href="javascript:;">
			<?php  } ?>
				<img ng-src="{{list.logo}}" class="img-responsive icon">
			</a>
		</td>
		<td class="text-left">
			<p class="color-dark" ng-bind="list.name"></p>
		</td>
		<td>
			<p ng-bind="list.end"></p>
		</td>
		<td class="vertical-middle vertical-middle table-manage-td">
			<div class="link-group">
				<a ng-href="{{links.switch}}uniacid={{list.uniacid}}&version_id={{list.current_version.id}}&type={{list.type}}">进入支付宝小程序</a>
				<?php  if($role_type) { ?>
				<a ng-href="{{links.post}}&acid={{list.acid}}&uniacid={{list.uniacid}}" ng-show="list.role == 'manager' || list.role == 'owner' || list.role == 'founder' || list.role == 'vice_founder'">管理设置</a>
				<?php  } ?>
			</div>
			<?php  if($role_type) { ?>
			<div class="manage-option text-right">
				<a href="{{links.post}}&acid={{list.acid}}&uniacid={{list.uniacid}}" ng-show="list.role == 'owner' || list.role == 'founder' || list.role == 'vice_founder'">基础信息</a>
				<a href="{{links.postUser}}&do=edit&uniacid={{list.uniacid}}&acid={{list.acid}}">使用者管理</a>
				<a href="{{links.postVersion}}&do=display&uniacid={{list.uniacid}}&acid={{list.acid}}">版本管理</a>
				<a href="{{links.post}}&do=modules_tpl&uniacid={{list.uniacid}}&acid={{list.acid}}&account_type={{list.type}}">可用应用模板/模块</a>
				<?php  if($_W['role'] != ACCOUNT_MANAGE_NAME_MANAGER) { ?>
				<a ng-href="{{links.del}}&acid={{list.acid}}&uniacid={{list.uniacid}}" ng-show="list.role == 'owner' || list.role == 'founder' || list.role == 'vice_founder'" onclick="if(!confirm('确认放入回收站吗？')) return false;" class="del">停用</a>
				<?php  } ?>
			</div>
			<?php  } ?>
		</td>
	</tr>
</table>
<div class="text-right">
	<?php  echo $pager;?>
</div>
<script>
	$(function(){
		$('[data-toggle="tooltip"]').tooltip();
	});
	switch_url = "<?php  echo url('account/display/switch')?>";
	angular.module('accountApp').value('config', {
		lists: <?php echo !empty($list) ? json_encode($list) : 'null'?>,
		links: {
			switch: switch_url,
			post: "<?php  echo url('account/post', array('account_type' => ACCOUNT_TYPE))?>",
			postUser: "<?php  echo url('account/post-user', array('account_type' => ACCOUNT_TYPE))?>",
			postVersion: "<?php  echo url('miniapp/manage', array('account_type' => ACCOUNT_TYPE))?>",
			del: "<?php  echo url('account/manage/delete', array('account_type' => ACCOUNT_TYPE))?>",
		}
	});
	angular.bootstrap($('#js-system-account-display'), ['accountApp']);
</script>
<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>