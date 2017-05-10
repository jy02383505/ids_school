<?php
/**
 * 
 * 班级模型
 * $SchoolBanjiModel = D('SchoolBanji');
 */
class SchoolBanjiModel extends Model {
	protected $trueTableName = 'TB_Sch_Banji';
	
	public function getList($map){
		return $this->where($map)->select();
	}
	
	public function getOneUUID($id=0){
		return $this->where("id=".$id)->field('uuid')->find();	
	}
	
	/**
	 * 功能：获取到年级的全部班级
	 * 返回值：逗号分隔的班级ID
	 * 用于：各处的IN查询
	 * 返回值格式：班级ID,班级ID,班级ID
	 * 返回值示例：1,2,4,5,6
	*/
	public function getAllBanjiStr($gradeId=0){
		if (empty($gradeId)){return 0;}
		
		$bj_str = "";//结果将是逗号分隔的学生ID
		$bj_arr = array();
		
		$map = array();
		$map['gradeId'] = array("EQ",$gradeId);//"1,2,3,4,5,6,7,8,9"
		$datas = $this->where($map)->field("id")->select();
		
		foreach($datas as $k=>$v){
			$bj_arr[] = $v['id'];
		}
		$bj_str = implode(",",$bj_arr);
		return $bj_str;
	}
	
	
	//获取一行记录
	public function getOne($id){
		return $this->where("id=".$id)->find();
	}
	
	//根据系列班级id获取系列教室id
	public function getRoomIdFromBanjiIds($banjiIdStr/*逗号分隔的*/){
		if (empty($banjiIdStr)){
			return false;	
		}
		if (strpos($banjiIdStr, ',')){
			;
		}
		$map_tmp = array();
		$map_tmp = array("IN",$banjiIdStr);
		$datas_tmp = $this->where($map_tmp)->field("roomId")->select();
		
	}
	
	/*
	 & 根据教室ID获取班级记录，只返回一行
	*/
	public function getBanjiOneRowFromRoomId($roomId){
		if (!$roomId){return false;}
		$mapTmp = array();
		$mapTmp['roomId'] = intval($roomId);
		$datasTmp = $this->where($mapTmp)->find();
		return $datasTmp;
	}
	
	/**
	 * 获得某一班级学生总数
	*/
	public function getStudentCountOfBanji($banjiId){
		if (!$banjiId){return false;}
		$stuModel = D('SchoolStudents');
		$stu_num = $stuModel->where("banjiId=".$banjiId)->count();//本班学生总数
		return $stu_num;
	}
	
	/**
	 * 根据班级Id获取教室Id
	*/
	public function getRoomIdFromBanjiId($banjiId=0){
		if (!$banjiId){return false;}
		$tmp = $this->where("banjiId=".$banjiId)->field("roomId")->find();
		return $tmp['roomId'];
	}
	
	/**
	 * 根据班级Id获取课程表Id
	*/
	public function getlessionTableIdFromBanjiId($banjiId=0){
		if (!$banjiId){return false;}
		$tmp = $this->where("banjiId=".$banjiId)->field("roomId")->find();//从班级表找roomId
		if ($tmp['roomId']){
			$lsTblModel = D('SchoolLessionTable');
			$lsTbl = $lsTblModel->where("roomId=".$tmp['id'])->field("id")->find();//从课程表中找课程表的自增Id，条件是roomId
			if ($lsTbl['id']){
				$lsTblId = $lsTbl['id'];
				return $lsTblId;	
			}else{
				return false;	
			}
			
		}else{
			return false;	
		}
	}	
	
	/**
	 * 根据班级ID获取本班所有学生ID，逗号分隔
	*/
	public function getStudentIdsStrFromBanjiId($banjiId=0){
		if (!$banjiId){return false;}
		$stuModel = D('SchoolStudents');
		$stu_num = $stuModel->where("banjiId=".$banjiId)->count();//本班学生总数
		if (!$stu_num){
			return '';
		}
		
		$students = $stuModel->where("banjiId=".$banjiId)->field("id")->order("id ASC")->select();
		$tmp = array();
		foreach ($students as $k=>$v){
			$tmp[] = $v['id'];
		}
		
		return implode(",",$tmp);
		
		
		
	}
	
	
	
	
}

