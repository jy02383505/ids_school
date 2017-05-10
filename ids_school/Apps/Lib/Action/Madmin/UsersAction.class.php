<?php
/**
 * 用户管理和访问控制（后台管理权限）
 * @author SKeam TJ
 *
 */
class UsersAction extends CommonAction {
	
	/**
	 * 管理员列表
	 */
	public function index() {
		
		import('ORG.Util.Page');
		
		// 构建查询条件
		$where = array();
		$account = I('get.account', '', 'strip_tags');
		if (!empty($account)) {
			$where['account'] = array('like', '%' . utf82gbk($account) . '%');
		}
	
		$user_type = trim(I("request.user_type"));
		$banjiId = I("request.banjiId",0,"int");
		switch ($user_type){
			case 'teacher':
				$where['type'] = 'teacher';
				//可管理的班级
				if ($banjiId){
					$banjiIdStr = ','.$banjiId.',';
					$where['banjiList'] = array('like','%'.$banjiIdStr.'%');
				}
				break;
				/*
			case 'student':
				$where['type'] = 'student';
				//某班的学生
				if ($banjiId){
					//某一班级全部学生ID
					$model_stu = D("SchoolStudents");
					$students = $model_stu->getBanjiStudentIdStr($banjiId);
					//var_dump($students);
					$where['referId'] = array("IN",$students);
				}
				break;
				*/
			case "other":
				$where['type'] = '';
				break;
			default:
				//可管理的班级
				if ($banjiId){
					$banjiIdStr = ','.$banjiId.',';
					$where['banjiList'] = array('like','%'.$banjiIdStr.'%');
				}
		}
		$this->assign("user_type",$user_type);
		$this->assign("banjiId",$banjiId);
		
		// 分页获取数据
		$userModel = M('Users');
		$totals = $userModel->where($where)->count();
		$Page = new Page($totals, 10);
		$show = $Page->show();
		
		$users = $userModel->where($where)->limit($Page->firstRow, $Page->listRows)->select();
		if ($users) {
			$roles = $this->getRoleList();
			foreach ($users as &$u) {
				
				
				$u['account'] = gbk2utf8($u['account']);
				$u['remark'] = gbk2utf8($u['remark']);
				foreach ($roles as $r) {
					if ($r['id'] == $u['role_id']) {
						$u['role'] = $r['name'];
					}
				}
				
				//处理学生或老师类型
				switch ($u['type']){
					case "teacher":
						$tmp_datas = array();
						$tmp_datas = D("SchoolTeachers")->where("id=".$u['referId'])->field("name")->find();
						$u['trueName'] = $tmp_datas['name'];
						$u['type_cn'] = "教师";
						
						$datas['teacherId']=$u['referId'];
						
						break;
				/*	case "student":
						$tmp_datas = array();
						$tmp_datas = D("SchoolStudents")->where("id=".$u['referId'])->field("name")->find();
						$u['trueName'] = $tmp_datas['name'];
						$u['type_cn'] = "学生";
						break;
						*/
					default:
						;
				}
				
				//可管理班级，中文班级名
				if ($u['banjiList']){
					$model_bj = D("SchoolBanji");
					$bj_map['id'] = array("IN",$u['banjiList']);
					$datas_bj_cn = $model_bj->where($bj_map)->field("name")->select();

					$user_banji_list_cn = "";//初始化中文班级名列表
					$arrTmp = array();
					foreach($datas_bj_cn as $k=>$v){
						$arrTmp[] = $v['name'];
					}
					$user_banji_list_cn = implode("，",$arrTmp);
					$u['user_banji_list_cn'] = $user_banji_list_cn;
				}
				
			}
		}
		
		//班级列表 START
		$banjiModel = D('SchoolBanji');
		$map = array();
		$banjis = $banjiModel->getList($map);
		$this->assign('banjis', $banjis);
		//班级列表 END	
		
		
		$this->assign('page', $show);
		$this->assign('users', $users);
		$this->display();
	}

	/**
	 * 添加管理员
	 */
	public function addUser() {
		if (IS_POST) {
			$this->saveUser();
		} else {
            //科目列表 START
			$subjectsModel = D('SchoolSubjects');
			$map = array();
			$subjects = $subjectsModel->getList($map);
			$this->assign('subjects', $subjects);
			//列表 END	
						
            //教师列表 START
			$teachersModel = D('SchoolTeachers');
			$map = array();
			$teachers = $teachersModel->getTeachersAndSubjects($map);
			$this->assign('teachers', $teachers);
			//教师列表 END	
			
            /*
			//学生列表 START
			$stuModel = D('SchoolStudents');
			$map = array();
			$students = $stuModel->getList($map);
			$this->assign('students', $students);
			//学生列表 END
			*/
			
            //班级列表 START
			$banjiModel = D('SchoolBanji');
			$map = array();
			$banjis = $banjiModel->getList($map);
			$this->assign('banjis', $banjis);
			//班级列表 END	
			
			
			$seltype = "radio";
			$this->assign('teacherModalSelType', $seltype);//教师选择弹出框中只允许单选
			
			/*
			$seltype = "radio";
			$this->assign('studentModalSelType', $seltype);//学生弹出框中只允许单选
			*/
			
			$this->assign("user_type","");
			
			$this->assign('roles', $this->getRoleList(1));
			
			$user = array();
			$user['status'] = 1;
			$this->assign("user",$user);
			
			
			
			$this->display('editUser');
		}
	}
	
