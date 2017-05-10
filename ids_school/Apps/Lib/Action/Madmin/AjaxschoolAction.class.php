<?php
class AjaxschoolAction extends Action {
	
	/**
	 * zjh add
	 * 学校LOGO上传，新增时不入库，修改时删除旧图片并更新记录
	*/
	public function uploadify_banji_logo() {
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
			$dbSavePath = $savePath .$upResult['savename'];//$uploadRoot.$savePath . $upResult['savename'];//utf82gbk实际什么也不做
			
			$banjiModel = D("SchoolBanji");
			
			//先取到原图片地址
			$sch_datas = $banjiModel->where("id=".$dataID)->field("logo")->find();
			
			if ($sch_datas){
				$old_pic_path = $sch_datas['logo'];
			//	file_put_contents("debug-999.txt",PHP_EOL."old_pic_path:".$old_pic_path.PHP_EOL,FILE_APPEND);//写调试到TXT
				//$upRootPath = rtrim(C('UPLOAD_COMM_PATH'), '/') . '/';
				@unlink(C('UPLOAD_COMM_PATH').$old_pic_path);//删除旧图片
				if (trim($old_pic_path) != '' && is_file(C('UPLOAD_COMM_PATH').$old_pic_path)) {
								
				}else{
					;
				}					
			}
			
			if ($dataID){
				$datas = array();
				$datas['id'] = $dataID;
				$datas['logo'] = str_replace( '\\', '/',$dbSavePath);
				$result = $banjiModel->save($datas);
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
		echo json_encode($reInfo);
	}	
	
	/**
	 * zjh add
	 * 学校荣誉上传，新增时不入库，修改时删除旧图片并更新记录
	*/
	public function uploadify_banji_honor() {
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
			
			$model = D("SchoolBanjiHonor");
			
			//先取到原图片地址
			$sch_datas = $model->where("id=".$dataID)->field("imagepath")->find();
			
			if ($sch_datas){
				$old_pic_path = $sch_datas['imagepath'];
			//	file_put_contents("debug-999.txt",PHP_EOL."old_pic_path:".$old_pic_path.PHP_EOL,FILE_APPEND);//写调试到TXT
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
				$datas['imagepath'] = str_replace( '\\', '/',$dbSavePath);
				$result = $model->save($datas);
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
		echo json_encode($reInfo);
	}		
	
	/**
	 * zjh add
	 * 事件图片上传，新增时不入库，修改时删除旧图片并更新记录
	*/
	public function uploadify_emergencies_image() {
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
			
			$model = D("SchoolEmergenciesTypes");
			
			//先取到原图片地址
			$datas = $model->where("id=".$dataID)->field("imagePath")->find();
			
			if ($datas){
				$old_pic_path = $datas['imagePath'];
			//	file_put_contents("debug-999.txt",PHP_EOL."old_pic_path:".$old_pic_path.PHP_EOL,FILE_APPEND);//写调试到TXT
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
				$datas['imagePath'] = str_replace( '\\', '/',$dbSavePath);
				$result = $model->save($datas);
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
		echo json_encode($reInfo);
	}	
	
	
	
	public function allStudent(){
		//学生列表 START
		$stuModel = D('SchoolStudents');
		$map = array();
		$students = $stuModel->getList($map);
		//$this->assign('students', $students);
		echo json_encode($students);
		//学生列表 EN
	}	
	
	public function studentsOneBanji(){
		$banjiId = I("request.banjiId",0,"int");
		$keyboard = trim(I("request.keyboard"));
		$keyType = trim(I("request.keyType"));//关键字类型
		$map = array();
		if ($banjiId){
			$map['banjiId'] = $banjiId;
		} else{
			if (session("username") == C('ADMIN_AUTH_KEY')) {//session("username")
				//超级管理员，列表中显示全部班级
				;
			}else{
				//非超级管理员，只显示自己有管理权限的
				$map['banjiId'] = array("IN",session('user_banji_list'));
			}

		}
		
		if (!empty($keyboard)){
			switch ($keyType){
				case 'code':
					$map['code'] = array('like', '%' . $keyboard . '%');
					break;
				case 'name':
					$map['name'] = array('like', '%' . $keyboard . '%');
					break;
				default:
					$map['name'] = array('like', '%' . $keyboard . '%');
			}			
		}
		
		//学生列表 START
		$stuModel = D('SchoolStudents');
		$students = $stuModel->where($map)->order("id DESC")->field("id,name")->select();
		//$this->assign('students', $students);
		$out = array();
		$out['stat'] = "1";
		$out['data'] = $students;
		echo json_encode($out);exit;
		//学生列表 EN
	}		
	
	/*
	 * 获取学生选择对话框搜索结果
	*/
	public function getStudents(){
		$banjiId = I("request.banjiId",0,"int");
		$keyboard = trim(I("request.keyboard"));
		$keyType = trim(I("request.keyType"));//关键字类型
		$map = array();
		if ($banjiId){
			$map['banjiId'] = $banjiId;
		} else{
			if (session("username") == C('ADMIN_AUTH_KEY')) {//session("username")
				//超级管理员，列表中显示全部班级
				;
			}else{
				//非超级管理员，只显示自己有管理权限的
				$map['banjiId'] = array("IN",session('user_banji_list'));
			}

		}
		
		if (!empty($keyboard)){
			switch ($keyType){
				case 'code':
					$map['code'] = array('like', '%' . $keyboard . '%');
					break;
				case 'name':
					$map['name'] = array('like', '%' . $keyboard . '%');
					break;
				default:
					$map['name'] = array('like', '%' . $keyboard . '%');
			}			
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
		
		//header("Content-type:text/html;charset=utf-8");
		//var_dump($end);
		
		//规范的显示一下用于调试检查
		/*
		foreach($end as $k=>$v){
			echo "<br>".$v['banjiName']." ".$v['studentCount']."<br>";
			foreach ($v['studentList'] as $kk=>$vv){
				//var_dump($vv);
				echo $vv['name'].",";
			}
				
		}*/
				
		
		$result = array("stat"=>"1","data"=>$end);
		echo json_encode($result);exit;
	}
	
	
	
	/*获取教师搜索结果*/
	public function getTeachers(){
		$gradeId = I("request.gradeId",0,"int");
		$banjiId = I("request.banjiId",0,"int");
		$subjectId = I("request.subjectId",0,"int");
		$keyboard = trim(I("request.keyboard"));
		$keyType = trim(I("request.keyType"));//关键字类型
		
		$teacherBanjiModel = D("SchoolTeacherBanji");
		$teacherSubjectModel = D('SchoolTeacherSubject');
		
		$map = array();
		
		if (session("username") == C('ADMIN_AUTH_KEY')) {//session("username")
			//超级管理员，列表中显示全部班级
			;
		}else{
			//非超级管理员，只显示自己有管理权限的
			//$map['banjiId'] = array("IN",session('user_banji_list'));
		}
		//file_put_contents("debug-getteachers.txt",PHP_EOL."----subjectId=".$subjectId.PHP_EOL,FILE_APPEND);
		
		$teacherModel = D('SchoolTeachers');
		$datas = array();
		
		if ($banjiId){
			
			$map_t = array();
			$map_t['banjiId'] = $banjiId;
			$tmpArr = array();
			$datas = $teacherBanjiModel->where($map_t)->field("teacherId")->select();
			
			foreach($datas as $k=>$v){
				$tmpArr[] = $v['teacherId'];
			}
			
			//此班级所有的教师数组
			$teachersArr_bj = array_unique($tmpArr);//移除数组中重复的值
			
			//此班级的所有教师，用逗号分隔
			$teacherStr_banji = implode(",",$teachersArr_bj);		
	//		file_put_contents("debug-getteachers.txt",PHP_EOL."----teacherStr_banji=".$teacherStr_banji.PHP_EOL,FILE_APPEND);
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
	//		file_put_contents("debug-getteachers.txt",PHP_EOL."----teacherStr_subject=".$teacherStr_subject.PHP_EOL,FILE_APPEND);
			$map['id'] = array("IN",$teacherStr_subject/*这已经是满足结果的教师*/);
			
		}
		if ($banjiId && $subjectId){
			$teacherArr_tmp = array_intersect($teachersArr_bj,$teachersArr_sub);//合并
			$map['id'] = array("IN",$teacherArr_tmp/*满足两个条件的交集ID*/);
			//file_put_contents("debug-getteachers.txt",PHP_EOL."----teacherArr_tmp=".implode(",",$teacherArr_tmp).PHP_EOL,FILE_APPEND);
		}
		
		
		if ($keyboard){
			switch ($keyType) {
				case "name"://姓名
					$map['name']=array('LIKE','%'.$keyboard.'%');
					break;
				case "code"://教师编号
					$map['code']=array('LIKE','%'.$keyboard.'%');
					break;
				default:
					$map['name']=array('LIKE','%'.$keyboard.'%');
			}
		}
		
		$teachers = array();
		$teachers = $teacherModel->getTeachersAndSubjects($map);
		
		//科目列表 START
		$subjectsModel = D('SchoolSubjects');
		$map = array();
		if ($subjectId){
			$map['id'] = $subjectId;	
		}
		$subjects = $subjectsModel->where($map)->field("id,name")->order("id DESC")->select();
		//$this->assign('subjects', $subjects);
		//列表 END	
		
		$out = array();
		$out['stat'] = "1";
		$out['data'] = $teachers;
		$out['subjects'] = $subjects;
//		file_put_contents("debug-getteachers.txt",PHP_EOL."debug:".PHP_EOL.":".serialize($teachers).PHP_EOL,FILE_APPEND);//写调试到TXT
//		file_put_contents("debug-getteachers.txt",PHP_EOL."debug:".PHP_EOL.":".$teacherStr_subject.PHP_EOL,FILE_APPEND);//写调试到TXT
		echo json_encode($out);exit;
		
		//
	}		
	
	
	
	/*获取教师搜索结果*/
	public function getTeachers2(){
		$gradeId = I("request.gradeId",0,"int");
		$banjiId = I("request.banjiId",0,"int");
		$subjectId = I("request.subjectId",0,"int");
		$keyboard = trim(I("request.keyboard"));
		$keyType = trim(I("request.keyType"));//关键字类型
		
		$teacherBanjiModel = D("SchoolTeacherBanji");
		$teacherSubjectModel = D('SchoolTeacherSubject');
		
		$map = array();
		
		if (session("username") == C('ADMIN_AUTH_KEY')) {//session("username")
			//超级管理员，列表中显示全部班级
			;
		}else{
			//非超级管理员，只显示自己有管理权限的
			//$map['banjiId'] = array("IN",session('user_banji_list'));
		}
		//file_put_contents("debug-getteachers.txt",PHP_EOL."----subjectId=".$subjectId.PHP_EOL,FILE_APPEND);
		
		$teacherModel = D('SchoolTeachers');
		$datas = array();
		
		if ($banjiId){
			
			$map_t = array();
			$map_t['banjiId'] = $banjiId;
			$tmpArr = array();
			$datas = $teacherBanjiModel->where($map_t)->field("teacherId")->select();
			
			foreach($datas as $k=>$v){
				$tmpArr[] = $v['teacherId'];
			}
			
			//此班级所有的教师数组
			$teachersArr_bj = array_unique($tmpArr);//移除数组中重复的值
			
			//此班级的所有教师，用逗号分隔
			$teacherStr_banji = implode(",",$teachersArr_bj);		
	//		file_put_contents("debug-getteachers.txt",PHP_EOL."----teacherStr_banji=".$teacherStr_banji.PHP_EOL,FILE_APPEND);
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
	//		file_put_contents("debug-getteachers.txt",PHP_EOL."----teacherStr_subject=".$teacherStr_subject.PHP_EOL,FILE_APPEND);
			$map['id'] = array("IN",$teacherStr_subject/*这已经是满足结果的教师*/);
			
		}
		if ($banjiId && $subjectId){
			$teacherArr_tmp = array_intersect($teachersArr_bj,$teachersArr_sub);//合并
			$map['id'] = array("IN",$teacherArr_tmp/*满足两个条件的交集ID*/);
			//file_put_contents("debug-getteachers.txt",PHP_EOL."----teacherArr_tmp=".implode(",",$teacherArr_tmp).PHP_EOL,FILE_APPEND);
		}
		
		
		if ($keyboard){
			switch ($keyType) {
				case "name"://姓名
					$map['name']=array('LIKE','%'.$keyboard.'%');
					break;
				case "code"://教师编号
					$map['code']=array('LIKE','%'.$keyboard.'%');
					break;
				default:
					$map['name']=array('LIKE','%'.$keyboard.'%');
			}
		}
		
		$teachers = array();
		$teachers = $teacherModel->getTeachersAndSubjects($map);
		
		//科目列表 START
		$subjectsModel = D('SchoolSubjects');
		$map = array();
		if ($subjectId){
			$map['id'] = $subjectId;	
		}
		$subjects = $subjectsModel->where($map)->field("id,name")->order("id DESC")->select();
		//$this->assign('subjects', $subjects);
		//列表 END	
		
		$out = array();
		$out['stat'] = "1";
		$out['data'] = $teachers;
		$out['subjects'] = $subjects;
//		file_put_contents("debug-getteachers.txt",PHP_EOL."debug:".PHP_EOL.":".serialize($teachers).PHP_EOL,FILE_APPEND);//写调试到TXT
//		file_put_contents("debug-getteachers.txt",PHP_EOL."debug:".PHP_EOL.":".$teacherStr_subject.PHP_EOL,FILE_APPEND);//写调试到TXT
		
		
		//重新组织
		//最外部是科目，subjictId,subjectName,teacherCount
		//每个科目下是本科目的教师，teacherId,teacherName
		//其它，是未分配科目的教师，teacherId,teacherName
		
		$end = array();
		foreach($subjects as $k=>$v){
			$teacher_list = array();
			$teacherCount = 0;
			foreach($teachers as $kk=>$vv){
				//判断该老师的subjectId是一个数字，还是逗号分隔的数字
				if ( strstr($vv['subjectId'],',') ){
					$tmpArray = explode(",",$vv['subjectId']);
					if (in_array($v['id'],$tmpArray)){
						$teacher_list[] = $vv;
						$teacherCount ++;
					}
					//echo $vv['subjectId'].'---';
					//var_dump($tmpArray);
				}elseif(!empty($vv['subjectId'])){
					if ($v['id']==$vv['subjectId']){
						$teacher_list[] = $vv;
						$teacherCount ++;
					}
					
				}
				
			}
			$subject_one = array("subjectId"=>$v['id'],"subjectName"=>$v['name'],"teacherCount"=>$teacherCount,"teacherList"=>$teacher_list);
			$end[] = $subject_one;
		}
		
		//不属任何一科目的教师
		$subject_other = array();//其它（作为一个科目）
		$teacher_list_other = array();//归入其它的全部教师
		$teacherCountOther = 0;
		foreach($teachers as $kk=>$vv){
			if (empty($vv['subjectId'])){
				$teacher_list_other[] = $vv;
				$teacherCountOther ++;
			}
		}
		$subject_other = array("subjectId"=>0,"subjectName"=>"其它","teacherCount"=>$teacherCountOther,"teacherList"=>$teacher_list_other);
		$end[] = $subject_other;
	//	header("Content-type:text/html;charset=utf-8");
		//var_dump($end);
		
		//规范的显示一下用于调试检查
		/*
		foreach($end as $k=>$v){
			echo "<br>".$v['subjectName']." ".$v['teacherCount']."<br>";
			foreach ($v['teacherList'] as $kk=>$vv){
				//var_dump($vv);
				echo $vv['name'].",";
			}
				
		}
		*/
		$result = array("stat"=>"1","data"=>$end);
		echo json_encode($result);exit;
		
		//
	}		
	
	/*
	 * ajax提交作业
	*/
	public function ajaxHomeworkSubmit(){
		$id = I("request.tid",0,"int");
		$homeworkId = I("request.homeworkId",0,"int"); 
		$evaluation = trim(I("request.evaluation"));
		$comment = trim(I("request.content"));
		
		//file_put_contents("ajaxHomeworkSubmit.txt",PHP_EOL."-".$id."-".$homeworkId."-".$evaluation."-".$comment.PHP_EOL);//写调试到TXT
		
		$model = D("SchoolHomeworkSubmit");

		
		$datas = array();
		$datas['id'] = $id;
		$datas['comment'] = $comment;
		$datas['evaluation'] = $evaluation;
		$datas['isSubmit'] = 1;
		
		$result = $model->save($datas);
		if ($result){
			echo json_encode(array("stat"=>"1","data"=>"更新成功"));
		}else{
			echo json_encode(array("stat"=>"0","data"=>"更新失败"));
		}
		
	}
	
	/**
	 * 删除投票选项
	 * 这儿实际上需要防止非法请求及直接输入id删除
	*/
	public function delVoteOption(){
		$modelOpt = D("SchoolVoteOption");
		$optid = I("request.optid",0,"int");
		if ($optid){
			$result = $modelOpt->where("id=".$optid)->delete();
		}
		echo json_encode(array("stat"=>"1","data"=>"删除成功"));
		
	}
	
	
	/**
	 * 删除投票选项
	 * 这儿实际上需要防止非法请求及直接输入id删除
	*/
	public function editVoteOption(){
		$modelOpt = D("SchoolVoteOption");
		$optid = I("request.optid",0,"int");
		$optValue = trim(I("request.optValue"));
		if ($optid){
			$data = array();
		//	$data['id'] = $optid;
			$data['content'] = $optValue;
			$result = $modelOpt->where("id=".$optid)->save($data);
		}else{
			//增加新记录	
		}
		echo json_encode(array("stat"=>"1","data"=>"更新成功"));
		
	}
	
	/**
	 * 增加投票项
	*/
	public function addVoteOption(){
		$modelOpt = D("SchoolVoteOption");
		$voteId = I("request.voteid",0,"int");
		if ($voteId){
			$data = array();
			$data['voteId'] = $voteId;
			$data['content'] = $optValue;
			$result = $modelOpt->data($data)->add();
			
			//添加投票项
		}else{
			//增加新记录	
		}
		echo json_encode(array("stat"=>"1","data"=>$result));
		
	}
		
	/**
	 * 资源对话框
	*/		

	//
	public function getResManager() {
		$type = I('post.type');
		$dirClassId = I('post.classid');
		$return = array();
	//	$where = array('status'=>1);
		$where['filetype'] = $type;
		$where['belong_dirclassid'] = $dirClassId;
		
		$resList = M('ResmanagerFiles')->field(array('id','filename','belong_dirclassid','filetype','filepath'))->where($where)->select();
		
		if (!empty($resList)) {
			foreach ($resList as &$file) {
				$file['filename'] = gbk2utf8($file['filename']);
			}
			$return = array('stat'=>'1', 'data'=>$resList);
		} else {
			$return = array('stat'=>'0', 'msg'=>'没有获取到可用的资源！请检查您的素材库……');
		}
		
		echo json_encode($return);
	}



	public function selectResToUse(){
		$type = I('post.type');
		$porgId = I('post.porg_id');
		$tempId = I('post.temp_id');
		$resId = I('post.res_id', 0, 'int');
		$return = array();
		
		if (!$tempId) {
			die(json_encode(array('stat'=>'0', 'msg'=>'操作失败！[原因]：数据错误！')));
		}
		
		if (!$resId) {
			die(json_encode(array('stat'=>'0', 'msg'=>'操作失败！[原因]：数据错误！')));
		}
		
		$res = M('ResmanagerFiles')->field(array('id','classid','filename','filetype','filepath', 'file_desc'))->where(array('id'=>$resId))->find();
		
		if (!$res) {
			die(json_encode(array('stat'=>'0', 'msg'=>'操作失败！[原因]：数据错误！')));
		}
		
		$sourcePath = rtrim(C('UPLOAD_COMM_PATH'), '/') . '/sucai/' .utf8ToGbk($res['filepath']);
		if (!file_exists($sourcePath)) {
			die(json_encode(array('stat'=>'0', 'msg'=>'操作失败！[原因]：文件不存在！')));
		}
		

			$data['tmp_article_classid'] = $tempId;
			$data['res_id'] = $lastestID;
			$data['filename'] = $res['filename'];
			$data['filetype'] = $res['filetype'];
			$data['filepath'] = $dbSavePath . $basename;
			$data['url'] = '/' . $destPath . $basename;
			$data['act'] = $type;
			$data['ctime'] = time();

			/* if ($re === false) {
				$return = array('stat'=>'0', 'msg'=>'操作失败！请刷新页面重试……');
			} */
			$return = array('stat'=>'1', 'data'=>$data);
			echo json_encode($return);

		
	}


	public function endpoints(){
		$et = trim(I("request.et"));
		$groupClassId = trim(I("request.groupClassId"));
		$keyboard =  trim(I("request.keyboard"));

		$map = array();
		if (!empty($keyboard)){
			$map['touchEndPointName'] = array('like', '%' . $keyboard . '%');
		}

		if (!empty($groupClassId)){
			$map['touchEndPoint_GroupClassId']=$groupClassId;
		}

		$endpointModel = D('Endpoint');
		$endpoint_list = $endpointModel->where($map)->order("tid DESC")->select();//var_dump($endpoint_list);
		$this->assign('endpoint_list', $endpoint_list);
			
			$return = array('stat'=>'1', 'data'=>$endpoint_list,'debug'=>$groupClassId);
			echo json_encode($return);
	}


	/**
	 * zjh add
	 * 评比结果图标，新增时不入库，修改时删除旧图片并更新记录
	*/
	public function uploadify_rate_icon() {
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
			
			$model = D("SchoolRateResult");
			
			//先取到原图片地址
			$sch_datas = $model->where("Id=".$dataID)->field("icon")->find();
			
			if ($sch_datas){
				$old_pic_path = $sch_datas['icon'];
			//	file_put_contents("debug-999.txt",PHP_EOL."old_pic_path:".$old_pic_path.PHP_EOL,FILE_APPEND);//写调试到TXT
				//$upRootPath = rtrim(C('UPLOAD_COMM_PATH'), '/') . '/';
				@unlink($old_pic_path);//删除旧图片
				if (trim($old_pic_path) != '' && is_file($old_pic_path)) {
								
				}else{
					;
				}					
			}
			
			if ($dataID){
				$datas = array();
				$datas['Id'] = $dataID;
				$datas['icon'] = str_replace( '\\', '/',$dbSavePath);
				$result = $model->save($datas);
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
		echo json_encode($reInfo);
	}		


	public function boardPhotosUploadify() {
	
		// 参数过滤
		$name = "";
		$banjiId = I('post.banjiId', 0, 'int');
		$boardId = I('post.boardId', 0, 'int');
		$isMyUpRoot = I('post.isMyUpRoot', 0, 'int');
		$folderName = trim(I('post.folderName'));
		
		// 实例化框架上传类
		import('ORG.Net.UploadFile');
		$uphandle = new UploadFile();
		
		// 设置上传文件允许的格式，以及文件大小
		$allowExts = array();
		$maxSize = 0;
		$allowExts = C('UPIMG_ALLOW_TYPES');
		$uphandle->allowExts = $allowExts;
		$uphandle->maxSize = C('UPIMAGE_MAX_SIZE');
		
		
		// 处理上传文件保存路径  && 处理文件保存名称
		$uploadRoot = '';
		if ($isMyUpRoot) {
			$uploadRoot = C('UPLOAD_COMM_PATH');
		} elseif ($dataType == 'soft') {
			$uploadRoot = C('a_main_upfile');
		} else {
			$uploadRoot = C('UPLOAD_ROOT_PATH');
		}
		
		$uphandle->savePath = rtrim($uploadRoot, '/') . '/';
		$savePath = '';
		if ($folderName != '') {
			$savePath = trim($folderName, '/') . '/';
			$uphandle->savePath .= $savePath;
		//	$uphandle->saveRule = '';//开放此句，则为原文件名，禁用时为随机文件名
			$uphandle->uploadReplace = false;
		}
		$uphandle->uploadReplace = true;

		//调用类上传方法
		$upResult = $uphandle->uploadOne($_FILES['myUpfile']);
		
		if(!$upResult) {
			$reInfo = array(
					'stat'	=>	'0',
					'msg'	=>	$uphandle->getErrorMsg()
			);
		} else {
			$upResult = $upResult[0];
			$dbSavePath = $savePath . $upResult['savename'];//utf82gbk实际什么也不做


	//		if ($boardId) {
	
					// 将本次上传的文件信息写入数据库媒体数据表
					$boardPhotoModel = D("SchoolBoardPhoto"); 
					$data = array();
					$data['name'] = "";
					$data['fileName'] = $uploadRoot . $savePath . $upResult['savename'];//$upResult['savename'];
					$data['boardId'] = $boardId;
					$data['banjiId'] = $banjiId;
					$resResult = $boardPhotoModel->add($data);
					//$mediaLibResult = $mediaLibModel->execute("insert into TB_MediaLib (filepath, type, resourceid) values ('" . $data['filepath'] . "', '" . $ext . "' , '" . $data['resourceid'] . "')");
					$reInfo = array(
							'stat'		=> '1',
							'id'		=>	$resResult,
							'url'		=>	'/' . $uploadRoot . $savePath . $upResult['savename'],
							'savePath'	=>	$dbSavePath,
							'pic'		=>	$upResult['savename'],
							'original'	=>	$upResult['name'],
							'size'		=>	$upResult['size'],
					);	
	
	//		}
	
		}
		echo json_encode($reInfo);
	}


	/*
	 * 删除板报照片
	*/
	public function delBoardPhoto(){
		$id = I("request.rid",0,"int");
		if (!$id){
			exit(json_encode(array('stat'=>0, 'msg'=>'id无效')));	
		}
		
		
		//检测班级
		
		
		//
		$boardPhotoModel = D("SchoolBoardPhoto"); 
		$datas_photo = $boardPhotoModel->where("id=".$id)->find();
		if ($datas_photo){
			$filepath = C('UPLOAD_COMM_PATH')."board/".$datas_photo['banjiId']."/".$datas_photo['fileName'];
			if (is_file($filepath)){
				@unlink($filepath);//删除旧图片
				
			}
			$datas_photo = $boardPhotoModel->where("id=".$id)->delete();
			
			exit(json_encode(array('stat'=>1, 'msg'=>$filepath)));		
		}
		
	}

	////////////////////////////////////////

		
	/**
	 * 检测用户名重复
	*/
	public function checkUserNameRepeat(){
		$account = trim(I("post.account"));
		$uid = I("post.uid",0,"int");
		if (empty($account)){
			die('用户名不得为空！');
		}
		
		$userModel = D("User");
		
		$map = array();
		if ($uid){
			$map['id'] = array("NEQ",$uid);	//不等于
			$map['account'] = $account;
		}else{
			$map['account'] = $account;
		}
		
		
		$result = $userModel->where($map)->find();
		if ($result){
			die('已有此用户名存在');
		}else{
			die('1');
		}
	}
	/**
	 * 获取本班科目教师对应关系，设置课程表用，如editLessionTable.html中
	*/	
	public function getSubjectTeacherId(){
		$btsModel = D("SchoolBanjiSubjectTeacher");
		$id = I("request.rid",0,"intval");
		if ($id){
			$datas = $btsModel->where(array("id"=>$id))->find();file_put_contents("debug.txt",PHP_EOL."debug:".PHP_EOL.serialize($datas).PHP_EOL,FILE_APPEND);//
			if ($datas){
				exit(json_encode(array('stat'=>1, 'msg'=>'查询成功','data'=>$datas)));	
			}else{
				exit(json_encode(array('stat'=>0, 'msg'=>'无对应记录')));
			}
		}else{
			exit(json_encode(array('stat'=>0, 'msg'=>'无对应记录...')));
		}
	}
	
	public function ajax_findTeacher(){
		$userModel = M('Users');
		$user_type = I('post.user_type');
		$rid = I('post.rid');
		$uid = I('post.uid');
		//检测学生重复/老师重复
		if($uid){
			// uid有值，说明是编辑
			$result = $userModel->where("type='".$user_type."' and referId=$rid and id != $uid")->find();
		}else{
			$result = $userModel->where("type='".$user_type."' and referId=$rid")->find();
		}
		die($result);
	}

	/**
	 * 根据班级或班级和科目过滤教师，如果科目未指定时，则直接返回仅根据班级过滤后的教师列表，班级科目均指定时，返回二者共同过滤后的教师。
	 * 后期修改，班级可能为空，班级为空时，显示所有教师 2016-11-26
	 * @return [json] [id和name字段的json对象]
	 */
	public function ajax_filtTeacherThroughSubjectOrBanji(){
		$banjiId = I('post.banjiId');
		$subjectId = I('post.subjectId');
		$teacherSubjectModel = D('SchoolTeacherSubject');
		$teacherIdsS = $teacherSubjectModel->where("subjectId=$subjectId")->getField('teacherId', true);
		$teacherBanjiModel = D('SchoolTeacherBanji');
		if($banjiId){
			$teacherIdsB = $teacherBanjiModel->where("banjiId=$banjiId")->getField('teacherId', true);
		}else{
			$teacherIdsB = $teacherBanjiModel->getField('teacherId', true);
		}
		if(!$subjectId){
			$teacherIds = $teacherIdsB;
		}else{
			foreach($teacherIdsS as $value){
				if(in_array($value, $teacherIdsB)){
					$teacherIds[] = $value;
				}
			}
		}
		if(!$teacherIds){
			die(json_encode(0));
		}
		$teacherIds = join(',', $teacherIds);
		$teachersModel = D('SchoolTeachers');
		$teacherList = $teachersModel->where("id in ($teacherIds)")->getField('id, name', true);
		foreach($teacherList as $k => $v){
			$temp[id] = $k;
			$temp[name] = $v;
			$teachers[] = $temp;
		}
		die(json_encode($teachers));
	}
		
	/**
	 * 为了替代安卓组的跑马灯而生
	 * 具体实现了班级通知的滚动，每25秒切换一条通知，规定宽度的区域，标题横向过长则滚动，内容过长则向下滚动，这里特殊处理，返回数据格式分两种{json, array}
	 * @return [页面+json] [配置信息使用页面分配数据，主体数据采用json格式，前端js拼装控制]
	 */
	public function carouselNotice(){
		$banjiId = $_GET[banjiId] ? : 'all';
		$currentTime = date('Y-m-d', time());
		if($banjiId == 'all'){
			$this->notices = json_encode(D('SchoolNotices')->where("banjiId is null and endTime >= '$currentTime' or banjiId='' and endTime >= '$currentTime'")->order('id desc')->select());
			$this->before = '[全校]';
		}else{
			$this->notices = json_encode(D('SchoolNotices')->where("banjiId like '%,$banjiId,%' and endTime >= '$currentTime'")->order('id desc')->select());
			$this->before = '';
		}

		$itemclassid = $_GET[itemclassid] ? : '3c855553-8169-4b7a-9a7a-84b7fbf8683b';
		$model = new Model();
		$configuration = $model->table("TB_sch_Widget_Box_Notices_Data")->where("itemclassid='$itemclassid'")->field('id, changetimespan, showtype, titleFontsize, titleForeground, ContentFontsize, ContentForeground')->find(); 
		$configuration[titleForeground] = '#'.substr($configuration[titleForeground], 3);
		$configuration[ContentForeground] = '#'.substr($configuration[ContentForeground], 3);
		$configuration[noticeWidth] = $_GET[noticeWidth] ? : '478px';
		$configuration[noticeHeight] = $_GET[noticeHeight] ? : '294px';
		$this->configuration = $configuration;
		$this->display();
	}

	/**
	 * ajax修改默认相册，思路很简单，idDefault字段来表示是否默认，将旧的置0，新的置1
	 * @return [string] [1成功，0失败]
	 */
	public function ajaxChangeDefaultAlbum(){
		$originId = $_POST[originId];
		$id = $_POST[id];
		$originAlbum = M()->table('TB_Sch_Albums')->where("id=$originId")->setField('isDefault', 0);
		$newAlbum = M()->table('TB_Sch_Albums')->where("id=$id")->setField('isDefault', 1);
		if($originAlbum && $newAlbum){
			echo '1';
		}else{
			echo '0';
		}
	}
}