<?php
/**
 * 学校前端接口，不需登陆验证
*/
class SchoolHomeAction extends Action {
	
    /**
     * 环境，ajax最上一条
	 * 参数：banjiId（班级ID）
     */
    public function ajax_row_environment() {    
		$banjiId = I("request.banjiId",0,"int");    
		$model = D("SchoolEnvironment");
		$datas = $model->where("banjiId = ".$banjiId)->order("Id DESC")->find();
		
		//header("Content-type: text/html; charset=utf-8");
		header("Access-Control-Allow-Origin: *");//允许跨域访问
		
		
		if (!$banjiId){
			$out = json_encode(array("stat"=>0,"data"=>"班级编号（banjiId）未提供"));
			echo $out;
			exit;
		}
		
		$out = array();
		if ($datas){
			$out = json_encode(array("stat"=>"1","data"=>$datas));
		}else{
			$error = "未查到此班级数据（banjiId=".$banjiId."）";
			$out = json_encode(array("stat"=>0,"data"=>$error));	
		}
		echo $out;
        
    }	
	
	/**
	 * 某班级签到情况
	 * 参数：banjiId（班级ID）
	 * 地址：
	*/
	public function ajax_student_signin_count(){
		$debug = I("request.debug",0,"int");  
		
		if ($debug){
			header("Content-type: text/html; charset=utf-8");
		}else{
			header("Access-Control-Allow-Origin: *");//允许跨域访问
		}
		
		$banjiId = I("request.banjiId",0,"int");    
		$studentModel = D("SchoolStudents");	
		$stuSignModel = D("SchoolStudentSignIn");	
		
		if (!$banjiId){
			$out = json_encode(array("stat"=>0,"data"=>"班级编号（banjiId）未提供"));
			echo $out;
			exit;
		}		
		
		/*
		$start = date("Y-m-d H:i:s",mktime(0,0,0,date("m"),date("d"),date("Y")));//2016-05-01 00:00:00
		$end = date("Y-m-d H:i:s",mktime(23,59,59,date("m"),date("d"),date("Y")));//2016-05-01 23:59:59
		echo $start."<br>";
		echo $end."<br>";
		*/
		
		
		//统计本班学生总数
		$totalStudent = $studentModel->where("banjiId=".$banjiId)->count();
		//echo "应到：".$totalStudent."<br>";
		
		//本班所有学生id，用逗号分隔
		$banji_all_student_arr = array();
		$banji_all_student_str = "";
		$datas = array();
		$datas = $studentModel->where("banjiId=".$banjiId)->field("id,name")->select();
		//var_dump($datas);
		if ($datas){
			foreach($datas as $k=>$v){
				$banji_all_student_arr[] = $v['id'];
			}

			if ($banji_all_student_arr){
				$banji_all_student_str = implode(",",$banji_all_student_arr);
				if ($debug){
					echo "<br>本班学生：".$banji_all_student_str;
				}	
			}

			if ($debug){
				var_dump($banji_all_student_str);
			}	
		
		}
		
		//统计本班今日签到总数
		$total_today_sign = 0;
		
		
	
		$condition = "select COUNT(*) as totalnum from TB_Sch_StudentSignIn where StudentId in ($banji_all_student_str) and DateDiff(dd,DateTime,getdate())=0";//这儿不对，要加上班级
		
		$total_today_sign = $studentModel->query($condition);
		
		//$db = Db::getInstance(C('RBAC_DB_DSN'));		//$total_today_sign = $db->query($condition);

		//echo "今日签到：".$total_today_sign[0]['totalnum']."人<br>";
		
		$truenum = $total_today_sign[0]['totalnum'];
		$datas = array("totalnum"=>"$totalStudent","truenum"=>"$truenum");
		
		$out = array();
		$out = json_encode(array("stat"=>"1","data"=>$datas));
		echo $out;exit;
	
	}
	