	/**
	 * 编辑管理员信息
	 */
	public function editUser() {
		if (IS_POST) {
			$this->saveUser();	
		} else {
			$id = I('get.uid', 0, 'int');
			
            //科目列表 START
			$subjectsModel = D('SchoolSubjects');
			$map = array();
			$subjects = $subjectsModel->getList($map);
			$this->assign('subjects', $subjects);
			//列表 END	
						
            //教师列表 START
			$teachersModel = D('SchoolTeachers');
			$map = array();
			$teachers = $teachersModel->getTeachersAndSubjects($map);
			$this->assign('teachers', $teachers);
			//教师列表 END	
			
			/*
            //学生列表 START
			$stuModel = D('SchoolStudents');
			$map = array();
			$students = $stuModel->getList($map);
			$this->assign('students', $students);
			//学生列表 END
			*/
			
            //班级列表 START
			$banjiModel = D('SchoolBanji');
			$map = array();
			$banjis = $banjiModel->getList($map);
			$this->assign('banjis', $banjis);
			//班级列表 END	

			
			//获取到全部已分配的IC卡，用逗号分隔
			/*
			$card_list_used = array();
			$map = array();
			$map['id'] = array("NEQ",$id);//排除当前用户的cardId
			$map['cardId'] = array("GT",0);
			$card_list_used = M('Users')->where($map)->field("cardId")->select();
			
			$card_used_str = "";//逗号分隔的字符串
			$card_used_arr = array();//数组格式
			if ($card_list_used !== false){
				foreach ($card_list_used as $k=>$v){
					$card_used_arr[] = $v['cardId'];
				}
			}
			$card_used_str = implode(',',$card_used_arr);//已使用的IC卡，逗号分隔（也包括自己当前使用的）
			
			
            //可选用的IC卡列表 START
			$cardModel = D('SchoolCards');
			$map = array();
			$map['id'] = array("NOT IN",$card_used_str);
			$card_list = $cardModel->where($map)->order("id DESC")->select();
			$this->assign('card_list', $card_list);
			//可选用的IC卡列表 END
			*/
			
			$seltype = "radio";
			$this->assign('teacherModalSelType', $seltype);//教师选择弹出框中只允许单选
			
			/*
			$seltype = "radio";
			$this->assign('studentModalSelType', $seltype);//学生弹出框中只允许单选
			*/
			
			
			if ($id) {
				// 获取组信息
				$user = M('Users')->find($id);
				
				if ($user) {
					$user['account'] = gbk2utf8($user['account']);
					$user['remark'] = gbk2utf8($user['remark']);
					
					$this->assign("user_type",$user['type']);
					//处理学生或老师类型
					switch ($user['type']){
						case "teacher":
							$tmp_datas = D("SchoolTeachers")->where("id=".$user['referId'])->field("name")->find();
							$user['trueName'] = $tmp_datas['name'];
							$user['type_cn'] = "教师";
							
						//	$datas['teacherId']=$user['referId'];
							$datas['banjiId'] = $user['banjiList'];
							
							$strTeachersId = $user['referId'];
							$this->assign("strTeachersId",$strTeachersId);
							//var_dump($strTeachersId);
							//类型是教师，且指定了教师ID
							if ($user['referId']){
								//所带科目，从TB_Sch_Teacher_Subject表获取，可能是多条
								$teacherSubjectModel = D('SchoolTeacherSubject');
								$datas_teacher_subject = $teacherSubjectModel->where("teacherId=".$user['referId'])->field("subjectId")->select();

								$subjectArrTmp = array();
								foreach($datas_teacher_subject as $k=>$v){
									$subjectArrTmp[] = $v['subjectId'];
								}
								$datas['subjectId'] = $subjectIdArr;//该老师所带科目
								
								$datas['teacherId'] = $user['referId'];
							}
							
							
							break;
							/*
						case "student":
							$tmp_datas = D("SchoolStudents")->where("id=".$user['referId'])->field("name")->find();
							$user['trueName'] = $tmp_datas['name'];
							$user['type_cn'] = "学生";
							
							$datas['userId'] = $user['referId'];
							$datas['banjiId'] = $user['banjiList'];
							break;
							*/
						default:
							$datas['userId'] = $user['referId'];
							$datas['banjiId'] = $user['banjiList'];							
							;
					}
					
					//已提交的已选择班级，预先复选中
					$arr_checked = array();
					$arr_checked = explode(',',$user['banjiList']);
					$banjiModel = D('SchoolBanji');
					if ( !empty( $arr_checked )){
						foreach ($arr_checked as $v){ 
						 $one = $banjiModel->where('id='.$v)->field('id,name')->find(); 
						 if ($one){//忽略首尾的0
							$banji_list[]=array('id'=>$one['id'],'name'=>$one['name']);
						 }
						} 
					}
					$this->assign('banji_list', $banji_list);
					
					$this->assign('datas', $datas);
					
					$this->assign('user', $user);					
				} else {
					$this->error('非法请求！');
				}
			} else {
				$this->error('非法请求！');
			}
			$this->assign('roles', $this->getRoleList(1));
			
			$this->display();
		}
	}

