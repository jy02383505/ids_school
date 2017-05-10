<?php

function P($var, $f = false) {
	echo '<div style="padding:10px;margin-bottom:10px;border:1px solid #ddd;background-color:#dff0d8;border-radius:10px;font-family:\'Microsoft YaHei\'"><pre style="font-family:\'Microsoft YaHei\';font-size:14px;color:#555;">';
	$f ? var_dump($var) : print_r($var);
	echo '</pre></div>';
}

function listPage($totalPages, $currPage, $url) {
	
	$prevPage = $currPage-1;
	$nextPage = $currPage+1;
	$prevALink = $nextALink = '';
	
	if ($prevPage > 0) {
		$prevALink = "<a class='btn' href='" . $url . '&p=' . $prevPage ."'>上一页</a>";
	} else {
		$prevALink = "<a class='btn-disabled' href='javascript:void(0);'>上一页</a>";
	}

	if ($nextPage <= $totalPages) {
		$nextALink = "<a class='btn' href='" . $url . '&p=' . $nextPage ."'>下一页</a>";
	} else {
		$nextALink = "<a class='btn-disabled' href='javascript:void(0);'>下一页</a>";
	}
	
	$pageStr = $prevALink . '<span>' . $currPage . '/' . $totalPages .'</span>' . $nextALink;
	return $pageStr;
}