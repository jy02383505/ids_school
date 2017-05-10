<?php
header('Content-type:text/html; charset=UTF-8');
/**
 * 图书检索查询数据接口
 * GET请求方式：http://192.168.1.72/Api/book.php?sign=227ff4f8-3551-4d19-03dc-b601080f4332&isbn=9787530211984
 * 参数说明：
 * sign参数必须，值为该接口的唯一标识ID；
 * isbn参数必须，值为书目标准编码，每个isbn号唯一对应一个书目。
 * 
 */
require_once './lib/MssqlConnect.class.php';
require_once './lib/OracleConnect.class.php';
require_once './lib/functions.php';

$LibServer = 'http://192.168.1.118:8080';
$sign = '227ff4f8-3551-4d19-03dc-b601080f4332';

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

$isbn = trim($_GET['isbn']);
$bookInfo = null;

// 测试数据
//$isbns = array('9787530211984', '9787020049295', '9787550252585', '7532740420', '7532740269', '9780142411346', '9780121000363', '9780080435756', '9780099284659');
if (empty($isbn)) {
    die(json_encode(array('status'=>0, 'msg'=>'非法请求！无效的isbn！')));
}

// 根据ISBN号从OPAC中查询数据
$link = new OracleConnect();
$sql = "select 主键码，题名,责任者,出版者,出版日期,标准编码 from 馆藏书目库 where 标准编码 = '" . $isbn . "'";
$book = $link->query($sql);
if (empty($book)) {
	$link->closeConnect();
    die(json_encode(array('status'=>0, 'msg'=>'没有查询到书目信息!')));
}

// 根据主键码查询书目馆藏信息
$sql2 = 'select dc.登录号，dc.条形码，dc.馆藏地址，dc.状态，dc.索书号，dc.分类号,dz.部门名称 from 馆藏典藏库 dc, 馆藏地址定义 dz where dc.主键码 = ' . $book[0]['主键码'] . ' and dc.馆藏地址 = dz.馆藏地址';
$collections = $link->query($sql2);
$link->closeConnect();
$bookLending = array();
if (!empty($collections)) {
	foreach ($collections as $item) {
		$tmp = array(
			'Collection'    =>  $item['部门名称'],     // 馆藏地
			'shelfNo'       =>  $item['索书号'],     // 索取号
			'barCode'       =>  $item['条形码'],     // 条形码
			'loginNo'       =>  $item['登录号'],         // 登录号   
			'type'          =>  $item['分类号'],           //借阅类型
			'status'        =>  $item['状态'],    //状态
		);
		array_push($bookLending, $tmp);
	}
}

// 根据ISBN号从书目数据库中查询书目元数据（含封面）
$timeout = array(
    'http'  =>  array(
        'timeout' => 180  //设置一个超时时间，单位为秒
    )
);
$ctx = stream_context_create($timeout);
$bookData = file_get_contents($LibServer . '/api/book?isbn=' . $isbn, 0, $ctx);
if ($bookData) {
	$bookData = json_decode($bookData, true);
	$bookData['title'] = $book[0]['题名'];
	$bookData['author'] = $book[0]['责任者'];
	$bookData['publisher'] = $book[0]['出版者'];
    $bookData['isbn'] = $isbn;
	$bookData['coverPath'] = $LibServer . '/' . $bookData['coverPath'];
	if (!$bookData['summary'] || trim($bookData['summary'], '　') == '') {
		$bookData['summary'] = '暂无内容。';
	}
	if (!$bookData['pages']) {
	    $bookData['pages'] = '未知';
	}
	$bookInfo = $bookData;
} else {
	$bookInfo['title'] = $book[0]['题名'];
	$bookInfo['author'] = $book[0]['责任者'];
	$bookInfo['publisher'] = $book[0]['出版者'];
	$bookInfo['pubdate'] = $book[0]['出版日期'];
	$bookInfo['coverPath'] = $LibServer . '/book/cover/default';
    $bookInfo['isbn'] = $isbn;
    $bookInfo['pages'] = '未知';
	$bookInfo['summary'] = '暂无内容。';
}

//$books = file_get_contents('data/books', 0, $ctx);
//$books = json_decode($books, true);
//$book = $books[$isbn];
//$book['coverPath'] = 'http://' . $_SERVER['HTTP_HOST'] . '/Api/data/' . $book['coverPath'] . '.jpg';

// 返回结果
die(json_encode(array('status'=>1, 'bookinfo'=>$bookInfo, 'booklenging'=>$bookLending)));