	private function saveUser() {
		$id = I('post.id', 0, 'int');
		$roleId = I('post.role_id', 0, 'int');
		$account = I('post.account', '', 'strip_tags');
		$password = I('post.password', '', 'strip_tags');
		$cfgpass = I('post.cfgpass', '', 'strip_tags');
		$status = I('post.status', 0, 'int');
		$mobile = I('post.mobile', '', 'strip_tags');
		$email = I('post.email', '', 'strip_tags');
		$remark = I('post.remark', '', 'strip_tags');
		$user_type = I('post.user_type', '', 'strip_tags');
		$referId = trim(I('post.referId', '', 'strip_tags'));
		$cardId = trim(I('post.cardId'));
		

		
		import('ORG.Util.String');
		$mobile = String::msubstr($mobile,0,11,'utf-8',false);
		
		$teacherIdArr=I('post.teacherIdStr');
		$teacherId = intval(implode(',',$teacherIdArr));//弹窗中设为单选，此处收到的只是一个数值，
		
//		$studentIdArr=I('post.studentIdStr');
//		$studentId = intval(implode(',',$studentIdArr));//弹窗中设为单选，此处收到的只是一个
		
		// 实例化数据模型
		$userModel = M('Users');
		
		if ($id){
			//修改时
			$ucount = $userModel->where("account='".$account."'")->count();
			if ($ucount > 1){
				$this->error("用户名已存在");
			}
			
			$udatas = $userModel->where("account='".$account."'")->find();
			if ($udatas){
				if ($udatas['id'] <> $id){
					$this->error("用户名已存在");
				}
			}
			
		}else{
			//新增时，检测用户名重复
			$udatas = $userModel->where("account='".$account."'")->find();
			if ($udatas){
				//var_dump($udatas);
				$this->error("用户名已存在");
			}
		}
		
	
		/*
		if ($cardId){
			//检测卡重复
			$map = array();
		//	$map['id'] = array("NEQ",$id);
			$map['cardId'] = $cardId;
			$datas = $userModel->where("cardId='".$cardId."' and id <>".$id)->field("cardId")->find();
			if ($datas){
				//var_dump($datas);exit;
				$this->error("重复");	
			}
			
			//检测卡是否存在
			$card_model = D('SchoolCards');
			$map = array();
			$map['code'] = $cardId;
			$result = $card_model->where($map)->find();
			
			if (!$result){
				$this->error("卡不存在");	
			}else{
				if ($result['isLocked']){
					$this->error("卡已被锁定");	
				}
			}
		}
		*/

		//检测学生重复/老师重复
		if ($user_type && $referId){
			$map_tp = array();
			if ($id){
				$map_tp['id'] = array("NEQ",$id);
			}
			$map_tp['type'] = $user_type;
			switch ($user_type){
				case "teacher":
					$referId = $teacherId;
					break;
				case "student";
					$referId = $studentId;
					break;
				default:
					;
			}
			$map_tp['referId'] = $referId;
			$datas_tp = $userModel->where($map_tp)->count();
			if ($datas_tp){
				$this->error("此用户与其它用户重复关联同一个学生或教师");
			}
		}

		
		//班级选择，如果是学生，则应该从学生资料中查到其班级
		$banjiIdArr=$_POST['banjiIdStr'];
		$banjiId = implode(',',$banjiIdArr);
		if (!empty($banjiId)){
			$banjiId = "0,".$banjiId.",0";
		}
		
			
		switch ($user_type){
			case "student":
				$referId = $studentId;
				break;
			case "teacher":
				$referId = $teacherId;
				break;
			default:
				$referId = 0;
		}
		
		
		if (empty($roleId) || empty($account)) {
			$this->error('请检查必填项！');
		}
		
		if (!$id) {
			if (empty($password) || empty($cfgpass)) {
				$this->error('请检查必填项！');
			}
		}
		
		if ($password != $cfgpass) {
			$this->error('两次密码输入不一致！');
		}
		

		
		// 组建数据
		$data['role_id'] = $roleId;
		$data['account'] = utf82gbk($account);
		if (!empty($password))
			$data['password'] = md5($password);
		$data['status'] = $status;
		$data['mobile'] = $mobile;
		$data['email'] = $email;
		$data['remark'] = utf82gbk($remark);
		$data['type'] = $user_type;
		$data['referId'] = $referId;//学生id或老师id
		$data['banjiList'] = $banjiId;//可管理的班级，逗号分隔
		$data['cardId'] = $cardId;
		
		$func = '';
		if ($id) {
			$func = 'save';
			$data['id'] = $id;
		} else {
			$func = 'add';
		}
		
		$result = $userModel->$func($data);
		
		if ($result !== false) {
			
			// 添加用户&&组关系表
			$roleUserModel = M('RoleUser');
			$uid = 0;
			if ($id) {
				$roleUserModel->where(array('user_id'=>$id))->delete();
				$uid = $id;				
			} else {
				$uid = $userModel->getLastInsID();
			}
			
			$result2 = $roleUserModel->add(array('role_id'=>$roleId, 'user_id'=>$uid));
			if ($result2 !== false) {
				$this->success('操作成功！', U('Users/index'));
			} else {
				if (!$id) {
					$userModel->delete($uid);
				}
				$this->error('操作失败！[原因]：' . $roleUserModel->getError());
			}
			
		} else {
			$this->error('操作失败！[原因]：' . $userModel->getError());
		}
	}
	
