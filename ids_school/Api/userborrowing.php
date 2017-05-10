<?php
header('Content-type:text/html; charset=UTF-8');
/**
 * 用户借阅查询数据接口
 * GET请求方式：http://192.168.1.72/Api/userborrowing.php?sign=d819950b-82cf-9dd1-30d2-127967e2d7d6&uid=653658458
 * 参数说明：
 * sign参数必须，值为该接口的唯一标识ID；
 * uid参数必须，值为借书证号，可通过用户手动输入和刷卡获得。
 * 
 */
require_once './lib/MssqlConnect.class.php';
require_once './lib/OracleConnect.class.php';
require_once './lib/functions.php';

$LibServer = 'http://192.168.1.118:8080';
$sign = 'd819950b-82cf-9dd1-30d2-127967e2d7d6';

// 接口参数验证
if ($_GET['sign'] != $sign) {
    die(json_encode(array('status'=>0, 'msg'=>'非法请求！无效的签名！')));
}

// 获取接口信息
$conn = new MssqlConnect();
$sql = 'select api_unicode,api_params from tb_dataapis where api_status = 1 and api_unicode = (?)';
$params = array($sign);
$result = $conn->query($sql, $params);
if (count($result) <= 0) {
    die(json_encode(array('status'=>0, 'msg'=>'非法请求!')));
}
$conn->closeConnect();
/* $apiInfo = $result[0];
$apiParams = json_decode(stripslashes($apiInfo['api_params']), true);
$validParams = array();
foreach ($apiParams['params'] as $pa) {
    array_push($validParams, $pa['paramName']);
}
P($validParams, false);
P($_GET, false); */

$uid = trim($_GET['uid']);

// 测试数据
if (empty($uid)) {
    die(json_encode(array('status'=>0, 'msg'=>'非法请求！')));
}

// 根据uid查询用户信息
$link = new OracleConnect();
$sql = "select 借书证号,读者条码,姓名,性别,身份证号,年级,专业,系别,读者级别,单位,电话,手机,联系地址,EMAIL,to_char(发证日期,'yyyy-mm-dd hh24:mi:ss') as 发证日期,to_char(失效日期,'yyyy-mm-dd hh24:mi:ss') as 失效日期,to_char(上次到馆时间,'yyyy-mm-dd hh24:mi:ss') as 上次到馆时间 from 读者库 where 借书证号 = '" . $uid . "' and rownum <= 1";

$userInfo = $link->query($sql);
if (empty($userInfo)) {
	$link->closeConnect();
    die(json_encode(array('status'=>0, 'msg'=>'没有查询到用户信息!')));
}

$uinfo = array(
	'borrowNum'			=>	$userInfo[0]['借书证号'],
	'readerBarCode'		=>	$userInfo[0]['读者条码'],
	'username'			=>	$userInfo[0]['姓名'],
	'IDCardNum'			=>	$userInfo[0]['身份证号'],
	'photo'			    =>	'http://192.168.1.2/Public/images/mobile/011.gif',
	'sex'				=>	$userInfo[0]['性别'] == 1 ? '女' : '男',
	'grade'				=>	$userInfo[0]['年级'],
	'major'				=>	$userInfo[0]['专业'],
	'department'		=>	$userInfo[0]['系别'],
	'company'			=>	$userInfo[0]['单位'],
	'phone'				=>	$userInfo[0]['电话'],
	'mobile'			=>	$userInfo[0]['手机'],
	'address'			=>	$userInfo[0]['联系地址'],
	'email'				=>	$userInfo[0]['EMAIL'],
	'openingDate'		=>	$userInfo[0]['发证日期'],
	'expiryDate'		=>	$userInfo[0]['失效日期'],
	'latestAccessTime'	=>	$userInfo[0]['上次到馆时间']
);
//P($uinfo);

// 根据用户读者条码获取借阅信息
$sql2 = "select lt.条形码,lt.登录号,to_char(lt.外借时间,'yyyy-mm-dd hh24:mi:ss') as 外借时间,to_char(lt.应归还时间,'yyyy-mm-dd hh24:mi:ss') as 应归还时间,lt.读者条码,sm.题名,dz.部门名称 from 流通库 lt, 馆藏典藏库 dc, 馆藏书目库 sm,馆藏地址定义 dz where lt.读者条码 = '" . $userInfo[0]['读者条码'] . "' and lt.条形码 = dc.条形码 and lt.登录号 = dc.登录号 and dc.主键码 = sm.主键码 and dc.馆藏地址 = dz.馆藏地址 order by lt.外借时间 asc";
$borrowingData = $link->query($sql2);
$link->closeConnect();

$borrowing = array();
if (!empty($borrowingData)) {
	foreach ($borrowingData as $item) {
		$tmp = array(
			'title'		    =>  $item['题名'],     // 馆藏地
			'barCode'       =>  $item['条形码'],     // 条形码
			'loginNo'       =>  $item['登录号'],         // 登录号   
			'Collection'    =>  $item['部门名称'],     // 馆藏地
			'lendoutTime'   =>  $item['外借时间'],           //外借时间
			'givebackTime'  =>  $item['应归还时间']           //应归还时间
		);
		array_push($borrowing, $tmp);
	}
}
//P($borrowing);

// 返回结果
die(json_encode(array('status'=>1, 'userInfo'=>$uinfo, 'borrowing'=>$borrowing)));