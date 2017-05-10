<?php
class LoginAction extends Action {
	
	public function _initialize() {
		dbConfig();
	}
	
	// 登录页面
	public function index(){
		if ($_SESSION[C('USER_AUTH_KEY')]) {
			$this->success('已登陆，无需重复登录！', U('Index/index'));
		} else {
			$this->display('Public:login');
		}
	}
	
	// 登录信息验证
	public function authGateway() {
		if (IS_POST) {
			$account = I('post.account');//用户名
			$password = I('post.password');//密码
			$vcode = I('post.vcode');//验证码
			
			if (C('s_login_vcode')) {
				// 验证验证码
				if ($_SESSION['verify'] != md5($vcode)) {
					// $this->error('验证码输入错误！');	
					echo json_encode(array('stat'=>-1, 'msg'=>'验证码错误！'));
					exit();
				}
			}
			
			if (empty($account) || empty($password)) {
				// $this->error('用户名、密码不合法！');	
				echo json_encode(array('stat'=>-2, 'msg'=>'用户名、密码不合法！'));
				exit();
			}
			
			// 数据库验证
			$userModel = M('users');
			$uInfo = $userModel->where(array('account'=>utf82gbk($account)))->find();

			if ($uInfo) {
				
				if (!$uInfo['status']) {
					echo json_encode(array('stat'=>-3, 'msg'=>'用户名已被禁用！请联系管理员查看！'));
					exit();
				}
				
				if ($uInfo['password'] == md5($password)) {
					
					session(C('USER_AUTH_KEY'), $uInfo['id']);//用户的自动编号id
					session('username', gbk2utf8($uInfo['account']));//用户名
					session('role', $uInfo['role_id']);//用户组id，对应数据表：tb_role
					session('last_login_time', date('Y-m-d H:i:s', $uInfo['last_login_time']));
					session('user_type',$uInfo['type']);//老师还是学生
					session('user_banji_list',$uInfo['banjiList']);//可管理班级字段，逗号分隔的班级编号
					session('refer_id',$uInfo['referId']);//绑定的学生或教师id，与user_type配合
					
					//可管理班级，中文班级名
					if ($uInfo['banjiList']){
						$model = D("SchoolBanji");
						$login_map['id'] = array("IN",$uInfo['banjiList']);
						$login_banji_datas = $model->where($login_map)->field("id, name")->select();

						foreach($login_banji_datas as $k=>$v){
							$user_banji_list_cn .= $v['name'].",";
							$temp[] = $v[id];
						}
						session('user_banji_list', '0,'.join(',', $temp).',0');
					}

					// 获取现有所有班级的id列表 added by lym
					$currentAllBanjiIds = D('SchoolBanji')->getField('id', true);
					if(session("username") == C('ADMIN_AUTH_KEY')){
						session('manageAllBanji', '1');
					}else{
						foreach($currentAllBanjiIds as $banjiId){
							if(in_array($banjiId, explode(',', session('user_banji_list')))){
								$banjiNum++;
							}
						}
						if($banjiNum == count($currentAllBanjiIds)){
							session('manageAllBanji', '1');
						}else{
							session('manageAllBanji', '0');
						}
					}

					session("user_banji_list_cn",$user_banji_list_cn);//一年一班,一年二班等中文班级名，逗号分隔
					
					// session('group_id', $uInfo['group_id']);
					
					// 如果用户是超级管理员，则可以进行一切操作
					if ($uInfo['account'] == C('ADMIN_AUTH_KEY')) {
						session(C('ADMIN_AUTH_KEY'), true);//实际是：session('admin',true)
					}
					
					//可管理的终端，从班级得到教室，再从教室得到终端
					//超级管理员，为全部终端
					$endpointModel = D("Endpoint");
					if(session("username") == C('ADMIN_AUTH_KEY')){
						//超级管理员
						$data_endpoint = $endpointModel->field("tid")->select();//数据库会有类型为空的，忽略掉//->where("touchEndPointSort <> '' ")
						foreach ($data_endpoint as $k=>$v){
							$edp[] = $v['tid'];
						}
						$my_endpoints = implode(",",$edp);
						
					} else {
						//找教室
						$SchoolBanjiModel = D('SchoolBanji');
						$SchoolRoomModel = D('SchoolRooms');
						
						$map = array();
						$map['id'] = array("IN",session("user_banji_list"));
						$data_room = $SchoolBanjiModel->where($map)->select();
						foreach ($data_room as $k=>$v){
							$rm[] = $v['roomId'];	
						}
						$rm = array_unique($rm);
						$my_room = implode(",",$rm);
						
						if (!empty($my_room)){
							$map = array();
							$map['id'] = array("IN",$my_room);
							$data_room_endpoint = $SchoolRoomModel->where($map)->field("endpointId")->select();//注意是逗号分隔的终端Id
						}
						
						$myend = array();
						foreach($data_room_endpoint as $k=>$v){
							$tmp = array();
							if (!empty($v['endpointId'])){
								$tmp = explode(",",$v['endpointId']);
								$myend = array_merge($myend,$tmp);						
							}
			
						}
			
						$my_endpoints = implode(",",array_unique($myend));
					}	
					//$this->assign("my_endpoints",$my_endpoints);
					session("my_endpoints",$my_endpoints);
										
					// 登录成功，取出用户权限信息
					import('@.ORG.RBAC');
					Rbac::saveAccessList();
					
					// 更新最后登录时间
					$userModel->where(array('id'=>$uInfo['id']))->save(array('last_login_time'=>time()));
					
					// $this->redirect(U('Index/index'));
					//日志 START
					$logModel = D("Log");
					$logModel->writeLog(session(C('USER_AUTH_KEY')),session('username'),'用户登陆','','userLogin');
					//日志　END
					
					echo json_encode(array('stat'=>1, 'msg'=>'登录成功！'));
					exit();
				} else {
					// $this->error('用户名或密码错误！');
					echo json_encode(array('stat'=>0, 'msg'=>'用户名或密码错误！！'));
					exit();
				}			
			} else {
				// $this->error('用户名或密码错误！');
				echo json_encode(array('stat'=>0, 'msg'=>'用户名或密码错误！！'));
				exit();
			}
			
		} else {
			redirect(PHP_FILE.C('USER_AUTH_GATEWAY'));
		}
	}
	
	// 验证码
	public function verifyCode() {
		import('@.ORG.Image');
		Image::buildImageVerify2();
	}
	
	// 退出登录
	public function eout() {
		//日志 START
		$logModel = D("Log");
		$logModel->writeLog(session(C('USER_AUTH_KEY')),session('username'),'用户退出登陆','','userLogout');
		//日志　END
		
		$_SESSION = array(); // 清除SESSION值
		
		// 判断客户端的cookie文件是否存在，存在的话将其设置为过期
		if (isset($_COOKIE[session_name()])) {
			setcookie(session_name(), '', time()-1, '/');
		}
		
		// 清除服务器的SESSION文件
		session_destroy();
		
		$this->redirect('Login/index');
	}
}