	private function getRoleList($status = 0) {
		$where = array();
		
		if ($status) {
			$where['status'] = $status;
		}
		$roles = M('Role')->where($where)->field('id,name')->select();
		if ($roles) {
			foreach ($roles as &$r) {
				$r['name'] = gbk2utf8($r['name']);
			}
		}
		return $roles;
	}
	
	/**
	 * 删除管理员
	 */
	public function delUser() {
		$id = I('get.uid', 0, 'int');
		
		if ($id) {
			$userModel = M('Users');
			$result = $userModel->delete($id);
			if ($result !== false) {
				$this->success('操作成功！');
			} else {
				$this->error('操作失败！[原因]：' . $userModel->getError());
			}
		} else {
			$this->error('非法请求！');
		}
	}
	
	/**
	 * 管理组列表
	 */
	public function rolesList() {

		import('ORG.Util.Page');
	
		// 构建查询条件
		$where = array();
		$name = I('get.name', '', 'strip_tags');
		if (!empty($name)) {
			$where['name'] = array('like', '%' . utf82gbk($name) . '%');
		}
	
		// 分页获取数据
		$roleModel = M('Role');
		$totals = $roleModel->where($where)->count();
		$Page = new Page($totals, 10);
		$show = $Page->show();
	
		$roles = $roleModel->where($where)->limit($Page->firstRow, $Page->listRows)->select();
		if ($roles) {
			foreach ($roles as &$r) {
				$r['name'] = gbk2utf8($r['name']);
				$r['remark'] = gbk2utf8($r['remark']);
				if (mb_strlen($r['remark'], 'UTF-8') > 16) {
					$r['remark_short'] = mb_substr($r['remark'], 0, 16, 'UTF-8') . '....';
				} else {
					$r['remark_short'] = $r['remark'];
				}
			}
		}
	
		$this->assign('page', $show);
		$this->assign('roles', $roles);
		$this->display();
	}
	
	/**
	 * 创建管理组
	 */
	public function addRole() {
		if (IS_POST) {
			$this->saveRole();
		} else {
			$this->display('editRole');
		}
	}
	
	/**
	 * 编辑管理组
	 */
	public function editRole() {
		if (IS_POST) {
			$this->saveRole();
		} else {
			$id = I('get.rid', 0, 'int');
			if ($id) {
				// 获取组信息
				$role = M('Role')->find($id);
				if ($role) {
					$role['name'] = gbk2utf8($role['name']);
					$role['remark'] = gbk2utf8($role['remark']);
					$this->assign('role', $role);
				} else {
					$this->error('非法请求！');
				}
			} else {
				$this->error('非法请求！');
			}
	
			$this->display();
		}
	}
	
	private function saveRole() {
		$id = I('post.id', 0, 'int');
		$name = I('post.name', '', 'strip_tags');
		$status = I('post.status', 0, 'int');
		$remark = I('post.remark', '', 'strip_tags');
	
		if (empty($name)) {
			$this->error('组名称不能为空！');
		}
	
		// 实例化数据模型
		$roleModel = M('Role');
	
		// 组建数据
		$data['name'] = utf82gbk($name);
		$data['status'] = $status;
		$data['remark'] = utf82gbk($remark);
		$data['pid'] = 0;
	
		$func = '';
		if ($id) {
			$func = 'save';
			$data['id'] = $id;
		} else {
			$func = 'add';
		}
	
		$result = $roleModel->$func($data);
	
		if ($result !== false) {
			$this->success('操作成功！', U('Users/rolesList'));
		} else {
			$this->error('操作失败！[原因]：' . $roleModel->getError());
		}
	}
	
