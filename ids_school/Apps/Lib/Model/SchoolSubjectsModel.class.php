<?php
/**
 * 
 * @author Skeam TJ
 * $subjectModel = D("SchoolSubjects");
 */
class SchoolSubjectsModel extends Model {
	protected $trueTableName = 'TB_Sch_Subjects';
	
	public function getList($map){
		return $this->where($map)->select();
	}
	
	//获取一行记录
	public function getOne($id){
		return $this->where("id=".$id)->find();
	}	
	
}