	/**
	 * 某班级当前课程信息和下节课程信息
	 * 参数：banjiId（班级ID）
	 http://school.touch.com/SchoolHome/ajax_banji_lession/banjiId/1/debug/1
	*/
	public function ajax_banji_lession(){
		$debug = I("request.debug",0,"int");  
		
		if ($debug){
			header("Content-type: text/html; charset=utf-8");
		}else{
			header("Access-Control-Allow-Origin: *");//允许跨域访问
		}
		
		$banjiId = I("request.banjiId",0,"int");    
		
		//获取当前时间，时：分：秒
		$the_time = date("H:i:s",mktime());//H为24小时格式
	//	$the_time = date("Y-m-d H:i:s",mktime());
		//$the_time = "10:13:03";
		//echo $the_time;
		
		//星期几
		$weekday = date("w");//获取数字星期几，比如123，注意0是星期
		
		  
		  

		//唯一请求参数检测		
		if (!$banjiId){
			echo json_encode(array("stat"=>0,"data"=>"班级编号（banjiId）未提供"));
			exit;
		}
		
		$banjiModel = D('SchoolBanji');
		$teacherModel = D('SchoolTeachers');
		$studentModel = D('SchoolStudents');
		$lessionModel = D('SchoolLessions');
		
		//初始化最终输出变量数组
		$out = array();
		$out_data = array();
		$out_data['banji_id'] = '';
		$out_data['banji_name'] = '';
		$out_data['banji_banzhuren'] = '';
		$out_data['banji_banzhan'] = '';
		
		$out_data['lession_number_curr'] = 0;//当前第几节		
		$out_data['lession_name_curr'] = '';
		$out_data['teacher_name_curr'] = '';
		$out_data['teacher_photo_curr'] = "";
		
		$out_data['lession_number_next'] = 0;//下节为第几节
		$out_data['lession_name_next'] = '';
		$out_data['teacher_name_next'] = '';	
		$out_data['teacher_photo_next'] = "";
	

		$bangi_datas = $banjiModel->where("id=".$banjiId)->find();
		$out_data['banji_name'] = $bangi_datas['name'];
		
		//获取全天上课时间，并判断当前是第几节课
		$lession_time_model = D("SchoolLessionTime");
		$today_lession_time_datas = $lession_time_model->order("starttime ASC")->select();
//	var_dump($today_lession_time_datas);
		foreach($today_lession_time_datas as $k=>$v){
//	echo $v['number'].'--'.$v['name']."--".$v['starttime'].'--'.$v['endtime']."<br>";
			if (($the_time > $v['starttime']) and ($the_time <$v['endtime']) ){
				if ($v['type']==0){

					$out_data['lession_name_curr'] = '当前课程';
					$out_data['teacher_name_curr'] = '当前教师';
					
					//当前课程
					$str = "select * FROM TB_Sch_Lessions WHERE banjiId= $banjiId AND weekday = $weekday AND ( '$the_time' > startTime ) AND  ( '$the_time' < endTime )";
					$curr_lession_datas = $lessionModel->query($str);
					$out_data['lession_name_curr'] = $curr_lession_datas[0]['name'];//当前课程
					$curr_lession_datas['lessionNumber'] = $curr_lession_datas[0]['lessionNumber'];
//	var_dump($curr_lession_datas);
					
					$teacherId = $curr_lession_datas[0]['teacherId'];
					
					$curr_teacher_datas = $teacherModel->where("id=".$teacherId)->field("name,imagePath")->find();
					$out_data['teacher_name_curr'] = $curr_teacher_datas['name'];//当前教师
					$out_data['teacher_photo_curr'] = $curr_teacher_datas['imagePath'];//当前教师照片
					$out_data['lession_number_curr'] = $v['number'];//当前第几节课
					$out_data['lession_number_next'] = $v['number'] + 1;//当前第几节课
				
					
					//下节课
					if ($curr_lession_datas['lessionNumber'] < 8){
						$lessionNumber_next = $curr_lession_datas['lessionNumber']+1;
						
						//echo "<br><br>".$lessionNumber_next."<br><br>";
						
						$out_data['lession_name_next'] = '下节课程';
						$out_data['teacher_name_next'] = '下节教师';
						
						$str_next = "select * FROM TB_Sch_Lessions WHERE banjiId= $banjiId AND weekday = $weekday AND lessionNumber = $lessionNumber_next";
						$next_lession_datas = $lessionModel->query($str_next);
						//var_dump($next_lession_datas);
						$out_data['lession_name_next'] = $next_lession_datas[0]['name'];//下节课程
						$teacherId_next = intval($next_lession_datas[0]['teacherId']);
						
						$next_teacher_datas = $teacherModel->where("id=".$teacherId_next)->field("name,imagePath")->find();
						$out_data['teacher_name_next'] = $next_teacher_datas['name'];//下节教师
						$out_data['teacher_photo_next'] = $next_teacher_datas['imagePath'];//下节教师照片

					}
				}else{
					//echo $v['name']."<br>";
					$lessionNumber_next = $v['number'];
//		echo "下一节课为第".$v['lessionNumber_next']."节课";
					
					//课间或课前，只有下节课信息
					if ($v['number']){
						$str_next = "select * FROM TB_Sch_Lessions WHERE banjiId= $banjiId AND weekday = $weekday AND lessionNumber = $lessionNumber_next";
						$next_lession_datas = $lessionModel->query($str_next);
						$out_data['lession_name_next'] = $next_lession_datas[0]['name'];//下节课程
						//var_dump($next_lession_datas);
						$teacherId_next = intval($next_lession_datas[0]['teacherId']);
						
						$next_teaccher_datas = $teacherModel->where("id=".$teacherId_next)->field("name")->find();
						$out_data['teacher_name_next'] = $next_teaccher_datas['name'];//下节教师
						
						if ($debug){
							echo "<br>当前：".$v['name'];//课前或课间
							echo "<br>下节课程：第".$lessionNumber_next."节课";
							echo "<br>上课教师：".$teacherId_next."-".$out_data['teacher_name_next'];
						}
					}
				}
				
				if ($debug){
					
					echo "<br>.........................................";
					echo "<br>班级ID：".$out_data['banji_id'];
					echo "<br>班级名称：".$out_data['banji_name'];
					echo "<br>";
					echo "<br>.........................................";	
					echo "<br>时间：".$the_time;//exit;//11:30:50				
					echo "<br>星期：".$weekday;
					echo "<br>";
					echo "<br>";
					echo "<br>当前第几节：".$out_data['lession_number_curr'];
					echo "<br>当前课程：".$out_data['lession_name_curr'];
					echo "<br>当前教师：".$out_data['teacher_name_curr'];
					echo "<br>当前教师照片：".$out_data['teacher_photo_curr'];
					echo "<br><img src='".$out_data['teacher_photo_curr']."' height=50 >";
					echo "<br>";
					echo "<br>.........................................";	
					echo "<br>下节第几节：".$out_data['lession_number_next'];				
					echo "<br>下节课程：".$out_data['lession_name_next'];
					echo "<br>下节教师：".$out_data['teacher_name_next'];	
					echo "<br>下节教师照片：".$out_data['teacher_photo_next'];
					echo "<br><img src='".$out_data['teacher_photo_next']."'  height=50 >";
					echo "<br><br>";	
				}
				
				if ($out_data){
					echo json_encode(array("stat"=>"1","data"=>$out_data));
				} else {
					echo json_encode(array("stat"=>"0","data"=>"错误"));
				}
			}
	
		}

	}
	