	/**
	 * 管理组权限分配
	 */
	public function assignPerms() {
		
		if (IS_POST) {
			$role_id = I('post.role_id', 0, 'int');
			if (!$role_id) {
				$this->error('非法操作！');
			}
			
			if (count($_POST['perms']) <= 0) {
				$this->error('非法操作！');
			}
			
			$nodeModel = M('Node');
			$indexNodeClassID = '91f7b348-3d99-debd-a703-ac7ce6e03649';//后台首页
			$programNodeClassID = 'a69422aa-6077-6385-4ffd-1676c591a4cc';//节目管理
			$endpointsNodeClassID = '3674e34f-c36f-32ed-bd2f-903923699adb';//数字班牌管理（终端管理）
			$endgroupNodeClassID = '20eec425-3114-ca6c-4816-1c5e112a8952';//终端组管理
			
			$accessData = $accessConData = array();
			array_push($accessData, array('role_id'=>$role_id, 'node_id'=>'b6760535-03b6-e6a4-7d9e-07ad6b32057f'));// 自动后台pid=0
			$indexNodeID = $nodeModel->where(array('node_id'=>$indexNodeClassID))->getField('id');
			$indexSubClassIDs = $nodeModel->where(array('pid'=>$indexNodeID))->getField('node_id', true);
			array_push($indexSubClassIDs, $indexNodeClassID);
			foreach ($indexSubClassIDs as $siid) {
				$accessData[] = array('role_id'=>$role_id, 'node_id'=>$siid);
			}
			
			if (in_array('5d05eb93-1454-9350-b0c0-6e1534da220d', $_POST['perms'])) {//管理素材
				array_push($accessData, array('role_id'=>$role_id, 'node_id'=>'24ee7c82-cf59-f8c3-f2a8-31645c51737f'));//添加素材分类
				array_push($accessData, array('role_id'=>$role_id, 'node_id'=>'edfa76ed-ce8f-f70d-b134-03e7d27db33d'));//读取素材分类信息
				array_push($accessData, array('role_id'=>$role_id, 'node_id'=>'690ec9b2-d086-b0e3-3edf-0dc1e5e61b57'));//删除素材
			}
			
			if (in_array('733eefe9-0d79-f427-b6aa-5314e3f2077d', $_POST['perms'])) {//新闻资源包
				array_push($accessData, array('role_id'=>$role_id, 'node_id'=>'72017362-1f8d-c3e3-4074-df7507901230'));
			}
			
			if (in_array($programNodeClassID, $_POST['perms'])) {
				foreach ($_POST['perms_Programs'] as $pp) {
					/*foreach ($_POST['perms_Programs_' . $pp] as $ppi) {
						$accessConData[] = array('role_id'=>$role_id, 'con_name'=>'Programs', 'con_classid'=>$programNodeClassID, 'con_type'=>$pp, 'con_item_classid'=>$ppi);
					}*/
					$accessConData[] = array('role_id'=>$role_id, 'con_name'=>'Programs', 'con_classid'=>$programNodeClassID, 'con_type'=>$pp, 'con_item_classid'=>'');
				}
				
				$programNodeID = $nodeModel->where(array('node_id'=>array('in', array($programNodeClassID, 'f0a54910-4665-4548-db70-c752395049c2'))))->getField('id', true);
				$progSubClassIDs = $nodeModel->where(array('pid'=>array('in', $programNodeID)))->getField('node_id', true);
				$accessData[] = array('role_id'=>$role_id, 'node_id'=>'f0a54910-4665-4548-db70-c752395049c2');
				foreach ($progSubClassIDs as $spid) {
					$accessData[] = array('role_id'=>$role_id, 'node_id'=>$spid);
				}
			}
			
			if (in_array($endpointsNodeClassID, $_POST['perms'])) {
				foreach ($_POST['perms_EndPoints'] as $pe) {
					$accessConData[] = array('role_id'=>$role_id, 'con_name'=>'EndPoints', 'con_classid'=>$endpointsNodeClassID, 'con_type'=>$pe, 'con_item_classid'=>'');
				}
				
				$endNodeID = $nodeModel->where(array('node_id'=>array('in', array($endpointsNodeClassID, $endgroupNodeClassID))))->getField('id', true);
				$endSubClassIDs = $nodeModel->where(array('pid'=>array('in', $endNodeID)))->getField('node_id', true);
				array_push($endSubClassIDs, $endgroupNodeClassID);
				foreach ($endSubClassIDs as $seid) {
					$accessData[] = array('role_id'=>$role_id, 'node_id'=>$seid);
				}
			}

			foreach ($_POST['perms'] as $p) {
				$accessData[] = array('role_id'=>$role_id, 'node_id'=>$p);
			}
			
			// 处理内容权限
			$accessConModel = M('AccessCon');
			$resultCon = $accessConModel->where(array('role_id'=>$role_id))->delete();
			$failedCon = 0;
			foreach ($accessConData as $item1) {
				$re = $accessConModel->add($item1);
				if (!$re) {
					$failedCon++;
				}
			}
			if ($failedCon) {
				$this->error('操作失败！请重试....');
			}
			
			// 处理模块权限
			$accessModel = M('Access');
			$result = $accessModel->where(array('role_id'=>$role_id))->delete();
			$failed = 0;
			foreach ($accessData as $item2) {
				$re = $accessModel->add($item2);
				if (!$re) {
					$failed++;
				}
			}
			if (!$failed) {
				$this->success('操作成功！');
			} else {
				$this->error('操作失败！请重试....');
			}
			
		} else {
			$id = I('get.rid', 0, 'int');
			if (!$id) {
				$this->error('非法请求！');
			}
			
			// 获取组信息
			$role = M('Role')->find($id);
			if (!$role) {
				$this->error('非法请求！');
			}
			
			$role['name'] = gbk2utf8($role['name']);
			$role['remark'] = gbk2utf8($role['remark']);
			$this->assign('role', $role);
	
			$nodeModel = D('Node');
			$nodes = $nodeModel->where(array('display'=>1))->order('pid asc, sort asc')->select();//只有display为1的才显示在权限分配中，此字段需在数据库中直接修改，无界面，一级和二级的都需要手动设为1
			if ($nodes) {
				$treeNodes = array();
				$nodeModel->treeNodes($treeNodes, $nodes, 1);
				//重新组织数据显示
				foreach ($treeNodes as &$treeNode) {
					/*if ($treeNode['name'] == 'Programs') {
						$x86Progams = M('Programs')->where(array('bevalid'=>1))->getField('program_classid, program_name');
						if ($x86Progams) {
							$treeNode['items']['x86'] = array('title'=>'x86触摸机', 'datalist'=>$x86Progams);
						}
						
						$aztPrograms = null;
						if ($aztPrograms) {
							$treeNode['items']['azt'] = array('title'=>'安卓触摸机', 'datalist'=>$aztPrograms);
						}
						
						$azadPrograms = null;
						if ($azadPrograms) {
							$treeNode['items']['azad'] = array('title'=>'安卓广告机', 'datalist'=>$azadPrograms);
						}
					}*/
					/*if ($treeNode['name'] == 'EndPoints' || $treeNode['name'] == 'Programs') { zjh 请求董工，改为如下一行*/
					if ($treeNode['name'] == 'EndPoints' ) {
						$treeNode['items']['x86'] = array('title'=>'x86触摸机');//title只用于模板显示
						$treeNode['items']['azt'] = array('title'=>'安卓触摸机');
						$treeNode['items']['azad'] = array('title'=>'安卓广告机');
					}
				}
				$this->assign('nodes', $treeNodes);
			}

			// 获取当前组已分配的权限
			$roleAccess = M('Access')->where(array('role_id'=>$id))->getField('node_id', true);
			$this->assign('roleAccess', $roleAccess);
			//var_dump($roleAccess);
			
			$roleAccessCon = M('AccessCon')->where(array('role_id'=>$id))->select();
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
			$this->assign('roleAccessCon', $roleAccessCons);
			$this->display();
		}
	}
	
