<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<ol class="breadcrumb we7-breadcrumb">
	<a href="<?php  echo url('account/manage', array('account_type' => ACCOUNT_TYPE_APP_NORMAL))?>"><i class="wi wi-back-circle"></i> </a>
	<li><a href="<?php  echo url('account/manage', array('account_type' => ACCOUNT_TYPE_APP_NORMAL))?>">小程序管理</a></li>
	<li>小程序设置</li>
</ol>
<div class="media media-wechat-setting">
	<a class="media-left">
		<span class="icon"><i class="wi wi-wxapp"></i></span>
		<img src="<?php  echo $account['logo'];?>" class="wechat-img">
	</a>
	<div class="media-body media-middle ">
		<h4 class="media-heading color-dark"><?php  echo $account['name'];?></h4>
		<span class="color-gray">小程序</span>
	</div>
	<?php  if($state == ACCOUNT_MANAGE_NAME_FOUNDER || $state == ACCOUNT_MANAGE_NAME_OWNER) { ?>
	<div class="media-right media-middle">
		<a href="<?php  echo url('account/manage/delete', array('uniacid' => $account['uniacid'], 'acid' => $account['acid'], 'account_type' => ACCOUNT_TYPE_APP_NORMAL))?>" class="btn btn-primary" onclick="return confirm('确认放入回收站吗？')">停  用</a>
	</div>
	<?php  } ?>
</div>
<div class="clearfix"></div>
<div class="btn-group we7-btn-group wechat-edit-group">
	
		<?php  if($state == ACCOUNT_MANAGE_NAME_FOUNDER || $state == ACCOUNT_MANAGE_NAME_OWNER || $state == ACCOUNT_MANAGE_NAME_VICE_FOUNDER) { ?>
		<a href="<?php  echo url('account/post/base', array('uniacid' => $account['uniacid'], 'acid' => $account['acid'], 'account_type' => ACCOUNT_TYPE_APP_NORMAL))?>" class="btn btn-default <?php  if($do == 'base') { ?> active<?php  } ?>">基础信息</a>
		<?php  } ?>
	

	

	<a href="<?php  echo url('account/post-user/edit', array('uniacid' => $account['uniacid'], 'acid' => $account['acid'], 'account_type' => ACCOUNT_TYPE_APP_NORMAL))?>" class="btn btn-default <?php  if($action == 'post-user' && $do == 'edit') { ?> active<?php  } ?>">使用者管理</a>
	<a href="<?php  echo url('wxapp/manage/display', array('uniacid' => $account['uniacid'], 'acid' => $account['acid'], 'account_type' => ACCOUNT_TYPE_APP_NORMAL))?>" class="btn btn-default <?php  if($action == 'manage' && $do == 'display') { ?> active<?php  } ?>">版本管理</a>
	<a href="<?php  echo url('account/post/modules_tpl', array('uniacid' => $account['uniacid'], 'acid' => $account['acid'], 'account_type' => ACCOUNT_TYPE_APP_NORMAL))?>" class="btn btn-default <?php  if($action == 'post' && $do == 'modules_tpl') { ?> active<?php  } ?>">可用应用模板/模块</a>
</div>

