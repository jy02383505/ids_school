<?php
class CommonAction extends Action {
	
	public function _initialize() {//thinkPHP的初始化函数，__initialize()不是php类中的函数，php类的构造函数只有__construct().
		
		import('@.ORG.RBAC');
	
		// 检测是否登录，没有登录就打回设置的网关
		Rbac::checkLogin();
		
		// 检测是否有权限没有权限就做相应的处理 Decision:决定; 决议; 果断; （法院的） 判决;

		if(!Rbac::AccessDecision('Madmin')) {
			$this->error('没有访问权限！');
		}
		
	
		//可管理的班级（逗号分隔，去掉首尾的0,和,0）
		$user_banji_str = session("user_banji_list");		//echo "<br>".$tmp_banjiList."<br>";
		if ($user_banji_str){
			if (substr($user_banji_str,0,2) =="0,"){
				$user_banji_str = substr($user_banji_str,2,strlen($user_banji_str)-2);	
			}
			if (substr($user_banji_str,-2) == ",0"){
				$user_banji_str = substr($user_banji_str,0,strlen($user_banji_str)-2);
			}
		}
		
		//可管理的班级（带0,和,0）
		$user_banji_str_00 = session("user_banji_list");//如果能保证数据库记录中均加了0,和,0，此处不用再验证首尾，预留此值备用
		//echo $user_banji_str_00;
		$this->assign("user_banji_str_00",$user_banji_str_00);
		
		//可管理的班级（数组格式）
		$user_banji_arr = explode(",",$tmp_banjiList);
		$this->assign("user_banji_arr",$user_banji_arr);
		//var_dump($user_banji_arr);
		

		
		// 获取数据库配置信息，并动态加载
		dbConfig();
	}
	
	/**
	 * 插件过滤器
	 * @return array|NULL
	 */
	protected function pluginsTypes() {
		$pluginsTypes = D('PluginsTypes')->where(array('iswebmanager'=>'true'))->getField('itemtype_classid,itemtype_name');
		if ($pluginsTypes) {
			foreach ($pluginsTypes as &$pt) {
				$pt = gbk2utf8($pt);
			}
			
			return $pluginsTypes;
		} else {
			return null;
		}
	}
	
	/**
	 * 顶部主导航权限判断
	 * @return array
	 */
	protected function topNavAccess() {
		
		$topNavs = array();
		$perms = array(
				'homepage'	=>	'91f7b348-3d99-debd-a703-ac7ce6e03649',//后台首页
		 		//'mall'	    =>	'a69422aa-6077-6385-4ffd-1676c591a4cc',//商场管理
				'template'	=>	'ebc22aa7-342f-1ba7-14d3-f8756494ee86',//模板管理
				'school'    =>	'64d33d93-bdd5-a9ab-1f4f-1ab521d19064',	//学校管理
		 		'programs'	=>	'a69422aa-6077-6385-4ffd-1676c591a4cc',	//节目管理
		 		'endPoints'	=>	'3674e34f-c36f-32ed-bd2f-903923699adb',	//数字班牌管理（终端管理）
		 		'syscfg'	=>	'9a364967-7038-a751-c261-c4b0ccfd3533',	//系统设置
		 		'ucenter'	=>	'd7ffe092-d6f5-3d0f-b87b-15f694e74b29',	//用户管理
		 		'resLib'	=>	'5294fac0-f693-af84-ab62-745028978a5e',	//资源管理
				'plan'		=>	'a61cfbe6-fd8d-c01b-2290-a8175ac84010'//制订计划
		 );
		//var_dump(C('ADMIN_AUTH_KEY'));//打印值：admin
		//var_dump($_SESSION['admin']);//打印值：bool(true)
		if (!$_SESSION[C('ADMIN_AUTH_KEY')]) {
			//不是超级管理员
			$access = M('Access')->where(array('role_id'=>$_SESSION['role']))->getField('node_id', true);//用M方法实例化，前缀默认为tb_，故省略不写，无模型文件
			//var_dump($access);
			foreach ($perms as $key=>$item) {
				if (in_array($item, $access))
					array_push($topNavs, $key);//array_push() 函数向第一个参数的数组尾部添加一个或多个元素（入栈），然后返回新数组的长度。
			}
		} else {
			//是管理员，返回全部顶部导航
			$topNavs = array_keys($perms);//返回包含数组中所有键名的一个新数组
		}
		
		//var_dump($_SESSION['role']);//打印值 int(1)
		//var_dump($topNavs);
		return $topNavs;
	}
	
	/**
	 * 
	*/
	protected function conAccess() {		
		//var_dump($_SESSION['role']);
		$roleAccessCon = M('AccessCon')->where(array('role_id'=>$_SESSION['role']))->select();
		$roleAccessCons = array();
		foreach ($roleAccessCon as $rac) {
			if ($rac['con_name'] == 'Programs') {
				//$roleAccessCons['Programs'][$rac['con_type']][] = $rac['con_item_classid'];
				$roleAccessCons['Programs'][] = $rac['con_type'];
			}
		
			if ($rac['con_name'] == 'EndPoints') {
				$roleAccessCons['EndPoints'][] = $rac['con_type'];
			}
		}
		//var_dump($roleAccessCons);
		return $roleAccessCons;
	}
	
	/* 旧的顶部导航 */
	protected function topNavAccessOLD() {
		//$topNavs = array(2=>'homepage', 3=>'scences', 4=>'syscfg', 5=>'endPoints', 6=>'ucenter');
		$topNavs = array(
				'91f7b348-3d99-debd-a703-ac7ce6e03649'	=>	'homepage',
				'0e6732a8-347a-c0b4-614d-60cb70ec2d7c'	=>	'scences',
				'9a364967-7038-a751-c261-c4b0ccfd3533'	=>	'syscfg',
				'3674e34f-c36f-32ed-bd2f-903923699adb'	=>	'endPoints',
				'd7ffe092-d6f5-3d0f-b87b-15f694e74b29'	=>	'ucenter',
				'5294fac0-f693-af84-ab62-745028978a5e'	=>	'resLib',
				'd8596a55-2967-e28c-b63e-c11e9bdec390'	=>	'programs'
		);
		
		if (!$_SESSION[C('ADMIN_AUTH_KEY')]) {
			$topAccess = M('Access')->where(array('role_id'=>$_SESSION['role']))->getField('node_id', true);
			foreach ($topNavs as $k=>$item) {
				if (!in_array($k, $topAccess))
					unset($topNavs[$k]);
			}
		}
		
		return $topNavs;
	}
	
	/**
	 * 检测是否可修改此班级
	*/
	public function check_banji($banji_id/*一个班级id*/){
		$user_banji_str = session("user_banji_list");
		$user_banji_arr = explode(",",$user_banji_str);
		
		if(session("username") == C('ADMIN_AUTH_KEY')){
			//超级管理员可显示所有班级
		} else {
			if (in_array($banji_id,$user_banji_arr)){
				//可管理此班级，放行
			}else{
				$this->error('对此班级无管理权限！');
			}	
		}
	
	}
	
	
}