	/**
	 * 某班级评比信息
	 * 参数：banjiId（班级ID）
	 http://school.touch.com/SchoolHome/ajax_banji_rate/banjiId/1
	*/
	public function ajax_banji_rate(){
		$debug = I("request.debug",0,"int");  
		
		if ($debug){
			header("Content-type: text/html; charset=utf-8");
		}else{
			header("Access-Control-Allow-Origin: *");//允许跨域访问
		}
		
		$banjiId = I("request.banjiId",0,"int");    
		$rateSubjectModel = D("SchoolRateSubject");	//评比项目
		$rateResultModel = D("SchoolRateResult");	//评比结果
		
		if (!$banjiId){
			$out = json_encode(array("stat"=>0,"data"=>"班级编号（banjiId）未提供"));
			echo $out;
			exit;
		}
		
		//初始化结果数组
		$data = array();
		$data['icon'] = "";//评比图标
		$data['title'] = "";//评比名称
		$data['order'] = 0;//名次
		
		//查询本班最后一条评比结果
		$subject_datas = array();
		$result_datas = array();
		
		$subject_datas = $rateSubjectModel->order("Id DESC")->find();
		//var_dump($subject_datas);
		
		if ($subject_datas){
			//评比名称
			$data['banjiId'] = $banjiId;
			$data['id'] = $subject_datas['Id'];
			$data['title'] = $subject_datas['name'];
			$data['icon'] =  html_entity_decode($subject_datas['icon']);//斜杠处理
			
			if ($debug){
				echo "<br>评比项目ID：".$data['id'];
				echo "<br>评比项目名称：".$data['title'];
				echo "<br>评比项目图标：".$data['icon'];
				echo "<br><img src='".html_entity_decode($data['icon'])."' height='20'>";
				echo "<br><br>";
			}
			
			//查找本评比科目下的数据
			$result_datas = $rateResultModel->where("subjectId=".intval($subject_datas['Id']))->order("score DESC")->select();//必须按成绩从大到小排序，计算名次，暂不考虑分数一样
			
			//var_dump($result_datas);
			foreach ($result_datas as $k=>$v){
				if ($debug){
					echo "<br>记录序号：".$v['Id']."-班级ID：".$v['banjiId']."-得分：".$v['score'];
				}
				
				if ($banjiId == $v['banjiId']){
					if ($debug){
						echo "&nbsp;&nbsp;&nbsp;&nbsp;当前班级排名为第 ".($k + 1)." 名";
					}
					$data['order'] = $k + 1;
				}
			}
			
			echo json_encode(array("stat"=>"1","data"=>$data));
			
		}else{
			
		}
	}
	