<div id="js-account-manage-wxapp" ng-controller="AccountManageWxappCtrl" ng-cloak>
	<!--版本管理-->
	<div class="text-right we7-margin-bottom">
		<a href="<?php  echo url('wxapp/post/design_method', array('uniacid' => $account['uniacid']))?>" class="btn btn-primary" target="_blank">添加新版本</a>
	</div>
	<table class="table we7-table vertical-middle wxapp-version-table">
		<tr>
			<th class="text-left">版本号</th>
			<th class="text-left">应用</th>
			<th class="text-center">之前应用版本</th>
			<th class="text-center">最新应用版本</th>
			<th class="text-right">操作</th>
		</tr>
		<tr ng-if="version_exist" ng-repeat="item in wxapp_version_lists">
			<td class="text-left">
				<div class="version" ng-bind="item.version"></div>
				<div class="color-gray" ng-bind="item.description"></div>
			</td>
			<td class="clearfix">
				<div class="item col-sm-6" ng-if="item.last_modules">
					<img ng-src="{{item.last_modules.logo}}" class="icon"/>
					<div class="name" ng-bind="item.last_modules.title"></div>
				</div>
			</td>
			<td class="text-center">
				<div ng-bind="item.last_modules.version"></div>
			</td>
			<td class="text-center">
				<div ng-bind="item.module.version"></div>
				<div class="color-red" ng-if="item.last_modules.version != item.module.version">
					<i class="wi wi-warning-sign"></i>
					<span style="font-size: 10px">需上传微信审核</span>
				</div>
			</td>
			<td class="text-right">
				<div class="link-group">
					<a href="./index.php?c=account&a=display&do=switch&uniacid={{item.uniacid}}&version_id={{item.id}}">进入</a>
					<a href="javascript:;" ng-click="showEditVersionInfoModal(item)" ng-show="item.type!=2">修改</a>
					<a ng-show="item.type == 2" href="./index.php?c=wxapp&a=post&do=post&design_method={{item.design_method}}&uniacid={{item.uniacid}}&version_id={{item.id}}&create_type={{item.type}}" >修改</a>
					<a href="javascript:;" class="del" ng-click="delWxappVersion(item.id)">删除</a>
				</div>
			</td>
		</tr>
		<tr ng-if="!version_exist">
			<td colspan="3" class="text-center">暂无数据</td>
		</tr>
	</table>
	<div class="modal fade" id="modal_edit_versioninfo"  tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog we7-modal-dialog" style="width:800px">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
					<h4 class="modal-title">修改信息</h4>
				</div>
				<div class="modal-body">
					<table class="table we7-table table-hover table-form">
						<col width="120px">
						<col />
						<tr>
							<td class="table-label">模块版本</td>
							<td><input type="text" class="form-control" ng-model="activeVersion.version" disabled></td>
						</tr>
						<tr>
							<td class="table-label">模块描述</td>
							<td><input type="text" class="form-control" ng-model="activeVersion.description" disabled></td>
						</tr>
						<tr>
							<td class="table-label">应用模块</td>
							<td class="wxapp-module-list">
								<div class="col-sm-6" ng-if="activeVersion.modules" ng-repeat="module in activeVersion.modules">
									<div class="item">
										<img ng-src="{{module.logo}}" class="icon"/>
										<div class="name" ng-bind="module.title"></div>
										<div ng-bind="module.version"></div>
										<div class="cover-dark"><a href="javascript:;" class="cover-delect" ng-click="delModule(module);"><i class="fa fa-minus-circle"></i>删除</a></div>
									</div>
								</div>
								<div class="col-sm-6">
									<div class="add" ng-click="showEditModuleModal()" ng-style="{'display': 'none'}">+</div>
								</div>
							</td>
						</tr>
					</table>
				</div>
				<div class="modal-footer" ng-if="wxapp_modules">
					<button type="button" class="btn btn-primary" ng-click="editVersionInfo()">确定</button>
					<button type="button" class="btn btn-default" data-dismiss="modal" ng-click="cancelVersionInfo()">取消</button>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="modal_edit_module"  tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
		<div class="modal-dialog we7-modal-dialog" style="width:800px">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">Close</span></button>
					<h4 class="modal-title">选择小程序</h4>
				</div>
				<div class="modal-body">
					<div class="input-group" style="width: 400px">
						<input class="form-control" name="title" ng-model="search.moduleName" type="text" placeholder="名称">
						<span class="input-group-btn">
							<button ng-click="searchModuleName()" class="btn btn-default">
								<i class="fa fa-search" style="font-size: 16px"></i>
							</button>
						</span>
					</div>
					<div class="we7-margin-top">
						<div class="col-sm-2 text-center we7-margin-bottom select-module-wxapp" ng-repeat="module in modules" ng-if="modules">
							<a href="javascript:;" ng-click="selectedWxModule(module, $event)">
								<img ng-src="{{module.logo}}" style="width:50px;height:50px;">
								<p class="text-over">{{module.title}}</p>
							</a>
							<span id="module-{{module.name}}" class="selected hide" style="position:absolute;width:82%;height:100%;left:10px;top:0;opacity:0.8;cursor:pointer;background:#e7e8eb; vertical-align:middle;font-size:30px"><i class="wi wi-right-sign color-green" style="margin-top:25px"></i></span>
						</div>
						<div class="text-center" ng-if="!modules">
							<span>暂无可用模块</span>
						</div>
					</div>
				</div>
				<div class="modal-footer" ng-if="modules">
					<button type="button" class="btn btn-primary" ng-click="changeWxModules()">确定</button>
					<button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
				</div>
			</div>
		</div>
	</div>
	<!--end 版本管理-->
</div>
<script>
require(['underscore'], function() {
	angular.module('wxApp').value('config', {
		'account': <?php echo !empty($account) ? json_encode($account) : 'null'?>,
		'wxapp_version_lists': <?php echo !empty($wxapp_version_lists) ? json_encode($wxapp_version_lists) : 'null'?>,
		'wxapp_modules' : <?php echo !empty($wxapp_modules) ? json_encode($wxapp_modules) : 'null'?>,
		'version_exist': <?php echo !empty($version_exist) ? json_encode($version_exist) : 'null'?>,
		'links': {
			'edit_version': "<?php  echo url('wxapp/manage/edit_version', array('acid' => $account['acid'], 'uniacid' => $account['uniacid'], 'account_type' => ACCOUNT_TYPE_APP_NORMAL))?>",
			'del_version': "<?php  echo url('wxapp/manage/del_version', array('acid' => $account['acid'], 'uniacid' => $account['uniacid'], 'account_type' => ACCOUNT_TYPE_APP_NORMAL))?>",
		},
	});
	angular.bootstrap($('#js-account-manage-wxapp'), ['wxApp']);
});
</script>
<?php (!empty($this) && $this instanceof WeModuleSite || 0) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>