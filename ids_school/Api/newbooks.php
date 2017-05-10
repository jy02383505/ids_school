<?php
header('Content-type:text/html; charset=UTF-8');
/**
 * 新书通报数据查询接口
 * GET请求方式：http://192.168.1.72/Api/newbooks.php?sign=06176c99-c250-163a-7fb5-73edcb6eb564&rows=10
 * 参数说明：
 * sign参数必需，值为该接口的唯一标识ID；
 * rows参数非必需，值为数值，用来定义获取数据的记录数，默认为10，最大值为16
 * 
 */
require_once './lib/MssqlConnect.class.php';
require_once './lib/OracleConnect.class.php';
require_once './lib/functions.php';

$LibServer = 'http://192.168.1.118:8080';
$sign = '06176c99-c250-163a-7fb5-73edcb6eb564';

// 接口参数验证
if ($_GET['sign'] != $sign) {
    die(json_encode(array('status'=>0, 'msg'=>'非法请求！')));
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

// 检索ISBN号
$link = new OracleConnect();
$rownum = (isset($_GET['rows']) && $_GET['rows']*1 > 0) ? ($_GET['rows']*1 > 16 ? 16 : $_GET['rows']*1) : 10;
$sql = "select 题名,责任者,出版者,出版日期,标准编码,处理日期 from (select 题名,责任者,出版者,出版日期,标准编码,处理日期 from 馆藏书目库 order by 处理日期 desc) where rownum <= " . $rownum;
$books = $link->query($sql);
$link->closeConnect();
if (empty($books)) {
    die(json_encode(array('status'=>0, 'msg'=>'没有查询到书目信息!')));
}

// 获取书目元数据
$timeout = array(
    'http'  =>  array(
        'timeout' => 180  //设置一个超时时间，单位为秒
     )
);
$books2 = array();
foreach ($books as $key=>&$book) {
	
	$loopIsbn = trim($book['标准编码'], ' 　 ');
	if (empty($loopIsbn)) continue;
	
	// 根据ISBN从书目数据库获取书目元数据
	$ctx = stream_context_create($timeout);
	$bookData = file_get_contents($LibServer . '/api/book?isbn=' . $loopIsbn, 0, $ctx);
	if (!$bookData) continue;
	$bookData = json_decode($bookData, true);
	$bookData['title'] = $book['题名'];
	$bookData['author'] = $book['责任者'];
	$bookData['publisher'] = $book['出版者'];
    $bookData['isbn'] = $loopIsbn;
    $bookData['coverPath'] = $LibServer . '/' . $bookData['coverPath'];
    //$bookData['coverPath'] = 'http://' . $_SERVER['HTTP_HOST'] . '/Api/data/' . $bookData['coverPath'] . '.jpg';
	if (!$bookData['summary'] || trim($bookData['summary'], '　') == '') {
		$bookData['summary'] = '暂无内容。';
	}
    array_push($books2, $bookData);
}

// 返回结果
die(json_encode(array('status'=>1, 'datalist'=>$books2)));

$html = '<style>table{border-collapse:collapse;}th,td{border:1px solid #ccc;padding:10px;}</style><table>';
$html .= '<tr>';
$html .= '<th>序号</th>';
$html .= '<th>封面</th>';
$html .= '<th>书名</th>';
$html .= '<th>作者</th>';
$html .= '<th>出版社</th>';
$html .= '<th>出版日期</th>';
$html .= '<th>价格</th>';
$html .= '<th>页数</th>';
$html .= '<th>简介</th>';
$html .= '</tr>';
$i = 1;
foreach ($books2 as $key=>$bk) {
    $html .= '<tr>';
    $html .= '<td>' . $i . '</td>';
    $html .= '<td><img height="120" src="' . $bk['coverPath'] . '"></td>';
    $html .= '<td>' . $bk['title'] . '</td>';
    $html .= '<td>' . $bk['author'] . '</td>';
    $html .= '<td>' . $bk['publisher'] . '</td>';
    $html .= '<td>' . $bk['pubdate'] . '</td>';
    $html .= '<td>' . $bk['price'] . '</td>';
    $html .= '<td>' . $bk['pages'] . '</td>';
    $html .= '<td>' . $bk['summary'] . '</td>';
    $html .= '</tr>';
    $i++;
}
$html .= '</table>';
echo $html;