<?php
/**
 * 
 * @author Skeam TJ
 *
 */
class SchoolTeachersModel extends Model {
	protected $trueTableName = 'TB_Sch_Teachers';
	
	public function getList($map){
		return $this->where($map)->select();
	}
	
	public function getOneUUID($id=0){
		return $this->where("id=".$id)->field('uuid')->find();	
	}
	
	//获取一行记录
	public function getOne($id){
		return $this->where("id=".$id)->find();
	}
	
	/*
	 * 获取教师列表，加上教师所授科目，放在subjectId字段
	 * 返回值：datas数组，只是其中每一个教师都加了subjectId字段，字段值是逗号分隔的科目ID
	*/
	public function getTeachersAndSubjects($map){
		$datas = array();
	//	$map = array();
		$datas = $this->where($map)->select();
		
		$teacherSubjectModel = D('SchoolTeacherSubject');
		
		foreach($datas as $k=>$v){
			//所带科目，从TB_Sch_Teacher_Subject表获取，可能是多条

			$datas_teacher_subject = $teacherSubjectModel->where("teacherId=".$v['id'])->field("subjectId")->select();
			$subjectArrTmp = array();
			foreach($datas_teacher_subject as $kk=>$vv){
				$subjectArrTmp[] = $vv['subjectId'];
			}
			
			$datas[$k]['subjectId'] = implode(",",$subjectArrTmp);//该老师所带科目,$subjectArrTmp;//
		//	var_dump($datas);
		}
		return $datas;
	}
	
	/*
	 * 获取教师列表，加上所带班级，班级可能是多个，放到banjiId字段，逗号分隔
	 */
	 
	public function getTeachersAndBanji($map){
		$teacherBanjiModel = D("SchoolTeacherBanji");
		
		$datas = array();
		$datas = $this->where($map)->select();
		
		foreach($datas as $k=>$v){
			$datas_teacher_banji = $teacherBanjiModel->where("teacherId=".$v['id'])->field("banjiId")->select();
			$banjiArrTmp = array();
			foreach($datas_teacher_banji as $kk=>$vv){
				$banjiArrTmp[] = $vv['banjiId'];
			}
			
			$datas[$k]['banjiId'] = implode(",",$banjiArrTmp);//该老师所带班级,$subjectArrTmp;//
		}
		return $datas;		
	}
	
	/*
	 * 获取教师列表，带班级和科目
	*/
	public function getTeachersAndSubjectBanji($map){
		$datas = array();
		$datas = $this->where($map)->select();
		
		$teacherSubjectModel = D('SchoolTeacherSubject');
		$teacherBanjiModel = D("SchoolTeacherBanji");
		
		foreach($datas as $k=>$v){
			//所带科目
			$datas_teacher_subject = $teacherSubjectModel->where("teacherId=".$v['id'])->field("subjectId")->select();
			$subjectArrTmp = array();
			foreach($datas_teacher_subject as $kk=>$vv){
				$subjectArrTmp[] = $vv['subjectId'];
			}
			$datas[$k]['subjectId'] = implode(",",$subjectArrTmp);//该老师所带科目,$subjectArrTmp;//

			//所带班级
			$datas_teacher_banji = $teacherBanjiModel->where("teacherId=".$v['id'])->field("banjiId")->select();
			$banjiArrTmp = array();
			foreach($datas_teacher_banji as $kk=>$vv){
				$banjiArrTmp[] = $vv['banjiId'];
			}
			
			$datas[$k]['banjiId'] = implode(",",$banjiArrTmp);//该老师所带班级,$subjectArrTmp;//
		}
		return $datas;
	}
	
	/**
	 * 通过班级过滤教师
	 * @param  [string] $banjiId [班级id，入口参数]
	 * @return [array] $teacherList [教师列表]
	 */
	public function filtTeachers($banjiId){
		$teacherBanjiModel = D('SchoolTeacherBanji');
		$teacherIds = $teacherBanjiModel->where("banjiId=$banjiId")->getField('teacherId', true);
		$teacherIds = join(',', $teacherIds);
		$teacherList = $this->where("id in ($teacherIds)")->getField('id, name', true);
		if(!$teacherList){
			return false;
		}
		foreach($teacherList as $k => $v){
			$temp[id] = $k;
			$temp[name] = $v;
			$teachers[] = $temp;
		}
		return $teachers;
	}

	
}

