<?php
//header('Content-type:text/html; charset=UTF-8');
//header("Cache-Control:no-cache,must-revalidate");//禁止页面缓存
/**
 * 天气预报显示
 */
 
define('APP_PATH', '');
require_once './lib/MssqlConnect.class.php';
require_once '../apps/conf/Madmin/config.php';

$server = $config['DB_PORT'] ? $config['DB_HOST'].','.$config['DB_PORT'] : $config['DB_HOST'];

$sign = 'a1217fa8-3817-8318-a4c1-7d5ebf81a881';

$imageFolder = "";

//白天
$imageFolder = "/Public/images/weather/images2/";
// $imageFolder = "/Public/images/weather/icon/day/";

// 获取信息
$conn = new MssqlConnect($server, $config['DB_USER'], $config['DB_PWD'], $config['DB_NAME']);
$sql = "select * from tb_reslib_weather where day_title IN ('今天','明天','后天')";
$params = array($sign);
$result = $conn->query($sql, $params);
if (count($result) <= 0) {
    die(json_encode(array('status'=>0, 'msg'=>'非法请求!')));
}

$sql = "select * from tb_reslib_weather where day_title='今天'";
$jt = $conn->query($sql, $params);
$data_jt = $jt[0];
$data_jt['img'] = $data_jt['dayPictureUrl'];

$sql = "select * from tb_reslib_weather where day_title='明天'";
$mt = $conn->query($sql, $params);
$data_mt = $mt[0];

$sql = "select * from tb_reslib_weather where day_title='后天'";
$ht = $conn->query($sql, $params);
$data_ht = $ht[0];

$conn->closeConnect();

// 秀秀让给9001一套新的样式，要的很急，临时先给一个switch来切换不同学校的样式吧
$projectName = @$_GET['projectName'] ? 'yueliangwanxiaoxue' : 'binheluxiaoxue';
switch($projectName){
    case 'binheluxiaoxue':
        $bgColor = '#FFFFFF';
        $color = '#191970';
        break;
    case 'yueliangwanxiaoxue':
        $bgColor = '#7ccc44';
        $color = '#FFFFFF';
        break;
    default:
        $bgColor = '#FFFFFF';
        $color = '#191970';
        break;
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=gbk" />
<style>
body{
	padding:25px 0;margin:0px;overflow:hidden;font-family:Microsoft Yahei,"Helvetica Neue",Helvetica,Hiragino Sans GB,WenQuanYi Micro Hei,sans-serif;color: <?php echo $color; ?>;font-size:12px;
	background: <?php echo $bgColor ?>;
}
img{vertical-align:middle;}
.day{
	float:left;width:50px;margin-left:0px;text-align:right;color:<?php echo $color; ?>;font-size:20px;overflow:hidden;text-align:center;
}

.abc{
	float:left;width:49%;height:103px;overflow:hidden;margin-right:0px;color:<?php echo $color; ?>;margin-right:1px;
}

#jl-content{padding:0px;margin-left:auto;margin-right:auto;margin-top:0;margin-bottom:0;width:100%;height:103px;overflow:hidden;background:<?php echo $bgColor ?>;}
.hang_1{
	clear:both;width:100%;height:100%;overflow:hidden;text-align:center;
}
	.t1{font-size:19pt;}
	.t2{font-size:28pt;font-family: "Microsoft YaHei";font-weight:bold;}
.hang_1 img{margin-right:5px;}
.temp{
	clear:both;width:100%;text-align:center;font-size:28px;color:<?php echo $color; ?>;letter-spacing: 1px;overflow:hidden;
}
</style>
</head>
<title>天气预报</title>
<body>

<div id="jl-content">
	<div style="width:auto;height:96px;overflow:hidden;line-height:48px;">
		<div class="abc">
       		<div class="hang_1">        
				<span class="t1">今天<img src="<?php echo $imageFolder.$data_jt['dayPictureUrl'];//图标?>" width="32"><?php echo $data_jt['weather'];//天气?></span>
                <br>
                <span class="t2"><?php echo $data_jt['temperature'];?></span>
                <!-- <span class="t2"><?php echo $data_jt['temperature'];?>℃</span> -->
            </div>
        </div>
        <div class="abc">
       		<div class="hang_1">
				<span class="t1">明天<img src="<?php echo $imageFolder.$data_mt['dayPictureUrl'];?>" width="32"><?php echo $data_mt['weather'];?></span>
                <br>
				<span class="t2"><?php echo $data_mt['temperature'];?></span>
                <!-- <span class="t2"><?php echo $data_mt['temperature'];?>℃</span> -->
            </div>
        </div>
        <div class="abc" style="display:none;">
        	<div class="hang_1">
                <div class="day">后天</div>
                <div class="daypic">
                    <img src="<?php echo $imageFolder.$data_ht['dayPictureUrl'];?>" width="32"><?php echo $data_ht['weather'];?>
             </div>
             </div>
            <div class="temp"><?php echo $data_ht['temperature'];?>℃</div>
            
        </div>		
		<div style="width:100%;height:0;overflow:hidden;clear:both;"></div>
	</div>
</div>
</body>
</html>