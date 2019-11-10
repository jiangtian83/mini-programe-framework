<?php defined('IN_IA') or exit('Access Denied');?><?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/header', TEMPLATE_INCLUDEPATH)) : (include template('common/header', TEMPLATE_INCLUDEPATH));?>
<style type="text/css">
  .divimg{
    width: 280px;
  }
  .divimg img{
    width: 270px;
     box-shadow: 5px 10px 10px 5px #888888;
   }
  .form-horizontal .form-group {
    margin-left: 0px;
    margin-right: 0px;
  }
  #templetBtn{
    width: 300px;margin: 0 auto;
  }
  .col-xs-1, .col-sm-1, .col-md-1, .col-lg-1, .col-xs-2, .col-sm-2, .col-md-2, .col-lg-2, .col-xs-3, .col-sm-3, .col-md-3, .col-lg-3, .col-xs-4, .col-sm-4, .col-md-4, .col-lg-4, .col-xs-5, .col-sm-5, .col-md-5, .col-lg-5, .col-xs-6, .col-sm-6, .col-md-6, .col-lg-6, .col-xs-7, .col-sm-7, .col-md-7, .col-lg-7, .col-xs-8, .col-sm-8, .col-md-8, .col-lg-8, .col-xs-9, .col-sm-9, .col-md-9, .col-lg-9, .col-xs-10, .col-sm-10, .col-md-10, .col-lg-10, .col-xs-11, .col-sm-11, .col-md-11, .col-lg-11, .col-xs-12, .col-sm-12, .col-md-12, .col-lg-12{
    padding-right: 0px;
  }