	public function assignPermsOLD() {
		
		// 获取内容管理模块的权限ID
		$contentModuleID = M('Node')->where(array('name'=>'Scences'))->getField('node_id');
		
		if (IS_POST) {
			$role_id = I('post.role_id', 0, 'int');
			$sids = trim(I('post.sids'), '-');
			$pids = trim(I('post.pids'), '-');
			if (!$role_id) {
				$this->error('非法操作！');
			}
			
			$initData1 = $initData2 = array();
			
			// 处理场景数据权限
			$roleScenceitemsModel = M('RoleScencesitems');
			$roleScenceitemsModel->where(array('role_id'=>$role_id))->delete();
			if (in_array($contentModuleID, $_POST['perms']) && !empty($sids)) {
				
				// 如果勾选了**内容管理**权限，则自动赋予用户 $initData1 中的模块权限
				//$initData1 = array(12, 36, 37, 38, 39, 40, 41, 42, 43);
				$initData1 = array('bcc14bd7-b8f8-5076-0f07-3f35756b8217','b6f667cb-1526-4560-2ce7-6087814d212e','19a447d8-66ac-ae2f-5516-e2165dd03445','41ea7516-8a13-bdde-7f8b-cb9d1994f8b2','162ec157-67fc-7201-7ee9-f9223736d189','368a5933-d829-0a75-30ea-4545545a52c8','0b4de3ae-0e0b-c960-7969-06a8bbc9f9b6','7fe11b6d-b660-9cbf-b6aa-f2756bacda57','4db035d9-eb2e-419a-7a8d-81e2238ae00d');
				
				$rsiData = array();
				if (!empty($sids)) {
					foreach (explode('-', $sids) as $sid) {
						$rsiData[] = array('id'=>$sid, 'type'=>1);
					}
				}
				
				if (!empty($pids)) {
					foreach (explode('-', $pids) as $pid) {
						$rsiData[] = array('id'=>$pid, 'type'=>2);
					}
				}
				
				// 把场景插件权限写入数据库
				$errCnt = 0;
				foreach ($rsiData as $rsi) {
					$data['role_id'] = $role_id;
					$data['scence_id'] = $rsi['id'];
					$data['type'] = $rsi['type'];
					$re = $roleScenceitemsModel->add($data);
					if (!$re) $errCnt++;
				}
				if ($errCnt) {
					$this->error('操作失败！请重试....');
				}
			}
			
			// 处理模块权限
			$accessModel = M('Access');
			$result = $accessModel->where(array('role_id'=>$role_id))->delete();
			$data = array();
			
			// 如果用户勾选了**修改班牌配置 31**权限，则自动赋予用户 模块ID为34，35，45的权限
			/* if (in_array(31, $_POST['perms'])) {
				$tmp = array(34, 35, 45);
				$initData1 = array_merge($initData1, $tmp);
			} */
			if (in_array('1aa8b4dd-829a-e92f-df84-0f30bb163cc7', $_POST['perms'])) {
				$tmp = array('9d5d8b08-897e-9242-1698-d2e61a67f0f0', '280ea3e6-16a6-cc84-5b44-e033c46664ea', 'e5788854-361a-6850-0fca-76628b2d9493');
				$initData1 = array_merge($initData1, $tmp);
			}
			//$initData2 = array(1, 2, 7, 8, 9, 10, 11, 16);
			$initData2 = array('b6760535-03b6-e6a4-7d9e-07ad6b32057f','91f7b348-3d99-debd-a703-ac7ce6e03649','d1404f93-256c-40c4-34f4-757a75a6a3c1','3f305549-feed-c2da-48f4-170b15b49bad','dd07120d-b681-af48-9c10-6dd3387fc210','a8c5f986-2d65-e239-b208-ee634a9ce082','375ad2cf-8d45-197e-4908-376bfdb4cb2a','bbbbd35c-4694-2876-14ce-43cf6f086980');
			$perms = empty($_POST['perms']) ? $initData2 : array_merge($initData1, $initData2, $_POST['perms']);
			foreach (array_unique($perms) as $nodeId) {
				$data[] = array('role_id'=>$role_id, 'node_id'=>$nodeId);
			}
			
			$failed = 0;
			foreach ($data as $item) {
				$re = $accessModel->add($item);
				if (!$re) {
					$failed++;
				}
			}
			if (!$failed) {
				$this->success('操作成功！');
			} else {
				$this->error('操作失败！请重试....');
			}
	
		} else {
			$id = I('get.rid', 0, 'int');
			if ($id) {
				// 获取组信息
				$role = M('Role')->find($id);
				if ($role) {
					$role['name'] = gbk2utf8($role['name']);
					$role['remark'] = gbk2utf8($role['remark']);
					$this->assign('role', $role);
	
					// 处理系统模块权限
					$nodeModel = D('Node');
					//$initData3 = array(2, 12, 14, 16, 17, 18, 19, 20, 34, 35, 36, 45, 48, 49);
					$initData3 = array('91f7b348-3d99-debd-a703-ac7ce6e03649','bcc14bd7-b8f8-5076-0f07-3f35756b8217','39144d22-f7bb-24c1-23bf-222d78f441ac','bbbbd35c-4694-2876-14ce-43cf6f086980','e51c2f15-85ad-3adc-fff6-7f2787f0c3ba','750b06b1-345b-d73a-28f5-38b8c99471b0','185c99ad-6878-4686-c729-446767bf4ba7','6903040d-5b06-590f-6ec6-45137bbd173d','9d5d8b08-897e-9242-1698-d2e61a67f0f0','280ea3e6-16a6-cc84-5b44-e033c46664ea','b6f667cb-1526-4560-2ce7-6087814d212e','e5788854-361a-6850-0fca-76628b2d9493','fbdc2910-5953-0233-4d11-048c2e95d488','4468a540-ed9f-3973-e9fd-5a759240d35d');
					$nodes = $nodeModel->where(array('node_id'=>array('not in', $initData3)))->order('pid asc, sort asc')->select();
					if ($nodes) {
						foreach ($nodes as &$nod) {
							$nod['title'] = gbk2utf8($nod['title']);
							$nod['remark'] = gbk2utf8($nod['remark']);
						}
	
						$treeNodes = array();
						$nodeModel->treeNodes($treeNodes, $nodes, 1);
						//dump($treeNodes);
						$this->assign('nodes', $treeNodes);
						
						// 获取当前组已分配的权限
						$roleAccess = M('Access')->where(array('role_id'=>$id))->getField('node_id', true);
						$this->assign('roleAccess', $roleAccess);
					}
					
					/// 处理内容管理模块权限（即：场景插件权限）
					$ScenceModel = D('Scences');
					$sList = $ScenceModel->field(array('id','classid','scencename','parentscence_id','ishomescence'))->order('parentscence_id asc, id asc')->select();
					 
					$homeSecnce = null;
					foreach ($sList as $scen) {
						if ($scen['ishomescence'] == 'True') {
							$homeSecnce = $scen;
							break;
						}
					}
					 
					if ($homeSecnce) {
						$sTree = array();
						$accScenceIds =  M('RoleScencesitems')->field(array('scence_id', 'type'))->where(array('role_id'=>$id))->select();
						$treeAcc = array('sids'=>array(), 'pids'=>array());
						foreach ($accScenceIds as $asi) {
							$treeAcc[$asi['type'] == 1 ? 'sids' : 'pids'][] = $asi['scence_id'];
						}
						$this->spTree($sTree, $homeSecnce, $treeAcc);
					}
					
					$this->assign('sTree', json_encode($sTree));
					
				} else {
					$this->error('非法请求！');
				}
			} else {
				$this->error('非法请求！');
			}
	
			$this->assign('contentModuleID', $contentModuleID);
			$this->display();
		}
	}
	
