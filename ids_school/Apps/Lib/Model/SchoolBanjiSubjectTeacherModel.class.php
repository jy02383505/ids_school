<?php
/**
 * 
 * 班级教师科目关联表
 * 主要存储某个班各科目的带课教师
 * 用途，比如在设置课程表时，只要指定课程，即可从这里得到对应的教师
 * $btsModel = D("SchoolBanjiSubjectTeacher");
 */
class SchoolBanjiSubjectTeacherModel extends Model {
	protected $trueTableName = 'TB_Sch_Banji_Teacher_Subject';
	
	//本班的全部记录
	public function getListFromBanjiId($banjiId){
		return $this->where("banjiId=".$banjiId)->select();
	}
	
	//本科目的全部记录
	public function getListFromSubjectId($subjectId){
		return $this->where("subjectId=".$subjectId)->select();
	}	
	
	//本教师的全部记录
	public function getListFromTeacherId($teacherId){
		return $this->where("teacherId=".$teacherId)->select();
	}
	
	//根据对应关系的自增id获取teacherId和subjectId
	public function getSubjectIdAndTeacherIdFromId(){
		
		
	}
	
}