</style>
<div class="panel panel-info">
  <div class="panel-heading">
    <h3 class="panel-title"> 快速创建模板 </h3>
  </div>
  <div class="panel-body">
    <div class="alert alert-danger" role="alert" style="font-size: 20px;"> 注意：选择模板点击一键设置后，当前这个小程序的所有数据都将会被覆盖。请谨慎操作！ </div>
    <div class="alert alert-info" role="alert" style="font-size: 16px;">
      第一次使用，没有掌握百度审核规律的话，建议使用第一套快速审核模板！
    <br>
    </div>

    <form class="form-horizontal" role="form" method="post" action="" name="submit" id="templet">  
    <div class="form-group" style="margin-left:0px;">
      <div class="col-sm-4">
        <div class="col-sm-12 divimg" style="text-align: center;"> <img src="<?php  echo $imgurl;?>t17.jpg"> </div>
        <div class="col-sm-12" style="text-align: center;">
          <input type="radio" name="tid" value="17"  style="width: 30px; height: 30px" >
        </div>
      </div>
      <div class="col-sm-4">
        <div class="col-sm-12 divimg" style="text-align: center;"> <img src="<?php  echo $imgurl;?>t1.jpg"> </div>
        <div class="col-sm-12" style="text-align: center;">
          <input type="radio" name="tid" value="1"  style="width: 30px; height: 30px" >
        </div>
      </div>
      <div class="col-sm-4">
        <div class="col-sm-12 divimg" style="text-align: center;"> <img src="<?php  echo $imgurl;?>t2.jpg"> </div>
        <div class="col-sm-12" style="text-align: center;">
          <input type="radio" name="tid" value="2" style="width: 30px; height: 30px">
        </div>
      </div>

    </div>
    <div class="form-group">
      <div class="col-sm-4">
        <div class="col-sm-12 divimg" style="text-align: center;"> <img src="<?php  echo $imgurl;?>t3.jpg"> </div>
        <div class="col-sm-12" style="text-align: center;">
          <input type="radio" name="tid" value="3"  style="width: 30px; height: 30px" >
        </div>
      </div>
      <div class="col-sm-4">
        <div class="col-sm-12 divimg" style="text-align: center;"> <img src="<?php  echo $imgurl;?>t5.jpg"> </div>
        <div class="col-sm-12" style="text-align: center;">
          <input type="radio" name="tid" value="5"  style="width: 30px; height: 30px" >
        </div>
      </div>
      <div class="col-sm-4">
        <div class="col-sm-12 divimg" style="text-align: center;"> <img src="<?php  echo $imgurl;?>t6.jpg"> </div>
        <div class="col-sm-12" style="text-align: center;">
          <input type="radio" name="tid" value="6"  style="width: 30px; height: 30px" >
        </div>
      </div>


    </div>



    <div class="form-group">

      <div class="col-sm-4">
        <div class="col-sm-12 divimg" style="text-align: center;"> <img src="<?php  echo $imgurl;?>t7.jpg"> </div>
        <div class="col-sm-12" style="text-align: center;">
          <input type="radio" name="tid" value="7"  style="width: 30px; height: 30px" >
        </div>
      </div>
      <div class="col-sm-4">
        <div class="col-sm-12 divimg" style="text-align: center;"> <img src="<?php  echo $imgurl;?>t8.jpg"> </div>
        <div class="col-sm-12" style="text-align: center;">
          <input type="radio" name="tid" value="8"  style="width: 30px; height: 30px" >
        </div>
      </div>
      <div class="col-sm-4">
        <div class="col-sm-12 divimg" style="text-align: center;"> <img src="<?php  echo $imgurl;?>t9.jpg"> </div>
        <div class="col-sm-12" style="text-align: center;">
          <input type="radio" name="tid" value="9"  style="width: 30px; height: 30px" >
        </div>
      </div>

    </div>

     <div class="form-group">
       <div class="col-sm-4">
         <div class="col-sm-12 divimg" style="text-align: center;"> <img src="<?php  echo $imgurl;?>t10.jpg"> </div>
         <div class="col-sm-12" style="text-align: center;">
           <input type="radio" name="tid" value="10"  style="width: 30px; height: 30px" >
         </div>
        </div>
       <div class="col-sm-4">
         <div class="col-sm-12 divimg" style="text-align: center;"> <img src="<?php  echo $imgurl;?>t16.jpg"> </div>
         <div class="col-sm-12" style="text-align: center;">
           <input type="radio" name="tid" value="16"  style="width: 30px; height: 30px" >
         </div>
       </div>


       <div class="col-sm-4">
         <div class="col-sm-12 divimg" style="text-align: center;"> <img src="<?php  echo $imgurl;?>t11.jpg"> </div>
         <div class="col-sm-12" style="text-align: center;">
           <input type="radio" name="tid" value="11"  style="width: 30px; height: 30px" >
         </div>
       </div>
    </div>

    <div class="form-group">

      <div class="col-sm-4">
        <div class="col-sm-12 divimg" style="text-align: center;"> <img src="<?php  echo $imgurl;?>t12.jpg"> </div>
        <div class="col-sm-12" style="text-align: center;">
          <input type="radio" name="tid" value="12"  style="width: 30px; height: 30px" >
        </div>
      </div>
      <div class="col-sm-4">
        <div class="col-sm-12 divimg" style="text-align: center;"> <img src="<?php  echo $imgurl;?>t13.jpg"> </div>
        <div class="col-sm-12" style="text-align: center;">
          <input type="radio" name="tid" value="13"  style="width: 30px; height: 30px" >
        </div>
      </div>
      <div class="col-sm-4">
        <div class="col-sm-12 divimg" style="text-align: center;"> <img src="<?php  echo $imgurl;?>t14.jpg"> </div>
        <div class="col-sm-12" style="text-align: center;">
          <input type="radio" name="tid" value="14"  style="width: 30px; height: 30px" >
        </div>
      </div>
    </div>


    <div class="form-group">
      <div class="col-sm-4">
        <div class="col-sm-12 divimg" style="text-align: center;"> <img src="<?php  echo $imgurl;?>t15.jpg"> </div>
        <div class="col-sm-12" style="text-align: center;">
          <input type="radio" name="tid" value="15"  style="width: 30px; height: 30px" >
        </div>
      </div>

      <div class="col-sm-4">
        <div class="col-sm-12 divimg" style="text-align: center;"> <img src="<?php  echo $imgurl;?>t18.jpg"> </div>
        <div class="col-sm-12" style="text-align: center;">
          <input type="radio" name="tid" value="18"  style="width: 30px; height: 30px" >
        </div>
      </div>

      <div class="col-sm-4">
        <div class="col-sm-12 divimg" style="text-align: center;"> <img src="<?php  echo $imgurl;?>t19.jpg"> </div>
        <div class="col-sm-12" style="text-align: center;">
          <input type="radio" name="tid" value="19"  style="width: 30px; height: 30px" >
        </div>
      </div>


    </div>


   
    <div class="form-group">
      <div class="col-sm-12" style="margin: 100px auto;">
        <button type="btn" id="templetBtn" class="btn btn-danger btn-lg btn-block">数据会重置，确定要创建么</button>
      </div>
    </div>
  </form>
  </div>
</div>
<script type="text/javascript">
  $(function(){
    $('#templetBtn').click(function(){
         var msg = "注意点击按钮后当前这个小程序的所有数据都将会被清空"; 
         if (confirm(msg)==true){ 
          $('#templet').submit();
         }else{ 
          return false; 
         } 
    })
  })
</script>
<?php (!empty($this) && $this instanceof WeModuleSite || 1) ? (include $this->template('common/footer', TEMPLATE_INCLUDEPATH)) : (include template('common/footer', TEMPLATE_INCLUDEPATH));?>