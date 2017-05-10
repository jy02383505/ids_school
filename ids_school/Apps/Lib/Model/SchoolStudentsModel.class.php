<?php
/**
 * 
 * @author Skeam TJ
 *
 */
class SchoolStudentsModel extends Model {
	protected $trueTableName = 'TB_Sch_Students';
	
	public function getList($map){
		return $this->where($map)->select();
	}

	/**
	 * 功能：获取到某班的全部学生
	 * 返回值：逗号分隔的全班学生ID
	 * 用于：各处的IN查询
	 * 返回值格式：学生ID,学生ID,学生ID
	 * 返回值示例：1,2,4,5,6
	*/
	public function getAllStudentStr($banjiId=0){
		if (empty($banjiStr)){return 0;}
		
		$stu = "";//结果将是逗号分隔的学生ID
		$stu_arr = array();
		
		$map = array();
		$map['banjiId'] = array("EQ",$banjiId);//"1,2,3,4,5,6,7,8,9"
		$datas = $this->where($map)->field("id")->select();
		
		foreach($datas as $k=>$v){
			$stu_arr[] = $v['id'];
		}
		$stu = implode(",",$stu_arr);
		return $stu;
	}
	
	/*
	 * 获取到某一个或几个班级的全部学生ID字符串
	 * 参数：班级ID 或 逗号分隔的多个班级ID
	 * 返回值：逗号分隔的学生ID
	 * 调用示例：
	 
			//某一班级全部学生ID
			$banjiId = 1;
			$banjiId = "1,2,3";
			$model_stu = D("SchoolStudents");
			$students = $model_stu->getBanjiStudentIdStr($banjiId);
	 
	*/
	public function getBanjiStudentIdStr($banjiStr=""/*格式：班级ID,班级ID*/){
		if (empty($banjiStr)){
			return 0;
		}
		
		$stu = "";
		$stu_arr = array();
		
		$map = array();
		
		if (stripos($banjiStr,',')){
			//多个班
			$map['banjiId'] = array("IN",$banjiStr);//"1,2,3,4,5,6,7,8,9"
		}else{
			$banjiStr = intval($banjiStr);
			$map['banjiId'] = array("EQ",$banjiStr);//一个班
		}
		$datas = $this->where($map)->field("id")->select();
		
		foreach($datas as $k=>$v){
			$stu_arr[] = $v['id'];
		}
		$stu = implode(",",$stu_arr);
		return $stu;
		
	}	
	
	/**
	 * 获取到某一个或几个班级的全部学生ID数组
	 * 参数：班级ID 或 逗号分隔的多个班级ID
	 * 返回值：学生ID组成的数组
	 *　调用示例：
			//某一班级全部学生ID
			$banjiId = 1;
			$banjiId = "1,2,3";
			$model_stu = D("SchoolStudents");
			$students = $model_stu->getBanjiStudentIdArr($banjiId);	 
	*/
	public function getBanjiStudentIdArr($banjiStr=""/*格式：班级ID,班级ID*/){
		if (empty($banjiStr)){
			return 0;
		}
		
		$stu = "";
		$stu_arr = array();
		
		$map = array();
		
		if (stripos($banjiStr,',')){
			//多个班
			$map['banjiId'] = array("IN",$banjiStr);//"1,2,3,4,5,6,7,8,9"
		}else{
			$banjiStr = intval($banjiStr);
			$map['banjiId'] = array("EQ",$banjiStr);//一个班
		}
		$datas = $this->where($map)->field("id")->select();
		
		foreach($datas as $k=>$v){
			$stu_arr[] = $v['id'];
		}
		return $stu_arr;
		
	}	
	
	/**
	 * 指定学生Id，获取到学生姓名
	*/
	public function getStudentName($studentId=""){
		if (empty($studentId) || !$studentId){return false;}
		$studentModel = D('SchoolStudents');
		
		$map = array();
		$datas = array();
		
		if (strpos($studentId, ',')){
			$map['id'] = array("IN",$studentId);
			$datas = $studentModel->where($map)->field("name")->select();
			$tmp = array();
			foreach ($datas as $k=>$v){
				$tmp[] = $v['name'];
			}
			//var_dump($tmp);
			return implode("，",$tmp);
		}else{
			$map['id'] = array("EQ",$studentId);
			$datas = $studentModel->where($map)->field("name")->find();
			return $datas['name'];
		}
		
	}
	
	
}