	/**
	 * 某班级当前课程信息和下节课程信息
	 * 参数：banjiId（班级ID）
	 http://school.touch.com/SchoolHome/ajax_banji_infor/banjiId/1
	*/
	public function ajax_banji_infor(){
		$debug = I("request.debug",0,"int");  
		
		if ($debug){
			header("Content-type: text/html; charset=utf-8");
		}else{
			header("Access-Control-Allow-Origin: *");//允许跨域访问
		}
		
		$banjiId = I("request.banjiId",0,"int");    
		  

		//唯一请求参数检测		
		if (!$banjiId){
			echo json_encode(array("stat"=>0,"data"=>"班级编号（banjiId）未提供"));
			exit;
		}
		
		$banjiModel = D('SchoolBanji');
		$teacherModel = D('SchoolTeachers');
		$studentModel = D('SchoolStudents');
		
		//初始化最终输出变量数组
		$out = array();
		$out_data = array();
		$out_data['banji_id'] = '';
		$out_data['banji_name'] = '';
		$out_data['banji_banzhuren'] = '';
		$out_data['banji_banzhan'] = '';
	
		$datas = array();
		$datas = $banjiModel->where("id=".$banjiId)->find();
		if ($datas){
			$out_data['banji_id'] = $datas['id'];
			$out_data['banji_name'] = $datas['name'];
			
			$teacher_datas = $teacherModel->where("id=".intval($datas['banzhurenId']))->find();
			if ($teacher_datas){
				$out_data['banji_banzhuren'] = $teacher_datas['name'];

			}
			
			$student_datas = $studentModel->where("id=".intval($datas['banzhanId']))->find();
			if ($student_datas){
				$out_data['banji_banzhan'] = $student_datas['name'];

			}
			
			if ($debug){
				echo "<br>班级ID：".$out_data['banji_id'];
				echo "<br>班级名称：".$out_data['banji_name'];
				echo "<br><br>班主任：".$out_data['banji_banzhuren'];
				echo "<br>班长：".$out_data['banji_banzhan'];
				echo "<br><br>";
			}
							
			echo json_encode(array("stat"=>"1","data"=>$out_data));
			
		}else{
			$error = "未查到此班级数据（banjiId=".$banjiId."）";
			$out = json_encode(array("stat"=>0,"data"=>$error));	
		}
	
	
	}
	
	
	/**
	 * 通知
	 * 参数：banjiId（班级ID）
	 http://school.touch.com/SchoolHome/ajax_banji_notice/banjiId/1
	*/
	public function ajax_banji_notice(){
		$debug = I("request.debug",0,"int");  
		
		if ($debug){
			header("Content-type: text/html; charset=utf-8");
		}else{
			header("Access-Control-Allow-Origin: *");//允许跨域访问
		}
		
		$banjiId = I("request.banjiId",0,"int");    
		  

		//唯一请求参数检测		
		if (!$banjiId){
			echo json_encode(array("stat"=>0,"data"=>"班级编号（banjiId）未提供"));
			exit;
		}
		
		//输出信息
		$out_datas = array();
		$out_datas['title'] = "";
		$out_datas['content'] = "";
		
		$datas = array();
		
		$noticeModel = D('SchoolNotices');		
		$datas = $noticeModel->order("id DESC")->limit(1)->find();
		if ($datas){
			$out_data['title'] = $datas['title'];
			$out_data['content'] = $datas['content'];
			
			if ($debug){
				echo "<br>标题：".$out_data['title'];
				echo "<br>公告：".$out_data['content'];
				echo "<br><br><br><br>";
				var_dump($out_data);
				echo "<br><br><br><br>";
			}
			
			
			echo json_encode(array("stat"=>"1","data"=>$out_data));
		}else{
			echo json_encode(array("stat"=>0,"data"=>"无通知公告信息"));	
		}
	}
	