	private function spTree(&$sTree, $parentScence, $accIds) {
	
		$sTree = array('id'=>$parentScence['id']*1, 'name'=>gbk2utf8($parentScence['scencename']), 'isParent'=>true, '_dataType'=>'scence', 'open'=>true);
		
		if (in_array($parentScence['id'], $accIds['sids'])) {
			$sTree['checked'] = true;
		}
		 
		$scenceModel = D('Scences');
		$childrenScences = $scenceModel->where(array('parentscence_id'=>$parentScence['classid']))->order('id asc')->select();
		if ($childrenScences) {
			foreach ($childrenScences as $cs) {
				$this->spTree($sTree['children'][], $cs, $accIds);
			}
		}
		 
		$pluginsModel = D('Plugins');
		$itemManagerTypes = array_keys($this->pluginsTypes());
		$pluginsWhere = array('belong_scenceid'=>$parentScence['classid'], 'itemtype_classid'=>array('in', $itemManagerTypes));
		$pluginsWhere['allowbackdata'] = 1;
		$plugins = $pluginsModel->field(array('id','name'))->where($pluginsWhere)->order('id asc')->select();
		foreach ($plugins as $pi) {
			$pitem = array('id'=>$pi['id']*1, 'name'=>gbk2utf8($pi['name']), '_dataType'=>'item', 'open'=>true);
			if (in_array($pi['id'], $accIds['pids'])) {
				$pitem['checked'] = true;
			}
			
			$sTree['children'][] = $pitem;
		}
	}
	
