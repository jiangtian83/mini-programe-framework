<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>

<div class="panel panel-info">
    <div class="panel-heading">
        <h3 class="panel-title">
          配置说明：
        </h3>
    </div>
    <div class="panel-body">
        <div style="font-size: 18px;">只能使用qq邮箱，登录邮箱，点击上方设置，然后在上方导航栏里选择[账户]，拖到下面找到下图位置，开启<span style="color:red;">第一项</span>POP3/SMTP服务，授权码就是第二张图片显示的数字<br/></div><br/>
        <div style="font-size: 18px; color: red">
            <?php  echo $error;?>
        </div>
        <img src="<?php  echo $img1;?>" style="width: 600px">
        <img src="<?php  echo $img2;?>">

        
        
    </div>
</div>


<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>