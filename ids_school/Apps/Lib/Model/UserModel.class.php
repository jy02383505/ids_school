<?php
/**
 * 
 * 用户表
 * $userModel = D("User");
 */
class UserModel extends Model {
	protected $trueTableName = 'tb_users';
	
	/**
	 * 检测一个管理员可管理班级是否是1个
	 * 如是一个，直接返回班级ID
	 * 其它情况，均返回false
	*/
	public function userBanjiListCount(){
		$banjiModel = D('SchoolBanji');
		$map = array();
		if (session("username") == C('ADMIN_AUTH_KEY')) {
			//超级管理员，列表中显示全部班级
			return false;
		}else{
			//非超级管理员
			$map['id'] = array("IN",session('user_banji_list'));
		}
		$banjiCount = $banjiModel->where($map)->count();
		$banji = $banjiModel->where($map)->field("id")->find();
		if ($banjiCount == 1){
		//	$banjiIdStr = session('user_banji_list');
		//	$banjiIdStr = str_replace("0,","",$banjiIdStr);
		//	$banjiId = str_replace(",0","",$banjiIdStr);
			$banjiId = $banji['id'];
			return $banjiId;
		}else{
			return false;	
		}

	}
	
	
	/**
	 * 整理可管理的班级ID
	*/

	
	/**
	 * 获取一个学生或教师的对应会员ID	
	 * 输入:学生或教师的自动编号
	 * 输出：会员的自动编号
	*/
	public function getUserId($id=0,$type="student"/* student,teacher*/){
		if (!$id){return 0;}
		$datas = array();
		$map = array();
		$map['referId'] = $id;
		$map['type'] = $type;
		$datas = $this->where($map)->field("id")->find();
		if ($datas){
			return $datas['id'];
		}else{
			return 0;	
		}
	}
	
	
	
	/**
	 *　获取一批学生的对应会员ID
	 * 输入，逗号分隔的学生或教师ID
	 * 输出，逗号分隔的会员ID
	*/
	public function getUserIdStr($idStr,$type="student"/* student,teacher*/){
		if (empty($idStr)){return 0;}

		$out = "";
		$arr = array();
		$map = array();
		$map['type'] = $type;
		if (stripos($idStr,',')){
			//多个
			$map['referId'] = array("IN",$idStr);//"1,2,3,4,5,6,7,8,9"
			$datas = $this->where($map)->field("id")->select();
			if ($datas){
				foreach($datas as $k=>$v){
					$arr[] = $v['id'];
				}
				$out = implode(",",$arr);
				return $out;
			}else{
				return 0;
			}
		}else{
			$idStr = intval($idStr);
			$map['referId'] = array("EQ",$idStr);//一个学生或教师
			$datas = $this->where($map)->field("id")->find();
			if ($datas){
				return $datas['id'];
			}else{
				return 0;
			}
		}
		
	}
	
	/*
	 * 获取学生、教师的中文名称
	*/
	public function getNameFromUserId($userId=0){
		$stuModel = D('SchoolStudents');
		$teachersModel = D('SchoolTeachers');
		
		$datas_user = $this->where("id=".$userId)->find();
		
		if (!$datas_user){
			return 0;
		}else{
			$id = $datas_user['referId'];	
			$type = $datas_user['type'];	
		}
		
		$datas = array();
		switch ($type){
		//	case "student":
		//		$datas = $stuModel->where("id=".$id)->find();
		//		break;
			case "teacher":
				$datas = $teachersModel->where("id=".$id)->find();
				break;
			default:
				//$datas = $datas_user;
				$datas['name'] = $datas_user['account'];
		}
		
		if ($datas){
			return $datas['name'];
		}else{
			return 0;	
		}
		
	}
	
	/**
	 * 根据userid获取学生班级id
	*/
	
	
	
	
}

