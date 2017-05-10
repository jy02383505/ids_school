<?php
class SchoolAction extends CommonAction {
    
	
    /**
     * 学校列表管理
     */
    public function index() {      
		$this->schoolList();  
		//$this->display("School/schoolList");
        
    }	
	
	/**
	 * 学校列表
	 *　author:zjh
	 * 表中只有一条记录，直接转到编缉页，多条才显示列表
	*/
	public function schoolList(){
		ob_start();
		//实例化一
//		$model = D('School');

		//实例化二
		$model = new Model();
		$count = $model->table("TB_Sch_School")->count(); 
		if (1 == $count){
			$result = $model->table("TB_Sch_School")->find(); 
			$id = $result['id'];
			
			$url = U('School/editSchool',array("id"=>$id));
			ob_end_clean();
			header("Location:".$url);
			
		}
		$datas = $model->table("TB_Sch_School")->select(); 
		
		//实例化三
//		$model = M();
//		$datas = $model->query("SELECT * FROM TB_Sch_School ORDER BY id DESC");
		
		//实例化四
		//$model = M("Sch_School");
		//$datas = $model->select(); 
		
		//测试
		//$testModel = M("Test","tb_");//这个可以用，对于TB_Sch_School这类的两个前缀的不适用
		//$d = $testModel->select();
		//var_dump($d);
		
		//测试：无模型类，表名前缀可省略，会自动匹配前缀，对应表tb_Test
		//$testModel = M("Test");//这个可以用，对于TB_Sch_School这类的两个前缀的不适用，
		//$d = $testModel->select();
		//var_dump($d);		
		
		$this->assign('datas', $datas);
		$this->display("School/schoolList");
	}
	
		
	/**
	 * 添加学校
	 * author:zjh
	*/
     public function addSchool() {
		
        if (IS_POST) {
			$Model = D('School');
			import('ORG.Util.String');
			$name = String::msubstr(trim(I('post.name')),0,20,'utf-8',false);//校名
			$code = String::msubstr(trim(I('post.code')),0,10,'utf-8',false);//学校编码
			$motto = String::msubstr(trim(I('post.motto')),0,20,'utf-8',false);//校训
			$description = String::msubstr(trim(I('post.description')),0,500,'utf-8',false);//学校介绍
			
            $data['name'] = $name;//校名
			$data['code'] = $code;//学校编码
            $data['logo'] = trim(I('post.photo'));//学校图标
			$data['motto'] = $motto;//校训
			$data['description'] = $description;//学校介绍
			$data['isDefault'] = 0;
			$result = $Model->data($data)->add();

		   // 执行操作
		   if ($result !== FALSE) {
			   //$insertId = $result;
			   $this->success('操作成功！', U('School/schoolList'));
		   } else {
			   $this->error('操作失败！[原因]：' . $Model->getError());
		   }
        } else {
            // 获取
            $this->display("editSchool");
        }
		 
		
	}
	
	/**
	 * 编缉学校	
	 * author:zjh
	*/
    public function editSchool() {
        if (IS_POST) {
			$model = D('School');
            $id = I('post.id', 0, 'int');
			import('ORG.Util.String');
			$name = String::msubstr(trim(I('post.name')),0,20,'utf-8',false);//校名
			$code = String::msubstr(trim(I('post.code')),0,10,'utf-8',false);//学校编码
			$motto = String::msubstr(trim(I('post.motto')),0,20,'utf-8',false);//校训
			$description = String::msubstr(trim(I('post.description')),0,500,'utf-8',false);//学校介绍
			
            $data['name'] = $name;//校名
			$data['code'] = $code;//学校编码
            $data['logo'] = trim(I('post.photo'));//学校图标
			$data['motto'] = $motto;//校训
			$data['description'] = $description;//学校介绍
			$data['isDefault'] = 1;
            
            $result = $model->where("id=".$id)->save($data);
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/schoolList'));
			} else {
			   $this->error('操作失败！[原因]：' . $model->getError());
			}   

            
        } else {
            $id = I('get.id',0,'intval');
			$model = D('School');
			$map['id'] = $id;
			$datas = $model->where($map)->find();
			
            if ($datas) {
				//$mallInfo['mainContent'] = stripslashes($mallInfo['mainContent']);
				$datas['description'] = html_entity_decode($datas['description']);//还原成html源码，处理掉转义等
				$this->assign('datas', $datas);
				$this->display("editSchool");               
            }

			
        }
    }	
	
	/**
	 * 删除学校	
	 * author:zjh
	*/
    public function delSchool() {
            $id = I('request.id',0,'intval');
			$model = D('School');
			
			$count = $model->count();
			if ($count == 1){
				$this->error('只有一个学校记录，不允许删除！' , U('School/schoolList'));
			}
			
			$map['id'] = $id;
			$result = $model->where($map)->delete();
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/schoolList'));
			} else {
			   $this->error('操作失败！[原因]：' . $model->getError());
			}    
			
			
	}
		
		
	/**
	 * zjh add
	 * 学校LOGO上传，新增时不入库，修改时删除旧图片并更新记录
	*/
	public function uloadify_icon() {
		// $session_name = session_name();
		// if(!isset($_POST[$session_name])){
		// 	exit;
		// }else{
		// 	session_id($_POST[$session_name]);
		// }
		// file_put_contents('postText.txt', json_encode($_POST));
		// 参数过滤
		$savename = trim(I('post.savename'));
		$appModel = trim(I('post.appModel'));

		$folderName = trim(I('post.folderName'));
		$dataID = I('post.dataID', 0, 'int');
		$isMyUpRoot = I('post.isMyUpRoot', 0, 'int');
		$isDBWrite = I('post.isDBWrite', 0, 'int');
		
		$dataType = I('post.dataType');//zjh add : 资源类型，图片image，音乐music，文件file，视频video
		$dataID = I('post.dataID', 0, 'int'); ;//学校ID，为多学校预留


		// 实例化框架上传类
		import('ORG.Net.UploadFile');
		$uphandle = new UploadFile();
		
		// 设置上传文件允许的格式，以及文件大小
		$allowExts = C('UPIMG_ALLOW_TYPES');
		
		$uphandle->allowExts = $allowExts;
		$uphandle->maxSize = C('UP'.strtoupper(in_array($dataType, array('all', 'imagevideo')) ? 'video' : $dataType).'_MAX_SIZE');
		
		// 处理上传文件保存路径  && 处理文件保存名称
		$uploadRoot = '';
		if ($isMyUpRoot) {
			$uploadRoot = C('UPLOAD_COMM_PATH');//'UPLOAD_COMM_PATH'		=>	'Uploads/',apps/conf/Madmin/config.php中定义的
		} elseif ($dataType == 'soft') {
			$uploadRoot = C('a_main_upfile');
		} else {
			$uploadRoot = C('UPLOAD_ROOT_PATH');
		}
		
		$uphandle->savePath = rtrim($uploadRoot, '/') . '/';
//		$savePath = '';
		if ($folderName != '') {
			$savePath = trim($folderName, '/') . '/';
			$uphandle->savePath .= $savePath;
			$uphandle->saveRule = '';
			$uphandle->uploadReplace = true;
		} elseif ($appModel == 'school') {
		    $savePath .= 'school/';
			$uphandle->savePath .= $savePath;
			$uphandle->saveRule = generateUniqueID();
			$uphandle->uploadReplace = true;
		} else {
			if (!empty($savename)){
				$uphandle->saveRule = $savename;
			}
			elseif ($dataType == 'soft'){
				$uphandle->saveRule = '';
			}
			else
			{
				$uphandle->saveRule = generateUniqueID();
			}
			$uphandle->uploadReplace = true;
		}
		
		//调用类上传方法
		$upResult = $uphandle->uploadOne($_FILES['myUpfile']);
		
		if(!$upResult) {
			$reInfo = array(
					'stat'	=>	'0',
					'msg'	=>	$uphandle->getErrorMsg()
			);
		} else {
			$upResult = $upResult[0];
			$dbSavePath = $uploadRoot.$savePath . $upResult['savename'];//utf82gbk实际什么也不做
			
			$schoolModel = D("School");
			
			//先取到原图片地址
			$sch_datas = $schoolModel->where("id=".$dataID)->field("logo")->find();
			
			if ($sch_datas){
				$old_pic_path = $sch_datas['logo'];
			//	file_put_contents("debug.txt",PHP_EOL."old_pic_path:".$old_pic_path.PHP_EOL,FILE_APPEND);//写调试到TXT
				//$upRootPath = rtrim(C('UPLOAD_COMM_PATH'), '/') . '/';
				@unlink($old_pic_path);//删除旧图片
				if (trim($old_pic_path) != '' && is_file($old_pic_path)) {
								
				}else{
					;
				}					
			}
			
			if ($dataID){
				$datas = array();
				$datas['id'] = $dataID;
				$datas['logo'] = str_replace( '\\', '/',$dbSavePath);
				$result = $schoolModel->save($datas);
			}
			$reInfo = array(
				'stat'		=> '1',
				'url'		=>	'/' . $uploadRoot . $savePath . $upResult['savename'],
				'savePath'	=>	$dbSavePath,
				'pic'		=>	$upResult['savename'],
				'original'	=>	$upResult['name'],
				'size'		=>	$upResult['size'],
				'resourceid'=>  $data['resourceId'],
			);
		}
		// file_put_contents('uploadifive.txt', json_encode($reInfo));
		echo json_encode($reInfo);
	}
		
		
	/**
	 * 年级列表
	 *　author:zjh
	*/
	public function gradeList(){
		$model = D('SchoolGrade');

		$map = array();
		$map['id'] = array('GT',0);
		
        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
        $totals = $model->where($map)->count();
        $Page = new Page($totals, 10);
        $show = $Page->show();
        $this->assign('page', $show);	
		
		$datas = $model->where($map)->order("id DESC")->limit($Page->firstRow.','.$Page->listRows)->select(); 
		
		$this->assign('datas', $datas);
		$this->display("School/gradeList");
	}		
		
		
	/**
	 * 添加年级
	 * author:zjh
	*/
     public function addGrade() {
		
        if (IS_POST) {
			$Model = D('SchoolGrade');
            $id = I('post.id', 0, 'int');
			
			$data = array();		
		//	$data['id'] = I('post.id');	
            $data['name'] = I('post.tname');
			$result = $Model->data($data)->add();

		   // 执行操作
		   if ($result !== FALSE) {
			   //$insertId = $result;
			   $this->success('操作成功！', U('School/GradeList'));
		   } else {
			   $this->error('操作失败！[原因]：' . $Model->getError());
		   }

        } else {
            
            // 获取
            $this->display("editGrade");
        }
		 
		
	}		
		
	/**
	 * 编缉年级	
	 * author:zjh
	*/
    public function editGrade() {
        
        if (IS_POST) {
			$model = D('SchoolGrade');
            $id = I('post.id', 0, 'int');
		//	import('ORG.Util.String');
		
			$data = array();		
			$data['id'] = I('post.id');	
            $data['name'] = I('post.tname');
            
            $result = $model->save($data);
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/GradeList'));
			} else {
			   $this->error('操作失败！[原因]：' . $model->getError());
			}            
        } else {
            $id = I('get.id',0,'intval');
			$model = D('SchoolGrade');
			$map['id'] = $id;
			$datas = $model->where($map)->find();
			
            if ($datas) {
				$this->assign('datas', $datas);
				$this->display("editGrade");               
            }
        }
    }			
		
		
	/**
	 * 删除年级
	 * author:zjh
	*/
    public function delGrade() {
        $id = I('get.id', 0, 'int');
        if (!$id) {
            $this->error('非法操作！');
        }
        
        $model = D('SchoolGrade');
        $result = $model->where(array('id'=>$id))->find();
        if (!$result) {
            $this->error('非法操作，不存在该记录！');
        }
 
        // 包含子分类的父级分类不能删除
        
        $delResult = $model->where(array('id'=>$id))->delete();
        if ($delResult !== false) {
           $this->success('操作成功！', U('School/gradeList'));
        } else {
           $this->error('操作失败！[原因]：' . $delResult->getError());
        }	
	}		
	
		
	/**
	 * 班级列表
	 *　author:zjh
	*/
	public function banjiList(){
		$model = D('SchoolBanji');
		$roomModel = D('SchoolRooms');
		
		//搜索条件
		$keyboard = trim(I('get.keyboard'));
		$gradeId = I("request.gradeId",0,"int");
		
		$this->assign('keyboard', $keyboard);
		$this->assign('gradeId', $gradeId);
		$this->assign('orderNext', $orderNext);
		
		$map = array();
	//	$map['id'] = array('GT',0);

		if(session("username") == C('ADMIN_AUTH_KEY')){
			//超级管理员可显示所有班级
		} else {
			$map['id'] = array("IN",session("user_banji_list"));
		}


		
		if ($keyboard){$map['name']=array('LIKE','%'.$keyboard.'%');}
		if ($gradeId){$map['gradeId']=array('EQ',$gradeId);}
		
        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
        $totals = $model->where($map)->count();
        $Page = new Page($totals, 10);
        $show = $Page->show();
        $this->assign('page', $show);
		
		$datas = $model->where($map)->order("id DESC")->limit($Page->firstRow.','.$Page->listRows)->select(); 
		
		//重新组织信息
		$gradeModel = D('SchoolGrade');
		$teacherModel = D('SchoolTeachers');
		$studentModel = D('SchoolStudents');
		foreach ($datas as $k => $v){
			$one = $gradeModel->where("id=".$v['gradeId'])->find();
			$datas[$k]['gradeName'] = $one['name'];
			//var_dump( $one['name']);
			
			$one = $teacherModel->where("id=".intval($v['banzhurenId']))->find();
			$datas[$k]['teacherName'] = $one['name'];
			
			$one = $studentModel->where("id=".intval($v['banzhanId']))->find();
			$datas[$k]['banzhanName'] = $one['name'];	
			
			//教室
			$room_map = array();
			$room_map['id'] = $v['roomId'];
			$room_datas = $roomModel->where($room_map)->find();
			if ($room_datas){
				$datas[$k]['room'] = $room_datas['name'];
			}else{
				$datas[$k]['room'] = "";
			}
		}
		
		$this->assign('datas', $datas);
		
		
		//年级列表 START
		$Model = D('SchoolGrade');
		$map = array();
		$grades = $Model->getList($map);
		$this->assign('grades', $grades);
		//年级列表 END
		
		
		
		$this->display("School/BanjiList");
	}			
		
		
	/**
	 * 添加班级
	 * author:zjh
	*/
     public function addBanji() {
		
        if (IS_POST) {
			$Model = D('SchoolBanji');
            $id = I('post.id', 0, 'int');
			
			//课程表中关联班级的教室，所以此处必须设置教室roomId
			/*$roomId = I('post.roomId', 0, 'int');			
			if (!$roomId){
				$this->error("教室必须设置");
			}*/
			
			$teacherIdArr=I('post.teacherIdStr');
			$teacherId = intval(implode(',',$teacherIdArr));//弹窗中设为单选，此处收到的只是一个数值，
			
			$studentIdArr=I('post.studentIdStr');
			$studentId = intval(implode(',',$studentIdArr));//弹窗中设为单选，此处收到的只是一个数值
					
			
			import('ORG.Util.String');//ThinkPHP/Extend/Library/ORG/Util/String.class.php
			$banjiName = String::msubstr(trim(I('post.tname')),0,10,'utf-8',false);
			$result = $Model->where("name='".$banjiName."'")->find();
			if ($result){
				$this->error("班级名称已存在");
			}
			
			$data = array();		
            $data['name'] = $banjiName;
			$data['gradeId'] = I('post.gradeId', 0, 'int');
			$data['roomId'] = I('post.roomId', 0, 'int');
			$data['banzhurenId'] = $teacherId;
			$data['banzhanId'] = $studentId;
			$data['logo'] = I('post.photo');
			$data['description'] = I('post.description');
			$data['manifesto'] = String::msubstr(trim(I('post.manifesto')),0,200,'utf-8',false);//班级宣言

			$result = $Model->data($data)->add();
			
			//创建默认相册，命名为“班级风采”，对应字段为isDefault=1
			$albumModel = D("SchAlbums");
			if ($result){
				$countAlbum = $albumModel->where("banjiId=".$result)->count();//要检测是因为有时候会直接删除班级的测试数据，这样检测会更安全
				if (!$countAlbum){
					$data = array();
					$data['name']="班级风采";
					$data['description']="";
					$data['isDefault']=1;
					$data['banjiId']=$result;
					$result = $albumModel->data($data)->add();
				}else{
					$countDefaultAlbum = $albumModel->where("isDefault=1 and banjiId=".$result)->count();
					if ($countDefaultAlbum){
						$data = array();
						$data['name']="班级风采";
						$data['description']="";
						$data['isDefault']=1;
						$data['banjiId']=$result;
						$result = $albumModel->data($data)->add();				
					}
				}
			}
			
			
			
			/*直接在编缉教室时指定，此处不需要了
			if ($result){
				//更新教室表里的banjiId
				$room_model = D("SchoolRooms");
				$room_model->banjiId = 0;
				$room_model->where("banjiId=".$result)->save();
				
				$room_model->banjiId = $result;
				$room_model->where("id=".$roomId)->save();	
				
			}*/

		   // 执行操作
		   if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/BanjiList'));
		   } else {
			   $this->error('操作失败！[原因]：' . $Model->getError());
		   }

        } else {
			$seltype = "radio";
			$this->assign('teacherModalSelType', $seltype);//教师选择弹出框中只允许单选
			
			$seltype = "radio";
			$this->assign('studentModalSelType', $seltype);//学生弹出框中只允许单选
			
            //年级列表 START
			$Model = D('SchoolGrade');
			$map = array();
			$grades = $Model->getList($map);
			$this->assign('grades', $grades);
			//年级列表 END
			
            //班级列表 START
			$banjiModel = D('SchoolBanji');
			$map = array();
			$banjis = $banjiModel->getList($map);
			$this->assign('banjis', $banjis);
			//班级列表 END
			
            //科目列表 START
			$subjectsModel = D('SchoolSubjects');
			$map = array();
			$subjects = $subjectsModel->getList($map);
			$this->assign('subjects', $subjects);
			//列表 END	
			
			//教师列表 START
			$teachersModel = D('SchoolTeachers');
			$map = array();
			$teachers = $teachersModel->getTeachersAndSubjectBanji($map);
			$this->assign('teachers', $teachers);
			//列表 END	
			
            //学生列表 START
			$stuModel = D('SchoolStudents');
			$map = array();
			$students = $stuModel->getList($map);
			$this->assign('students', $students);
			//学生列表 END
			
			//教室列表
			$roomModel = D('SchoolRooms');
			$map = array();
			$rooms = $roomModel->select($map);
			$this->assign('rooms', $rooms);
			//教室列表			
						
            // 获取
            $this->display("editBanji");
        }
		 
		
	}		
		
	/**
	 * 编缉班级	
	 * author:zjh
	*/
    public function editBanji() {
        
        if (IS_POST) {
			$model = D('SchoolBanji');
			$gradeModel = D('SchoolGrade');
			//$banjiClassModel = D('SchoolBanjiClass');
            $id = I('post.id', 0, 'int');
			$photo = I('post.photo',"","trim");
			
			//课程表中关联班级的教室，所以此处必须设置教室roomId
			/*$roomId = I('post.roomId', 0, 'int');			
			if (!$roomId){
				$this->error("教室必须设置");
			}*/
			
			
			$this->check_banji($id);//是否可操作此班级ID
			
			$old = $model->where("id=".$id)->find();
		//	$uploadRootPath = rtrim(C('UPLOAD_COMM_PATH'), '/') . '/';
			if (!empty($photo) && $photo != $old['logo']){
				if (is_file(ltrim($old['logo'],"/"))) {
					@unlink(ltrim($old['logo'],"/"));//删除旧图
				}			
			}
			
			import('ORG.Util.String');
			$banjiName = String::msubstr(trim(I('post.tname')),0,10,'utf-8',false);
			$result = $model->where("name='".$banjiName."'")->find();
			if ($result){
				if ($result['id'] == $id){
					;
				} else{
					$this->error("班级名称已存在");
				}
			}
			
			$teacherIdArr=I('post.teacherIdStr');
			$teacherId = intval(implode(',',$teacherIdArr));//弹窗中设为单选，此处收到的只是一个数值，
			
			$studentIdArr=I('post.studentIdStr');
			$studentId = intval(implode(',',$studentIdArr));//弹窗中设为单选，此处收到的只是一个数值，类
		
			$data = array();
			$data['id'] = I('post.id',0,"int");	
            $data['name'] = String::msubstr(trim(I('post.tname')),0,10,'utf-8',false);
			$data['gradeId'] = I('post.gradeId', 0, 'int');
			$data['roomId'] = I('post.roomId', 0, 'int');
			$data['banzhurenId'] = $teacherId;
			$data['banzhanId'] = $studentId;
			$data['logo'] = $photo;
			$data['description'] = I('post.description');
									
			$data['manifesto'] = String::msubstr(trim(I('post.manifesto')),0,50,'utf-8',false);//班级宣言
            
			$map = array();
			$map['id'] = array("eq",I('post.id',0,"int"));
			//$map['id'] = array("IN",session("user_banji_list"));
            $result = $model->save($data);
			
			//更新教室表里的banjiId
			/* 直接在编缉教室时指定，此处不需要了
			$room_model = D("SchoolRooms");
			$room_model->banjiId = 0;
			$room_model->where("banjiId=".$id)->save();
			
			$room_model->banjiId = $id;
			$room_model->where("id=".$roomId)->save();			
			*/
			
			//创建默认相册，命名为"班级风采"，对应字段为isDefault=1
			$albumModel = D("SchAlbums");
			if ($result){
				$countDefaultAlbum = $albumModel->where("isDefault=1 and banjiId=".$id)->count();//要检测是因为有时候会直接删除班级的测试数据，这样检测会更安全
				if (!$countDefaultAlbum){
					$data = array();
					$data['name']="班级风采";
					$data['description']="班级风采相册...";
					$data['isDefault']=1;
					$data['banjiId']=$id;
					$result = $albumModel->data($data)->add();				
				}			
			}
			
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/BanjiList'));
			} else {
			   $this->error('操作失败！[原因]：' . $model->getError());
			}            

            
        } else {
			
            //年级列表 START
			$Model = D('SchoolGrade');
			$map = array();
			$grades = $Model->getList($map);
			$this->assign('grades', $grades);
			//年级列表 END
			
            //教师列表 START
			$teachersModel = D('SchoolTeachers');
			$map = array();
			$teachers = $teachersModel->getTeachersAndSubjectBanji($map);
			$this->assign('teachers', $teachers);
			//列表 END	
			
            //科目列表 START
			$subjectsModel = D('SchoolSubjects');
			$map = array();
			$subjects = $subjectsModel->getList($map);
			$this->assign('subjects', $subjects);
			//列表 END	
			
			//教室列表
			$roomModel = D('SchoolRooms');
			$map = array();
			$rooms = $roomModel->select($map);
			$this->assign('rooms', $rooms);
			//教室列表
			
			
			//班级列表 START
			$banjiModel = D('SchoolBanji');
			$map = array();
			if (session("username") == C('ADMIN_AUTH_KEY')) {//session("username")
				//超级管理员，列表中显示全部班级
				;
			}else{
				//非超级管理员，班级列表中只显示有权限的
				$map['id'] = array("IN",session('user_banji_list'));
			}
			$banjis = $banjiModel->where($map)->select();
			$this->assign('banjis', $banjis);
			//班级列表 END
			
			$seltype = "radio";
			$this->assign('teacherModalSelType', $seltype);//教师选择弹出框中只允许单选
			
			$seltype = "radio";
			$this->assign('studentModalSelType', $seltype);//学生弹出框中只允许单选
			
			$this->assign("hideStudentModelSearch",0);//隐藏学生弹出框中的搜索区

            $id = I('get.id',0,'intval');
			
			$this->check_banji($id);//是否可操作此班级ID
			
			$modelBanji = D('SchoolBanji');
			
			$map = array();
			$map['id'] = $id;
			
			$datas = $modelBanji->where($map)->find();
            if ($datas) {
				//学生列表 START
				$stuModel = D('SchoolStudents');
				$map = array();
				$map['banjiId'] = $id;//只显示本班的学生
				$students = $stuModel->where($map)->select();//
				$students_count = $stuModel->where($map)->count();//
				
				//学生对话框：对话框中的提示
				if ($students_count == 0){$student_dialog_message = "本班暂无录入学生信息，请到[学生管理]进行设置";}else{$student_dialog_message = "";}
				$this->assign('student_dialog_message', $student_dialog_message);
				
				//学生对话框：对话框中显示本班级及本班学生
				$banji_one = array();
				$banji_one[] = array("banjiId"=>$id,"banjiName"=>$datas['name'],"studentCount"=>$students_count,"studentList"=>$students);
				$this->assign("data_banji_student_model",$banji_one);	
				
				//学生对话框：在对话框中选中当前班长
				$this->assign("strStudentsId",$datas['banzhanId']);
				
				//班级编缉页：显示当前班长姓名
				if ($datas['banzhanId']){
					$student = $stuModel->where("id=".$datas['banzhanId'])->field("name")->find();
					$this->assign("banzhanName",$student['name']);
				}
				
				//学生列表 END
				
				//已提交的已选择教师，预先复选中
				$arr_checked = array();
				$arr_checked = explode(',',$datas['banzhurenId']);
				$teacherModel = D('SchoolTeachers');
				if ( !empty( $arr_checked )){
					foreach ($arr_checked as $v){ 
						$one = $teacherModel->where('id='.intval($v))->field('id,name')->find(); 
						//var_dump($one);
						if ($one){//忽略首尾的0
							$teacher_list[]=array('id'=>$one['id'],'name'=>$one['name']);
						}
					} 
					
				}
				$this->assign('teacher_list', $teacher_list);//
				$datas['teacherId']=$datas['banzhurenId'];
				$this->assign("strTeachersId",$datas['banzhurenId']);
				
				
				
				$this->assign('datas', $datas);
				$this->banjis = $banjis;
				$this->display("editBanji");               
            } else {
				$this->error('操作失败！[原因]：' . $modelBanji->getError());
			}

        }
    }			
				
	/**
	 * 删除班级
	*/
	public function delBanji(){
        /*
		$id = I('get.id', 0, 'int');
        if (!$id) {
            $this->error('非法操作！');
        }
        
        $model = D('SchoolBanji');
        $result = $model->where(array('id'=>$id))->find();
        if (!$result) {
            $this->error('非法操作，不存在该记录！');
        }else{
			if (is_file(ltrim($result['logo'],"/"))) {
				@unlink(ltrim($result['logo'],"/"));//删除旧图
			}			
		}

		//更新教室表里的banjiId
		$room_model = D("SchoolRooms");
		$room_model->banjiId = 0;
		$room_model->where("banjiId=".$id)->save();
		
		//更新学生表里的banjiId
		$student_model = D("SchoolStudents");
		$student_model->banjiId = 0;
		$student_model->where("banjiId=".$id)->save();
		
		//删除班级课程表
		
		//删除班级视频集
		
		//删除班级相册
		
		//删除班级座位安排
		
		//删除班级学生
		
		
        
        $delResult = $model->where(array('id'=>$id))->delete();
        if ($delResult !== false) {
           $this->success('操作成功！', U('School/banjiList'));
        } else {
           $this->error('操作失败！[原因]：' . $delResult->getError());
        }*/	
		
	    $ids = trim(I('post.nids'));
	
	    if (empty($ids)) {
	        die(json_encode(array('stat'=>0, '请求参数错误！')));
	    }
	
	    $idsArr = explode(',', trim($ids, ','));
	    if (count($idsArr) <= 0) {
	        die(json_encode(array('stat'=>0, '请求参数错误！')));
	    }
	
	    $model = D('SchoolBanji');
		$map = array();
		$map['id'] = array("IN",$ids);
		$datas = $model->where($map)->select();
		foreach($datas as $k=>$v){
			//主要是保证删除图片
			if (!empty($v['logo'])){
				$imagePath = C('UPLOAD_COMM_PATH')."/".ltrim($v['logo'],"/");
				@unlink($imagePath);//删除图片
			}
			
			//@unlink(ltrim('Uploads/school/6f439e9a-8e02-3a73-6e30-0ba70b0e3df6.jpg',"/"));
			$result = $model->where("id=".$v['id'])->delete();
		}		
		
	    if ($result !== false) {
	        die(json_encode(array('stat'=>1, '操作成功！')));
	    } else {
	        die(json_encode(array('stat'=>0, '操作失败！[原因]：' . $resModel->getError())));
	    }	
		
		
			
	}
	

	
	/**
	 * 班级分类列表，分页有问题
	 * author:zjh
	*/
     public function banjiClass() {
        $banjiClassModel = D('SchoolBanjiClass');
		
        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
		$where = array();
		//$where['Pid'] = 0;
        $totals = $banjiClassModel->where($where)->count();
        $Page = new Page($totals, 2);
		//分页跳转的时候保证查询条件
		foreach($map as $key=>$val) {
			$Page->parameter[$key] = urlencode($val);
		}
        $show = $Page->show();
        $this->assign('page', $show);
		

        $originTypes = $banjiClassModel->order('sort asc,pid asc, id asc')->select();
        $datas = array();
        $banjiClassModel->sortedTypes($datas, $originTypes);
        //var_dump($datas);
        $this->assign('datas', $datas);

		$this->display("banjiClass");
	}	
	
	
	/**
	 * 添加班级分类
	 * author:zjh
	*/
    public function addBanjiClass() {
        if (IS_POST) {
			// 处理表单提交参数
			$name = I('post.name');
			$pid = I('post.pid', 0, 'int');
			$sort = I('post.sortnum', 0, 'int');
			
			// 实例化分类模型
			$banjiClassModel = D('SchoolBanjiClass');
			$data['name'] = $name;
							import('ORG.Util.String');
							$uuid = String::uuid();
							$uuid = str_replace("{","",$uuid);
							$uuid = str_replace("}","",$uuid);
			$data['uuid'] =	$uuid;//import('ORG.Util.String');
			$data['pid'] = $pid;
			$data['sort'] = $sort;
			
			// 执行操作
			$result = $banjiClassModel->data($data)->add();
			if ($result !== FALSE) {
				$this->success('操作成功！', U('School/banjiClass'));
			} else {
				$this->error('操作失败！[原因]：' . $banjiClassModel->getError());
			}
        } else {
			
			$classInfo = array();
			$classInfo['sortnum'] = 0;
			$this->assign('classInfo', $classInfo);
            
            // 获取父级分类数据
            $banjiClassModel = D('SchoolBanjiClass');
            $originTypes = $banjiClassModel->order('Pid asc, ID asc')->select();
            $class = array();
            $banjiClassModel->sortedTypes($class, $originTypes);
            $this->assign('class', $class);
            
            $this->display('editBanjiClass');
        }
    }	
	
	/**
	 * 修改班级分类
	 * author:zjh
	*/
	public function editBanjiClass(){
        if (IS_POST) {
			// 处理表单提交参数
			$id = I('post.id', 0, 'int');
			$name = I('post.name');
			$pid = I('post.pid', 0, 'int');
			$sort = I('post.sortnum', 0, 'int');
			
			// 实例化分类模型
			$banjiClassModel = D('SchoolBanjiClass');
			
			$data['name'] = $name;
			$data['pid'] = $pid;
			$data['sort'] = $sort;
			
			$map=array();
			$map['id']=$id;
            $result = $banjiClassModel->where($map)->save($data);
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/banjiClass'));
			} else {
			   $this->error('操作失败！[原因]：' . $banjiClassModel->getError());
			}            
			
        } else {
            
            $id = I('get.id', 0, 'int');
            if (!$id) {
                $this->error('非法操作！');
            }
            $banjiClassModel = D('SchoolBanjiClass');
            $classInfo = $banjiClassModel->where(array('id'=>$id))->find();
            if (!$classInfo) {
                $this->error('非法操作！');
            }
            
            $this->assign('classInfo', $classInfo);var_dump($classInfo);
			
            // 获取父级分类数据
            $banjiClassModel = D('SchoolBanjiClass');
            $originTypes = $banjiClassModel->order('pid asc, id asc')->select();
            $class = array();
            $banjiClassModel->sortedTypes($class, $originTypes);
            $this->assign('class', $class);
            
            $this->display('editBanjiClass');
        }
	}
	
	/**
	 * 删除班级分类
	 * author:zjh
	*/
    public function delBanjiClass() {
        $id = I('get.tid', 0, 'int');
        if (!$id) {
            $this->error('非法操作！');
        }
        
        $banjiClassModel = D('SchoolBanjiClass');
        $banjiClassInfo = $banjiClassModel->where(array('id'=>$id))->find();
        if (!$banjiClassInfo) {
            $this->error('非法操作，不存在该分类！');
        }
 
         // 包含子分类的父级分类不能删除
        $childrenClass = $banjiClassModel->where(array('pid'=>$id))->find();//有下级分类
        if ($childrenClass) {
            $this->error('请先删除下级分类！');
        }   
        $childrenBanji = D('SchoolBanji')->where(array('classId'=>$banjiClassInfo['id']))->find();//分类下有班级
        if ($childrenClass) {
            $this->error('该分类下有班级，请先将班级从此分类下移除！');
        }   		
		/*
        if ($childrenClass || $childrenBanji) {
            $this->error('该分类非空，不允许删除！');
        }*/
        
        $delResult = $banjiClassModel->where(array('id'=>$id))->delete();
        if ($delResult !== false) {
           $this->success('操作成功！', U('School/banjiClass'));
        } else {
           $this->error('操作失败！[原因]：' . $banjiClassModel->getError());
        }	
		
			
	}
	
	
	
		
	/**
	 * 教师列表
	 *　author:zjh
	*/
	public function teachersList(){
		$model = D('SchoolTeachers');
		$teacherBanjiModel = D("SchoolTeacherBanji");
		$teacherSubjectModel = D('SchoolTeacherSubject');	
		$departModel = D('SchoolDepartment');	
		
		
		//处理批量删除
		/*
		$dotype = I("request.dotype");//echo "dotype=".$dotype;
		$ids= I('get.ids', '', 'strip_tags');
		if (!empty($ids)){
			$map_1['id'] = array("IN",$ids);
			$datas_1 = $model->where($map_1)->select();
			foreach($datas_1 as $k=>$v){
				//主要是保证删除图片
				if (!empty($v['imagePath'])){
					$first = substr($v['imagePath'], 0,1);
					if ($first == "/"){
						$pic_path = substr($v['imagePath'], 1,strlen($v['imagePath'])-1);//以/开头的地址无法删除
						//echo $pic_path;exit;
					}else{
						$pic_path = $v['imagePath'];
					}
					@unlink($pic_path);//删除旧图片
				}
				
				//
				$model->where("id=".$v['id'])->delete();
			}
			//var_dump($datas_1);
		}
		*/
		
		
		//搜索条件
		$keyboard = trim(I('get.keyboard'));
		$banjiId = I('get.banjiId');
		$subjectId = I('get.subjectId');
		$orderType = I('get.orderType');
		$department = I('get.department');
		
		switch ($orderType)
		{
		case 'sortasc':
			$order = "sort ASC";
			$orderNext = 'sortdesc';
			break;  
		case 'sortdesc':
			$order = "sort DESC";
			$orderNext = 'sortasc';
			break;
		case 'idasc':
			$order = "id ASC";
			$orderNext = 'iddesc';
			break;
		default:{
			$order = "sort DESC";
			$orderNext = "sortasc";
			}
		}
		
		$this->assign('keyboard', $keyboard);
		$this->assign('banjiId', $banjiId);
		$this->assign('subjectId', $subjectId);
		$this->assign('orderNext', $orderNext);
		$this->assign('department', $department);
		
		$map = array();
		$map['id'] = array('GT',0);
		if ($keyboard){$map['name']=array('LIKE','%'.$keyboard.'%');}
	//	if ($banjiId){$map['banjiId']=array('LIKE','%,'.$banjiId.',%');}//前后加逗号，搜索：,1,对于字段为逗号分隔的id，要在入库时前后加上0,和,0
		if ($department){$map['department'] = $department;}
		if ($banjiId){
			$map_t = array();
			$map_t['banjiId'] = $banjiId;
			$tmpArr = array();
			$datas_teacher_banji = $teacherBanjiModel->where($map_t)->field("teacherId")->select();
			
			foreach($datas_teacher_banji as $k=>$v){
				$tmpArr[] = $v['teacherId'];
			}
			
			//此班级所有的教师数组
			$teachersArr_bj = array_unique($tmpArr);//移除数组中重复的值
			
			//此班级的所有教师，用逗号分隔
			$teacherStr_banji = implode(",",$teachersArr_bj);		
		//	file_put_contents("debug-getteachers.txt",PHP_EOL."----teacherStr_banji=".$teacherStr_banji.PHP_EOL,FILE_APPEND);
			$map['id'] = array("IN",$teacherStr_banji/*这已经是满足结果的教师*/);
		}
		
		if ($subjectId){
			$map_t = array();
			$map_t['subjectId'] = $subjectId;
			$tmpArr = array();
			$datas = array();
			$datas = $teacherSubjectModel->where("subjectId=".$subjectId)->field("teacherId")->select();
			
			foreach($datas as $k=>$v){
				$tmpArr[] = $v['teacherId'];
			}
			
			//此班级所有的教师数组
			$teachersArr_sub = array_unique($tmpArr);//移除数组中重复的值
			
			//此科目的所有教师，用逗号分隔
			$teacherStr_subject = implode(",",$teachersArr_sub);	
//			file_put_contents("debug-getteachers.txt",PHP_EOL."----teacherStr_subject=".$teacherStr_subject.PHP_EOL,FILE_APPEND);
			$map['id'] = array("IN",$teacherStr_subject/*这已经是满足结果的教师*/);
			
		}
		if ($banjiId && $subjectId){
			$teacherArr_tmp = array_intersect($teachersArr_bj,$teachersArr_sub);//合并
			$map['id'] = array("IN",$teacherArr_tmp/*满足两个条件的交集ID*/);
			//file_put_contents("debug-getteachers.txt",PHP_EOL."----teacherArr_tmp=".implode(",",$teacherArr_tmp).PHP_EOL,FILE_APPEND);
		}
		
		
        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
        $totals = $model->where($map)->count();
        $Page = new Page($totals, 10);
        $show = $Page->show();
        $this->assign('page', $show);	
		$order = "id DESC";
		$datas = $model->where($map)->order($order)->limit($Page->firstRow.','.$Page->listRows)->select(); //var_dump($datas);
		
		//重新组织信息
		//$gradeModel = D('SchoolGrade');
		$subjectModel = D('SchoolSubjects');//科目模型
		$teacherBanjiModel = D("SchoolTeacherBanji");
		foreach ($datas as $k => $v){
			//$one = $gradeModel->where("id=".$v['gradeId'])->find();
			//$datas[$k]['gradeName'] = $one['name'];
			
			//所带科目，从TB_Sch_Teacher_Subject表获取，可能是多条
			$teacherSubjectModel = D('SchoolTeacherSubject');
			$datas_teacher_subject = $teacherSubjectModel->where("teacherId=".$v['id'])->field("subjectId")->select();

			//所带科目名
			$subjectArrTmp = array();
			foreach($datas_teacher_subject as $kk=>$vv){
				$subjectArrTmp[] = $vv['subjectId'];
			}
			$datas[$k]['subjectId'] = $subjectArrTmp;//该老师所带科目
			$subjectIdStr = implode(",",$datas[$k]['subjectId']);

			$some = array();
			$subjectIdStr = $subjectIdStr;//gradeId多选，逗号分隔的字符串
			$some = $subjectModel->where("id in ($subjectIdStr)")->field("name")->select();
				
			//所带班级	
			$datas_teacher_banji = $teacherBanjiModel->where("teacherId=".$v['id'])->field("banjiId")->select();
			$banjiArrTmp = array();
			foreach($datas_teacher_banji as $kk=>$vv){
				$banjiArrTmp[] = $vv['banjiId'];
			}
			
			$datas[$k]['banjiId'] = implode(",",$banjiArrTmp);//该老师所带班级,$subjectArrTmp;//
				
				
			$datas_department = $departModel->where("id=".$v['department'])->field("name")->find();
			$datas[$k]['departmentName'] = $datas_department['name'];
				
			$result = "";
			if ( !empty( $some )){
				foreach ($some as $vv){ 
					$result .= "，".$vv["name"]; 
				} 
			}
			
			$datas[$k]['subjectName'] = ltrim($result,"，");
			
		}
		
		//重新组织信息，在教师列表中显示年级信息
		/*
		$gradeModel = D('SchoolGrade');
		foreach ($datas as $k => $v){
			
			$some = array();
			$gradeIdStr = $v['gradeId'];//gradeId多选，逗号分隔的字符串
			$some = $gradeModel->where("id in ($gradeIdStr)")->field("name")->select();
				
			$result = "";
			if ( !empty( $some )){
				foreach ($some as $vv){ 
					$result .= "，".$vv["name"]; 
				} 
			}
			
			$datas[$k]['gradeName'] = ltrim($result,"，");
		}
		*/

		//重新组织信息，在教师列表中显示班级信息
		$banjiModel = D('SchoolBanji');
		
		foreach ($datas as $k => $v){
			$some = array();
			$banjiIdStr = $v['banjiId'];//banjiId多选，逗号分隔的字符串
			$some = $banjiModel->where("id in ($banjiIdStr)")->field("name")->select();
				
			$result = "";
			if ( !empty( $some )){
				foreach ($some as $vv){ 
					$result .= "，".$vv["name"]; 
				} 
			}
			
			$datas[$k]['banjiName'] = ltrim($result,"，");
		}
		
		
		$this->assign('datas', $datas);
		
		//年级列表 START
		$Model = D('SchoolGrade');
		$map = array();
		$grades = $Model->getList($map);
		$this->assign('grades', $grades);
		//年级列表 END
		
		//班级列表 START
		$banjiModel = D('SchoolBanji');
		$map = array();
		$banjis = $banjiModel->getList($map);
		$this->assign('banjis', $banjis);
		//班级列表 END			
		
		//部门列表
		$departModel = D('SchoolDepartment');
		$originDepartments = $departModel->order('pid asc, id asc')->select();
		$departments = array();
		$departModel->sortedTypes($departments, $originDepartments);
		$this->assign('departments', $departments);	
		//部门 END
		
		//科目列表 START
		$subjectsModel = D('SchoolSubjects');
		$map = array();
		$subjects = $subjectsModel->getList($map);
		$this->assign('subjects', $subjects);
		//列表 END	
		
		
		$this->display("School/TeachersList");
	}				
	
	
		
	/**
	 * 添加教师
	 * author:zjh
	 * 添加教师时，所带年级和所带科目均为复选，除了在表中字段中存为逗号
	*/
     public function addTeacher() {
        if (IS_POST) {
			$model = D('SchoolTeachers');
			$gradeModel = D('SchoolGrade');
			$banjiModel = D('SchoolBanji');

            $id = I('post.id', 0, 'int');
			$code = I('post.code');
			$sex = I('post.sex');
			if (empty($sex)){$sex = "男";}
			
			//科目选择
			$subjectIdArr=$_POST['subjectIdStr'];
			$subjectId = implode(',',$subjectIdArr);
		//	var_dump($subjectIdArr);exit;
			
			//班级选择
			$banjiIdArr=$_POST['banjiIdStr'];
			$banjiId = implode(',',$banjiIdArr);
			$banjiId = "0,".$banjiId.",0";
			
			//生成年级
			$gradeId = "";
			$gradeId_datas = array();
			
			$datas = array();
			if (!empty($banjiIdArr)){
				$datas = $banjiModel->where("id in (".$banjiId.")")->field("gradeId")->select();
				foreach ($datas as $v){
					$gradeId_datas[] = $v['gradeId'];
				}
				
				$gradeId_arr = array_unique($gradeId_datas);//移除数组中重复的值
				$gradeId = implode(",",$gradeId_arr);//??????????????????????????
				$gradeId = "0,".$gradeId.",0";
			}
			
			$data = array();		
		//	$data['id'] = I('post.id');	
            $data['name'] = I('post.tname');
			$data['teacherId'] = I('post.teacherId');
			$data['code'] = trim(I('post.code'));		
			$data['sex'] = $sex;
			$data['imagePath'] = I('post.photoPath');
			$data['description'] = I('post.description');
			$data['department'] = I('post.department');

			$teacherId = $model->data($data)->add();//返回的是新增记录的id，也就是teacherId
			
			//写关联表 START
			if ($teacherId){
				$teacherBanjiModel = D('SchoolTeacherBanji');
				$teacherSubjectModel = D('SchoolTeacherSubject');
				$map = array();
				$map['teacherId'] = $teacherId;
				$teacherBanjiModel->where($map)->delete();//删除教师年级表中本教师全部记录后重新插入
				$teacherSubjectModel->where($map)->delete();//删除教师年级表中本教师全部记录后重新插入
				
				//循环添加 教师-班级
				if (is_array($banjiIdArr)){
					foreach ($banjiIdArr as $k => $v){
						$data = array();
						$data['teacherId']=$teacherId;
						$data['banjiId']=$v;
						//$teachGradeModel = D('SchoolTeacherGrade');
						$teacherBanjiModel->data($data)->add();
					}
				}
				
				//
				
				//循环添加 教师-科目
				if (is_array($subjectIdArr)){
					foreach ($subjectIdArr as $k => $v){
						$data = array();
						$data['teacherId']=$teacherId;
						$data['subjectId']=$v;
						//$teacherSubjectModel = D('SchoolTeacherSubject');
						$teacherSubjectModel->data($data)->add();
					}
				}
				
			}
			//写关联表 END

		   // 执行操作
		   if ($teacherId !== FALSE) {
			   $this->success('操作成功！', U('School/TeachersList'));
		   } else {
			   $this->error('操作失败！[原因]：' . $Model->getError());
		   }

        } else {
            //年级列表 START
			$gradeModel = D('SchoolGrade');
			$map = array();
			$grades = $gradeModel->getList($map);
			$this->assign('grades', $grades);
			//年级列表 END
			
            //班级列表 START
			$banjiModel = D('SchoolBanji');
			$map = array();
			$banjis = $banjiModel->getList($map);
			$this->assign('banjis', $banjis);
			//班级列表 END			
			
            //科目列表 START
			$subjectsModel = D('SchoolSubjects');
			$map = array();
			$subjects = $subjectsModel->getList($map);
			$this->assign('subjects', $subjects);
			//列表 END	
			
            //教师列表 START
			$teachersModel = D('SchoolTeachers');
			$map = array();
			$teachers = $teachersModel->getList($map);
			$this->assign('teachers', $teachers);
			//列表 END	
			
			//部门列表
			$departModel = D('SchoolDepartment');
			$originDepartments = $departModel->order('pid asc, id asc')->select();
			$departments = array();
			$departModel->sortedTypes($departments, $originDepartments);
			$this->assign('departments', $departments);	
			//部门 END
			
			$seltype = "checkbox";
			$this->assign('subjectModalSelType', $seltype);//科目弹出框中只允许单选				
			
            // 获取
            $this->display("editTeacher");
        }
		 
		
	}			
	
	
	/**
	 * 修改教师	
	 * author:zjh
	*/
    public function editTeacher() {
        if (IS_POST) {
			$model = D('SchoolTeachers');
			$gradeModel = D('SchoolGrade');
			$banjiModel = D('SchoolBanji');

            $id = I('post.id', 0, 'int');
			$code = I('post.code');
			$sex = I('post.sex');
			if (empty($sex)){$sex = "男";}
			$imagepath = I('post.photoPath',"","trim");
			
			if ($id){
				$old = $model->where("id=".$id)->find();
				$oldImgField = trim($old['imagePath']);
				$oldImagePath = C('UPLOAD_COMM_PATH')."/".ltrim($old['imagePath'],"/");
				
				$uploadRootPath = rtrim(C('UPLOAD_COMM_PATH'), '/') . '/';
				if ($oldImgField != '' && is_file($oldImagePath)) {
					//原图片字段不为空，并且图片文件存在//新图片与旧图片地址不同时才删除旧图
					if (!empty($imagepath) && $imagepath != $oldImgField){
						@unlink($oldImagePath);//删除旧图
					}
				 }
			}
			
			//科目选择
			$subjectIdArr=$_POST['subjectIdStr'];
			$subjectId = implode(',',$subjectIdArr);
		//	var_dump($subjectIdArr);exit;
			
			//班级选择
			$banjiIdArr=$_POST['banjiIdStr'];
			$banjiId = implode(',',$banjiIdArr);
			$banjiId = "0,".$banjiId.",0";
			
			//生成年级
			$gradeId = "";
			$gradeId_datas = array();
			
			$datas = array();
			if (!empty($banjiIdArr)){
				$datas = $banjiModel->where("id in (".$banjiId.")")->field("gradeId")->select();
				foreach ($datas as $v){
					$gradeId_datas[] = $v['gradeId'];
				}
				
				$gradeId_arr = array_unique($gradeId_datas);//移除数组中重复的值
				$gradeId = implode(",",$gradeId_arr);//??????????????????????????
				$gradeId = "0,".$gradeId.",0";
			}
			
			$data = array();		
			$data['id'] = I('post.id');	
            $data['name'] = I('post.tname');
			$data['teacherId'] = I('post.teacherId');
			$data['code'] = trim(I('post.code'));		
			$data['sex'] = $sex;
			$data['imagePath'] = I('post.photoPath');
			$data['description'] = I('post.description');
			$data['department'] = I('post.department');
            $result = $model->save($data);
			
			//写关联表 START
			if ($result !== false){//save方法的更新判断失败用 false === 来判断， 否则执行都是成功的，只是如果为0 表示没有更新任何记录
				$teacherBanjiModel = D('SchoolTeacherBanji');
				$teacherSubjectModel = D('SchoolTeacherSubject');
				$map = array();
				$map['teacherId'] = $id;
				$teacherBanjiModel->where($map)->delete();//删除教师年级表中本教师全部记录后重新插入
				$teacherSubjectModel->where($map)->delete();//删除教师年级表中本教师全部记录后重新插入
				
				//循环添加 教师-班级
				if (is_array($banjiIdArr)){
					foreach ($banjiIdArr as $k => $v){
						$data = array();
						$data['teacherId']=$id;
						$data['banjiId']=$v;
						//$teachGradeModel = D('SchoolTeacherGrade');
						$teacherBanjiModel->data($data)->add();
					}
				}
				
				//循环添加 教师-科目
				if (is_array($subjectIdArr)){
					foreach ($subjectIdArr as $k => $v){
						$data = array();
						$data['teacherId']=$id;
						$data['subjectId']=$v;
						//$teacherSubjectModel = D('SchoolTeacherSubject');
						$teacherSubjectModel->data($data)->add();
					}
				}
			}
			//写关联表 END			
			
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/TeachersList'));
			} else {
			   $this->error('操作失败！[原因]：' . $model->getError());
			}            

            
        } else {
			
            //年级列表 START
			$Model = D('SchoolGrade');
			$map = array();
			$grades = $Model->getList($map);
			$this->assign('grades', $grades);
			//年级列表 END

            //年级列表 START
			$gradeModel = D('SchoolGrade');
			$map = array();
			$grades = $gradeModel->getList($map);
			$this->assign('grades', $grades);
			//年级列表 END
			
            //班级列表 START
			$banjiModel = D('SchoolBanji');
			$map = array();
			$banjis = $banjiModel->getList($map);
			$this->assign('banjis', $banjis);
			//班级列表 END			
			
            //科目列表 START
			$subjectsModel = D('SchoolSubjects');
			$map = array();
			$subjects = $subjectsModel->getList($map);
			$this->assign('subjects', $subjects);
			//列表 END	
			
            //教师列表 START
			$teachersModel = D('SchoolTeachers');
			$map = array();
			$teachers = $teachersModel->getList($map);
			$this->assign('teachers', $teachers);
			//var_dump($teachers);
			//列表 END	
			
			//部门列表
			$departModel = D('SchoolDepartment');
			$originDepartments = $departModel->order('pid asc, id asc')->select();
			$departments = array();
			$departModel->sortedTypes($departments, $originDepartments);
			$this->assign('departments', $departments);	
			//部门 END
			
			$seltype = "checkbox";
			$this->assign('subjectModalSelType', $seltype);//科目弹出框中只允许多选

            $id = I('get.id',0,'intval');			
			$teacherModel = D('SchoolTeachers');
			
			$map = array();
			$map['id'] = $id;
			
			$datas = $teacherModel->where($map)->find();
            if ($datas) {
				//已提交的已选择科目，预先复选中
				$arr_checked = array();
				$teacherSubjectModel = D('SchoolTeacherSubject');
				$arr_checked = array();
				$map = array();
				$map['teacherId']=$id;
				$datas_teacher_subject = $teacherSubjectModel->where($map)->field("subjectId")->select();
				foreach($datas_teacher_subject as $k=>$v){
					$arr_checked[] = $v['subjectId'];
				}
				$datas['subjectId'] = implode(",",$arr_checked);


				$subjectModel = D('SchoolSubjects');
				if ( !empty( $arr_checked )){
					foreach ($arr_checked as $v){ 
					 $one = $subjectModel->where('id='.$v)->field('id,name')->find(); 
					 if ($one){//忽略首尾的0
					 	$subject_list[]=array('id'=>$one['id'],'name'=>$one['name']);
					 }
					} 
				}
				$this->assign('subject_list', $subject_list);
			//	var_dump($ts);
				
				
				//已提交的已选择班级，预先复选中
				$teacherBanjiModel = D('SchoolTeacherBanji');
				$arr_bj_checked = array();
				$map = array();
				$map['teacherId']=$id;
				$datas_teacher_banji = $teacherBanjiModel->where($map)->field("banjiId")->select();
				foreach($datas_teacher_banji as $k=>$v){
					$arr_bj_checked[] = $v['banjiId'];
				}
				$datas['banjiId'] = implode(",",$arr_bj_checked);
				
				$banjiModel = D('SchoolBanji');
				if ( !empty( $arr_bj_checked )){
					foreach ($arr_bj_checked as $v){ 
					 $one = $banjiModel->where('id='.$v)->field('id,name')->find(); 
					 if ($one){//忽略首尾的0
					 	$banji_list[]=array('id'=>$one['id'],'name'=>$one['name']);
					 }
					} 
				}
				$this->assign('banji_list', $banji_list);
				
				$this->assign('datas', $datas);
				$this->display("editTeacher");               
            } else {
				$this->error('操作失败！[原因]：' . $teacherModel->getError());
			}

        }
    }			
	
	/**
	 * 删除教师
	*/
    public function delTeacher() {
		/*
		$id = I('request.id',0,'intval');
		$model = D('SchoolTeachers');
		
		if ($id){
			$old = $model->where("id=".$id)->find();
			$uploadRootPath = rtrim(C('UPLOAD_COMM_PATH'), '/') . '/';
			if (is_file(ltrim($old['imagePath'],"/"))) {
				@unlink(ltrim($old['imagePath'],"/"));//删除旧图
			}
			
			$map['id'] = $id;
			$result = $model->where($map)->delete();
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/teachersList'));
			} else {
			   $this->error('操作失败！[原因]：' . $model->getError());
			}				
		}else{
			$this->error('操作失败！[原因]：id为空');			
		}
		*/

	    $ids = trim(I('post.nids'));
	
	    if (empty($ids)) {
	        die(json_encode(array('stat'=>0, '请求参数错误！')));
	    }
	
	    $idsArr = explode(',', trim($ids, ','));
	    if (count($idsArr) <= 0) {
	        die(json_encode(array('stat'=>0, '请求参数错误！')));
	    }
	
	    $model = D('SchoolTeachers');
		$map = array();
		$map['id'] = array("IN",$ids);
		$datas = $model->where($map)->select();
		foreach($datas as $k=>$v){
			//主要是保证删除图片
			if (!empty($v['imagePath'])){
				$imagePath = C('UPLOAD_COMM_PATH')."/".ltrim($v['imagePath'],"/");
				@unlink($imagePath);//删除图片
			}
			
			//@unlink(ltrim('Uploads/school/6f439e9a-8e02-3a73-6e30-0ba70b0e3df6.jpg',"/"));
			$result = $model->where("id=".$v['id'])->delete();
		}		
		
	    if ($resResult !== false) {
	        die(json_encode(array('stat'=>1, '操作成功！')));
	    } else {
	        die(json_encode(array('stat'=>0, '操作失败！[原因]：' . $resModel->getError())));
	    }
		
		
		
		
	}	
	
	
	
	
	
	
	
		
		
	/**
	 * 科目列表
	 *　author:zjh
	*/
	public function subjectsList(){
		$model = D('SchoolSubjects');
		
		
		//搜索条件
		$keyboard = trim(I('get.keyboard'));
		$gradeId = I('get.gradeId');
		$teacherId = I('get.teacherId');
		$orderType = I('get.orderType');
		
		switch ($orderType)
		{
		case 'sortasc':
			$order = "sort ASC";
			$orderNext = 'sortdesc';
			break;  
		case 'sortdesc':
			$order = "sort DESC";
			$orderNext = 'sortasc';
			break;
		case 'idasc':
			$order = "id ASC";
			$orderNext = 'iddesc';
			break;
		default:{
			$order = "sort DESC";
			$orderNext = "sortasc";
			}
		}
		
		$this->assign('keyboard', $keyboard);
		$this->assign('gradeId', $gradeId);
		$this->assign('teacherId', $teacherId);
		$this->assign('orderNext', $orderNext);
		
		$map = array();
		$map['id'] = array('GT',0);
		if ($keyboard){$map['name']=array('LIKE','%'.$keyboard.'%');}
		if ($gradeId){$map['gradeId']=array('LIKE','%'.$gradeId.'%');}
		if ($teacherId){$map['teacherId']=array('LIKE','%'.$teacherId.'%');}
		
        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
        $totals = $model->where($map)->count();
        $Page = new Page($totals, 10);
        $show = $Page->show();
        $this->assign('page', $show);	
		
		$datas = $model->where($map)->order($order)->limit($Page->firstRow.','.$Page->listRows)->order("id DESC")->select(); 
		
		//重新组织信息 START
		$gradeModel = D('SchoolGrade');
		foreach ($datas as $k => $v){
			$some = array();
			$gradeIdStr = $v['gradeId'];//gradeId多选，逗号分隔的字符串
			$some = $gradeModel->where("id in ($gradeIdStr)")->field("name")->select();
				
			$result = "";
			if ( !empty( $some )){
				foreach ($some as $vv){ 
					$result .= "，".$vv["name"]; 
				} 
			}
			
			$datas[$k]['gradeName'] = ltrim($result,"，");
		}
		
		$teacherModel = D('SchoolTeachers');
		foreach ($datas as $k => $v){
			$some = array();
			$teacherIdStr = $v['teacherId'];//gradeId多选，逗号分隔的字符串
			
			$some = $teacherModel->where("id in ($teacherIdStr)")->field("name")->select();
				
			$result = "";
			if ( !empty( $some )){
				foreach ($some as $vv){ 
					$result .= "，".$vv["name"]; 
				} 
			}
			
			$datas[$k]['teacherName'] = ltrim($result,"，");
		}
		
		//重新组织信息 END
		
		$this->assign('datas', $datas);
		
		//年级列表 START
		$Model = D('SchoolGrade');
		$map = array();
		$grades = $Model->getList($map);
		$this->assign('grades', $grades);
		//年级列表 END
		
		//教师列表 START
		$teachersModel = D('SchoolTeachers');
		$map = array();
		$teachers = $teachersModel->getList($map);
		$this->assign('teachers', $teachers);
		//var_dump($teachers);
		//列表 END			
		
		$this->display("School/subjectsList");
	}					
		
		
		
	/**
	 * 添加科目
	 * author:zjh
	*/
     public function addSubject() {
		
        if (IS_POST) {
			$model = D('SchoolSubjects');

            $id = I('post.id', 0, 'int');
			
			$gradeIdArr=$_POST['gradeIdStr'];
			$gradeId = '0,'.implode(',',$gradeIdArr).',0';
			
			$teacherIdArr=$_POST['teacherIdStr'];
			$teacherId = '0,'.implode(',',$teacherIdArr).',0';
			
			$data = array();		
			//$data['id'] = I('post.id');	
            $data['name'] = I('post.tname');
							import('ORG.Util.String');
							$uuid = String::uuid();
							$uuid = str_replace("{","",$uuid);
							$uuid = str_replace("}","",$uuid);
			$data['uuid'] =	$uuid;//import('ORG.Util.String');
			$data['gradeId'] = $gradeId;
								$data['gradeUUID'] = '';
			//var_dump($gradeModel->getOneUUID($data['gradeId']));exit;
			$data['teacherId'] = $teacherId;
								 $data['teacherUUID'] = '';
			$data['description'] = I('post.description');
            
            $result = $model->data($data)->add();
			if ($result) {
			   $this->success('操作成功！', U('School/subjectsList'));
			} else {
			   $this->error('操作失败！[原因]：' . $model->getError());
			}    

        } else {
            //年级列表 START
			$Model = D('SchoolGrade');
			$map = array();
			$grades = $Model->getList($map);
			$this->assign('grades', $grades);
			//年级列表 END
			

            //年级列表 START
			$gradeModel = D('SchoolGrade');
			$map = array();
			$grades = $gradeModel->getList($map);
			$this->assign('grades', $grades);
			//年级列表 END
			
            //班级列表 START
			$banjiModel = D('SchoolBanji');
			$map = array();
			$banjis = $banjiModel->getList($map);
			$this->assign('banjis', $banjis);
			//班级列表 END			
			
            //科目列表 START
			$subjectsModel = D('SchoolSubjects');
			$map = array();
			$subjects = $subjectsModel->getList($map);
			$this->assign('subjects', $subjects);
			//列表 END	
			
            //教师列表 START
			$teachersModel = D('SchoolTeachers');
			$map = array();
			$teachers = $teachersModel->getList($map);
			$this->assign('teachers', $teachers);
			//var_dump($teachers);
			//列表 END	


            $this->display("editSubject");
        }
		 
		
	}		
		
	/**
	 * 编缉科目
	 * author:zjh
	*/
    public function editSubject() {
        
        if (IS_POST) {
			$model = D('SchoolSubjects');

            $id = I('post.id', 0, 'int');
			
			$gradeIdArr=$_POST['gradeIdStr'];
			$gradeId = '0,'.implode(',',$gradeIdArr).',0';
			
			$teacherIdArr=$_POST['teacherIdStr'];
			$teacherId = '0,'.implode(',',$teacherIdArr).',0';
			
			$data = array();		
			$data['id'] = I('post.id');	
            $data['name'] = I('post.tname');
			$data['gradeId'] = $gradeId;
			$data['teacherId'] = $teacherId;
			$data['description'] = I('post.description');
            
            $result = $model->save($data);
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/subjectsList'));
			} else {
			   $this->error('操作失败！[原因]：' . $model->getError());
			}            

            
        } else {
			
            //年级列表 START
			$Model = D('SchoolGrade');
			$map = array();
			$grades = $Model->getList($map);
			$this->assign('grades', $grades);
			//年级列表 END
			

            //年级列表 START
			$gradeModel = D('SchoolGrade');
			$map = array();
			$grades = $gradeModel->getList($map);
			$this->assign('grades', $grades);
			//年级列表 END
			
            //班级列表 START
			$banjiModel = D('SchoolBanji');
			$map = array();
			$banjis = $banjiModel->getList($map);
			$this->assign('banjis', $banjis);
			//班级列表 END			
			
            //科目列表 START
			$subjectsModel = D('SchoolSubjects');
			$map = array();
			$subjects = $subjectsModel->getList($map);
			$this->assign('subjects', $subjects);
			//列表 END	
			
            //教师列表 START
			$teachersModel = D('SchoolTeachers');
			$map = array();
			$teachers = $teachersModel->getList($map);
			$this->assign('teachers', $teachers);
			//var_dump($teachers);
			//列表 END	
			

            $id = I('get.id',0,'intval');
			
			$subjectModel = D('SchoolSubjects');
			
			$map = array();
			$map['id'] = $id;
			
			$datas = $subjectModel->where($map)->find();
            if ($datas) {
				//已提交的已选择年级，预先复选中
				$arr_checked = array();
				$arr_checked = explode(',',$datas['gradeId']);
				$gradeModel = D('SchoolGrade');
				if ( !empty( $arr_checked )){
					foreach ($arr_checked as $v){ 
					 $one = $gradeModel->where('id='.$v)->field('id,name')->find(); 
					 if ($one){//忽略首尾的0
					 	$grade_list[]=array('id'=>$one['id'],'name'=>$one['name']);
					 }
					} 
				}
				$this->assign('grade_list', $grade_list);//zjh add	
				
				
				//已提交的已选择科目，预先复选中
				$arr_checked = array();
				$arr_checked = explode(',',$datas['teacherId']);
				$teacherModel = D('SchoolTeachers');
				if ( !empty( $arr_checked )){
					foreach ($arr_checked as $v){ 
					 $one = $teacherModel->where('id='.$v)->field('id,name')->find(); 
					 if ($one){//忽略首尾的0
					 	$teacher_list[]=array('id'=>$one['id'],'name'=>$one['name']);
					 }
					} 
				}
				$this->assign('teacher_list', $teacher_list);
				
				
				$this->assign('datas', $datas);
				$this->display("editSubject");               
            } else {
				$this->error('操作失败！[原因]：' . $subjectModel->getError());
			}

        }
    }			
		
	public function delSubject(){
		/*
            $id = I('request.id',0,'intval');
			$model = D('SchoolSubjects');
			
			$count = $model->count();
			if ($count == 1){
				$this->error('只有一个记录，不允许删除！' , U('School/subjectsList'));
			}
			
			$map['id'] = $id;
			$result = $model->where($map)->delete();
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/subjectsList'));
			} else {
			   $this->error('操作失败！[原因]：' . $model->getError());
			}   */
			
	    $ids = trim(I('post.nids'));
	
	    if (empty($ids)) {
	        die(json_encode(array('stat'=>0, '请求参数错误！')));
	    }
	
	    $idsArr = explode(',', trim($ids, ','));
	    if (count($idsArr) <= 0) {
	        die(json_encode(array('stat'=>0, '请求参数错误！')));
	    }
	
	    $model = D('SchoolSubjects');
	    $result = $model->where(array('id'=>array('in', $idsArr)))->delete();
	    if ($result !== false) {
	        die(json_encode(array('stat'=>1, '操作成功！')));
	    } else {
	        die(json_encode(array('stat'=>0, '操作失败！[原因]：' . $resModel->getError())));
	    }
			
	}
						
		
	/**
	 * 教室列表
	 *　author:zjh
	*/
	public function roomList(){
		$model = D('SchoolRooms');

		$map = array();
		$map['id'] = array('GT',0);
		
        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
        $totals = $model->where($map)->count();
        $Page = new Page($totals, 10);
        $show = $Page->show();
        $this->assign('page', $show);	
		
		$datas = $model->where($map)->order("id DESC")->limit($Page->firstRow.','.$Page->listRows)->select(); 
		
		//重新组织信息
		$banjiModel = D('SchoolBanji');
		$cameraModel = D('SchoolCamera');
		$endpointModel = D('Endpoint');
		foreach ($datas as $k => $v){
			//列表中显示摄像头（一个或多个）
			if ($v['cameraId']){
				$camera_map = array();
				if (strpos($v['cameraId'], ',')){
					$camera_map['Id'] = array("IN",$v['cameraId']);
				}else{
					$camera_map['Id'] = array("EQ",$v['cameraId']);
				}
				$datas_camera_tmp = array();
				$datas_camera_tmp = $cameraModel->where($camera_map)->order("Id DESC")->select();	
				
				$tmp1 = array();
				foreach($datas_camera_tmp as $kk=>$vv){
					$tmp1[] = $vv['code'];
				}
				
				$datas[$k]['cameraName'] = implode("，",$tmp1);
				
			}else{
				$datas[$k]['cameraName'] = "";
			}
			//列表中显示摄像头（一个或多个） END

			//列表中显示终端（一个或多个）
			if ($v['endpointId']){
				$endpoint_map = array();
				if (strpos($v['endpointId'], ',')){
					$endpoint_map['tid'] = array("IN",$v['endpointId']);
				}else{
					$endpoint_map['tid'] = array("EQ",$v['endpointId']);
				}
				$datas_endpoint_tmp = array();
				$datas_endpoint_tmp = $endpointModel->where($endpoint_map)->order("tid DESC")->select();	
				
				$tmp1 = array();
				foreach($datas_endpoint_tmp as $kk=>$vv){
					if (!empty($vv['touchEndPointName'])){
						$tmp1[] = $vv['touchEndPointName'];
					}else{
						$tmp1[] = $vv['touchMainId'];
					}
				}
				
				$datas[$k]['endpointName'] = implode("，",$tmp1);
				
			}else{
				$datas[$k]['endpointName'] = "";
			}
			//列表中显示终端 （一个或多个） END

			

		}
		
		$this->assign('datas', $datas);
		$this->display("School/roomList");
	}		
		
		
	/**
	 * 添加教室
	 * author:zjh
	*/
     public function addRoom() {
		
        if (IS_POST) {
			$endpointModel = D('Endpoint');
			$Model = D('SchoolRooms');
 			$banjiIdArr=I('post.banjiIdStr');
			$banjiId = intval(implode(',',$banjiIdArr));//弹窗中设为单选，此处收到的只是一个数值，类型为
			
			//摄像头选择
			$cameraIdArr=$_POST['cameraIdStr'];
			$cameraId = implode(',',$cameraIdArr);
			$linenumber = I('post.linenumber', 0, 'int');
			$columnnumber = I('post.columnnumber', 0, 'int');
			$seating = I('post.seating', 0, 'int');//不能行乘以列得到座位数，因为几列不一定有相同行数

			//终端选择	
			$endpointIdArr=$_POST['endpointIdStr'];
			$endpointId = implode(',',$endpointIdArr);					
			
			//防止终端选择重复 START /////////////
			$endpointIdOther = array();
			$tmp = 	$Model->where("endpointId <> ''")->field("endpointId")->select();

			$tmpStr = "";
			$tmpArr = array();
			foreach($tmp as $k=>$v){
				if (!empty($v['endpointId'])){
					$str = $v['endpointId'];
					$tmpStr .= ",".$str;
				}
			}
			$tmpStr = ltrim($tmpStr, ",");//去左逗号
			$tmpStr = rtrim($tmpStr, ",");//去右逗号
			
			//已占用的终端号形成一个长字符串，逗号分隔，拆成数组
			if (!empty($tmpStr)){
				$usedEndPoindIdArr = explode(",",$tmpStr);
				sort($usedEndPoindIdArr);//数组排序
				$usedEndPointIdArrNew = array_unique($usedEndPoindIdArr);//去重复
			}
			
			//当前选择的，拆成一个数组，遍历，与已占用的比较，发现重复中止遍历，并退出
			$endpointIdArr = explode(",",$endpointId);
			if (is_array($endpointIdArr)){
				foreach($endpointIdArr as $k=>$v){
					if (in_array($v,$usedEndPointIdArrNew)){
						$this->error("指定的终端已占用");
					}
				}
			}
			//防止终端选择重复 END /////////////
			
			
			
			
			//防止将多个教室重复指定到一个班级
			
			$data = array();		
            $data['name'] = I('post.tname');
			$data['floor'] = I('post.floors', 0, 'int');
			$data['linenumber'] = $linenumber;//行
			$data['columnnumber'] = $columnnumber;//列
			$data['seating'] = $seating;//座位数
			$data['cameraId'] = $cameraId;
			$data['description'] = I('post.description');
            $data['endpointId'] = $endpointId;//摄像头，逗号分隔的多个值或单值
			
			$result = $Model->data($data)->add();

			// 创建教室后自动创建课程表 added by lym
			$this->addLessionTable($result, $addRoom=1);

		   	// 执行操作
  			if ($result !== FALSE) {
				//座位表
				$seatModel = D('SchoolSeatPlan');
				$seatModel->resetSeatPlan($result,"roomId");//初始化座位表（自动清空原有教室的数据）
				
			   //$insertId = $result;
				//把摄像头存到终端表TB_TEndpoint的字段cameraId（多个终端保存的摄像头内容是一样的）
				if (is_array($endpointIdArr)){
					foreach($endpointIdArr as $k=>$v){
						$endpointModel->where("tid=".$v)->setField('cameraId',$cameraId);
					}
				}
			    $this->success('操作成功！', U('School/roomList'));
			} else {
			    $this->error('操作失败！[原因]：' . $Model->getError());
			}

        } else {
            //班级列表 START
			$banjiModel = D('SchoolBanji');
			$map = array();
			$banjis = $banjiModel->getList($map);
			$this->assign('banjis', $banjis);
			//班级列表 END	
			
			//终端列表 START
			$endpointModel = D('Endpoint');
			// $endpoint_list = $endpointModel->order("tid DESC")->getField('tid', 'touchEndPoint_GroupClassId', 'touchEndPointName', 'touchMainId');//var_dump($endpoint_list);
			$endpoint_list = $endpointModel->order("tid DESC")->select();//var_dump($endpoint_list);
			$this->assign('endpoint_list', $endpoint_list);
			//终端列表 END

			
			// 终端组列表 START
			$endGroupModel = D('EndpointsGroups');
			$groups = $endGroupModel->order('level asc, id asc')->select();
			// $groups = $endGroupModel->where(array('grouptype'=>'x86'))->order('level asc, id asc')->select();
			// if ($groups) {
			// 	$treeGrps = array();
			// 	$endGroupModel->sortNodes($treeGrps, $groups);
			// 	$this->assign('endpointGroups', $treeGrps);//var_dump($treeGrps);
			// }
			// 终端组列表 END
			foreach($groups as &$group){
				foreach($endpoint_list as $endpoint){
					if($endpoint[touchEndPoint_GroupClassId] == $group[groupclassid]){
						$group[endpoints][] = $endpoint;
					}
				}
			}
			$this->groups = $groups;

			$seltype = "radio";
			$this->assign('banjiModalSelType', $seltype);//班级弹出框中只允许单选
			
			//摄像头列表 START
			$cameraModel = D('SchoolCamera');
			$camera_list = $cameraModel->order("Id DESC")->select();
			$this->assign('camera_list', $camera_list);
			//摄像头列表 END
			
            // 获取
            $this->display("editRoom");
        }
		 
		
	}		
		
	/**
	 * 编缉教室	
	 * author:zjh
	*/
    public function editRoom() {
        if (IS_POST) {
//			$banjiId=I('post.banjiId',0,"int");
			//摄像头选择
			$cameraIdArr=$_POST['cameraIdStr'];
			$cameraId = implode(',',$cameraIdArr);
			$linenumber = I('post.linenumber', 0, 'int');
			$columnnumber = I('post.columnnumber', 0, 'int');
			$seating = I('post.seating', 0, 'int');//不能行乘以列得到座位数，因为几列不一定有相同行数
			
			
			$endpointModel = D('Endpoint');
			$model = D('SchoolRooms');
            $id = I('post.id', 0, 'int');
			
		//	$resetSeatTable = I("post.resetSeatTable",0,"int");
		//	if ($resetSeatTable){
				//简单判断一下座位表中的记录是否与 行x列一致，不一致则初始化
		//		$seatModel = D('SchoolSeatPlan');
		//		$seatModel->resetSeatPlan($id,"roomId");//初始化座位表（自动清空原有教室的数据）
		//	}
			
			$seatModel = D('SchoolSeatPlan');
			$seatCount = $seatModel->where("roomId=".$id)->count();
			
			$room = $model->where("id=".$id)->find();
			if ($room){
				$old_linenumber = $room['linenumber'];
				$old_columnnumber = $room['columnnumber'];
				
				//检测记录数是否正确，当不再直接操作数据库时，可不用检测比较
				$rightNum = $old_linenumber * $old_columnnumber;
				if ($seatCount <> $rightNum ){
					//die("计划表中的记录数与应有的记录数不匹配");
					$seatModel->resetSeatPlan($id,"roomId");//初始化座位表（自动清空原有教室的数据）
				}				
				
				//检测行列变化，相应的在座位表中新增行列或减少行列
				if ($old_linenumber != $linenumber  || $old_columnnumber!=$columnnumber){
					$seatModel = D('SchoolSeatPlan');
					$seatModel->modifySeatTable($id,$old_linenumber,$old_columnnumber,$linenumber,$columnnumber);
				}
				
			}else{
				$this->error("无此教室");
			}
			

			//终端选择	
			$endpointIdArr=$_POST['endpointIdStr'];
			$endpointId = implode(',',$endpointIdArr);					
			
			//防止终端选择重复 START /////////////
			$endpointIdOther = array();
			$tmp = 	$model->where("id <> ".$id)->field("endpointId")->select();

			$tmpStr = "";
			$tmpArr = array();
			foreach($tmp as $k=>$v){
				if (!empty($v['endpointId'])){
					$str = $v['endpointId'];
					$tmpStr .= ",".$str;
				}
			}
			$tmpStr = ltrim($tmpStr, ",");//去左逗号
			$tmpStr = rtrim($tmpStr, ",");//去右逗号
			
			//已占用的终端号形成一个长字符串，逗号分隔，拆成数组
			if (!empty($tmpStr)){
				$usedEndPoindIdArr = explode(",",$tmpStr);
				sort($usedEndPoindIdArr);//数组排序
				$usedEndPointIdArrNew = array_unique($usedEndPoindIdArr);//去重复
			}
			
			//当前选择的，拆成一个数组，遍历，与已占用的比较，发现重复中止遍历，并退出
			$endpointIdArr = explode(",",$endpointId);
			if (is_array($endpointIdArr)){
				foreach($endpointIdArr as $k=>$v){
					if (in_array($v,$usedEndPointIdArrNew)){
						$this->error("指定的终端已占用");
					}
				}
			}
			//防止终端选择重复 END /////////////
				
			//防止将多个教室重复指定到一个班级
			

			
			$data = array();		
			$data['id'] = I('post.id');	
            $data['name'] = I('post.tname');
			$data['floor'] = I('post.floors', 0, 'int');
			$data['linenumber'] = $linenumber;//行
			$data['columnnumber'] = $columnnumber;//列
			$data['seating'] = $seating;//座位数
			$data['cameraId'] = $cameraId;//逗号分隔的Id
			$data['description'] = I('post.description');
            $data['endpointId'] = $endpointId;//终端Id,逗号分隔的多个值或单值
			
            $result = $model->save($data);
			if ($result !== FALSE) {
				//把摄像头存到终端表TB_TEndpoint的字段cameraId（多个终端保存的摄像头内容是一样的）
				if (is_array($endpointIdArr)){
					foreach($endpointIdArr as $k=>$v){
						$endpointModel->where("tid=".$v)->setField('cameraId',$cameraId);
					}
				}
				
			   $this->success('操作成功！', U('School/roomList'));
			} else {
			   $this->error('操作失败！[原因]：' . $model->getError());
			}            
        } else {
            //班级列表 START
			$banjiModel = D('SchoolBanji');
			$map = array();
			$banjis = $banjiModel->getList($map);
			$this->assign('banjis', $banjis);
			//班级列表 END	
			
			//摄像头列表 START
			$cameraModel = D('SchoolCamera');
			$camera_list = $cameraModel->order("Id DESC")->select();
			$this->assign('camera_list', $camera_list);
			//摄像头列表 END
			
			//终端列表 START
			$endpointModel = D('Endpoint');
			$endpoint_list = $endpointModel->order("tid DESC")->select();//var_dump($endpoint_list);
			$this->assign('endpoint_list', $endpoint_list);
			//终端列表 END

			
			// 终端组列表 START
			$endGroupModel = D('EndpointsGroups');
			$groups = $endGroupModel->order('level asc, id asc')->select();
			// $groups = $endGroupModel->where(array('grouptype'=>'x86'))->order('level asc, id asc')->select();
			if ($groups) {
				$treeGrps = array();
				$endGroupModel->sortNodes($treeGrps, $groups);
				$this->assign('endpointGroups', $treeGrps);//var_dump($treeGrps);
			}
			// 终端组列表 END
			
			$seltype = "radio";
			$this->assign('banjiModalSelType', $seltype);//班级弹出框中只允许单选
			
			
			$this->assign('cameraModalSelType', "checkbox");//摄像头对话框，允许复选
			
			$this->assign('endpointModalSelType', "checkbox");//终端对话框，允许复选
			
            $id = I('get.id',0,'intval');
			$model = D('SchoolRooms');
			$map['id'] = $id;
			$datas = $model->where($map)->find();

            if ($datas) {
				$cameraIds = $datas['cameraId'];
				$endpointIds = $datas['endpointId'];
				
				//把已选择的摄像头，形成一个列表显示
				if (!empty($cameraIds)){
					$camera_map = array();
					if (strpos($cameraIds, ',')){
						$camera_map['Id'] = array("IN",$cameraIds);
					}else{
						$camera_map['Id'] = array("EQ",$cameraIds);
					}
					
					$datas_camera_tmp = $cameraModel->where($camera_map)->order("Id DESC")->select();
					foreach($datas_camera_tmp as $k=>$v){
						$row = array();
						$row['Id']=$v['Id'];
						$row['name']=$v['code'];
						$cameras[] = $row;
					}
				}
				
				//把已选择的数字班牌，形成一个列表显示
				if (!empty($endpointIds)){
					$endpoint_map = array();
					if (strpos($endpointIds, ',')){
						$endpoint_map['tid'] = array("IN",$endpointIds);
					}else{
						$endpoint_map['tid'] = array("EQ",$endpointIds);
					}
					
					//$endpointModel = D('Endpoint');
					$datas_endpoint_tmp = $endpointModel->where($endpoint_map)->order("tid DESC")->select();
					foreach($datas_endpoint_tmp as $k=>$v){
						$row = array();
						$row['tid']=$v['tid'];
						$row['touchEndPointName']=$v['touchEndPointName'];
						$row['touchMainId']=$v['touchMainId'];
						$endpoints[] = $row;
					}
				}				
				
				
				foreach($groups as &$group){
					foreach($endpoint_list as $endpoint){
						if($endpoint[touchEndPoint_GroupClassId] == $group[groupclassid]){
							$group[endpoints][] = $endpoint;
						}
					}
				}
				$this->groups = $groups;
				//$this->assign("touchEndPoint_GroupClassId",$datas['touchEndPoint_GroupClassId']);//当前终端所属的终端组
				$this->assign('cameras', $cameras);//已选择的摄像头，是一个列表
				$this->assign('endpoints', $endpoints);//已选择的终端，是一个列表
				$this->assign('cameraIds', $cameraIds);//已选择的，是逗号分隔的ID
				$this->assign('datas', $datas);
				$this->display("editRoom");               
            }
        }
    }			
		
		
	/**
	 * 删除教室
	 * author:zjh
	*/
    public function delRoom() {
        /*
		$id = I('get.id', 0, 'int');
        if (!$id) {
            $this->error('非法操作！');
        }
        
        $model = D('SchoolRooms');
        $result = $model->where(array('id'=>$id))->find();
        if (!$result) {
            $this->error('非法操作，不存在该记录！');
        }
 
        // 包含子分类的父级分类不能删除
        
        $delResult = $model->where(array('id'=>$id))->delete();
        if ($delResult !== false) {
           $this->success('操作成功！', U('School/roomList'));
        } else {
           $this->error('操作失败！[原因]：' . $banjiClassModel->getError());
        }	
		*/
		
	    $ids = trim(I('post.nids'));
	
	    if (empty($ids)) {
	        die(json_encode(array('stat'=>0, 'msg'=>'请求参数错误！')));
	    }
	
	    $idsArr = explode(',', trim($ids, ','));
	    if (count($idsArr) <= 0) {
	        die(json_encode(array('stat'=>0, 'msg'=>'请求参数错误！')));
	    }
	
	    $model = D('SchoolRooms');
		$map = array();
		$map['id'] = array("IN",$ids);
		/*
		$datas = $model->where($map)->select();
		foreach($datas as $k=>$v){
			//主要是保证删除图片
			
			if (!empty($v['imagePath'])){
				$imagePath = C('UPLOAD_COMM_PATH')."/".ltrim($v['imagePath'],"/");
				@unlink($imagePath);//删除图片
			}
			
			$result = $model->where("id=".$v['id'])->delete();
		}*/
		$result = $model->where($map)->delete();

		// 将对应的课程表也删除
		$lessionTableModel = D('SchoolLessionTable');
		$lessionsModel = D('SchoolLessions');
		$lessionTableResult = $lessionTableModel->where("roomId in ($ids)")->delete();
		$lessionsResult = $lessionsModel->where("roomId in ($ids)")->delete();
		if(!$lessionTableResult){
			die(json_encode(array('stat'=>0, 'msg'=>'操作失败！[原因]：' . $lessionTableModel->getError())));
		}
		if(!$lessionsResult){
			die(json_encode(array('stat'=>0, 'msg'=>'操作失败！[原因]：' . $lessionsModel->getError())));
		}
		
	    if ($result !== false) {
	        die(json_encode(array('stat'=>1, 'msg'=>'操作成功！')));
	    } else {
	        die(json_encode(array('stat'=>0, 'msg'=>'操作失败！[原因]：' . $model->getError())));
	    }		
		
	}				
		
		
		
		
	/**
	 * 学生列表
	 *　author:zjh
	*/
	public function studentList(){
		$model = D('SchoolStudents');

		//搜索条件
		$keyboard = trim(I('get.keyboard'));
		$gradeId = I('get.gradeId');
		$banjiId = I('get.banjiId');
		$orderType = I('get.orderType');
		
		switch ($orderType)
		{
		case 'sortasc':
			$order = "sort ASC";
			$orderNext = 'sortdesc';
			break;  
		case 'sortdesc':
			$order = "sort DESC";
			$orderNext = 'sortasc';
			break;
		case 'idasc':
			$order = "id ASC";
			$orderNext = 'iddesc';
			break;
		default:{
			$order = "id DESC";
			$orderNext = "sortasc";
			}
		}
		
		$this->assign('keyboard', $keyboard);
		$this->assign('gradeId', $gradeId);
		$this->assign('banjiId', $banjiId);
		$this->assign('orderNext', $orderNext);

		$banjiModel = D('SchoolBanji');
		$map = array();
		if (!empty($keyboard)){$map['name']=array('LIKE','%'.$keyboard.'%');}//关键字

		if ($banjiId){
			$map['banjiId'] = $banjiId;
		}else{
			if (session("username") == C('ADMIN_AUTH_KEY')) {
				//超级管理员，列表中显示全部班级
			}else{
				//非超级管理员，班级列表中只显示有权限的
				$map['banjiId'] = array("IN",session('user_banji_list'));
				$where['_complex'] = $map;//复合查询
				$where['banjiId']  = array('gt',0);//复合查询
			}
		}
	
        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
        $totals = $model->where($map)->count();
        $Page = new Page($totals, 10);
        $show = $Page->show();
        $this->assign('page', $show);	

		if (session("username") == C('ADMIN_AUTH_KEY')) {
			//超级管理员，列表中显示全部班级
			$datas = $model->where($map)->order("id DESC")->limit($Page->firstRow.','.$Page->listRows)->select();
		}else{
			$datas = $model->where($map)->order("id DESC")->limit($Page->firstRow.','.$Page->listRows)->select();
		}



					
		//重新组织信息
		$gradeModel = D('SchoolGrade');
		$banjiModel = D('SchoolBanji');
		foreach ($datas as $k => $v){
			$one = $gradeModel->where("id=".$v['gradeId'])->find();
			$datas[$k]['gradeName'] = $one['name'];
			//var_dump( $one['name']);
		}
		foreach ($datas as $k => $v){
			$one = $banjiModel->where("id=".$v['banjiId'])->find();
			$datas[$k]['banjiName'] = $one['name'];
			//var_dump( $one['name']);
		}		
		
		//班级列表 START
		$banjiModel = D('SchoolBanji');
		$map = array();
		if (session("username") == C('ADMIN_AUTH_KEY')) {
			//超级管理员，列表中显示全部班级
		}else{
			//非超级管理员，班级列表中只显示有权限的
			$map['id'] = array("IN",session('user_banji_list'));
		}
		$banjis = $banjiModel->getList($map);
		$this->assign('banjis', $banjis);
		//班级列表 END
	
		$this->assign('datas', $datas);
		$this->display("School/studentList");
	}		
		
		
	/**
	 * 添加学生
	 * author:zjh
	*/
     public function addStudent() {
		
        if (IS_POST) {
			$Model = D('SchoolStudents');
            $id = I('post.id', 0, 'int');
			
			$data = array();		
		//	$data['id'] = I('post.id');	
            $data['name'] = I('post.tname');
			$data['code'] = I('post.code');//学号
			$data['birthday'] = I('post.birthday');
			$data['sex'] = I('post.sex');
			
            $data['banjiId'] = I('post.banjiId',0,"int");
			//从班级表攻取年级
				$banjiModel = D("SchoolBanji");
				$banji_datas= $banjiModel->where("id=".intval(I('post.banjiId',0,"int")))->find();
				$gradeId = $banji_datas['gradeId'];
				
			$data['gradeId'] = intval($gradeId);

			$data['image'] = I('post.photo');
			$data['imagePath'] = I('post.photoPath');

			$result = $Model->data($data)->add();

		   // 执行操作
		   if ($result !== FALSE) {
			   //$insertId = $result;
			   $this->success('操作成功！', U('School/StudentList'));
		   } else {
			   $this->error('操作失败！[原因]：' . $Model->getError());
		   }

        } else {
			
			//班级列表 START
			$banjiModel = D('SchoolBanji');
			$map = array();
			if (session("username") == C('ADMIN_AUTH_KEY')) {
				//超级管理员，列表中显示全部班级
			}else{
				//非超级管理员，班级列表中只显示有权限的
				$map['id'] = array("IN",session('user_banji_list'));
			}
			$banjis = $banjiModel->getList($map);
			$this->assign('banjis', $banjis);
			//班级列表 END
			
               
			
            // 获取
            $this->display("editStudent");
        }
		 
		
	}		
		
	/**
	 * 编缉学生	
	 * author:zjh
	*/
    public function editStudent() {        
        if (IS_POST) {
			$model = D('SchoolStudents');
            $id = I('post.id', 0, 'int');
			$name = I('post.tname',"","trim");
			$name = str_replace(' ','',$name);//所有空格
			$name = str_replace('　','',$name);//所有中文空格
			$imagepath = I('post.photoPath',"","trim");
		//	import('ORG.Util.String');
		
			if ($id){
				$old = $model->where("id=".$id)->find();
				$oldImgField = trim($old['imagePath']);
				$oldImagePath = C('UPLOAD_COMM_PATH')."/".ltrim($old['imagePath'],"/");
				
				$uploadRootPath = rtrim(C('UPLOAD_COMM_PATH'), '/') . '/';
				if ($oldImgField != '' && is_file($oldImagePath)) {
					//原图片字段不为空，并且图片文件存在//新图片与旧图片地址不同时才删除旧图
					if (!empty($imagepath) && $imagepath != $oldImgField){
						@unlink($oldImagePath);//删除旧图
					}
				 }
			}
		
			$data = array();		
			$data['id'] = I('post.id');	
            $data['name'] = $name;
			$data['code'] = I('post.code');//学号
			$data['birthday'] = I('post.birthday');
			$data['sex'] = I('post.sex');
			
            $data['banjiId'] = I('post.banjiId',0,"int");
			//从班级表攻取年级
				$banjiModel = D("SchoolBanji");
				$banji_datas= $banjiModel->where("id=".intval(I('post.banjiId',0,"int")))->find();
				$gradeId = $banji_datas['gradeId'];
				
			$data['gradeId'] = intval($gradeId);
			$data['imagePath'] = I('post.photoPath');
            
            $result = $model->save($data);
			
			//删除旧图
			
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/StudentList'));
			} else {
			   $this->error('操作失败！[原因]：' . $model->getError());
			}            
        } else {
            $id = I('get.id',0,'intval');
			$model = D('SchoolStudents');
			$map['id'] = $id;
			$datas = $model->where($map)->find();
			if (!$datas){
				 $this->error('无此记录');
			}
			
			//班级列表 START
			$banjiModel = D('SchoolBanji');
			$map = array();
			if (session("username") == C('ADMIN_AUTH_KEY')) {
				//超级管理员，列表中显示全部班级
			}else{
				//非超级管理员，班级列表中只显示有权限的
				$map['id'] = array("IN",session('user_banji_list'));
			}
			$banjis = $banjiModel->getList($map);
			$this->assign('banjis', $banjis);
			//班级列表 END
			
			$datas[birthday] = (array)$datas[birthday];
			$datas[birthday] = $datas[birthday] ? substr($datas[birthday][date], 0, 10) : '';
			
            if ($datas) {
				$this->assign('datas', $datas);
				$this->display("editStudent");               
            }
        }
    }			
		
		
	/**
	 * 删除学生
	 * author:zjh
	*/
    public function delStudent() {
		/*
        $id = I('get.id', 0, 'int');
        if (!$id) {
            $this->error('非法操作！');
        }
        
        $model = D('SchoolStudents');
        $result = $model->where(array('id'=>$id))->find();
        if (!$result) {
            $this->error('非法操作，不存在该记录！');
        }
 
        // 删除图片
        
        $delResult = $model->where(array('id'=>$id))->delete();
        if ($delResult !== false) {
           $this->success('操作成功！', U('School/studentList'));
        } else {
           $this->error('操作失败！[原因]：' . $banjiClassModel->getError());
        }	*/
		
	    $ids = trim(I('post.nids'));
	
	    if (empty($ids)) {
	        die(json_encode(array('stat'=>0, '请求参数错误！')));
	    }
	
	    $idsArr = explode(',', trim($ids, ','));
	    if (count($idsArr) <= 0) {
	        die(json_encode(array('stat'=>0, '请求参数错误！')));
	    }
	
	    $model = D('SchoolStudents');
		$map = array();
		$map['id'] = array("IN",$ids);
		$datas = $model->where($map)->select();
		foreach($datas as $k=>$v){
			//主要是保证删除图片
			if (!empty($v['imagePath'])){
				$imagePath = C('UPLOAD_COMM_PATH')."/".ltrim($v['imagePath'],"/");
				@unlink($imagePath);//删除图片
			}
			
			//@unlink(ltrim('Uploads/school/6f439e9a-8e02-3a73-6e30-0ba70b0e3df6.jpg',"/"));
			$result = $model->where("id=".$v['id'])->delete();
		}		
		
	    if ($resResult !== false) {
	        die(json_encode(array('stat'=>1, '操作成功！')));
	    } else {
	        die(json_encode(array('stat'=>0, '操作失败！[原因]：' . $resModel->getError())));
	    }		
		
		
	}			
		
		
		
	/**
	 * 卡片列表
	 *　author:zjh
	*/
	public function cardList(){
		$model = D('SchoolCards');

		//搜索条件
		$keyboard = trim(I('get.keyboard'));
		$keytype = I('get.keytype');//关键字：卡号？内码？
		if (empty($keytype)){$keytype="code";}
		$this->assign("keytype",$keytype);
		/*
		$orderType = I('get.orderType');
		
		switch ($orderType)
		{
		case 'sortasc':
			$order = "sort ASC";
			$orderNext = 'sortdesc';
			break;  
		case 'sortdesc':
			$order = "sort DESC";
			$orderNext = 'sortasc';
			break;
		case 'idasc':
			$order = "id ASC";
			$orderNext = 'iddesc';
			break;
		default:{
			$order = "id DESC";
			$orderNext = "sortasc";
			}
		}
		$this->assign('orderNext', $orderNext);
		*/
		
		$this->assign('keyboard', $keyboard);
		
		
		$map = array();
		$map['id'] = array('GT',0);
		switch ($keytype){
			case 'code':
				//echo "卡号";
				if ($keyboard){$map['code']=array('LIKE','%'.$keyboard.'%');}
				break;
			case 'num':
				//echo "内码";
				if ($keyboard){$map['num']=array('LIKE','%'.$keyboard.'%');}
				break;
			default:
			{
				//默认为搜索卡号
				if ($keyboard){$map['code']=array('LIKE','%'.$keyboard.'%');}
			}
				
		}
		
		if ($gradeId){$map['gradeId']=array('LIKE','%'.$gradeId.'%');}//if ($gradeId){$map['gradeId']=array('EQ',$gradeId);}

	
        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
        $totals = $model->where($map)->count();
        $Page = new Page($totals, 10);
        $show = $Page->show();
        $this->assign('page', $show);	
		
		$datas = $model->where($map)->order("id DESC")->limit($Page->firstRow.','.$Page->listRows)->select(); //->field("id,code,userId,type,createTime,isLocked,sort,num,cardtype,convert(VARCHAR(24),outTime,120) as outTime")
		
		//重新组织信息
		$studentModel = D('SchoolStudents');
		$teachersModel = D('SchoolTeachers');
		foreach ($datas as $k => $v){
			if($v['type'] == 1){//教师
				$one = $teachersModel->where("id=".$v['userId'])->find();
				$datas[$k]['userName'] = $one['name'];
				$datas[$k]['type_cn'] = "教师";
			}
			
			if($v['type'] == 2){//学生
				$one = $studentModel->where("id=".$v['userId'])->find();
				$datas[$k]['userName'] = $one['name'];
				$datas[$k]['type_cn'] = "学生";
			}		
			
			switch ($v['type']) {
				case 1:
					break;
				case 2:
					break;
				default:
					;
			}

			//var_dump( $one['name']);
		}

		//班级列表 START
		$banjiModel = D('SchoolBanji');
		$map = array();
		$banjis = $banjiModel->getList($map);
		$this->assign('banjis', $banjis);
		//班级列表 END			
		
		$this->assign('datas', $datas);
		$this->display("School/cardList");
	}		
		
		
	/**
	 * 添加卡片
	 * author:zjh
	*/
     public function addCard() {
		
        if (IS_POST) {
			$model = D('SchoolCards');
            $id = I('post.id', 0, 'int');
		
			//检测重复
			$map = array();
			//$map['id']  = array('NEQ',$id);//不等于当前卡
			$map['num'] = trim(I('post.num'));
			$result = $model->where($map)->field('id')->find(); 
			
			//var_dump($map);exit;
			if ($result){
				$this->error('操作失败！[原因]：此学生已发卡，如需重新发卡请锁定前卡' . $model->getError());
			}
		
		
			$data = array();		
			//$data['id'] = I('post.id');	
            $data['code'] = trim(I('post.code'));
			$data['num'] = trim(I('post.num'));
			$data['cardtype'] = trim(I('post.cardtype'));
			$data['isLocked'] = I('post.isLocked', 0, 'int');
			
			$result = $model->data($data)->add();

		   // 执行操作
		   if ($result !== FALSE) {
			   //$insertId = $result;
			   $this->success('操作成功！', U('School/cardList'));
		   } else {
			   $this->error('操作失败！[原因]：' . $Model->getError());
		   }

        } else {
            
			$seltype = "radio";
			$this->assign('studentModalSelType', $seltype);//学生弹出框中只允许单选
			
			$seltype = "radio";
			$this->assign('teacherModalSelType', $seltype);//教师选择弹出框中只允许单选
			
            //学生列表 START
			$stuModel = D('SchoolStudents');
			$map = array();
			$students = $stuModel->getList($map);
			$this->assign('students', $students);
			//学生列表 END	
			
            //教师列表 START
			$teachersModel = D('SchoolTeachers');
			$map = array();
			$teachers = $teachersModel->getList($map);
			$this->assign('teachers', $teachers);
			//列表 END	
			
            // 获取
            $this->display("editCard");
        }
		 
		
	}		
		
	/**
	 * 编缉卡片	
	 * author:zjh
	*/
    public function editCard() {
        
        if (IS_POST) {
			$model = D('SchoolCards');
            $id = I('post.id', 0, 'int');
			
			/*
			$type = I('post.types', 0, 'int');
			
			switch ($type){
				case 2:{
					$studentIdArr=I('post.studentIdStr');			
					$studentId = intval(implode(',',$studentIdArr));//弹窗中设为单选，此处收到的只是一个数值
					$userId = $studentId;
					break;
					}
				case 1:
					{
					$teacherIdArr=I('post.teacherIdStr');
					$teacherId = intval(implode(',',$teacherIdArr));//弹窗中设为单选，此处收到的只是一个数值
					$userId = $teacherId;
					break;		
					}
				default:
					//error
			}
			*/
			//检测重复
			$map = array();
			$map['id']  = array('NEQ',$id);//不等于当前卡
			$map['num'] = trim(I('post.num'));
			$map['isLocked'] = 0;
			$result = $model->where($map)->field('id')->find(); 
			
			//var_dump($map);exit;
			if ($result){
				$this->error('操作失败！[原因]内码重复' . $model->getError());
			}
		
		
			$data = array();		
			$data['id'] = I('post.id');	
            $data['code'] = trim(I('post.code'));
			$data['num'] = trim(I('post.num'));
			$data['cardtype'] = I('post.cardtype');
			$data['isLocked'] = I('post.isLocked', 0, 'int');
            
            $result = $model->save($data);
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/cardList'));
			} else {
			   $this->error('操作失败！[原因]：' . $model->getError());
			}            
        } else {
            $id = I('get.id',0,'intval');
			$model = D('SchoolCards');
			$map['id'] = $id;
			$datas = $model->where($map)->find();//->field("id,name,code,userId,type,createTime,isLocked,sort,num,cardtype,convert(VARCHAR(24),outTime,120) as outTime")
	
            if ($datas) {
				$this->assign('datas', $datas);
				$this->display("editCard");               
            }
        }
    }			
		
		
	/**
	 * 删除卡片
	 * author:zjh
	*/
    public function delCard() {
        $id = I('get.id', 0, 'int');
        if (!$id) {
            $this->error('非法操作！');
        }
        
        $model = D('SchoolCards');
        $result = $model->where(array('id'=>$id))->find();
        if (!$result) {
            $this->error('非法操作，不存在该记录！');
        }
 
        // 包含子分类的父级分类不能删除
        
        $delResult = $model->where(array('id'=>$id))->delete();
        if ($delResult !== false) {
           $this->success('操作成功！', U('School/cardList'));
        } else {
           $this->error('操作失败！[原因]：' . $banjiClassModel->getError());
        }	
	}				
		
		
		
	/**
	 * 课程表列表
	 * admin直接显示所有TB_Sch_Lession_Table表中所有记录
	 * 非admin则根据session中的可管理班级来找到对应的TB_Sch_Lession_Table表中指定过教室的记录
	 * author: lym
	 */
	public function lessionTableList(){
		$allBanji = D('SchoolBanji')->select();
		$allRoom = D('SchoolRooms')->select();
		$allLessionTable = D('SchoolLessionTable')->order('id desc')->select();
		if(session("username") == C('ADMIN_AUTH_KEY')){
			foreach($allLessionTable as &$lessionTable){
				foreach($allRoom as $room){
					if($lessionTable[roomId] == $room[id]){
						$lessionTable[roomName] = $room[name];
					}
				}
				foreach($allBanji as $banji){
					if($lessionTable[roomId] == $banji[roomId]){
						$lessionTable[banjiId] = $banji[id];
						$lessionTable[banjiName] = $banji[name];
					}
				}
				$lessionTable[lessionNum] = D('SchoolLessions')->where("subjectId > 0 and tbId = ".$lessionTable[id])->count();
			}
			$data = $allLessionTable;
		} else {
			$user_banji_str = session("user_banji_list");
			foreach($allBanji as $banji){
				if(in_array($banji[id], explode(',', $user_banji_str))){
					$banjiOfCurrentUser[] = $banji;
				}
			}
			foreach($banjiOfCurrentUser as &$theBanji){
				foreach($allRoom as $theRoom){
					if($theBanji[roomId] == $theRoom[id]){
						$theBanji[roomName] = $theRoom[name];
					}
				}
			}
			$lessionTables = array();
			foreach($allLessionTable as $theLessionTable){
				foreach($banjiOfCurrentUser as $thatBanji){
					if($thatBanji[roomId] == $theLessionTable[roomId]){
						$currentLessionTable[id] = $theLessionTable[id];
						$currentLessionTable[banjiId] = $thatBanji[id];
						$currentLessionTable[banjiName] = $thatBanji[name];
						$currentLessionTable[roomName] = $thatBanji[roomName];
						$currentLessionTable[roomId] = $theLessionTable[roomId];
						$currentLessionTable[lessionNum] = D('SchoolLessions')->where("subjectId > 0 and tbId = ".$theLessionTable[id])->count();
						array_push($lessionTables, $currentLessionTable);
					}
				}
			}
			$data = $lessionTables;
		}
        import('ORG.Util.Page');
        $totals = count($data);
        $Page = new Page($totals, 10);
        $this->page = $Page->show();
		$this->data = array_slice($data, $Page->firstRow, $Page->listRows);
		$this->display("School/lessionTableList");
	}		
		
		
	/**
	 * 编缉课程表	
	 * author:zjh
	*/
    public function editLessionTable() {
        if (IS_POST) {
			//只更新备注，不允许修改班级
			$id = I('post.id', 0, 'int');
			$content = I('post.tblContent');
			
			$model = D('SchoolLessionTable');
			
			if (!$id){
				echo json_encode(array("stat"=>"0","msg"=>"id无效","data"=>""));exit;
			}else{
				
				$result = $model->where("id=".$id)->find();
				if (!$result){
					echo json_encode(array("stat"=>"0","msg"=>"无此课程表","data"=>""));exit;
				}
			}
			
			$data = array();		
			$data['id'] = $id;	
            $data['description'] = $content;			
			
			$result = $model->save($data);
			if ($result){
				echo json_encode(array("stat"=>"1","msg"=>"更新成功","data"=>""));exit;	
			}else{
				echo json_encode(array("stat"=>"0","msg"=>"更新失败","data"=>""));exit;
			}
			
			/*
			$model = D('SchoolLessionTable');
            $id = I('post.id', 0, 'int');
		
			$roomModel = D('SchoolRooms');
			
			$zb = I("request.zb",0,"int");
			//如果是正常模式，需要从班级Id取到roomId
			if ($zb){
				//走班模式
				$roomId = 	I("request.roomId",0,"int");
				//暂时不考虑走班模式
				//缺权限验证
				
				
			}else{

				//正常模式
				$banjiId = 	I("request.banjiId",0,"int");
				$this->check_banji($banjiId);//是否可操作此班级ID
				
				if (!$banjiId){$this->error("班级未指定");}
				$map_room = array();
				$map_room['banjiId'] = $banjiId;
				$datas_room = $roomModel->where($map_room)->find();
				if (!$datas_room){
					$this->error("班级未指定教室");
				}else{
					$roomId = $datas_room['id'];	
				}
			}
			if (!$roomId){$this->error("教室未指定");}
		
		
			$data = array();		
			$data['id'] = I('post.id', 0, 'int');	
            $data['name'] = I('post.name');
			$data['zb'] = $zb;
			$data['roomId'] = $roomId;
			$data['description'] = I('post.description');

            
            $result = $model->save($data);
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/lessionTableList'));
			} else {
			   $this->error('操作失败！[原因]：' . $model->getError());
			}            
			*/
        } else {
			$id = I('get.lessionTableId',0,'intval');//记录的自增id
			$roomId = I('get.roomId');
			if (!$id){
				$this->error('无此课程表，请先为本教室创建一个课程表','/School/lessionTableList',2);
			}
			$tbId = $id;
			
			$model = D('SchoolLessionTable');
			$roomModel = D('SchoolRooms');
			$banjiModel = D('SchoolBanji');
			$subjectsModel = D('SchoolSubjects');
			$teachersModel = D('SchoolTeachers');
			
			$map['id'] = $id;
			$datas = $model->where($map)->find();
			$this->assign("id",$datas['id']);
			
			$datas_banji = $banjiModel->getBanjiOneRowFromRoomId(intval($datas['roomId']));//行3434333
			$datas_room = $roomModel->where("id=".$datas['roomId'])->find();
			//$banjiId  = $this->getBanjiIdFromRoomId(intval($datas['roomId']));//这个方法因为需要this指针，被放弃，用行3434333的方法
			$this->assign("datas_banji",$datas_banji);
			$this->assign("banjiName",$datas_banji['name']);
			$this->assign("roomName",$datas_room['name']);
			$banjiId  = $datas_banji['id'];
			$datas['banjiId'] = $banjiId;
			$this->check_banji($banjiId);//是否可操作此班级ID
			
			$btsModel = D("SchoolBanjiSubjectTeacher");
			$btsRelation = $btsModel->where("roomId=$roomId")->order('id desc')->select();
			$subjectsChoose = array();
			foreach($btsRelation as $bts){
				$temp[id] = $bts[id];
				$temp[subjectName] = $subjectsModel->where("id=$bts[subjectId]")->getField('name');
				$temp[teacherName] = $teachersModel->where("id=$bts[teacherId]")->getField('name');
				$temp[banjiName] = $teachersModel->where("id=$bts[banjiId]")->getField('name');
				$subjectsChoose[] = $temp;
			}
			$this->subjectsChoose = $subjectsChoose;
			
			$gradeModel = D('SchoolGrade');
			$map = array();
			$grades = $gradeModel->getList($map);
			$this->assign('grades', $grades);
			//年级列表 END
				

			
            //班级列表 START
			$banjiModel = D('SchoolBanji');
			$map_bj = array();
			
			if(session("username") == C('ADMIN_AUTH_KEY')){
				//超级管理员可显示所有班级
			} else {
				//获取到可管理班级
				$user_banji_str = session("user_banji_list");//$map['banjiId'] = array("IN",$user_banji_str);
				$map_bj['id'] = array("IN",$user_banji_str);
			}
			
			$banjis = $banjiModel->getList($map_bj);
			if ($banjis){
				foreach($banjis as $k=>$v){
					$map_room = array();
					$map_room['banjiId'] = $v['id'];
					$datas_room = $roomModel->where($map_room)->find();
					if ($datas_room){
						$banjis[$k]['room'] = $datas_room['name'];
						$banjis[$k]['roomId'] = $datas_room['id'];
					} else {
						$banjis[$k]['room'] = '无教室';
						$banjis[$k]['roomId'] = 0;
					}
				}
			}
			$this->assign('banjis', $banjis);
			//班级列表 END		
			
			
			
            //教室列表 START
			$roomModel = D('SchoolRooms');
			$map = array();
			$rooms = $roomModel->select();
			$this->assign('rooms', $rooms);//var_dump($rooms);
			//教室列表 END	
			
			//科目列表 START
			$subjectsModel = D('SchoolSubjects');
			$map = array();
			$subjects = $subjectsModel->getList($map);
			$this->assign('subjects', $subjects);
			//列表 END	
			
            //教师列表 START
			$teachersModel = D('SchoolTeachers');
			$map = array();
			$teachers = $teachersModel->getList($map);
			$this->assign('teachers', $teachers);
			//列表 END	
			
            if ($datas) {				
				$this->assign("banjiId",$banjiId);
				$this->assign('datas', $datas);
				              
            }
			
			//var_dump($id);
			
			//显示课程表各单元格	
			$schoolLessionModel = D('SchoolLessions');		
			$map = array();
			//$map['gradeId'] = $gradeId;
			$map['tbId'] = $tbId;
			//$map['postion'] = array('IN','1,2,3,4,5');
			$datas_1 = $schoolLessionModel->where(" ((postion='1-1') or (postion='1-2') or (postion='1-3') or (postion='1-4') or (postion='1-5') or (postion='1-6') or (postion='1-7')) and tbId = ".$tbId)->select(); //var_dump($datas_1);
			$datas_2 = $schoolLessionModel->where(" ((postion='2-1') or (postion='2-2') or (postion='2-3') or (postion='2-4') or (postion='2-5') or (postion='2-6') or (postion='2-7')) and tbId = ".$tbId)->select(); //var_dump($datas_2);
			$datas_3 = $schoolLessionModel->where(" ((postion='3-1') or (postion='3-2') or (postion='3-3') or (postion='3-4') or (postion='3-5') or (postion='3-6') or (postion='3-7')) and tbId = ".$tbId)->select();
			$datas_4 = $schoolLessionModel->where(" ((postion='4-1') or (postion='4-2') or (postion='4-3') or (postion='4-4') or (postion='4-5') or (postion='4-6') or (postion='4-7')) and tbId = ".$tbId)->select();
			$datas_5 = $schoolLessionModel->where(" ((postion='5-1') or (postion='5-2') or (postion='5-3') or (postion='5-4') or (postion='5-5') or (postion='5-6') or (postion='5-7')) and tbId = ".$tbId)->select();
			$datas_6 = $schoolLessionModel->where(" ((postion='6-1') or (postion='6-2') or (postion='6-3') or (postion='6-4') or (postion='6-5') or (postion='6-6') or (postion='6-7')) and tbId = ".$tbId)->select();
			$datas_7 = $schoolLessionModel->where(" ((postion='7-1') or (postion='7-2') or (postion='7-3') or (postion='7-4') or (postion='7-5') or (postion='7-6') or (postion='7-7')) and tbId = ".$tbId)->select();
			$datas_8 = $schoolLessionModel->where(" ((postion='8-1') or (postion='8-2') or (postion='8-3') or (postion='8-4') or (postion='8-5') or (postion='8-6') or (postion='8-7')) and tbId = ".$tbId)->select();
			
			
			$subjectModel = D('SchoolSubjects');
            if ($datas_1) {
				$row1 = array();
				foreach ($datas_1 as $k => $v){
					//如果subjectId不为0，则查询其科目中文名
					if ($v['subjectId']){
						$t_datas = $subjectModel->where("id=".$v['subjectId'])->field("name")->find();
						$subjectName = $t_datas['name'];
						
					} else {
						$subjectName = "";//课程未设置
					}
					//$row1[] = array("name"=>$subjectName,"id"=>$v['id']);//$v['name'];	
					
					if ($v['teacherId']){
						$teach_datas = array();
						$teach_datas = $teachersModel->where("id=".$v['teacherId'])->field("name")->find();
						$teacherName = $teach_datas['name'];
						
					} else {
						$teacherName = "";//课程未设置
					}
					$row1[] = array("name"=>$subjectName,"teacherName"=>$teacherName,"id"=>$v['id']);//$v['name'];
					
								
				}//第1行放在一维数组中
				//var_dump($datas_1);
			}
            if ($datas_2) {
				foreach ($datas_2 as $k => $v){
					//如果subjectId不为0，则查询其科目中文名
					if ($v['subjectId']){
						$t_datas = $subjectModel->where("id=".$v['subjectId'])->field("name")->find();
						$subjectName = $t_datas['name'];
					} else {
						$subjectName = "";//"课程未设置";
					}
					if ($v['teacherId']){
						$teach_datas = $teachersModel->where("id=".$v['teacherId'])->field("name")->find();
						$teacherName = $teach_datas['name'];
						
					} else {
						$teacherName = "";//课程未设置
					}
					$row2[] = array("name"=>$subjectName,"teacherName"=>$teacherName,"id"=>$v['id']);//$v['name'];			
				}//第2行放在一维数组中
				//var_dump($row2);
			}
            if ($datas_3) {
				foreach ($datas_3 as $k => $v){
					//如果subjectId不为0，则查询其科目中文名
					if ($v['subjectId']){
						$t_datas = $subjectModel->where("id=".$v['subjectId'])->field("name")->find();
						$subjectName = $t_datas['name'];
					} else {
						$subjectName = "";//"课程未设置";
					}
					if ($v['teacherId']){
						$teach_datas = $teachersModel->where("id=".$v['teacherId'])->field("name")->find();
						$teacherName = $teach_datas['name'];
						
					} else {
						$teacherName = "";//课程未设置
					}
					$row3[] = array("name"=>$subjectName,"teacherName"=>$teacherName,"id"=>$v['id']);//$v['name'];
								
				}//第3行放在一维数组中
				//var_dump($row3);
			}		
            if ($datas_4) {
				foreach ($datas_4 as $k => $v){
					//如果subjectId不为0，则查询其科目中文名
					if ($v['subjectId']){
						$t_datas = $subjectModel->where("id=".$v['subjectId'])->field("name")->find();
						$subjectName = $t_datas['name'];
					} else {
						$subjectName = "";//"课程未设置";
					}
					if ($v['teacherId']){
						$teach_datas = $teachersModel->where("id=".$v['teacherId'])->field("name")->find();
						$teacherName = $teach_datas['name'];
						
					} else {
						$teacherName = "";//课程未设置
					}
					$row4[] = array("name"=>$subjectName,"teacherName"=>$teacherName,"id"=>$v['id']);//$v['name'];				
				}//第4行放在一维数组中
				//var_dump($row4);
			}					
			
            if ($datas_5) {
				foreach ($datas_5 as $k => $v){
					//如果subjectId不为0，则查询其科目中文名
					if ($v['subjectId']){
						$t_datas = $subjectModel->where("id=".$v['subjectId'])->field("name")->find();
						$subjectName = $t_datas['name'];
					} else {
						$subjectName = "";//"课程未设置";
					}
					if ($v['teacherId']){
						$teach_datas = $teachersModel->where("id=".$v['teacherId'])->field("name")->find();
						$teacherName = $teach_datas['name'];
						
					} else {
						$teacherName = "";//课程未设置
					}
					$row5[] = array("name"=>$subjectName,"teacherName"=>$teacherName,"id"=>$v['id']);//$v['name'];			
				}//第5行放在一维数组中
				//var_dump($row5);
			}					
            if ($datas_6) {
				foreach ($datas_6 as $k => $v){
					//如果subjectId不为0，则查询其科目中文名
					if ($v['subjectId']){
						$t_datas = $subjectModel->where("id=".$v['subjectId'])->field("name")->find();
						$subjectName = $t_datas['name'];
					} else {
						$subjectName = "";//"课程未设置";
					}
					if ($v['teacherId']){
						$teach_datas = $teachersModel->where("id=".$v['teacherId'])->field("name")->find();
						$teacherName = $teach_datas['name'];
						
					} else {
						$teacherName = "";//课程未设置
					}
					$row6[] = array("name"=>$subjectName,"teacherName"=>$teacherName,"id"=>$v['id']);//$v['name'];				
				}//第6行放在一维数组中
				//var_dump($row6);
			}					
            if ($datas_7) {
				foreach ($datas_7 as $k => $v){
					//如果subjectId不为0，则查询其科目中文名
					if ($v['subjectId']){
						$t_datas = $subjectModel->where("id=".$v['subjectId'])->field("name")->find();
						$subjectName = $t_datas['name'];
					} else {
						$subjectName = "";//"课程未设置";
					}
					if ($v['teacherId']){
						$teach_datas = $teachersModel->where("id=".$v['teacherId'])->field("name")->find();
						$teacherName = $teach_datas['name'];
						
					} else {
						$teacherName = "";//课程未设置
					}
					$row7[] = array("name"=>$subjectName,"teacherName"=>$teacherName,"id"=>$v['id']);//$v['name'];				
				}//第7行放在一维数组中
				//var_dump($row7);
			}					
            if ($datas_8) {
				foreach ($datas_8 as $k => $v){
					//如果subjectId不为0，则查询其科目中文名
					if ($v['subjectId']){
						$t_datas = $subjectModel->where("id=".$v['subjectId'])->field("name")->find();
						$subjectName = $t_datas['name'];
					} else {
						$subjectName = "";//"课程未设置";
					}
					if ($v['teacherId']){
						$teach_datas = $teachersModel->where("id=".$v['teacherId'])->field("name")->find();
						$teacherName = $teach_datas['name'];
						
					} else {
						$teacherName = "";//课程未设置
					}
					$row8[] = array("name"=>$subjectName,"teacherName"=>$teacherName,"id"=>$v['id']);//$v['name'];				
				}//第8行放在一维数组中
				//var_dump($row8);
			}					
						
			$lessTable = array();
			$lessTable[] = $row1;
			$lessTable[] = $row2;
			$lessTable[] = $row3;
			$lessTable[] = $row4;
			$lessTable[] = $row5;
			$lessTable[] = $row6;
			$lessTable[] = $row7;
			$lessTable[] = $row8;
			header("Content-type: text/html; charset=utf-8");
			$this->assign('lessTable', $lessTable);			
			
			$this->display("editLessionTable"); 
        }
    }
		
	/**
	 * 添加课程表	
	 * author:zjh
	*/
    public function addLessionTable($roomId=0, $addRoom=0) {
        if (IS_POST) {
			$model = D('SchoolLessionTable');
			$roomModel = D('SchoolRooms');
			/*
			$zb = I("request.zb",0,"int");
			//如果是正常模式，需要从班级Id取到roomId
			if ($zb){
				//走班模式
				$roomId = 	I("request.roomId",0,"int");
			}else{
				//正常模式
				$roomId = 	I("request.roomId",0,"int");
				//$banjiId = 	I("request.banjiId",0,"int");
				//if (!$banjiId){$this->error("班级未指定");}
				
				//$banjiId = $this->getBanjiIdFromRoomId($roomId);
				//$this->check_banji($banjiId);//是否可操作此班级ID
				//var_dump($banjiId);
			}*/


			if(!$roomId){
				$roomId = I("request.tblRoomId");
			}
			if(!$addRoom){
				if (!$roomId){
					die(json_encode(array("stat"=>"0","msg"=>"新增课程表失败","data"=>"")));
				}
			}
			$description = trim(I('post.tblContent'));
			/*
			 * 防止为同一个教室创建重复课程表
			*/
		//	$result = $model->where("roomId=".$roomId)->find();
			
			$ltbCount = $model->where("roomId=".$roomId)->count();
			if(!$addRoom){
				if ($ltbCount){
					die(json_encode(array("stat"=>"0","msg"=>"每个教室只能有一张课程表","data"=>"")));
				}			
			}
			
		
			$data = array();		
		//	$data['id'] = I('post.id', 0, 'int');	
            $data['name'] = I('post.name');
			// $data['zb'] = $zb;
			$data['roomId'] = $roomId;
			$data['description'] = I('post.tblContent');
            
            $result = $model->data($data)->add();
			if ($result){
				$tbId = $result;	
			}else{
				//失败，提示创建失败，后面不再执行	
			}
			
			/*
			 * 添加成功后，立即创建课程表内容
			*/
			
				
				$schoolLessionModel = D('SchoolLessions');
				$ntow = array('1'=>'一','2'=>'二','3'=>'三','4'=>'四','5'=>'五','6'=>'六','7'=>'七','8'=>'八');
				
				//星期一至星期七
				for ($x=1; $x<=7; $x++) {				
					
					//一天的8节课
					for ($y=1; $y<=8; $y++) {
						$data = array();
						$data['tbId'] = $tbId;
						$pos = $y.'-'.$x;
						$data['postion'] = $pos;//格式：第几节课-星期几
					//	$data['name'] = '';
						$data['roomId'] = $roomId;
						$data['lessionNumber'] = $y;//第几节课：1,2,3,4,5,6,7,8
					//	$data['startTime'] = $startTimeArr[$y];
					//	$data['endTime'] = $endTimeArr[$y];
						
						$data['leftName'] = "第".$ntow[$y]."节";//第几节课中文
						$data['topName'] = '星期'.$ntow[$x];//星期几
						$data['weekday'] = $x;//数字,星期几
						$result = $schoolLessionModel->data($data)->add();
					} 
				}		
		
			//创建课程表结束			
			
			// $out = array();
			//echo json_encode(array("stat"=>"1","data"=>'创建课程表成功，请立即去设置课程表内容'));exit;
			if(!$addRoom){
				die(json_encode(array("stat"=>"1","data"=>$tbId,"msg"=>"创建课程表成功，请立即去设置课程表内容")));
			}
 
			          
        } else {
			/*
			//判断是否走班
			$zb = I("request.zb",0,"int");
			$this->assign("zb",$zb);
			
			$roomModel = D('SchoolRooms');
			if ($zb){
				//教室列表 START
				
				$map = array();
				$rooms = $roomModel->select();
				$this->assign('rooms', $rooms);//var_dump($rooms);
				//教室列表 END	
			} else{
				//班级列表 START
				$banjiModel = D('SchoolBanji');
				$map_bj = array();
				
				if(session("username") == C('ADMIN_AUTH_KEY')){
					//超级管理员可显示所有班级
				} else {
					//获取到可管理班级
					$user_banji_str = session("user_banji_list");//$map['banjiId'] = array("IN",$user_banji_str);
					$map_bj['id'] = array("IN",$user_banji_str);
				}
				//var_dump($map_bj);
				$banjis = $banjiModel->getList($map_bj);
				if ($banjis){
					foreach($banjis as $k=>$v){
						$map_room = array();
						$map_room['banjiId'] = $v['id'];
						$datas_room = $roomModel->where($map_room)->find();
						if ($datas_room){
							$banjis[$k]['room'] = $datas_room['name'];
							$banjis[$k]['roomId'] = $datas_room['id'];
						} else {
							$banjis[$k]['room'] = '无教室';
							$banjis[$k]['roomId'] = 0;
						}
					}
				}
				$this->assign('banjis', $banjis);
				//班级列表 EN	

			}
			
			$this->assign('datas', $datas);
			$this->display("editLessionTable");       
			*/        
        }
    }
	
	/**
	 * 删除课程表
	*/
	public function delLessionTable(){
        $id = I('get.id', 0, 'int');
        if (!$id) {
            $this->error('非法操作！');
        }
        
        $model = D('SchoolLessionTable');
        $result = $model->where(array('id'=>$id))->find();
        if (!$result) {
            $this->error('非法操作，不存在该记录！');
			$roomId = 0;
        }else{
			$roomId = intval($result['roomId']);
		}
		//获取班级ID
		$banjiId = $this->getBanjiIdFromRoomId($roomId);
		$this->check_banji($banjiId);//是否可操作此班级ID

 		//删除课程
 		$schoolLessionModel = D('SchoolLessions');
		$delResult = $schoolLessionModel->where("tbId=".$id)->delete();
		//var_dump($delResult);
		//if ($delResult){//课程有可能未生成，会返回0
			//删除课程表
			$delResult = $model->where("id=".$id)->delete();
			if ($delResult !== false) {
			   $this->success('操作成功！', U('School/lessionTableList'));
			} else {
			   $this->error('操作失败！[原因]：' . $banjiClassModel->getError());
			}
		//}
        
			
	}
	
	/*
	 * 保存教室科目(教师)对应关系，因为课程表要以教室为主导 by lym
	 */
	public function saveBanjiSubjectTeacher(){
		//获取到课程表ID
		$tbId = I("post.tbId",0,"int");
		$doType = trim(I("post.doType"));
		$currentId = I("post.currentId",0,"int");
		$teacherId = I("post.teacherId",0,"int");
		$subjectId = I("post.subjectId",0,"int");
		$roomId = I('post.roomId');
		
		//检测参数
		if (!in_array($doType, array("add","edit"))){
			die(json_encode(array("stat"=>"0","data"=>$doType,"msg"=>'操作类型错误')));
		}
		// if (!$teacherId){
		// 	echo json_encode(array("stat"=>"0","data"=>$doType,"msg"=>'教师未设置'));exit;
		// }
		if (!$subjectId){
			die(json_encode(array("stat"=>"0","data"=>$doType,"msg"=>'科目未设置')));
		}
		
		//获取到roomId
		$ltModel = D('SchoolLessionTable');
		$datas_ls = $ltModel->where("id=".$tbId)->find();
		if (!$datas_ls){
			die(json_encode(array("stat"=>"0","data"=>$doType,"msg"=>'未找到此课程表')));
		}else{
			$roomId = $datas_ls['roomId'];
		}
		
		if (!$roomId){
			die(json_encode(array("stat"=>"0","msg"=>'教室未设置')));
		}
		
		//$roomModel = D('SchoolRooms');
		// 若绑定过教室，则查出班级
		$banjiId = D('SchoolBanji')->where("roomId=$roomId")->getField('id');
		// $datas_banji = $banjiModel->getBanjiOneRowFromRoomId($roomId);
		// $banjiId = $datas_banji['id'];
		// if (!$banjiId){
		// 	echo json_encode(array("stat"=>"0","data"=>$banjiId,"msg"=>'教室未对应班级'));exit;
		// }
		
		// 编辑or新增教室、科目(、班级、教师)关系
		$btsModel = D("SchoolBanjiSubjectTeacher");
		//判断新增还是更新，存储
		$data = array();
		switch ($doType){
			case "add":
				$data['roomId'] = $roomId;
				$data['subjectId'] = $subjectId;
				$data['banjiId'] = $banjiId;
				$data['teacherId'] = $teacherId;
				$result = $btsModel->data($data)->add();
				if($result){
					die(json_encode(array("stat"=>"1","data"=>$doType,"msg"=>"班级科目教师对应关系 新增成功")));
				}else{
					die(json_encode(array("stat"=>"0","data"=>$doType,"msg"=>$btsModel->getError())));
				}
			case "edit":
				$data['id'] = $currentId;
				$data['roomId'] = $roomId;
				$data['subjectId'] = $subjectId;
				$data['banjiId'] = $banjiId;
				$data['teacherId'] = $teacherId;
				$result = $btsModel->save($data);
				if($result){
					die(json_encode(array("stat"=>"1","data"=>$doType,"msg"=>"班级科目教师对应关系 更新成功")));
				}else{
					die(json_encode(array("stat"=>"0","data"=>$doType,"msg"=>$btsModel->getError())));
				}
			default:
				die(json_encode(array("stat"=>"0","data"=>$doType,"msg"=>'操作类型错误')));
		}
	}
	
	/*
	 * 删除班级教师科目对应关系
	*/
	public function delBanjiSubjectTeacher(){
		$id = I("request.rid",0,"int");
		if (!$id){
			echo json_encode(array("stat"=>"0","msg"=>'参数错误'));exit;
		}
		
		//此处应该进行权限验证，防止删除其它班的记录
		
		$btsModel = D("SchoolBanjiSubjectTeacher");
		$delResult = $btsModel->where("id=".$id)->delete();
		if ($delResult){
			echo json_encode(array("stat"=>"1","msg"=>'删除成功'));exit;
		}else{
			echo json_encode(array("stat"=>"0","msg"=>'删除失败'));exit;
		}
		
	}
	
	/**
	 * 清空一个课程表
	*/
	public function ajaxResetLessionTable(){
		$tbId = I("request.tbId",0,"int");
		//判断是否有权限操作，防止跨班级

		$lessionTablemodel = D('SchoolLessionTable');
		$lessionModel = D('SchoolLessions');
		
		$data = array();
		$data['subjectId'] = 0;
		$data['teacherId'] = 0;
		$result = $lessionModel->where("tbId=".$tbId)->data($data)->save();
		if ($result){
			echo json_encode(array("stat"=>"1","msg"=>'操作成功'));exit;
		}else{
			echo json_encode(array("stat"=>"0","msg"=>'操作失败'));exit;
		}
		
	}
		
	/**
	 * 课程表排课	
	 * author:zjh
	*/
	/*
    public function setLessionTable() {
        
        if (IS_POST) {
			$model = D('SchoolLessions');
            $id = I('post.id', 0, 'int');
			if (1 == $id){
				 $this->error('操作失败！禁止处理演示用数据' );
			}
		
			$data = array();		
			$data['id'] = I('post.id', 0, 'int');	
            $data['name'] = I('post.name');
			$data['starttime'] = I('post.starttime');
			$data['endtime'] = I('post.endtime');
			$data['gradeId'] = I('post.gradeId', 0, 'int');
			$data['description'] = I('post.description');

            
            $result = $model->save($data);
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/lessionTableList'));
			} else {
			   $this->error('操作失败！[原因]：' . $model->getError());
			}            
        } else {
            $id = I('get.id',0,'intval');
			$model = D('SchoolLessionTable');
			$schoolLessionModel = D('SchoolLessions');
			$roomModel = D('SchoolRooms');
			$banjiModel = D('SchoolBanji');
			
			$map = array();
			$map['id'] = $id;
			$info = $model->where($map)->find();
			$gradeId = $info['gradeId'];			
			$roomId = $info['roomId'];
			$tbId = $info['id'];//intval($id);
			
			if (!$roomId){
				$this->error("教室ID为空");	
			}
			if (!$tbId){
				$this->error("课程表ID为空");	
			}	
			
		//	$banjiId = $this->getBanjiIdFromRoomId($roomId);
		//	$this->check_banji($banjiId);//是否可操作此班级ID
			
			if ($info){
				//重新组织信息
				$gradeModel = D('SchoolGrade');
				$one = $gradeModel->where("id=".$info['gradeId'])->find();
				$info['gradeName'] = $one['name'];
				
				//教室roomId
				
				
				$banjiModel = D('SchoolBanji');
				$one = $banjiModel->where("id=".$banjiId)->find();
				$info['banjiName'] = $one['name'];
				
				
			}
			
			//var_dump($tbId);
			$ntow = array('1'=>'一','2'=>'二','3'=>'三','4'=>'四','5'=>'五','6'=>'六','7'=>'七','8'=>'八');
			
		//	if (!$banjiId){
		//		$this->error('操作失败！[原因]：' . '班级ID为空');
		//	}
			
			//确保本课程表不重复生成课程记录
			$map = array();
			$map['tbId'] = $tbId;
			$result=$schoolLessionModel->where($map)->find();
			if ($result){
				//已创建过课程数据记录
				//var_dump("有记录");
			} else {
				//var_dump("无记录");
				//未创建过课程表的课程数据记录，批量创建
				//8节课 x 5天 = 40条记录
				//tbId
				//position
				//name
				//gradeId
				//banjiId
				//lessionNumber
				//startTime
				//endTime
				//createTime
				
				//$tmModel = new Model();
				//$lession_time_datas = $tmModel->table("TB_Sch_Lession_Time")->where("type=0")->field("starttime")->order("id desc")->select();//上课时间表//加order就出错
				$lesstimeModel = D("SchoolLessionTime");
				$lession_time_datas = $lesstimeModel->where("type=0")->order("number ASC")->select();//
				$startTimeArr = array();
				$endTimeArr = array();
				if ($lession_time_datas){
					foreach($lession_time_datas as $k=>$v){
						$startTimeArr[$k+1] = $v['starttime'];//$k从零开始，实际需要键从1开始
						$endTimeArr[$k+1] = $v['endtime'];
					}
				};

				//课程开始时间
//				$startTimeArr = array("1"=>'08:00:00',"2"=>'09:00:00',"3"=>'10:00:00',"4"=>'11:00:00',"5"=>'14:00:00',"6"=>'15:00:00',"7"=>'16:00:00',"8"=>'17:00:00');
				//课程结束时间
//				$endTimeArr = array("1"=>'09:00:00',"2"=>'10:00:00',"3"=>'11:00:00',"4"=>'14:00:00',"5"=>'15:00:00',"6"=>'16:00:00',"7"=>'17:00:00',"8"=>'18:00:00');
				
						
				//每节课程开始时间和结束时间
				$lessionTimeModel = D("SchoolLessionTime");
				$ltDatas = $lessionTimeModel->order("id DESC")->select();
				if ($ltDatas){
					//
				} else {
					$this->error('操作失败！[原因]：' . '请检查课程-时间关系表[TB_Sch_Lession_Time]');
				}
				
				//roomId
				
				header("Content-type: text/html; charset=utf-8");
				//星期一至星期五
				for ($x=1; $x<=5; $x++) {				
					
					//一天的8节课
					for ($y=1; $y<=8; $y++) {
						$data = array();
						$data['tbId'] = $tbId;
						$pos = $y.'-'.$x;
						//echo $pos;
						$data['postion'] = $pos;//格式：第几节课-星期几
						$data['name'] = '';
						$data['gradeId'] = $gradeId;
					//	$data['banjiId'] = $banjiId;
						$data['roomId'] = $roomId;
						$data['lessionNumber'] = $y;//第几节课：1,2,3,4,5,6,7,8
						$data['startTime'] = $startTimeArr[$y];
						$data['endTime'] = $endTimeArr[$y];
						
						$data['leftName'] = "第".$ntow[$y]."节";//第几节课中文
						$data['topName'] = '星期'.$ntow[$x];//星期几
						$data['weekday'] = $x;//数字,星期几
						//var_dump($data);
						$result = $schoolLessionModel->data($data)->add();
					} 
				}
			}
			
			//判断是否已生成过课程记录，未生成的批量生成，已生成的显示
			
			//exit;
			
			//$schoolLessionModel = D('SchoolLessions');
			$map = array();
			//$map['gradeId'] = $gradeId;
			$map['tbId'] = $tbId;
			//$map['postion'] = array('IN','1,2,3,4,5');
			$datas_1 = $schoolLessionModel->where(" ((postion='1-1') or (postion='1-2') or (postion='1-3') or (postion='1-4') or (postion='1-5')) and tbId = ".$tbId)->select(); //var_dump($datas);
			$datas_2 = $schoolLessionModel->where(" ((postion='2-1') or (postion='2-2') or (postion='2-3') or (postion='2-4') or (postion='2-5')) and tbId = ".$tbId)->select(); //var_dump($datas);
			$datas_3 = $schoolLessionModel->where(" ((postion='3-1') or (postion='3-2') or (postion='3-3') or (postion='3-4') or (postion='3-5')) and tbId = ".$tbId)->select();
			$datas_4 = $schoolLessionModel->where(" ((postion='4-1') or (postion='4-2') or (postion='4-3') or (postion='4-4') or (postion='4-5')) and tbId = ".$tbId)->select();
			$datas_5 = $schoolLessionModel->where(" ((postion='5-1') or (postion='5-2') or (postion='5-3') or (postion='5-4') or (postion='5-5')) and tbId = ".$tbId)->select();
			$datas_6 = $schoolLessionModel->where(" ((postion='6-1') or (postion='6-2') or (postion='6-3') or (postion='6-4') or (postion='6-5')) and tbId = ".$tbId)->select();
			$datas_7 = $schoolLessionModel->where(" ((postion='7-1') or (postion='7-2') or (postion='7-3') or (postion='7-4') or (postion='7-5')) and tbId = ".$tbId)->select();
			$datas_8 = $schoolLessionModel->where(" ((postion='8-1') or (postion='8-2') or (postion='8-3') or (postion='8-4') or (postion='8-5')) and tbId = ".$tbId)->select();
			
			
			$teachersModel = D('SchoolTeachers');
            if ($datas_1) {
				foreach ($datas_1 as $k => $v){
					//如果teacherId不为0，则查询其姓名
					if ($v['teacherId']){
						$t_datas = $teachersModel->where("id=".$v['teacherId'])->field("name")->find();
						$teacherName = $t_datas['name'];
					} else {
						$teacherName = "教师未设置";
					}
					$row1[] = array("name"=>$v['name'],"id"=>$v['id'],'teacherId'=>$v['teacherId'],"teacherName"=>$teacherName);//$v['name'];				
				}//第1行放在一维数组中
				//var_dump($datas_1);
			}
            if ($datas_2) {
				foreach ($datas_2 as $k => $v){
					//如果teacherId不为0，则查询其姓名
					if ($v['teacherId']){
						$t_datas = $teachersModel->where("id=".$v['teacherId'])->field("name")->find();
						$teacherName = $t_datas['name'];
					} else {
						$teacherName = "教师未设置";
					}
					$row2[] = array("name"=>$v['name'],"id"=>$v['id'],'teacherId'=>$v['teacherId'],"teacherName"=>$teacherName);//$v['name'];				
				}//第2行放在一维数组中
				//var_dump($row2);
			}
            if ($datas_3) {
				foreach ($datas_3 as $k => $v){
					//如果teacherId不为0，则查询其姓名
					if ($v['teacherId']){
						$t_datas = $teachersModel->where("id=".$v['teacherId'])->field("name")->find();
						$teacherName = $t_datas['name'];
					} else {
						$teacherName = "教师未设置";
					}
					$row3[] = array("name"=>$v['name'],"id"=>$v['id'],'teacherId'=>$v['teacherId'],"teacherName"=>$teacherName);//$v['name'];				
				}//第3行放在一维数组中
				//var_dump($row3);
			}		
            if ($datas_4) {
				foreach ($datas_4 as $k => $v){
					$row4[] = array("name"=>$v['name'],"id"=>$v['id'],'teacherId'=>$v['teacherId'],"teacherName"=>$teacherName);//$v['name'];				
				}//第4行放在一维数组中
				//var_dump($row4);
			}					
			
            if ($datas_5) {
				foreach ($datas_5 as $k => $v){
					//如果teacherId不为0，则查询其姓名
					if ($v['teacherId']){
						$t_datas = $teachersModel->where("id=".$v['teacherId'])->field("name")->find();
						$teacherName = $t_datas['name'];
					} else {
						$teacherName = "教师未设置";
					}
					$row5[] = array("name"=>$v['name'],"id"=>$v['id'],'teacherId'=>$v['teacherId'],"teacherName"=>$teacherName);//$v['name'];				
				}//第5行放在一维数组中
				//var_dump($row5);
			}					
            if ($datas_6) {
				foreach ($datas_6 as $k => $v){
					//如果teacherId不为0，则查询其姓名
					if ($v['teacherId']){
						$t_datas = $teachersModel->where("id=".$v['teacherId'])->field("name")->find();
						$teacherName = $t_datas['name'];
					} else {
						$teacherName = "教师未设置";
					}
					$row6[] = array("name"=>$v['name'],"id"=>$v['id'],'teacherId'=>$v['teacherId'],"teacherName"=>$teacherName);//$v['name'];				
				}//第6行放在一维数组中
				//var_dump($row6);
			}					
            if ($datas_7) {
				foreach ($datas_7 as $k => $v){
					//如果teacherId不为0，则查询其姓名
					if ($v['teacherId']){
						$t_datas = $teachersModel->where("id=".$v['teacherId'])->field("name")->find();
						$teacherName = $t_datas['name'];
					} else {
						$teacherName = "教师未设置";
					}
					$row7[] = array("name"=>$v['name'],"id"=>$v['id'],'teacherId'=>$v['teacherId'],"teacherName"=>$teacherName);//$v['name'];				
				}//第7行放在一维数组中
				//var_dump($row7);
			}					
            if ($datas_8) {
				foreach ($datas_8 as $k => $v){
					//如果teacherId不为0，则查询其姓名
					if ($v['teacherId']){
						$t_datas = $teachersModel->where("id=".$v['teacherId'])->field("name")->find();
						$teacherName = $t_datas['name'];
					} else {
						$teacherName = "教师未设置";
					}
					$row8[] = array("name"=>$v['name'],"id"=>$v['id'],'teacherId'=>$v['teacherId'],"teacherName"=>$teacherName);//$v['name'];				
				}//第8行放在一维数组中
				//var_dump($row8);
			}					
						
			$lessTable = array();
			$lessTable[] = $row1;
			$lessTable[] = $row2;
			$lessTable[] = $row3;
			$lessTable[] = $row4;
			$lessTable[] = $row5;
			$lessTable[] = $row6;
			$lessTable[] = $row7;
			$lessTable[] = $row8;
			//var_dump($lessTable);
			$this->assign('lessTable', $lessTable);

			$seltype = "radio";
			$this->assign('subjectModalSelType', $seltype);//班级弹出框中只允许单选
			
			$seltype = "radio";
			$this->assign('teacherModalSelType', $seltype);//教师选择弹出框中只允许单选

            //科目列表 START
			$subjectsModel = D('SchoolSubjects');
			$map = array();
			$subjects = $subjectsModel->getList($map);
			$this->assign('subjects', $subjects);
			//列表 END	
			
            //教师列表 START
			$teachersModel = D('SchoolTeachers');
			$map = array();
			$teachers = $teachersModel->getList($map);
			$this->assign('teachers', $teachers);
			//列表 END	

			$this->assign('info', $info);
			$this->display("setLessionTable");
        }
    }		
		*/
		
	/*
	 * 更新课程表中的某一课程
	*/		
	public function ajaxSetLession(){
		$lsId = I('request.lsId', 0, 'int');//lession表的id
		$btsId = I('request.btsId', 0, 'int');//班级科目教师对应关系表的id
		$resetone = I('request.resetone');//重置一个课程表单元格为空
		
		$lessionModel = D('SchoolLessions');
		$btsModel = D("SchoolBanjiSubjectTeacher");//TB_Sch_Banji_Teacher_Subject
		$banjiModel = D('SchoolBanji');
		$teachersModel = D('SchoolTeachers');
		$subjectsModel = D('SchoolSubjects');
		
		
		if ($resetone == "yes"){
			$data = array();
			$data['id'] = $lsId;	
			$data['subjectId'] = 0;
			$data['teacherId'] = 0;
			
			$result = $lessionModel->save($data);
			if ($result) {
			   echo json_encode(array("stat"=>"1","msg"=>'设置成功',"data"=>"" ));exit;
			} else {
			   echo json_encode(array("stat"=>"0","msg"=>'设置失败',"data"=>""));exit;
			}
		}
		
		//查询关系表
		$datas_bts = $btsModel->where("id=".$btsId)->find();
		if ($datas_bts){
			//从中间表获取到班级、科目、教师中文名
			$banjiId = $datas_bts['banjiId'];
			$subjectId = $datas_bts['subjectId'];
			$teacherId = $datas_bts['teacherId'];
			
			$datas_subject = $subjectsModel->where("id=".$subjectId)->find();
			if ($datas_subject){
				$subjectName = $datas_subject['name'];
			}
			$datas_teacher = $teachersModel->where("id=".$teacherId)->find();
			if ($datas_teacher){
				$teacherName = $datas_teacher['name'];
			}
			
			$data = array();
			$data['id'] = $lsId;	
			$data['subjectId'] = $subjectId;
			$data['teacherId'] = $teacherId;
			
			$result = $lessionModel->save($data);
		//	file_put_contents("debug-ajaxsetlession.txt",PHP_EOL."debug:".PHP_EOL."id:".$id.";name:".$name.PHP_EOL,FILE_APPEND);//写调试到TXT
			if ($result) {
			   echo json_encode(array("stat"=>"1","data"=>$subjectName."<br>".$teacherName,"msg"=>'设置成功'));
			} else {
			   echo json_encode(array("stat"=>"0","msg"=>'设置失败'));
			}
			
			
			
		}else{
			echo json_encode(array("stat"=>"0","msg"=>'参数错误'));
		}
		
  
	}
		
	/*
	 * 更新课程表中的某一教师
	*/	
	/*	
	public function ajaxSetLessionTeacher(){
		$id = I('get.id', 0, 'int');
		$teacherId = I('get.teacherid');
		$model = D('SchoolLessions');
		
		$data['id'] = $id;	
		$data['teacherId'] = $teacherId;
		
		$result = $model->save($data);
//		file_put_contents("debug-ajaxsetlessionTeacher.txt",PHP_EOL."debug:".PHP_EOL."id:".$id.";name:".$name.PHP_EOL,FILE_APPEND);//写调试到TXT
		if ($result) {
		   echo json_encode(array("stat"=>"1","msg"=>'设置成功'));
		} else {
		   echo json_encode(array("stat"=>"0","msg"=>'设置失败'));
		}  
	}		
		*/
		
		
	/**
	 * 课程列表
	 *　author:zjh
	*/
	public function lessionList(){
		$model = D('School');
		
		$map = array();
		$map['hide'] = 0;
		$malls = $model->where($map)->select(); 
		$this->assign('datas', $malls);
		$this->display("School/schoolList");
	}
	
	
	
	/**
	 * 获取班级列表Json格式，用于重新选择年级后，更新班级下拉列表
	 *　author:zjh
	*/
	public function getBanjiListJson(){
		$gradeId = intval(I('request.gradeId', 0, 'int'));
		$model = D('SchoolBanji');
		
		$map = array();
		$map['gradeId']=$gradeId;
		
		$datas = $model->where($map)->field("id,name")->select();
		
		if ($datas){
			echo json_encode($datas);//这儿怎么传递stat????????????????
		}else {
			echo json_encode(array("stat"=>"0","msg"=>$model->getError()));
		}

	}

	/**
	 * 学生签到列表
	 *　author:zjh
	*/
	/*public function studentSignInList(){
		$model = D('SchoolStudentSignIn');
		$banjiId = I('get.banjiId',0,"int");
		$this->assign("banjiId",$banjiId);
		
		$map = array();
		$starttime = trim(I("request.starttime"));
		if (!empty($starttime)){
			$starttime = date("Y-m-d",strtotime($starttime));
			$map['_string'] = " Convert(varchar(10),signIntime,120) = '$starttime' and signIntime <>'' ";//只需要统计signIntime为今天的总数即可
			
		}
		$this->assign("starttime",$starttime);
		
		//本班学生
		$stuModel = D('SchoolStudents');
		$stu_bj_arr = array();
		$stu_bj_str = "";
		if($banjiId){
			$map_stu = array();
			$map_stu['banjiId'] = $banjiId;
			$datas_stu = $stuModel->where($map_stu)->field("id")->select();
			foreach($datas_stu as $k=>$v){
				$stu_bj_arr[] = $v['id'];
			}
			$stu_bj_str = implode(",",$stu_bj_arr);//本班所有学生，逗号分隔
			//echo $stu_bj_str;
			//$stu_num = $stuModel->where($map_stu)->count();//本班学生总数
			//echo $stu_num;
			$map['StudentId']=array("IN",$stu_bj_str);
		}
		
		
		
	
        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
        $totals = $model->where($map)->count();
        $Page = new Page($totals, 10);
        $show = $Page->show();
        $this->assign('page', $show);		
		$datas = $model->where($map)->field("Id,StudentId,convert(VARCHAR(24),signIntime,120) as signtime")->order("Id DESC")->limit($Page->firstRow.','.$Page->listRows)->select(); 
		
		if ($datas){
			//重新组织信息
			$stuModel = D('SchoolStudents');
			$gradeModel = D('SchoolGrade');
			$banjiModel = D('SchoolBanji');
			$teachersModel = D('SchoolTeachers');
			foreach ($datas as $k => $v){
				$one = $stuModel->where("id=".$v['StudentId'])->find();
				$datas[$k]['studentName'] = $one['name'];
				
				$stu = $stuModel->where("id=".$v['StudentId'])->find();
				$gradeId = $stu['gradeId'];
				$banjiId = $stu['banjiId'];
				
				//年级名
				$one = $gradeModel->where("id=".$stu['gradeId'])->find();
				$datas[$k]['gradeName'] = $one['name'];
				
				//班级名
				$one = $banjiModel->where("id=".$stu['banjiId'])->find();
				$datas[$k]['banjiName'] = $one['name'];	
				$banzhurenId = $one['banzhurenId'];	
				
				//班主任
				$one = $teachersModel->where("id=".$banzhurenId)->find();
				$datas[$k]['banzhurenName'] = $one['name'];	
				
				//$datas[$k]['DateTime'] = $v['DateTime'];
				//var_dump($v['signtime']);
			}

		}
		//年级列表 START
		$gradeModel = D('SchoolGrade');
		$map = array();
		$grades = $gradeModel->getList($map);
		$this->assign('grades', $grades);
		//年级列表 END
		
		//班级列表 START
		$banjiModel = D('SchoolBanji');
		$map = array();
		if (session("username") == C('ADMIN_AUTH_KEY')) {
			//超级管理员，列表中显示全部班级
		}else{
			//非超级管理员，班级列表中只显示有权限的
			$map['id'] = array("IN",session('user_banji_list'));
		}
		$banjis = $banjiModel->getList($map);
		$this->assign('banjis', $banjis);
		//班级列表 END			
		
		$this->assign('datas', $datas);
		$this->display("School/studentSignInList");
	}*/
	
	/**
	 * 教师签到列表
	 *　author:zjh
	*/
	public function teacherSignInList(){
		$model = D('SchoolTeacherSignIn');
		
		$map = array();
	
        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
        $totals = $model->where($map)->count();
        $Page = new Page($totals, 10);
        $show = $Page->show();
        $this->assign('page', $show);		
		$datas = $model->where($map)->field("Id,TeacherId,convert(VARCHAR(24),DateTime,120) as signtime")->order("Id DESC")->limit($Page->firstRow.','.$Page->listRows)->select(); 
		
		if ($datas){
			//重新组织信息
			$teachersModel = D('SchoolTeachers');
			foreach ($datas as $k => $v){
				//
				$one = $teachersModel->where("id=".$v['TeacherId'])->field("name")->find();
				$datas[$k]['teacherName'] = $one['name'];	
			}

		}		
		
		//年级列表 START
		$gradeModel = D('SchoolGrade');
		$map = array();
		$grades = $gradeModel->getList($map);
		$this->assign('grades', $grades);
		//年级列表 END
		
		//班级列表 START
		$banjiModel = D('SchoolBanji');
		$map = array();
		$banjis = $banjiModel->getList($map);
		$this->assign('banjis', $banjis);
		//班级列表 END	
			
		$this->assign('datas', $datas);
		$this->display("School/teacherSignInList");
	}
	
	/**
	 * 学生签到统计
	*/
	public function studentSignCount(){
		$par = trim(I("request.par"));
		if ($par == '' || empty($par)){
			$par = 'index';//统计第一步：显示可用的班级列表
		}
		$this->assign("par",$par);
		
		$starttime = trim(I("request.starttime"));
		$banjiId = I("request.banjiId",0,"int");
		if (empty($starttime) || $starttime==''){
			$starttime = date("Y-m-d",mktime());	
		}else{
			$starttime = date("Y-m-d",strtotime($starttime));
		}
		
		

		$this->assign("banjiId",$banjiId);
		$this->assign("starttime",$starttime);

		
		$stuModel = D('SchoolStudents');
		
		//班级列表 START
		$banjiModel = D('SchoolBanji');
		$map = array();
		$banjis = $banjiModel->getList($map);
		$this->assign('banjis', $banjis);
		//班级列表 END	
		
		if ($banjiId){
			$banji_datas = $banjiModel->where("id=".$banjiId)->find();
			$this->assign("banjiName",$banji_datas['name']);
		}
		
		//班级列表 START
		$banjiModel = D('SchoolBanji');
		$map = array();
		if (session("username") == C('ADMIN_AUTH_KEY')) {
			//超级管理员，列表中显示全部班级
		}else{
			//非超级管理员，班级列表中只显示有权限的
			$map['id'] = array("IN",session('user_banji_list'));
		}
		$banjis = $banjiModel->getList($map);
		$this->assign('banjis', $banjis);
		//班级列表 END

		
		$this->assign("data_count",$data_count);
		switch ($par){
			case "index":

				//班级列表
				$this->display("School/studentSignCountIndex");
				break;
			case "signInCountlistOfOneBanji":
				$banjiId = I("request.banjiId",0,"int");
				$stuSignInModel = D("SchoolStudentSignIn");
				$stuModel = D('SchoolStudents');
				
				$map = array();
				$starttime = trim(I("request.starttime"));
				if ($starttime != '' && !empty($starttime)){
					
					$starttime = date("Y-m-d",strtotime($starttime));
					$tmp_start = $starttime ." 00:00:01";
					$tmp_end = $starttime ." 23:59:59";
					$map['signIntime'] = array("BETWEEN",array($tmp_start,$tmp_end));
				}//确保日期格式正确
				
				if ($banjiId){
					$studentIdStr = $stuModel->getBanjiStudentIdStr($banjiId);//本班所有学生，逗号分隔
					$map['StudentId'] = array("IN",$studentIdStr);
				}
				
				
				
				// 加载数据分页类
				import('ORG.Util.Page');
				// 数据分页
		//		$totals = $stuSignInModel->query("select COUNT(DISTINCT convert(VARCHAR(10),signIntime,120)) as total from TB_Sch_StudentSignIn");
		
				$totals = $stuSignInModel->where($map)->field("COUNT(DISTINCT convert(VARCHAR(10),signIntime,120)) as total")->count();
				$total = $totals[0]['total'];
				$Page = new Page($total, 10);
				$show = $Page->show();
				$this->assign('page', $show);
				
				//查询签到流水记录中所有日期（去重复）
				$days = 0;
				
		//		$days_count = $stuSignInModel->query("select COUNT(DISTINCT convert(VARCHAR(10),signIntime,120)) from TB_Sch_StudentSignIn");
		//		$days_list = $stuSignInModel->query("select DISTINCT convert(VARCHAR(10),signIntime,120) as signIntime from TB_Sch_StudentSignIn order by signIntime DESC limit");
				$days_list = $stuSignInModel->where($map)->field("DISTINCT convert(VARCHAR(10),signIntime,120) as signIntime")->order("signIntime DESC")->limit($Page->firstRow.','.$Page->listRows)->select();
				//var_dump($days_list);
							
				foreach($days_list as $kk=>$vv){
					//echo "<br>".$vv;
					$count = $this->getBanjiSignInCount($banjiId,$vv['signIntime']);
					$days_list[$kk]['data']=$count;
				}
				//var_dump($days_list);
				
				$this->assign("days_list",$days_list);
				$this->display("School/studentSignCount");
				break;
			case "signInListOfOneBanjiTheDay":
				$map = array();
				$starttime = trim(I("request.starttime"));
				if ($starttime != '' && !empty($starttime)){
					
					$starttime = date("Y-m-d",strtotime($starttime));
					$tmp_start = $starttime ." 00:00:01";
					$tmp_end = $starttime ." 23:59:59";
					$map['signIntime'] = array("BETWEEN",array($tmp_start,$tmp_end));
				}//确保日期格式正确
				
				$signModel = D("SchoolStudentSignIn");
				$datas = $this->getBanjiSignInList($banjiId,$starttime);
				
				$this->assign("starttime",$starttime);
				$this->display("School/studentSignInList");
				break;
			default:
				;
		}
		
	}
	
	/**
	 * 获取上学时间和放学时间
	 * 从课时数据表，取第一节课的上课时间，或最后一节课的下课时间
	*/
	public function getSchoolTime($type=0){
		$model = new Model();
		if ($type){
			//放学时间
			$datas = $model->table("TB_Sch_Lession_Time")->where("type=0")->field("endtime")->order("endtime DESC")->find();
			return $datas['endtime'];
		}else{
			//上学时间
			$datas = $model->table("TB_Sch_Lession_Time")->where("type=0")->field("starttime")->order("starttime ASC")->find();
			return $datas['starttime'];
		}
	}
	
	/**
	 * 统计某个班的签到
	 * 班级ID必须指定有效值
	 * 开始时间和结束时间可为空，如为空，则显示当前日期
	 * 
	*/
	public function getBanjiSignInCount($banjiId=0,$datetime=''/* 某一天，格式：2016-06-07 */){
		if (!$banjiId){
			return 0;
		}
		
		//统计日期
		if (empty($datetime)){
			$datetime=date("Y-m-d",time());
		}
		
		//返回格式定义:班级ID，班级总人数，签到人数，迟到人数，签退人数
		$out = array("datetime"=>$datetime,"banjiId"=>0,"banji_name"=>"","stu_num"=>0,"qiandao_num"=>0,"chidao_num"=>0,"qiantui_num"=>0);
		
		$school_starttime = $this->getSchoolTime(0);//上学时间
		//echo $school_starttime;
		
		$stuModel = D('SchoolStudents');
		$banjiModel = D('SchoolBanji');
		
		$datas_bj = $banjiModel->where("id=".$banjiId)->find();
		$out['banji_name'] = $datas_bj['name'];
		
		//本班学生
		$stu_bj_arr = array();
		$stu_bj_str = "";
		if($banjiId){
			$map_stu = array();
			$map_stu['banjiId'] = $banjiId;
			$datas_stu = $stuModel->where($map_stu)->field("id")->select();
			foreach($datas_stu as $k=>$v){
				$stu_bj_arr[] = $v['id'];
			}
			$stu_bj_str = implode(",",$stu_bj_arr);//本班所有学生，逗号分隔
			//echo $stu_bj_str;
			$stu_num = $stuModel->where($map_stu)->count();//本班学生总数
			//echo $stu_num;
		}
		
		$out['banjiId']=$banjiId;//班级ID
		$out['stu_num']=$stu_num;//学生总数
		
		
		$signModel = D("SchoolStudentSignIn");


		$map_count = array();
		$map_count['StudentId'] = array("IN",$stu_bj_str);//"1,2,3,4"
		$map_count['_string'] = " Convert(varchar(10),signIntime,120) = '$datetime' and signIntime <>'' ";//只需要统计signIntime为今天的总数即可
		$qiandao_num = $signModel->where($map_count)->count();//注意这儿必须是2016-04-19
		/*foreach($datas as $k=>$v){
			$dt = get_object_vars($v['signIntime']);
			//var_dump($dt);
			echo "<br><br>studentid:".$v['StudentId']."---".$dt['date'];
		}*/
		//var_dump($qiandao_num);
		//echo "班级：".$banjiId."-统计时间：".$datetime;
		
		$out['qiandao_num'] = $qiandao_num;//本班签到人数
//var_dump($map_count);
		
		//今日早到（签到但晚于规定时间）
		
		$map_count = array();
		$map_count['StudentId'] = array("IN",$stu_bj_str);//"1,2,3,4"
		//$map_count['_string'] = " Convert(varchar(10),DateTime,120) = '$datetime' and Convert(varchar(10),DateTime,108) <= '$school_starttime'";//08:00:00
		$count_chidao = $signModel->where($map_count)->field("Id,Convert(varchar(10),signIntime,108) as tm")->select();//注意这儿必须是2016-04-1//->field("Convert(varchar(10),DateTime,108) as tm")
		/*foreach($count_chidao as $k=>$v){
			echo "<br>".$v['Id'].'-'.$v['tm'];
			//if ($v['tm'] > "08:00:00")
			if ($v['tm'] > $school_starttime){
				echo "迟到";
			} elseif ($v['tm'] == $school_starttime) {
				echo "准时";	
			}else{
				echo "??";	
			}
		}*/

		//迟到
		$map_count = array();
		$map_count['StudentId'] = array("IN",$stu_bj_str);//"1,2,3,4"
		$map_count['_string'] = "Convert(varchar(10),signIntime,120) = '$datetime' and signIntime <>''  and Convert(varchar(10),signIntime,108) > '$school_starttime' ";//只需要统计signIntime为今天的总数即可
		$chidao_num = $signModel->where($map_count)->count();//注意这儿必须是2016-04-19
		$out['chidao_num'] = $chidao_num;//本班指定日迟到人数

		$out['weiqiandao_num'] = intval($stu_num - $qiandao_num);

		//var_dump($count_chidao);
		return $out;
	}
	
	/**
	 * 某个班的签到记录列表
	 * 班级ID必须指定有效值
	 * 开始时间和结束时间可为空，如为空，则显示当前日期
	 * 
	*/
	public function getBanjiSignInList($banjiId=0,$datetime=''/* 某一天，格式：2016-06-07 */){
		if (!$banjiId){
			return 0;
		}
		
		//统计日期，为空时取今天
		if (empty($datetime)){
			$datetime=date("Y-m-d",time());
		}
		
		//返回格式定义:班级ID，班级总人数，签到人数，迟到人数，签退人数
		$out = array("datetime"=>$datetime,"banjiId"=>0,"banji_name"=>"","stu_num"=>0,"qiandao_num"=>0,"chidao_num"=>0,"qiantui_num"=>0);
		
		$school_starttime = $this->getSchoolTime(0);//上学时间
		//echo $school_starttime;
		
		$stuModel = D('SchoolStudents');
		$banjiModel = D('SchoolBanji');
		
		$datas_bj = $banjiModel->where("id=".$banjiId)->find();
		$out['banji_name'] = $datas_bj['name'];
		$this->assign("banjiName",$datas_bj['name']);
		
		//本班学生
		$stu_bj_arr = array();
		$stu_bj_str = "";
		if($banjiId){
			$map_stu = array();
			$map_stu['banjiId'] = $banjiId;
			$datas_stu = $stuModel->where($map_stu)->field("id")->select();
			foreach($datas_stu as $k=>$v){
				$stu_bj_arr[] = $v['id'];
			}
			$stu_bj_str = $stuModel->getBanjiStudentIdStr($banjiId);//本班所有学生，逗号分隔
			//echo $stu_bj_str;
			$stu_num = $stuModel->where($map_stu)->count();//本班学生总数
			//echo $stu_num;
		}
		
		//已签到
		$stuModel = D('SchoolStudents');
		$signModel = D("SchoolStudentSignIn");
		$map = array();
		$map['StudentId'] = array("IN",$stu_bj_str);//"1,2,3,4"
		$map['_string'] = " Convert(varchar(10),signIntime,120) = '$datetime' and signIntime <>'' ";//只需要统计signIntime为今天的总数即可
		$qiandao_count = $signModel->where($map)->field("Id,StudentId,convert(VARCHAR(24),signIntime,120) as signtime")->count();
		$qiandao_list = $signModel->where($map)->field("Id,StudentId,convert(VARCHAR(24),signIntime,120) as signtime")->select();//注意这儿必须是2016-04-19

		$stu_qiandao_arr = array();
		foreach($qiandao_list as $k=>$v){
			$stuDatas = $stuModel->where("id=".$v['StudentId'])->field('name')->find();
			$qiandao_list[$k]['studentName'] = $stuDatas['name'];//姓名
			
			$tmp_time = date("H:i:s",strtotime($v['signtime']));
			$qiandao_list[$k]['stat'] = $tmp_time;
			if ($tmp_time <= $school_starttime){
				$qiandao_list[$k]['stat'] = "";//"正常";
			} else {
				$qiandao_list[$k]['stat'] = "迟到";
			}
			//迟到/正常
			$stu_qiandao_arr[] = $v['StudentId'];//已签到学生ID数组
		}
		$this->assign("qiandao_list",$qiandao_list);
		
		//未签到
		$weiqiandao_num = $stu_num - $qiandao_count;
		if ($weiqiandao_num > 0){
			//echo "未签到人数".$weiqiandao_num;
			$stu_qiandao_str = implode(",",$stu_qiandao_arr);
			$this->assign("weiqiandao_num",$weiqiandao_num);
			
			$map = array();
			$map['id'] = array("NOT IN",$stu_qiandao_str);
			$map['banjiId'] = $banjiId;
			$students_list_not_sign = $stuModel->where($map)->field("id,name")->select();
			$this->assign("students_list_not_sign",$students_list_not_sign);
		}
		

		
		
	}
	
	/**
	 * 通知列表
	 *　author:zjh
	*/
	public function noticesList(){
		$model = D('SchoolNotices');
		$model_bj = D("SchoolBanji");

		//搜索条件
		$keyboard = trim(I('get.keyboard'));
		$orderType = I('get.orderType');
		$banjiId = I('get.banjiId',0,"int");
		
		$this->assign('keyboard', $keyboard);
		$this->assign('orderNext', $orderNext);
		$this->assign('banjiId', $banjiId);

		$map = array();
		$map['id'] = array('GT',0);
		if ($keyboard){$map['title']=array('LIKE','%'.$keyboard.'%');}
	
		
        // 加载数据分页类
        import('ORG.Util.Page');
		
		//过滤可见班级的....................................................
	//	if ($banjiId){$map['banjiId']=array('LIKE','%,'.$banjiId.',%');}//前后加逗号，搜索：,1,对于字段为逗号分隔的id，要在入库时前后加上0,和,0
		
		//班级列表 START
		$banjiModel = D('SchoolBanji');
		if (session("username") == C('ADMIN_AUTH_KEY')) {
			//超级管理员，列表中显示全部班级
		}else{
			//非超级管理员，班级列表中只显示有权限的
			$map_bj['id'] = array("IN",session('user_banji_list'));
		}
		$banjis = $banjiModel->getList($map_bj);
		$this->assign('banjis', $banjis);
		//班级列表 END		
		
		$stuModel = D('SchoolStudents');
		$userModel = D('User');
		
		//echo session('user_banji_list');
		$bjid_str = session('user_banji_list');
		$bjid_arr = explode(",",$bjid_str);//拆成数组
		$bjid_arr = array_unique($bjid_arr);//去重复
		
		$key = array_search(0, $bjid_arr);//清除0元素（已经去重复，所以一次就够了）
		if ($key !== false){
			array_splice($bjid_arr, $key, 1);
		}
		if ($banjiId){
			$map['banjiId']=array('LIKE','%,'.$banjiId.',%');
		}else{
			if (session("username") !== C('ADMIN_AUTH_KEY') && !empty($bjid_arr)){
				
				$tmp = array();
				foreach($bjid_arr as $k=>$v){
					$tmp[] = array('like','%,'.$v.',%');
				}
				
				$tmp[] = '';
				$tmp[] = 'or';
				
				$map['banjiId']  = $tmp; 
				//$map['banjiId']  = array(array('like','%,3,%'), array('like','%,4,%'), '','or'); 
	
			}
		}
		/*
		switch (session('user_type')){
			case "teacher"://老师
				//echo '老师';
				//这儿先停一下，因为教师和班级的关联在课程管理那儿
			//	$map['banjiId'] = array("IN",session('user_banji_list'));
				break;
			case "student"://学生
				//echo '学生';
				//echo session('refer_id');
				//$user_datas = $userModel->where("id=".session(C('USER_AUTH_KEY')))->field("referId")->find();
				$stu_datas = $stuModel->where("id=".session('refer_id'))->field("id,banjiId,name")->find();
				if ($stu_datas){
					//var_dump($stu_datas);
					$map['banjiId'] = array('LIKE','%,'.$stu_datas['banjiId'].',%');
				}
				break;
				
			default:
				;
		}*/
		
        
        // 数据分页
        $totals = $model->where($map)->count();
        $Page = new Page($totals, 10);
        $show = $Page->show();
        $this->assign('page', $show);	
		//var_dump($map);
		$datas = $model->where($map)->field("id,title,banjiId,convert(VARCHAR(24),endTime,120) as endtime, userId")->order("id DESC")->limit($Page->firstRow.','.$Page->listRows)->select(); 
		//echo $model->getlastsql();
		
		//重新组织信息
		$noticeClassModel = D('SchoolNoticesClass');
		foreach ($datas as $k => &$v){
			//classid为整数，只有一值时
			$one = $noticeClassModel->where("id=".$datas[$k]['classId'])->find();
			$datas[$k]['className'] = $one['name'];
	
			$datas_bj = $model_bj->where("id IN (" .$v['banjiId']. ")")->field("name")->select();
			$u = array();
			foreach($datas_bj as $kk=>$vv){
				$u[] = $vv['name'];
			}
			$userArr = $userModel->where("id = $v[userId]")->field('type, account, referId, id')->find();
			if(!$userArr[type]){
				$v[publisher] = $userArr[account];
				$v[referId] = $userArr[referId];
				$v[type] = $userArr[type];
				$v[publisherId] = session(C('USER_AUTH_KEY'));
			}else{
				$v[publisher] = D('SchoolTeachers')->where("id=$userArr[referId]")->getField('name');
				$v[referId] = $userArr[referId];
				$v[type] = $userArr[type];
				$v[publisherId] = session(C('USER_AUTH_KEY'));
			}
			
			$datas[$k]['banjiName'] = implode(",",$u);

		}
		
		//分类
		$noticeClassModel = D('SchoolNoticesClass');
		$originClass = $noticeClassModel->order('pid asc, id asc')->select();
		$class = array();
		$noticeClassModel->sortedTypes($class, $originClass);
		$this->assign('class', $class);
		
		$this->assign('datas', $datas);
		$this->display("School/noticesList");
	}		
		
	/**
	 * 添加通知
	 * author:zjh
	*/
     public function addNotice() {
        if (IS_POST) {
			$Model = D('SchoolNotices');
			
			$all_school = I("request.chk_all_school");//全校
			
			//班级选择
			if ($all_school){
				$banjiId = "";//全校时，banjiId字段为空
			}else{
				$banjiIdArr=$_POST['banjiIdStr'];
				$banjiId = implode(',',$banjiIdArr);
				$banjiId = "0,".$banjiId.",0";				
			}
			
			$data = array();		
			//$data['id'] = I('post.id', 0, 'int');	
            $data['title'] = I('post.name');
			$data['content'] = I('post.content');
			$data['beginTime'] = I('post.starttime');
			$data['endTime'] = I('post.endtime');
			$data['banjiId'] = $banjiId;	
			//$data['createTime'] = date("Y/m/d h:i:s", mktime());
			$data['classId'] = I('post.classId', 0, 'int');
			$data['userId'] = session(C('USER_AUTH_KEY'));
			$result = $Model->data($data)->add();

		   // 执行操作
		   if ($result !== FALSE) {
			   //$insertId = $result;
			   $this->success('操作成功！', U('School/noticesList'));
		   } else {
			   $this->error('操作失败！[原因]：' . $Model->getError());
		   }

        } else {
			//分类
			$noticeClassModel = D('SchoolNoticesClass');
			$originClass = $noticeClassModel->order('pid asc, id asc')->select();
			$class = array();
			$noticeClassModel->sortedTypes($class, $originClass);
			$this->assign('class', $class);
			
			//班级列表 START
			$banjiModel = D('SchoolBanji');
			if (session("username") == C('ADMIN_AUTH_KEY')) {
				//超级管理员，列表中显示全部班级
			}else{
				//非超级管理员，班级列表中只显示有权限的
				$map_bj['id'] = array("IN",session('user_banji_list'));
			}
			$banjis = $banjiModel->getList($map_bj);
			$this->assign('banjis', $banjis);
			//班级列表 END

			
            // 
            $this->display("editNotice");
        }
		 
		
	}		
		
	/**
	 * 编缉通知	
	 * author:zjh
	*/
    public function editNotice() {
        
        if (IS_POST) {
			$model = D('SchoolNotices');
            $id = I('post.id', 0, 'int');
			
			$all_school = I("request.chk_all_school");//全校
			
			//班级选择
			if ($all_school){
				$banjiId = "";//全校时，banjiId字段为空
			}else{
				$banjiIdArr=$_POST['banjiIdStr'];
				$banjiId = implode(',',$banjiIdArr);
				$banjiId = "0,".$banjiId.",0";				
			}

			
			$data = array();		
			$data['id'] = I('post.id', 0, 'int');	
            $data['title'] = I('post.name');
			$data['content'] = I('post.content');
			$data['beginTime'] = I('post.starttime');
			$data['endTime'] = I('post.endtime');
			$data['banjiId'] = $banjiId;	
			$data['classId'] = I('post.classId', 0, 'int');
			$data['userId'] = session(C('USER_AUTH_KEY'));
            
            $result = $model->save($data);
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/noticesList'));
			} else {
			   $this->error('操作失败！[原因]：' . $model->getError());
			}            
        } else {
            $id = I('get.id',0,'intval');

			//分类
			$noticeClassModel = D('SchoolNoticesClass');
			$originClass = $noticeClassModel->order('pid asc, id asc')->select();
			$class = array();
			$noticeClassModel->sortedTypes($class, $originClass);
			$this->assign('class', $class);
			
			//班级列表 START
			$banjiModel = D('SchoolBanji');
			if (session("username") == C('ADMIN_AUTH_KEY')) {
				//超级管理员，列表中显示全部班级
			}else{
				//非超级管理员，班级列表中只显示有权限的
				$map_bj['id'] = array("IN",session('user_banji_list'));
			}
			$banjis = $banjiModel->getList($map_bj);
			$this->assign('banjis', $banjis);
			//班级列表 END
			
			$model = D('SchoolNotices');
			$map['id'] = $id;
			$datas = $model->field("id,title,content,banjiId,convert(VARCHAR(24),endTime,120) as endtime")->where($map)->find();
			
            if ($datas) {
				
				//已提交的已选择班级，预先复选中
				$arr_checked = array();
				$arr_checked = explode(',',$datas['banjiId']);
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
				
				
				
				
				$this->assign('status', $datas['status']);
				$this->assign('datas', $datas);
				$this->display("editNotice");               
            }
        }
    }			
		
		
	/**
	 * 删除通知
	 * author:zjh
	*/
    public function delNotice() {
        $id = I('get.id', 0, 'int');
        if (!$id) {
            $this->error('非法操作！');
        }
        
        $model = D('SchoolNotices');
        $result = $model->where(array('id'=>$id))->find();
        if (!$result) {
            $this->error('非法操作，不存在该记录！');
        }
 
        // 包含子分类的父级分类不能删除
        
        $delResult = $model->where(array('id'=>$id))->delete();
        if ($delResult !== false) {
           $this->success('操作成功！', U('School/noticesList'));
        } else {
           $this->error('操作失败！[原因]：' . $model->getError());
        }	
	}			
	
	
	
	/**
	 * 通知分类列表，分页有问题
	 * author:zjh
	*/
    /* public function noticeClass() {
        $noticeClassModel = D('SchoolNoticesClass');
		
        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
		$where = array();
		//$where['Pid'] = 0;
        $totals = $noticeClassModel->where($where)->count();
        $Page = new Page($totals, 10);
		//分页跳转的时候保证查询条件
		foreach($map as $key=>$val) {
			$Page->parameter[$key] = urlencode($val);
		}
        $show = $Page->show();
        $this->assign('page', $show);
		

        $originTypes = $noticeClassModel->order('id DESC')->select();
        $datas = array();
        $noticeClassModel->sortedTypes($datas, $originTypes);
        //var_dump($datas);
        $this->assign('datas', $datas);

		$this->display("noticeClass");
	}	*/
	
	
	/**
	 * 添加通知分类
	 * author:zjh
	*/
   /* public function addNoticeClass() {
        if (IS_POST) {
			// 处理表单提交参数
			$name = I('post.name');
			$pid = I('post.pid', 0, 'int');
			$sort = I('post.sortnum', 0, 'int');
			
			// 实例化分类模型
			$noticeClassModel = D('SchoolNoticesClass');
			$data['name'] = $name;
							import('ORG.Util.String');
							$uuid = String::uuid();
							$uuid = str_replace("{","",$uuid);
							$uuid = str_replace("}","",$uuid);
			$data['uuid'] =	$uuid;//import('ORG.Util.String');
			$data['pid'] = $pid;
			$data['sort'] = $sort;
			
			// 执行操作
			$result = $noticeClassModel->data($data)->add();
			if ($result !== FALSE) {
				$this->success('操作成功！', U('School/noticeClass'));
			} else {
				$this->error('操作失败！[原因]：' . $noticeClassModel->getError());
			}
        } else {
			
			$classInfo = array();
			$classInfo['sortnum'] = 0;
			$this->assign('classInfo', $classInfo);
            
            // 获取父级分类数据
            $noticeClassModel = D('SchoolNoticesClass');
            $originTypes = $noticeClassModel->order('Pid asc, ID asc')->select();
            $class = array();
            $noticeClassModel->sortedTypes($class, $originTypes);
            $this->assign('class', $class);
            
            $this->display('editNoticeClass');
        }
    }	*/
	
	/**
	 * 修改通知分类
	 * author:zjh
	*/
	/*public function editNoticeClass(){
        if (IS_POST) {
			// 处理表单提交参数
			$id = I('post.id', 0, 'int');
			$name = I('post.name');
			$pid = I('post.pid', 0, 'int');
			$sort = I('post.sortnum', 0, 'int');
			
			// 实例化分类模型
			$noticeClassModel = D('SchoolNoticesClass');
			
			$data['name'] = $name;
			$data['pid'] = $pid;
			$data['sort'] = $sort;
			
			$map=array();
			$map['id']=$id;
            $result = $noticeClassModel->where($map)->save($data);
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/noticeClass'));
			} else {
			   $this->error('操作失败！[原因]：' . $noticeClassModel->getError());
			}            
			
        } else {
            
            $id = I('get.id', 0, 'int');
            if (!$id) {
                $this->error('非法操作！');
            }
            $noticeClassModel = D('SchoolNoticesClass');
            $classInfo = $noticeClassModel->where(array('id'=>$id))->find();
            if (!$classInfo) {
                $this->error('非法操作！');
            }
            
            $this->assign('classInfo', $classInfo);//var_dump($classInfo);
			
            // 获取父级分类数据
            //$noticeClassModel = D('SchoolNoticesClass');
            $originTypes = $noticeClassModel->order('pid asc, id asc')->select();
            $class = array();
            $noticeClassModel->sortedTypes($class, $originTypes);
            $this->assign('class', $class);
            
            $this->display('editNoticeClass');
        }
	}*/
	
	/**
	 * 删除通知分类
	 * author:zjh
	*/
    /*public function delNoticeClass() {
        $id = I('get.tid', 0, 'int');
        if (!$id) {
            $this->error('非法操作！');
        }
        
        $noticeClassModel = D('SchoolNoticesClass');
        $noticeClassInfo = $noticeClassModel->where(array('id'=>$id))->find();
        if (!$noticeClassInfo) {
            $this->error('非法操作，不存在该分类！');
        }
 
         // 包含子分类的父级分类不能删除
        $childrenClass = $noticeClassModel->where(array('pid'=>$id))->find();//有下级分类
        if ($childrenClass) {
            $this->error('请先删除下级分类！');
        }   
        $childrenNotice = D('SchoolNotice')->where(array('classId'=>$noticeClassInfo['id']))->find();//分类下有通知
        if ($childrenClass) {
            $this->error('该分类下有通知，请先将通知从此分类下移除！');
        }   		
		
        //if ($childrenClass || $childrenNotice) {
        //    $this->error('该分类非空，不允许删除！');
        //}
        
        $delResult = $noticeClassModel->where(array('id'=>$id))->delete();
        if ($delResult !== false) {
           $this->success('操作成功！', U('School/noticeClass'));
        } else {
           $this->error('操作失败！[原因]：' . $noticeClassModel->getError());
        }	
		
			
	}*/
	
	
	/**
	 * 板报列表
	 *　author:zjh
	*/
	public function boardsList(){
		$model = D('SchoolBoards');
		$type = trim(I("request.type"));
		if (empty($type)){
			$type = "banjiList";
		}
		
		
		switch ($type){
			case "banjiList":
				$user = D("User");
				//班级列表 START
				$banjiModel = D('SchoolBanji');
				$map = array();
				if (session("username") == C('ADMIN_AUTH_KEY')) {
					;//超级管理员，列表中显示全部班级
				}else{
					//非超级管理员，班级列表中只显示有权限的
					$map['id'] = array("IN",session('user_banji_list'));
				}
				//$banjiCount = $banjiModel->where($map)->count();
				$banjis = $banjiModel->where($map)->order("id DESC")->select();
				
				$boardPhotoModel = D("SchoolBoardPhoto");
				foreach($banjis as $k=>$v){
					$count = $boardPhotoModel->where("banjiId=".$v['id'])->count();
					$banjis[$k]['photonum'] = $count;
				}
				$this->assign('banjis', $banjis);
				//班级列表 END
				
				$result = $user->userBanjiListCount();//可管理班级是否是1
		
				//只可管理一个表的话，直接显示荣誉列表
				if ($result){
					redirect('/School/boardsList/type/boardPhotoList/banjiId/'.$result, 0, '页面跳转中...');exit;
				}
				
				//显示班级列表
				$this->display("boardBanjiList");//列出班级
				break;
			case "boardPhotoList":
				$banjiId=I('request.banjiId',0,"int");
				//var_dump($banjiId);
				
				//找到板报（也就是默认的一个相册），没有的话创建一个
				$boardModel = D('SchoolBoards');
				
				//确保每个班级均有板报，没有的话会自动创建
				$result = $boardModel->checkBoard($banjiId);
				
				$datas_board = $boardModel->where("banjiId=".$banjiId)->find();
				
				$SchoolBanjiModel = D('SchoolBanji');
				$datas_banji = $SchoolBanjiModel->where("id=".$banjiId)->find();
				$banjiName = $datas_banji['name'];
				$this->assign("banjiName",$banjiName);
				$this->assign("banjiId",$banjiId);
				
				//显示本班级的板报照片
				$boardPhotoModel = D("SchoolBoardPhoto");
				$datas_photo = $boardPhotoModel->where("banjiId=".$banjiId)->select();
				foreach($datas_photo as $k=>$v){
					$datas_photo[$k]['filepath'] = "board/".$banjiId."/".$v['fileName'];
				}
				$this->assign("photos",$datas_photo);
				
				$this->assign("board",$datas_board);
				$this->display("boardPhotoList");
			

				break;

			default:
				exit;
		}
		exit;

		$this->display("School/boardsList");
	}		
		
	/**
	 * 添加板报
	 * author:zjh
	*/
     /*public function addBoard() {
        if (IS_POST) {
			$Model = D('SchoolBoards');
        } else {
            $this->display("editBoard");
        }
	}*/
		
	/**
	 * 编缉板报	
	 * author:zjh
	*/
   /* public function editBoard() {
        if (IS_POST) {
			$model = D('SchoolBoards');
        } else {
            $id = I('get.id',0,'intval');
			$this->display("editBoard");
        }
    }*/
		
		
	/**
	 * 删除板报
	 * author:zjh
	*/
    /*public function delBoard() {
        $id = I('get.id', 0, 'int');
        if (!$id) {
            $this->error('非法操作！');
        }
        
        $model = D('SchoolBoards');
        $result = $model->where(array('id'=>$id))->find();
        if (!$result) {
            $this->error('非法操作，不存在该记录！');
        }
 
        // 包含子分类的父级分类不能删除
        
        $delResult = $model->where(array('id'=>$id))->delete();
        if ($delResult !== false) {
           $this->success('操作成功！', U('School/boardsList'));
        } else {
           $this->error('操作失败！[原因]：' . $model->getError());
        }	
	}*/
	
	
	
	/**
	 * 板报分类列表，分页有问题
	 * author:zjh
	*/
     public function boardClass() {
        $boardClassModel = D('SchoolBoardsClass');
		
        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
		$where = array();
		//$where['Pid'] = 0;
        $totals = $boardClassModel->where($where)->count();
        $Page = new Page($totals, 10);
		//分页跳转的时候保证查询条件
		foreach($map as $key=>$val) {
			$Page->parameter[$key] = urlencode($val);
		}
        $show = $Page->show();
        $this->assign('page', $show);
		

        $originTypes = $boardClassModel->order('id asc')->select();
        $datas = array();
        $boardClassModel->sortedTypes($datas, $originTypes);
        //var_dump($datas);
        $this->assign('datas', $datas);

		$this->display("boardClass");
	}	
	
	
	/**
	 * 添加板报分类
	 * author:zjh
	*/
    public function addBoardClass() {
        if (IS_POST) {
			// 处理表单提交参数
			$name = I('post.name');
			$pid = I('post.pid', 0, 'int');
			$sort = I('post.sortnum', 0, 'int');
			
			// 实例化分类模型
			$boardClassModel = D('SchoolBoardsClass');
			$data['name'] = $name;
							import('ORG.Util.String');
							$uuid = String::uuid();
							$uuid = str_replace("{","",$uuid);
							$uuid = str_replace("}","",$uuid);
			$data['uuid'] =	$uuid;//import('ORG.Util.String');
			$data['pid'] = $pid;
			$data['sort'] = $sort;
			
			// 执行操作
			$result = $boardClassModel->data($data)->add();
			if ($result !== FALSE) {
				$this->success('操作成功！', U('School/boardClass'));
			} else {
				$this->error('操作失败！[原因]：' . $boardClassModel->getError());
			}
        } else {
			
			$classInfo = array();
			$classInfo['sortnum'] = 0;
			$this->assign('classInfo', $classInfo);
            
            // 获取父级分类数据
            $boardClassModel = D('SchoolBoardsClass');
            $originTypes = $boardClassModel->order('Pid asc, ID asc')->select();
            $class = array();
            $boardClassModel->sortedTypes($class, $originTypes);
            $this->assign('class', $class);
            
            $this->display('editBoardClass');
        }
    }	
	
	/**
	 * 修改板报分类
	 * author:zjh
	*/
	public function editBoardClass(){
        if (IS_POST) {
			// 处理表单提交参数
			$id = I('post.id', 0, 'int');
			$name = I('post.name');
			$pid = I('post.pid', 0, 'int');
			$sort = I('post.sortnum', 0, 'int');
			
			// 实例化分类模型
			$boardClassModel = D('SchoolBoardsClass');
			
			$data['name'] = $name;
			$data['pid'] = $pid;
			$data['sort'] = $sort;
			
			$map=array();
			$map['id']=$id;
            $result = $boardClassModel->where($map)->save($data);
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/boardClass'));
			} else {
			   $this->error('操作失败！[原因]：' . $boardClassModel->getError());
			}            
			
        } else {
            
            $id = I('get.id', 0, 'int');
            if (!$id) {
                $this->error('非法操作！');
            }
            $boardClassModel = D('SchoolBoardsClass');
            $classInfo = $boardClassModel->where(array('id'=>$id))->find();
            if (!$classInfo) {
                $this->error('非法操作！');
            }
            
            $this->assign('classInfo', $classInfo);//var_dump($classInfo);
			
            // 获取父级分类数据
            //$boardClassModel = D('SchoolBoardsClass');
            $originTypes = $boardClassModel->order('pid asc, id asc')->select();
            $class = array();
            $boardClassModel->sortedTypes($class, $originTypes);
            $this->assign('class', $class);
            
            $this->display('editBoardClass');
        }
	}
	
	/**
	 * 删除板报分类
	 * author:zjh
	*/
    public function delBoardClass() {
        $id = I('get.tid', 0, 'int');
        if (!$id) {
            $this->error('非法操作！');
        }
        
        $boardClassModel = D('SchoolBoardsClass');
        $boardClassInfo = $boardClassModel->where(array('id'=>$id))->find();
        if (!$boardClassInfo) {
            $this->error('非法操作，不存在该分类！');
        }
 
         // 包含子分类的父级分类不能删除
        $childrenClass = $boardClassModel->where(array('pid'=>$id))->find();//有下级分类
        if ($childrenClass) {
            $this->error('请先删除下级分类！');
        }   
        $childrenNotice = D('SchoolNotice')->where(array('classId'=>$boardClassInfo['id']))->find();//分类下有板报
        if ($childrenClass) {
            $this->error('该分类下有板报，请先将板报从此分类下移除！');
        }   		
		/*
        if ($childrenClass || $childrenNotice) {
            $this->error('该分类非空，不允许删除！');
        }*/
        
        $delResult = $boardClassModel->where(array('id'=>$id))->delete();
        if ($delResult !== false) {
           $this->success('操作成功！', U('School/boardClass'));
        } else {
           $this->error('操作失败！[原因]：' . $boardClassModel->getError());
        }	
	}		
	
	
	/**
	 * 留言列表
	 *　author:zjh
	*/
	public function messageList(){
		$messageModel = D('SchoolMessage');
		$studentModel = D('SchoolStudents');
		$teacherModel = D('SchoolTeachers'); 
		$banjiModel = D('SchoolBanji');
		$userModel = D("User");
		$model_recv_user = new Model();//TB_Sch_Message_Recv_User
		$model_message = D('SchoolMessage');
		
		//处理批量删除
		$dotype = I("request.dotype");//echo "dotype=".$dotype;
		$strIds = I('get.ids', '', 'strip_tags');
	//	var_dump($strIds);
		$arrIds = explode(",",$strIds);
		if (!empty($strIds)){
			$model_recv_user = new Model();
			
			foreach($arrIds as $k=>$v){
				
				if(session("username") == C('ADMIN_AUTH_KEY')){
					//删除：留言-学生关联表	
					$map = array();
					$map['messageId'] = $v;	
					$model_recv_user->table("TB_Sch_Message_Recv_User")->where($map)->delete();
					
					//删除：留言			
					$map_msg = array();
					$map_msg['id'] = $v;
					$model_message->where($map_msg)->delete();	
				} else {
					//查询本留言的作者，防止删除别人的
					$map_msg = array();
					$map_msg['id'] = $v;
					$map_msg['authorId'] = session(C('USER_AUTH_KEY'));
					$datas = $model_message->where($map_msg)->find();
					if (!$datas){
						$this->error("无符合条件的记录");
					}
					
					//删除：留言-学生关联表	
					$map = array();
					$map['messageId'] = $v;	
					$model_recv_user->table("TB_Sch_Message_Recv_User")->where($map)->delete();
					
					//删除：留言			
					$map_msg = array();
					$map_msg['id'] = $v;
					$map_msg['authorId'] = session(C('USER_AUTH_KEY'));	
					$model_message->where($map_msg)->delete();				
					
				}
				

			}
			
		}
		
		
		
        // 加载数据分页类
        import('ORG.Util.Page');
		$map = array();

		//教师能看到所管理班级的全部留言
		if(session("username") == C('ADMIN_AUTH_KEY')){
			;//无条件
		} else {
			if (session('user_type') == "teacher"){
				//$teacherId = session('refer_id');
				$map['TB_Sch_Message.authorId'] = session(C('USER_AUTH_KEY'));
			}else{
				//一般管理，未绑定教师ID
				$map['TB_Sch_Message.authorId'] = session(C('USER_AUTH_KEY'));
			}
		}
		$totals = $messageModel->where($map)->count();
		$Page = new Page($totals, 10);
		$show = $Page->show();
		$this->assign('page', $show);
		$datas = $messageModel->table("TB_Sch_Message")->where($map)->field("TB_Sch_Message.id,TB_Sch_Message.authorId,convert(VARCHAR(24),TB_Sch_Message.datetime,120) as datetime,TB_Sch_Message.content")->order("id DESC")->limit($Page->firstRow.','.$Page->listRows)->select(); 
		foreach($datas as $k=>$v){
			//发送者：authorId可能是老师，也可能是非老师的管理员
			$user = $userModel->where("id=".$v['authorId'])->find();
			if ($user['type']=='teacher'){
				$datas[$k]['authorType'] = '[老师]';
				$data_teacher = $teacherModel->where("id=".$user['referId'])->find();
				$datas[$k]['author'] = $data_teacher['name'];
			}else{
				$datas[$k]['authorType'] = '[管理]';
				$datas[$k]['author'] = session("username");
			}
			
			//查询接收者列表（同一消息可能发给多个学生）
			$datas_receiver = array();
			$model_recv_user = new Model();
			$datas_receiver = $model_recv_user ->table("TB_Sch_Message_Recv_User")->where("messageId=".$v['id'])->field("receiverId")->order("receiverId DESC")->select();
			$u = array();				
			foreach($datas_receiver as $kk=>$vv){
				$user = array();
				$datas_stu = array();
				$referId=0;
				
				//接收者肯定是学生，不再判断
				$model_stu = D("SchoolStudents");
				$datas_stu = $model_stu->where("id=".$vv['receiverId'])->field("name")->find();
				$u[] = $datas_stu['name'];
			}
			$stu = array();
			if ($u){
				$stu = implode(",",$u);
				//echo "本消息发送目标：".$stu;
				$datas[$k]['receiver'] = $stu;
			}
			//接收者
			//$datas[$k]['receiver'] = "a,b,c";
			
			
			
		}

		//班级列表 START
		$banjiModel = D('SchoolBanji');
		$map = array();
		$banjis = $banjiModel->getList($map);
		$this->assign('banjis', $banjis);
		//班级列表 END
		
		//留言者类型
		$authortype = array(array("id"=>1,"name"=>"学生"),array("id"=>2,"name"=>"教师"));
		$this->assign('authortype', $authortype);
		
		$this->assign('datas', $datas);
		$this->display("School/messageList");
	}		
		
	/**
	 * 添加留言
	 * author:zjh
	*/
     public function addMessage() {
        if (IS_POST) {
			$Model = D('SchoolMessage');
			$studentIdArr=I('post.studentIdStr');
			$model_user = new Model();
			
			$data = array();
			$data['content'] = I('post.content');
			$data['datetime'] = date("Y/m/d h:i:s", mktime());
			$data['authorId'] = session(C('USER_AUTH_KEY'));//留言者为当前登陆用户ID
			$result = $Model->data($data)->add();

			//留言存储成功
			if ($result){
				//存储留言-学生关联表
				$model_recv_user = new Model();
				foreach ($studentIdArr as $k=>$v){
					//关联表
					$data = array();
					$data['messageId'] = $result;
					$data['receiverId'] = $v;//这儿是登陆用户的id
					$data['status'] = 0;
					
					$model_recv_user->table("TB_Sch_Message_Recv_User")->data($data)->add();
				}
				$this->success('操作成功！', U('School/messageList'));			
			}else{
				$this->error('操作失败！[原因]：' . $Model->getError());
			}
        } else {
			//分类
			$messageClassModel = D('SchoolMessageClass');
			$originClass = $messageClassModel->order('pid asc, id asc')->select();
			$class = array();
			$messageClassModel->sortedTypes($class, $originClass);
			$this->assign('class', $class);
			
			$stuModel = D('SchoolStudents');
			$map = array();
			$map_bj = array();
			
			if(session("username") == C('ADMIN_AUTH_KEY')){
				//超级管理员可显示所有班级
			} else {
				//获取到可管理班级
				$user_banji_str = session("user_banji_list");//$map['banjiId'] = array("IN",$user_banji_str);
				$map['banjiId'] = array("IN",$user_banji_str);
				$map_bj['id'] = array("IN",$user_banji_str);
			}

			//班级列表 START
			$banjiModel = D('SchoolBanji');
			//$map_bj = array();
			$banjis = $banjiModel->where($map_bj)->select();
			//班级列表 END
			
			
			//学生列表 START
			$stuModel = D('SchoolStudents');
			$students = $stuModel->where($map)->order("id DESC")->field("id,name,banjiId")->select();
			//$this->assign('students', $students);
			$out = array();
			$out['stat'] = "1";
			$out['data'] = $students;
			//学生列表 EN	
			
			$end = array();
			//重新组织
			//最外部是班级，banjiId,banjiName,studentCount,studentList
			//studentList中是本班的全部符合条件的学生
			//其它，未分到班级的都显示在其它，其它也算是一个班级了
			foreach($banjis as $k=>$v){		
				$student_list = array();
				$studentCount = 0;
				foreach($students as $kk=>$vv){
					//判断该学生的banjiId
					if ($v['id']==$vv['banjiId']){
						$student_list[] = $vv;
						$studentCount ++;
					}
				}
				$banji_one = array("banjiId"=>$v['id'],"banjiName"=>$v['name'],"studentCount"=>$studentCount,"studentList"=>$student_list);
				$end[] = $banji_one;
			}
			
			//不属任何一个班级
			if(session("username") == C('ADMIN_AUTH_KEY')){
				$banji_other = array();//其它（作为一个科目）
				$student_list_other = array();//归入其它的全部教师
				$studentCountOther = 0;
				foreach($students as $kk=>$vv){
					if (!$vv['banjiId']){
						$student_list_other[] = $vv;
						$studentCountOther ++;
					}
				}
				$banji_other = array("banjiId"=>0,"banjiName"=>"其它","studentCount"=>$studentCountOther,"studentList"=>$student_list_other);
				$end[] = $banji_other;
			}
			$this->assign("data_banji_student_model",$end);
			$this->banjis = $banjis;

			
            // 
            $this->display("editMessage");
        }
		 
		
	}		
		
	/**
	 * 编缉留言	
	 * author:zjh
	*/
    public function editMessage() {
        
        if (IS_POST) {
			$model = D('SchoolMessage');
			$model_user = new Model();
			
			$id = I("post.id",0,"int");
			
			$studentIdArr=I('post.studentIdStr');
			//$studentId = intval(implode(',',$studentIdArr));//弹窗中设为单选，此处收到的只是一个数值
			
			$data = array();		
			$data['id'] = I('post.id', 0, 'int');	
			$data['content'] = I('post.content');
		//	$data['datetime'] = date("Y/m/d h:i:s", mktime());
		//	$data['authorId'] = session(C('USER_AUTH_KEY'));
            $result = $model->save($data);
			
			if ($result){
				//
				$model_recv_user = new Model();
				
				//删除全部本留言的关联，然后新建关联
				$model_recv_user->table("TB_Sch_Message_Recv_User")->where("messageId=".$id)->delete();
				
				foreach ($studentIdArr as $k=>$v){
					//留言-学生关联表
					$data = array();
					$data['messageId'] = $id;
					$data['receiverId'] = $v;//studentId
					$data['status'] = 0;
					
					$model_recv_user->table("TB_Sch_Message_Recv_User")->data($data)->add();
				}
			}
			
			if ($result) {
			   $this->success('操作成功！', U('School/messageList'));
			} else {
			   $this->error('操作失败！[原因]：' . $model->getError());
			}
        } else {
            $id = I('get.id',0,'intval');
			
			$stuModel = D('SchoolStudents');
			$map = array();
			$map_bj = array();
			
			if(session("username") == C('ADMIN_AUTH_KEY')){
				//超级管理员可显示所有班级
			} else {
				//获取到可管理班级
				$user_banji_str = session("user_banji_list");//$map['banjiId'] = array("IN",$user_banji_str);
				$map['banjiId'] = array("IN",$user_banji_str);
				$map_bj['id'] = array("IN",$user_banji_str);
			}
			
			//班级列表 START
			$banjiModel = D('SchoolBanji');
			$map_bj = array();
			$banjis = $banjiModel->where($map_bj)->select();
			//班级列表 END
			
			
			//学生列表 START
			$stuModel = D('SchoolStudents');
			$students = $stuModel->where($map)->order("id DESC")->field("id,name,banjiId")->select();
			//$this->assign('students', $students);
			$out = array();
			$out['stat'] = "1";
			$out['data'] = $students;
			//学生列表 EN	
			
			$end = array();
			//重新组织
			//最外部是班级，banjiId,banjiName,studentCount,studentList
			//studentList中是本班的全部符合条件的学生
			//其它，未分到班级的都显示在其它，其它也算是一个班级了
			foreach($banjis as $k=>$v){		
				$student_list = array();
				$studentCount = 0;
				foreach($students as $kk=>$vv){
					//判断该学生的banjiId
					if ($v['id']==$vv['banjiId']){
						$student_list[] = $vv;
						$studentCount ++;
					}
				}
				$banji_one = array("banjiId"=>$v['id'],"banjiName"=>$v['name'],"studentCount"=>$studentCount,"studentList"=>$student_list);
				$end[] = $banji_one;
			}
			
			//不属任何一个班级
			$banji_other = array();//其它（作为一个科目）
			$student_list_other = array();//归入其它的全部教师
			$studentCountOther = 0;
			foreach($students as $kk=>$vv){
				if (!$vv['banjiId']){
					$student_list_other[] = $vv;
					$studentCountOther ++;
				}
			}
			$banji_other = array("banjiId"=>0,"banjiName"=>"其它","studentCount"=>$studentCountOther,"studentList"=>$student_list_other);
			$end[] = $banji_other;
			$this->assign("data_banji_student_model",$end);
		
			
			//留言
			$model = D('SchoolMessage');
			$map['id'] = $id;
			$datas = $model->where($map)->find();
			
			if ($datas){
				$model_recv_user = new Model();
				$datas_rcv = array();
				$rcv_studentid_arr = array();//接收者id数组
				
				$map_rcv = array();
				$map_rcv['messageId'] = $id;
				
				//所有接收者ID
				$datas_rcv = $model_recv_user->table("TB_Sch_Message_Recv_User")->where($map_rcv)->field("receiverId")->select();//receiverId现在等于studentId
				if ($datas_rcv){
					$model_stu = D('SchoolStudents');
					
					//接收者数组
					foreach($datas_rcv as $k=>$v){
						$rcv_studentid_arr[] = $v['receiverId'];
					}	
					
					//接收者ID数组转字符串
					$str_recv_studentId = implode(",",$rcv_studentid_arr);//逗号分隔的学生Id
					//echo $str_recv_studentId;
					
					//获取学生姓名
					$map = array();
					$map['id'] = array("IN",$str_recv_studentId);
					$recv = $model_stu->where($map)->field("id,name")->order("id DESC")->select();
					$student_list = $recv;
					$this->assign("student_list",$student_list);
					
					$strStudentsId = $str_recv_studentId;
					$this->assign("strStudentsId",$strStudentsId);
				}


			}
			
				
			

		//	var_dump($datas_rcv_uid);			

			$this->assign('datas_rcv_uid', $datas_rcv_uid);	//接收者id数组
			$this->assign("status",$datas['status']);
			$this->assign('datas', $datas);			
			$this->banjis = $banjis;
			$this->display("editMessage");
			
        }
    }			
		
	/**
	 * 预览留言
	 * author:zjh
	*/
	public function showMessage(){
		$id = I("request.id",0,"int");
		if (!$id){$this->error("参数错误");}

		$model = D('SchoolMessage');
		$datas = array();
		$map = array();
		
	//	if(session("username") == C('ADMIN_AUTH_KEY')){
			//超级管理员可显示所有班级
	//	} else {
			//登陆用户：老师还是学生
	//		switch (session('user_type')){
	//			case "teacher"://老师后台
					$map['TB_Sch_Message.id'] = $id;
					$datas = $model->table("TB_Sch_Message")->where($map)->field("TB_Sch_Message.id,TB_Sch_Message.authorId,convert(VARCHAR(24),TB_Sch_Message.datetime,120) as datetime,TB_Sch_Message.content")->find();
					
					if(session("username") == C('ADMIN_AUTH_KEY')){
						//超级管理员可显示所有班级
					} else {
						if ($datas['authorId'] != session(C('USER_AUTH_KEY'))){//留言者等于当前用户ID
							$this->error("作为老师，只能查看自己发出的留言");
						}
					}
					//留言者
					$author = M()->table("tb_users")->where("id=".$datas['authorId'])->find();
				//	echo "id=".$id.",authorId=".$datas['authorId'];
					$tp = "";//称谓：老师/同学
					switch ($author['type']){
						case "teacher":
							$tmp_data = M()->table("TB_Sch_Teachers")->where("id=".$author['referId'])->find();
							$tp = '[老师]';
			
							break;
						/*case "student";
							$tmp_data = M()->table("TB_Sch_Students")->where("id=".$author['referId'])->find();
							$tp = '[同学]';
							break;*/
						default:
							$tmp_data['name'] = $author['account'];
							$tp = '-';
					}
			
					$datas['author'] = $tmp_data['name'].$tp;//$author['account'];
					
					//查询接收者列表（同一消息可能发给多个学生）
					$datas_receiver = array();
					$datas_receiver = M()->table("TB_Sch_Message_Recv_User")->where("messageId=".$id)->field("receiverId")->select();
					$u = array();
					foreach($datas_receiver as $k=>$v){
						//获取绑定的referId
						//$user = M()->table("tb_users")->where("id=".$v['receiverId'])->field("type,referId")->find();
						//$referId = $user['referId'];
						
						//接收者肯定是学生，不再判断
						$model_stu = D("SchoolStudents");
						$datas_stu = $model_stu->where("id=".$v['receiverId'])->field("name")->find();
						$u[] = $datas_stu['name'];
					}
					if ($u){
						$stu = implode("，",$u);
						//echo "本消息发送目标：".$stu;
						$datas['receiver'] = $stu;
						$datas['receiverNumber'] = count($u);
					}
					
					
					//由用户id获取用户
					//var_dump($datas_receiver);
					
		//			break;
			/*	case "student"://学生后台
					//echo 'TB_Sch_Message.id='.$id.'<br>';
					$map['TB_Sch_Message.id'] = $id;
					$map['TB_Sch_Message_Recv_User.receiverId'] = session(C('USER_AUTH_KEY'));
					$datas = $model->table("TB_Sch_Message_Recv_User")->where($map)->field("TB_Sch_Message.id,TB_Sch_Message.authorId,TB_Sch_Message_Recv_User.receiverId,convert(VARCHAR(24),TB_Sch_Message.datetime,120) as datetime,TB_Sch_Message.content")->join("TB_Sch_Message ON TB_Sch_Message_Recv_User.messageId=TB_Sch_Message.id")->find();
					if ($datas['receiverId'] != session(C('USER_AUTH_KEY'))){//接收者等于当前用户ID
						echo "允许查看者：".$datas['receiverId'];
						$this->error("作为学生，只能查看给自己的留言");
					}
					
					//更新状态
					$map  = array();
					$map['messageId'] = $id;
					$map['receiverId'] = session(C('USER_AUTH_KEY'));//这样不对，不知道为什么
					$result = $model->table("TB_Sch_Message_Recv_User")->where("messageId=".$id ." and receiverId=".session(C('USER_AUTH_KEY')))->field("id")->find();
					if ($result){
						//找到主id
						$rcv_id	= $result['id'];
						//echo "关联表的id=".$rcv_id."<br>";
					}
					
					$model_recv_user = M();
					$data_update = array();
					$data_update['status'] = 1;

					M()->query("update TB_Sch_Message_Recv_User set status = '1' where id=".$rcv_id);//更新状态为已读
					
					//留言者
					$author = M()->table("tb_users")->where("id=".$datas['authorId'])->find();
					//echo "id=".$id.",authorId=".$datas['authorId'];
			
					$tp = "";//称谓：老师/同学
					switch ($author['type']){
						case "teacher":
							$tmp_data = M()->table("TB_Sch_Teachers")->where("id=".$author['referId'])->find();
							$tp = '[老师]';
			
							break;
						case "student";
							$tmp_data = M()->table("TB_Sch_Students")->where("id=".$author['referId'])->find();
							$tp = '[同学]';
							break;
						default:
							$tmp_data['name'] = $author['account'];
							$tp = '-';
					}
			
					$datas['author'] = $tmp_data['name'].$tp;//$author['account'];
					$datas['receiver'] = "我";
					break;
					*/
		//		default:
		//			$this->error("错误的用户类型");
		//			return;
		//	}
		//}

		
		//权限判断
		//
		
		
		$this->assign("datas",$datas);
		$this->display("message_show");
		
	}	
		
		
	/**
	 * 删除留言
	 * author:zjh
	*/
    public function delMessage() {
        $id = I('get.id', 0, 'int');
        if (!$id) {
            $this->error('非法操作！');
        }
		
		//$this->check_banji($banjiId);//是否可操作此班级ID
        
        $model = D('SchoolMessage');
        $result = $model->where(array('id'=>$id,'authorId'=>session(C('USER_AUTH_KEY'))))->find();
        if (!$result) {
            $this->error('非法操作，不存在该记录！');
        }
		
 
        // 删除接收关联表
		M()->table("TB_Sch_Message_Recv_User")->where("messageId=".$id)->delete();
        
        $delResult = $model->where(array('id'=>$id))->delete();
        if ($delResult !== false) {
           $this->success('操作成功！', U('School/messageList'));
        } else {
           $this->error('操作失败！[原因]：' . $model->getError());
        }	
	}			
	
	
	
	/**
	 * 留言分类列表，分页有问题
	 * author:zjh
	*/
     public function messageClass() {
        $messageClassModel = D('SchoolMessageClass');
		
        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
		$where = array();
		//$where['Pid'] = 0;
        $totals = $messageClassModel->where($where)->count();
        $Page = new Page($totals, 10);
		//分页跳转的时候保证查询条件
		foreach($map as $key=>$val) {
			$Page->parameter[$key] = urlencode($val);
		}
        $show = $Page->show();
        $this->assign('page', $show);
		

        $originTypes = $messageClassModel->order('sort asc,pid asc, id asc')->select();
        $datas = array();
        $messageClassModel->sortedTypes($datas, $originTypes);
        //var_dump($datas);
        $this->assign('datas', $datas);

		$this->display("messageClass");
	}	
	
	
	/**
	 * 添加留言分类
	 * author:zjh
	*/
    public function addMessageClass() {
        if (IS_POST) {
			// 处理表单提交参数
			$name = I('post.name');
			$pid = I('post.pid', 0, 'int');
			$sort = I('post.sortnum', 0, 'int');
			
			// 实例化分类模型
			$messageClassModel = D('SchoolMessageClass');
			$data['name'] = $name;
							import('ORG.Util.String');
							$uuid = String::uuid();
							$uuid = str_replace("{","",$uuid);
							$uuid = str_replace("}","",$uuid);
			$data['uuid'] =	$uuid;//import('ORG.Util.String');
			$data['pid'] = $pid;
			$data['sort'] = $sort;
			
			// 执行操作
			$result = $messageClassModel->data($data)->add();
			if ($result !== FALSE) {
				$this->success('操作成功！', U('School/messageClass'));
			} else {
				$this->error('操作失败！[原因]：' . $messageClassModel->getError());
			}
        } else {
			
			$classInfo = array();
			$classInfo['sortnum'] = 0;
			$this->assign('classInfo', $classInfo);
            
            // 获取父级分类数据
            $messageClassModel = D('SchoolMessageClass');
            $originTypes = $messageClassModel->order('Pid asc, ID asc')->select();
            $class = array();
            $messageClassModel->sortedTypes($class, $originTypes);
            $this->assign('class', $class);
            
            $this->display('editMessageClass');
        }
    }	
	
	/**
	 * 修改留言分类
	 * author:zjh
	*/
	public function editMessageClass(){
        if (IS_POST) {
			// 处理表单提交参数
			$id = I('post.id', 0, 'int');
			$name = I('post.name');
			$pid = I('post.pid', 0, 'int');
			$sort = I('post.sortnum', 0, 'int');
			
			// 实例化分类模型
			$messageClassModel = D('SchoolMessageClass');
			
			$data['name'] = $name;
			$data['pid'] = $pid;
			$data['sort'] = $sort;
			
			$map=array();
			$map['id']=$id;
            $result = $messageClassModel->where($map)->save($data);
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/messageClass'));
			} else {
			   $this->error('操作失败！[原因]：' . $messageClassModel->getError());
			}            
			
        } else {
            
            $id = I('get.id', 0, 'int');
            if (!$id) {
                $this->error('非法操作！');
            }
            $messageClassModel = D('SchoolMessageClass');
            $classInfo = $messageClassModel->where(array('id'=>$id))->find();
            if (!$classInfo) {
                $this->error('非法操作！');
            }
            
            $this->assign('classInfo', $classInfo);//var_dump($classInfo);
			
            // 获取父级分类数据
            //$messageClassModel = D('SchoolMessageClass');
            $originTypes = $messageClassModel->order('pid asc, id asc')->select();
            $class = array();
            $messageClassModel->sortedTypes($class, $originTypes);
            $this->assign('class', $class);
            
            $this->display('editMessageClass');
        }
	}
	
	/**
	 * 删除留言分类
	 * author:zjh
	*/
    public function delMessageClass() {
        $id = I('get.tid', 0, 'int');
        if (!$id) {
            $this->error('非法操作！');
        }
        
        $messageClassModel = D('SchoolMessageClass');
        $messageClassInfo = $messageClassModel->where(array('id'=>$id))->find();
        if (!$messageClassInfo) {
            $this->error('非法操作，不存在该分类！');
        }
 
         // 包含子分类的父级分类不能删除
        $childrenClass = $messageClassModel->where(array('pid'=>$id))->find();//有下级分类
        if ($childrenClass) {
            $this->error('请先删除下级分类！');
        }   
        $childrenNotice = D('SchoolNotice')->where(array('classId'=>$messageClassInfo['id']))->find();//分类下有留言
        if ($childrenClass) {
            $this->error('该分类下有留言，请先将留言从此分类下移除！');
        }   		
		/*
        if ($childrenClass || $childrenNotice) {
            $this->error('该分类非空，不允许删除！');
        }*/
        
        $delResult = $messageClassModel->where(array('id'=>$id))->delete();
        if ($delResult !== false) {
           $this->success('操作成功！', U('School/messageClass'));
        } else {
           $this->error('操作失败！[原因]：' . $messageClassModel->getError());
        }	
		
			
	}
	


	
	/**
	 * 评比活动列表
	 *　author:zjh
	*/
	public function pinbiList(){
		$model = D('SchoolPinbi');

		$map = array();
		$map['id'] = array('GT',0);
		
        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
        $totals = $model->where($map)->count();
        $Page = new Page($totals, 10);
        $show = $Page->show();
        $this->assign('page', $show);	
		
		$datas = $model->where($map)->field("id,classId,title,gradeId,banjiId,status,convert(VARCHAR(24),startTime,120) as starttime,convert(VARCHAR(24),endTime,120) as endtime,convert(VARCHAR(24),createTime,120) as createtime,convert(VARCHAR(24),updateTime,120) as updatetime")->order("id DESC")->limit($Page->firstRow.','.$Page->listRows)->select(); 
		
		//重新组织信息
		$pinbiClassModel = D('SchoolPinbiClass');
		$gradeModel = D('SchoolGrade');
		$banjiModel = D('SchoolBanji');
		
		foreach ($datas as $k => $v){
			$one = $gradeModel->where("id=".$v['gradeId'])->find();
			$datas[$k]['gradeName'] = $one['name'];
			
			$one = $banjiModel->where("id=".$v['banjiId'])->find();
			$datas[$k]['banjiName'] = $one['name'];			
			
			//classid为整数，只有一值时
			$one = $pinbiClassModel->where("id=".$datas[$k]['classId'])->find();
			$datas[$k]['className'] = $one['name'];
			
			switch ($v['status'])
			{
				case 1:
				$datas[$k]['status']="<font >已通过</font>";
				break;  
			case 2:
				$datas[$k]['status']="<font color='red'>未通过</font>";
				break;
			default:
				$datas[$k]['status']="<font color='green'>待审核</font>";
			}

		}
		
		//年级列表 START
		$gradeModel = D('SchoolGrade');
		$map = array();
		$grades = $gradeModel->getList($map);
		$this->assign('grades', $grades);
		//年级列表 END
		
		//班级列表 START
		$banjiModel = D('SchoolBanji');
		$map = array();
		$banjis = $banjiModel->getList($map);
		$this->assign('banjis', $banjis);
		//班级列表 END	
		
		$this->assign('datas', $datas);
		$this->display("School/pinbiList");
	}		
		
	/**
	 * 添加评比
	 * author:zjh
	*/
     public function addPinbi() {
        if (IS_POST) {
			$Model = D('SchoolPinbi');
            //$id = I('post.id', 0, 'int');
			
			$data = array();		
			//$data['id'] = I('post.id', 0, 'int');	
            $data['title'] = I('post.name');
			$data['content'] = I('post.content');
			//$data['banjiId'] = 0;
			$data['updateTime'] = date("Y/m/d h:i:s", mktime());
			$data['createTime'] = date("Y/m/d h:i:s", mktime());
			$data['classId'] = I('post.classId', 0, 'int');
			$data['status'] = 0;//未审核

			$result = $Model->data($data)->add();

		   // 执行操作
		   if ($result !== FALSE) {
			   //$insertId = $result;
			   $this->success('操作成功！', U('School/pinbiList'));
		   } else {
			   $this->error('操作失败！[原因]：' . $Model->getError());
		   }

        } else {
			//分类
			$pinbiClassModel = D('SchoolPinbiClass');
			$originClass = $pinbiClassModel->order('pid asc, id asc')->select();
			$class = array();
			$pinbiClassModel->sortedTypes($class, $originClass);
			$this->assign('class', $class);
            // 
            $this->display("editPinbi");
        }
		 
		
	}		
		
	/**
	 * 编缉评比	
	 * author:zjh
	*/
    public function editPinbi() {
        
        if (IS_POST) {
			$model = D('SchoolPinbi');
            //$id = I('post.id', 0, 'int');
			
			$data = array();		
			$data['id'] = I('post.id', 0, 'int');	
            $data['title'] = I('post.name');
			$data['mainContent'] = I('post.content');
			$data['banjiId'] = 0;
			$data['updateTime'] = date("Y/m/d h:i:s", mktime());
			//$data['createTime'] = date("Y/m/d h:i:s", mktime());
			$data['classId'] = I('post.classId', 0, 'int');
			$data['status'] = I('post.status', 0, 'int');;//未审核

           
            $result = $model->save($data);
			if ($result) {
			   $this->success('操作成功！', U('School/pinbiList'));
			} else {
			   $this->error('操作失败！[原因]：' . $model->getError());
			}            
        } else {
            $id = I('get.id',0,'intval');
			
			//分类
			$pinbiClassModel = D('SchoolPinbiClass');
			$originClass = $pinbiClassModel->order('pid asc, id asc')->select();
			$class = array();
			$pinbiClassModel->sortedTypes($class, $originClass);
			$this->assign('class', $class);
			
			
			$model = D('SchoolPinbi');
			$map['id'] = $id;
			$datas = $model->field("id,title,status,mainContent,classId,convert(VARCHAR(24),startTime,120) as starttime,convert(VARCHAR(24),endTime,120) as endtime")->where($map)->find();
			
            if ($datas) {
				$this->assign("status",$datas['status']);
				$this->assign('datas', $datas);

            }
			$this->display("editPinbi");   
        }
    }			
		
	/**
	 * 预览评比
	 * author:zjh
	*/
	public function showPinbi(){
		
		}	
		
		
	/**
	 * 删除评比
	 * author:zjh

	*/
    public function delPinbi() {
        $id = I('get.id', 0, 'int');
        if (!$id) {
            $this->error('非法操作！');
        }
        
        $model = D('SchoolPinbi');
        $result = $model->where(array('id'=>$id))->find();
        if (!$result) {
            $this->error('非法操作，不存在该记录！');
        }
 
        // 包含子分类的父级分类不能删除
        
        $delResult = $model->where(array('id'=>$id))->delete();
        if ($delResult !== false) {
           $this->success('操作成功！', U('School/pinbiList'));
        } else {
           $this->error('操作失败！[原因]：' . $model->getError());
        }	
	}			
	
	
	
	/**
	 * 评比分类列表，分页有问题
	 * author:zjh
	*/
     public function pinbiClass() {
        $pinbiClassModel = D('SchoolPinbiClass');
		
        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
		$where = array();
		//$where['Pid'] = 0;
        $totals = $pinbiClassModel->where($where)->count();
        $Page = new Page($totals, 10);
		//分页跳转的时候保证查询条件
		foreach($map as $key=>$val) {
			$Page->parameter[$key] = urlencode($val);
		}
        $show = $Page->show();
        $this->assign('page', $show);
		

        $originTypes = $pinbiClassModel->order('sort asc,pid asc, id asc')->select();
        $datas = array();
        $pinbiClassModel->sortedTypes($datas, $originTypes);
        //var_dump($datas);
        $this->assign('datas', $datas);

		$this->display("pinbiClass");
	}	
	
	
	/**
	 * 添加评比分类
	 * author:zjh
	*/
    public function addPinbiClass() {
        if (IS_POST) {
			// 处理表单提交参数
			$name = I('post.name');
			$pid = I('post.pid', 0, 'int');
			$sort = I('post.sortnum', 0, 'int');
			
			// 实例化分类模型
			$pinbiClassModel = D('SchoolPinbiClass');
			$data['name'] = $name;
							import('ORG.Util.String');
							$uuid = String::uuid();
							$uuid = str_replace("{","",$uuid);
							$uuid = str_replace("}","",$uuid);
			$data['uuid'] =	$uuid;//import('ORG.Util.String');
			$data['pid'] = $pid;
			$data['sort'] = $sort;
			
			// 执行操作
			$result = $pinbiClassModel->data($data)->add();
			if ($result !== FALSE) {
				$this->success('操作成功！', U('School/pinbiClass'));
			} else {
				$this->error('操作失败！[原因]：' . $pinbiClassModel->getError());
			}
        } else {
			
			$classInfo = array();
			$classInfo['sortnum'] = 0;
			$this->assign('classInfo', $classInfo);
            
            // 获取父级分类数据
            $pinbiClassModel = D('SchoolPinbiClass');
            $originTypes = $pinbiClassModel->order('Pid asc, ID asc')->select();
            $class = array();
            $pinbiClassModel->sortedTypes($class, $originTypes);
            $this->assign('class', $class);
            
            $this->display('editPinbiClass');
        }
    }	
	
	/**
	 * 修改评比分类
	 * author:zjh
	*/
	public function editPinbiClass(){
        if (IS_POST) {
			// 处理表单提交参数
			$id = I('post.id', 0, 'int');
			$name = I('post.name');
			$pid = I('post.pid', 0, 'int');
			$sort = I('post.sortnum', 0, 'int');
			
			// 实例化分类模型
			$pinbiClassModel = D('SchoolPinbiClass');
			
			$data['name'] = $name;
			$data['pid'] = $pid;
			$data['sort'] = $sort;
			
			$map=array();
			$map['id']=$id;
            $result = $pinbiClassModel->where($map)->save($data);
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/pinbiClass'));
			} else {
			   $this->error('操作失败！[原因]：' . $pinbiClassModel->getError());
			}            
			
        } else {
            
            $id = I('get.id', 0, 'int');
            if (!$id) {
                $this->error('非法操作！');
            }
            $pinbiClassModel = D('SchoolPinbiClass');
            $classInfo = $pinbiClassModel->where(array('id'=>$id))->find();
            if (!$classInfo) {
                $this->error('非法操作！');
            }
            
            $this->assign('classInfo', $classInfo);//var_dump($classInfo);
			
            // 获取父级分类数据
            //$pinbiClassModel = D('SchoolPinbiClass');
            $originTypes = $pinbiClassModel->order('pid asc, id asc')->select();
            $class = array();
            $pinbiClassModel->sortedTypes($class, $originTypes);
            $this->assign('class', $class);
            
            $this->display('editPinbiClass');
        }
	}
	
	/**
	 * 删除评比分类
	 * author:zjh
	*/
    public function delPinbiClass() {
        $id = I('get.tid', 0, 'int');
        if (!$id) {
            $this->error('非法操作！');
        }
        
        $pinbiClassModel = D('SchoolPinbiClass');
        $pinbiClassInfo = $pinbiClassModel->where(array('id'=>$id))->find();
        if (!$pinbiClassInfo) {
            $this->error('非法操作，不存在该分类！');
        }
 
         // 包含子分类的父级分类不能删除
        $childrenClass = $pinbiClassModel->where(array('pid'=>$id))->find();//有下级分类
        if ($childrenClass) {
            $this->error('请先删除下级分类！');
        }   
        $childrenNotice = D('SchoolNotice')->where(array('classId'=>$pinbiClassInfo['id']))->find();//分类下有评比
        if ($childrenClass) {
            $this->error('该分类下有评比，请先将评比从此分类下移除！');
        }   		
		/*
        if ($childrenClass || $childrenNotice) {
            $this->error('该分类非空，不允许删除！');
        }*/
        
        $delResult = $pinbiClassModel->where(array('id'=>$id))->delete();
        if ($delResult !== false) {
           $this->success('操作成功！', U('School/pinbiClass'));
        } else {
           $this->error('操作失败！[原因]：' . $pinbiClassModel->getError());
        }	
			
	}
	


	/**
	 * 评比结果列表
	 *　author:zjh
	*/
	public function pinbiDataList(){
		$pbId = I('get.pbid',0,'intval');
		if ($pbId){
			$pbModel = D('SchoolPinbi');
			$map = array();
			$map['id'] = $pbId;
			$infor = $pbModel->where($map)->find();
		   
			if ($infor){
				//输出本活动详情
				$this->assign("infor",$infor);//var_dump($infor);
				$this->assign("pbid",$pbId);
			} else{
				//无此评比活动
			}
		}
		
		
		$model = D('SchoolPinbiData');

		$map = array();
		$map['id'] = array('GT',0);
		if ($pbId){
			$map['pinbiId'] = array('eq',$pbId);
		}
		
        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
        $totals = $model->where($map)->count();
        $Page = new Page($totals, 10);
        $show = $Page->show();
        $this->assign('page', $show);	
		
		$datas = $model->where($map)->order("id DESC")->limit($Page->firstRow.','.$Page->listRows)->select(); 
		
		//重新组织信息
		$pinbiClassModel = D('SchoolPinbiClass');
		$gradeModel = D('SchoolGrade');
		$banjiModel = D('SchoolBanji');
		$studentModel = D('SchoolStudents');
		$teachersModel = D('SchoolTeachers');
		foreach ($datas as $k => $v){
			$one = $gradeModel->where("id=".$v['gradeId'])->find();
			$datas[$k]['gradeName'] = $one['name'];
			
			$one = $banjiModel->where("id=".$v['banjiId'])->find();
			$datas[$k]['banjiName'] = $one['name'];		
			
			$one = $studentModel->where("id=".$v['studentId'])->find();
			$datas[$k]['studentName'] = $one['name'];	
			
			$one = $teachersModel->where("id=".$v['teacherId'])->find();
			$datas[$k]['teacherName'] = $one['name'];
			
			//classid为整数，只有一值时
			$one = $pinbiClassModel->where("id=".$datas[$k]['classId'])->find();
			$datas[$k]['className'] = $one['name'];
			
	
			
			switch ($v['status'])
			{
				case 1:
				$datas[$k]['status']="<font >已通过</font>";
				break;  
			case 2:
				$datas[$k]['status']="<font color='red'>未通过</font>";
				break;
			default:
				$datas[$k]['status']="<font color='green'>待审核</font>";
			}

		}
		
		//年级列表 START
		$gradeModel = D('SchoolGrade');
		$map = array();
		$grades = $gradeModel->getList($map);
		$this->assign('grades', $grades);
		//年级列表 END
		
		//班级列表 START
		$banjiModel = D('SchoolBanji');
		$map = array();
		$banjis = $banjiModel->getList($map);
		$this->assign('banjis', $banjis);
		//班级列表 END	
		
		$this->assign('datas', $datas);
		$this->display("School/pinbiDataList");
	}		


	/**
	 * 添加评比
	 * author:zjh
	*/
     public function addPinbiData() {
        if (IS_POST) {
			$pbid = I('post.pbid', 0, 'int');	
			
			$banjiIdArr=I('post.banjiIdStr');
			$banjiId = intval(implode(',',$banjiIdArr));//弹窗中设为单选，此处收到的只是一个数值
			
			$studentIdArr=I('post.studentIdStr');
			$studentId = intval(implode(',',$studentIdArr));//弹窗中设为单选，此处收到的只是一个数值
			
			$teacherIdArr=I('post.teacherIdStr');
			$teacherId = intval(implode(',',$teacherIdArr));//弹窗中设为单选，此处收到的只是一个数值，
			
			$model = D('SchoolPinbiData');
			$data = array();
			//$data['id'] = I('post.id', 0, 'int');	
			$data['pinbiId'] = I('post.pbid', 0, 'int');
			$data['title'] = I('post.name');
			$data['mainContent'] = I('post.mainContent');
			$data['banjiId'] = $banjiId;
			$data['teacherId'] = $teacherId;
			$data['studentId'] = $studentId;
			$data['updateTime'] = date("Y/m/d h:i:s", mktime());
			$data['createTime'] = date("Y/m/d h:i:s", mktime());
			$data['scores'] = I('post.scores', 0, 'int');
			$data['status'] = 1;//I('post.status', 0, 'int');
			
			$result = $model->data($data)->add();

		   // 执行操作
		   if ($result) {
			   $this->success('操作成功！', U('School/pinbiDataList',array("pbid"=>$pbid)));
		   } else {
			   $this->error('操作失败！[原因]：' . $model->getError());
		   }

        } else {
			$pbid = I('get.pbid',0,'intval');
			if ($pbid){
				//var_dump($pbid);
				$this->assign("pbid",$pbid);
				$pbModel = D('SchoolPinbi');
				$map = array();
				$map['id'] = $pbid;
				$infor = $pbModel->where($map)->find();
			   
				if ($infor){
					//输出本活动详情
					$this->assign("fullScores",$infor['fullScores']+1);

					$this->assign("infor",$infor);//var_dump($infor);
				} else{
					//无此评比活动
				}
			}			
			
			
			//年级列表 START
			$gradeModel = D('SchoolGrade');
			$map = array();
			$grades = $gradeModel->getList($map);
			$this->assign('grades', $grades);
			//年级列表 END
			
			//班级列表 START
			$banjiModel = D('SchoolBanji');
			$map = array();
			$banjis = $banjiModel->getList($map);
			$this->assign('banjis', $banjis);
			//班级列表 END	
			
			//教师列表 START
			$teachersModel = D('SchoolTeachers');
			$map = array();
			$teachers = $teachersModel->getList($map);
			$this->assign('teachers', $teachers);
			//列表 END	        
			
			 //学生列表 START
			$stuModel = D('SchoolStudents');
			$map = array();
			$students = $stuModel->getList($map);
			$this->assign('students', $students);
			//学生列表 END
			
			$seltype = "radio";
			$this->assign('studentModalSelType', $seltype);//学生弹出框中只允许
			
			$seltype = "radio";
			$this->assign('teacherModalSelType', $seltype);//教师选择弹出框中只允许单选			
			
			$seltype = "radio";
			$this->assign('banjiModalSelType', $seltype);//班级弹出框中只允许单选
			
			
            // 
            $this->display("editPinbiData");
        }
		 
		
	}	

	/**
	 * 修改评比结果数据
	 * author:zjh
	*/
     public function editPinbiData() {
        if (IS_POST) {
			$pbid = I('post.pbid', 0, 'int');	
			
			$banjiIdArr=I('post.banjiIdStr');
			$banjiId = intval(implode(',',$banjiIdArr));//弹窗中设为单选，此处收到的只是一个数值
			
			$studentIdArr=I('post.studentIdStr');
			$studentId = intval(implode(',',$studentIdArr));//弹窗中设为单选，此处收到的只是一个数值
			
			$teacherIdArr=I('post.teacherIdStr');
			$teacherId = intval(implode(',',$teacherIdArr));//弹窗中设为单选，此处收到的只是一个数值，
			
			$model = D('SchoolPinbiData');
			$data = array();
			$data['id'] = I('post.id', 0, 'int');	
			$data['title'] = I('post.name');
			$data['mainContent'] = I('post.mainContent');
			$data['banjiId'] = $banjiId;
			$data['teacherId'] = $teacherId;
			$data['studentId'] = $studentId;
			$data['updateTime'] = date("Y/m/d h:i:s", mktime());
			$data['createTime'] = date("Y/m/d h:i:s", mktime());
			$data['scores'] = I('post.scores', 0, 'int');
			$data['status'] = 1;//I('post.status', 0, 'int');
			$result = $model->save($data);

		   if ($result) {
			   $this->success('操作成功！', U('School/pinbiDataList',array("pbid"=>$pbid)));
		   } else {
			   $this->error('操作失败！[原因]：' . $model->getError());
		   }
		}else{
			//显示模板 start
			$id = I('get.id',0,'intval');
			if (!$id){
				$this->error('操作失败！[原因]：' . "查询失败");
			}
			$model = D('SchoolPinbiData');
			$map = array();
			$map['id'] = $id;
			$datas = $model->where($map)->find();
			if (!$datas){
				$this->error('操作失败！[原因]：' . "查询失败");
			}
			
			//输出
			$this->assign("datas",$datas);//var_dump($datas);
			$this->assign("pbid",$datas['pinbiId']);

			$pbid=$datas['pinbiId'];//var_dump($pbid);
			$scores = $datas['scores'];
			$this->assign("scores",$scores);
			$this->assign("pbid",$pbid);
			
			//已提交的已选择年级，预先复选中
			$arr_checked = array();
			$arr_checked = explode(',',$datas['teacherId']);
			$teacherModel = D('SchoolTeachers');
			if ( !empty( $arr_checked )){
				foreach ($arr_checked as $v){ 
					$one = $teacherModel->where('id='.intval($v))->field('id,name')->find(); 
					//var_dump($one);
					if ($one){//忽略首尾的0
						$teacher_list[]=array('id'=>$one['id'],'name'=>$one['name']);
					}
				} 
				
			}
			$this->assign('teacher_list', $teacher_list);
			
			//已提交的已选择班级，预先复选中
			$arr_checked = array();
			$arr_checked = explode(',',$datas['banjiId']);
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
			
			//已提交的已选择年级，预先复选中
			$arr_checked = array();
			$arr_checked = explode(',',$datas['studentId']);
			$studentModel = D('SchoolStudents');
			if ( !empty( $arr_checked )){
				foreach ($arr_checked as $v){ 
					$one = $studentModel->where('id='.intval($v))->field('id,name')->find(); 
					//var_dump($one);
					if ($one){//忽略首尾的0
						$student_list[]=array('id'=>$one['id'],'name'=>$one['name']);
					}
				} 
				
			}
			$this->assign('student_list', $student_list);
			
			//评比活动的内容
			//var_dump($pbid);
			if ($pbid){
				$pbModel = D('SchoolPinbi');
				$map = array();
				$map['id'] = $pbid;
				$infor = $pbModel->where($map)->find();
			   
				if ($infor){
					//输出本活动详情
					//var_dump($infor['fullScores']);
					$this->assign("fullScores",$infor['fullScores']+1);
					$this->assign("infor",$infor);//var_dump($infor);
				} else{
					//无此评比活动
				}
			}			
			
			
			//年级列表 START
			$gradeModel = D('SchoolGrade');
			$map = array();
			$grades = $gradeModel->getList($map);
			$this->assign('grades', $grades);
			//年级列表 END
			
			//班级列表 START
			$banjiModel = D('SchoolBanji');
			$map = array();
			$banjis = $banjiModel->getList($map);
			$this->assign('banjis', $banjis);
			//班级列表 END	
			
			//教师列表 START
			$teachersModel = D('SchoolTeachers');
			$map = array();
			$teachers = $teachersModel->getList($map);
			$this->assign('teachers', $teachers);
			//列表 END	        
			
			 //学生列表 START
			$stuModel = D('SchoolStudents');
			$map = array();
			$students = $stuModel->getList($map);
			$this->assign('students', $students);
			//学生列表 END
			
			$seltype = "radio";
			$this->assign('studentModalSelType', $seltype);//学生弹出框中只允许
			
			$seltype = "radio";
			$this->assign('teacherModalSelType', $seltype);//教师选择弹出框中只允许单选			
			
			$seltype = "radio";
			$this->assign('banjiModalSelType', $seltype);//班级弹出框中只允许单选
			
			
			// 
			$this->display("editPinbiData");
		}
	}

	/**
	 * 新评比列表
	 * author:zjh
	*/

	public function rateList(){
		$type = trim(I("request.type"));
		if (empty($type)){
			$type = "banjiList";
		}
		
		
		switch ($type){
			case "banjiList":
				$user = D("User");
				//班级列表 START
				$banjiModel = D('SchoolBanji');
				$map = array();
				if (session("username") == C('ADMIN_AUTH_KEY')) {
					;//超级管理员，列表中显示全部班级
				}else{
					//非超级管理员，班级列表中只显示有权限的
					$map['id'] = array("IN",session('user_banji_list'));
				}
				//$banjiCount = $banjiModel->where($map)->count();
				$banjis = $banjiModel->where($map)->order("id DESC")->select();
				$this->assign('banjis', $banjis);
				//班级列表 END
				
				$result = $user->userBanjiListCount();//可管理班级是否是1
		
				//只可管理一个表的话，直接显示荣誉列表
				if ($result){
					redirect('/School/banjiHonors/type/honorList/banjiId/'.$result, 0, '页面跳转中...');exit;
				}
				
				//显示班级列表
				$this->display("rateBanjiList");//列出班级
				break;
			case "rateResultList":
			
				//评比结果
				$banjiId = I("request.banjiId",0,"int");
				$this->assign('banjiId', $banjiId);
				//var_dump($banjiId);
				
				$model = D('SchoolRateResult');
		
				$map = array();
				$map['banjiId'] = array('EQ',$banjiId);
				
				// 加载数据分页类
				import('ORG.Util.Page');
				
				// 数据分页
				$totals = $model->where($map)->count();
				$Page = new Page($totals, 10);
				$show = $Page->show();
				$this->assign('page', $show);	
				
				$datas = $model->where($map)->order("id DESC")->limit($Page->firstRow.','.$Page->listRows)->select(); 
				//var_dump($datas);
				
				//重新组织信息
				$gradeModel = D('SchoolGrade');
				$banjiModel = D('SchoolBanji');
				
				foreach ($datas as $k => $v){
				//	$one = $gradeModel->where("id=".$v['gradeId'])->find();
				//	$datas[$k]['gradeName'] = $one['name'];
					
					$one = $banjiModel->where("id=".$v['banjiId'])->find();
					$datas[$k]['banjiName'] = $one['name'];			
		
					
					switch ($v['hide'])
					{
					case 0:
						$datas[$k]['status']="<font >显示</font>";
						break;  
					case 1:
						$datas[$k]['status']="<font color='red'>隐藏</font>";
						break;
					default:
						$datas[$k]['status']="<font color='green'>隐藏</font>";
					}
					
					$reModel = D("SchoolRateResult");
					$renum = $reModel->where("subjectId=".$v['Id'])->count();
					$datas[$k]['resultCount'] = $renum;	
		
				}
				
				
				//班级列表 START
				$banjiModel = D('SchoolBanji');
				$map = array();
				$banjis = $banjiModel->getList($map);
				$this->assign('banjis', $banjis);
				//班级列表 END	
				
				$this->assign('datas', $datas);
				$this->display("rateResultList");
				break;

			default:
				exit;
		}
		exit;
		


	}
	

	

	
	/**
	 * 添加新评比结果数据
	*/
	public function addRateResult(){
        if (IS_POST) {
			$banjiId=I('post.banjiId',0,"int");
			
			$model = D('SchoolRateResult');
			$data = array();
			
			$data['subject'] = trim(I('post.subject'));
			$data['banjiId'] = $banjiId;
			$data['ratedate'] = date("Y/m/d h:i:s", mktime());
			$data['rank'] = I('post.rank');
			$data['icon'] = I('post.photo');
			
			$result = $model->data($data)->add();
			
			// 执行操作
			if ($result) {
				$this->success('操作成功！', U('School/rateList',array("type"=>"rateResultList","banjiId"=>$banjiId)));
			} else {
				$this->error('操作失败！[原因]：' . $model->getError());
			}

        } else {
			$banjiId = I('request.banjiId',0,'intval');
			$this->assign('banjiId', $banjiId);
			
			//班级列表 START
			$banjiModel = D('SchoolBanji');
			$map = array();
			if (session("username") == C('ADMIN_AUTH_KEY')) {
				;//超级管理员
			}else{
				//非超级管理员，班级列表中只显示有权限的
				$map['id'] = array("IN",session('user_banji_list'));
			}
			//$banjiCount = $banjiModel->where($map)->count();
			$banjis = $banjiModel->where($map)->order("id DESC")->select();
			$this->assign('banjis', $banjis);
			//班级列表 END		
			

		
            // 
            $this->display("editRateResult");
        }		
	}
	
	/**
	 * 修改新评比结果数据
	*/
	public function editRateResult(){
        if (IS_POST) {
			$banjiId = I('post.banjiId', 0, 'int');	
			
			$model = D('SchoolRateResult');
			$data = array();
			$data['Id'] = I('post.id', 0, 'int');
			$data['subject'] = trim(I('post.subject'));
			$data['banjiId'] = $banjiId;
			$data['ratedate'] = date("Y/m/d", strtotime(I("request.rateDate")));
			$data['rank'] = I('post.rank');
			$data['icon'] = I('post.photo');
			
			$result = $model->save($data);
			
			
			// 执行操作
			if ($result) {
				$this->success('操作成功！', U('School/rateList',array("type"=>"rateResultList","banjiId"=>$banjiId)));
			} else {
				$this->error('操作失败！[原因]：' . $model->getError());
			}

        } else {
			$id = I('request.id',0,'intval');
			
			if ($id){
				$pbModel = D('SchoolRateResult');
				$map = array();
				$map['Id'] = $id;
				$datas = $pbModel->where($map)->field("Id,banjiId,subject,rank,icon,convert(VARCHAR(24),ratedate,120) as starttime")->find();
				//var_dump($datas);
				if ($datas){
					$banjiId = $datas['banjiId'];
					$this->assign("datas",$datas);//var_dump($banjiId);
					$this->assign('banjiId', $banjiId);

					
				} else{
					//无此评比活动
				}
				
			}			
			
			
			//班级列表 START
			$banjiModel = D('SchoolBanji');
			$map = array();
			if (session("username") == C('ADMIN_AUTH_KEY')) {
				;//超级管理员
			}else{
				//非超级管理员，班级列表中只显示有权限的
				$map['id'] = array("IN",session('user_banji_list'));
			}
			//$banjiCount = $banjiModel->where($map)->count();
			$banjis = $banjiModel->where($map)->order("id DESC")->select();
			$this->assign('banjis', $banjis);
			//班级列表 END	


			
            // 
            $this->display("editRateResult");
        }		
	}	
	
	/*
	 * 删除评比结果
	*/	
	public function delRateResult(){
		/*
		$id = I('request.id',0,'intval');
		$model = D('SchoolRateResult');

		$datas = $model->where("id=".$id)->find();
		if ($datas){
			$banjiId = $datas['banjiId'];
		}else{
			
		}
		$map['Id'] = $id;
		$result = $model->where($map)->delete();
		if ($result !== FALSE) {
		   $this->success('操作成功！', U('School/rateList/',array("type"=>"rateResultList","banjiId"=>$banjiId)));
		} else {
		   $this->error('操作失败！[原因]：' . $model->getError());
		} 
		*/
		
	    $ids = trim(I('post.nids'));
	
	    if (empty($ids)) {
	        die(json_encode(array('stat'=>0, '请求参数错误！')));
	    }
	
	    $idsArr = explode(',', trim($ids, ','));
	    if (count($idsArr) <= 0) {
	        die(json_encode(array('stat'=>0, '请求参数错误！')));
	    }
	
	    $model = D('SchoolRateResult');
	    //$result = $model->where(array('Id'=>array('in', $idsArr)))->delete();
		$map = array();
		$map['Id'] = array("IN",$ids);
		$datas = $model->where($map)->select();
		foreach($datas as $k=>$v){
			//主要是保证删除图片
			if (!empty($v['icon'])){
			//	$imagePath = C('UPLOAD_COMM_PATH')."/".ltrim($v['icon'],"/");
				$imagePath = ltrim($v['icon'],"/");
				@unlink($imagePath);//删除图片
			}
			//@unlink(ltrim('Uploads/school/6f439e9a-8e02-3a73-6e30-0ba70b0e3df6.jpg',"/"));
			$result = $model->where("Id=".$v['Id'])->delete();
		}
		
	    if ($result !== false) {
	        die(json_encode(array('stat'=>1, '操作成功！')));
	    } else {
	        die(json_encode(array('stat'=>0, '操作失败！[原因]：' . $resModel->getError())));
	    }
		
		
	}
	
	
	/*
	 * 值日表
	*/
	public function dutyList(){
		
		$model = D('SchoolBanji');
		$roomModel = D('SchoolRooms');
		
		//搜索条件
		$keyboard = trim(I('get.keyboard'));
		$gradeId = I("request.gradeId",0,"int");
		
		$this->assign('keyboard', $keyboard);
		$this->assign('gradeId', $gradeId);
		$this->assign('orderNext', $orderNext);
		
		$map = array();
	//	$map['id'] = array('GT',0);

		if(session("username") == C('ADMIN_AUTH_KEY')){
			//超级管理员可显示所有班级
		} else {
			$map['id'] = array("IN",session("user_banji_list"));
		}

		
		if ($keyboard){$map['name']=array('LIKE','%'.$keyboard.'%');}
		
        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
        $totals = $model->where($map)->count();
        $Page = new Page($totals, 10);
        $show = $Page->show();
        $this->assign('page', $show);
		
		$datas = $model->where($map)->order("id DESC")->limit($Page->firstRow.','.$Page->listRows)->select(); 
		
		if ($totals == 1){
			//echo "只能管理一个班级，直接跳转";
			$banjiId = $datas[0]['id'];
			
			redirect('/School/editDuty/banjiId/'.$banjiId, 0, '页面跳转中...');
			exit;
		}
		
		//重新组织信息
		$gradeModel = D('SchoolGrade');
		$teacherModel = D('SchoolTeachers');
		$studentModel = D('SchoolStudents');
		foreach ($datas as $k => $v){
			$one = $gradeModel->where("id=".$v['gradeId'])->find();
			$datas[$k]['gradeName'] = $one['name'];
			//var_dump( $one['name']);
			
			$one = $teacherModel->where("id=".intval($v['banzhurenId']))->find();
			$datas[$k]['teacherName'] = $one['name'];
			
			$one = $studentModel->where("id=".intval($v['banzhanId']))->find();
			$datas[$k]['banzhanName'] = $one['name'];	
			
			//教室
			$room_map = array();
			$room_map['id'] = $v['roomId'];
			$room_datas = $roomModel->where($room_map)->find();
			if ($room_datas){
				$datas[$k]['room'] = $room_datas['name'];
			}else{
				$datas[$k]['room'] = "";
			}
		}
		
		$this->assign('datas', $datas);
		
		
		$this->display("duty_index");
		exit;
		
		/*
		$model = D('SchoolDuty');
		
		//搜索条件
		$keyboard = trim(I('get.keyboard'));
		$gradeId = I('get.gradeId');
		$banjiId = I('get.banjiId');
		$orderType = I('get.orderType');
		
		switch ($orderType)
		{
		case 'sortasc':
			$order = "sort ASC";
			$orderNext = 'sortdesc';
			break;  
		case 'sortdesc':
			$order = "sort DESC";
			$orderNext = 'sortasc';
			break;
		case 'idasc':
			$order = "id ASC";
			$orderNext = 'iddesc';
			break;
		default:{
			$order = "id DESC";
			$orderNext = "sortasc";
			}
		}
		
		$this->assign('keyboard', $keyboard);
		$this->assign('gradeId', $gradeId);
		$this->assign('banjiId', $banjiId);
		$this->assign('orderNext', $orderNext);


		$map = array();
		$map['Id'] = array('GT',0);
		if ($keyboard){$map['name']=array('LIKE','%'.$keyboard.'%');}//关键字
		if ($gradeId){$map['gradeId']=array('EQ',$gradeId);}//年级
		if ($banjiId){$map['banjiId']=array('EQ',$banjiId);}//班级
		
        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
        $totals = $model->where($map)->count();
        $Page = new Page($totals, 10);
        $show = $Page->show();
        $this->assign('page', $show);	
		
		$datas = $model->where($map)->limit($Page->firstRow.','.$Page->listRows)->order("Id DESC")->select(); 
		
		if ($datas){
			//重新组织信息
			$banjiModel = D('SchoolBanji');
			$studentModel = D('SchoolStudents');
			
			$weekday = array("0"=>"-","1"=>"星期一","2"=>"星期二","3"=>"星期三","4"=>"星期四","5"=>"星期五","6"=>"星期六","7"=>"星期七");
			
			foreach ($datas as $k => $v){
				$one = $banjiModel->where("id=".$v['banjiId'])->find();
				$datas[$k]['banjiName'] = $one['name'];
				
				$datas[$k]['weekday'] = $weekday[$v[dutyday]];
				
				//学生
				if (!empty($v['memberList'])){
					$memberList = $v['memberList'];
					//var_dump($memberList);
					$stu_datas = $studentModel->where("id in (".$memberList.")")->field("name")->select();
					//$this->assign('stu_datas', $stu_datas);
					foreach ($stu_datas as $kk => $vv){
						$datas[$k]['students'] .= "，".$vv['name']; 
					}
					
				}
			}	
			
			
		}
		
		//班级列表 START
		$banjiModel = D('SchoolBanji');
		$map = array();
		$banjis = $banjiModel->getList($map);
		$this->assign('banjis', $banjis);
		//班级列表 END	
		
		$this->assign('datas', $datas);
		$this->display("dutyList");
		*/
	}
	
	/**
	 * 添加值日表
	*/
	public function addDuty(){
		/*
       if (IS_POST) {
			$model = D('SchoolDuty');
            $id = I('post.id', 0, 'int');
			$studentIdArr=I('post.studentIdStr');
			$studentId = implode(',',$studentIdArr);//intval(implode(',',$studentIdArr));//弹窗中设为单选，此处收到的只是一个数值，类
		
			$banjiIdArr=I('post.banjiIdStr');
			$banjiId = intval(implode(',',$banjiIdArr));//弹窗中设为单选，此处收到的只是一个数值，类型为
			//var_dump($banjiId);exit;		
		
			if (!$banjiId){
				$this->error('操作失败！[原因]：' . "必须指定班级");
			}
		
		
			$data = array();		
			//$data['Id'] = I('post.id');	
			$data['banjiId'] = $banjiId;
            $data['dutyday'] = I('post.dutyday', 0, 'int');
			$data['memberList'] = $studentId;
            
            $result = $model->data($data)->add();
			if ($result) {
			   $this->success('操作成功！', U('School/dutyList'));
			} else {
			   $this->error('操作失败！[原因]：' . $model->getError());
			}            
        } else {
			$this->assign('banjiId', 0);
			
			//班级列表 START
			$banjiModel = D('SchoolBanji');
			$map = array();
			$banjis = $banjiModel->getList($map);
			$this->assign('banjis', $banjis);
			//班级列表 END	
			$seltype = "radio";
			$this->assign('banjiModalSelType', $seltype);//班级弹出框中只允许单
			
			
			
            //学生列表 START 
			
			//$stuModel = D('SchoolStudents');
			//$map = array();
			//$students = $stuModel->getList($map);
			//$this->assign('students', $students);
			
			$student_dialog_message = "请先选择班级";
			$this->assign('student_dialog_message', $student_dialog_message);
			//学生列表 END
			
			$weekday = array(array("id"=>"1","name"=>"星期一"),array("id"=>"2","name"=>"星期二"),array("id"=>"3","name"=>"星期三"),array("id"=>"4","name"=>"星期四"),array("id"=>"5","name"=>"星期五"),array("id"=>"6","name"=>"星期六"),array("id"=>"7","name"=>"星期七"));
			$this->assign('weekday', $weekday);
			

			

			$this->display("editDuty");               

        }
		*/
		
	}
	
	/**
	 * 修改值日表
	*/
	public function editDuty(){
       if (IS_POST) {
			$model = D('SchoolDuty');
            $curDay = I('post.curDay', 0, 'int');
			$banjiId = I('post.banjiId', 0, 'int');
			$members = I('post.members');
			
			
			//$studentId = implode(',',$studentIdArr);//intval(implode(',',$studentIdArr));//弹窗中设为单选，此处收到的只是一个数值，类
		
			$data = array();		
			$data['memberList'] = $members;
            
            $result = $model->where("banjiId=".$banjiId." and dutyday=".$curDay)->setField('memberList',$members);
			
			//获取到值日学生的中文名返回
			$stuModel = D('SchoolStudents');
			$stuName = $stuModel->getStudentName($members);
			
			if ($result) {
			   echo json_encode(array("stat"=>"1","msg"=>$members,"data"=>$stuName));exit;
			} else {
			   echo json_encode(array("stat"=>"0","msg"=>"更新失败","data"=>""));exit;
			}      
			
			      
        } else {
            $banjiId = I('get.banjiId',0,'intval');
			$this->assign('banjiId', $banjiId);
			if (!$banjiId){
				$this->error('班级ID为空');
			}
			
			if (session("username") == C('ADMIN_AUTH_KEY')) {
				//超级管理员
			}else{
				//是否可操作此班级ID
				$this->check_banji($banjiId);
			}
			$studentModel = D('SchoolStudents');
			
			$dutyModel = D('SchoolDuty');
			$dutyModel->resetDutyTable($banjiId);//值日表数据有效性保证
			
			$map['banjiId'] = $banjiId;
			$datas = $dutyModel->where($map)->order("dutyday ASC")->select();
			if ($datas){
				$banjiModel = D('SchoolBanji');
				$one = $banjiModel->where("id=".$banjiId)->find();
				$banjiName = $one['name'];
				$this->assign("banjiName",$banjiName);
				
				foreach($datas as $k=>$v){
					$tmp = "";
					if (!empty($v['memberList'])){
						$tmp = $studentModel->getStudentName($v['memberList']);
						$datas[$k]['memberNameList'] = $tmp;
					}
				}
				
				//班级列表 START
				$this->assign('banjis', $banjiId);
				//班级列表 END
				
				//学生列表 START
				$students = $studentModel->where("banjiId=".$banjiId)->field("id,name")->order("id DESC")->select();
				$this->assign('students', $students);//var_dump($students);
				//学生列表 END					
				
				
				
			}else{

				
			}
			
			//$seltype = "radio";
			//$this->assign('studentModalSelType', $seltype);//学生弹出框中只允许单
			

			//var_dump($datas);
            if ($datas) {
				$this->assign('datas', $datas);
				$this->display("editDuty");               
            }
        }
		
		
	}
	
	/**
	 * 删除值日表（）
	*/
	/*
	public function delDuty(){
        $id = I('get.id', 0, 'int');
        if (!$id) {
            $this->error('非法操作！');
        }
        
        $model = D('SchoolDuty');
        $result = $model->where(array('Id'=>$id))->find();
        if (!$result) {
            $this->error('非法操作，不存在该记录！');
        }
 
        $delResult = $model->where(array('Id'=>$id))->delete();
        if ($delResult !== false) {
           $this->success('操作成功！', U('School/dutyList'));
        } else {
           $this->error('操作失败！[原因]：' . $model->getError());
        }
	
	}*/
	
	/*
	 * 考场列表
	*/
	public function kaochangList(){
		$model = D('SchoolKaochang');
		
		//处理批量删除
		$dotype = I("request.dotype");//echo "dotype=".$dotype;
		$ids= I('get.ids', '', 'strip_tags');
		if (!empty($ids)){
			$result = $model->where("id in(".$ids.")")->delete();
		}
		
		//搜索条件
		$keyboard = trim(I('get.keyboard'));
		$gradeId = I('get.gradeId');
		$banjiId = I('get.banjiId');
		$orderType = I('get.orderType');
		
		switch ($orderType)
		{
		case 'sortasc':
			$order = "sort ASC";
			$orderNext = 'sortdesc';
			break;  
		case 'sortdesc':
			$order = "sort DESC";
			$orderNext = 'sortasc';
			break;
		case 'idasc':
			$order = "id ASC";
			$orderNext = 'iddesc';
			break;
		default:{
			$order = "id DESC";
			$orderNext = "sortasc";
			}
		}
		
		$this->assign('keyboard', $keyboard);
		$this->assign('gradeId', $gradeId);
		$this->assign('banjiId', $banjiId);
		$this->assign('orderNext', $orderNext);


		$map = array();
		$map['Id'] = array('GT',0);
		if ($keyboard){$map['name']=array('LIKE','%'.$keyboard.'%');}//关键字
		if ($gradeId){$map['gradeId']=array('EQ',$gradeId);}//年级
		if ($banjiId){$map['banjiId']=array('EQ',$banjiId);}//班级
		
        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
        $totals = $model->where($map)->count();
        $Page = new Page($totals, 10);
        $show = $Page->show();
        $this->assign('page', $show);	
		
		$datas = $model->where($map)->limit($Page->firstRow.','.$Page->listRows)->order("Id DESC")->select(); 
		
		if ($datas){
			//重新组织信息
			$roomModel = D('SchoolRooms');
			$endpointModel = D('Endpoint');
			$cameraModel = D('SchoolCamera');

			foreach ($datas as $k => $v){
				if ($v['roomId']){
					$one = $roomModel->where("id=".$v['roomId'])->find();
					$datas[$k]['room_name'] = $one['name'];
				}
				if ($v['cameraId']){
					$one = $cameraModel->where("Id=".$v['cameraId'])->find();
					$datas[$k]['camera_name'] = $one['code'];
				}
				if ($v['endPointId']){
					$one = $endpointModel->where("tid=".$v['endPointId'])->find();
					$datas[$k]['endpoint_name'] = $one['touchMainId'];
				}
			}	
		}
		
		//班级列表 START
		$banjiModel = D('SchoolBanji');
		$map = array();
		$banjis = $banjiModel->getList($map);
		$this->assign('banjis', $banjis);
		//班级列表 END	
		
		$this->assign('datas', $datas);
		$this->display("kaochangList");		
	}
	
	/*
	 * 增加考场
	*/
	public function addKaochang(){
       if (IS_POST) {
			$model = D('SchoolKaochang');
            $id = I('post.id', 0, 'int');
			$roomIdArr=I('post.roomIdStr');
			$roomId = implode(',',$roomIdArr);//intval(implode(',',$studentIdArr));//弹窗中设为单选，此处收到的只是一个数值，类
		
			//取教室的行和列
			if ($roomId){
				$roomModel = D('SchoolRooms');
				$data_room = $roomModel->where("id=".$roomId)->find();
				if ($data_room){
					$linenumber = $data_room['linenumber'];
					$columnnumber = $data_room['columnnumber'];
				}else{
					$this->error('操作失败！[原因]：' . $roomModel->getError());
				}
			}else{
				$this->error('操作失败！[原因]：教室指定错误');			
			}
		
			$data = array();		
			$data['name'] = I('post.name');	
            $data['roomId'] = $roomId;
			$data['linenumber'] = $linenumber;
			$data['columnnumber'] = $columnnumber;
            
            $result = $model->data($data)->add();
			if ($result) {
			   $this->success('操作成功！', U('School/kaochangList'));
			} else {
			   $this->error('操作失败！[原因]：' . $model->getError());
			}            
        } else {
			$model = D('SchoolKaochang');
			
			$seltype = "radio";
			$this->assign('roomModalSelType', $seltype);//教室弹出框中只允许单
			
            //教室列表 START
			$roomModel = D('SchoolRooms');
			$map = array();
			$rooms = $roomModel->select();
			$this->assign('rooms', $rooms);//var_dump($rooms);
			//教室列表 END	

			$this->assign('datas', $datas);
			$this->display("editKaochang");               

        }		
	}
	

	
	
	/*
	 * 修改考场
	*/
	public function editKaochang(){
       if (IS_POST) {
			$model = D('SchoolKaochang');
            $id = I('post.id', 0, 'int');
			$roomIdArr=I('post.roomIdStr');
			$roomId = implode(',',$roomIdArr);//intval(implode(',',$studentIdArr));//弹窗中设为单选，此处收到的只是一个数值，类
		
			//取教室的行和列
			if ($roomId){
				$roomModel = D('SchoolRooms');
				$data_room = $roomModel->where("id=".$roomId)->find();
				if ($data_room){
					$linenumber = $data_room['linenumber'];
					$columnnumber = $data_room['columnnumber'];
				}else{
					$this->error('操作失败！[原因]：' . $roomModel->getError());
				}
			}else{
				$this->error('操作失败！[原因]：教室指定错误');			
			}
		
			$data = array();		
			$data['id'] = I('post.id');	
			$data['name'] = I('post.name');	
            $data['roomId'] = $roomId;
			$data['linenumber'] = $linenumber;
			$data['columnnumber'] = $columnnumber;
            
            $result = $model->save($data);
			if ($result) {
			   $this->success('操作成功！', U('School/kaochangList'));
			} else {
			   $this->error('操作失败！[原因]：' . $model->getError());
			}            
        } else {
            $id = I('get.id',0,'intval');
			$model = D('SchoolKaochang');
			$map['id'] = $id;
			$datas = $model->where($map)->find();
			
			$seltype = "radio";
			$this->assign('roomModalSelType', $seltype);//教室弹出框中只允许单
			
            //教室列表 START
			$roomModel = D('SchoolRooms');
			$map = array();
			$rooms = $roomModel->select();
			$this->assign('rooms', $rooms);//var_dump($rooms);
			//教室列表 END	
			
			if ($datas){
				//已提交的已选择教室，预先复选中
				$arr_checked = array();
				$arr_checked = explode(',',$datas['roomId']);
				//$roomModel = D('SchoolRooms');
				if ( !empty( $arr_checked )){
					foreach ($arr_checked as $v){ 
						$one = $roomModel->where('id='.intval($v))->field('id,name')->find(); 
						if ($one){//忽略首尾的0
							
							$room_list[]=array('id'=>$one['id'],'name'=>$one['name']);
						}
					} 
				}
				$this->assign('room_list', $room_list);//zjh add	
				//var_dump($room_list);
			}

			$this->assign('datas', $datas);
			$this->display("editKaochang");               

        }
	}	
	
	
	/*
	 * 删除考场
	*/
	public function delKaochang(){
		/*
		$id = I('get.id', 0, 'int');
        if (!$id) {
            $this->error('非法操作！');
        }
		$model = D('SchoolKaochang');
        $delResult = $model->where(array('id'=>$id))->delete();
        if ($delResult !== false) {
           $this->success('操作成功！', U('School/kaochangList'));
        } else {
           $this->error('操作失败！[原因]：' . $model->getError());
        }*/
		
	    $ids = trim(I('post.nids'));
	
	    if (empty($ids)) {
	        die(json_encode(array('stat'=>0, '请求参数错误！')));
	    }
	
	    $idsArr = explode(',', trim($ids, ','));
	    if (count($idsArr) <= 0) {
	        die(json_encode(array('stat'=>0, '请求参数错误！')));
	    }
	
	    $model = D('SchoolKaochang');
	    $result = $model->where(array('id'=>array('in', $idsArr)))->delete();
	    if ($result !== false) {
	        die(json_encode(array('stat'=>1, '操作成功！')));
	    } else {
	        die(json_encode(array('stat'=>0, '操作失败！[原因]：' . $resModel->getError())));
	    }
	}		
	
	/*
	 * 考试科目列表
	*/
	public function kaochangSubjectList(){
		$model = D('SchoolKaochangSubject');
		
		//搜索条件
		$keyboard = trim(I('get.keyboard'));
		$gradeId = I('get.gradeId');
		$banjiId = I('get.banjiId');
		$orderType = I('get.orderType');
		
		switch ($orderType)
		{
		case 'sortasc':
			$order = "sort ASC";
			$orderNext = 'sortdesc';
			break;  
		case 'sortdesc':
			$order = "sort DESC";
			$orderNext = 'sortasc';
			break;
		case 'idasc':
			$order = "id ASC";
			$orderNext = 'iddesc';
			break;
		default:{
			$order = "id DESC";
			$orderNext = "sortasc";
			}
		}
		
		$this->assign('keyboard', $keyboard);
		$this->assign('gradeId', $gradeId);
		$this->assign('banjiId', $banjiId);
		$this->assign('orderNext', $orderNext);


		$map = array();
		$map['id'] = array('GT',0);
		if ($keyboard){$map['name']=array('LIKE','%'.$keyboard.'%');}//关键字
		if ($gradeId){$map['gradeId']=array('EQ',$gradeId);}//年级
		if ($banjiId){$map['banjiId']=array('EQ',$banjiId);}//班级
		
        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
        $totals = $model->where($map)->count();
        $Page = new Page($totals, 10);
        $show = $Page->show();
        $this->assign('page', $show);	
		
		$datas = $model->where($map)->limit($Page->firstRow.','.$Page->listRows)->field("id,name,invigilatingTeachers,numbers,realNumbers,convert(VARCHAR(24),startTime,120) as starttime,convert(VARCHAR(24),endTime,120) as endtime")->order("Id DESC")->select(); 
		
		if ($datas){
			//重新组织信息
			$teachersModel = D('SchoolTeachers');
			
			foreach ($datas as $k => $v){
				//
				if (!empty($v['invigilatingTeachers'])){
					$memberList = $v['invigilatingTeachers'];
					//var_dump($memberList);
					$t_datas = $teachersModel->where("id in (".$memberList.")")->field("name")->select();
					//$this->assign('stu_datas', $stu_datas);
					foreach ($t_datas as $kk => $vv){
						$datas[$k]['teachers'] .= $vv['name']."，"; 
					}
					$datas[$k]['teachers'] = substr($datas[$k]['teachers'],0,-1);//去右边的逗号;
				}
			}	
			
			
		}
		
		//班级列表 START
		$banjiModel = D('SchoolBanji');
		$map = array();
		$banjis = $banjiModel->getList($map);
		$this->assign('banjis', $banjis);
		//班级列表 END	
		
		$this->assign('datas', $datas);
		$this->display("kaochangSubjectList");
	}
	
	/**
	 * 增加考试科目
	*/
	/*
	public function addKaochangSubject(){
		if (IS_POST){
			
		}else{
			
			
			
			
			$this->display("editKaochangSubject");
		}
	}*/
	
	/**
	 * 修改考试科目
	*/
	/*
	public function editKaochangSubject(){
		if (IS_POST){
			$model = D('SchoolKaochangSubject');
			
			//考试科目为单选
			//$subjectIdArr=I('post.subjectIdStr');
			//$subjectId = intval(implode(',',$subjectIdArr));//弹窗中设为单选，此处收到的只是一
			
			//监考教师为多选
			$teacherIdArr=I('post.teacherIdStr');
			$teacherId = implode(',',$teacherIdArr);//
			
			$data = array();		
			$data['id'] = I('post.id', 0, 'int');	
            $data['name'] = I('post.name');
			$data['invigilatingTeachers'] = $teacherId;	
			$data['startTime'] = I('post.starttime');
			$data['endTime'] = I('post.endtime');
			$data['numbers'] = I('post.numbers', 0, 'int');
			$data['realNumbers'] = I('post.realNumbers', 0, 'int');
			$data['description'] = I('post.description');
			$data['sort'] = I('post.sort', 0, 'int');
            
            $result = $model->save($data);
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/kaochangSubjectList'));
			} else {
			   $this->error('操作失败！[原因]：' . $model->getError());
			}    
			
			
		}else{
			//显示模板 start
			$id = I('get.id',0,'intval');
			if (!$id){
				$this->error('操作失败！[原因]：' . "查询失败");
			}
			
			$model = D('SchoolKaochangSubject');
			$map = array();
			$map['id'] = $id;
			$datas = $model->where($map)->field("id,name,invigilatingTeachers,description,numbers,realNumbers,convert(VARCHAR(24),startTime,120) as starttime,convert(VARCHAR(24),endTime,120) as endtime")->find();
			if (!$datas){
				$this->error('操作失败！[原因]：' . "查询失败");
			}else{
			
				//已提交的已选择教师，预先复选中
				$arr_checked = array();
				$arr_checked = explode(',',$datas['invigilatingTeachers']);
				$teacherModel = D('SchoolTeachers');
				if ( !empty( $arr_checked )){
					foreach ($arr_checked as $v){ 
					 $one = $teacherModel->where('id='.$v)->field('id,name')->find(); 
					 if ($one){//忽略首尾的0
					 	$teacher_list[]=array('id'=>$one['id'],'name'=>$one['name']);
					 }
					} 
				}
				$this->assign('teacher_list', $teacher_list);//zjh add	
				$datas['teacherId'] = $datas['invigilatingTeachers'];//对话框需要

				//查询关联表，获取本考场已设置的考试科目
				$examplanModel = D("SchoolKaochangExamPlan");


			}
			
			$seltype = "radio";
			$this->assign('subjectModalSelType', $seltype);//班级弹出框中只允许单选
			
			
            //科目列表 START
			$subjectsModel = D('SchoolSubjects');
			$map = array();
			$subjects = $subjectsModel->getList($map);
			$this->assign('subjects', $subjects);
			//列表 END	
			
			//教师列表 START
			$teachersModel = D('SchoolTeachers');
			$map = array();
			$teachers = $teachersModel->getList($map);
			$this->assign('teachers', $teachers);
			//列表 END	    
			
			$this->assign('datas', $datas);
			$this->display("editKaochangSubject");
		}
		
		
	}
	*/
	
	/**
	 * 考试时间：列表
	*/
	public function examinationTime(){
		$model = D("SchoolExaminationTime"); 
		
		//处理批量删除
		$dotype = I("request.dotype");//echo "dotype=".$dotype;
		$ids= I('get.ids', '', 'strip_tags');
		if (!empty($ids)){
			$model->where("id in(".$ids.")")->delete();
		}

		
		//搜索条件
		$keyboard = trim(I('get.keyboard'));
		$starttime = I('get.starttime');
		$endtime = I('get.endtime');
		
		$this->assign('keyboard', $keyboard);
		$this->assign('starttime', $starttime);
		$this->assign('endtime', $endtime);

		$map = array();
		$map['id'] = array('GT',0);
		if ($keyboard){$map['name']=array('LIKE','%'.$keyboard.'%');}//关键字
		if ($starttime){$map['beginTime']=array('EQ',$starttime);}//年级
		if ($endtime){$map['endTime']=array('EQ',$endtime);}//班级
		
        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
        $totals = $model->where($map)->count();
        $Page = new Page($totals, 10);
        $show = $Page->show();
        $this->assign('page', $show);	
		
		$datas = $model->where($map)->limit($Page->firstRow.','.$Page->listRows)->field("id,convert(VARCHAR(24),beginTime,120) as begintime,convert(VARCHAR(24),endTime,120) as endtime")->order("beginTime DESC")->select();
		//var_dump($datas);
		if ($datas){
			//重新组织信息
			$teachersModel = D('SchoolTeachers');
			$roomModel = D('SchoolRooms');
			$subjectModel = D('SchoolSubjects');
			$kaochangModel = D('SchoolKaochang');
			foreach ($datas as $k => $v){
				//
				if (!empty($v['teacherList'])){
					$teacherList = $v['teacherList'];
					//var_dump($memberList);
					$t_datas = $teachersModel->where("id in (".$teacherList.")")->field("name")->select();
					//$this->assign('stu_datas', $stu_datas);
					foreach ($t_datas as $kk => $vv){
						$datas[$k]['teachers'] .= $vv['name']."，"; 
					}
					
					
					
				}
				
				$one = array();
				$one = $roomModel->where('id='.intval($v['kaochangId']))->field('id,name')->find();
				$datas[$k]['room'] = $one['name'];
				
				$one = array();
				$one = $subjectModel->where('id='.intval($v['subjectId']))->field('id,name')->find();
				$datas[$k]['subject'] = $one['name'];
							
				$one = array();
				$one = $kaochangModel->where('id='.intval($v['kaochangId']))->field('id,name')->find();
				$datas[$k]['kaochang'] = $one['name'];
				
						
			}	
			
			
		}
		$this->assign('datas', $datas);	
		
		$this->display("examinationTime");
	}
	
	/**
	 * 考试时间：新增
	*/
	public function addExaminationTime(){
        if (IS_POST) {
			//日期
			$thedate = I("post.thedate");
			$begintime = I("post.begintime");
			$endtime = I("post.endtime");
			
			//格式化
			$begin = $thedate." ".$begintime;
			$end = $thedate." ".$endtime;
			
			//再转化一次确保格式无误
			$begin = date("Y-m-d H:i:s",strtotime($begin));
			$end = date("Y-m-d H:i:s",strtotime($end));
			
			$examinationTimeModel = D("SchoolExaminationTime"); 
			
			$data = array();
			$data['beginTime'] = $begin;
			$data['endTime'] = $end;

			$result = $examinationTimeModel->data($data)->add();
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/examinationTime'));
			} else {
			   $this->error('操作失败！[原因]：' . $examinationTimeModel->getError());
			} 
        } else {
			$this->assign("id",0);
			$this->display("editExaminationTime");
		
        }
	}
	
	/**
	 * 考试时间：修改
	*/
	public function editExaminationTime(){
        if (IS_POST) {
			$id = I("post.id",0,"int");
			
			//日期
			$thedate = I("post.thedate");

			if (!$id){
				$this->error("参数错误");	
			}
			
			$begintime = I("post.begintime");
			$endtime = I("post.endtime");
			
			//格式化
			$begin = $thedate." ".$begintime;
			$end = $thedate." ".$endtime;
			
			//再转化一次确保格式无误
			$begin = date("Y-m-d H:i:s",strtotime($begin));
			$end = date("Y-m-d H:i:s",strtotime($end));
			
			$examinationTimeModel = D("SchoolExaminationTime"); 
			
			$data = array();
			$data['beginTime'] = $begin;
			$data['endTime'] = $end;
			
			$result = $examinationTimeModel->where("id=".$id)->data($data)->save();
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/examinationTime'));
			} else {
			   $this->error('操作失败！[原因]：' . $examinationTimeModel->getError());
			} 
			
        } else {
			$id = I("request.id",0,"int");
			if (!$id){
				$this->error("参数错误");	
			}
			$examinationTimeModel = D("SchoolExaminationTime"); 			
			$datas = $examinationTimeModel->where("id=".$id)->field("id,convert(VARCHAR(24),beginTime,120) as begintime,convert(VARCHAR(24),endTime,120) as endtime")->find();
			//var_dump($datas);
			
			//把开始时间拆分成日期和小时，分钟
			$begintime = $datas['begintime'];
			$endtime = $datas['endtime'];
			
			$this->thedate = date('Y-m-d',strtotime($begintime));
			$this->begintime = date('H:i:s',strtotime($begintime));
			$this->endtime = date('H:i:s',strtotime($endtime));
			
			$this->assign("id",$id);
			$this->assign("datas",$datas);
			$this->display("editExaminationTime");
		
        }		
		
	}	
	
	/**
	 * 考试时间：删除
	*/
	public function delExaminationTime(){
		$id = I("request.id",0,"int");
		if (!$id){
			$this->error("参数错误");	
		}
		
		$examinationTimeModel = D("SchoolExaminationTime"); 			
		$result = $examinationTimeModel->where("id=".$id)->delete();
		
		if ($result !== FALSE) {
		   $this->success('操作成功！', U('School/examinationTime'));

		} else {
		   $this->error('操作失败！[原因]：' . $model->getError());
		}    
		
			
	}
	
	
	
	
	/**
	 * 考试安排列表
	*/
	public function kaochangExamplanList(){
		$model = D('SchoolKaochangExamPlan');
		
		//搜索条件
		$keyboard = trim(I('get.keyboard'));
		$gradeId = I('get.gradeId');
		$banjiId = I('get.banjiId');
		$orderType = I('get.orderType');
		
		switch ($orderType)
		{
		case 'sortasc':
			$order = "sort ASC";
			$orderNext = 'sortdesc';
			break;  
		case 'sortdesc':
			$order = "sort DESC";
			$orderNext = 'sortasc';
			break;
		case 'idasc':
			$order = "id ASC";
			$orderNext = 'iddesc';
			break;
		default:{
			$order = "id DESC";
			$orderNext = "sortasc";
			}
		}
		
		$this->assign('keyboard', $keyboard);
		$this->assign('gradeId', $gradeId);
		$this->assign('banjiId', $banjiId);
		$this->assign('orderNext', $orderNext);


		$map = array();
		$map['id'] = array('GT',0);
		if ($keyboard){$map['name']=array('LIKE','%'.$keyboard.'%');}//关键字
		if ($gradeId){$map['gradeId']=array('EQ',$gradeId);}//年级
		if ($banjiId){$map['banjiId']=array('EQ',$banjiId);}//班级
		
        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
        $totals = $model->where($map)->count();
        $Page = new Page($totals, 10);
        $show = $Page->show();
        $this->assign('page', $show);	
		
		$datas = $model->where($map)->limit($Page->firstRow.','.$Page->listRows)->field("Id,kaochangId,subjectId,population,teacherList,convert(VARCHAR(24),beginTime,120) as begintime,convert(VARCHAR(24),endTime,120) as endtime")->order("Id DESC")->select(); 
		//var_dump($datas);
		if ($datas){
			//重新组织信息
			$teachersModel = D('SchoolTeachers');
			$roomModel = D('SchoolRooms');
			$subjectModel = D('SchoolSubjects');
			$kaochangModel = D('SchoolKaochang');
			foreach ($datas as $k => $v){
				if (!empty($v['teacherList'])){
					$teacherList = $v['teacherList'];
					$t_datas = $teachersModel->where("id in (".$teacherList.")")->field("name")->select();
					$tmp = array();
					foreach ($t_datas as $kk => $vv){
						$tmp[] = $vv['name'];
					}
					$datas[$k]['teachers'] = implode("，",$tmp);//监考老师用逗号分隔
				}
				
				$one = array();
				$one = $roomModel->where('id='.intval($v['kaochangId']))->field('id,name')->find();
				$datas[$k]['room'] = $one['name'];
				
				$one = array();
				$one = $subjectModel->where('id='.intval($v['subjectId']))->field('id,name')->find();
				$datas[$k]['subject'] = $one['name'];
							
				$one = array();
				$one = $kaochangModel->where('id='.intval($v['kaochangId']))->field('id,name')->find();
				$datas[$k]['kaochang'] = $one['name'];
				
						
			}	
			
			
		}
		
		//班级列表 START
		$banjiModel = D('SchoolBanji');
		$map = array();
		$banjis = $banjiModel->getList($map);
		$this->assign('banjis', $banjis);
		//班级列表 END	
		
		$this->assign('datas', $datas);
		$this->display("kaochangExamplanList");
	}
	
	/**
	 * 增加考试安排
	*/
	public function addKaochangExamplan(){
		if (IS_POST){
			$model = D('SchoolKaochangExamPlan');		
        //  $id = I('post.id', 0, 'int');
			
			//考试科目为单选
			$subjectIdArr=I('post.subjectIdStr');
			$subjectId = intval(implode(',',$subjectIdArr));//弹窗中设为单选，此处收到的只是一
			
			//考场为单选
			$kaochangIdArr=I('post.kaochangIdStr');
			$kaochangId = intval(implode(',',$kaochangIdArr));//弹窗中设为单选，此处收到的只是一			
			
			//监考教师为多选
			$teacherIdArr=I('post.teacherIdStr');
			$teacherId = implode(',',$teacherIdArr);//
			
			$data = array();		
		//	$data['Id'] = $id;	
            $data['kaochangId'] = $kaochangId;
			$data['subjectId'] = $subjectId;	
			$data['population'] = I('post.population', 0, 'int');
			$data['teacherList'] = $teacherId;
			$data['beginTime'] = I('post.starttime');
			$data['endTime'] = I('post.endtime');
			$data['description'] = I('post.description');
            
            $result = $model->data($data)->add();
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/kaochangExamplanList'));
			} else {
			   $this->error('操作失败！[原因]：' . $model->getError());
			} 
		}else{

			//考场为单选
			$seltype = "radio";
			$this->assign('kaochangModalSelType', $seltype);//弹出框中只允许单选
			
			//考场列表 START
			$kaochangModel = D('SchoolKaochang');
			$map = array();
			$kaochangs = $kaochangModel->where($map)->select();
			$this->assign('kaochangs', $kaochangs);//var_dump($kaochangs);
			//列表 END	
			
			$seltype = "radio";
			$this->assign('subjectModalSelType', $seltype);//弹出框中只允许单选
			
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
			//列表 END					
			
			//班级列表 START
			$banjiModel = D('SchoolBanji');
			$map = array();
			$banjis = $banjiModel->getList($map);
			$this->assign('banjis', $banjis);
			//班级列表 END				

				//考试时间列表 START
				$this->assign('examTimeModalSelType',"radio");//弹出框中只允许单选
				
				$examinationTimeModel = D("SchoolExaminationTime"); 
				$examTimes = $examinationTimeModel->field("id,convert(VARCHAR(24),beginTime,120) as starttime,convert(VARCHAR(24),endTime,120) as endtime")->order("starttime DESC")->select();
				//var_dump($examTimes);
				$dateArr = array();
				foreach($examTimes as $k=>$v){
					if (!in_array(date("Y-m-d",strtotime($v['starttime'])),$dateArr)){
						$dateArr[] = date("Y-m-d",strtotime($v['starttime']));//每个日期作为一个数组元素
					}
					//$examTimes[$k]['thedate'] = date("Y-m-d",strtotime($v['starttime']));
				}//无键名的日期数组
				
				//var_dump($dateArr);
				
				$dateArr2 = array();
				foreach($dateArr as $k=>$v){
					$dateArr2[] = array("thedate"=>$v);
				}//形成带键名的日期数组
				//var_dump($dateArr2);
				
				foreach($dateArr2 as $k=>$v){
					$tmp=array();
					foreach($examTimes as $kk=>$vv){
						if ($v['thedate']==date("Y-m-d",strtotime($vv['starttime']))){
							//格式：array("stime"=>"08:00:00","etime"=>"09:00:00")
							$tmp[] = array("stime"=>date("H:i:s",strtotime($vv['starttime'])),"etime"=>date("H:i:s",strtotime($vv['endtime'])),"starttime"=>$vv['starttime'],"endtime"=>$vv['endtime']);
						}
					}
					$dateArr2[$k]['timearr'] = $tmp;
					//var_dump($tmp);
				}
				//var_dump($dateArr2);
				
				$this->assign('examTimes', $dateArr2);
				$this->assign('dateArr', $dateArr);
				//考试时间列表 END
			
			
			$this->assign('datas', $datas);
			$this->display("editKaochangExamplan");
		}	
	}
	
	/**
	 * 修改考试安排
	*/
	public function editKaochangExamplan(){
		if (IS_POST){
			$model = D('SchoolKaochangExamPlan');		
            $id = I('post.id', 0, 'int');
			
			//考试科目为单选
			$subjectIdArr=I('post.subjectIdStr');
			$subjectId = intval(implode(',',$subjectIdArr));//弹窗中设为单选，此处收到的只是一
			
			//考场为单选
			$kaochangIdArr=I('post.kaochangIdStr');
			$kaochangId = intval(implode(',',$kaochangIdArr));//弹窗中设为单选，此处收到的只是一			
			
			//监考教师为多选
			$teacherIdArr=I('post.teacherIdStr');
			$teacherIdArr = array_unique($teacherIdArr);//移除数组中重复的值
			$teacherId = implode(',',$teacherIdArr);//
			
			
			$data = array();		
			$data['Id'] = $id;	
            $data['kaochangId'] = $kaochangId;
			$data['subjectId'] = $subjectId;	
			$data['population'] = I('post.population', 0, 'int');
			$data['teacherList'] = $teacherId;
			$data['beginTime'] = I('post.starttime');
			$data['endTime'] = I('post.endtime');
			$data['description'] = I('post.description');
            
            $result = $model->save($data);
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/kaochangExamplanList'));
			} else {
			   $this->error('操作失败！[原因]：' . $model->getError());
			} 
		}else{
			//显示模板 start
			$id = I('get.id',0,'intval');
			if (!$id){
				$this->error('操作失败！[原因]：' . "查询失败");
			}
			
			$model = D('SchoolKaochangExamPlan');
			$map = array();
			$map['Id'] = $id;
			$datas = $model->where($map)->field("Id,kaochangId,subjectId,population,description,teacherList,convert(VARCHAR(24),beginTime,120) as starttime,convert(VARCHAR(24),endTime,120) as endtime")->find();
			if (!$datas){
				$this->error('操作失败！[原因]：' . "查询失败");
			}else{
				
				//时间
				$this->assign("thedate",date("Y-m-d",strtotime($datas['starttime'])));
				$this->assign("stime",date("H:i:s",strtotime($datas['starttime'])));
				$this->assign("etime",date("H:i:s",strtotime($datas['endtime'])));
				
				//已提交的已选择教师，预先复选中
				$arr_checked = array();
				$arr_checked = explode(',',$datas['teacherList']);
				$teacherModel = D('SchoolTeachers');
				if ( !empty( $arr_checked )){
					foreach ($arr_checked as $v){ 
					 $one = $teacherModel->where('id='.$v)->field('id,name')->find(); 
					 if ($one){//忽略首尾的0
					 	$teacher_list[]=array('id'=>$one['id'],'name'=>$one['name']);
					 }
					} 
				}
				$this->assign('teacher_list', $teacher_list);//zjh add	
				$datas['teacherId'] = $datas['teacherList'];//对话框需要
				$strTeachersId = $datas['teacherList'];
				$this->assign('strTeachersId', $strTeachersId);
				
				//已提交的已选择考场，预先复选中
				$arr_checked = array();
				$arr_checked = explode(',',$datas['kaochangId']);
				$kaochangModel = D('SchoolKaochang');
				if ( !empty( $arr_checked )){
					foreach ($arr_checked as $v){ 
						$one = $kaochangModel->where('id='.intval($v))->field('id,name')->find(); 
						if ($one){//忽略首尾的0
							
							$kaochang_list[]=array('id'=>$one['id'],'name'=>$one['name']);
						}
					} 
				}
				$this->assign('kaochang_list', $kaochang_list);//zjh add
				
				//已提交的已选择科目，预先复选中
				$arr_checked = array();
				$arr_checked = explode(',',$datas['subjectId']);
				$subjectModel = D('SchoolSubjects');
				if ( !empty( $arr_checked )){
					foreach ($arr_checked as $v){ 
					 $one = $subjectModel->where('id='.$v)->field('id,name')->find(); 
					 if ($one){//忽略首尾的0
					 	$subject_list[]=array('id'=>$one['id'],'name'=>$one['name']);
					 }
					} 
				}
				$this->assign('subject_list', $subject_list);
								
				
				//考场为单选
				$seltype = "radio";
				$this->assign('kaochangModalSelType', $seltype);//弹出框中只允许单选
				
				//考场列表 START
				$kaochangModel = D('SchoolKaochang');
				$map = array();
				$kaochangs = $kaochangModel->where($map)->select();
				$this->assign('kaochangs', $kaochangs);//var_dump($kaochangs);
				//列表 END	
				
				$seltype = "radio";
				$this->assign('subjectModalSelType', $seltype);//弹出框中只允许单选
				
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
				//列表 END	
				
				//班级列表 START
				$banjiModel = D('SchoolBanji');
				$map = array();
				$banjis = $banjiModel->getList($map);
				$this->assign('banjis', $banjis);
				//班级列表 END
				
				//考试时间列表 START
				$this->assign('examTimeModalSelType',"radio");//弹出框中只允许单选
				
				$examinationTimeModel = D("SchoolExaminationTime"); 
				$examTimes = $examinationTimeModel->field("id,convert(VARCHAR(24),beginTime,120) as starttime,convert(VARCHAR(24),endTime,120) as endtime")->order("starttime DESC")->select();
				//var_dump($examTimes);
				$dateArr = array();
				foreach($examTimes as $k=>$v){
					if (!in_array(date("Y-m-d",strtotime($v['starttime'])),$dateArr)){
						$dateArr[] = date("Y-m-d",strtotime($v['starttime']));//每个日期作为一个数组元素
					}
					//$examTimes[$k]['thedate'] = date("Y-m-d",strtotime($v['starttime']));
				}//无键名的日期数组
				
				//var_dump($dateArr);
				
				$dateArr2 = array();
				foreach($dateArr as $k=>$v){
					$dateArr2[] = array("thedate"=>$v);
				}//形成带键名的日期数组
				//var_dump($dateArr2);
				
				foreach($dateArr2 as $k=>$v){
					$tmp=array();
					foreach($examTimes as $kk=>$vv){
						if ($v['thedate']==date("Y-m-d",strtotime($vv['starttime']))){
							//格式：array("stime"=>"08:00:00","etime"=>"09:00:00")
							$tmp[] = array("stime"=>date("H:i:s",strtotime($vv['starttime'])),"etime"=>date("H:i:s",strtotime($vv['endtime'])),"starttime"=>$vv['starttime'],"endtime"=>$vv['endtime']);
						}
					}
					$dateArr2[$k]['timearr'] = $tmp;
					//var_dump($tmp);
				}
				//var_dump($dateArr2);
				
				$this->assign('examTimes', $dateArr2);
				$this->assign('dateArr', $dateArr);
				//考试时间列表 END
				
			}
			
			$this->assign('datas', $datas);
			$this->display("editKaochangExamplan");
		}
	}
	
	/**
	 * 考试安排：删除　
	*/
	public function delKaochangExamplan(){
		/*
		$id = I("request.id",0,"int");
		$model = D('SchoolKaochangExamPlan');
		$result = $model->where("id=".$id)->delete();
		if ($result !== FALSE) {
		   $this->success('操作成功！', U('School/kaochangExamplanList'));
		} else {
		   $this->error('操作失败！[原因]：' . $model->getError());
		} */
		
	    $ids = trim(I('post.nids'));
	
	    if (empty($ids)) {
	        die(json_encode(array('stat'=>0, '请求参数错误！')));
	    }
	
	    $idsArr = explode(',', trim($ids, ','));
	    if (count($idsArr) <= 0) {
	        die(json_encode(array('stat'=>0, '请求参数错误！')));
	    }
	
	    $model = D('SchoolKaochangExamPlan');
	    $result = $model->where(array('Id'=>array('in', $idsArr)))->delete();
	    if ($result !== false) {
	        die(json_encode(array('stat'=>1, '操作成功！')));
	    } else {
	        die(json_encode(array('stat'=>0, '操作失败！[原因]：' . $resModel->getError())));
	    }		
		
		
	}
	
	/**
	 * 作业列表
	*/
	
	/**
	 * 作业-学生-列表（学生与作业对应关系）
	*/
	
	/**
	 * 增加作业
	*/
	
	/**
	 * 修改作业
	*/
	
	/**
	 * 删除作业
	*/
	
	/**
	 * 批量生成一个作业的提交记录，全班每个学生一条
	*/
	
	/**
	 * 作业提交情况
	*/
	public function homeworkSubmit(){
		$type = trim(I("request.type"));
		$banjiId = I("request.banjiId",0,"int");
		if (empty($type)){
			$type = "banjiList";
		}
		$user = D("User");
		
		switch ($type){
			case "banjiList":
				//班级列表 START
				$banjiModel = D('SchoolBanji');
				$map = array();
				if (session("username") == C('ADMIN_AUTH_KEY')) {
					//超级管理员，列表中显示全部班级
					;
				}else{
					//非超级管理员，班级列表中只显示有权限的
					$map['id'] = array("IN",session('user_banji_list'));
				}
				//$banjiCount = $banjiModel->where($map)->count();
				$banjis = $banjiModel->where($map)->order("id DESC")->select();
				$this->assign('banjis', $banjis);
				//班级列表 END
				
				$result = $user->userBanjiListCount();//可管理班级是否是1
		
				if ($result){
					redirect('/School/homeworkSubmit/type/subjectList/banjiId/'.$result, 0, '页面跳转中...');exit;
				}
				
				
		
				$this->display("homeworkSubmit_index");//列出班级
				break;
			case "subjectList":
				//列出科目TB_Sch_Banji_Teacher_Subject
				$banjiModel = D('SchoolBanji');
				$btsModel = D("SchoolBanjiSubjectTeacher");
				$banjiId = I("request.banjiId",0,"int");
				if (!$banjiId){
					$this->error("班级ID为空");	
				}
				
				$studentNumber = $banjiModel->getStudentCountOfBanji($banjiId);
				$this->assign('studentNumber', $studentNumber);
				
				//作业提交情况初始化
				$homeworkSumbitModel = D('SchoolHomeworkSubmit');
				$homeworkSumbitModel->resetHomeworkSubmit($banjiId);
				
				$datas = $homeworkSumbitModel->where("banjiId=".$banjiId)->select();
				$subjectsModel = D('SchoolSubjects');
				foreach($datas as $k=>$v){
					$subject = $subjectsModel->where("id=".$v['subjectId'])->field("name")->find();
					$datas[$k]['subjectName'] = $subject['name'];
				}
				
				$datas_banji = $banjiModel->getOne($banjiId);
				$banjiName = $datas_banji['name'];
				
				$this->assign('banjiName', $banjiName);
				$this->assign('datas', $datas);
				$this->display("homeworkSubmit_subjects");
				break;
			case "updateOne":
				$id = I("request.id",0,"int");
				$quantity = I("request.quantity",0,"int");
				$homeworkSumbitModel = D('SchoolHomeworkSubmit');
				$homeworkSumbitModel->where("id=".$id)->setField("quantity",$quantity);//更新一个字段
				echo json_encode(array("stat"=>"1","msg"=>"更新成功","data"=>$datas));
				break;
			default:
				;
		}

		
		
	}
	
	/**
	 * 统计某一个班级某天的作业提交情况
	 * 参数：班级ID，年-月-日,科目
	*/
	public function homeworkCountOne($banjiId=0,$date='',$subjectId=0){
		if (!$banjiId || $date=='' || !$subjectId){
			return 0;
		}
		//获取到本班全部学生数，即应交作业人数
		$stuModel = D('SchoolStudents');
		$stu_num = $stuModel->where("banjiId=".$banjiId)->count();//var_dump($stu_num);
		
	//	echo '<br>班级ID：'.$banjiId;
	//	echo '<br>科目Id：'.$subjectId;
	//	echo '<br>本班学生总数：'.$stu_num;
		
		
		//
		//本班指定天的，指定科目的作业ID
		$hwkModel = D("SchoolHomework");
		$homeworkIds = $hwkModel->getHwkIds($banjiId,$date,$subjectId,'str');//var_dump($homeworkIds);
	//	echo "<br>符合条件的作业ID：".$homeworkIds;
		
		$homeworkIdsArr = $hwkModel->getHwkIds($banjiId,$date,$subjectId,'arr');//var_dump($homeworkIds);
	//	echo "<br>符合条件的作业ID数组：";
	//	var_dump($homeworkIdsArr);
		$out = array();
		
		foreach ($homeworkIdsArr as $k=>$v){
			$out[$k]['homeworkId'] = $v;//结果数组，作业id
		//	echo "<br>作业ID=".$v;
	
			$hmk = $hwkModel->where("id=".$v)->field("description as name")->find();
			$out[$k]['homeworkName'] = $hmk['name'];
			
			//统计指定作业的完成情况
			$model_hmk_count = D("SchoolHomeworkSubmit");
			$result = $model_hmk_count->where("homeworkId=".$v)->count();//总条数，理论上应该==本班学生总数
			$out[$k]['stuNum'] = $result;
			
			//统计已提交
			$resultIsSubmit = $model_hmk_count->where("homeworkId=".$v." and isSubmit=1")->count();
			$out[$k]['isSubmitNum'] = $resultIsSubmit;
			
			//统计未提交
			$resultNoSubmit = $model_hmk_count->where("homeworkId=".$v." and isSubmit=0")->count();
			$out[$k]['noSubmitNum'] = $resultNoSubmit;
			
			$out[$k]['isSubmitList'] = "张三,李四";
			$out[$k]['noSubmitNumList'] = "王五,赵六";
			
		}
		
		//var_dump($out);//各作业的提交统计，多维
		
		
		//获取到指定日期指定科目的作业（一科目可能有多条作业）
		
		
		//echo "<br>";
		
		return $out;
	}
	
	
	/**
	 * 作业提交
	*/

	
	
	
//////////////////////董工 start ////////////////////////////////////////////////////////////	
	
	
    /**
	 * 摄像头列表
	 *　author:mqy
	*/
    public function cameraList() {
		$modal = D("SchCamera");
        // 加载数据分页类
        import('ORG.Util.Page');
        // 数据分页
        $totals = $modal->count();
        $Page = new Page($totals, 12);
        $show = $Page->show();
        $this->assign('page', $show);			
		$datas = $modal->order("Id DESC")->select(); 
		$this->assign('datas', $datas);	
		$EndpointModel = D('Endpoint');
		$Endpoint = $EndpointModel->select();
        $this->assign('Endpoint', $Endpoint);
		$this->display();
	}
	
	/**
	 * 添加摄像头信息
	 * author:mqy
	*/
    public function addCamera() {
       if (IS_POST) {
        $data = array();
			$Model = D('SchCamera');
            $data['code'] = I('post.Name');	 
			$data['EndpointId'] = I('post.EndpointId');
			$data['ip'] = I('post.ip');
            $data['mac'] = I('post.mac');
            $data['modelNum'] = I('post.modelNum');
            $data['username'] = I('post.username');
            $data['password'] = I('post.password');
            $data['port'] = I('post.port');
			$result = $Model->data($data)->add();

		   // 执行操作
		   if ($result !== FALSE) {
			   //$insertId = $result;
			   $this->success('操作成功！', U('School/cameraList'));
		   } else {
			   $this->error('操作失败！[原因]：' . $Model->getError());
		   }

        } else {
            
			$EndpointModel = D('Endpoint');
            $Endpoint = $EndpointModel->select();
            $this->assign('Endpoint', $Endpoint);
			
			
            $this->display("editCamera");
        }			

	}	
	
	/**
	 * 修改摄像头信息
	 * author:mqy
	*/
     public function editCamera() {
		 
       if (IS_POST) {
			$actiModel = D('SchCamera');
            $id = I('request.id', 0, 'int');
			
			$data['Id'] = $id;
            $data['code'] = I('post.Name');	 
			$data['EndpointId'] = I('post.EndpointId');
			$data['ip'] = I('post.ip');
            $data['mac'] = I('post.mac');
            $data['modelNum'] = I('post.modelNum');
            $data['username'] = I('post.username');
            $data['password'] = I('post.password');
            $data['port'] = I('post.port');
			$result = $actiModel->save($data);
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/cameraList'));
			} else {
			   $this->error('操作失败！[原因]：' . $actiModel->getError());
			}            

            
        } else {		 
		 
			$id = I('get.id',0,'intval');
			$actiModel = D('SchCamera');
			$map['Id'] = $id;
			$actiInfo = $actiModel->where($map)->find();
			$EndpointModel = D('Endpoint');
			$Endpoint = $EndpointModel->select();
            $this->assign('Endpoint', $Endpoint);
			if ($actiInfo) {
				$this->assign('actiInfo', $actiInfo);
				$this->display();           
			}else{
				//错误student
				 $this->error('操作失败！[原因]：' . $actiModel->getError());
			}
		}
		 
		 
		
	}	
		
	/**
	 * 删除摄像头信息
	 * author:mqy
	*/
     public function delCamera() {
		//$this->success('（假的）操作成功！', U('Mall/activities'));
		$id = I('request.ID', 0, 'int');
		
		if (!$id) {
			$this->error('非法操作！');
		}
		$actiModel = D('SchCamera');
		$actiInfo = $actiModel->where(array('Id'=>$id))->find();
		if (!$actiInfo) {
			$this->error('无此信息！');
		}
		
        $delResult = $actiModel->where(array('Id'=>$id))->delete();
        if ($delResult !== false) {
           $this->success('操作成功！', U('School/cameraList'));
        } else {
           $this->error('操作失败！[原因]：' . $actiClassModel->getError());
        }			
		
		
	}			
		/**
	 * 活动分类列表
	 * author:zjh
	*/
     public function monitorList() {
        // 层级关系格式化分类数据
        $model = D('SchMonitor');
		
          // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
        $totals = $model->where($map)->count();
        $Page = new Page($totals, 10);
        $show = $Page->show();
        $this->assign('page', $show);	
		
        $sortedTypes = $model->limit($Page->firstRow.','.$Page->listRows)->order("Id DESC")->select();
        $this->assign('storeTypes', $sortedTypes);
        $EndpointModel = D('Endpoint');
	    $Endpoint = $EndpointModel->select();
        $this->assign('Endpoint', $Endpoint);
		$this->display();
	}	
	
	
	/**
	 * 添加监测设备
	 * author:zjh
	*/
    public function addMonitor() {
        if (IS_POST) {
			// 处理表单提交参数
			$tName = I('post.Num');
			$sortnum = I('post.EndpointId', 0, 'int');
			
			// 实例化分类模型
			$actiClassModel = D('SchMonitor');
			$data['Num'] = $tName;
			$data['EndpointId'] = $sortnum;
			
			// 执行操作
			$result = $actiClassModel->data($data)->add();
			if ($result !== FALSE) {
				$this->success('操作成功！', U('School/monitorList'));
			} else {
				$this->error('操作失败！[原因]：' . $actiClassModel->getError());
			}
        } else {
			$EndpointModel = D('Endpoint');
			$Endpoint = $EndpointModel->select();
            $this->assign('Endpoint', $Endpoint);
            $this->display('editMonitor');
        }
    }	
	
	/*
	 * 修改环境监测设备
	*/
	public function editMonitor(){
        if (IS_POST) {
			// 处理表单提交参数
			$id = I('post.id', 0, 'int');
			$tName = I('post.Num');
			$sortnum = I('post.EndpointId', 0, 'int');
			
			// 实例化分类模型
			$actiClassModel = D('SchMonitor');
			
			$data['Num'] = $tName;
			$data['EndpointId'] = $sortnum;
			
			$map=array();
			$map['Id']=$id;
            $result = $actiClassModel->where($map)->save($data);
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/monitorList'));
			} else {
			   $this->error('操作失败！[原因]：' . $actiClassModel->getError());
			}            
        } else {
            $id = I('get.tid', 0, 'int');
            if (!$id) {
                $this->error('非法操作！');
            }
            $actiClassModel = D('SchMonitor');
            $classInfo = $actiClassModel->where(array('Id'=>$id))->find();
            if (!$classInfo) {
                $this->error('非法操作！');
            }
            
            $this->assign('classInfo', $classInfo);
			$EndpointModel = D('Endpoint');
			$Endpoint = $EndpointModel->select();
            $this->assign('Endpoint', $Endpoint);
            
            $this->display();
        }
	}
	
	/**
	 * 删除环境监测设备
	 * author:zjh
	*/
    public function delMonitor() {
        $id = I('get.tid', 0, 'int');
        if (!$id) {
            $this->error('非法操作！');
        }
        
        $actiClassModel = D('SchMonitor');
        $brandClassInfo = $actiClassModel->where(array('Id'=>$id))->find();
        if (!$brandClassInfo) {
            $this->error('非法操作，不存在该设备信息！');
        }
        
        $delResult = $actiClassModel->where(array('Id'=>$id))->delete();
        if ($delResult !== false) {
           $this->success('操作成功！', U('School/monitorList'));
        } else {
           $this->error('操作失败！[原因]：' . $actiClassModel->getError());
        }	
	}	
	
		/**
	 * 读卡器列表
	 * author:zjh
	*/
     public function readerList() {
        // 层级关系格式化分类数据
        $model = D('SchReader');
		
        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
        $totals = $model->where($map)->count();
        $Page = new Page($totals, 10);
        $show = $Page->show();
        $this->assign('page', $show);	
		
        $sortedTypes = $model->limit($Page->firstRow.','.$Page->listRows)->order("Id DESC")->select();
        $this->assign('storeTypes', $sortedTypes);
        $EndpointModel = D('Endpoint');
	    $Endpoint = $EndpointModel->select();
        $this->assign('Endpoint', $Endpoint);
		$this->display();
	}	
	
	
	/**
	 * 增加读卡器
	 * author:mqy
	*/
    public function addReader() {
        if (IS_POST) {
			// 处理表单提交参数
			$tName = I('post.Num');
			$sortnum = I('post.EndpointId', 0, 'int');
			// 实例化分类模型
			$actiClassModel = D('SchReader');
			$data['Num'] = $tName;
			$data['EndpointId'] = $sortnum;
			
			// 执行操作
			$result = $actiClassModel->data($data)->add();
			if ($result !== FALSE) {
				$this->success('操作成功！', U('School/reader'));
			} else {
				$this->error('操作失败！[原因]：' . $actiClassModel->getError());
			}
        } else {
			$EndpointModel = D('Endpoint');
			$Endpoint = $EndpointModel->select();
            $this->assign('Endpoint', $Endpoint);
            $this->display('editReader');
        }
    }	
	
	public function editReader(){
        if (IS_POST) {
			// 处理表单提交参数
			$id = I('post.id', 0, 'int');
			$tName = I('post.Num');
			$sortnum = I('post.EndpointId', 0, 'int');
			
			// 实例化分类模型
			$actiClassModel = D('SchReader');
			
			$data['Num'] = $tName;
			$data['EndpointId'] = $sortnum;
			
			$map=array();
			$map['Id']=$id;
            $result = $actiClassModel->where($map)->save($data);
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/reader'));
			} else {
			   $this->error('操作失败！[原因]：' . $actiClassModel->getError());
			}            
        } else {
            $id = I('get.tid', 0, 'int');
            if (!$id) {
                $this->error('非法操作！');
            }
            $actiClassModel = D('SchReader');
            $classInfo = $actiClassModel->where(array('Id'=>$id))->find();
            if (!$classInfo) {
                $this->error('非法操作！');
            }
            
            $this->assign('classInfo', $classInfo);
			$EndpointModel = D('Endpoint');
			$Endpoint = $EndpointModel->select();
            $this->assign('Endpoint', $Endpoint);
            
            $this->display();
        }
	}
	
	/**
	 * 删除活动分类
	 * author:mqy
	*/
    public function delReader() {
        $id = I('get.tid', 0, 'int');
        if (!$id) {
            $this->error('非法操作！');
        }
        
        $actiClassModel = D('SchReader');
        $brandClassInfo = $actiClassModel->where(array('Id'=>$id))->find();
        if (!$brandClassInfo) {
            $this->error('非法操作，不存在该设备信息！');
        }
        
        $delResult = $actiClassModel->where(array('Id'=>$id))->delete();
        if ($delResult !== false) {
           $this->success('操作成功！', U('School/reader'));
        } else {
           $this->error('操作失败！[原因]：' . $actiClassModel->getError());
        }	
	}	
	
	/*
	 * 相册列表
	 */
    public function albumList() {
		$type = trim(I("request.type"));
		if (empty($type)){
			$type = "banjiList";
		}
		switch ($type){
			case "banjiList":
				$user = D("User");
				//班级列表 START
				$banjiModel = D('SchoolBanji');
				$map = array();
				if (session("username") == C('ADMIN_AUTH_KEY')) {
					;//超级管理员，列表中显示全部班级
				}else{
					//非超级管理员，班级列表中只显示有权限的
					$map['id'] = array("IN",session('user_banji_list'));
				}
				//$banjiCount = $banjiModel->where($map)->count();
				$banjis = $banjiModel->where($map)->order("id DESC")->select();
				
				$albumModal = D("SchAlbums");
				foreach($banjis as $k=>$v){
					$count = $albumModal->where("banjiId=".$v['id'])->count();
					$banjis[$k]['albumnum'] = $count;
				}
				$this->assign('banjis', $banjis);
				//班级列表 END
				
				$result = $user->userBanjiListCount();//可管理班级是否是1
		
				//只可管理一个表的话，直接显示荣誉列表
				if ($result){
					redirect('/School/albumList/type/albumList/banjiId/'.$result, 0, '页面跳转中...');exit;
				}
				
				//显示班级列表
				$this->display("albumBanjiList");//列出班级
				break;
			case "albumList":	
				$albumModal = D("SchAlbums");
				$map = array();
				
				$keyboard = I('request.keyboard');
				$searchtype = I('request.searchtype');
				$albumClassId = I('request.albumClassId');
				if (!empty($keyboard)){
					switch ($searchtype)
					{
					case 'aName':
						$map['aName']  = array('LIKE','%'.$keyboard.'%');
		
						break;  
					case 'id':
						$map['id']  = $keyboard;
						break;
					default:
						$map['aName']  = array('LIKE','%'.$keyboard.'%');
					}
					
				} 	 
				if ($albumClassId){
					$map['classId']  = $albumClassId;
				}
				$banjiId = I("request.banjiId",0,"int");
				if ($banjiId){
					$map['banjiId'] = $banjiId;	
					if (session("username") == C('ADMIN_AUTH_KEY')) {
						//超级管理员，列表中显示全部班级
					}else{
						$this->check_banji($banjiId);//是否可操作此班级ID
					}
				} else{
					if (session("username") == C('ADMIN_AUTH_KEY')) {
						//超级管理员，列表中显示全部班级
					}else{
						//非超级管理员，班级列表中只显示有权限的
						$map['banjiId'] = array("IN",session('user_banji_list'));
					}
					
				}
				
				
				// 加载数据分页类
				import('ORG.Util.Page');
				
				// 数据分页
				$totals = $albumModal->where($map)->count();
				$Page = new Page($totals, 10);
				$show = $Page->show();
				$this->assign('page', $show);	
				
				$albums = $albumModal->where($map)->limit($Page->firstRow. ',' .$Page->listRows)->order('id desc')->select(); 
				
				
				//$resModel = D('SCResources');
				$ablumPhotoModel = D("SchAlbumsPhotos");
				
				//重新组织信息
				foreach ($albums as $k => $v){
					$one=$albumModal->table("TB_Sch_Banji")->where("id=".$v['banjiId'])->find();
					$albums[$k]['banjiName'] = $one['name'];
					
					$count = $ablumPhotoModel->where("albumId=".$v['id'])->count();
					$albums[$k]['photonum'] = $count;
					
					//封面
					//$coverImage = $resModel->where("resourceId='".$albums[$k]['coverImage']."'")->find();
					//$albums[$k]['coverImageFile'] = C("UPLOAD_COMM_PATH").$coverImage['resFilepath'];
					//var_dump($albums[$k]['coverImageFile']);
				
				}
		
		
						//相册分类
						$albumClassModel = D('SCAlbumClass');
						$originTypes = $albumClassModel->order('Pid asc, ID asc')->select();
						$albumClass = array();
						$albumClassModel->sortedTypes($albumClass, $originTypes);
						$this->assign('albumClassId', $albumClass);	
				
				$this->assign('albums', $albums);	
				$this->display("albumList");
				break;

			default:
				exit;
		}					
		

		
		//$this->display();
	}
	
	/**
	 * 添加相册
	 * author:mqy
	*/
     public function addAlbum() {
		if (IS_POST) { 
			$Model = D('SchAlbums');

			//$data['id'] = $id;
            $data['name'] = I('post.aName');	 
			//$data['classId'] = '';
			//$data['aPhotoCounts'] = 0;
			//$data['coverImage'] = '';
			$data['description'] = I('post.description');	
			//$data['aContent'] = I('post.aContent');
			$data['sort'] = I('post.sortnum', 0, 'int');
			$data['createTime'] = time();
            $data['updateTime'] = time();
			$data['banjiId'] = I('post.banjiId');
			
			$result = $Model->data($data)->add();

		   // 执行操作
		   if ($result !== FALSE) {
			   //$insertId = $result;
			   $this->success('操作成功！', U('School/albumList'));
		   } else {
			   $this->error('操作失败！[原因]：' . $Model->getError());
		   }
			
			
			//获取表单提交
			
			
			//入库
		}
		else {
			//班级列表 START
			$banjiModel = D('SchoolBanji');
			$map = array();
			if (session("username") == C('ADMIN_AUTH_KEY')) {//session("username")
				//超级管理员，列表中显示全部班级
				;
			}else{
				//非超级管理员，班级列表中只显示有权限的
				$map['id'] = array("IN",session('user_banji_list'));
			}
			$banjis = $banjiModel->where($map)->select();
			$this->assign('banjis', $banjis);
			//班级列表 END
	
			
			$this->display("editAlbum");
		}
		
	}		
	
	/**
	 * 修改相册
	 * author:mqy
	*/
     public function editAlbum() {
        if (IS_POST) {
			$albumModel = D('SchAlbums');
            $id = I('request.id', 0, 'int'); 
			$data['id'] = $id;
            $data['name'] = I('post.aName');	 
			//$data['classId'] = '';
			//$data['aPhotoCounts'] = 0;
			//$data['coverImage'] = '';
			$data['description'] = I('post.description');	
			//$data['aContent'] = I('post.aContent');
			$data['sort'] = I('post.sortnum', 0, 'int');
            $data['updateTime'] = time();
			$data['banjiId'] = I('post.banjiId');
						
			$result = $albumModel->save($data);
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/albumList'));
			} else {
			   $this->error('操作失败！[原因]：' . $albumModel->getError());
			}  			
			
		} else {
			$id = I('get.id',0,'intval');
			$albumModel = D('SchAlbums');
			$map['id'] = $id;
			$albumInfo = $albumModel->where($map)->find();
			
			if ($albumInfo) {

				//班级列表 START
				$banjiModel = D('SchoolBanji');
				$map = array();
				if (session("username") == C('ADMIN_AUTH_KEY')) {//session("username")
					//超级管理员，列表中显示全部班级
					;
				}else{
					//非超级管理员，班级列表中只显示有权限的
					$map['id'] = array("IN",session('user_banji_list'));
				}
				$banjis = $banjiModel->where($map)->select();
				$this->assign('banjis', $banjis);
				//班级列表 END
				
				$this->assign('album', $albumInfo);
				$this->display();           
			}else{
				//错误	
			}				
		}	
	}
	
	/*
	 * 删除相册
	*/	
    public function delAlbum() {
        $id = I('get.id', 0, 'int');
        if (!$id) {
            $this->error('非法操作！');
        }
        
        $SchAlbumsModel = D('SchAlbums');
        $album = $SchAlbumsModel->where(array('id'=>$id))->find();
        if (!$album) {
            $this->error('非法操作，不存在该相册！');
        }else{
			if ($album['isDefault']==1){
				$this->error('班级风采相册不允许删除！');
			}	
		}
		
		//删除相册下的所有图片
		$abbumPhotoModel = D("SchAlbumsPhotos");
		$datas = $abbumPhotoModel->where("albumId=".$id)->field("filepath")->select();
		foreach($datas as $k=>$v){
			//判断文件是否存在，是则删除文件
			$filepath = str_replace( '\\', '/',$v['filepath']);	
			@unlink($filepath);
			var_dump($filepath);
		}

        $delResult = $SchAlbumsModel->where(array('id'=>$id))->delete();
        if ($delResult !== false) {
           $this->success('操作成功！', U('School/albumList'));
        } else {
           $this->error('操作失败！[原因]：' . $SchAlbumsModel->getError());
        }
	}	
	
	/*
	 * 增加相片
	*/
	public function addPhotos() {
	    $albumId = I('request.id',0,'intval');
	    $albumModel = D('SchAlbums');
	    $map = array();
	    $map['id'] = $albumId;
	    $albumInfo = $albumModel->where($map)->find();//相册记录
	    if ($albumInfo){
			$banjiId = $albumInfo['banjiId'];
			$this->assign("banjiId",$banjiId);
	           $banji=$albumModel->table("TB_Sch_Banji")->where("id=".$albumInfo['banjiId'])->find();
               $albumInfo['banjiName']=$banji['name'];
	        $this->assign('albumInfo', $albumInfo);
	    }	
		
	    //相册的图片列表
	    $resModel = D('SchAlbumsPhotos');
	    $condition = array();
	    $condition['albumId'] = $albumId;
	    	
	    // 加载数据分页类
	    import('ORG.Util.Page');
	    	
	    // 数据分页
	    $totals = $resModel->where($condition)->count();
	    $Page = new Page($totals, 10);
	    $show = $Page->show();
	    $this->assign('page', $show);
	    	
	    $photos = $resModel->where($condition)->limit($Page->firstRow. ',' .$Page->listRows)->order("id desc")->select();
	    if ($photos){
	        $this->assign('photos', $photos);
	    }

	    $this->display("editPhotos");
	}
	
	/**
	 * 删除相册中的一张相片
	 * author:mqy
	 */
	public function delPhoto() {
	
	
	    $Id = trim(I('get.resourceId'));
	    header("Content-type: text/html; charset=utf-8");
		if (!empty($Id)) {
                $sucaiRootPath = rtrim(C('UPLOAD_COMM_PATH'), '/') . '/';
				$rmfModel = M('SchAlbumsPhotos');
				$fileList = $rmfModel->where(array('id'=> $Id))->find();
                if($fileList){
                        $fileList['filepath'] = $fileList['filepath'];//utf8ToGbk($fileList['filepath']);
                        if (trim($fileList['filepath']) != '' && is_file($sucaiRootPath . $fileList['filepath'])) {
                            @unlink($sucaiRootPath . $fileList['filepath']);
                            $delRe = $rmfModel->where(array('id'=>$Id))->delete();
					    }else{
					       //exit(json_encode(array('stat'=>0, 'msg'=>'数据请求错误！文件路径错误！请检查该图片路径')));
						   $delRe = $rmfModel->where(array('id'=>$Id))->delete();
					    }
                        
                }else{
                    exit(json_encode(array('stat'=>0, 'msg'=>'操作失败！[原因]：' . $rmfModel->getError())));
                }
                if ($delRe !== false) {
				    exit(json_encode(array('stat'=>1,'msg'=>'操作成功！')));
                } else {
				    exit(json_encode(array('stat'=>0, 'msg'=>'操作失败！[原因]：' . $rmfModel->getError())));
                }
		} else {
			exit(json_encode(array('stat'=>0, 'msg'=>'数据请求错误！请刷新页面重试……')));
		}
	}	
	
	/**
	 * 添加视频集
	*/
     public function addVideoGroup() {
		if (IS_POST) { 
			$model = D('SchoolVideoGroup');
			
			$banjiId = I('request.banjiId',0,"int");
            $data['name'] = I('post.name');	 
			$data['icon'] = I('post.photo');
			$data['description'] = I('post.description');	
			$data['banjiId'] = $banjiId;
			
		//	var_dump($data);exit;
			
			$result = $model->data($data)->add();

            // 执行操作
            if ($result !== FALSE) {
               $this->success('操作成功！', U('School/videoList',array("type"=>"videoGroupList","banjiId"=>$banjiId)));
            } else {
               $this->error('操作失败！[原因]：' . $model->getError());
            }
			
		}
		else {
			$groupId = I('request.groupId',0,"int");
			$this->assign('groupId', $groupId);
			$banjiId = I('request.banjiId',0,"int");
			$this->assign('banjiId', $banjiId);
			
			//班级列表 START
			$banjiModel = D('SchoolBanji');
			$map = array();
			if (session("username") == C('ADMIN_AUTH_KEY')) {//session("username")
				//超级管理员，列表中显示全部班级
				;
			}else{
				//非超级管理员，班级列表中只显示有权限的
				$map['id'] = array("IN",session('user_banji_list'));
			}
			$banjis = $banjiModel->where($map)->select();
			$this->assign('banjis', $banjis);
			//班级列表 END		

			
			$this->display("editVideoGroup");
		}
		
	}		
	
	/**
	 * 修改视频集
	 * author:mqy
	*/
     public function editVideoGroup() {
        if (IS_POST) {
			$videoGroupModel = D('SchoolVideoGroup');
            $id = I('request.id', 0, 'int'); 
			//$banjiId = I('request.banjiId',0,"int");
			
			$data['id'] = $id;
            $data['name'] = I('post.name');	 
			$data['icon'] = I('post.photo');
			$data['description'] = I('post.description');	
			//$data['groupId'] = I('post.groupId', 0, 'int');
			//$data['banjiId'] = $banjiId;
						
			$result = $videoGroupModel->save($data);
			if ($result !== FALSE) {
				$banjiId=$videoGroupModel->where("id=".$id)->getField('banjiId');
				
			   $this->success('操作成功！', U('School/videoList',array("type"=>"videoGroupList","banjiId"=>$banjiId)));
			} else {
			   $this->error('操作失败！[原因]：' . $videoGroupModel->getError());
			}  			
			
		} else {
			$id = I('get.id',0,'intval');
			$videoGroupModel = D('SchoolVideoGroup');
			
			//班级列表 START
			$banjiModel = D('SchoolBanji');
			$map = array();
			if (session("username") == C('ADMIN_AUTH_KEY')) {//session("username")
				//超级管理员，列表中显示全部班级
				;
			}else{
				//非超级管理员，班级列表中只显示有权限的
				$map['id'] = array("IN",session('user_banji_list'));
			}
			$banjis = $banjiModel->where($map)->select();
			$this->assign('banjis', $banjis);
			//班级列表 END
			
			
			$map['id'] = $id;
			$datas = $videoGroupModel->where($map)->find();
			if ($datas) {
				//班级列表
                $SchoolBanjiModel = D('SchoolBanji');
                $SchoolBanji = $SchoolBanjiModel->select();
                $this->assign('SchoolBanji', $SchoolBanji);			
				
				$this->assign('banjiId', $datas['banjiId']);
				$this->assign('videoGroup', $videoGroupInfo);
			}else{
				//错误	
			}		
			$this->assign('datas', $datas);
			$this->display("editVideoGroup"); 
		}	
	}
	
	/*
	 * 删除视频集
	*/
    public function delVideoGroup() {
        $id = I('get.id', 0, 'int');
        if (!$id) {
            $this->error('非法操作！');
        }
        
        $videoGroupModel = D('SchoolVideoGroup');
        $result = $videoGroupModel->where(array('id'=>$id))->find();//视频集存在？
        if (!$result) {
            $this->error('非法操作，不存在该信息！');
        }else{
			$banjiId = $result['banjiId'];
			$this->check_banji($banjiId);//是否可操作此班级ID
		}

		//查找视频集下的所有视频，并逐条删除
		$videoModel = D("SchoolVideo");
		$datas_video = $videoModel->where("groupId=".$id)->select();
		foreach ($datas_video as $k=>$v){
			if (trim($v['filePath']) != '' && is_file($v['filePath'])) {
				@unlink($v['filePath']);
			}else{
				;
			}
			$delResult = $videoModel->where("id=".$v['id'])->delete();
		}



        $delResult = $videoGroupModel->where(array('id'=>$id))->delete();
        if ($delResult !== false) {
           $this->success('操作成功！', U('School/videoList'));
        } else {
           $this->error('操作失败！[原因]：' . $videoGroupModel->getError());
        }
	}	
	
	/**
	 * 视频列表
	*/
    public function videoList() {
		$type = trim(I("request.type"));
		if (empty($type)){
			$type = "banjiList";
		}
		switch ($type){
			case "banjiList":
				$user = D("User");
				//班级列表 START
				$banjiModel = D('SchoolBanji');
				$map = array();
				if (session("username") == C('ADMIN_AUTH_KEY')) {
					;//超级管理员，列表中显示全部班级
				}else{
					//非超级管理员，班级列表中只显示有权限的
					$map['id'] = array("IN",session('user_banji_list'));
				}
				//$banjiCount = $banjiModel->where($map)->count();
				$banjis = $banjiModel->where($map)->order("id DESC")->select();
				
				$videoGroupModal = D("SchoolVideoGroup");
				$videoModal = D("SchoolVideo");
				foreach($banjis as $k=>$v){
					//本班视频集个数
					$count = $videoGroupModal->where("banjiId=".$v['id'])->count();
					$banjis[$k]['videoGroupNum'] = $count;
					
					$datas_group = $videoGroupModal->where("banjiId=".$v['id'])->field("id,name")->select();
					$tmp_id = array();
					$tmp_name = array();
					foreach($datas_group as $kk=>$vv){
						$tmp_id[] = $vv['id'];
						if ($kk<5){
						$tmp_name[] = $vv['name'];
						}
					}
					$groupIdStr = implode(",",$tmp_id);//逗号分隔的本班视频集ID
					$groupNameStr = implode("，",$tmp_name);//逗号分隔的本班视频集ID
					
					//本班视频总数
					$count = $videoModal->where("groupId in (".$groupIdStr.")")->count();
					$banjis[$k]['videoNum'] = intval($count);
					$banjis[$k]['groupName'] = $groupNameStr;
				}
				$this->assign('banjis', $banjis);
				//班级列表 END
				
				$result = $user->userBanjiListCount();//可管理班级是否是1
		
				//只可管理一个班的话，直接显示视频列表
				if ($result){
					redirect('/School/videoList/type/videoGroupList/banjiId/'.$result, 0, '页面跳转中...');exit;
				}
				
				//显示班级列表				
				$this->display("videoBanjiList");
				break;
			case "videoGroupList": //视频集列表	
				$videoGroupModal = D("SchoolVideoGroup");
				$videoModal = D("SchoolVideo");
				$banjiModel = D('SchoolBanji');
				$banjiId = I("request.banjiId",0,"int");
				$this->assign('banjiId', $banjiId);
				
				if (!$banjiId){
					$this->error("班级未指定");
				}
					
				$this->check_banji($banjiId);//是否可操作此班级ID
				$map['banjiId'] = $banjiId;
				
				// 加载数据分页类
				import('ORG.Util.Page');
				
				// 数据分页
				$totals = $videoGroupModal->where($map)->count();
				$Page = new Page($totals, 10);
				$show = $Page->show();
				$this->assign('page', $show);	
				$videoGroup = $videoGroupModal->where($map)->limit($Page->firstRow. ',' .$Page->listRows)->order('id desc')->select(); 
				//重新组织信息
				foreach ($videoGroup as $k => $v){
					$count = $videoModal->where("groupId=".$v['id'])->count();
					if ($count){
						$videoGroup[$k]['videonum'] = $count;
					}else{
						$videoGroup[$k]['videonum'] = 0;
					}
					
					$one=$banjiModel->where("id=".$v['banjiId'])->find();
					if($one){
						$videoGroup[$k]['banjiName'] = $one['name'];
					}
				}
				
				$this->assign('videoGroup', $videoGroup);
				$this->display("videoGroupList");
				break;
			case "videoFileList"://视频文件
				$groupId = I("request.id",0,"int");
				$videoModel = D('SchoolVideo');
				
				//处理批量删除
				//$dotype = I("request.dotype");//echo "dotype=".$dotype;
				$ids= I('get.ids', '', 'strip_tags');
				if (!empty($ids)){
					//$fileList = $videoModel->where(array('id'=>array('in', explode('-', $ids))))->select();
					$fileList = $videoModel->where("id in(".$ids.")")->select();
					foreach ($fileList as $file) {
						$file['filePath'] = utf8ToGbk($file['filePath']);
						if (trim($file['filePath']) != '' && is_file($file['filePath'])) {
							@unlink($file['filePath']);
						}
						// $result = $videoModel->where(array('id='.$ids))->delete();
					}					
					$result = $videoModel->where("id in(".$ids.")")->delete();
				}

				$banjiModel = D('SchoolBanji');
				$videoGroupModal = D("SchoolVideoGroup");
				$videoGroupData = $videoGroupModal->where("id=".$groupId)->find();
				if ($videoGroupData){
					$banjiId = $videoGroupData['banjiId'];
					$groupName = $videoGroupData['name'];
				}
				$banjiData = $banjiModel->getOne($banjiId);
				$banjiName = $banjiData['name'];
				
				$videoModel = D('SchoolVideo');
				
				$map = array();
				$map['groupId'] = $groupId;
				// 加载数据分页类
				import('ORG.Util.Page');
				
				// 数据分页
				$totals = $videoModel->where($map)->count();
				$Page = new Page($totals, 10);
				$show = $Page->show();
				$this->assign('page', $show);	
				$datas = $videoModel->where($map)->limit($Page->firstRow. ',' .$Page->listRows)->order('id desc')->select(); 

				foreach($datas as $k=>&$v){
					$datas[$k]['banjiName'] = $banjiName;
					$fullPath = iconv('UTF-8', 'GBK', $v[filePath]);
					$v[canDownload] = is_file($fullPath) ? 1 : 0;
				}
				
				$this->assign('group', $videoGroupData);
				$this->assign('groupId', $groupId);
				
				$this->assign('banjiId', $banjiId);
				$this->assign('banjiName', $banjiName);
				
				$this->assign('videos', $datas);
				$this->display("videoFileList");
				break;
			default:
				exit;
		}
	}
	
	
	
	
	public function addVideo() {
		if (IS_POST) { 
			$banjiId = I('post.banjiId',0,"int");
			$groupId = I('post.videoGroupId',0,"int");
			$Model = D('SchoolVideo');
            $data['title'] = I('post.name');	 
			$data['description'] = I('post.description');
            if(I('post.filePath')){
                $data['filePath'] = I('post.filePath');	
                $data['filename'] = I('post.filename');
                $data['originalFileName'] = I('post.filename');
                $data['filesize'] = I('post.filesize');
            }    
			$data['operationTime'] = time();
            //$data['updateTime'] = time();
			$data['banjiId'] = I('post.banjiId');
			$data['groupId'] = $groupId;
            $data['auditState']='1';
			$result = $Model->data($data)->add();
		   // 执行操作
		   if ($result !== FALSE) {
			   //$insertId = $result;
			   $this->success('操作成功！', U('School/videoList',array("type"=>"videoFileList","id"=>$groupId)));
		   } else {
			   $this->error('操作失败！[原因]：' . $Model->getError());
		   }
		}
		else {
			
			//
            $groupId = I("request.groupId",0,"int");
			$this->assign('groupId', $groupId);
			$this->assign('videoGroupId', $groupId);
			
			$this->display("editVideoList");
		}
		
	}
	
	/**
	 * 修改视频
	 * author:mqy
	*/
	
     public function editVideo() {
        if (IS_POST) {
			$videoModel = D('SchoolVideo');
            $id = I('request.id', 0, 'int'); 
			$groupId = 0;
			
			$tmp = $videoModel->where("id=".$id)->find();
			
			if ($tmp){
				$groupId = $tmp['groupId'];	
			}else{
				$this->error("无记录");	
			}
			
			$data['id'] = $id;
            $data['title'] = I('post.name');	 
			$data['description'] = I('post.description');
            if(I('post.filePath')){
                $data['filePath'] = I('post.filePath');
                $data['filename'] = I('post.filePath');
                $data['originalFileName'] = I('post.filePath');	
                $data['filesize'] = I('post.filesize');
            }else{
                if(I('post.oldFilePath')){
                    $data['filePath'] = I('post.oldFilePath');
                    $data['filename'] = I('post.oldFilePath');
                    $data['originalFileName'] = I('post.oldFilePath');	
                    $data['filesize'] = I('post.oldSize');  
                }
            } 	
			$data['operationTime'] = time();
            //$data['updateTime'] = time();
			$data['banjiId'] = I('post.banjiId');
						
			$result = $videoModel->save($data);
            if(I('post.oldFilePath')!=''&&I('post.filePath')!=''){
                $oldFilePath = trim(I('post.oldFilePath'));	
             
                $sucaiRootPath = rtrim(C('UPLOAD_COMM_PATH'), '/') . '/school/';
                $oldFilePath = utf8ToGbk($oldFilePath);
			    if (trim($oldFilePath) != '' && is_file($sucaiRootPath . $oldFilePath)) {
                    @unlink($sucaiRootPath . $oldFilePath);
			    }
            }
			if ($result !== FALSE) {
				
			   $this->success('操作成功！', U('School/VideoList',array("type"=>"videoFileList","id"=>$groupId)));
			} else {
			   $this->error('操作失败！[原因]：' . $videoModel->getError());
			}  			
			
		} else {
			$id = I('get.id',0,'intval');
			$videoModel = D('SchoolVideo');
			$map['id'] = $id;
			$data = $videoModel->where($map)->find();
			
			if ($data) {
				//
				$groupId = $data['groupId'];
				$this->assign('groupId', $groupId);
				$this->assign('data', $data);
				          
			}else{
				//错误	
			}	
			$this->display("editVideoList");			
		}
	}
	
    public function delVideo() {
        $ids = trim(I('post.fids'), '-');
		if (!empty($ids) && count(explode('-', $ids)) > 0) {
			
			$sucaiRootPath = rtrim(C('UPLOAD_COMM_PATH'), '/') . '/school/';
            //exit(json_encode(array('stat'=>0, 'msg'=>$sucaiRootPath)));
			$rmfModel = M('SchoolVideo');
			$fileList = $rmfModel->where(array('id'=>array('in', explode('-', $ids))))->select();
			foreach ($fileList as $file) {
				$file['filePath'] = utf8ToGbk($file['filePath']);
				if (trim($file['filePath']) != '' && is_file($sucaiRootPath . $file['filePath'])) {
					@unlink($sucaiRootPath . $file['filePath']);
				}
			}
			$delRe = $rmfModel->where(array('id='.$ids))->delete();
			
			if ($delRe !== false) {
				exit(json_encode(array('stat'=>1)));
			} else {
				exit(json_encode(array('stat'=>0, 'msg'=>'操作失败！[原因]：' . $rmModel->getError())));
			}
		} else {
			exit(json_encode(array('stat'=>0, 'msg'=>'数据请求错误！请刷新页面重试……')));
		}
	}

	/**
	 * 删除一条视频记录及文件
	*/
	public function delOneVideo(){
		$id = I('post.fids',0,"int");
		
		if ($id){
			$sucaiRootPath = rtrim(C('UPLOAD_COMM_PATH'), '/') . '/school/';
			$model = D('SchoolVideo');
			$datas = $model->where("id=".$id)->find();
			
			if ($datas){
				
				$datas['filePath'] = utf8ToGbk($datas['filePath']);
				if (trim($datas['filePath']) != '' && is_file($datas['filePath'])) {
					@unlink($datas['filePath']);
					//file_put_contents("debug-delOneVideo.txt",PHP_EOL."----filepath=".$sucaiRootPath . $datas['filePath'].PHP_EOL,FILE_APPEND);
				}
				$re = $model->where(array('id='.$id))->delete();
				if ($re !== false) {
					exit(json_encode(array('stat'=>1,'msg'=>'操作成功')));
				} else {
					exit(json_encode(array('stat'=>0, 'msg'=>'操作失败！[原因]：' . $model->getError())));
				}
			}
		}
		
		
	}




	

	
	
	
    /**
	 * 倒计时列表
	 *　author:mqy
	*/
    public function timingList() {
		$modal = D("SchTiming");
        // 加载数据分页类
        import('ORG.Util.Page');
        // 数据分页
        $totals = $modal->count();
        $Page = new Page($totals, 12);
        $show = $Page->show();
        $this->assign('page', $show);			
		$datas = $modal->order("Id DESC")->field("Id,banjiId,convert(VARCHAR(24),beginTime,120) as beginTime,convert(VARCHAR(24),endTime,120) as endTime,prompt")->limit($Page->firstRow.','.$Page->listRows)->select(); //

	    foreach($datas as $k=> $v){
           /* if($v['beginTime']){
                $tempdate=(array)$v['beginTime'];
                $tempdate=strtotime($tempdate['date']);
                $datas[$k]['beginTime']=date("Y-m-d H:i",$tempdate);
            }
            if($v['endTime']){
                $tempdate=(array)$v['endTime'];
                $tempdate=strtotime($tempdate['date']);
                $datas[$k]['endTime']=date("Y-m-d H:i",$tempdate);
            }
            */
        }
		$this->assign('datas', $datas);	
		$SchoolBanjiModel = D('SchoolBanji');
		$SchoolBanji = $SchoolBanjiModel->select();
        $this->assign('SchoolBanji', $SchoolBanji);
		$this->display();
	}
	
	/**
	 * 添加倒计时信息
	 * author:mqy
	*/
    public function addTiming() {
       if (IS_POST) {
        $data = array();
			$Model = D('SchTiming');
            $data['banjiId'] = I('post.banjiId');
			if(I('post.addType')=='manu'||I('post.addType')==''){
                if(I('post.starttime')){
                $data['beginTime'] = I('post.starttime');
                }
            }else{
                if(I('post.endtime')){
                    $data['endTime'] = I('post.endtime');
                }
            
            }
			
			
            $data['prompt'] = I('post.modelNum');
			$result = $Model->data($data)->add();

		   // 执行操作
		   if ($result !== FALSE) {
			   //$insertId = $result;
			   $this->success('操作成功！', U('School/timingList'));
		   } else {
			   $this->error('操作失败！[原因]：' . $Model->getError());
		   }

        } else {
            
			$EndpointModel = D('SchoolBanji');
            $Endpoint = $EndpointModel->select();
            $this->assign('banji', $Endpoint);
			
			
            $this->display("editTiming");
        }		

	}	
	
	/**
	 * 修改倒计时信息
	 * author:mqy
	*/
     public function editTiming() {
		 
       if (IS_POST) {
			$actiModel = D('SchTiming');
            $id = I('request.id', 0, 'int');
			
			$data['Id'] = $id; 
			$data['banjiId'] = I('post.banjiId');
            if(I('post.addType')=='manu'||I('post.addType')==''){
                if(I('post.starttime')){
                $data['beginTime'] = I('post.starttime');
                $data['endTime']=null;
                }
            }else{
                if(I('post.endtime')){
                    $data['endTime'] = I('post.endtime');
                    $data['beginTime']=null;
                }
            
            }
			
            $data['prompt'] = I('post.modelNum');

			$result = $actiModel->save($data);
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/timingList'));
			} else {
			   $this->error('操作失败！[原因]：' . $actiModel->getError());
			}            

            
        } else {		 
		 
			$id = I('get.id',0,'intval');
			$actiModel = D('SchTiming');
			$map['Id'] = $id;
			$actiInfo = $actiModel->where($map)->find();
                $tempdate=(array)$actiInfo['beginTime'];
                $tempdate=strtotime($tempdate['date']);
                $actiInfo['beginTime']=$tempdate ;
                $tempdate=(array)$actiInfo['endTime'];
                $tempdate=strtotime($tempdate['date']);
                $actiInfo['endTime']=$tempdate ;
			$EndpointModel = D('SchoolBanji');
			$Endpoint = $EndpointModel->select();
            $this->assign('banji', $Endpoint);
			if ($actiInfo) {
				$this->assign('actiInfo', $actiInfo);
				$this->display();           
			}else{
				//错误student
				 $this->error('操作失败！[原因]：' . $actiModel->getError());
			}
		}
		
	}	
		
	/**
	 * 删除倒计时信息
	 * author:mqy
	*/
     public function delTiming() {
		//$this->success('（假的）操作成功！', U('Mall/activities'));
		$id = I('request.ID', 0, 'int');
		
		if (!$id) {
			$this->error('非法操作！');
		}
		$actiModel = D('SchTiming');
		$actiInfo = $actiModel->where(array('Id'=>$id))->find();
		if (!$actiInfo) {
			$this->error('无此信息！');
		}
		
        $delResult = $actiModel->where(array('Id'=>$id))->delete();
        if ($delResult !== false) {
           $this->success('操作成功！', U('School/timingList'));
        } else {
           $this->error('操作失败！[原因]：' . $actiClassModel->getError());
        }			
		
		
	}


	/**
	 * 环境监测
	*/
    public function Environment(){
		$model =  D('SchEnvironment');
		$malls = $model->limit(7)->order('id desc')->select(); 
        foreach($malls as $k=> $v){
            if($v['dateTime']){
                $tempdate=(array)$v['dateTime'];
                $tempdate=strtotime($tempdate['date']);
                $malls[$k]['dateTime']=date('Y-m-d',$tempdate) ;
            }
        }
        //$malls=json_encode($malls);
		$this->assign('datas', $malls);
		$this->display();
	}
	
//////////////////////董工 end ////////////////////////////////////////////////////////////		
	
	
	/**
	 * 获取某班级的学生
	 * 返回Json格式
	 * stat:0或1
	 * data:数组
	*/
	public function ajax_student_in_banji(){
		$banjiId = I("request.banjiId",0,"int");
		if (!$banjiId){
			echo json_encode(array("stat"=>"0","data"=>"班级ID为0"));
			exit;
		}else{
			$model = D("SchoolStudents");
			$datas = array();
			$datas = $model->where("banjiId=".$banjiId)->order("id DESC")->select();
			if ($datas){
				echo json_encode(array("stat"=>"1","data"=>$datas));
				exit;
			}else{
				echo json_encode(array("stat"=>"0","data"=>"查询结果为空"));
				exit;
			}
		}
	}
	
	/**
	 * 获取某班学生列表，格式为逗号分隔的学生id
	*/
	public function student_list_in_banji_str($banjiId=0){
		$banjiId = 2;//intval($banjiId);
		//if (!$banjiId){return "false";exit;}
		$stuModel = D('SchoolStudents');
		$student_datas = array();
		$student_list_str = "";
		$tmp = array();
		$student_datas = $stuModel->where("banjiId=".$banjiId)->field("id")->order("id DESC")->select();
		if ($student_datas){
			foreach($student_datas as $k=>$v){
				$tmp1[] = $v['id'];
			}
			
			$student_list_str = implode(",",$tmp1);//用逗号分隔一维数组的元素
			return $student_list_str;
		}else{
			//无学生
		}
	}
	
	/**
	 * 部门列表
	*/
	public function departmentList(){
		$model = D('SchoolDepartment');

		
        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
		$where = array();
		//$where['Pid'] = 0;
        $totals = $model->where($where)->count();
        $Page = new Page($totals, 2);
		//分页跳转的时候保证查询条件
		foreach($map as $key=>$val) {
			$Page->parameter[$key] = urlencode($val);
		}
        $show = $Page->show();
        $this->assign('page', $show);

        $originTypes = $model->order('sort asc,pid asc, id asc')->select();
        $datas = array();
        $model->sortedTypes($datas, $originTypes);
        //var_dump($datas);
        $this->assign('datas', $datas);		
		$this->display("School/departmentList");		
		
	}
	
	/**
	 * 增加部门
	*/
	public function addDepartment(){
        if (IS_POST) {
			// 处理表单提交参数
			$name = I('post.name');
			$pid = I('post.pid', 0, 'int');
			$sort = I('post.sortnum', 0, 'int');
			
			// 实例化分类模型
			$model = D('SchoolDepartment');
			$data['name'] = $name;
			$data['pid'] = $pid;
			$data['sort'] = $sort;
			
			// 执行操作
			$result = $model->data($data)->add();
			if ($result !== FALSE) {
				$this->success('操作成功！', U('School/departmentList'));
			} else {
				$this->error('操作失败！[原因]：' . $model->getError());
			}
        } else {
			
			$classInfo = array();
			$classInfo['sort'] = 0;
			$this->assign('classInfo', $classInfo);
            
            // 获取父级分类数据
            $model = D('SchoolDepartment');
            $originTypes = $model->order('pid asc, id asc')->select();
            $class = array();
            $model->sortedTypes($class, $originTypes);
            $this->assign('class', $class);
            
            $this->display('editDepartment');
        }	
	}
	
	/**
	 * 修改部门
	*/
	public function editDepartment(){
        if (IS_POST) {
			// 处理表单提交参数
			$id = I('post.id', 0, 'int');
			$name = I('post.name');
			$pid = I('post.pid', 0, 'int');
			$sort = I('post.sortnum', 0, 'int');
			
			// 实例化分类模型
			$model = D('SchoolDepartment');
			
			$data['name'] = $name;
			$data['pid'] = $pid;
			$data['sort'] = $sort;
			
			$map=array();
			$map['id']=$id;
            $result = $model->where($map)->save($data);
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/departmentList'));
			} else {
			   $this->error('操作失败！[原因]：' . $model->getError());
			}            
			
        } else {
            $id = I('get.id', 0, 'int');
            if (!$id) {
                $this->error('非法操作！');
            }
            $model = D('SchoolDepartment');
            $info = $model->where(array('id'=>$id))->find();
            if (!$info) {
                $this->error('非法操作！');
            }
            
            $this->assign('classInfo', $info);
			
            // 获取父级分类数据
            //$model = D('SchoolDepartment');
            $origin = $model->order('pid asc, id asc')->select();
            $departments = array();
            $model->sortedTypes($departments, $origin);
            $this->assign('class', $departments);
            
            $this->display('editDepartment');
        }
		
	}	
	
	/**
	 * 删除部门
	*/
	public function delDepatment(){
        $id = I('get.id', 0, 'int');
        if (!$id) {
            $this->error('非法操作！');
        }
        
        $model = D('SchoolDepartment');
 
         // 包含子分类的父级分类不能删除
        $result = $model->where(array('pid'=>$id))->find();//有下级分类
        if ($result) {
            $this->error('请先删除下级部门！');
        }   
		
		
        $delResult = $model->where(array('id'=>$id))->delete();
        if ($delResult !== false) {
           $this->success('操作成功！', U('School/departmentList'));
        } else {
           $this->error('操作失败！[原因]：' . $model->getError());
        }	
		
	}
	
	/*
	 * 设置上课时间
	 * 只设置了上下课时间，课间时间未设置
	*/
	public function lessonTime(){
		if (IS_POST) {
			$attr_id = I("post.attr_id");
			$data_name = I("post.data_name");
			$data_value = I("post.data_value");
			$model = new Model();
			if($model->table("TB_Sch_Lession_Time")->where("id=".$attr_id)->setField($data_name,$data_value)){
				echo '设置成功！';
			}else{
				echo '服务器买药去了，请稍后再试！';
			}
		} else {
			$model = new Model();
			$this->data = $model->table("TB_Sch_Lession_Time")->where("type=0")->order("sort ASC")->select();
			$this->display("editLessonTime");
		}
	}

	/*
	 * 根据教室ID获取班级ID，有返回值，无返回0
	*/
	public function getBanjiIdFromRoomId($roomId=0){
		if (!$roomId){
			//$this->error("教室ID为空");	
			return 0;
		}
		$roomModel = D('SchoolRooms');
		
		$map_room = array();
		$map_room['id'] = $roomId;
		$datas_room = $roomModel->where($map_room)->find();
		$banjiId = intval($datas_room['banjiId']);
		if (!$banjiId){
			//$this->error("此教室未指定班级");
			return 0;
		}else{
			return $banjiId;	
		}
		
		
	}
	
	/*
	 * 获取到可管理的班级的全部学生，可能是多个班级的学生
	*/
	public function getBanjiStudentStr($banjiStr=""/*格式：班级ID,班级ID*/){
		
		if (empty($banjiStr)){return 0;}
		$model_bj = D("SchoolBanji");
		$model_stu = D("SchoolStudents");
		
		$stu = "";//结果将是逗号分隔的学生ID
		$stu_arr = array();
		
		$map = array();
		$map['banjiId'] = array("IN",$banjiStr);//"1,2,3,4,5,6,7,8,9"
		$datas = $model_stu->where($map)->field("id")->select();
		
		foreach($datas as $k=>$v){
			$stu_arr[] = $v['id'];
		}
		$stu = implode(",",$stu_arr);
		//var_dump($stu);
		return $stu;
		
	}
	
	/**
	 * 班级荣誉列表
	*/
	public function banjiHonors(){
		$type = trim(I("request.type"));
		if (empty($type)){
			$type = "banjiList";
		}
		
		
		switch ($type){
			case "banjiList":
				$user = D("User");
				//班级列表 START
				$banjiModel = D('SchoolBanji');
				$map = array();
				if (session("username") == C('ADMIN_AUTH_KEY')) {
					;//超级管理员，列表中显示全部班级
				}else{
					//非超级管理员，班级列表中只显示有权限的
					$map['id'] = array("IN",session('user_banji_list'));
				}
				//$banjiCount = $banjiModel->where($map)->count();
				$banjis = $banjiModel->where($map)->order("id DESC")->select();
				$this->assign('banjis', $banjis);
				//班级列表 END
				
				$result = $user->userBanjiListCount();//可管理班级是否是1
		
				//只可管理一个表的话，直接显示荣誉列表
				if ($result){
					redirect('/School/banjiHonors/type/honorList/banjiId/'.$result, 0, '页面跳转中...');exit;
				}
				
				//显示班级列表
				$this->display("banjiHonor_index");//列出班级
				break;
			case "honorList":
				$banjiId = I("request.banjiId",0,"int");
				if (!$banjiId){
					return false;
				}

				$map_bj = array();
				
				$user = D("User");
				$result = $user->userBanjiListCount();
				
				//班级列表 START
				$banjiModel = D('SchoolBanji');
				if (session("username") == C('ADMIN_AUTH_KEY')) {
					//超级管理员，列表中显示全部班级
				}else{
					//非超级管理员，班级列表中只显示有权限的
					$map_bj['id'] = array("IN",session('user_banji_list'));
				}
				$banjis = $banjiModel->getList($map_bj);
				$this->assign('banjis', $banjis);
				//班级列表 END
				
				//班级荣誉
				$map = array();
				$modelBanjiHonor = D("SchoolBanjiHonor");
				
				//指定班级
				$banjiId = I("request.banjiId",0,"int");
				$this->assign("banjiId",$banjiId);
				if ($banjiId){
					$map['banjiId'] = $banjiId;	
					if (session("username") == C('ADMIN_AUTH_KEY')) {
						//超级管理员，列表中显示全部班级
					}else{
						$this->check_banji($banjiId);//是否可操作此班级ID
					}
				} else{
					if (session("username") == C('ADMIN_AUTH_KEY')) {
						//超级管理员，列表中显示全部班级
					}else{
						//非超级管理员，班级列表中只显示有权限的
						$map['banjiId'] = array("IN",session('user_banji_list'));
					}
					
				}
				
				//关键字
				$keyboard = trim(I('get.keyboard'));
				$this->assign("keyboard",$keyboard);
				if ($keyboard){
					$map['type']=array('LIKE','%'.$keyboard.'%');
				}
				
				// 加载数据分页类
				import('ORG.Util.Page');
				// 数据分页
				$totals = $modelBanjiHonor->where($map)->count();
				$Page = new Page($totals, 10);
				$show = $Page->show();
				$this->assign('page', $show);	
				
				//查询班级荣誉表
				$datas = $modelBanjiHonor->where($map)->field("id,icon,banjiId,type,[level],imagepath,organization,description,convert(VARCHAR(24),datetime,120) as datetime")->order("id DESC")->limit($Page->firstRow.','.$Page->listRows)->select(); 
				foreach ($datas as $k => $v){
					//重新组织数据
					$one = array();
					$one = $banjiModel->where("id=".$v['banjiId'])->field("name")->find();
					$datas[$k]['banjiName'] = $one['name'];
					
					$datas[$k]['datetime'] = date("Y-m-d",strtotime($v['datetime']));
				}
				
				$this->assign("datas",$datas);



				$this->display("banjiHonor_list");
				break;
			default:
				;
		}
		

	}

	/**
	 * 班级荣誉-增加
	*/
	public function addBanjiHonor(){
        if (IS_POST) {
			$banjiId = I('post.banjiId',0,"int");
			
			$model = D("SchoolBanjiHonor");
			
			import('ORG.Util.String');
			$type = String::msubstr(trim(I('post.type')),0,30,'utf-8',false);//type
			$level = String::msubstr(trim(I('post.level')),0,20,'utf-8',false);//level
			$datetime = trim(I("request.datetime"));
			if ($datetime != '' && !empty($datetime)){
				$datetime = date("Y-m-d",strtotime($datetime));
			} else {
				;
			}//确保日期格式正确
			
			$banjiId = I('post.banjiId',0,"int");
			if(session("username") == C('ADMIN_AUTH_KEY')){
				//超级管理员可显示所有班级
			} else {
				$this->check_banji($banjiId);//是否可操作此班级ID
			}
			
			
			$organization = String::msubstr(trim(I('post.organization')),0,50,'utf-8',false);
			$description = String::msubstr(trim(I('post.description')),0,500,'utf-8',false);//
			
			if (!$banjiId){
				$this->error("班级未指定");	
			}
		
			$data = array();		
            $data['type'] = $type;
			$data['level'] = $level;
			$data['datetime'] = $datetime;			
            $data['banjiId'] = $banjiId;
			$data['imagepath'] = I('post.photo');
			$data['icon'] = I('post.icon');
			$data['organization'] = $organization;
			$data['description'] = $description;
     //       var_dump($data);
            $result = $model->data($data)->add();
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/banjiHonors',array("type"=>"honorList","banjiId"=>$banjiId)));
			} else {
			   $this->error('操作失败！[原因]：' . $model->getError());
			}            
        } else {		
			$map = array();
			
			//班级列表 START
			$banjiModel = D('SchoolBanji');
			
			if (session("username") == C('ADMIN_AUTH_KEY')) {
				//超级管理员，列表中显示全部班级
			}else{
				//非超级管理员，班级列表中只显示有权限的
				$map['id'] = array("IN",session('user_banji_list'));
			}
			$banjis = $banjiModel->where($map)->order("id DESC")->select();
			$this->assign('banjis', $banjis);
			//班级列表 END
	
			$model = D("SchoolHonor");
			$map = array();
			
			$banjiId = I("request.banjiId");
			if ($banjiId){
				$map['banjiId'] = $banjiId;	
			}
			$this->display("editHonor");
		}
	}

	/**
	 * 班级荣誉-增加
	*/
	public function editBanjiHonor(){
        if (IS_POST) {

			$banjiId = I('post.banjiId',0,"int");
			$model = D("SchoolBanjiHonor");

			import('ORG.Util.String');
			$id = I('post.id',0,"int");
			$type = String::msubstr(trim(I('post.type')),0,30,'utf-8',false);//type
			$level = String::msubstr(trim(I('post.level')),0,20,'utf-8',false);//level
			$datetime = trim(I("request.datetime"));
			if ($datetime != '' && !empty($datetime)){
				$datetime = date("Y-m-d",strtotime($datetime));
			} else {
				;
			}//确保日期格式正确
			
			$banjiId = I('post.banjiId',0,"int");
			if(session("username") == C('ADMIN_AUTH_KEY')){
				//超级管理员可显示所有班级
			} else {
				$this->check_banji($banjiId);//是否可操作此班级ID
			}
			
			$organization = String::msubstr(trim(I('post.organization')),0,50,'utf-8',false);
			$description = String::msubstr(trim(I('post.description')),0,500,'utf-8',false);//
			
			if (!$banjiId){
				$this->error("班级未指定");	
			}
		
			$data = array();
			$data['id'] = $id;		
            $data['type'] = $type;
			$data['level'] = $level;
			$data['datetime'] = $datetime;			
            $data['banjiId'] = $banjiId;
			$data['imagepath'] = I('post.photo');
			$data['icon'] = I('post.icon');
			$data['organization'] = $organization;
			$data['description'] = $description;
       //     var_dump($data);
            $result = $model->save($data);
			if ($result !== FALSE) {
				//$banjiId = I('post.banjiId',0,"int");
			    $this->success('操作成功！', U('School/banjiHonors',array("type"=>"honorList","banjiId"=>$banjiId)));
			} else {
			   $this->error('操作失败！[原因]：' . $model->getError());
			}            
        } else {		
			$id = I('request.id',0,"int");
			$map = array();
			
			//班级列表 START
			$banjiModel = D('SchoolBanji');
			
			if (session("username") == C('ADMIN_AUTH_KEY')) {
				//超级管理员，列表中显示全部班级
			}else{
				//非超级管理员，班级列表中只显示有权限的
				$map['id'] = array("IN",session('user_banji_list'));
			}
			$banjis = $banjiModel->where($map)->order("id DESC")->select();
			$this->assign('banjis', $banjis);
			//班级列表 END
	
			$model = D("SchoolBanjiHonor");
			$map = array();
			$datas = $model->where("id=".$id)->field("id,icon,banjiId,type,[level],imagepath,organization,description,convert(VARCHAR(24),datetime,120) as datetime")->find();
			$datas['datetime'] = date("Y-m-d",strtotime($datas['datetime']));
		//	var_dump($datas);
			$banjiId = I("request.banjiId");
			if ($banjiId){
				$map['banjiId'] = $banjiId;	
			}
			$this->assign("datas",$datas);
			$this->display("editHonor");
		}
	}

	/**
	 * 删除班级荣誉
	*/
	public function delBanjiHonor(){
		$id = I("request.id",0,"int");	
		if (!$id){
			$this->error("参数错误");	
		}
		
		//查找此条，检测banjiId是否在权限内
		$model = D("SchoolBanjiHonor");
		$datas = array();
		$datas = $model->where("id=".$id)->find();
		
		$banjiId = $datas['banjiId'];
		if(session("username") == C('ADMIN_AUTH_KEY')){
			//超级管理员可显示所有班级
		} else {
			$this->check_banji($banjiId);//是否可操作此班级ID
			//删除图片
			$old_pic_path = $datas['imagepath'];
			$old_icon_path = $datas['icon'];
		//	file_put_contents("debug.txt",PHP_EOL."old_pic_path:".$old_pic_path.PHP_EOL,FILE_APPEND);//写调试到TXT
			//$upRootPath = rtrim(C('UPLOAD_COMM_PATH'), '/') . '/';
			@unlink($old_pic_path);//删除旧图片
			@unlink($old_icon_path);
			
			if (trim($old_pic_path) != '' && is_file($old_pic_path)) {
							
			}else{
				;
			}	
		}
		$result = $model->where("id=".$id)->delete();
		
	   // 执行操作
	   if ($result !== FALSE) {
			$this->success('操作成功！', U('School/banjiHonors',array("type"=>"honorList","banjiId"=>$banjiId)));
	   } else {
		   $this->error('操作失败！[原因]：' . $Model->getError());
	   }
		
	}

	/**
	 * 投票列表
	*/
	public function votesList(){
		$modelVote = D("SchoolVote");
		$modelOpt = D("SchoolVoteOption");
		$userModel = D("User");
		
		//搜索条件
		$keyboard = trim(I('get.keyboard'));
		$banjiId = I('get.banjiId');		
		$this->assign('keyboard', $keyboard);
		$this->assign('banjiId', $banjiId);
		
		$map = array();
		if ($keyboard){$map['name']=array('LIKE','%'.$keyboard.'%');}
	//	if ($banjiId){$map['banjiId']=array('LIKE','%,'.$banjiId.',%');}//前后加逗号，搜索：,1,对于字段为逗号分隔的id，要在入库时前后加上		
		if ($banjiId){$map['banjiId']=array('LIKE','%,'.$banjiId.',%');}
		
		if (session("username") == C('ADMIN_AUTH_KEY')) {
			;//超级管理员，列表中显示全部班级
		}else{
			//非超级管理员，班级列表中只显示有权限的
			$map['sponserId'] = session(C('USER_AUTH_KEY'));
		}	
				
		
		
        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
        $totals = $modelVote->where($map)->count();
        $Page = new Page($totals, 10);
        $show = $Page->show();
        $this->assign('page', $show);
		
		
		
		$datas = $modelVote->where($map)->field("id,name,content,sponserId,convert(VARCHAR(24),endtime,120) as endtime")->limit($Page->firstRow.','.$Page->listRows)->order("id DESC")->select();
	
		foreach($datas as $k=>$v){
			$optCount = $modelOpt->where("voteId=".$v['id'])->count();
			$datas[$k]['optNum'] = $optCount;
			$datas[$k]['sponserName'] = $userModel->getNameFromUserId($v['sponserId']);
		}
		
		
		//班级列表 START
		$banjiModel = D('SchoolBanji');
		$map = array();
		if (session("username") == C('ADMIN_AUTH_KEY')) {
			//超级管理员，列表中显示全部班级
		}else{
			//非超级管理员，班级列表中只显示有权限的
			$map['id'] = array("IN",session('user_banji_list'));
		}
		$banjis = $banjiModel->getList($map);
		$this->assign('banjis', $banjis);
		//班级列表 END
		
		
		$this->assign("datas",$datas);
		$this->display();
	}
	
	
	
	
	
	
	/**
	 * 新增投票
	*/
	public function addVote(){
		if (IS_POST) {
			import('ORG.Util.String');
			$name = String::msubstr(trim(I('post.name')),0,100,'utf-8',false);//
			$content = String::msubstr(trim(I('post.content')),0,100,'utf-8',false);//
			$banjiIdArr = $_POST['banjiId'];
			sort($banjiIdArr);
			$banjiIdStr = implode(",",$banjiIdArr);
			$banjiIdStr = "0,".$banjiIdStr.",0";//前后加0，用于LIKE搜索
			$endtime = trim(I("request.endtime"));
			
			if (!empty($endtime)){
				$endtime = date("Y-m-d",strtotime($endtime));
			} else {
				$endtime = date("Y-m-d",strtotime("+1 day"));
			}//确保日期格式正确
			
			$options = $_POST['option_content'];
			sort($options);
			
			$modelVote = D("SchoolVote");
			$modelOpt = D("SchoolVoteOption");
			
			//投票主题入库
			$data = array();
			$data['name'] = $name;
			$data['content'] = $content;
			$data['sponserId'] = session(C('USER_AUTH_KEY'));
			$data['endtime'] = $endtime;
			$data['banjiId'] = $banjiIdStr;
			$result = $modelVote->data($data)->add();
			
			//选项入库
			if ($result){
				$voteId = $result;
				foreach($options as $k=>$v){
					//遍历提交的选项数组，不为空的插入选项表
				//	if (!empty($v)){	
						$data_opt = array();
						$data_opt['voteId'] = $result;
						$data_opt['content'] = trim($v);
						$modelOpt->data($data_opt)->add();
				//	}
				}
			}

			
			//
			$datas = array();
			$datas_opt = $modelOpt->where("voteId=".$voteId)->select();
			
			//某一个或几个班级的全部学生，返回：逗号分隔的学生ID
			$modelStu = D("SchoolStudents");
			$stus = $modelStu->getBanjiStudentIdStr($banjiIdStr);//一个或几个班级的全部学生
			$studentIdArr = explode(",",$stus);
			
			//学生ID到用户ID的转换，返回:逗号分隔的用户ID
	//		$userModel = D('User');
	//		$userStr = $userModel->getUserIdStr($stus,"student");//学生对应的会员ID（如果某学生无用户ID，则忽略此学生）
	//		$userArr = explode(",",$userStr);

			//原始的投票项
			$modelResult = D("SchoolVoteResult");
			
			if ($studentIdArr){
				//生成投票的原始记录
				foreach($studentIdArr as $k=>$v){
					$data_result = array();
					$data_result['studentId'] = $v;
					$data_result['voteId'] = $voteId;
					$data_result['optionId'] = 0;
					$result = $modelResult->data($data_result)->add();
				}	
			}
			
		   // 执行操作
		   if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/votesList'));
		   } else {
			   $this->error('操作失败！[原因]：' . $modelResult->getError());
		   }			
			
		}else{		
			//班级列表 START
			$banjiModel = D('SchoolBanji');
			$map = array();
			if (session("username") == C('ADMIN_AUTH_KEY')) {//session("username")
				//超级管理员，列表中显示全部班级
				;
			}else{
				//非超级管理员，班级列表中只显示有权限的
				$map['id'] = array("IN",session('user_banji_list'));
			}
			$banjis = $banjiModel->where($map)->select();
			$this->assign('banjis', $banjis);
			//班级列表 END
			
			$this->display("editVote");
		}
		
		
	}

	
	/**
	 * 修改投票
	*/
	public function editVote(){
		if (IS_POST) {
			$voteId = I("post.id",0,"int");
			import('ORG.Util.String');
			$name = String::msubstr(trim(I('post.name')),0,100,'utf-8',false);//
			$content = String::msubstr(trim(I('post.content')),0,100,'utf-8',false);//
			$banjiIdArr = $_POST['banjiId'];
			sort($banjiIdArr);
			$banjiIdStr = implode(",",$banjiIdArr);
			$banjiIdStr = "0,".$banjiIdStr.",0";
			$endtime = trim(I("request.endtime"));
			
			
			
			if (!empty($endtime)){
				$endtime = date("Y-m-d",strtotime($endtime));
			} else {
				$endtime = date("Y-m-d",strtotime("+1 day"));
			}//确保日期格式正确
			
			$options = $_POST['option_content'];
			sort($options);
			
			$modelVote = D("SchoolVote");
			$modelOpt = D("SchoolVoteOption");
			$modelResult = D("SchoolVoteResult");

			
			//投票主题（更新）
			$data = array();
			$data['id'] = $voteId;
			$data['name'] = $name;
			$data['content'] = $content;
			$data['sponserId'] = session(C('USER_AUTH_KEY'));
			$data['endtime'] = $endtime;
			$data['banjiId'] = $banjiIdStr;
			$result = $modelVote->save($data);
			
			
			//清空投票结果和选项
			$modelResult->where("voteId=".$voteId)->delete();
			$modelOpt->where("voteId=".$voteId)->delete();
			
			//选项入库（新增）
			foreach($options as $k=>$v){
				//遍历提交的选项数组，插入选项表
				$data_opt = array();
				$data_opt['voteId'] = $voteId;
				$data_opt['content'] = trim($v);
			//	var_dump($data_opt);
				$modelOpt->data($data_opt)->add();
			}
		
			//
			$datas = array();
			$datas_opt = $modelOpt->where("voteId=".$voteId)->select();
			
			//某一个或几个班级的全部学生，返回：逗号分隔的学生ID
			$modelStu = D("SchoolStudents");
			$stus = $modelStu->getBanjiStudentIdStr($banjiIdStr);//一个或几个班级的全部学生
			$studentIdArr = explode(",",$stus);
			//学生ID到用户ID的转换，返回:逗号分隔的用户ID
//			$userModel = D('User');
//			$userStr = $userModel->getUserIdStr($stus,"student");//学生对应的会员ID（如果某学生无用户ID，则忽略此学生）
//			$userArr = explode(",",$userStr);

			//原始的投票项
			$modelResult = D("SchoolVoteResult");
			
			if ($studentIdArr){
				//生成投票的原始记录
				foreach($studentIdArr as $k=>$v){
					$data_result = array();
					$data_result['studentId'] = $v;
					$data_result['voteId'] = $voteId;
					$data_result['optionId'] = 0;
					$result = $modelResult->data($data_result)->add();
				}	
			}			
			
		   // 执行操作
		   if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/votesList'));
		   } else {
			   $this->error('操作失败！[原因]：' . $modelResult->getError());
		   }				

			
			
			
			
		}else{
			$id = I("request.id",0,"int");
			if (!$id){
				$this->error("参数错误");	
			}
		
			$modelVote = D("SchoolVote");
			$datas = $modelVote->where("id=".$id)->field("id,name,content,sponserId,banjiId,convert(VARCHAR(24),endtime,120) as endtime")->find();//var_dump($datas);
			$this->assign("datas",$datas);
			
			//范围：转为班级数组
			$banjiIdArr = explode(",",$datas['banjiId']);
			sort($banjiIdArr);
			$this->assign("banjiIdArr",$banjiIdArr);
			//var_dump($banjiIdArr);
		
			//班级列表 START
			$banjiModel = D('SchoolBanji');
			$map = array();
			if (session("username") == C('ADMIN_AUTH_KEY')) {//session("username")
				//超级管理员，列表中显示全部班级
				;
			}else{
				//非超级管理员，班级列表中只显示有权限的
				$map['id'] = array("IN",session('user_banji_list'));
			}
			$banjis = $banjiModel->where($map)->select();
			$this->assign('banjis', $banjis);
			//班级列表 END
		
		
			//获取选项列表
			$modelOpt = D("SchoolVoteOption");
			$map = array();
			$map['voteId'] = $datas['id'];
			
			$optCount = $modelOpt->where($map)->count();
			$this->assign('optCount', $optCount);
			
			$datasOpt = $modelOpt->where($map)->order("id ASC")->select();
			$this->assign('datasOpt', $datasOpt);
		
			$this->display("editVote");
		}
	}	
	
	/**
	 * 删除投票
	*/
	public function delVote(){
		
		$voteId = I("request.id",0,"int");
		if (!$voteId){
			$this->error("参数为空");	
		}
		
		$modelVote = D("SchoolVote");
		
		//判断权限
		$datas = $modelVote->where("id=".$voteId)->find();
		if (!$datas){
			$this->error("删除失败");	
		}else{
			if (session("username") == C('ADMIN_AUTH_KEY')) {
				//超级管理员，列表中显示全部班级
			}else{
				if ($datas['sponserId'] != session(C('USER_AUTH_KEY'))){
					$this->error("无权限");	
				}
			}
		}
		
		$map = array();
		$map['voteId'] = $voteId;
		//删除投票记
		$modelResult = D("SchoolVoteResult");
		$result = $modelResult->where($map)->delete();
		
		if ($result !== FALSE) {
			//删除投票选项
			$modelOpt = D("SchoolVoteOption");
			$result1 = $modelOpt->where($map)->delete();
			
			if ($result1 !== FALSE) {
				//删除投票主题
				
				$result2 = $modelVote->where("id=".$voteId)->delete();	
				if ($result2 !== FALSE){
					$this->success('操作成功！', U('School/votesList'));
				}else{
					$this->error('操作失败！[原因]：' . $modelVote->getError());
				}		
			}else{
				$this->error('操作失败！[原因]：' . $modelOpt->getError());
			}
			//$this->success('操作成功！', U('School/banjiHonors'));
		} else {
			$this->error('操作失败！[原因]：' . $modelResult->getError());
		}
		
	}

	
	/**
	 * 投票结果统计
	*/
	public function voteResult(){
		$voteId = I("request.id",0,"int");
		
		$modelVote = D("SchoolVote");
		$modelOpt = D("SchoolVoteOption");
		$modelResult = D("SchoolVoteResult");
		$stuModel = D('SchoolStudents');
		$userModel = D('User');
		
		$datas_vote = $modelVote->where("id=".$voteId)->find();
		
		if ($datas_vote){
			//总票数，即总人数
			$allNumber = $modelResult->where("voteId=".$voteId)->count();
			$datas_vote['allnumber'] = $allNumber;
			
			//已投票人数
			$hadSendNumber = $modelResult->where("voteId=".$voteId ." and optionId>0")->count();
			$datas_vote['hadSendNumber'] = $hadSendNumber;
		}
		
		//选项
		$datas_opt = $modelOpt->where("voteId=".$voteId)->select();
	//	var_dump($datas_opt);
		
		//统计
		foreach($datas_opt as $k=>$v){
			
			$resultNum = $modelResult->where("optionId=".$v['id'])->count();//voteId = $v['voteId']
			$datas_opt[$k]['num'] = $resultNum;
			
			//$allNumber = $modelResult->where("voteId=".$v['voteId'])->count();
			$x = $resultNum / $allNumber;//round();
			$datas_opt[$k]['bili'] = sprintf("%.2f",$x*100);
		}

		//总票数，也就是可投票的总人数
		
		
	
		//结果列表
		$datas_result = $modelResult->where("voteId=".$voteId)->select();
		foreach($datas_result as $k=>$v){
			//学生姓名
			//$stu_name = $userModel->getNameFromUserId($v['userId']);
			$datas_tmp_stu = array();
			$datas_tmp_stu = $stuModel->where("id=".$v['studentId'])->field("name")->find();
			$datas_result[$k]['stu_name'] = $datas_tmp_stu['name'];
			
			//选项内容
			if ($v['optionId']){
				$datas_tmp1 = $modelOpt->where("id=".$v['optionId'])->find();
				$datas_result[$k]['option'] = $datas_tmp1['content'];
			}else{
				$datas_result[$k]['option'] = "-";
			}
			
			
		}
		
		$this->assign("datas_vote",$datas_vote);
		$this->assign("datas_opt",$datas_opt);
		$this->assign("datas_result",$datas_result);
		$this->display("voteResult");
	}


	/*
	 * 紧急事件列表
	*/
	public function emergencies(){
		$emModel = D('SchoolEmergencies');
		$map = array();
		
		$keyboard = trim(I('get.keyboard'));
		$typeId = I('get.typeId',0,"int");
		$starttime = trim(I("request.starttime"));
		$endtime = trim(I("request.endtime"));
		
		if ($keyboard){$map['TB_Sch_EmergencyTypes.name']=array('LIKE','%'.$keyboard.'%');}//关键字
		
		//开始时间
		if (empty($starttime) || $starttime==''){
		//	$starttime = date("Y-m-d",mktime());	
		}else{
			$starttime = date("Y-m-d H:i:s",strtotime($starttime));
		}
		
		//结束时间
		if (empty($endtime) || $endtime==''){
		//	$endtime = date("Y-m-d",mktime());	
		}else{
			$endtime = date("Y-m-d H:i:s",strtotime($endtime));
		}
		
		//搜索条件
		if ( $starttime){
			$map['TB_Sch_Emergencies.beginTime'] = array("EGT",$starttime);
		}
		if ( $endtime){
			$map['TB_Sch_Emergencies.endTime'] = array("ELT",$endtime);
		}		
		
		$this->assign("starttime",$starttime);
		$this->assign("endtime",$endtime);
		$this->assign("typeId",$typeId);
		$this->assign("keyboard",$keyboard);

        // 加载数据分页类
        import('ORG.Util.Page');
        // 数据分页
        $totals = $emModel->where($map)->count();
        $Page = new Page($totals, 10);
        $show = $Page->show();
        $this->assign('page', $show);	
		
		$emergenciesConditionModel = new Model();
		$datas = $emergenciesConditionModel->table("TB_Sch_Emergencies")->where($map)->field("TB_Sch_Emergencies.id,TB_Sch_Emergencies.roomId,TB_Sch_Room.name as roomName,convert(VARCHAR(24),TB_Sch_Emergencies.beginTime,120) as starttime,convert(VARCHAR(24),TB_Sch_Emergencies.endTime,120) as endtime")->join("LEFT /*RIGHT*/ JOIN TB_Sch_Room ON TB_Sch_Emergencies.roomId = TB_Sch_Room.id")->order("id DESC")->limit($Page->firstRow.','.$Page->listRows)->select(); 
		//var_dump($datas);
		$this->assign('datas', $datas);
		$this->display();	
	}
	
	/**
	 * 紧急事件条件设置
	*/
	public function emergenciesCondition(){
        if (IS_POST) {
			$pm25 = I("request.pm25",0,"int");//pm25
			$temperature = I("request.temperature",0,"int");//温度
			$methanal = I("request.methanal",0,"int");//甲醛
			
			//
			$emergenciesConditionModel = D("SchoolEmergenciesCondition");
			$data = array();
			$data['pm25'] = $pm25;
			$data['temperature'] = $temperature;
			$data['methanal'] = $methanal;
			$result = $emergenciesConditionModel->where("id=1")->save($data);
			if ($result !== FALSE) {
			   $this->success('操作成功！', U('School/emergenciesCondition'));
			} else {
			   $this->error('操作失败！[原因]：' . $emergenciesConditionModel->getError());
			}
			
		} else {
			$emergenciesConditionModel = D("SchoolEmergenciesCondition");
			$datas = $emergenciesConditionModel->where("id>0")->order("id DESC")->find();
			//var_dump($datas);
			$this->assign('datas', $datas);
			$this->display("emergenciesCondition");
		}	
	}

	/*
	 * 增加：紧急事件
	*/
	/*
	public function addEmergencies(){
       if (IS_POST) {
			$model = D('SchoolEmergencies');
            $id = I('post.id', 0, 'int');
			$typeId = I("request.typeId",0,"int");

			$starttime = trim(I("request.starttime"));
			$endtime = trim(I("request.endtime"));
			
			if (!empty($starttime)){
				$starttime = date("Y-m-d H:i:s",strtotime($starttime));
			}//确保日期格式正确
			
			if (!empty($endtime)){
				$endtime = date("Y-m-d H:i:s",strtotime($endtime));
			}


		
			$data = array();	
		//	$data['id'] = $id;	
			$data['beginTime'] = $starttime;	
            $data['endTime'] = $endtime;
			$data['typeId'] = $typeId;
            
            $result = $model->data($data)->add();
			if ($result) {
			   $this->success('操作成功！', U('School/emergencies'));
			} else {
			   $this->error('操作失败！[原因]：' . $model->getError());
			}   
		   
        } else {
			$model = D("SchoolEmergencies");
		
			$typeModel = D("SchoolEmergenciesTypes");
			$type_datas = $typeModel->order("id DESC")->select();
			$this->assign('type_datas', $type_datas);
			//var_dump($type_datas);
			
			$this->display("editEmergencies");	
		}	
	}*/
	
	/*
	 * 编缉：紧急事件
	*/
	/*
	public function editEmergencies(){
       if (IS_POST) {
			$model = D('SchoolEmergencies');
            $id = I('post.id', 0, 'int');
			$typeId = I("request.typeId",0,"int");

			$starttime = trim(I("request.starttime"));
			$endtime = trim(I("request.endtime"));
			
			if (!empty($starttime)){
				$starttime = date("Y-m-d H:i:s",strtotime($starttime));
			}//确保日期格式正确
			
			if (!empty($endtime)){
				$endtime = date("Y-m-d H:i:s",strtotime($endtime));
			}


		
			$data = array();	
			$data['id'] = $id;	
			$data['beginTime'] = $starttime;	
            $data['endTime'] = $endtime;
			$data['typeId'] = $typeId;
            
            $result = $model->save($data);
			if ($result) {
			   $this->success('操作成功！', U('School/emergencies'));
			} else {
			   $this->error('操作失败！[原因]：' . $model->getError());
			}   
		   
        } else {
			$id = I("request.id",0,"int");
			$model = D("SchoolEmergencies");
			if (!$id){
				$this->error("参数错误");	
			}
			
			$datas = array();
			$datas = $model->where("id = ".$id)->field("id,typeId,convert(VARCHAR(24),beginTime,120) as starttime,convert(VARCHAR(24),endTime,120) as endtime")->find();
			$this->assign('datas', $datas);	
		
			$typeModel = D("SchoolEmergenciesTypes");
			$type_datas = $typeModel->order("id DESC")->select();
			$this->assign('type_datas', $type_datas);
			//var_dump($type_datas);
			
			$this->display("editEmergencies");	
		}
	}*/
	
	/*
	 * 删除：紧急事件
	*/
	/*
	public function delEmergencies(){
		$id = I("request.id",0,"int");
		$model = D("SchoolEmergencies");
		if (!$id){
			$this->error("参数错误");	
		}
		$result = $model->where("id=".$id)->delete();
		if ($result) {
		   $this->success('操作成功！', U('School/emergencies'));
		} else {
		   $this->error('操作失败！[原因]：' . $model->getError());
		} 
	}*/
	
	/**
	 * 列表：紧急事件类型
	 * author:zjh
	*/
	/*
    public function emergenciesType() {
		$model = D('SchoolEmergenciesTypes');
		$map = array();
		
		$keyboard = trim(I('get.keyboard'));
		
		if ($keyboard){$map['name']=array('LIKE','%'.$keyboard.'%');}//关键字
		
		$this->assign("keyboard",$keyboard);

        // 加载数据分页类
        import('ORG.Util.Page');
        // 数据分页
        $totals = $model->where($map)->count();
        $Page = new Page($totals, 10);
        $show = $Page->show();
        $this->assign('page', $show);	
		
		$datas = $model->where($map)->order("id DESC")->limit($Page->firstRow.','.$Page->listRows)->select(); 
	//	var_dump($datas);
		$this->assign('datas', $datas);		
		$this->display("emergenciesType");
	}*/
	
	/**
	 * 新增：紧急事件类型
	 * author:zjh
	*/
	/*
    public function addEmergenciesType() {
		
		$displaytype = array();
		$displaytype[] = array("id"=>"1","type"=>"pictures","name"=>"图片");
		$displaytype[] = array("id"=>"2","type"=>"music","name"=>"音频");
		$displaytype[] = array("id"=>"3","type"=>"videos","name"=>"视频");
		$this->assign('displaytype', $displaytype);	
		
			$rmModel = D('ResmanagerDirs');
			$resTypeTreeData = array();
			$resTypeTreeData['id'] = 0;
			$resTypeTreeData['name'] = '根目录';
			$resTypeTreeData['open'] = true;
			$resTypeTreeData['classid'] = '';
			$resTypeTreeData['children'] = $rmModel->getZTreeData();
			$this->assign('treeData', json_encode($resTypeTreeData));//var_dump($resTypeTreeData);
			
			//$this->assign('columns', $this->getGroupColumns($groupClassID));
			$this->assign('tempClassId', generateUniqueID());
			$this->assign('artiConTypes', $this->getArticeTypes());
			$this->assign('spColumns', array('weatdir'));
		
		
		$this->display("editEmergenciesType");
	}		
	*/
	
	/**
	 * 修改：紧急事件类型
	 * author:zjh
	*/
	/*
    public function editEmergenciesType() {
		
       if (IS_POST) {
			$model = D('SchoolEmergenciesTypes');
            $id = I('post.id', 0, 'int');
			$name = trim(I("request.name"));
			$type = trim(I("request.type"));
			$value = trim(I("request.value"));
			$displaytype = trim(I("request.displaytype"));
			$filepath = trim(I("request.filepath"));

			$data = array();

			switch ($displaytype){
				case "pictures":
					$displaytype = "image";
					$data['imagePath'] = trim(I("request.imagePath"));
					break;
				case "music":
					$displaytype = "audio";
					$data['audioPath'] = $filepath;
					break;
				case "videos":
					$displaytype = "video";
					$data['videoPath'] = $filepath;
					break;
				default:
					$displaytype = "image";
					$data['imagePath'] = trim(I("request.imagePath"));
			}

			
				
			$data['id'] = $id;	
			$data['name'] = $name;	
            $data['type'] = $type;
			$data['value'] = $value;
			
			
			
			$data['displaytype'] = $displaytype;
			
            $result = $model->save($data);
			
			var_dump($data);
			
			
			if ($result) {
		//	   $this->success('操作成功！', U('School/emergencies'));
			} else {
			   $this->error('操作失败！[原因]：' . $model->getError());
			}   
		   
        } else {
			$id = I("request.id",0,"int");
			$model = D("SchoolEmergenciesTypes");
			if (!$id){
				$this->error("参数错误");	
			}
			
			$resList = array();
			
			$datas = array();
			$datas = $model->where("id = ".$id)->find();
			if ($datas){
			switch ($datas['displaytype']){
				case "image":
					$displaytype = "image";
					$datas['displaytype'] = "pictures";
					break;
				case "audio":
					$displaytype = "audio";
					$datas['displaytype'] = "music";
					
					$resList[] = array("id"=>1,"filename"=>$datas['audioPath']);
					break;
				case "video":
					$displaytype = "video";
					$datas['displaytype'] = "videos";
					
					$resList[] = array("id"=>1,"filename"=>$datas['videoPath']);
					break;
				default:
					$displaytype = "image";
					$datas['displaytype'] = "pictures";
			}
				
			}
			
			
			$this->assign('datas', $datas);	
			$this->assign('resList', $resList);	

			$rmModel = D('ResmanagerDirs');
			$resTypeTreeData = array();
			$resTypeTreeData['id'] = 0;
			$resTypeTreeData['name'] = '根目录';
			$resTypeTreeData['open'] = true;
			$resTypeTreeData['classid'] = '';
			$resTypeTreeData['children'] = $rmModel->getZTreeData();
			$this->assign('treeData', json_encode($resTypeTreeData));//var_dump($resTypeTreeData);
			
			//$this->assign('columns', $this->getGroupColumns($groupClassID));
			
		$displaytype = array();
		$displaytype[] = array("id"=>"1","type"=>"pictures","name"=>"图片");
		$displaytype[] = array("id"=>"2","type"=>"music","name"=>"音频");
		$displaytype[] = array("id"=>"3","type"=>"videos","name"=>"视频");
		$this->assign('displaytype', $displaytype);	
			
			
			$this->assign('tempClassId', generateUniqueID());
			$this->assign('artiConTypes',$artiConTypes);
			$this->assign('spColumns', array('weatdir'));
			
		//	var_dump($this->getArticeTypes());
			
			$this->display("editEmergenciesType");
			
		}
	}	*/
	
	/**
	 * 删除：紧急事件类型
	 * author:zjh
	*/
	/*
    public function delEmergenciesType() {

	}*/
	
	
	/**
	 * 获取文章类型
	 * @param string $typeCode
	 * @return mixed < array | string >
	 */
	private function getArticeTypes($typeCode = '') {
		$artiTypes = M('ProgramsArticleTypes')->field(array('id,article_type_enname,article_type_cnname'))->order('id asc')->select();
		if ($artiTypes) {
			
			$types = array();
			
			foreach ($artiTypes as $item) {
				$types[$item['article_type_enname']] = $item['article_type_cnname'];
			}

			return empty($typeCode) ? $types : $types[$typeCode];
			 
		} else {
			return null;
		}
	}
	
	/**
	 * 座位安排
	*/
	public function SeatPlan(){

		$model = D('SchoolBanji');
		$roomModel = D('SchoolRooms');
		
		//搜索条件
		$keyboard = trim(I('get.keyboard'));
		$gradeId = I("request.gradeId",0,"int");
		
		$this->assign('keyboard', $keyboard);
		$this->assign('gradeId', $gradeId);
		$this->assign('orderNext', $orderNext);
		
		$map = array();
	//	$map['id'] = array('GT',0);

		if(session("username") == C('ADMIN_AUTH_KEY')){
			//超级管理员可显示所有班级
		} else {
			$map['id'] = array("IN",session("user_banji_list"));
		}


		
		if ($keyboard){$map['name']=array('LIKE','%'.$keyboard.'%');}
		
        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
        $totals = $model->where($map)->count();
        $Page = new Page($totals, 10);
        $show = $Page->show();
        $this->assign('page', $show);
		
		$datas = $model->where($map)->order("id DESC")->limit($Page->firstRow.','.$Page->listRows)->select(); 
		
		if ($totals == 1){
			//echo "只能管理一个班级，直接跳转";
			$banjiId = $datas[0]['id'];
			//$this->editSeatPlan($datas[0]['id']);
			redirect('/School/editSeatPlan/banjiId/'.$banjiId, 0, '页面跳转中...');
			exit;
		}
		
		//重新组织信息
		$gradeModel = D('SchoolGrade');
		$teacherModel = D('SchoolTeachers');
		$studentModel = D('SchoolStudents');
		foreach ($datas as $k => $v){
			$one = $gradeModel->where("id=".$v['gradeId'])->find();
			$datas[$k]['gradeName'] = $one['name'];
			//var_dump( $one['name']);
			
			$one = $teacherModel->where("id=".intval($v['banzhurenId']))->find();
			$datas[$k]['teacherName'] = $one['name'];
			
			$one = $studentModel->where("id=".intval($v['banzhanId']))->find();
			$datas[$k]['banzhanName'] = $one['name'];	
			
			//教室
			$room_map = array();
			$room_map['id'] = $v['roomId'];
			$room_datas = $roomModel->where($room_map)->find();
			if ($room_datas){
				$datas[$k]['room'] = $room_datas['name'];
			}else{
				$datas[$k]['room'] = "";
			}
		}
		
		$this->assign('datas', $datas);
		
		//年级列表 START
		$Model = D('SchoolGrade');
		$map = array();
		$grades = $Model->getList($map);
		$this->assign('grades', $grades);
		//年级列表 END
		
		
		$this->display("School/seatplan_index");
		
		
	}
	

	
	/**
	 * 设置修改座位表
	 * 参数1：班级ID
	*/
	public function editSeatPlan($banjiId=0){
		
		/**
		 * dotype参数为setOne时，为更新某一个座位，放此处是为了减少函数，免却权限设置的麻烦
		*/
		if ($_POST['dotype']=="setOne"){
			$banjiId = I("request.banjiId",0,"int");
			$roomId = I("request.roomId",0,"int");
			$line = I("request.line",0,"int");
			$col = I("request.col",0,"int");
			$studentId = I("request.studentId",0,"int");
			$id = I("request.id",0,"int");//自增ID
			
			//检测参数
			if (!$roomId){
				echo json_encode(array("stat"=>"0","msg"=>"教室ID为空","data"=>""));exit;
			}
			
			if (!$studentId){
			//	echo json_encode(array("stat"=>"0","msg"=>"学生编号无效","data"=>""));exit;
			}
			
			//更新
			$seatModel = D('SchoolSeatPlan');
			//$result = $seatModel->where("id=".$id)->setField('studentId',$studentId);
			
			if ($studentId && $line && $col){
				//检测重复
				
				//更新
				$data = array('studentId'=>$studentId);
				$result = $seatModel->where("line=".$line ." and col=".$col ." and roomId=".$roomId)->setField($data);
			}else if (!$studentId && $line && $col){
				//清空：
				$data = array('studentId'=>"0");
				$result = $seatModel->where("line=".$line ." and col=".$col ." and roomId=".$roomId)->setField($data);
				$datas = $line."-".$col;
			}
			
			if ($result){
				echo json_encode(array("stat"=>"1","msg"=>"设置成功","data"=>$datas));exit;
			}else{
				echo json_encode(array("stat"=>"0","msg"=>"设置失败","data"=>""));exit;	
			}
			

			exit;	
		}
		
		if (!$banjiId){
			$banjiId = I("request.banjiId",0,"int");
			
		}else{
			
		}
		$this->assign("banjiId",$banjiId);
		
		
		//班级对应的教室
		$banjiModel = D('SchoolBanji');
		$datas_banji = $banjiModel->where("id=".$banjiId)->field("name,roomId")->find();
		if ($datas_banji){
			$roomId = $datas_banji['roomId'];
			$this->assign("roomId",$roomId);
			
			$this->assign("banjiName",$datas_banji['name']);
		}else{
			$this->error("此班级未绑定教室");
		}
		
			
		
		//某个班级的全部学生
		$stuModel = D('SchoolStudents');
		$datas_student = $stuModel->where("banjiId=".$banjiId)->field("id,name")->order("id DESC")->select();
		$this->assign("datas_student",$datas_student);
		

		
		//教室
		$roomModel = D('SchoolRooms');
		$datas_room = $roomModel->where("id=".$roomId)->find();
		if ($datas_room){
			$line = $datas_room['linenumber'];
			$col = $datas_room['columnnumber'];
		}else{
			$this->error("教室记录查询失败");
		}
				
		//echo $line."-".$col;
		$this->assign("line",$line);
		$this->assign("col",$col);
		
		//格式化数组
		$format_seat_table = array();
		for($i=0;$i<$line;$i++){
			for($j=0;$j<$col;$j++){
				$lable1 = $i."-".$j;
				$format_seat_table[$i][$j] = array("lable"=>$lable,"line"=>$i,"col"=>$j);
				
			}
		}
	//	var_dump($format_seat_table);
		
		$seatModel = D('SchoolSeatPlan');
	//	$seatModel->resetSeatPlan($banjiId);//初始化座位表（自动清空原有教室的数据）
		$datas_seat_table = $seatModel->seatTableData($banjiId);//座位表（带学生姓名）
	//	var_dump($datas_seat_table);
		$this->assign("datas_seat_table",$datas_seat_table);
		$datas_seat = $seatModel->where("roomId=".$roomId)->select();
		foreach ($datas_seat as $k=>$v){
			
			
		}
		
		
		
		$this->display("seatplan_table");
	}


	/**
	 * 考场考位设置
	*/
	public function kaoChangKaoWei(){
		
		$examPlanId = I("request.id",0,"int");
		$kaochangId = I("request.kaochangId",0,"int");
		$this->assign("examPlanId",$examPlanId);
		$this->assign("kaochangId",$kaochangId);
		
		$examplanModel = D("SchoolKaochangExamPlan");
		$kaochangModel = D('SchoolKaochang');
		$roomModel = D('SchoolRooms');
		
		$data_kaochang = $kaochangModel->where("id=".$kaochangId)->find();//var_dump($data_kaochang);
		$linenumber = $data_kaochang['linenumber'];
		$columnnumber = $data_kaochang['columnnumber'];
		$this->assign("line",$linenumber);
		$this->assign("col",$columnnumber);
		
		//生成空的原始记录
		$kaochanSeatModel = D("SchoolKaochangSeat");
		$count = $kaochanSeatModel->where("examPlanId=".$examPlanId)->count();
		if (!$count){
			for($i=1;$i<$linenumber+1;$i++){//行
				for($k=1;$k<$columnnumber+1;$k++){//列
					//$pos = $i ."-". $k;
					//echo $pos."<br>";
					$data['examPlanId'] = $examPlanId;
					$data['studentId'] = 0;
					$data['examNumber'] = '';
					$data['rowPosition'] = $i;//行
					$data['colPosition'] = $k;//列
					$data['pos'] = $i."-".$k;
					$kaochanSeatModel->data($data)->add();
		
				}				
			}
		} else{
			
			$datas_seat_table = array();
			$datas_seat_table = $kaochanSeatModel->where("examPlanId=".$examPlanId)->select();
			$stuModel = D('SchoolStudents');
			foreach($datas_seat_table as $k=>$v){
				if ($v['studentId']){
					$one = array();
					$one = $stuModel->where("id=".$v['studentId'])->field("name")->find();
					$datas_seat_table[$k]['studentName'] = $one['name'];
				}
			}
			$this->assign("datas_seat_table",$datas_seat_table);//var_dump($datas_seat_table);
			
		}
		
		$this->assign('studentModalSelType',"radio");//学生弹出框中只允许单选
		
		//班级列表 START
		$banjiModel = D('SchoolBanji');
		$map = array();
		$banjis = $banjiModel->getList($map);
		$this->assign('banjis', $banjis);
		//班级列表 END		
		
		//学生列表 START
		$stuModel = D('SchoolStudents');
		$students = $stuModel->where($map)->order("id DESC")->field("id,name,banjiId")->select();
		//$this->assign('students', $students);
		$out = array();
		$out['stat'] = "1";
		$out['data'] = $students;
		//学生列表 EN	
		
		$end = array();
		//重新组织
		//最外部是班级，banjiId,banjiName,studentCount,studentList
		//studentList中是本班的全部符合条件的学生
		//其它，未分到班级的都显示在其它，其它也算是一个班级了
		foreach($banjis as $k=>$v){		
			$student_list = array();
			$studentCount = 0;
			foreach($students as $kk=>$vv){
				//判断该学生的banjiId
				if ($v['id']==$vv['banjiId']){
					$student_list[] = $vv;
					$studentCount ++;
				}
			}
			$banji_one = array("banjiId"=>$v['id'],"banjiName"=>$v['name'],"studentCount"=>$studentCount,"studentList"=>$student_list);
			$end[] = $banji_one;
		}
		
		//不属任何一个班级
		$banji_other = array();//其它（作为一个科目）
		$student_list_other = array();//归入其它的全部教师
		$studentCountOther = 0;
		foreach($students as $kk=>$vv){
			if (!$vv['banjiId']){
				$student_list_other[] = $vv;
				$studentCountOther ++;
			}
		}
		$banji_other = array("banjiId"=>0,"banjiName"=>"其它","studentCount"=>$studentCountOther,"studentList"=>$student_list_other);
		$end[] = $banji_other;
		$this->assign("data_banji_student_model",$end);
		//var_dump($end);
		
		
		$this->display("kaoChangKaoWei");
	}
	
	/**
	 * 处理考位表
	*/
	public function setKaoChangSeat(){
		$id = I("request.tdid",0,"int");
		$examPlanId = I("request.examPlanId",0,"int");
		$currPos = trim(I("request.currPos"));//当前单元格位置，格式：行-列
		$studentId = I("request.studentId",0,"int");
		$dotype = trim(I("request.dotype"));//处理方式 set设置，reset清除,resetall清除所有
		//考号?????????????????????
		$kaochangSeatModel = D("SchoolKaochangSeat");
		
		if (!$examPlanId){
			//考试安排examPlanId不正确
		}
		if (empty($currPos)){
			//坐标有误
		}	
		if (empty($dotype)){
			$dotype = "set";
		}
		if ($dotype == "reset"){
			$data = array('studentId'=>0,'examNumber'=>'');
			$result = $kaochangSeatModel->where("examPlanId=".$examPlanId ." and pos='".$currPos."'")->setField($data);	
			echo json_encode(array("stat"=>"1","msg"=>"已初始化","data"=>""));exit;
		}
	
		
		
		//把currPos拆分，得到行和列的位置
	//	if (strpos($currPos, '-')){
	//		$left = substr($currPos,0,strpos($currPos, '-'));
	//		$right = substr($currPos,strpos($currPos, '-'));//从第x个字符开始取至最后
	//	}else{
			//位置参数不正确	
	//	}
		
		//当几个参数确保有效的情况下，更新数据库表
		$kaochangSeatModel = D("SchoolKaochangSeat");
		$result = $kaochangSeatModel->where("examPlanId=".$examPlanId ." and pos='".$currPos."'")->setField('studentId',$studentId);
		if ($result){
			echo json_encode(array("stat"=>"1","msg"=>"更新成功","data"=>""));exit;	
		}else{
			echo json_encode(array("stat"=>"0","msg"=>"更新失败","data"=>"examPlanId=".$examPlanId.";studentId=".$studentId.";pos=".$currPos.";id=".$id));exit;
		}
	}

	/*
	 * author:zjh
	 * 刷新班牌
	 * 如果调用EndpointsAction.class.php中的soapClientWay会要求分配终端管理权限，这与实际会有冲突，所以从soapClientWay中摘出下发命令，放在School控制器中来刷新场景
	 */
	public function makeUpdate(){
		if(IS_POST){
			// 命令列表
			$commandList  = array(
				/*'ZipGroupData'					=>	array('commandName'=>'Command_ZipGroupData_X86','commandNote'=>'生成最新数据'),//zjh add cs版的模板中有此命令：生成最新数据按钮
				'PublishNewMainSoft'			=>	array('commandName'=>'Command_PublishNewMainSoft','commandNote'=>'请求更新系统版本'),
				'DownLoadDataFile'				=>	array('commandName'=>'Command_DownLoadDataFile','commandNote'=>'更新数据文件'),
				'RestartMain'					=>	array('commandName'=>'Command_RestartMain','commandNote'=>'请求重启终端主程序'),
				'ShutdownMain'					=>	array('commandName'=>'Command_ShutdownMain','commandNote'=>'请求关闭终端主程序'),
				'RestartSystem'					=>	array('commandName'=>'Command_RestartSystem','commandNote'=>'请求重启终端计算机'),
				'CloseSystem'					=>	array('commandName'=>'Command_CloseSystem','commandNote'=>'请求终端关闭计算机'),
				'CaptureScreen'					=>	array('commandName'=>'Command_CaptureScreen','commandNote'=>'请求终端截图'),
				'ChangeEndpointName'			=>	array('commandName'=>'Command_ChangeEndpointName','commandNote'=>'请求修改终端名称'),
				'ChangeTouchMainCloseTime'		=>	array('commandName'=>'Command_ChangeTouchMainCloseTime','commandNote'=>'请求修改关机时间'),
				'ChangeTouchMainExitCode'		=>	array('commandName'=>'Command_ChangeTouchMainExitCode','commandNote'=>'请求修改终端关机码'),
				'ChangeEndpointInterval'		=>	array('commandName'=>'Command_ChangeEndpointInterval','commandNote'=>'请求修改终端心跳间隙'),
				'ChangeTouchMainAdsDelayTime'	=>	array('commandName'=>'Command_ChangeTouchMainAdsDelayTime','commandNote'=>'请求修改广告延迟时间'),
				'PowerOnOffTime'				=>	array('commandName'=>'Command_PowerOnOffTime','commandNote'=>'指定新的开关机计划'),*/
				'RefreshScene'					=>	array('commandName'=>'Command_RefreshScene','commandNote'=>'刷新场景插件')
			);			
			
			// 表单提交参数
			$endType = trim(I('request.endType'));	// 该参数应对应终端类型
			$type = trim(I('request.type'));	// 该参数：grp-指定的组，eps-选中的终端
			$cmdKey = trim(I('request.cmd'));	// 该参数应对应命令列表的键值
			$tids = trim(I('request.tids'), '-');	// 如果type为grp，该参数的值为终端组classid;如果type为eps，该参数的值为终端id，多个用"-"

			if (empty($type) || empty($cmdKey) || empty($tids)) {
				echo json_encode(array('stat'=>0, 'msg'=>'[ RequestData ] 网络数据错误，请刷新页面重试……'));
				exit();
			}
			
			if (!in_array($cmdKey, array_keys($commandList))) {
				echo json_encode(array('stat'=>0, 'msg'=>'非法操作！'));
				exit();
			}
			
			$params = null;
			$commandName = $commandList[$cmdKey]['commandName'];
			$commandNote = $commandList[$cmdKey]['commandNote'];
			$errMsg = $commandList[$cmdKey]['commandNote'] . '失败...Caused by WSDL.';
				
			//  请求TServer的服务端口
			try {
				$options = array(
						'exceptions'=>true,
						'cache_wsdl'=>WSDL_CACHE_NONE
				);
				$clients = @new SoapClient('http://'.C("TSERVER_IP").':'.C("SOAP_PORT").'?wsdl', $options);//'http://localhost:5249?wsdl'
			} catch (Exception $e) {
				//echo json_encode(array('stat'=>0, 'msg'=>$e->getMessage()));
				echo json_encode(array('stat'=>0, 'msg'=>$errMsg));
				exit();
			} catch(SoapFault $f) {
				// echo json_encode(array('stat'=>0, 'msg'=>$f->getMessage()));
				echo json_encode(array('stat'=>0, 'msg'=>$errMsg));
				exit();
			}				
				
				
			//echo json_encode(array('stat'=>1, 'msg'=>'ok'));exit;	
		//	请求TServer的服务端口
		//	if ($type == 'eps') {
				$tidsArr = explode('-', $tids);//终端自增Id
				if (count($tidsArr > 0)) {
					$EPModel = D('Endpoint');
					foreach($tidsArr as $v) {
						$tmid = $EPModel->where(array('tid'=>$v))->getField('touchMainId');
						if (!$tmid) {
							continue;
						}
						
						try {
						//	$taskModel = D('EPTask');//任务
						//	$taskModel->clearHistoryTask();
							
							$commandParam1 = '';
							$params = array('systemName'=>C("SYSTEMNAME")/*董工加的参数，本文件后面多个params格式同此/apps/conf/madmin/config.php*/,'touchMainId'=>$tmid,'commandName'=>$commandName,'commandParam1'=>$commandParam1,'commandParam2'=>'','commandNote'=>$commandNote);
							
							$ss = $clients->EndpointTask($params);
							
						} catch (Exception $e) {
							// echo json_encode(array('stat'=>0, 'msg'=>$e->getMessage()));
							echo json_encode(array('stat'=>0, 'msg'=>$errMsg));
							exit();
						} catch(SoapFault $f) {
							// echo json_encode(array('stat'=>0, 'msg'=>$f->getMessage()));
							echo json_encode(array('stat'=>0, 'msg'=>$errMsg));
							exit();
						}
					}
				
					echo json_encode(array('stat'=>1));
					exit();
				} else {
					echo json_encode(array('stat'=>0, 'msg'=>'[ EndPointsID ] 数据请求错误，请刷新页面重试....'));
					exit();
				}
			//}	
		}else{
			
			$banjis = session("user_banji_list");//可管理的班级
			
			//班级对应的教室
			$SchoolBanjiModel = D('SchoolBanji');
			$map = array();
			$map['id'] = array('IN',$banjis);
			$datas_bj = $SchoolBanjiModel->where($map)->field("roomId")->select();	//var_dump($datas_bj);		
			$tmp=array();
			if (is_array($datas_bj) and !empty($datas_bj)){
				foreach ($datas_bj as $k=>$v){
					$tmp[] = $v['roomId'];
					$str .=$v['roomId'].",";
				}
			}
			
			$arrRoom=array_flip($tmp);//去除重复
			$rooms = implode(",",$arrRoom);//转化为逗号分隔的教室Id
			
			//教室对应的终端
			$SchoolRoomModel = D('SchoolRooms');
			$map['id'] = array('IN',$rooms);
			$datas_room = $SchoolRoomModel->where($map)->field("endpointId")->select();	//var_dump($datas_room);
			$ends=array();
			if (is_array($datas_room) and !empty($datas_bj)){
				foreach($datas_room as $k=>$v){
					if (!empty($v['endpointId'])){
						if (stripos($a['endpointId'],',')){
							//拆成数组，并与$tmp组合
							$ends = array_merge ($ends,$explode(",",$v['endpointId']));
						}else{
							//作为元素加到$tmp
							array_push($ends,intval($v['endpointId']));
						}
					}
				}//var_dump($ends);
			}
			
			//终端的touchMainId
			$EPModel = D('Endpoint');
			$map=array();
			$map['tid'] = array('IN',$ends);
			$endClassIdsArr = $EPModel->where($map)->getField('touchMainId', true);	//var_dump($endClassIdsArr);
					
			
			// 将当前登陆用户可管理的班级展示出来。数据来源于session
			$class_arr = explode(",", session('user_banji_list'));
			$class_name_arr = explode(",", session('user_banji_list_cn'));
			if($class_arr[0] == "0"){
				array_shift($class_arr);//删除第一个数组元素
			}
			if($class_arr[count($class_arr)-1] == "0"){
				array_pop($class_arr);//删除最后一个数组元素
			}
			if(!$class_name_arr[count($class_name_arr)-1]){
				array_pop($class_name_arr);
			}
			if(count($class_arr) == count($class_name_arr)){
				$temp_arr = array();
				foreach ($class_arr as $key => $value) {
					$temp_arr[$key][class_id] = $value;
				}
				foreach ($class_name_arr as $key => $value) {
					$temp_arr[$key][class_name] = $value;
				}
			}
			$this->classes = $temp_arr;
			$this->display();
		}
	}


	/**
	 * 班级评比 START ***************************************************************
	*/
	
	
	/**
	 * author:zjh
	 * 班级评比结果列表
	 * 如可管理班级为1，直接显示结果列表
	 * 如可管理多个班级，显示班级列表
	*/
	function ComparisonResults(){
		$banjiId = I("request.banjiId",0,"intval");
		
		$model = D('SchoolComparisonResult');
		$banjiModel = D('SchoolBanji');
		
		$map = array();		
		$user_banji_str = session("user_banji_list");//可管理班级
		$user_banji_arr = array_unique(explode(",",$user_banji_str));//可管理班级拆为数组，移除重复值，主要去0
	//	unset($user_banji_arr[array_search("0",$user_banji_arr)]);//array_search("Cat",$a)按元素值返回键名。去除后保持索引
		array_splice($user_banji_arr,$user_banji_arr[array_search("0",$user_banji_arr)],1);//注意此处不能用unset，因为会留下键名
		
		$user_banji_count = count($user_banji_arr);//可管理的班级数量
	//	echo '可管理班级数量'.$user_banji_count;
		//var_dump($user_banji_arr);
	//	echo $user_banji_arr[0];
		
		
		
		//未指定banjiId
		if (!$banjiId){
			switch ($user_banji_count) {
				case 0://可管理0个班级
					$this->error("您可管理的班级数为0");
					break;
				case 1://可管理1个班级
					
					$banjiId = intval($user_banji_arr[0]);//var_dump($banjiId);
					$one = $banjiModel->getOne($banjiId);//var_dump($one);
					$banjiName = $one['name'];
					$this->assign("banjiName",$banjiName);
					
					$this->ComparisonResultsOfTheBanjiId($banjiId);
					//$this->display("ComparisonResults");
					break;
				default://可管理多个班级
					$model = D('SchoolBanji');
					// 加载数据分页类
					import('ORG.Util.Page');
					
					// 数据分页
					$totals = $model->where($map)->count();
					$Page = new Page($totals, 10);
					$show = $Page->show();
					$this->assign('page', $show);
					
					$datas = $model->where($map)->order("id DESC")->limit($Page->firstRow.','.$Page->listRows)->select(); 
					$this->assign('datas', $datas);
					$this->display("ComparisonResults_banjilist");
			}
		}else{
			$this->ComparisonResultsOfTheBanjiId($banjiId);
		}
	}

	/**
	 * 显示指定班级的班级评比结果ID
	*/
	function ComparisonResultsOfTheBanjiId($banjiId){
		
		if (session("username") == C('ADMIN_AUTH_KEY')) {
			//超级管理员
		}else{
			//是否可操作此班级ID
			$this->check_banji($banjiId);
		}
		$this->assign("banjiId",$banjiId);
		
		$compItemModel = D('SchoolComparisonItem');//班级评比项目
		$resultModel = D('SchoolComparisonResult');//班级评比结果
		$banjiModel = D('SchoolBanji');//班级
		$stuModel = D('SchoolStudents');//学生
			
		//显示班级名称
		$one = $banjiModel->getOne($banjiId);//var_dump($one);
		$banjiName = $one['name'];
		$this->assign("banjiName",$banjiName);			
				
		//本班所有学生
		$studentIdStrTheBanji = $banjiModel->getStudentIdsStrFromBanjiId($banjiId);//本班所有学生Id，逗号分隔	echo $studentIdStr; 		$map['student_id'] = array('in',$studentIdStr);//字段student_id in 指定班级ID全班的学生Id


        // 加载数据分页类
        import('ORG.Util.Page');
        
        // 数据分页
        $totals = $compItemModel->where($map)->count();
        $Page = new Page($totals, 10);
        $show = $Page->show();
        $this->assign('page', $show);	


		//最上层是班级评比项目列表（实际每个班都显示了全部项目，不管有没有指定学生）
		$datas_comp_item = $compItemModel->order("id ASC")->limit($Page->firstRow.','.$Page->listRows)->select();//评比项目
		
		
		foreach ($datas_comp_item as $k => $v){
			
			//班级评比结果
			$map1['student_id'] = array('in',$studentIdStrTheBanji);//只显示本班的，通过判断student_id in 本班全部学生Id
			$map1['comparison_item_id'] = $v['id'];
			$datas_v = $resultModel->where($map1)->order("id ASC")->select();//获到到此项目的结果，可能是0条、1条、多条
			$tmp = array();
			foreach ($datas_v as $kk=>$vv){
				$tmp[] = $vv['student_id'];//加入临时数组后用逗号切割
			}
			
			//有记录，用逗号切割
			if (count($tmp)){
				$studentIdStr = implode(",",$tmp);//逗号分隔的studentId
				$datas_comp_item[$k]['studentId'] = $studentIdStr;
				
				//显示学生姓名，多个用逗号分隔
				$map_stu['id'] = array('in',$studentIdStr);//学生Id字符串必须是当前班级的
				$datas_stu = $stuModel->where($map_stu)->order("id ASC")->select();//var_dump($datas_stu);
				$tmp1 = array();
				foreach($datas_stu as $k1=>$v1){
					$tmp1[] = $v1['name'];
				}
				$studentName = implode("，",$tmp1);
				$datas_comp_item[$k]['studentName'] = $studentName;
			}else{
					
			}
			
			$datas_comp_item[$k]['banjiName'] = $banjiName;

		}
		
		$this->assign("datas",$datas_comp_item);
		//var_dump($datas_comp_item);

		//当前班级的所有学生 START （对话框）
		$students = $stuModel->where("banjiId=".$banjiId)->field("id,name")->order("id DESC")->select();
		$this->assign('students', $students);//var_dump($students);
		//当前班级的所有学生 END			


		$this->display("ComparisonResults_index");
	}


	/**
	 * 列出所有的“班级评比项目”
	*/
	function ComparisonItemList(){
		$compItemModel = D('SchoolComparisonItem');
		$datas_comp_item = $compItemModel->where($map)->order("id ASC")->select();
		$this->assign("datas_comp_item",$datas_comp_item);
	}

	/**
	 * 设置评比结果
	*/
	function ComparisonResultsSet(){
		$banjiId = I("post.banjiId",0,"intval");
		$selStudentIdStr = I("post.members","","trim");//所选学生Id，可能为空，也可能是1个或多个，多个的话是逗号分隔的
		$curItemId = I("post.curItemId",0,"intval");//当前班级评比项目Id
		if (!$banjiId){
			die(json_encode(array("stat"=>"0","msg"=>"班级ID为空")));
		}
		if (!$curItemId){
			die(json_encode(array("stat"=>"0","msg"=>"当前班级评比项目ID为空")));
		}
		//检测是否有权限操作本班
		
		//获取到本班的所有学生
		$stuModel = D('SchoolStudents');//学生
		$studentIdStrOfTheBanji = $stuModel->getBanjiStudentIdStr($banjiId);//本班所有学生，逗号分隔
		//if ($banjiId){
		//	die(json_encode(array("stat"=>"1","msg"=>"本班所有学生","data"=>$studentIdStrOfTheBanji)));
		//}
		
		//先清空本班本评比项目，然后插入新记录（简单化操作，本评比项目下所有本班学生的记录都删除）
		$map = array();
		$map['comparison_item_id'] = $curItemId;
		$map['student_id'] = array("IN",$studentIdStrOfTheBanji);//
		$resultModel = D('SchoolComparisonResult');//班级评比结果
		$result = $resultModel->where($map)->delete();
		
		//
		
		//插入所选择的学生		
		if (!empty($selStudentIdStr)){
			$selStudentIdArr = explode(",",$selStudentIdStr);//选择的学生Id，转为数组
			if (count($selStudentIdArr)){
				foreach ($selStudentIdArr as $k=>$v){
					//循环插入新记录
					$data = array();
					$data['student_id'] = $v;
					$data['comparison_item_id'] = $curItemId;
					$result = $resultModel->data($data)->add();//这儿实际应该检测是否成功，选择多条的话插入失败的几率还是有的
				}	
			}
		}		
		
		
		die(json_encode(array("stat"=>"1","msg"=>"更新成d功","data"=>$selStudentIdStr)));
		
		
		
	}
	

	/**
	 * author:zjh 
	 * 新增班级评比结果
	*/
	/*
	function ComparisonResultsAdd(){
		if (IS_POST) {
			$this->ComparisonResultsSave();//保存
		} else {
			
			//班级评比项目下拉列表
			$comparisonItem = M()->table('TB_Sch_Comparison_Item')->order("id DESC")->select();
			$this->assign("comparisonItem",$comparisonItem);

			//学生选择对话框 START --------------------------------------------------------------------
			$seltype = "radio";
			$this->assign('studentModalSelType', $seltype);//学生弹出框中只允许单选
			$stuModel = D('SchoolStudents');
			$map = array();
			$map_bj = array();
			
			if(session("username") == C('ADMIN_AUTH_KEY')){
				//超级管理员可显示所有班级
			} else {
				//获取到可管理班级
				$user_banji_str = session("user_banji_list");//$map['banjiId'] = array("IN",$user_banji_str);
				$map['banjiId'] = array("IN",$user_banji_str);
				$map_bj['id'] = array("IN",$user_banji_str);
			}

			//班级列表 START
			$banjiModel = D('SchoolBanji');
			//$map_bj = array();
			$banjis = $banjiModel->where($map_bj)->select();
			//班级列表 END
			
			
			//学生列表 START
			$stuModel = D('SchoolStudents');
			$students = $stuModel->where($map)->order("id DESC")->field("id,name,banjiId")->select();
			//$this->assign('students', $students);
			$out = array();
			$out['stat'] = "1";
			$out['data'] = $students;
			//学生列表 EN	
			
			$end = array();
			//重新组织
			//最外部是班级，banjiId,banjiName,studentCount,studentList
			//studentList中是本班的全部符合条件的学生
			//其它，未分到班级的都显示在其它，其它也算是一个班级了
			foreach($banjis as $k=>$v){		
				$student_list = array();
				$studentCount = 0;
				foreach($students as $kk=>$vv){
					//判断该学生的banjiId
					if ($v['id']==$vv['banjiId']){
						$student_list[] = $vv;
						$studentCount ++;
					}
				}
				$banji_one = array("banjiId"=>$v['id'],"banjiName"=>$v['name'],"studentCount"=>$studentCount,"studentList"=>$student_list);
				$end[] = $banji_one;
			}
			
			//不属任何一个班级
			if(session("username") == C('ADMIN_AUTH_KEY')){
				$banji_other = array();//其它（作为一个科目）
				$student_list_other = array();//归入其它的全部教师
				$studentCountOther = 0;
				foreach($students as $kk=>$vv){
					if (!$vv['banjiId']){
						$student_list_other[] = $vv;
						$studentCountOther ++;
					}
				}
				$banji_other = array("banjiId"=>0,"banjiName"=>"其它","studentCount"=>$studentCountOther,"studentList"=>$student_list_other);
				$end[] = $banji_other;
			}
			$this->assign("data_banji_student_model",$end);
			$this->banjis = $banjis;	
			
			//学生选择对话框 END --------------------------------------------------------------------
			
			$this->display("ComparisonResultsEdit");
		}
	}
	*/

	/**
	 * author:zjh 
	 * 修改班级评比结果
	*/
	/*
	function ComparisonResultsEdit(){
		if (IS_POST) {
			$this->ComparisonResultsSave();//保存
		} else {
			//当前记录
			$id = I("request.id",0,"intval");
			if (!$id){
				$this->error("参数错误");
			}
			
			$rmodel = D('SchoolComparisonResult');
			$datas_result = $rmodel->where("id=".$id)->find();
			if (!$datas_result['id']){
				$this->error("无此记录");
			}else{
				$this->assign("datas_result",$datas_result);//当前记录输出
			}
			
			//班级评比项目下拉列表
			$comparisonItem = M()->table('TB_Sch_Comparison_Item')->order("id DESC")->select();
			$this->assign("comparisonItem",$comparisonItem);

			//学生
			if ($datas_result['student_id']){
				$stuModel = D('SchoolStudents');
				$student = $stuModel->where("id=".$datas_result['student_id'])->field("name")->find();//var_dump($student);
				$this->assign("studentName",$student['name']);//当前记录中的学生的中文名
				
				//学生对话框：在对话框中选中
				$this->assign("strStudentsId",$datas_result['student_id']);//在学生对话框中预先选中学生
			}


			//学生选择对话框 START --------------------------------------------------------------------
			$seltype = "radio";
			$this->assign('studentModalSelType', $seltype);//学生弹出框中只允许
			$stuModel = D('SchoolStudents');
			$map = array();
			$map_bj = array();
			
			if(session("username") == C('ADMIN_AUTH_KEY')){
				//超级管理员可显示所有班级
			} else {
				//获取到可管理班级
				$user_banji_str = session("user_banji_list");//$map['banjiId'] = array("IN",$user_banji_str);
				$map['banjiId'] = array("IN",$user_banji_str);
				$map_bj['id'] = array("IN",$user_banji_str);
			}

			//班级列表 START
			$banjiModel = D('SchoolBanji');
			//$map_bj = array();
			$banjis = $banjiModel->where($map_bj)->select();
			//班级列表 END
			
			
			//学生列表 START
			$stuModel = D('SchoolStudents');
			$students = $stuModel->where($map)->order("id DESC")->field("id,name,banjiId")->select();
			//$this->assign('students', $students);
			$out = array();
			$out['stat'] = "1";
			$out['data'] = $students;
			//学生列表 EN	
			
			$end = array();
			//重新组织
			//最外部是班级，banjiId,banjiName,studentCount,studentList
			//studentList中是本班的全部符合条件的学生
			//其它，未分到班级的都显示在其它，其它也算是一个班级了
			foreach($banjis as $k=>$v){		
				$student_list = array();
				$studentCount = 0;
				foreach($students as $kk=>$vv){
					//判断该学生的banjiId
					if ($v['id']==$vv['banjiId']){
						$student_list[] = $vv;
						$studentCount ++;
					}
				}
				$banji_one = array("banjiId"=>$v['id'],"banjiName"=>$v['name'],"studentCount"=>$studentCount,"studentList"=>$student_list);
				$end[] = $banji_one;
			}
			
			//不属任何一个班级
			if(session("username") == C('ADMIN_AUTH_KEY')){
				$banji_other = array();//其它（作为一个科目）
				$student_list_other = array();//归入其它的全部教师
				$studentCountOther = 0;
				foreach($students as $kk=>$vv){
					if (!$vv['banjiId']){
						$student_list_other[] = $vv;
						$studentCountOther ++;
					}
				}
				$banji_other = array("banjiId"=>0,"banjiName"=>"其它","studentCount"=>$studentCountOther,"studentList"=>$student_list_other);
				$end[] = $banji_other;
			}
			$this->assign("data_banji_student_model",$end);
			$this->banjis = $banjis;	
			
			//学生选择对话框 END --------------------------------------------------------------------		
			
			$this->display("ComparisonResultsEdit");
		}
	}
	
	*/
	
	
	

	/**
	 * author:zjh 
	 * 保存班级评比结果
	*/
	/*
	function ComparisonResultsSave(){
		$id = I("request.id",0,"intval");//当前记录Id
		
		$comparisonItemId = I("request.comparisonItemId",0,"intval");//班级评比项目Id
		
		$studentIdArr=I('post.studentIdStr');
		$studentId = intval(implode(',',$studentIdArr));//弹窗中设为单选，此处收到的只是一个数值
		
		$model = D('SchoolComparisonResult');
		
		$data = array();
		$data['student_id'] = $studentId;
		$data['comparison_item_id'] = $comparisonItemId;
		
		$func = '';
		if ($id) {
			$func = 'save';
			$data['id'] = $id;
		} else {
			$func = 'add';
		}
		
	//	var_dump($data);
	//	exit;
		$result = $model->$func($data);
		
		// 执行操作
		if ($result !== FALSE) {
			$this->success('操作成功！', U('School/ComparisonResults'));
		} else {
			$this->error('操作失败！[原因]：' . $Model->getError());
		}		
	}
	*/
	
	/**
	 * author:zjh 
	 * 删除班级评比结果
	*/
	function ComparisonResultsDel(){
	    $ids = trim(I('post.nids'));
	
	    if (empty($ids)) {
	        die(json_encode(array('stat'=>0, '请求参数错误！')));
	    }
	
	    $idsArr = explode(',', trim($ids, ','));
	    if (count($idsArr) <= 0) {
	        die(json_encode(array('stat'=>0, '请求参数错误！')));
	    }
	
	    $model = D('SchoolComparisonResult');
	    $result = $model->where(array('id'=>array('in', $idsArr)))->delete();
	    if ($result !== false) {
	        die(json_encode(array('stat'=>1, '操作成功！')));
	    } else {
	        die(json_encode(array('stat'=>0, '操作失败！[原因]：' . $model->getError())));
	    }			
		
	}




	/**
	 * 班级评比 END ******************************************************************
	*/


	public function aaaaaa(){
		echo "aaaaaa";
	}
	
	public function bbbbbb(){
		echo "bbbbbb";
	}
	
	public function cccccc(){
		echo "cccccc";
	}
	public function aaa(){
		echo "aaa";
	}	
	
	public function bbb(){
		echo "bbb";
	}	
	
	public function ccc(){
		echo "ccc";
	}
	
	public function ddd(){
		echo "ddd";
	}	
	
	/**
	 * 班级评比项目列表页面展示数据，页面挺简单，过滤条件更简单，展示全部，按照id倒序排列
	 */
	public function comparisonItems(){
        import('ORG.Util.Page');
		$total = M()->table('TB_Sch_Comparison_Item')->count();
		$model = M()->table('TB_Sch_Comparison_Item');
        $numPerPage = $_GET[numPerPage] ? : 12;
        $Page = new Page($total, $numPerPage);
		$this->rows = $model->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
        $this->show = $Page->show();
		$this->display();
	}
	
	/**
	 * 新增班级评比项目
	 */
	public function addComparisonItem(){
		$comparison = M()->table('TB_Sch_Comparison_Item');
		if(IS_POST){
			if($comparison->create()){
				if($latest = $comparison->add()){
				   	$this->success($latest.'操作成功！', U('comparisonItems'));
				}else{
					$this->error($comparison->getError());
				}
			}else{
				$this->error('操作失败！[原因]：' . $comparison->getError(), U('comparisonItems'));
			}
		}else{
			$this->display('editComparisonItem');
		}
	}
	
	/**
	 * 编辑班级评比项目页面，post提交之后更新数据库相关记录，提交之前则展示之前的数据于页面
	 */
	public function editComparisonItem(){
		$id = $_GET[id] ? $_GET[id] : 1;
		$comparison = M()->table('TB_Sch_Comparison_Item');
		if(IS_POST){
			if($comparison->create()){
				$comparison->save();
			   	$this->success('操作成功！', U('comparisonItems'));
			}else{
				$this->error('操作失败！[原因]：' . $comparison->getError(), U('comparisonItems'));
			}
		}else{
			$this->row = $comparison->find($id);
			$this->display();
		}
	}

	/**
	 * ajax删除，其实ajax删除已经越来越普遍了，删除操作以后默认就应该是这样了
	 * @return [text] [返回1表示删除成功，前端页面会有相应提示，返回0表示操作有误并给出提示]
	 */
	public function delComparisonItem(){
		$id = $_POST[id] ? $_POST[id]: 1;
		$comparison = M()->table('TB_Sch_Comparison_Item');
		if($comparison->delete($id)){
			echo 1;
		}else{
			echo 0;
		}
	}
	
	/**
	 * 对班级评比项目的批量删除
	 */
	public function batchDel(){
		$ids = $_POST[batchDel];
		foreach($ids as $id){
			$comparison = M()->table('TB_Sch_Comparison_Item');
			if($comparison->delete($id)){
				$r++;
			}
		}
		if($r == count($ids)){
		   	$this->success('操作成功！', U('comparisonItems'));
		}else{
			$this->error('操作失败！[原因]：' . $comparison->getError(), U('comparisonItems'));
		}
	}
		
}