	/**
	 * 百科，小知识
	*/
	/**
	 * 通知
	 * 参数：banjiId（班级ID）
	 http://school.touch.com/SchoolHome/ajax_banji_baike/banjiId/1
	*/
	public function ajax_banji_baike(){
		$debug = I("request.debug",0,"int");  
		
		if ($debug){
			header("Content-type: text/html; charset=utf-8");
		}else{
			header("Access-Control-Allow-Origin: *");//允许跨域访问
		}
		
		$banjiId = I("request.banjiId",0,"int");    
		  

		//唯一请求参数检测		
		if (!$banjiId){
			echo json_encode(array("stat"=>0,"data"=>"班级编号（banjiId）未提供"));
			exit;
		}
		
		$out_data = array();
		$out_data['title'] = "";
		$out_data['content'] = "";
		
		$model = new Model();
		$datas = $model->table("tb_reslib_baike")->order("id DESC")->limit(1)->find();
		if ($datas){
			$out_data['title'] = $datas['title'];
			$out_data['content'] = $datas['contents'];	
			
			if ($debug){
				echo "<br>标题：".$out_data['title'];
				echo "<br>公告：".$out_data['content'];
				echo "<br><br><br><br>";
				var_dump($out_data);
				echo "<br><br><br><br>";
			}
			
			echo json_encode(array("stat"=>"1","data"=>$out_data));
		}
		
	}
	
	/**
	 * 值日
	 * 参数：banjiId（班级ID）
	 http://school.touch.com/SchoolHome/ajax_banji_notice/banjiId/1
	*/	
	