	private function scencesItems() {
		$scences = D('Scences')->field(array('id','classid','scencename'))->order('id asc')->select();
		if ($scences) {
			$itemsModel = D('Plugins');
			
			// 插件过滤器
			$itemManagerTypes = array_keys($this->pluginsTypes());
			
			foreach ($scences as &$sce) {
				$sce['scencename'] = gbk2utf8($sce['scencename']);
				$sce['items'] = $itemsModel->field(array('id','classid','name'))->where(array('belong_scenceid'=>$sce['classid'], 'itemtype_classid'=>array('in', $itemManagerTypes)))->order('id asc')->select();
				if ($sce['items']) {
					foreach ($sce['items'] as &$item) {
						$item['name'] = gbk2utf8($item['name']);
					}
				}
			}
		}
		return $scences;
	}
	
	/**
	 * 删除管理组
	 */
	public function delRole() {
		$id = I('get.rid', 0, 'int');
	
		if ($id) {
	
			// 非空的组不能删除
			$roleItemCounts = M('Users')->where(array('role_id'=>$id))->count();
	
			if ($roleItemCounts > 0) {
				$this->error('非空的组不能删除！');
			}
	
			$roleModel = M('Role');
			$result = $roleModel->delete($id);
			if ($result !== false) {
				$this->success('操作成功！');
			} else {
				$this->error('操作失败！[原因]：' . $roleModel->getError());
			}
		} else {
			$this->error('非法请求！');
		}
	}
	
}