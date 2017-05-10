<?php
header('Content-type:text/html; charset=UTF-8');
/**
 * 图书检索查询数据接口
 * GET请求方式：http://192.168.1.72/Api/books.php?sign=f18ac237-da72-2c94-32f1-ed4bb612bb73&keywords=发展&type=title&queryWay=1&rows=12&p=2
 * 参数说明：
 * sign参数必需，值为该接口的唯一标识ID；
 * rows参数可选，值为数值，用来定义分页每页记录数，默认为10，最大值为16；
 * p参数可选，值为整数，用户设置分页读物数据的页码，如果不设置该参数，默认为1（该接口返回值中包含总页数totalPages和当前页码currPage，可通过这两个数据值实现前后翻页功能）。
 * 
 */
require_once './lib/MssqlConnect.class.php';
require_once './lib/OracleConnect.class.php';
require_once './lib/functions.php';

$LibServer = 'http://192.168.1.118:8080';
$sign = 'f18ac237-da72-2c94-32f1-ed4bb612bb73';

// 接口参数验证
if ($_GET['sign'] != $sign) {
    die(json_encode(array('status'=>0, 'msg'=>'非法请求！')));
}
//unset($_GET['sign']);
//file_put_contents('opac_request_data.txt', json_encode($_GET));

// 获取接口信息
$conn = new MssqlConnect();
$sql = 'select api_unicode,api_params from tb_dataapis where api_status = 1 and api_unicode = (?)';
$params = array($sign);
$result = $conn->query($sql, $params);
if (count($result) <= 0) {
    die(json_encode(array('status'=>0, 'msg'=>'非法请求!')));
}
$conn->closeConnect();

// 处理用户输入数据
if (!isset($_GET['keywords']) || ($keywords = trim($_GET['keywords'])) == '') {
    die(json_encode(array('status'=>0, 'msg'=>'请输入检索内容!')));
}

$where = $where2 = '';

if (isset($_GET['type']) && isset($_GET['queryWay'])) {
	switch ($_GET['queryWay']*1) {
		case 1: $keywords = " like '" . $keywords . "%'"; break;
		case 2: $keywords = " = '" . $keywords . "'"; break;
		case 3: $keywords = " like '%" . $keywords . "%'"; break;
	}

	$filedName = '';
	switch (trim($_GET['type'])) {
		case 'title': $fieldName = '题名'; break;
		case 'author': $fieldName = '责任者'; break;
		case 'publisher': $fieldName = '出版者'; break;
		case 'ssh': $fieldName = '索书号'; break;
	}
	$where .= ' where ' . $fieldName . ' ' . $keywords;
	$where2 .= ' where t.' . $fieldName . ' ' . $keywords;
} else {
	die(json_encode(array('status'=>0, 'msg'=>'系统参数错误!')));
}

// 测试数据：两种格式的ISBN号
//$isbns = array('9787530211984', '9787020049295', '9787550252585', '7532740420', '7532740269', '9780142411346', '9780121000363', '9780080435756', '9780099284659');
//$isbns = array('7-301-05744-X', '7-111-11883-9', '7-111-12641-6', '7-5361-2682-4', '7-5612-1618-1', '7-03-011516-3', '7-5628-1403-1', '7-301-06187-0', '7-03-011175-3', '7-5636-1771-X');

// 检索ISBN号
$link = new OracleConnect();
$order = ' order by 处理日期 desc';
$totalsQuery = $link->query('select count(主键码) as totalrows from 馆藏书目库' . $where);
//P('select count(*) as totalrows from 馆藏书目库' . $where, false);
$totalsRows = $totalsQuery[0]['TOTALROWS']*1;
if ($totalsRows <= 0) {
	die(json_encode(array('status'=>0, 'msg'=>'没有查询到书目信息!')));
}
$perpage = (isset($_GET['rows']) && $_GET['rows']*1 > 0) ? ($_GET['rows']*1 > 16 ? 16 : $_GET['rows']*1) : 10;
$totalPages = ceil($totalsRows/$perpage);
$currPage = (isset($_GET['p']) && $_GET['p']*1 > 0) ? ($_GET['p']*1 > $totalPages ? $totalPages : $_GET['p']*1) : 1;
$startRowNo = $perpage*($currPage - 1);
$limit = " between " . ($startRowNo + 1) . " and " . ($startRowNo + $perpage);
$sql = "select t2.题名,t2.责任者,t2.出版者,t2.出版日期,t2.标准编码 from (select t.题名,t.责任者,t.出版者,t.出版日期,t.标准编码, row_number()over(" . $order . ") orderNumber from 馆藏书目库 t " . $where2 . $order . ") t2 where orderNumber " . $limit;
//P($sql, false);
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
//$ctx = stream_context_create($timeout);
//$books = file_get_contents($LibServer . '/api/books?isbns=' . implode(',', $isbns), 0, $ctx); // 需要做一个请求超时处理
//$books = file_get_contents('data/books', 0, $ctx); // 需要做一个请求超时处理
/*if (!$books) {
    die(json_encode(array('status'=>0, 'msg'=>'请求超时!')));
}*/
//file_put_contents('data/books', $books);
//$books = json_decode($books, true);
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
    if (!$bookData['pages']) {
        $bookData['pages'] = '未知';
    }
	if (!$bookData['summary'] || trim($bookData['summary'], '　') == '') {
		$bookData['summary'] = '暂无内容。';
	}
    array_push($books2, $bookData);
}
//P($books2);
// 返回结果
die(json_encode(array('status'=>1, 'datalist'=>$books2, 'totalsRows'=>$totalsRows, 'totalPages'=>$totalPages, 'currPage'=>$currPage)));
/*
unset($_GET['p']);
$url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'] . '?' . http_build_query($_GET);
P(listPage($totalPages, $currPage, $url));
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
echo $html;*/