	public function ajax_banji_zhiri(){
		$debug = I("request.debug",0,"int");  
		
		if ($debug){
			header("Content-type: text/html; charset=utf-8");
		}else{
			header("Access-Control-Allow-Origin: *");//允许跨域访问
		}
		
		$banjiId = I("request.banjiId",0,"int");    

		//星期几
		$weekday = date("w");//获取数字星期几，比如123，注意0是星期

		//唯一请求参数检测		
		if (!$banjiId){
			echo json_encode(array("stat"=>0,"data"=>"班级编号（banjiId）未提供"));
			exit;
		}
		
		$out_data = array();
		
		$dutyModel = D("SchoolDuty");
		$studentModel = D('SchoolStudents');
		
		$datas = array();
		$datas = $dutyModel->where("banjiId=".$banjiId." and dutyday = ".$weekday)->find();
		if ($datas){
			if ($datas['memberList']){
				$memberList = $datas['memberList'];

				$student_datas = $studentModel->where("id in ( $memberList )")->field("name")->select();
				//var_dump($student_datas);
				
				foreach($student_datas as $k=>$v){
					$out_data[] = $v['name'];
				}
				
				
				if ($debug){
					var_dump($out_data);
					echo "<br><br><br><br>";
				}
				
				echo json_encode(array("stat"=>"1","data"=>$out_data));
			}

		}else{
			echo json_encode(array("stat"=>0,"data"=>"无值日信息"));	
		}
			
	
	}
	
	
	/**
	 * 暂时不做这个了，暂时不做这个了，暂时不做这个了，暂时不做这个了，暂时不做这个了，未完成
	 * 某考场的考试安排
	 * 参数：banjiId（班级ID）
	 * http://school.touch.com/SchoolHome/ajax_kaochang_infor/banjiId/1/debug/1
	*/
	public function ajax_kaochang_infor(){
		$debug = I("request.debug",0,"int");  
		
		if ($debug){
			header("Content-type: text/html; charset=utf-8");
		}else{
			header("Access-Control-Allow-Origin: *");//允许跨域访问
		}
		
		$banjiId = I("request.banjiId",0,"int");    
		
		//获取当前时间，时：分：秒
	//	$the_time = date("H:i:s",mktime());//H为24小时格式
		$the_time = date("Y-m-d H:i:s",mktime());
		//$the_time = "10:13:03";
		//echo $the_time;

		//唯一请求参数检测		
		if (!$banjiId){
			echo json_encode(array("stat"=>0,"data"=>"班级编号（banjiId）未提供"));
			exit;
		}
		
		$banjiModel = D('SchoolBanji');
		$roomModel = D('SchoolRooms');//教室
		$kcModel = D('SchoolKaochang');//考场
		$ksPlanModel = D('SchoolKaochangExamPlan');//考试安排
		$teacherModel = D('SchoolTeachers');
		$subjectModel = D('SchoolSubjects');		
		
		//根据班级ID找到教室Id,
		$banji_datas = $banjiModel->where("id=".$banjiId)->find();
		if ($banji_datas){
			if ($debug){
				//var_dump($banji_datas);
			}
		}else{
			echo json_encode(array("stat"=>0,"data"=>"无此班级"));
		}
		
		//根据教室ID，找考场记录
		$room_datas = $roomModel->where("banjiId=".$banjiId)->find();
		if ($room_datas){
			$roomId = $room_datas['id'];
			if ($debug){	
				//var_dump($roomId);
			}
		}else{
			echo json_encode(array("stat"=>0,"data"=>"有此班级，但未设置本班教室"));
		}
		
		//根据教室roomId，读取考场
		$kaochang_datas = $kcModel->where("roomId=".$roomId)->find();;
		if ($kaochang_datas){
			$kaochangId = $kaochang_datas['id'];
			if ($debug){	
				//var_dump($kaochangId);
			}
		}else{
			echo json_encode(array("stat"=>0,"data"=>"本班级教室未设置为考场"));
		}
				
		//根据考场记录ID，查找本考试的科目安排
		$ksplan_datas = $ksPlanModel->where("kaochangId=".$kaochangId)->field("Id,kaochangId,subjectId,population,description,teacherList,convert(VARCHAR(24),beginTime,120) as starttime,convert(VARCHAR(24),endTime,120) as endtime")->select();
		
		if ($ksplan_datas){
	//		$debug?var_dump($ksplan_datas):'';//调试
			foreach($ksplan_datas as $k=>$v){
				//重新组织数据
				
				//考试科目
				$subject_datas = $subjectModel->where("id=".intval($v['subjectId']))->find();
				if ($subject_datas){
					$ksplan_datas[$k]['subjectName']=$subject_datas['name'];
				}
				
			//	var_dump($v['teacherList']);
				
				//监考老师
				$query = "id in (".$v['teacherList'].")";
				$teacher_datas = $teacherModel->where($query)->select();
//				$debug?var_dump($teacher_datas):"";//调试
				
				$tmp_0 = array();
				$tmp_1 = array();
				
				foreach($teacher_datas as $k=>$v){
					$tmp_0[] = $v['name'];
				}
				//var_dump($tmp_0);
				
				$tmp_1 = implode("、",$tmp_0);//用逗号分隔
//				var_dump($tmp_1);
				$ksplan_datas[$k]['teacher_list'] = $tmp_1;
				
				if ($debug){
			//		echo "<br>Id=".$v['Id'].';subjectId='.$v['subjectId'].';subjectName='.$subject_datas['name'].";teacher=".$ksplan_datas[$k]['teacher_list'].';开始时间：'.$v['beginTime'];
				}
			}
		}
		
	}
	
	
	/**
	 * 班级倒计时
	 * 参数：banjiId（班级ID）
	 * http://school.touch.com/SchoolHome/ajax_kaochang_infor/banjiId/1/debug/1
	*/
	public function ajax_banji_timer(){
		$debug = I("request.debug",0,"int");  
		
		if ($debug){
			header("Content-type: text/html; charset=utf-8");
		}else{
			header("Access-Control-Allow-Origin: *");//允许跨域访问
		}
		
		$banjiId = I("request.banjiId",0,"int");    
		
		//获取当前时间，时：分：秒
	//	$the_time = date("H:i:s",mktime());//H为24小时格式
	//	$the_time = date("Y-m-d H:i:s",mktime());
		//$the_time = "10:13:03";
		//echo $the_time;

		//唯一请求参数检测		
		if (!$banjiId){
			echo json_encode(array("stat"=>0,"data"=>"班级编号（banjiId）未提供"));
			exit;
		}
		
		$timerModal = D("SchTiming");;	
		$datas = $timerModal->where("banjiId=".$banjiId)->find();
		
		
		if ($debug){
			echo "<br><br><br><br><br>";var_dump($datas);
		}
		
		if ($datas){
			echo json_encode(array("stat"=>"1","data"=>$datas));
		}else{
			echo json_encode(array("stat"=>0,"data"=>"无数据记录"));
		}
		

		
	
	}
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
	
}